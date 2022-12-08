<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$bDefaultColumns = $arResult["GRID"]["DEFAULT_COLUMNS"];
$colspan = ($bDefaultColumns) ? count($arResult["GRID"]["HEADERS"]) : count($arResult["GRID"]["HEADERS"]) - 1;
$bPropsColumn = false;
$bUseDiscount = false;
$bPriceType = false;
$bShowNameWithPicture = ($bDefaultColumns) ? true : false; // flat to show name and picture column in one column
//echo '<pre>'; print_r($arResult["GRID"]["ROWS"]);
global $ETAJ, $PODIEM_PRICE, $PODIEM, $USTANOVKA, $DATE_DELIVERY;
if( $arResult["DELIVERY_PRICE"] == '99999999')
{
	$arResult["DELIVERY_PRICE"] = '';
	$arResult["ORDER_TOTAL_PRICE"] = $arResult["ORDER_TOTAL_PRICE"] - 99999999;
}
?>

<div class="bx_ordercart">
	<div class="bx_ordercart_first">
		<div class="bx_ordercart_first_left">Ваш заказ</div>
		<div class="bx_ordercart_first_right"><a href="/personal/cart/">Вернуться в корзину</a></div>
	</div>

	<div class="bx_ordercart_order_table_container">
	<div class="bx_ordercart_second">
		<div class="bx_ordercart_first_left">В заказе <?=count($arResult["GRID"]["ROWS"])?> <?countNum(count($arResult["GRID"]["ROWS"]))?> </div>
		<div class="bx_ordercart_first_right"><i class="fa fa-angle-down"></i></div>
	</div>	
	<div class="summary_items" data-show="" style="display:none;">
			<?foreach ($arResult["GRID"]["ROWS"] as $k => $arData):?>
			
			<?//print_r($arData['data']);
						$arPRICE = CCatalogProduct::GetOptimalPrice($arData['data']['PRODUCT_ID']);
						$D_PRICE = $D_PRICE+$arPRICE['RESULT_PRICE']['DISCOUNT_PRICE'];						
						$BASE_PRICE = $BASE_PRICE+$arPRICE['RESULT_PRICE']['BASE_PRICE']; 			
			?>
				<div class="summary_item">
					<div class="img"> <img src="<?=$arData['data']['DETAIL_PICTURE_SRC']?>"></div>
					<div class="summary_item_right">
						<div class="name"><?=$arData['data']['NAME']?></div>
						<div class="quantity_row">
						<?=$arData['data']['QUANTITY']?> шт. x <?=$arData['data']['PRICE']?>&nbsp;₽<?if($arData['data']['WEIGHT'] != ''):?>, вес <?=$arData['data']['WEIGHT']?> кг.<?elseif( $arResult['GABARITS'][$arData['data']['PRODUCT_ID']]['WEIGHT'] != '' ):?>, вес <?=$arResult['GABARITS'][$arData['data']['PRODUCT_ID']]['WEIGHT']?> кг.<?endif;?></div>
						
						<?if( $arData['data']['PROPS'][0]['CODE'] == 'КОМПЛЕКТ' ):?>
							<div class="complect_title">В комплекте с</div>
							<?foreach( $arData['data']['PROPS'] as $CODE_PROPS):?>
								<?if($CODE_PROPS['CODE'] == 'КОМПЛЕКТ' ) continue;?>
							<div class="complect_item">
								<div class="complect_item_name"><?=$CODE_PROPS['NAME']?></div>
								<div class="complect_item_quantity">1 шт х <?=$CODE_PROPS['VALUE']?></div>
							</div>
							<?endforeach?>
							
						<?endif;?>
					
					</div>
				</div>
			<?endforeach;?>
		</div>	
		<div class="summary">
			<?if( $BASE_PRICE > $D_PRICE):?>
			<div class="summary_item summary_item_total_amount">
				<div class="summary_item_left">Сумма</div>
				<div class="summary_item_right"><?=$BASE_PRICE?> ₽</div>
			</div>
			<div class="summary_item summary_item_discount">
				<div class="summary_item_left">Скидка</div>
				<div class="summary_item_right"><?=$BASE_PRICE-$D_PRICE?> ₽</div>
			</div>	
			<?endif;?>
			<div class="summary_item">
				<div class="summary_item_left">Итого</div>
				<div class="summary_item_right"><?=$arResult["ORDER_TOTAL_PRICE"]?> ₽</div>
			</div>
			
		</div>
	
	
		<div class="bx_ordercart_order_pay_center">
		<a href="javascript:void();" onclick="submitForm('Y'); return false;" class="checkout"><?=GetMessage("SOA_TEMPL_BUTTON")?></a></div>	
	</div>
	<div class="bx_ordercart_order_added">
		<h4>Дополнительно</h4>
			<div class="summary_delivery_item">
				<div class="summary_delivery_item_left">Доставка </div>
				<div class="summary_delivery_item_right"><?=$arResult["DELIVERY_PRICE"]?> ₽</div>
			</div>	
			<div class="summary_delivery_item summary_delivery_item_info">
				<div class="summary_delivery_item_all"><?if( $DATE_DELIVERY!= '' ):?><span><?=$DATE_DELIVERY?></span><?endif;?></div>
			</div>			
			<?if( $PODIEM == 1 ):?>
			<div class="summary_delivery_item">
				<div class="summary_delivery_item_left">Подъем на этаж </div>
				<div class="summary_delivery_item_right"><?if($PODIEM_PRICE!= ''):?><?=$PODIEM_PRICE?> ₽<?endif;?></div>
			</div>
			<div class="summary_delivery_item summary_delivery_item_info <?if( $ETAJ > 0 ):?>etaj_active<?endif;?>">
				<div class="summary_delivery_item_all"><?if( $ETAJ > 0 ):?><span>(<?=$ETAJ?> этаж)</span><?endif;?></div>
			</div>			
			
			<?endif;?>
			<?if( $USTANOVKA == 1 ):?>
			<div class="summary_delivery_item summary_delivery_item_info_ustanovka">
				<div class="summary_delivery_item_left">Установка <span></span></div>
				<div class="summary_delivery_item_right"></div>
			</div>
			<?endif;?>
			<div class="summary_delivery_item summary_delivery_item_info_ustanovka_2">
			
				<?$APPLICATION->IncludeFile(SITE_DIR."include/order/text_props_order_install.php", Array(), Array("MODE" => "html",  "NAME" => 'Текст о установке'));?>			
			</div>
			
	</div>


	<div class="personal_data">
	Нажимая кнопку "оформить заказ" вы даёте согласие на обработку ваших <a href="/consent/" target=_blank>персональных данных</a>.
	</div>
	<div class="bx_ordercart_order_pay2">

		
		<div class="bx_section" style="display:none;">
		<div class="bx_block r1x3 pt8">
			<?=GetMessage("SOA_TEMPL_SUM_COMMENTS")?>
			</div>
			<div class="bx_block r3x1"><textarea name="ORDER_DESCRIPTION" id="ORDER_DESCRIPTION" rows=3 cols=30><?=$arResult["USER_VALS"]["ORDER_DESCRIPTION"]?></textarea></div>
			<input type="hidden" name="" value="">
			<div style="clear: both;"></div><br />
		</div>
	</div>
</div>

<?
	function countNum($num)
	{
		if( $num == 1 || $num == 21 )
			echo 'товар';
		elseif( $num == 2 || $num == 3 || $num == 4 )
			echo 'товара';
		else
			echo 'товаров';
	}
?>