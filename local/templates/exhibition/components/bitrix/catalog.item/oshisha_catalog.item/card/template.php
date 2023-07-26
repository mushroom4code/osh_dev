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
$jsonForModal = [];

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

$subscription_item_ids = array_column($arResult["CURRENT_USER_SUBSCRIPTIONS"]["SUBSCRIPTIONS"]??[], 'ITEM_ID');
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

if ($show_price) {
    $jsonForModal = [
        'ID' => $item['ID'],
        'BUY_LINK' => $arItemIDs['BUY_LINK'],
        'QUANTITY_ID' => $arItemIDs['QUANTITY_ID'],
        'DETAIL_PAGE_URL' => $item['DETAIL_PAGE_URL'],
        'MORE_PHOTO' => $morePhoto,
        'DESCRIPTION' => $item['DETAIL_TEXT'] ?? '',
        'PRODUCT' => $item['PRODUCT'],
        'USE_DISCOUNT' => $useDiscount['VALUE'],
        'ACTUAL_BASKET' => $priceBasket,
        'PRICE' => $price['PRICE_DATA'],
        'SALE_PRICE' => round($specialPrice),
        'POPUP_PROPS' => $prop_see_in_window ?? 0,
        'NAME' => $productTitle,
        'LIKE' => [
            'ID_PROD' => $item['ID_PROD'],
            'F_USER_ID' => $item['F_USER_ID'],
            'COUNT_LIKE' => $item['COUNT_LIKE'] ?? 0,
            'COUNT_LIKES' => $item['COUNT_LIKES'] ?? 0,
            'COUNT_FAV' => $item['COUNT_FAV'] ?? 0,
        ],
        'USE_CUSTOM_SALE_PRICE' => false,
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
        if ($taste['VALUE']) {
            if (count($taste['VALUE']) > 2) {
                $showToggler = true;
            } elseif (count($taste['VALUE']) > 1) {
                // поместятся на одной строке 2 вкуса или нет
                $showToggler = (mb_strlen($taste['VALUE'][0]) + mb_strlen($taste['VALUE'][1])) > 18;
            }
            $togglerState = $showToggler ? ' many-tastes' : ' d-none many-tastes';
            $listClass = $showToggler ? ' js__tastes-list' : '';
        } ?>

        <div class="item-product-info">
            <div class="toggle_taste card-price <?= $taste['VALUE'] ? 'js__tastes' : '' ?>">
                <div class="variation_taste <?= $showToggler ? '' : 'show_padding' ?> <?= $listClass ?>">
                    <?php if ($taste['VALUE']) {
                        foreach ($taste['VALUE'] as $key => $name) {
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

                                    $propId = $taste['ID'];
                                    $valueKey = abs(crc32($taste["VALUE_ENUM_ID"][$keys]));
                                    ?>
                                    <span class="taste js__taste <?= $tasteSize ?>"
                                          data-prop-id="<?= "ArFilter_{$propId}" ?>"
                                          data-background="<?= '#' . $color[1] ?>"
                                          id="<?= "taste-ArFilter_{$propId}_{$valueKey}" ?>"
                                          data-filter-get='<?= "ArFilter_{$propId}_{$valueKey}" ?>'><?= $name ?></span>
                                <?php }
                            }
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
                <?php if ($taste['VALUE']) { ?>
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
                    <a class="bx_catalog_item_title <?= $styleForNo . ' ' . $not_auth ?>"
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
                <?php if ($price['PRICE_DATA'][1]['PRICE'] !== '0' && $item['PRODUCT']['QUANTITY'] !== '0') { ?>
                    <div class="box_with_fav_bask align-items-lg-center align-items-md-center align-items-end">
                        <?php if ($price['PRICE_DATA'][1]['PRICE'] !== '') { ?>
                            <div class="box_with_price card-price font_weight_600 d-flex flex-column min-height-auto">
                                <div class="d-flex flex-column">
                                    <div class="bx_price <?= $styleForNo ?> position-relative">
                                        <?= round($price['PRICE_DATA'][2]['PRICE']); ?>₽
                                    </div>
                                </div>
                            </div>

                            <div class="box_quantity_for_uric_line">
                                <span class="font-12 ml-1"><?= $item['PRODUCT']['QUANTITY'] ?></span>
                                <span class="font-12">шт.</span>
                            </div>
                            <? if ($arResult['IS_SUBSCRIPTION_PAGE'] == 'Y'): ?>
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
                                    <?php if ($show_price) { ?>
                                        <div class="btn red_button_cart btn-plus add2basket"
                                             data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                             data-product_id="<?= $item['ID']; ?>"
                                             data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                             id="<?= $arItemIDs['BUY_LINK']; ?>"
                                             <? if ($priceBasket > 0): ?>style="display:none;"<? endif; ?>>
                                            <img class="image-cart"
                                                 src="/local/templates/Oshisha/images/cart-white.png"/>
                                        </div>
                                        <div class="product-item-amount-field-contain-wrap"
                                             <? if ($priceBasket > 0): ?>style="display:flex;"<? endif; ?>
                                             data-product_id="<?= $item['ID']; ?>">
                                            <div class="product-item-amount-field-contain d-flex flex-row align-items-center">
                                                <a class="btn-minus  minus_icon no-select add2basket"
                                                   id="<?= $arItemIDs['BUY_LINK']; ?>"
                                                   href="javascript:void(0)" data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                                   data-product_id="<?= $item['ID']; ?>">
                                                </a>
                                                <div class="product-item-amount-field-block">
                                                    <input class="product-item-amount card_element"
                                                           id="<?= $arItemIDs['QUANTITY_ID'] ?>"
                                                           type="text"
                                                           value="<?= $priceBasket ?>">
                                                </div>
                                                <a class="btn-plus plus_icon no-select add2basket"
                                                   data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                   id="<?= $arItemIDs['BUY_LINK']; ?>" href="javascript:void(0)"
                                                   data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                                   data-product_id="<?= $item['ID']; ?>"
                                                   title="Доступно <?= $item['PRODUCT']['QUANTITY'] ?> товар"></a>
                                            </div>
                                            <div class="alert_quantity" data-id="<?= $item['ID'] ?>"></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?endif; ?>
                        <?php }
                        if (!$USER->IsAuthorized() && !$show_price) { ?>
                            <div class="btn-plus <?= $not_auth ?>"
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
