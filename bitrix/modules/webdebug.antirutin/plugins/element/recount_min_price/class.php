<?
namespace WD\Antirutin\Plugins\Element;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\PluginElement;

class RecountMinPrice extends PluginElement {
	
	const GROUP = 'CATALOG';
	
	protected $arFieldsFilter = [
		'PRICES' => ['IS_WRITEABLE' => 'Y'],
	];
	
	protected $arFieldsFilter2 = [
		'PROPERTIES' => ['TYPE_FULL' => ['S', 'N']],
		'PRICES' => ['IS_WRITEABLE' => 'Y'],
	];
	
	// *******************************************************************************************************************
	// Main methods
	// *******************************************************************************************************************
	
	/**
	 *	Execute!
	 */
	public function processElement($intElementId){
		if(!count($this->getPricesId())){
			$this->setError(static::getMessage('ERROR_NO_SOURCE_PRICE'));
			return false;
		}
		if(!$this->get('target')){
			$this->setError(static::getMessage('ERROR_NO_TARGET_FIELD'));
			return false;
		}
		$bResult = $this->processMinPrice($intElementId);
		return $bResult;
	}
	
	/**
	 *	Get source value for transliteration
	 */
	protected function processMinPrice($intElementId){
		$arMinPrices = [];
		$arFeatures = [
			'OFFERS' => true,
		];
		if($this->get('including_main_price') == 'Y'){
			$arPricesId = $this->getPricesId();
			if(!empty($arPricesId)){
				$arFeatures['PRICES'] = $arPricesId;
			}
		}
		$arElement = IBlock::getElementArray($intElementId, $this->intIBlockId, $arFeatures);
		# Get optimal price for main product
		if($this->get('including_main_price') == 'Y'){
			if($this->get('with_discounts') == 'Y'){
				$arOptimalPrice = Helper::getOptimalPrice($intElementId, $this->get('site_id'), $arElement['PRICES']);
				if(is_array($arOptimalPrice) && $arOptimalPrice['UNROUND_DISCOUNT_PRICE']){
					$arOptimalPrice['PRICE'] = $arOptimalPrice['UNROUND_DISCOUNT_PRICE'];
					$arMinPrices[] = Helper::convertCurrencyArray($arOptimalPrice, $this->get('currency_id'), false);
				}
			}
			else{
				$arItemPrices = [];
				foreach($arElement['PRICES'] as $arPrice){
					$arItemPrices[] = Helper::convertCurrencyArray($arPrice, $this->get('currency_id'), false);
				}
				$arMinPrices[] = min($arItemPrices);
			}
		}
		# Get optimal price for offers
		if(is_array($arElement['OFFERS']) && !empty($arElement['OFFERS'])){
			$arFeatures = [];
			$arPricesId = $this->getPricesId();
			if(!empty($arPricesId)){
				$arFeatures['PRICES'] = $arPricesId;
			}
			$intOffersIBlockId = $arElement['OFFERS_IBLOCK_ID'];
			foreach($arElement['OFFERS'] as $intOfferId){
				$arOffer = IBlock::getElementArray($intOfferId, $intOffersIBlockId, $arFeatures);
				if($this->get('with_discounts') == 'Y'){
					$arOptimalPrice = Helper::getOptimalPrice($intOfferId, $this->get('site_id'), $arOffer['PRICES']);
					if(is_array($arOptimalPrice) && $arOptimalPrice['UNROUND_DISCOUNT_PRICE']){
						$arOptimalPrice['PRICE'] = $arOptimalPrice['UNROUND_DISCOUNT_PRICE'];
						$arMinPrices[] = Helper::convertCurrencyArray($arOptimalPrice, $this->get('currency_id'), false);
					}
				}
				else{
					$arItemPrices = [];
					foreach($arOffer['PRICES'] as $arPrice){
						$arItemPrices[] = Helper::convertCurrencyArray($arPrice, $this->get('currency_id'), false);
					}
					$arMinPrices[] = min($arItemPrices);
				}
			}
		}
		$arMinPrices = array_filter($arMinPrices);
		# Calculate min price
		if(!empty($arMinPrices)){
			$this->saveMinPrice($intElementId, min($arMinPrices), $this->get('currency_id'));
		}
		return true;
	}
	
	/**
	 *	Get saved prices id
	 */
	protected function getPricesId(){
		$arResult = [];
		if(is_array($this->get('price_type'))){
			foreach($this->get('price_type') as $strField){
				if($intPriceId = $this->isPrice($strField)){
					$arResult[] = $intPriceId;
				}
			}
		}
		return $arResult;
	}
	
	/**
	 *	Save calculated min price
	 */
	protected function saveMinPrice($intElementId, $fMinPrice, $strCurrency){
		$strField = $this->get('target');
		$strCurrency = $this->get('currency_id');
		if($intPropertyId = $this->isProperty($strField)){
			$this->setPropertyValue($intElementId, $intPropertyId, $fMinPrice);
		}
		elseif($intPriceId = $this->isPrice($strField)){
			Helper::setProductPrice($intElementId, $intPriceId, $fMinPrice, $strCurrency);
		}
	}
	
}

?>