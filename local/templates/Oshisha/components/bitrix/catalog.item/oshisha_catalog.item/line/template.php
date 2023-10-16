<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

$specialPrice = 0;
if (!empty($price['USER_PRICE'])) {
    $specialPrice = $price['USER_PRICE']['PRICE'];
}

if (!empty($price['SALE_PRICE']['PRICE']) &&
    ($useDiscount['VALUE_XML_ID'] == 'true' || USE_CUSTOM_SALE_PRICE)) {

    $specialPrice = ($specialPrice === 0 || $price['SALE_PRICE']['PRICE'] < $specialPrice)
        ? $price['SALE_PRICE']['PRICE']
        : $specialPrice;
}

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

$subscription_item_ids = array_column($arResult["CURRENT_USER_SUBSCRIPTIONS"]["SUBSCRIPTIONS"] ?? [], 'ITEM_ID');
$found_key = array_search((string)$item['ID'], $subscription_item_ids);
$is_key_found = isset($found_key) && ($found_key !== false);

if (empty($morePhoto[0])) {
    $morePhoto[0]['SRC'] = '/local/templates/Oshisha/images/no-photo.gif';
}

$prop_see_in_window = [];
foreach ($item['PROPERTIES'] as $key => $props_val) {
    if ($item['POPUP_PROPS'][$key]['SEE_POPUP_WINDOW'] == 'Y' && !empty($props_val['VALUE'])) {
        $prop_see_in_window[] = $props_val;
    }
}

if (($newProduct['VALUE'] == 'Да') && ($hitProduct['VALUE'] != 'Да')) { ?>
    <span class="taste bg-greenLight dark:bg-greenButton text-white absolute -left-4 -top-3 py-2.5 px-1 rounded-full
     text-xs z-10 font-medium">NEW</span>
<?php }
if ($hitProduct['VALUE'] === 'Да') { ?>
    <span class="taste bg-yellowSt text-black absolute font-semibold -left-4 -top-3 py-2.5 px-1.5 rounded-full
    text-xs z-10">ХИТ</span>
<?php } ?>
<div class="catalog-item-product dark:bg-darkBox border dark:border-0 border-gray-product rounded-xl p-4 h-full relative
<?= ($item['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>
<?php if (!$show_price) { ?> blur_photo <?php } ?>" data-product_id="<?= $item['ID'] ?>">
    <div class="bx_catalog_item_container product-item position-relative h-full">
        <div class="item-product-info h-full flex flex-row justify-between">
            <div>
                <?php if ($price['PRICE_DATA']['PRICE'] !== '') { ?>
                    <div class="bx_catalog_item_price mt-2 mb-2">
                        <div class="box_with_titles flex flex-row text-xs text-textLight justify-between dark:text-textDarkLightGray">
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
                    <div class="box_with_titles flex flex-row text-xs text-textLight dark:text-textDarkLightGray">
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
                <div class="box_with_title_like mb-3 d-flex align-items-center h-10">
                    <div class="box_with_text mb-3">
                        <a class="bx_catalog_item_title line-clamp-2 text-sm font-medium mb-2 text-textLight
                        dark:font-light dark:text-textDarkLightGray hover:text-hover-red
                        <?= $styleForNo . ' ' . $not_auth ?>"
                           href="<?= $item['DETAIL_PAGE_URL']; ?>"
                           data-href="<?= $href ?>"
                           title="<?= $productTitle; ?>">
                            <?= $productTitle; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
            $showSubscribeBtn = false;
            $compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCT_TPL_MESS_BTN_COMPARE')); ?>
            <div class="bx_catalog_item_controls mr-2 ml-2">
                <?php if ($price['PRICE_DATA']['PRICE'] !== '0' && $item['PRODUCT']['QUANTITY'] !== '0') { ?>
                    <div class="box_with_fav_bask flex flex-row justify-between items-center">
                        <?php if ($price['PRICE_DATA']['PRICE'] !== '') { ?>
                            <div class="box_with_price card-price font_weight_600  min-height-auto">
                                <div class="flex flex-row">
                                    <div class="bx_price text-xl font-semibold dark:font-medium mr-2 <?= $styleForNo ?> position-relative">
                                        <?php
                                        if (!empty($specialPrice)) {
                                            echo(round($specialPrice));
                                        } else {
                                            echo(round($price['PRICE_DATA']['PRICE']));
                                        } ?>₽
                                    </div>
                                    <?php if (!empty($specialPrice)) { ?>
                                        <div class="font-10 d-lg-block d-mb-block d-flex flex-wrap align-items-center">
                                            <span class="line-through font-light decoration-red text-textLight
                                             dark:text-grayIconLights mr-2">
                                                <?= $price['PRICE_DATA']['PRICE'] ?>₽</span>
                                            <span class="sale-percent text-light-red font-medium">
                                                - <?= (round($price['PRICE_DATA']['PRICE']) - round($specialPrice)) ?>₽
                                            </span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if ($arResult['IS_SUBSCRIPTION_PAGE'] == 'Y'): ?>
                                <div class="detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                            <?= $is_key_found ? 'subscribed' : '' ?> min_card">
                                    <i class="fa fa-bell-o <?= $is_key_found ? 'filled' : '' ?>" aria-hidden="true"></i>
                                </div>
                                <div id="popup_mess" class="catalog_popup<?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                             <?= $is_key_found ? 'subscribed' : '' ?>"
                                     data-subscription_id="<?= $is_key_found ? $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][$found_key]['ID'] : '' ?>"
                                     data-product_id="<?= $item['ID']; ?>">
                                </div>
                            <? else: ?>
                                <div class="d-flex row-line-reverse justify-content-between box-basket">
                                    <?php if ($show_price && $item['ADD_TO_BASKET']) { ?>
                                        <!--                                    <div class="btn red_button_cart btn-plus add2basket"-->
                                        <!--                                         data-url="--><?php //= $item['DETAIL_PAGE_URL'] ?><!--"-->
                                        <!--                                         data-product_id="--><?php //= $item['ID']; ?><!--"-->
                                        <!--                                         data-max-quantity="--><?php //= $item['PRODUCT']['QUANTITY'] ?><!--"-->
                                        <!--                                         id="--><?php //= $arItemIDs['BUY_LINK']; ?><!--"-->
                                        <!--                                         --><? // if ($priceBasket > 0): ?><!--style="display:none;"--><? // endif; ?>
                                        <!--                                    >-->
                                        <!--                                        <img class="image-cart" src="/local/templates/Oshisha/images/cart-white.png"/>-->
                                        <!--                                    </div>-->
                                        <div class="product-item-amount-field-contain-wrap relative"
                                             <?php if ($priceBasket > 0): ?>style="display:flex;"<?php endif; ?>
                                             data-product_id="<?= $item['ID']; ?>">
                                            <div class="product-item-amount-field-contain flex flex-row items-center">
                                                <a class="btn-minus no-select add2basket cursor-pointer"
                                                   id="<?= $arItemIDs['BUY_LINK']; ?>"
                                                   href="javascript:void(0)" data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                                   data-product_id="<?= $item['ID']; ?>">
                                                    <svg width="22" height="2" viewBox="0 0 22 2" fill="none"
                                                         class="stroke-dark dark:stroke-white stroke-[1.5px]"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1 1H21" stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                <div class="product-item-amount-field-block">
                                                    <input class="product-item-amount dark:bg-grayButton bg-textDarkLightGray
                                                focus:border-none text-center border-none text-sm
                                                 shadow-none py-2.5 px-3 mx-2 outline-none rounded-md w-14 card_element"
                                                           id="<?= $arItemIDs['QUANTITY_ID'] ?>"
                                                           type="number"
                                                           max="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                           value="<?= $priceBasket ?>">
                                                </div>
                                                <a class="btn-plus no-select add2basket cursor-pointer"
                                                   data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                   id="<?= $arItemIDs['BUY_LINK']; ?>" href="javascript:void(0)"
                                                   data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                                   data-product_id="<?= $item['ID']; ?>"
                                                   title="Доступно <?= $item['PRODUCT']['QUANTITY'] ?> товар">
                                                    <svg width="20" height="20" viewBox="0 0 20 20"
                                                         class="fill-light-red dark:fill-white"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M18.8889 11.111H1.11111C0.503704 11.111 0 10.6073 0 9.9999C0 9.3925 0.503704 8.88879 1.11111 8.88879H18.8889C19.4963 8.88879 20 9.3925 20 9.9999C20 10.6073 19.4963 11.111 18.8889 11.111Z"/>
                                                        <path d="M10 20C9.39262 20 8.88892 19.4963 8.88892 18.8889V1.11111C8.88892 0.503704 9.39262 0 10 0C10.6074 0 11.1111 0.503704 11.1111 1.11111V18.8889C11.1111 19.4963 10.6074 20 10 20Z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                            <div class="alert_quantity hidden absolute p-3 text-xs bg-filterGray
                                            dark:bg-dark-box w-full shadow-lg rounded-bl-xl"
                                                 data-id="<?= $item['ID'] ?>"></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <? endif; ?>
                        <?php }
                        if (!$USER->IsAuthorized() && !$show_price) { ?>
                            <div class="btn-plus <?= $not_auth ?>"
                                 data-href="<?= $href ?>">
                                <span class="btn red_button_cart text-xs dark:text-textDark text-white font-medium
                                dark:bg-dark-red bg-light-red py-2 px-4 rounded-5">Подробнее</span>
                            </div>
                        <?php } else if (!$item['ADD_TO_BASKET']) { ?>
                            <div>
                                <span class="btn red_button_cart text-xs dark:text-textDark text-white font-medium
                                dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 open-popup"
                                      onclick="showHidePopupPrice(this)">Подробнее</span>
                                <div class="text-black font-light hidden absolute p-5 shadow-lg popup-window-price
                                bg-filterGray dark:text-textDarkLightGray text-xs dark:bg-grayButton rounded-lg w-72
                                 z-20 left-0">
                                    У вас нет активных контрагентов для совершения покупок на этом сайте!<br>
                                    Вы можете
                                    <a href="/personal/contragents/"
                                       class="text-light-red dark:text-white font-semibold">Создать контрагента</a>
                                    и обратиться к менеджеру <br>
                                    или перейти на наш <a href="https://oshisha.net"
                                                          class="text-light-red dark:text-white font-semibold">Розничный
                                        сайт</a>
                                </div>
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
            </div>
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
                        <?php if (isset($item['PRODUCT_PROPERTIES'][$propID]))
                            unset($item['PRODUCT_PROPERTIES'][$propID]);
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
