<?
namespace WD\Antirutin\Plugins\Element;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\PluginElement;

class SetQuantityByStores extends PluginElement {
	
	const GROUP = 'CATALOG';
	
	protected $arFieldsFilter = [
		'CATALOG' => ['CODE' => ['STORE_AMOUNT_%']],
	];
	
	// *******************************************************************************************************************
	// Main methods
	// *******************************************************************************************************************
	
	/**
	 *	Execute!
	 */
	public function processElement($intElementId){
		if($this->isEmpty('stores') || !is_array($this->get('stores'))){
			$this->setError(static::getMessage('ERROR_NO_STORES'));
			return false;
		}
		$bResult = true;
		$bProduct = in_array($this->get('mode'), ['product', 'all']);
		$bOffers = in_array($this->get('mode'), ['offers', 'all']);
		if($bProduct){
			$bResult = $this->processSingleElement($intElementId);
		}
		if($bOffers){
			$bResult = $this->processElementOffers($intElementId);
		}
		return $bResult;
	}
	
	/**
	 *	Process product or offer
	 */
	protected function processSingleElement($intElementId, $intIBlockId=null){
		$arFeatures = ['STORES' => true];
		$intIBlockId = is_numeric($intIBlockId) ? $intIBlockId : $this->intIBlockId;
		$arElement = IBlock::getElementArray($intElementId, $intIBlockId, $arFeatures);
		$fAmount = 0;
		foreach($this->get('stores') as $strField){
			if($intStoreId = $this->isStoreAmount($strField)){
				if(is_array($arElement['STORES'][$intStoreId])){
					$fAmount += floatVal($arElement['STORES'][$intStoreId]['AMOUNT']);
				}
			}
		}
		return Helper::setProductField($intElementId, 'QUANTITY', $fAmount);
	}
	
	/**
	 *	
	 */
	protected function processElementOffers($intElementId){
		$bResult = false;
		$arCatalog = Helper::getCatalogArray($this->intIBlockId);
		if(is_array($arCatalog) && $arCatalog['OFFERS_IBLOCK_ID'] && $arCatalog['OFFERS_PROPERTY_ID']){
			$arFilter = [
				'IBLOCK_ID' => $arCatalog['OFFERS_IBLOCK_ID'],
				'PROPERTY_'.$arCatalog['OFFERS_PROPERTY_ID'] => $intElementId,
			];
			$resOffers = \CIBlockElement::getList([], $arFilter, false, false, ['ID']);
			while($arOffer = $resOffers->getNext(false, false)){
				$bOfferResult = $this->processSingleElement($arOffer['ID'], $arCatalog['OFFERS_IBLOCK_ID']);
				if($bOfferResult){
					$bResult = true;
				}
			}
		}
		return $bResult;
	}
	
}

?>