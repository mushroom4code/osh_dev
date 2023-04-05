<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use Enterego\EnteregoHelper;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $item
 * @var array $actualItem
 * @var array $minOffer
 * @var array $itemIds
 * @var array $price
 * @var array $measureRatio
 * @var bool $haveOffers
 * @var bool $showSubscribe
 * @var array $morePhoto
 * @var bool $showSlider
 * @var bool $itemHasDetailUrl
 * @var string $imgTitle
 * @var string $productTitle
 * @var string $buttonSizeClass
 * @var CatalogSectionComponent $component
 */
$mainId = $this->GetEditAreaId($item['ID']);

$arItemIDs = array(
	'ID' => $mainId,
	'DISCOUNT_PERCENT_ID' => $mainId . '_dsc_pict',
	'STICKER_ID' => $mainId . '_sticker',
	'BIG_SLIDER_ID' => $mainId . '_big_slider',
	'BIG_IMG_CONT_ID' => $mainId . '_bigimg_cont',
	'SLIDER_CONT_ID' => $mainId . '_slider_cont',
	'OLD_PRICE_ID' => $mainId . '_old_price',
	'PRICE_ID' => $mainId . '_price',
	'DISCOUNT_PRICE_ID' => $mainId . '_price_discount',
	'PRICE_TOTAL' => $mainId . '_price_total',
	'SLIDER_CONT_OF_ID' => $mainId . '_slider_cont_',
	'QUANTITY_ID' => $mainId . '_quantity',
	'QUANTITY_DOWN_ID' => $mainId . '_quant_down',
	'QUANTITY_UP_ID' => $mainId . '_quant_up',
	'QUANTITY_MEASURE' => $mainId . '_quant_measure',
	'QUANTITY_LIMIT' => $mainId . '_quant_limit',
	'BUY_LINK' => $mainId . '_buy_link',
	'ADD_BASKET_LINK' => $mainId . '_add_basket_link',
	'BASKET_ACTIONS_ID' => $mainId . '_basket_actions',
	'NOT_AVAILABLE_MESS' => $mainId . '_not_avail',
	'COMPARE_LINK' => $mainId . '_compare_link',
	'TREE_ID' => $mainId . '_skudiv',
	'DISPLAY_PROP_DIV' => $mainId . '_sku_prop',
	'DISPLAY_MAIN_PROP_DIV' => $mainId . '_main_sku_prop',
	'OFFER_GROUP' => $mainId . '_set_group_',
	'BASKET_PROP_DIV' => $mainId . '_basket_prop',
	'SUBSCRIBE_LINK' => $mainId . '_subscribe',
	'TABS_ID' => $mainId . '_tabs',
	'TAB_CONTAINERS_ID' => $mainId . '_tab_containers',
	'SMALL_CARD_PANEL_ID' => $mainId . '_small_card_panel',
	'TABS_PANEL_ID' => $mainId . '_tabs_panel'
);
$favorite = '';
$styleForTaste = '';
$taste = $item['PROPERTIES'][PROPERTY_KEY_VKUS];
$codeProp = $item['PROPERTIES']['CML2_TRAITS'];
$useDiscount = $item['PROPERTIES']['USE_DISCOUNT'];
$newProduct = $item['PROPERTIES'][PROP_NEW];
$hitProduct = $item['PROPERTIES'][PROP_HIT];
$rowResHidePrice = $item['PROPERTIES']['SEE_PRODUCT_AUTH']['VALUE'];

$show_price = true;
$priceBasket = 0;
$styleForNo = $href = $not_auth = $styleForTaste = '';
$productTitle = str_replace("\xC2\xA0", " ", $productTitle);
$jsonForModal = [];

if ($rowResHidePrice == 'Нет' && !$USER->IsAuthorized()) {
	$show_price = false;
	$not_auth = 'link_header_box';
}


if ($item['PRODUCT']['QUANTITY'] == '0') {
	$styleForNo = 'not_av';
}

foreach ($item['ACTUAL_BASKET'] as $key => $val) {
	if ($key == $item['ID']) {
		$priceBasket = $val;
	}
}

if (!$show_price) {
	$href = $item['DETAIL_PAGE_URL'];
	$item['DETAIL_PAGE_URL'] = 'javascript:void(0)';
}

$subscription_item_ids = array_column($arResult["CURRENT_USER_SUBSCRIPTIONS"]["SUBSCRIPTIONS"], 'ITEM_ID');
$found_key = array_search((string)$item['ID'], $subscription_item_ids);
$is_key_found = (isset($found_key) && ($found_key !== false)) ? true : false;

if (empty($morePhoto[0])) {
	$morePhoto[0]['SRC'] = '/local/templates/Oshisha/images/no-photo.gif';
}

$prop_see_in_window = [];
foreach ($item['PROPERTIES'] as $key => $props_val) {
	if ($item['POPUP_PROPS'][$key]['SEE_POPUP_WINDOW'] == 'Y' && !empty($props_val['VALUE'])) {
		$prop_see_in_window[] = $props_val;
	}
}

$boolShow = false;
$active = null;
$priceDef = 0;
$offersForModal = [];
foreach ($item['OFFERS'] as $keys => $quantityNull) {
	if ($quantityNull['CATALOG_QUANTITY'] > 0 && $boolShow === false) {
		$boolShow = true;
	}
	if ($active == null && (int)$quantityNull['CATALOG_QUANTITY'] > 0) {
		$active = $keys;
		$priceDef = $quantityNull['PRICES_CUSTOM'][1]['PRICE'];
	}

	$offersForModal[$quantityNull['ID']] = [
		'ID' => $quantityNull['ID'],
		'PRICE' => $quantityNull['PRICES_CUSTOM'],
		'SALE_PRICE' => '',
		'DETAIL_PICTURE' => $quantityNull['DETAIL_PICTURE']['SRC'],
		'QUANTITY'=> $quantityNull['CATALOG_QUANTITY'],
		'PROPS'=> $quantityNull['PROPERTIES'],
 	];
}

if ($show_price) {
	$jsonForModal = [
		'ID' => $item['ID'],
		'BUY_LINK' => $arItemIDs['BUY_LINK'],
		'TYPE_PRODUCT' => 'OFFERS',
		'OFFERS' => $offersForModal,
		'QUANTITY_ID' => $arItemIDs['QUANTITY_ID'],
		'PRODUCT' => $item['PRODUCT'],
		'USE_DISCOUNT' => $useDiscount['VALUE'],
		'POPUP_PROPS' => $prop_see_in_window ?? 0,
		'NAME' => $productTitle,
		'LIKE' => [
			'ID_PROD' => $item['ID_PROD'],
			'F_USER_ID' => $item['F_USER_ID'],
			'COUNT_LIKE' => $item['COUNT_LIKE'] ?? 0,
			'COUNT_LIKES' => $item['COUNT_LIKES'] ?? 0,
			'COUNT_FAV' => $item['COUNT_FAV'] ?? 0,
		],
		'USE_CUSTOM_SALE_PRICE' => USE_CUSTOM_SALE_PRICE,
		'BASE_PRICE' => BASIC_PRICE,
		'ADVANTAGES_PRODUCT' => $item['PROPERTIES']['ADVANTAGES_PRODUCT']['VALUE'] ?? []
	];
}

?>
<div class="catalog-item-product <?= ($item['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>
<?php if (!$show_price) { ?> blur_photo <?php } ?>">
	<input type="hidden" class="product-values" value="<?= htmlspecialchars(json_encode($jsonForModal)); ?>"/>
	<div class="bx_catalog_item_container product-item position-relative <?= $taste['VALUE'] ? 'is-taste' : '' ?>">
		<?php if (($newProduct['VALUE'] == 'Да') && ($hitProduct['VALUE'] != 'Да')) { ?>
			<span class="taste new-product" data-background="#F55F5C">NEW</span>
		<?php }

		if ($hitProduct['VALUE'] === 'Да') { ?>
			<span class="taste new-product" style="padding: 8px 6px;" data-background="#F55F5C">ХИТ</span>
		<?php }

		$showToggler = false; // по умолчанию стрелки нет (случаи когда вкус 1)
		$togglerState = 'd-none';
		$listClass = '';

		if (count($taste['VALUE']) > 0) {
			if (count($taste['VALUE']) > 2) {
				$showToggler = true;
			} elseif (count($taste['VALUE']) > 1) {
				// поместятся на одной строке 2 вкуса или нет
				$showToggler = (mb_strlen($taste['VALUE'][0]) + mb_strlen($taste['VALUE'][1])) > 18;
			}
			$togglerState = $showToggler ? ' many-tastes' : ' d-none many-tastes';
			$listClass = $showToggler ? ' js__tastes-list' : '';
		} ?>

		<div class="item-product-info d-flex flex-column justify-content-between height-100">
			<div class="toggle_taste card-price <?= $taste['VALUE'] ? 'js__tastes' : '' ?>">
				<div class="variation_taste <?= $showToggler ? '' : 'show_padding' ?> <?= $listClass ?>">

					<?php foreach ($taste['VALUE'] as $key => $name) {
						foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
							if ($key === $keys) {
								$color = explode('#', $value);
								$tasteSize = 'taste-small';

								if (4 < mb_strlen($name) && mb_strlen($name) <= 8) {
									$tasteSize = 'taste-normal';
								} elseif (8 < mb_strlen($name) && mb_strlen($name) <= 13) {
									$tasteSize = 'taste-long';
								} elseif (mb_strlen($name) > 13) {
									$tasteSize = 'taste-xxl';
								}
								?>
								<span class="taste <?= $tasteSize ?>" data-background="<?= '#' . $color[1] ?>"
								      id="<?= $color[0] ?>"><?= $name ?> </span>
							<?php }
						}
					} ?>

				</div>
				<div class="variation_taste_toggle <?= $togglerState ?> js__taste_toggle"></div>
			</div>
			<div class="bx_catalog_item_overlay"></div>
			<div class="image_cart position-relative <?= $not_auth ?>" data-href="<?= $href ?>">
				<a class=" <?= $styleForTaste ?>"
				   href="<?= $item['DETAIL_PAGE_URL']; ?>">
					<?php if (!empty($item['PREVIEW_PICTURE']['SRC'])) { ?>
						<img src="<?= $item['PREVIEW_PICTURE']['SRC']; ?>" alt="<?= $productTitle ?>"/>
					<?php } else { ?>
						<img src="/local/templates/Oshisha/images/no-photo.gif" alt="no photo"/>
					<?php } ?>
				</a>
				<i class="open-fast-window" data-item-id="<?= $item['ID'] ?>"></i>
			</div>
			<?php if ($price['PRICE_DATA'][1]['PRICE'] !== '') { ?>
				<div class="bx_catalog_item_price mt-2 mb-2 d-flex  justify-content-end">
					<div class="all-prices-by-line">
						<div class="d-flex flex-column prices-block">
							<?php foreach ($price['PRICE_DATA'] as $items) { ?>
								<p class="price-row mb-1">
									<span class="font-11 font-10-md mb-2"><?= $items['NAME'] ?></span>
									<span class="dash"> - </span><br>
									<span class="font-12 font-11-md"><b><?= $items['PRINT_PRICE'] ?></b></span>
								</p>
							<?php } ?>
						</div>
					</div>

					<div class="box_with_price line-price font_weight_600 d-flex flex-column min-height-auto">
						<div class="d-flex flex-column">
							<div class="bx_price <?= $styleForNo ?> position-relative">
								<?php $sale = false;
								if (USE_CUSTOM_SALE_PRICE && !empty($price['SALE_PRICE']['PRICE']) ||
									$useDiscount['VALUE_XML_ID'] == 'true' && !empty($price['SALE_PRICE']['PRICE'])) {
									echo(round($price['SALE_PRICE']['PRICE']));
									$sale = true;
								} else {
									echo '<span class="font-10 card-price-text">от </span> ' . (round($priceDef));
								} ?>₽
							</div>

							<?php if (USE_CUSTOM_SALE_PRICE && !empty($price['SALE_PRICE']['PRICE']) ||
								$useDiscount['VALUE_XML_ID'] == 'true' && !empty($price['SALE_PRICE']['PRICE'])) { ?>
								<div class="font-10 d-lg-block d-mb-block d-flex flex-wrap align-items-center">
									<b class="decoration-color-red mr-2"><?= $price['PRICE_DATA'][0]['PRICE'] ?>₽</b>
									<b class="sale-percent">
										- <?= (round($price['PRICE_DATA'][0]['PRICE']) - round($price['SALE_PRICE']['PRICE'])) ?>
										₽
									</b>
								</div>
							<?php } ?>
						</div>
					</div>

					<div class="box_with_titles">
						<?php
						$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
							'templates',
							[
								'ID_PROD' => $item['ID_PROD'],
								'F_USER_ID' => $item['F_USER_ID'],
								'LOOK_LIKE' => false,
								'LOOK_FAVORITE' => true,
								'COUNT_LIKE' => $item['COUNT_LIKE'],
								'COUNT_FAV' => $item['COUNT_FAV'],
								'COUNT_LIKES' => $item['COUNT_LIKES'],
							],
							$component,
							[
								'HIDE_ICONS' => 'Y'
							]
						);
						$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
							'templates',
							array(
								'ID_PROD' => $item['ID_PROD'],
								'F_USER_ID' => $item['F_USER_ID'],
								'LOOK_LIKE' => true,
								'LOOK_FAVORITE' => false,
								'COUNT_LIKE' => $item['COUNT_LIKE'],
								'COUNT_FAV' => $item['COUNT_FAV'],
								'COUNT_LIKES' => $item['COUNT_LIKES'],
							),
							$component,
							array('HIDE_ICONS' => 'Y'),
						);
						?>
					</div>
				</div>
			<?php } else { ?>
				<div class="all-prices-by-line">
					<div class="d-flex flex-column prices-block">
						<?php foreach ($price['PRICE_DATA'] as $items) { ?>
							<p class="price-row mb-1">
								<span class="font-11 font-10-md mb-2"><?= $items['NAME'] ?></span>
								<span class="dash"> - </span><br>
								<span class="font-12 font-11-md"><b><?= $items['PRINT_PRICE'] ?></b></span>
							</p>
						<?php } ?>
					</div>
				</div>
				<div class="box_with_titles">
					<div class="not_product">
						Товара нет в наличии
					</div>
					<?php
					$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
						'templates',
						array(
							'ID_PROD' => $item['ID_PROD'],
							'F_USER_ID' => $item['F_USER_ID'],
							'LOOK_LIKE' => false,
							'LOOK_FAVORITE' => true,
							'COUNT_LIKE' => $item['COUNT_LIKE'],
							'COUNT_FAV' => $item['COUNT_FAV'],
							'COUNT_LIKES' => $item['COUNT_LIKES'],
						)
						,
						$component,
						array('HIDE_ICONS' => 'Y')
					);
					$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
						'templates',
						array(
							'ID' => $item['ID_PROD'],
							'F_USER_ID' => $item['F_USER_ID'],
							'LOOK_LIKE' => true,
							'LOOK_FAVORITE' => false,
							'COUNT_LIKE' => $item['COUNT_LIKE'],
							'COUNT_FAV' => $item['COUNT_FAV'],
							'COUNT_LIKES' => $item['COUNT_LIKES'],
						),
						$component,
						array('HIDE_ICONS' => 'Y')
					); ?>
				</div>
			<?php } ?>
			<div class="box_with_title_like d-flex align-items-center">
				<?php if (count($taste['VALUE']) > 0) { ?>
					<div class="toggle_taste_line">
						<div class="variation_taste">
							<?php foreach ($taste['VALUE'] as $key => $name) {
								foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
									if ($key === $keys) {
										$color = explode('#', $value); ?>
										<span class="taste" data-background="<?= '#' . $color[1] ?>"
										      id="<?= $color[0] ?>">
                                        <?= $name ?>
                                </span>
									<?php }
								}
							} ?>
						</div>
					</div>
				<?php } ?>

				<?php if ($GLOBALS['UserTypeOpt'] === true) { ?>
					<div class="codeProduct font-10 mr-4">
						<?php
						foreach ($codeProp['DESCRIPTION'] as $key => $code) {
							if ($code === 'Код') {
								echo $codeProp['VALUE'][$key];
							}
						} ?>
					</div>
				<?php }
				?>
				<div class="box_with_text">
					<a class="bx_catalog_item_title <?= $not_auth ?>"
					   href="<?= $item['DETAIL_PAGE_URL']; ?>"
					   data-href="<?= $href ?>"
					   title="<?= $productTitle; ?>">
						<?= $productTitle; ?>
					</a>
					<?php
					if (!empty($item['DETAIL_TEXT'])) { ?>
						<p class="detail-text"><?= $item['DETAIL_TEXT'] ?></p>
					<?php } ?>
				</div>
			</div>
			<?php
			$showSubscribeBtn = false;
			$compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCT_TPL_MESS_BTN_COMPARE')); ?>
			<div class="bx_catalog_item_controls">
				<?php if (!empty($item['OFFERS']) && $boolShow) { ?>
					<div class="box_with_fav_bask align-items-lg-center align-items-md-center align-items-end">
						<?php if ($priceDef) { ?>
							<div class="box_with_price card-price font_weight_600 d-flex flex-column min-height-auto">
								<div class="d-flex flex-column">
									<div class="bx_price position-relative">
										<?php $sale = false;
										if (USE_CUSTOM_SALE_PRICE && !empty($item['OFFERS'][0]['PRICES_CUSTOM']['SALE_PRICE']['PRICE']) ||
											$useDiscount['VALUE_XML_ID'] == 'true' && !empty($price['SALE_PRICE']['PRICE'])) {
											echo(round($price['SALE_PRICE']['PRICE']));
											$sale = true;
										} else {
											echo '<span class="font-10 card-price-text">от </span> ' . (round($priceDef));
										} ?>₽
									</div>
									<?php if (USE_CUSTOM_SALE_PRICE && !empty($price['SALE_PRICE']['PRICE']) ||
										$useDiscount['VALUE_XML_ID'] == 'true' && !empty($price['SALE_PRICE']['PRICE'])) { ?>
										<div class="font-10 d-lg-block d-mb-block d-flex flex-wrap align-items-center">
											<b class="decoration-color-red mr-2"><?= $price['PRICE_DATA'][0]['PRICE'] ?>
												₽</b>
											<b class="sale-percent">
												- <?= (round($price['PRICE_DATA'][0]['PRICE']) - round($price['SALE_PRICE']['PRICE'])) ?>
												₽
											</b>
										</div>
									<?php } ?>
								</div>
							</div>
							<? if ($arResult['IS_SUBSCRIPTION_PAGE'] == 'Y'): ?>
								<div class="detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                            <?= $is_key_found ? 'subscribed' : '' ?> min_card">
									<i class="fa fa-bell-o <?= $is_key_found ? 'filled' : '' ?>"
									   aria-hidden="true"></i>
								</div>
								<div id="popup_mess"
								     class="catalog_popup<?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                             <?= $is_key_found ? 'subscribed' : '' ?>"
								     data-subscription_id="<?= $is_key_found ? $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][$found_key]['ID'] : '' ?>"
								     data-product_id="<?= $item['ID']; ?>">
								</div>
							<?php else:
								if ($USER->IsAuthorized()) { ?>
									<div class="d-flex row-line-reverse justify-content-between box-basket">
										<div class="btn red_button_cart js__show-block">
											<img class="image-cart"
											     src="/local/templates/Oshisha/images/cart-white.png"/>
										</div>
									</div>
								<? } endif;
						}
						if (!$USER->IsAuthorized() && !$show_price) { ?>
							<div class="btn-plus js__show-block"
							     data-href="<?= $href ?>">
								<span class="btn red_button_cart d-lg-block d-md-block d-none">Подробнее</span>
								<i class="fa fa-question d-lg-none d-md-none d-block red_button_cart font-16 p-4-8"
								   aria-hidden="true"></i>
							</div>
						<?php } ?>
					</div>
					<div style="clear: both;"></div>
				<?php } else { ?>
					<div id="<?= $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="not_avail">
						<div class="box_with_fav_bask">
							<div class="not_product detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                    <?= $is_key_found ? 'subscribed' : '' ?>">
								Нет в наличии
							</div>
							<div class="detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                    <?= $is_key_found ? 'subscribed' : '' ?> min_card">
								<i class="fa fa-bell-o <?= $is_key_found ? 'filled' : '' ?>" aria-hidden="true"></i>
							</div>
						</div>
						<div style="clear: both;"></div>
						<div id="popup_mess" class="catalog_popup<?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                         <?= $is_key_found ? 'subscribed' : '' ?>"
						     data-subscription_id="<?= $is_key_found ? $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][$found_key]['ID'] : '' ?>"
						     data-product_id="<?= $item['ID']; ?>">
						</div>
					</div>
				<?php } ?>

				<div class="box_with_titles line-view">
					<?php
					$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
						'templates',
						[
							'ID_PROD' => $item['ID_PROD'],
							'F_USER_ID' => $item['F_USER_ID'],
							'LOOK_LIKE' => false,
							'LOOK_FAVORITE' => true,
							'COUNT_LIKE' => $item['COUNT_LIKE'],
							'COUNT_FAV' => $item['COUNT_FAV'],
							'COUNT_LIKES' => $item['COUNT_LIKES'],
						],
						$component,
						[
							'HIDE_ICONS' => 'Y'
						]
					);
					$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
						'templates',
						array(
							'ID_PROD' => $item['ID_PROD'],
							'F_USER_ID' => $item['F_USER_ID'],
							'LOOK_LIKE' => true,
							'LOOK_FAVORITE' => false,
							'COUNT_LIKE' => $item['COUNT_LIKE'],
							'COUNT_FAV' => $item['COUNT_FAV'],
							'COUNT_LIKES' => $item['COUNT_LIKES'],
						),
						$component,
						array('HIDE_ICONS' => 'Y'),
					);
					?>
				</div>
			</div>
		</div>
		<div
			class="info-prices-box-hover info-prices-box-bottom box-offers cursor-pointer ml-2 d-none bg-white p-2 justify-content-between flex-column">
			<?php if (!empty($item['OFFERS'])) { ?>
				<div>
					<p class="p-0 mb-1 close-box-price cursor-pointer text-right" title="Закрыть">
						<svg width="15" height="15" viewBox="0 0 9 8" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 7.5L8 0.5M1 0.5L8 7.5" stroke="#565656" stroke-linecap="round"
							      stroke-linejoin="round"></path>
						</svg>
					</p>
					<div class="d-flex flex-wrap flex-row mb-2 justify-content-end box-offers-auto">
						<?php
						$quantity_basket_default = 0;
						foreach ($item['OFFERS'] as $key => $offer) {
							if ((int)$offer['CATALOG_QUANTITY'] > 0) {

								$active_box = 'false';
								$basketItem = 0;
								if (!empty($item['ACTUAL_BASKET'][$offer['ID']])) {
									$basketItem = $item['ACTUAL_BASKET'][$offer['ID']];
								}
								if ($active === $key && (int)$offer['CATALOG_QUANTITY'] > 0) {
									$active_box = 'true';
									$quantity_basket_default = $basketItem;
								}

								$offer['NAME'] = htmlspecialcharsbx($offer['NAME']);
								foreach ($offer['PROPERTIES'] as $prop) {
									if (!empty($prop['VALUE']) && strripos($prop['CODE'], 'CML2_') === false) {
										$prop_value = $prop['VALUE'];
										$typeProp = $prop['CODE'];
										if ($prop['CODE'] === 'GRAMMOVKA_G') {
											$prop_value .= ' гр.';
										}
									}
								}
								if ($typeProp === 'GRAMMOVKA_G') { ?>
									<div class="red_button_cart width-fit-content mb-lg-2 m-md-2 m-1 offer-box "
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
							}
						} ?>
					</div>
				</div>
				<div class="mt-1">
					<div class="prices-all mb-3">
						<?php $prod_off_id = $item['ID'];
						$prod_off_quantity = 0;
						foreach ($item['OFFERS'] as $key_offer => $offer_price) { ?>
							<?php $dNone = 'd-none';
							if ($key_offer == $active) {
								$prod_off_id = $offer_price['ID'];
								$prod_off_quantity = $offer_price['CATALOG_QUANTITY'];
								$dNone = '';
							} ?>
							<div class="<?= $dNone ?> mb-lg-2 m-md-2 m-1 box-prices"
							     data-offer-id="<?= $offer_price['ID'] ?>">
								<?php foreach ($offer_price['PRICES_CUSTOM'] as $prices) { ?>
									<p class="mb-1">
										<span class="font-11 font-10-md mb-2"><?= $prices['NAME'] ?></span>
										<span class="dash"> - </span>
										<span class="font-12 font-11-md"><b><?= $prices['PRINT_PRICE'] ?></b></span>
									</p>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
					<div
						class="d-flex row-line-reverse justify-content-between align-items-center box-basket mb-lg-2 m-md-2 m-1 bx_catalog_item_controls">
						<div class="bx_price position-relative font-weight-bolder">
							<?php $sale = false;
							if (USE_CUSTOM_SALE_PRICE && !empty($item['OFFERS'][$active]['PRICES_CUSTOM']['SALE_PRICE']['PRICE']) ||
								$useDiscount['VALUE_XML_ID'] == 'true' && !empty($price['SALE_PRICE']['PRICE'])) {
								echo(round($price['SALE_PRICE']['PRICE']));
								$sale = true;
							} else {
								echo '<span class="font-10 card-price-text">от </span> ' . (round($item['OFFERS'][$active]['PRICES_CUSTOM'][1]['PRICE']));
							} ?>₽
						</div>
						<?php if ($USER->IsAuthorized()) { ?>
							<div class="product-item-amount-field-contain-wrap" style="display:flex;"
							     data-product_id="<?= $prod_off_id; ?>">
								<div class="product-item-amount-field-contain d-flex flex-row align-items-center">
									<a class="btn-minus  minus_icon no-select add2basket"
									   href="javascript:void(0)" data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
									   data-product_id="<?= $prod_off_id; ?>">
									</a>
									<div class="product-item-amount-field-block">
										<input class="product-item-amount card_element"
										       type="text"
										       value="<?= $quantity_basket_default ?>">
									</div>
									<a class="btn-plus plus_icon no-select add2basket"
									   data-max-quantity="<?= $prod_off_quantity ?>" href="javascript:void(0)"
									   data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
									   data-product_id="<?= $prod_off_id; ?>"
									   title="Доступно <?= $prod_off_quantity ?> товар"></a>
								</div>
								<div class="alert_quantity" data-id="<?= $prod_off_id ?>"></div>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
		$emptyProductProperties = empty($item['PRODUCT_PROPERTIES']);
		if ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET'] && !$emptyProductProperties) { ?>
			<div id="<?= $arItemIDs['BASKET_PROP_DIV']; ?>" style="display: none;">
				<?php
				if (!empty($item['PRODUCT_PROPERTIES_FILL'])) {
					foreach ($item['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo) {
						?>
						<input type="hidden"
						       name="<?= $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<?= $propID; ?>]"
						       value="<?= htmlspecialcharsbx($propInfo['ID']); ?>">
						<?php if (isset($item['PRODUCT_PROPERTIES'][$propID])) {
							unset($item['PRODUCT_PROPERTIES'][$propID]);
						}
					}
				}
				$emptyProductProperties = empty($item['PRODUCT_PROPERTIES']); ?>
			</div>
			<?php

		} else {
			if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']) {
				$canBuy = $item['JS_OFFERS'][$item['OFFERS_SELECTED']]['CAN_BUY'];

				unset($canBuy);
			}
			$boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $item['OFFERS_PROPS_DISPLAY']);
			$boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
			if ($boolShowProductProps || $boolShowOfferProps) { ?>
				<div class="bx_catalog_item_articul">
					<?php if ($boolShowProductProps) {
						foreach ($item['DISPLAY_PROPERTIES'] as $arOneProp) {
							?><br><strong><?= $arOneProp['NAME']; ?></strong> <?
							echo(
							is_array($arOneProp['DISPLAY_VALUE'])
								? implode(' / ', $arOneProp['DISPLAY_VALUE'])
								: $arOneProp['DISPLAY_VALUE']
							);
						}
					}
					if ($boolShowOfferProps) { ?>
						<span id="<?= $arItemIDs['DISPLAY_PROP_DIV']; ?>"
						      style="display: none;"></span>
					<?php } ?>
				</div>
				<?php
			}
			if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']) {
				if (!empty($item['OFFERS_PROP'])) {
					$arSkuProps = array();
					if ($item['OFFERS_PROPS_DISPLAY']) {
						foreach ($item['JS_OFFERS'] as $keyOffer => $arJSOffer) {
							$strProps = '';
							if (!empty($arJSOffer['DISPLAY_PROPERTIES'])) {
								foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp) {
									$strProps .= '<br>' . $arOneProp['NAME'] . ' <strong>' . (
										is_array($arOneProp['VALUE'])
											? implode(' / ', $arOneProp['VALUE'])
											: $arOneProp['VALUE']
										) . '</strong>';
								}
							}
							$item['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
						}
					}
				}
			}
		}
		?>
	</div>
	<div id="result_box"></div>
</div>
