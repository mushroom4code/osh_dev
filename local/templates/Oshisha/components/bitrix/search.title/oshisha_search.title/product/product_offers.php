<?php

if (!empty($arItem) && !empty($itemOffers) && !empty($propsForOffers)) {
	$active = null;
	foreach ($itemOffers as $offer) {
		if ($active === null) {
			$active = $offer['ID'];
		}
	}
	$offerActive = $itemOffers[$active];
	?>
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
		<?php $backerDef = $arResult['BASKET_ITEMS'][$offerActive['ID']] ?? 0; ?>
		<div id="search_item_<? echo $arElement['ID'] ?>" class="bx_item_block_detail" style="display: none"
		     tabindex="0">
			<div class="d-flex flex-wrap flex-row mb-2 justify-content-end box-offers-auto">
				<?php
				foreach ($itemOffers as $keyItem => $offer) {
					if ((int)$offer['CATALOG_QUANTITY'] > 0) {
						$basketQuantity = $arResult['BASKET_ITEMS'][$offer['ID']] ?? 0;
						$taste = [];
						$offer['NAME'] = htmlspecialcharsbx($offer['NAME']);
						$prop = $offer['PROPERTIES'];
						if (!empty($offer['PROPERTIES']) && !empty($offer['PROPERTIES']['VALUE'])
							&& strripos($offer['PROPERTIES']['CODE'],'CML2') === false) {
							$prop_value = !is_array($prop['VALUE']) ?
								$prop['VALUE'] . $propsForOffers[$prop['CODE']]['PREF'] : $prop['VALUE'];
							$active_box = 'false';
							$type = $propsForOffers[$prop['CODE']]['TYPE'] ?? 'text';
							if ($keyItem == $active) {
								$active_box = 'true';
							}
							if (!empty($prop_value)) {
								if ($type === 'text') { ?>
									<div class="red_button_cart width-fit-content mb-lg-2 m-md-2 m-1 offer-box"
									     title="<?= $offer['NAME'] ?>"
									     data-active="<?= $active_box ?>"
									     data-product_id="<?= $offer['ID'] ?>"
									     data-product_quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
									     data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
									     data-basket-quantity="<?= $basketQuantity ?>"
									     data-basket_quantity="<?= $basketQuantity ?>"
									     data-price_base="<?= $offer['PRICES']['Основная']['PRINT_VALUE_VAT'] ?>"
									     data-sale_price="<?= $offer['PRICES']['Сайт скидка']['PRINT_VALUE_VAT'] ?>"
									     data-sale_base="<?= $offer['PRICES']['Розничная']['PRINT_VALUE_VAT'] ?>"
									     data-sale="<?= ($arElement['USE_DISCOUNT'] == 'Да') ? 1 : 0 ?>"
									     data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
									     data-onevalue="<?= $offer['ID'] ?>">
										<?= $prop_value ?>
									</div>
								<?php } elseif ($type === 'color') { ?>
									<div title="<?= $offer['NAME'] ?>"
									     data-active="<?= $active_box ?>"
									     data-product_id="<?= $offer['ID'] ?>"
									     data-product_quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
									     data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
									     data-basket-quantity="<?= $basketQuantity ?>"
									     data-basket_quantity="<?= $basketQuantity ?>"
									     data-price_base="<?= $offer['PRICES']['Основная']['PRINT_VALUE_VAT'] ?>"
									     data-sale_price="<?= $offer['PRICES']['Сайт скидка']['PRINT_VALUE_VAT'] ?>"
									     data-sale_base="<?= $offer['PRICES']['Розничная']['PRINT_VALUE_VAT'] ?>"
									     data-sale="<?= ($arElement['USE_DISCOUNT'] == 'Да') ? 1 : 0 ?>"
									     data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
									     data-onevalue="<?= $offer['ID'] ?>"
									     class="mr-1 offer-box color-hookah br-10 mb-1">
										<img src="<?= $prop_value ?>"
										     class="br-10"
										     width="50"
										     height="50"
										     alt="<?= $offer['NAME'] ?>"
										     loading="lazy"/>
									</div>
								<?php } elseif ($type === 'colorWithText') { ?>
									<div
										class="red_button_cart display-flex flex-row p-1 variation_taste
										 taste font-14 width-fit-content mb-1 mr-1 offer-box cursor-pointer"
										title="<?= $offer['NAME'] ?>"
										data-active="<?= $active_box ?>"
										data-product_id="<?= $offer['ID'] ?>"
										data-product_quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
										data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
										data-basket-quantity="<?= $basketQuantity ?>"
										data-basket_quantity="<?= $basketQuantity ?>"
										data-price_base="<?= $offer['PRICES']['Основная']['PRINT_VALUE_VAT'] ?>"
										data-sale_price="<?= $offer['PRICES']['Сайт скидка']['PRINT_VALUE_VAT'] ?>"
										data-sale_base="<?= $offer['PRICES']['Розничная']['PRINT_VALUE_VAT'] ?>"
										data-sale="<?= ($arElement['USE_DISCOUNT'] == 'Да') ? 1 : 0 ?>"
										data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
										data-onevalue="<?= $offer['ID'] ?>">
										<?php foreach ($prop_value as $elem_taste) { ?>
											<span class="taste mb-0"
											      data-background="<?= $elem_taste['color'] ?>"
											      style="background-color: <?= $elem_taste['color'] ?>;
												      border-color: <?= $elem_taste['color'] ?>;font-size: 11px;">
											<?= $elem_taste['name'] ?>
										</span>
										<?php } ?>
									</div>
									<?php
								}
							}
						}
					}
				} ?>
			</div>
			<div class="prices-all">
				<?php foreach ($itemOffers as $k => $price) {
					$dNone = 'd-none';
					if ($active == $k) {
						$dNone = '';
					} ?>
					<div class="<?= $dNone ?> prices-block box-prices" data-offer_id="<?= $price['ID'] ?>">
						<p>
							<span class="font-14 mr-2">Розничная (до 10к)</span> -
							<span
								class="font-14 ml-2 <?= ($arElement['USE_DISCOUNT'] == 'Да') ? 'price-discount' : '' ?>">
                                <?= $price['PRICES']['Розничная']['PRINT_VALUE_VAT'] ?></span>
						</p>
						<p>
							<span class="font-14 mr-2">Основная (до 30к)</span> -
							<span
								class="font-14 ml-2 <?= ($arElement['USE_DISCOUNT'] == 'Да') ? 'price-discount' : '' ?>">
                                <?= $price['PRICES']['Основная']['PRINT_VALUE_VAT'] ?></span>
						</p>
						<p>
							<span class="font-14 mr-2">b2b (от 30к)</span> -
							<span
								class="font-14 ml-2 <?= ($arElement['USE_DISCOUNT'] == 'Да') ? 'price-discount' : '' ?>">
                                <?= $price['PRICES']['b2b']['PRINT_VALUE_VAT'] ?></span>
						</p>
						<div class="old_and_current_prices_block">
							<div style="<?= ($arElement['USE_DISCOUNT'] == 'Нет') ? 'display: none' : '' ?>"
							     class="product-item-detail-price-current"
							     id="<?= $arElement['PRICE_ID'] ?>">
								<?= ($arElement['USE_DISCOUNT'] == 'Да') ?
									$price['PRICES']['Сайт скидка']['PRINT_VALUE_VAT'] :
									$price['PRICES']['Основная']['PRINT_VALUE_VAT'] ?>
							</div>
							<? if ($arElement['USE_DISCOUNT'] == 'Да'): ?>
								<span
									class="span">Старая цена <?= $price['PRICES']['Основная']['PRINT_VALUE_VAT'] ?></span>
							<? endif; ?>
						</div>
					</div>
				<?php } ?>
			</div>
			<? if ($arElement['ACTIVE'] === 'Y') : ?>
				<div
					class="mb-lg-3 mb-md-3 mb-4 d-flex flex-row align-items-center bx_catalog_item bx_catalog_item_controls"
					<?= (!$offerActive['PRICES']['Основная']['CAN_BUY'] ? ' style="display: none;"' : '') ?>
					data-entity="quantity-block">
					<div class="product-item-amount-field-contain">
                    <span class="btn-minus no-select minus_icon add2basket basket_prod_detail offers"
                          data-url="<?= $arItem['URL'] ?>"
                          data-product_id="<?= $offerActive['ID']; ?>"
                          id="<?= $arElement['QUANTITY_DOWN_ID'] ?>"
                          data-max_quantity="<?= $offerActive['CATAlOG_QUANTITY'] ?>"
                          data-max-quantity="<?= $offerActive['CATAlOG_QUANTITY'] ?>"
                          tabindex="0">
                    </span>
						<div class="product-item-amount-field-block">
							<input class="product-item-amount card_element cat-det offers"
							       id="<?= $arElement['QUANTITY_ID'] ?>"
							       type="number" value="<?= $backerDef ?>"
							       data-url="<?= $arItem['URL'] ?>"
							       data-product_id="<?= $offerActive['ID']; ?>"
							       data-max-quantity="<?= $offerActive['CATAlOG_QUANTITY'] ?>"/>
						</div>
						<span class="btn-plus no-select plus_icon add2basket basket_prod_detail offers"
						      data-url="<?= $arItem['URL'] ?>"
						      data-max_quantity="<?= $offerActive['CATAlOG_QUANTITY'] ?>"
						      data-max-quantity="<?= $offerActive['CATAlOG_QUANTITY'] ?>"
						      data-product_id="<?= $offerActive['ID']; ?>"
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
<?php }