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
$countProduct = count($arResult["GRID"]["ROWS"]);
?>

			
<h5 class="order_text"><b>Оформление заказа</b></h5>
<div id="bx-soa-total" class="mb-5 bx-soa-sidebar">
	<div class="bx-soa-cart-total-ghost"></div>
	<div class="bx-soa-cart-total">
		<div class="bx-soa-cart-total-line"><span class="bx-soa-cart-t"><span>Товары &nbsp;(<?=$countProduct?>)</span></span><span class="bx-soa-cart-d"><?=$arResult["ORDER_PRICE_FORMATED"]?></span></div>
		<div class="bx-soa-cart-total-line"><span class="bx-soa-cart-t">Общий вес:</span><span class="bx-soa-cart-d"><?=$arResult["ORDER_WEIGHT_FORMATED"]?></span></div>
		<div class="bx-soa-cart-total-line"><span class="bx-soa-cart-t">Доставка:</span>
			<span class="bx-soa-cart-d"><span class="bx-soa-price-free"><?if (doubleval($arResult["DELIVERY_PRICE"]) > 0):?><?=$arResult["DELIVERY_PRICE_FORMATED"]?><?else:?>бесплатно<?endif;?></span></span></div>


		<div class="bx-soa-cart-total-line bx-soa-cart-total-line-total"><span class="bx-soa-cart-t">Общая стоимость</span><span class="bx-soa-cart-d"><?=$arResult["ORDER_TOTAL_PRICE_FORMATED"]?></span></div>
		<div class="bx-soa-cart-total-button-containerd-block"><a href="javascript:void(0)" onclick="submitForm('Y'); return false;"  class="btn btn_basket btn-order-save">Оформить заказ</a></div>


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