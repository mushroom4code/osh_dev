<div class="col-md-6 col-sm-6 col-lg-6 product_left col-12">
	<div class="product-item-detail-slider-container
<?php if (!empty($taste['VALUE'])) { ?> p-lg-md-25 <?php } ?>" id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
		<div class="variation_taste" style="max-width: 10%; height: 90%">
			<?php foreach ($taste['VALUE'] as $key => $nameTaste) {
				foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
					if ($key === $keys) {
						$color = explode('#', $value); ?>
						<span class="taste" data-background="<?= '#' . $color[1] ?>"
						      id="<?= $color[0] ?>">
                                    <?= $nameTaste ?>
                                    </span>
					<?php }
				}
			} ?>
		</div>
		<div class="product-item-detail-slider-block
                    <?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '') ?>"
		     data-entity="images-slider-block">
			<div>
				<span class="product-item-detail-slider-left carousel_elem_custom" data-entity="slider-control-left"
				      style="display: none;"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
				<span class="product-item-detail-slider-right carousel_elem_custom"
				      data-entity="slider-control-right"
				      style="display: none;"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
				<div class="product-item-detail-slider-images-container" data-entity="images-container">
					<?php if (!empty($actualItem['PICTURE'][0]['SRC'])) {
						foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
							<div class="product-item-detail-slider-image<?= ($key == 0 ? ' active' : '') ?>"
							     data-entity="image" data-id="<?= $photo['ID'] ?>">
								<img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
								     title="<?= $title ?>"<?= ($key == 0 ? ' itemprop="image"' : '') ?>>
							</div>
						<?php }
					} else { ?>
						<div class="product-item-detail-slider-image active" data-entity="image"
						     data-id="1">
							<img src="/local/templates/Oshisha/images/no-photo.gif" itemprop="image">
						</div>
						<?php
					}
					if ($arParams['SLIDER_PROGRESS'] === 'Y') { ?>
						<div class="product-item-detail-slider-progress-bar"
						     data-entity="slider-progress-bar"
						     style="width: 0;"></div>
					<?php } ?>
				</div>
			</div>
			<div class="box_with_net" <?php if (empty($taste['VALUE'])){ ?>style="padding: 20px;"<?php } ?>>
				<?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
					'templates',
					array(
						'ID_PROD' => $arResult['ID'],
						'F_USER_ID' => $FUser_id,
						'LOOK_LIKE' => true,
						'LOOK_FAVORITE' => true,
						'COUNT_LIKE' => $arResult['COUNT_LIKE'],
						'COUNT_FAV' => $arResult['COUNT_FAV'],
						'COUNT_LIKES' => $arResult['COUNT_LIKES'],
					)
					,
					$component,
					array('HIDE_ICONS' => 'Y')
				); ?>
				<a href="#" class="delligate shared" title="Поделиться"
				   data-element-id="<?= $arResult['ID'] ?>">
					<i class="fa fa-paper-plane-o" aria-hidden="true"></i>
					<div class="shared_block">
						<?php $APPLICATION->IncludeComponent(
							"arturgolubev:yandex.share",
							"",
							array(
								"DATA_IMAGE" => "",
								"DATA_RESCRIPTION" => "",
								"DATA_TITLE" => $arResult['NAME'],
								"DATA_URL" => 'https://' . SITE_SERVER_NAME . $arResult['DETAIL_PAGE_URL'],
								"OLD_BROWSERS" => "N",
								"SERVISE_LIST" => BXConstants::Shared(),
								"TEXT_ALIGN" => "ar_al_left",
								"TEXT_BEFORE" => "",
								"VISUAL_STYLE" => "icons"
							)
						); ?>
					</div>
				</a>
			</div>
		</div>
		<?php if ($haveOffers) {
			foreach ($arResult['OFFERS'] as $keyOffer => $offer) {
				if (!isset($offer['MORE_PHOTO_COUNT']) || $offer['MORE_PHOTO_COUNT'] <= 0) {
					continue;
				}

				$strVisible = $arResult['OFFERS_SELECTED'] == $keyOffer ? '' : 'none'; ?>
				<div class="product-item-detail-slider-controls-block mt-2"
				     id="<?= $itemIds['SLIDER_CONT_OF_ID'] . $offer['ID'] ?>"
				     style="display: <?= $strVisible ?>;">
					<?php foreach ($offer['MORE_PHOTO'] as $keyPhoto => $photo) { ?>
						<div class="product-item-detail-slider-controls-image<?= ($keyPhoto == 0 ? ' active' : '') ?>"
						     data-entity="slider-control"
						     data-value="<?= $offer['ID'] . '_' . $photo['ID'] ?>">
							<img src="<?= $photo['SRC'] ?>">
						</div>
					<?php } ?>
				</div>
				<?php
			}
		} else { ?>
			<div class="product-item-detail-slider-controls-block margin_block_element"
			     id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
				<?php if (!empty($actualItem['PICTURE']) && count($actualItem['PICTURE']) > 0) {
					foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
						<div class="product-item-detail-slider-controls-image<?= ($key == 0 ? ' active' : '') ?>"
						     data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
							<img src="<?= $photo['SRC'] ?>">
						</div>
						<?php
					}
				} ?>
			</div>
			<?php
		}
		?>
	</div>
</div>
<?php
$showOffersBlock = $haveOffers && !empty($arResult['OFFERS_PROP']);
$mainBlockProperties = array_intersect_key($arResult['DISPLAY_PROPERTIES'], $arParams['MAIN_BLOCK_PROPERTY_CODE']);
$showPropsBlock = !empty($mainBlockProperties) || $arResult['SHOW_OFFERS_PROPS'];
$showBlockWithOffersAndProps = $showOffersBlock || $showPropsBlock; ?>
<div
	class="col-md-5 col-sm-6 col-lg-6 col-12 mt-lg-0 mt-md-0 mt-4 d-flex flex-column product_right catalog-item-product justify-content-between">
	<h1 class="head-title"><?= $name ?></h1>
	<?php if ($isGift) { ?>
		<div>
			<h4 class="bx-title">Данная продукция не продается отдельно</h4>
		</div>
		<?php
	} else { ?>
		<p class="text_prev mb-4"><?= $arResult['PREVIEW_TEXT'] ?></p>
		<div class="d-flex flex-lg-column flex-md-column flex-column-reverse">
		<?php
		$height = 10;
		$strong = 0;
		if (isset($arResult['PROPERTIES'][PROP_STRONG_CODE]) && !empty($arResult['PROPERTIES'][PROP_STRONG_CODE]['VALUE'])) {
			switch ($arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE_SORT']) {
				case "1":
					$strong = 0.5;
					$color = "#07AB66";
					break;
				case "2":
					$strong = 1.5;
					$color = "#FFC700";
					break;
				case "3":
					$strong = 2.5;
					$color = "#FF7A00";
					break;
			} ?>
			<div style="color: <?= $color ?>" class="column mt-lg-3 mt-md-3 mt-0 mb-4">
				<p class="condensation_text">
					Крепость: <?= $arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE'] ?> </p>
				<div class="d-flex flex-row">
					<?php for ($i = 0; $i < 3; $i++) { ?>
						<div
							style="border-color: <?= $color ?>; <?= ($strong - $i) >= 1 ? "background-color: $color" : ''; ?>"
							class="condensation">
							<?php if ($strong - $i == 0.5) { ?>
							<svg style="position: absolute; left: -5px; top: -1px" width="42"
							     height="<?= $height ?>" xmlns="http://www.w3.org/2000/svg">
								<path d="
                                        M 20 <?= $height ?>
                                        L 10 <?= $height ?>
                                        Q 0 <?= $height / 2 ?> 10 0
                                        L 30 0" stroke="<?= $color ?>" fill="<?= $color ?>"
								/>
								<?php } ?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		<?php }
		foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName) {
			switch ($blockName) {
				case 'quantityLimit':
					if ($show_price) {
						$arParams['SHOW_MAX_QUANTITY'] = 'N';
						if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
							if ($haveOffers) { ?>
								<div class="mb-3" id="<?= $itemIds['QUANTITY_LIMIT'] ?>"
								     style="display: none;">
									<span class="product-item-quantity" data-entity="quantity-limit-value"></span>
								</div>
							<?php } else {
								if ($measureRatio && (float)$actualItem['PRODUCT']['QUANTITY'] > 0
									&& $actualItem['CHECK_QUANTITY']) { ?>
									<div class="mb-3 text-center"
									     id="<?= $itemIds['QUANTITY_LIMIT'] ?>">
										<span
											class="product-item-detail-info-container-title"><?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:</span>
										<span class="product-item-quantity"
										      data-entity="quantity-limit-value">
                                                <?php if ($arParams['SHOW_MAX_QUANTITY'] === 'M') {
	                                                if ((float)$actualItem['PRODUCT']['QUANTITY'] / $measureRatio >= $arParams['RELATIVE_QUANTITY_FACTOR']) {
		                                                echo $arParams['MESS_RELATIVE_QUANTITY_MANY'];
	                                                } else {
		                                                echo $arParams['MESS_RELATIVE_QUANTITY_FEW'];
	                                                }
                                                } else {
	                                                echo $actualItem['PRODUCT']['QUANTITY'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'];
                                                } ?>
                                        </span>
									</div>
									<?php
								}
							}
						}
					}
					break;
				case 'quantity':
						$show = false;
						$active = null;
						$priceDef = 0;
						foreach ($arResult['OFFERS'] as $keys => $quantityNull) {
							if ($quantityNull['CATALOG_QUANTITY'] > 0 && $show === false) {
								$show = true;
							}
							if ($active == null && (int)$quantityNull['CATALOG_QUANTITY'] > 0) {
								$active = $keys;
								$priceDef = $quantityNull['PRICES_CUSTOM'][1]['PRICE'];
							}
						}?>
						<div>
							<div class="bx_price position-relative font-weight-bolder font-22 mb-3">
								<?php $sale = false;
								if (USE_CUSTOM_SALE_PRICE && !empty($arResult['OFFERS'][$active]['PRICES_CUSTOM']['SALE_PRICE']['PRICE']) ||
									$useDiscount['VALUE_XML_ID'] == 'true' && !empty($price['SALE_PRICE']['PRICE'])) {
									echo(round($price['SALE_PRICE']['PRICE']));
									$sale = true;
								} else {
									echo '<span class="font-12 card-price-text">от </span> ' . (round($arResult['OFFERS'][$active]['PRICES_CUSTOM'][1]['PRICE']));
								} ?> руб.
							</div>
							<div class="mt-1">
								<div class="prices-all mb-3">
									<?php $prod_off_id = $actualItem['ID'];
									$prod_off_quantity = 0;
									foreach ($arResult['OFFERS'] as $key_offer => $offer_price) { ?>
										<?php $dNone = 'd-none';
										$active_box = 'false';
										$basketItem = 0;
										if (!empty($item['ACTUAL_BASKET'][$offer['ID']])) {
											$basketItem = $item['ACTUAL_BASKET'][$offer['ID']];
										}
										if ($active === $key_offer && (int)$offer['CATALOG_QUANTITY'] > 0) {
											$active_box = 'true';
											$prod_off_id = $offer_price['ID'];
											$quantity_basket_default = $basketItem;
											$prod_off_quantity = $offer_price['CATALOG_QUANTITY'];
											$dNone = '';
										} ?>
										<div class="<?= $dNone ?> mb-2 box-prices p-3 width-fit-content br-10 bg-gray-white"
										     data-offer-id="<?= $offer_price['ID'] ?>">
											<?php foreach ($offer_price['PRICES_CUSTOM'] as $price) { ?>
												<p class="mb-1">
													<span class="font-14 mb-2"><?= $price['NAME'] ?></span>
													<span class="dash"> - </span>
													<span class="font-14"><b><?= $price['PRINT_PRICE'] ?></b></span>
												</p>
											<?php } ?>
										</div>
									<?php } ?>
								</div>
								<div
									class="d-flex row-line-reverse justify-content-between align-items-center box-basket mb-3 bx_catalog_item_controls">
									<?php if ($USER->IsAuthorized()) { ?>
										<div class="product-item-amount-field-contain-wrap" style="display:flex;"
										     data-product_id="<?= $prod_off_id; ?>">
											<div
												class="product-item-amount-field-contain d-flex flex-row align-items-center">
												<a class="btn-minus  minus_icon no-select add2basket"
												   href="javascript:void(0)"
												   data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
												   data-product_id="<?= $prod_off_id; ?>">
												</a>
												<div class="product-item-amount-field-block">
													<input class="product-item-amount card_element"
													       type="text"
													       value="<?= $quantity_basket_default ?>">
												</div>
												<a class="btn-plus plus_icon no-select add2basket"
												   data-max-quantity="<?= $prod_off_quantity ?>"
												   href="javascript:void(0)"
												   data-url="<?= $actualItem['DETAIL_PAGE_URL'] ?>"
												   data-product_id="<?= $prod_off_id; ?>"
												   title="Доступно <?= $prod_off_quantity ?> товар"></a>
											</div>
											<div class="alert_quantity" data-id="<?= $prod_off_id ?>"></div>
										</div>
									<?php } ?>
								</div>
							</div>
							<div class="d-flex flex-wrap flex-row mb-2 box-offers-auto">
								<?php
								$quantity_basket_default = 0;
								foreach ($arResult['OFFERS'] as $key => $offer) {
									if ((int)$offer['CATALOG_QUANTITY'] > 0) {

										$active_box = 'false';
										$basketItem = 0;
										if (!empty($actualItem['ACTUAL_BASKET'][$offer['ID']])) {
											$basketItem = $actualItem['ACTUAL_BASKET'][$offer['ID']];
										}
										if ($active === $key && (int)$offer['CATALOG_QUANTITY'] > 0) {
											$active_box = 'true';
											$quantity_basket_default = $basketItem;
										}

										$offer['NAME'] = htmlspecialcharsbx($offer['NAME']);
										foreach ($offer['PROPERTIES'] as $prop) {
											if (!empty($prop['VALUE']) && strripos($prop['CODE'], 'CML2_') === false) {
												$prop_value = $prop['VALUE'];
												if($prop['CODE'] === 'GRAMMOVKA_G'){
													$prop_value .= ' гр.';
												}
											}
										}
										?>
										<div class="red_button_cart font-14 width-fit-content mb-lg-2 m-md-2 m-1 offer-box cursor-pointer"
										     title="<?= $offer['NAME'] ?>"
										     data-active="<?= $active_box ?>"
										     data-product_id="<?= $offer['ID'] ?>"
										     data-product-quantity="<?= $offer['CATALOG_QUANTITY'] ?>"
										     data-basket-quantity="<?= $basketItem ?>"
										     data-price-base="<?= $offer['PRICES_CUSTOM'][1]['PRINT_PRICE'] ?>"
										     data-treevalue="<?= $offer['ID'] ?>_<?= $offer['ID'] ?>"
										     data-onevalue="<?= $offer['ID'] ?>">
											<?= $prop_value ?? '0' ?>
										</div>
									<?php }
								} ?>
							</div>
						</div>
					<?php
					break;
			}
		} ?>
		<div class="new_box d-flex flex-row align-items-center mb-lg-0 mb-md-0 mb-5">
			<span></span>
			<p>Наличие товара, варианты и стоимость доставки будут указаны далее при оформлении заказа. </p>
		</div>
		<?php if ($actualItem['PRODUCT']['QUANTITY'] != '0') { ?></div><?php } ?>
	<?php } ?>
</div>