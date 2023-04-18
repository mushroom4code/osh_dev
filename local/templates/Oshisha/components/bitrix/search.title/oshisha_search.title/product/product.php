<?php if (!empty($arItem)) ?>
<div class="catalog-item-product not-input-parse">
	<div class="bx_item_block" onclick="window.location='<?= $arItem["URL"] ?>';">
		<? if (!empty($arElement["PICTURE"])): ?>
			<div class="bx_img_element">
				<div class="bx_image"
				     style="background-image: url('<? echo $arElement["PICTURE"] ?>')"></div>
			</div>
		<? endif; ?>
		<div class="bx_item_element">
			<a href="<? echo $arItem["URL"] ?>"><?= $arElement["NAME"] ?></a>

			<i id="search_result<? echo $arElement['ID'] ?>_detail_opener"
			   class="fa fa-angle-right" aria-hidden="true" tabindex="0"></i>
		</div>
		<div style="clear:both;"></div>
	</div>
	<?php
	$textButton = 'Забронировать';
	$classButton = 'btn_basket';
	if ($arElement['BASKET_QUANTITY'] > 0) {
		$textButton = 'Забронировано';
		$classButton = 'addProductDetailButton';
	}
	?>
	<div id="search_item_<? echo $arElement['ID'] ?>" class="bx_item_block_detail" style="display: none"
	     tabindex="0">
		<div class="d-flex flex-column prices-block">
			<p>
				<span class="font-14 mr-2">Розничная (до 10к)</span> -
				<span
					class="font-14 ml-2 <?= ($arElement['USE_DISCOUNT'] == 'Да') ? 'price-discount' : '' ?>">
                                <?= $arElement['PRICES']['Розничная']['PRINT_VALUE_VAT'] ?></span>
			</p>
			<p>
				<span class="font-14 mr-2">Основная (до 30к)</span> -
				<span
					class="font-14 ml-2 <?= ($arElement['USE_DISCOUNT'] == 'Да') ? 'price-discount' : '' ?>">
                                <?= $arElement['PRICES']['Основная']['PRINT_VALUE_VAT'] ?></span>
			</p>
			<p>
				<span class="font-14 mr-2">b2b (от 30к)</span> -
				<span
					class="font-14 ml-2 <?= ($arElement['USE_DISCOUNT'] == 'Да') ? 'price-discount' : '' ?>">
                                <?= $arElement['PRICES']['b2b']['PRINT_VALUE_VAT'] ?></span>
			</p>
		</div>
		<div class="old_and_current_prices_block">
			<div style="<?= ($arElement['USE_DISCOUNT'] == 'Нет') ? 'display: none' : '' ?>"
			     class="product-item-detail-price-current"
			     id="<?= $arElement['PRICE_ID'] ?>">
				<?= ($arElement['USE_DISCOUNT'] == 'Да') ?
					$arElement['PRICES']['Сайт скидка']['PRINT_VALUE_VAT'] :
					$arElement['PRICES']['Основная']['PRINT_VALUE_VAT'] ?>
			</div>
			<? if ($arElement['USE_DISCOUNT'] == 'Да'): ?>
				<span
					class="span">Старая цена <?= $arElement['PRICES']['Основная']['PRINT_VALUE_VAT'] ?></span>
			<? endif; ?>
		</div>
		<? if ($arElement['CATALOG_QUANTITY'] > 0): ?>
			<div
				class="mb-lg-3 mb-md-3 mb-4 d-flex flex-row align-items-center bx_catalog_item bx_catalog_item_controls"
				<?= (!$arElement['PRICES']['Основная']['CAN_BUY'] ? ' style="display: none;"' : '') ?>
				data-entity="quantity-block">
				<div class="product-item-amount-field-contain">
                                <span class="btn-minus no-select minus_icon add2basket basket_prod_detail"
                                      data-url="<?= $arItem['URL'] ?>"
                                      data-product_id="<?= $arElement['ID']; ?>"
                                      id="<?= $arElement['QUANTITY_DOWN_ID'] ?>"
                                      data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] ?>"
                                      tabindex="0">
                                </span>
					<div class="product-item-amount-field-block">
						<input class="product-item-amount card_element cat-det"
						       id="<?= $arElement['QUANTITY_ID'] ?>"
						       type="number" value="<?= $arElement['BASKET_QUANTITY'] ?>"
						       data-url="<?= $arItem['URL'] ?>"
						       data-product_id="<?= $arElement['ID']; ?>"
						       data-max-quantity="<?= $arElement['QUANTITY'] ?>"/>
					</div>
					<span class="btn-plus no-select plus_icon add2basket basket_prod_detail"
					      data-url="<?= $arItem['URL'] ?>"
					      data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] ?>"
					      data-product_id="<?= $arElement['ID']; ?>"
					      id="<?= $arElement['QUANTITY_UP_ID'] ?>" tabindex="0">
                                </span>
				</div>
				<div id="result_box"></div>
				<div id="popup_mess"></div>
			</div>
		<? else: ?>
			<div class="mb-4 d-flex justify-content-between align-items-center">
				<div class="not_product detail_popup">Нет в наличии</div>
			</div>
		<? endif; ?>
	</div>
	<script>
        $('#search_result<?echo $arElement['ID']?>_detail_opener').click(function (event) {
            event.stopImmediatePropagation();
            $("#search_item_<?echo $arElement['ID']?>").toggle("fast");
            var matrix = $(this).css("transform");
            if (matrix !== 'none') {
                var values = matrix.split('(')[1].split(')')[0].split(',');
                var a = values[0];
                var b = values[1];
                var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));
            } else {
                var angle = 0;
            }
            if (angle == 90) {
                $(this).css({'transform': 'rotate(0deg)', 'transition-duration': '600ms'});
                $('.bx_searche div.alert_quantity[data-id="<?echo $arElement['ID']?>"]').removeClass('show_block');
                $('.bx_searche div.alert_quantity[data-id="<?echo $arElement['ID']?>"]').contents().remove();
            } else {
                $(this).css({'transform': 'rotate(90deg)', 'transition-duration': '600ms'});
            }
        });
	</script>
	<div class="alert_quantity" data-id="<?= $arElement['ID'] ?>"></div>
</div>