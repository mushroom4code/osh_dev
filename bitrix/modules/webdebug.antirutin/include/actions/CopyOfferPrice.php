<?
class CWDA_CopyOfferPrice extends CWDA_Plugin {
	CONST GROUP = 'CATALOG';
	CONST CODE = 'COPY_OFFER_PRICE';
	CONST NAME = 'ТП: установка цены из родительского товара';
	//
	static function GetDescription() {
		$Descr = 'Плагин устанавливает цену торгового предложения копированием из родительского товара. Имейте ввиду:<ul><li>При выборе основного инфоблока, модуль установит цену всем предложениям, относящимся к выбранным товарам,</li><li>при выборе инфоблока торговых предложений, модуль установит цену непосредственно к выбранным элементам.</li></ul>';
		if (!CWDA::IsUtf()) {
			$Descr = CWDA::ConvertCharset($Descr);
		}
		return $Descr;
	}
	static function GetMessage($Code, $ConvertCharset=false) {
		$MESS = array(
			'ALERT_NO_FIELD_PRICE' => 'Укажите тип цены для копирования',
			'PRICE_TARGET' => 'Тип цен',
		);
		$MESS = trim($MESS[$Code]);
		if ($ConvertCharset && !CWDA::IsUtf()) {
			$MESS = CWDA::ConvertCharset($MESS);
		}
		return $MESS;
	}
	//
	static function AddHeadData() {
		?>
		<script>
		BX.addCustomEvent('onWdaAfterActionChange', function(){
			if(WdaCurrentAction=='<?=self::CODE?>'){
				WDA_<?=self::CODE?>_Fill();
			}
		});
		BX.addCustomEvent('onWdaBeforeSubmit', function(){
			if(WdaCurrentAction=='<?=self::CODE?>'){
				 if ($.trim($('#wda_price_target').val())=='') {
					alert('<?=self::GetMessage('ALERT_NO_FIELD_PRICE',true);?>');
					WdaCanSubmit = false;
				}
			}
		});
		//
		function WDA_<?=self::CODE?>_Fill(){
			var Select = $('#wda_filter_param');
			var IBlock = $('#wda_select_iblock').val();
			// Target
			var SelectTarget = $('#wda_price_target').html(Select.html());
			SelectTarget.find('optgroup').not('optgroup[data-group=PRICES]').remove();
			SelectTarget.change();
		}
		</script>
		<?
	}
	static function ShowSettings($IBlockID=false) {
		?>
		<div id="wda_settings_<?=self::CODE?>">
			<div class="wda_settings_header"><?=self::GetMessage('PRICE_TARGET');?></div>
			<div>
				<div><select name="params[price_target]" id="wda_price_target" class="wda_select_field"></select></div>
			</div>
		</div>
		<?
	}
	static function Process($ElementID, $arElement, $Params) {
		$bResult = false;
		$IBlockElement = new CIBlockElement;
		$PriceTarget = preg_replace('#^CATALOG_PRICE_(\d+)$#i','$1',$Params['price_target']);
		$OffersIBlockID = $arElement['IBLOCK_ID'];
		if($OffersIBlockID>0 && $PriceTarget>0) {
			$arCatalog = CCatalog::GetByID($OffersIBlockID);
			if(is_array($arCatalog) && $arCatalog['PRODUCT_IBLOCK_ID']>0 && $arCatalog['SKU_PROPERTY_ID']>0){
				foreach($arElement['PROPERTIES'] as $PropCode => $arProperty){
					if($arProperty['ID']==$arCatalog['SKU_PROPERTY_ID']){
						$ProductID = $arProperty['VALUE'];
						if($ProductID>0){
							$resProduct = CIBlockElement::GetList(array(),array('ID'=>$ProductID,'IBLOCK_ID'=>$arCatalog['PRODUCT_IBLOCK_ID']),false,false,array('ID','CATALOG_GROUP_'.$PriceTarget));
							if($arProduct = $resProduct->GetNext(false,false)){
								if(!CWDA::SetProductPrice($ElementID, $PriceTarget, $arProduct['CATALOG_PRICE_'.$PriceTarget], $arProduct['CATALOG_CURRENCY_'.$PriceTarget])){
									CWDA::Log('Error set price '.$arProduct['CATALOG_PRICE_'.$PriceTarget].' [ID='.$PriceTarget.'] to element '.$ElementID);
								}
							}
						}
						break;
					}
				}
			}
			elseif(is_array($arCatalog) && $arCatalog['OFFERS_IBLOCK_ID']>0 && $arCatalog['OFFERS_PROPERTY_ID']>0) {
				$arFilter = array(
					'IBLOCK_ID' => $arCatalog['OFFERS_IBLOCK_ID'],
					'PROPERTY_'.$arCatalog['OFFERS_PROPERTY_ID'] => $ElementID,
				);
				$fPrice = FloatVal($arElement['CATALOG_PRICE_'.$PriceTarget]);
				$strCurrency = $arElement['CATALOG_CURRENCY_'.$PriceTarget];
				$resOffers = CIBlockElement::GetList(array(),$arFilter,false,false,array('ID'));
				$bResult = true;
				while($arOffer = $resOffers->getNext(false, false)){
					if(!CWDA::SetProductPrice($arOffer['ID'], $PriceTarget, $fPrice, $strCurrency)){
						CWDA::Log('Error set price '.$fPrice.' [ID='.$PriceTarget.'] to offer '.$arOffer['ID']);
						$bResult = false;
					}
				}
				unset($resOffers, $arOffer);
			}
		}
		return $bResult;
	}
}
?>