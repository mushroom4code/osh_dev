<?php if (!empty($arItem) && !empty($itemOffers)) ?>
<div class="catalog-item-product">
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
	<div id="search_item_<? echo $arElement['ID'] ?>" class="bx_item_block_detail " style="display: none"
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
                    <span class="btn-minus no-select minus_icon add2basket basket_prod_detail offers"
                          data-url="<?= $arItem['URL'] ?>"
                          data-product_id="<?= $arElement['ID']; ?>"
                          id="<?= $arElement['QUANTITY_DOWN_ID'] ?>"
                          data-max_quantity="<?= $prod_off_quantity ?>"
                          data-max-quantity="<?= $prod_off_quantity ?>"
                          tabindex="0">
                    </span>
					<div class="product-item-amount-field-block">
						<input class="product-item-amount card_element cat-det offers"
						       id="<?= $arElement['QUANTITY_ID'] ?>"
						       type="number" value="<?= $arElement['BASKET_QUANTITY'] ?>"
						       data-url="<?= $arItem['URL'] ?>"
						       data-product_id="<?= $arElement['ID']; ?>"
						       data-max-quantity="<?= $arElement['CATAlOG_QUANTITY'] ?>"/>
					</div>
					<span class="btn-plus no-select plus_icon add2basket basket_prod_detail offers"
					      data-url="<?= $arItem['URL'] ?>"
					      data-max_quantity="<?= $prod_off_quantity ?>"
					      data-max-quantity="<?= $prod_off_quantity ?>"
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
		<div class="d-flex flex-wrap flex-row mb-2 justify-content-end box-offers-auto">
			<?php foreach ($itemOffers as $offer) {
				if ((int)$offer['CATALOG_QUANTITY'] > 0) {

					$taste = [];
					$offer['NAME'] = htmlspecialcharsbx($offer['NAME']);
					if (!empty($offer['PROPERTY_GRAMMOVKA_G_VALUE'])
						|| !empty($offer['PROPERTY_SHTUK_V_UPAKOVKE_VALUE'])
						|| !empty($offer['PROPERTY_KOLICHESTVO_ZATYAZHEK_VALUE'])) {
						if (!empty($offer['PROPERTY_GRAMMOVKA_G_VALUE'])) {
							$prop_value = $offer['PROPERTY_GRAMMOVKA_G_VALUE'];
						} else {
							if (!empty($offer['PROPERTY_KOLICHESTVO_ZATYAZHEK_VALUE'])) {
								$prop_value = $offer['PROPERTY_KOLICHESTVO_ZATYAZHEK_VALUE'];
							} else {
								if (!empty($offer['PROPERTY_SHTUK_V_UPAKOVKE_VALUE'])) {
									$prop_value = $offer['PROPERTY_SHTUK_V_UPAKOVKE_VALUE'];
								}
							}
						} ?>
						<div class="red_button_cart width-fit-content mb-lg-2 m-md-2 m-1 offer-box"
						     title="<?= $offer['NAME'] ?>"
						     data-active="<?= $active_box ?>"
						     data-product_id="<?= $offer['ID'] ?>"
						     data-product_quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
						     data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
						     data-basket-quantity="<?= 0 ?>"
						     data-basket_quantity="<?= 0 ?>"
						     data-price_base="<?= $offer['PRICE'][1]['PRICE'] ?>"
						     data-sale_price="<?= 0 ?>"
						     data-sale_base="<?= $offer['PRICE'][3]['PRICE'] ?>"
						     data-sale="<?= 0 ?>"
						     data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
						     data-onevalue="<?= $offer['ID'] ?>">
							<?= $prop_value ?? '0' ?>
						</div>
					<?php } elseif (!empty($offer['PROPERTY_TSVET_VALUE'])) { ?>
						<div title="<?= $offer['NAME'] ?>"
						     data-active="<?= $active_box ?>"
						     data-product_id="<?= $offer['ID'] ?>"
						     data-product_quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
						     data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
						     data-basket-quantity="<?= $basketItem ?>"
						     data-basket_quantity="<?= $basketItem ?>"
						     data-price_base="<?= $offer['PRICES_CUSTOM']['PRICE_DATA'][1]['PRICE'] ?>"
						     data-sale_price="<?= $price['SALE_PRICE']['PRICE'] ?>"
						     data-sale_base="<?= $offer['PRICES_CUSTOM']['PRICE_DATA'][0]['PRICE'] ?>"
						     data-sale="<?= $sale ?>"
						     data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
						     data-onevalue="<?= $offer['ID'] ?>"
						     class="mr-1 offer-box color-hookah br-10 mb-1">
							<img src="<?= $offer['PICTURE'] ?>"
							     class="br-10"
							     width="50"
							     height="50"
							     alt="<?= $offer['NAME'] ?>"
							     loading="lazy"/>
						</div>
					<?php } elseif (!empty($offer['PROPERTY_VKUS_VALUE'])) { ?>
						<div
							class="red_button_cart display-flex flex-row p-1 variation_taste
										 taste font-14 width-fit-content mb-1 mr-1 offer-box cursor-pointer"
							title="<?= $offer['NAME'] ?>"
							data-active="<?= $active_box ?>"
							data-product_id="<?= $offer['ID'] ?>"
							data-product_quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
							data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
							data-basket-quantity="<?= $basketItem ?>"
							data-basket_quantity="<?= $basketItem ?>"
							data-price_base=data-price_base="<?= $offer['PRICES_CUSTOM']['PRICE_DATA'][1]['PRICE'] ?>"
							data-sale_price="<?= $price['SALE_PRICE']['PRICE'] ?>"
							data-sale_base="<?= $offer['PRICES_CUSTOM']['PRICE_DATA'][0]['PRICE'] ?>"
							data-sale="<?= $sale ?>"
							data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
							data-onevalue="<?= $offer['ID'] ?>">
							<?php
							foreach ($offer['PROPERTY_VKUS_VALUE'] as $elem_taste) { ?>
								<span class="taste mb-0"
								      data-background="<?= $elem_taste['color'] ?>"
								      style="background-color: <?= $elem_taste['color'] ?>;
									      border-color:<?= $elem_taste['color'] ?>;
									      padding: 4px 8px;
									      color:black;
									      line-height: 1.5;
									      display: block"><?= $elem_taste ?></span>
							<?php } ?>
						</div>
						<?php
					}
				}
			} ?>
		</div>
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