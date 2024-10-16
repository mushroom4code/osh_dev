<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Fuser;
use DataBase_like;

CModule::IncludeModule("highloadblock");

use Bitrix\Highloadblock as HL;
use Enterego\EnteregoHelper;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 * @var CUser $USER
 */
$this->setFrameMode(true);

global $SETTINGS;
$arIskCode = explode(",", $SETTINGS['arIskCode']);
$templateLibrary = array('popup', 'fx');
$currencyList = '';
$article = $arResult['PROPERTIES']['CML2_TRAITS'];
$mainId = $this->GetEditAreaId($arResult['ID']);


if (!empty($arResult['CURRENCIES'])) {
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = array(
    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList,
    'ITEM' => array(
        'ID' => $arResult['ID'],
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'OFFERS_SELECTED' => $arResult['OFFERS_SELECTED'],
        'JS_OFFERS' => $arResult['JS_OFFERS']
    )
);
unset($currencyList, $templateLibrary);

$itemIds = array(
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
$obName = $templateData['JS_OBJ'] = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];
$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];
$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

$haveOffers = !empty($arResult['OFFERS']);
if ($haveOffers) {
    $actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
        ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
        : reset($arResult['OFFERS']);
    $showSliderControls = false;

    foreach ($arResult['OFFERS'] as $offer) {
        if ($offer['MORE_PHOTO_COUNT'] > 1) {
            $showSliderControls = true;
            break;
        }
    }
} else {
    $actualItem = $arResult;
    $showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}

$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$skuProps = [];
$isGift = EnteregoHelper::productIsGift($arResult['ID']);
$useDiscount = $arResult['PROPERTIES']['USE_DISCOUNT'];
$rowResHidePrice = $arResult['PROPERTIES']['SEE_PRODUCT_AUTH']['VALUE'];
$price = $actualItem['PRICES_CUSTOM'];

$priceCalculate = $price['PRICE_DATA'][2]['PRICE'];
$price_new = '<span class="font-14 card-price-text">от </span> ' . $price['PRICE_DATA'][1]['PRINT_PRICE'];

if (!empty($price['USER_PRICE']['PRICE'])) {
    $specialPrice = $price['USER_PRICE'];
}

if ((USE_CUSTOM_SALE_PRICE || $useDiscount['VALUE_XML_ID'] === 'true') && !empty($price['SALE_PRICE']['PRINT_PRICE'])
    && (!isset($specialPrice) || $price['SALE_PRICE']['PRICE'] < $specialPrice['PRICE'])) {

    $specialPrice = $price['SALE_PRICE'];
}
if (isset($specialPrice)) {
    $priceCalculate = $specialPrice['PRICE'];
}

if (intval($SETTINGS['MAX_QUANTITY']) > 0 && $SETTINGS['MAX_QUANTITY'] < $actualItem['PRODUCT']['QUANTITY'])
    $actualItem['PRODUCT']['QUANTITY'] = $SETTINGS['MAX_QUANTITY'];

$showDescription = !empty($arResult['DETAIL_TEXT']);
$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['PRODUCT']['SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

$item_id = [];
$FUser_id = '';
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
$item_id[] = $arResult['ID'];
$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);

foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
    $arResult['COUNT_LIKES'] = $count;
}

$arResult['COUNT_LIKE'] = $count_likes['USER'][$arResult['ID']]['Like'][0];
$arResult['COUNT_FAV'] = $count_likes['USER'][$arResult['ID']]['Fav'][0];

$taste = $arResult['PROPERTIES'][PROPERTY_KEY_VKUS];
$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';

$priceBasket = 0;

if (!empty($arParams['BASKET_ITEMS'][$arResult["ID"]])) {
    $priceBasket = $arParams['BASKET_ITEMS'][$arResult["ID"]];
}

$actualItem['PICTURE'] = [];
if (!empty($actualItem['DETAIL_PICTURE'])) {
    $actualItem['PICTURE'][] = $actualItem['DETAIL_PICTURE'];
}

if (!empty($actualItem['MORE_PHOTO'])) {
    foreach ($actualItem['MORE_PHOTO'] as $item) {
        if (!in_array($item['SRC'] ?? [], $actualItem['PICTURE'][0] ?? []))  {
            $actualItem['PICTURE'][] = $item;
        }
    }
}
$show_price = true;
if ($rowResHidePrice == 'Нет' && !$USER->IsAuthorized()) {
    $show_price = false;
}
global $option_site;
?>
    <div class="bx-catalog-element  cat-det <?php if (!$show_price) { ?>blur_photo<?php } ?>"
         id="<?= $itemIds['ID'] ?>">
        <div class="row mb-3">
            <div class="col" id="navigation">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:breadcrumb",
                    "oshisha_breadcrumb",
                    array(
                        "START_FROM" => "0",
                        "PATH" => "",
                        "SITE_ID" => "-"
                    ),
                    false,
                    array('HIDE_ICONS' => 'Y')
                ); ?>
            </div>
        </div>
        <?php if ($rowResHidePrice == 'Нет' && !empty($option_site->text_rospetrebnadzor_product)) { ?>
            <p class="font-14  mb-lg-4  mb-md-4 mb-2"><?= $option_site->text_rospetrebnadzor_product; ?></p>
        <?php } ?>
        <div class="box_with_photo_product row">
            <div class="col-md-6 col-sm-6 col-lg-6 product_left col-12">
                <div class="product-item-detail-slider-container <?php if (!empty($taste['VALUE'])) { ?> p-lg-md-25
                    <?php } ?>" id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
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
                            <span class="product-item-detail-slider-left carousel_elem_custom"
                                  data-entity="slider-control-left"
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
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <div class="product-item-detail-slider-image active" data-entity="image"
                                         data-id="1">
                                        <img src="/local/templates/Oshisha/images/no-photo.gif" itemprop="image">
                                    </div>
                                    <?
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
                                    <? $APPLICATION->IncludeComponent(
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
                    <?php
                    if ($haveOffers) {
                        foreach ($arResult['OFFERS'] as $keyOffer => $offer) {
                            if (!isset($offer['MORE_PHOTO_COUNT']) || $offer['MORE_PHOTO_COUNT'] <= 0)
                                continue;

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
            <div class="col-md-5 col-sm-6 col-lg-6 col-12 mt-lg-0 mt-md-0 mt-4 d-flex flex-column product_right justify-content-between">
                <h1 class="head-title"><?= $name ?></h1>
                <?php if ($isGift) { ?>
                    <div>
                        <h4 class="bx-title">Данная продукция не продается отдельно</h4>
                    </div>
                    <?php
                } else { ?>
                <div class="d-flex flex-lg-column flex-md-column flex-column-reverse">
                    <?php
                    $height = 10;
                    $strong = 0;
                    if (isset($arResult['PROPERTIES'][PROP_STRONG_CODE]) && !empty($arResult['PROPERTIES'][PROP_STRONG_CODE]['VALUE'])) {
                        switch ($arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE_SORT']) {
                            case "1" || '100':
                                $strong = 0.5;
                                $color = "#07AB66";
                                break;
                            case "2" || '200':
                                $strong = 1.5;
                                $color = "#FFC700";
                                break;
                            case "3" || '300':
                                $strong = 2.5;
                                $color = "#FF7A00";
                                break;
                        } ?>
                        <div style="color: <?= $color ?>" class="column mt-lg-3 mt-md-3 mt-0 mb-4">
                            <p class="condensation_text">
                                Крепость: <?= $arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE'] ?> </p>
                            <div class="d-flex flex-row">
                                <?php for ($i = 0; $i < 3; $i++) { ?>
                                    <div style="border-color: <?= $color ?>; <?= ($strong - $i) >= 1 ? "background-color: $color" : ''; ?>"
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
                                <?php } ?>
                            </div>
                        </div>
                    <?php }
                    foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName) {
                    switch ($blockName) {
                    case 'price':
                    $styles = ''; ?>
                    <div class="mb-4 d-flex flex-column">
                        <div class="mb-3 d-flex flex-row align-items-center">
                            <div class="product-item-detail-price-current"
                                 id="<?= $itemIds['PRICE_ID'] ?>">
                                <?= $price['PRICE_DATA'][2]['PRINT_PRICE']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            break;
            case 'quantityLimit':
                if ($show_price) {
                    $arParams['SHOW_MAX_QUANTITY'] = 'N';
                    if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
                        if ($haveOffers) { ?>
                            <div class="mb-3" id="<?= $itemIds['QUANTITY_LIMIT'] ?>"
                                 style="display: none;">
                                                        <span class="product-item-quantity"
                                                              data-entity="quantity-limit-value"></span>
                            </div>
                        <?php } else {
                            if ($measureRatio && (float)$actualItem['PRODUCT']['QUANTITY'] > 0
                                && $actualItem['CHECK_QUANTITY']) { ?>
                                <div class="mb-3 text-center"
                                     id="<?= $itemIds['QUANTITY_LIMIT'] ?>">
                                    <span class="product-item-detail-info-container-title"><?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:</span>
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
            if ($show_price) {
            if ($actualItem['PRODUCT']['QUANTITY'] != '0') { ?>
                <div class="mb-lg-3 mb-md-3 mb-4 d-flex flex-row align-items-center bx_catalog_item bx_catalog_item_controls"
                    <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                     data-entity="quantity-block">
                    <div class="product-item-amount-field-contain mr-3">
                                                    <span class="btn-minus no-select minus_icon add2basket basket_prod_detail"
                                                          data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                                          data-product_id="<?= $arResult['ID']; ?>"
                                                          id="<?= $itemIds['QUANTITY_DOWN_ID'] ?>"
                                                          data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>">
                                                    </span>
                        <div class="product-item-amount-field-block">
                            <input class="product-item-amount card_element cat-det" id="<?= $itemIds['QUANTITY_ID'] ?>"
                                   type="number" value="<?= $priceBasket ?>"
                                   data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                   data-product_id="<?= $arResult['ID']; ?>"
                                   data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"/>
                        </div>
                        <span class="btn-plus no-select plus_icon add2basket basket_prod_detail"
                              data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                              data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"
                              data-product_id="<?= $arResult['ID']; ?>"
                              id="<?= $itemIds['QUANTITY_UP_ID'] ?>"></span>
                    </div>
                    <a id="<?= $arResult['BUY_LINK']; ?>" href="javascript:void(0)" rel="nofollow"
                       class="add2basket basket_prod_detail btn red_button_cart"
                       data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>" data-product_id="<?= $arResult['ID']; ?>"
                       title="Добавить в корзину">
                        <img class="image-cart" src="/local/templates/Oshisha/images/cart-white.png"/>
                    </a>
                    <div id="result_box"></div>
                    <div id="popup_mess"></div>
                </div>
                <div class="alert_quantity" data-id="<?= $arResult['ID'] ?>"></div>
                <div class="d-flex flex-lg-column flex-md-column flex-column-reverse">
                    <div class="mb-4 d-flex align-items-center">
                        <a href="#" class="link_prod_quant"><span>В наличии </span></a>
                        <a href="#" class="ml-lg-5 ml-md-5 ml-3 link_prod"><span>Посмотреть наличие в магазинах</span>
                        </a>
                    </div>
                    <?php }
                    else { ?>
                        <div class="bx_catalog_item_controls mb-5 d-flex flex-row align-items-center
                                                     bx_catalog_item"
                            <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                             data-entity="quantity-block">
                            <div class="d-flex flex-row align-items-center mr-3">
                                <div class="product-item-amount-field-contain">
                                                                <span class=" no-select minus_icon add2basket basket_prod_detail mr-3"
                                                                      style="pointer-events: none;">
                                                                </span>
                                    <div class="product-item-amount-field-block">
                                        <input class="product-item-amount" id="<?= $itemIds['QUANTITY_ID'] ?>"
                                               disabled="disabled" type="number" value="0">
                                    </div>
                                    <span class="no-select plus_icon add2basket basket_prod_detail ml-3"
                                          style="pointer-events: none;">
                                                                </span>
                                </div>
                                <a id="<?= $arResult['BUY_LINK']; ?>" href="javascript:void(0)"
                                   rel="nofollow"
                                   class="basket_prod_detail detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                   <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?> detail_disabled"
                                   data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                   data-product_id="<?= $arResult['ID']; ?>"
                                   title="Добавить в корзину">Забронировать</a>
                            </div>
                            <div id="result_box" style="width: 100%;position: absolute;"></div>
                            <div class="detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>">
                                <i class="fa fa-bell-o <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'filled' : '' ?>"
                                   aria-hidden="true"></i>
                            </div>
                            <div id="popup_mess"
                                 class="popup_mess_prods <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>"
                                 data-subscription_id="<?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? $arResult['ITEM_SUBSCRIPTION']['ID'] : '' ?>"
                                 data-product_id="<?= $arResult['ID']; ?>"></div>
                        </div>
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="not_product detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>">
                                Нет в наличии
                            </div>
                        </div>
                        <?php
                    }
                    }
                    break;
                    }
                    } ?>
                    <div class="new_box d-flex flex-row align-items-center mb-lg-0 mb-md-0 mb-5">
                        <span></span>
                        <p>Наличие товара, варианты и стоимость доставки будут указаны далее при оформлении заказа. </p>
                    </div>
                    <?php if ($actualItem['PRODUCT']['QUANTITY'] != '0') { ?></div><?php } ?>
                <div class="ganerate_price_wrap ml-auto mt-5 w-75 font-weight-bold h5"
                     <? if ($priceBasket > 0): ?><? else: ?>style="display:none;"<? endif; ?>>
                    Итого:
                    <div class="inline-block float-right ganerate_price">
                        <?= (round($priceCalculate) * $priceBasket) . ' ₽'; ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php if ($haveOffers) {
            if ($arResult['OFFER_GROUP']) { ?>
                <div class="row">
                    <div class="col">
                        <?php foreach ($arResult['OFFER_GROUP_VALUES'] as $offerId) { ?>
                            <span id="<?= $itemIds['OFFER_GROUP'] . $offerId ?>" style="display: none;">
							<?php $APPLICATION->IncludeComponent(
                                'bitrix:catalog.set.constructor',
                                'bootstrap_v4',
                                array(
                                    'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                                    'IBLOCK_ID' => $arResult['OFFERS_IBLOCK'],
                                    'ELEMENT_ID' => $offerId,
                                    'PRICE_CODE' => $arParams['PRICE_CODE'],
                                    'BASKET_URL' => $arParams['BASKET_URL'],
                                    'OFFERS_CART_PROPERTIES' => $arParams['OFFERS_CART_PROPERTIES'],
                                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                    'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                    'DETAIL_URL' => $arParams['~DETAIL_URL']
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            ); ?>
						</span>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
        } else {
            if ($arResult['MODULES']['catalog'] && $arResult['OFFER_GROUP']) { ?>
                <div class="row">
                    <div class="col">
                        <?php $APPLICATION->IncludeComponent(
                            'bitrix:catalog.set.constructor',
                            'bootstrap_v4',
                            array(
                                'CUSTOM_SITE_ID' => $arParams['CUSTOM_SITE_ID'] ?? null,
                                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                'ELEMENT_ID' => $arResult['ID'],
                                'PRICE_CODE' => $arParams['PRICE_CODE'],
                                'BASKET_URL' => $arParams['BASKET_URL'],
                                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                'CACHE_TIME' => $arParams['CACHE_TIME'],
                                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                'CURRENCY_ID' => $arParams['CURRENCY_ID']
                            ),
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        ); ?>
                    </div>
                </div>
                <?php
            }
        } ?>
        <ul class="nav nav-fill mb-3 mt-5" role="tablist">
            <?php if ($showDescription) { ?>
                <li class="nav-item link">
                    <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home"
                       role="tab" aria-controls="pills-home" aria-selected="true">
                        <span><?= $arParams['MESS_DESCRIPTION_TAB'] ?></span></a>
                </li>
                <?php
            }
            if (!empty($arResult['DISPLAY_PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) { ?>
                <li class="nav-item">
                    <a class="nav-link <? if (!$showDescription): ?>active<? endif; ?>" id="pills-profile-tab"
                       data-toggle="pill" href="#pills-profile"
                       role="tab" aria-controls="pills-profile" aria-selected="false">
                        <span><?= $arParams['MESS_PROPERTIES_TAB'] ?></span>
                    </a>
                </li>
                <?php
            }
            if ($arParams['USE_COMMENTS'] === 'Y') { ?>
                <li class="nav-item">
                    <a class="nav-link" id="pills-contact-tab" data-toggle="pill" href="#pills-contact"
                       role="tab" aria-controls="pills-contact" aria-selected="false">
                        <i class="fa fa-comment-o" aria-hidden="true"></i>
                        <span><?= $arParams['MESS_COMMENTS_TAB'] ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <div class="tab-content mt-5">
            <?php if ($showDescription) { ?>
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                    <?php
                    if ($arResult['DETAIL_TEXT'] != '') {
                        echo $arResult['DETAIL_TEXT_TYPE'] === 'html' ? $arResult['DETAIL_TEXT'] : '<p>' . $arResult['DETAIL_TEXT'] . '</p>';
                    } ?>
                </div>
                <?php
            }

            if (!empty($arResult['PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                include(__DIR__ . '/props/template.php');
            }
            if ($arParams['USE_COMMENTS'] === 'Y') { ?>
                <div class="tab-pane fade " id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                    <?php $componentCommentsParams = array(
                        'ELEMENT_ID' => $arResult['ID'],
                        'ELEMENT_CODE' => '',
                        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                        'SHOW_DEACTIVATED' => $arParams['SHOW_DEACTIVATED'],
                        'URL_TO_COMMENT' => '',
                        'WIDTH' => '',
                        'COMMENTS_COUNT' => '5',
                        'BLOG_USE' => $arParams['BLOG_USE'],
                        'FB_USE' => $arParams['FB_USE'],
                        'FB_APP_ID' => $arParams['FB_APP_ID'],
                        'VK_USE' => $arParams['VK_USE'],
                        'VK_API_ID' => $arParams['VK_API_ID'],
                        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                        'CACHE_TIME' => $arParams['CACHE_TIME'],
                        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                        'BLOG_TITLE' => '',
                        'BLOG_URL' => $arParams['BLOG_URL'],
                        'PATH_TO_SMILE' => '',
                        'EMAIL_NOTIFY' => $arParams['BLOG_EMAIL_NOTIFY'],
                        'AJAX_POST' => 'Y',
                        'SHOW_SPAM' => 'Y',
                        'SHOW_RATING' => 'N',
                        'FB_TITLE' => '',
                        'FB_USER_ADMIN_ID' => '',
                        'FB_COLORSCHEME' => 'light',
                        'FB_ORDER_BY' => 'reverse_time',
                        'VK_TITLE' => '',
                        'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME']
                    );
                    if (isset($arParams["USER_CONSENT"]))
                        $componentCommentsParams["USER_CONSENT"] = $arParams["USER_CONSENT"];
                    if (isset($arParams["USER_CONSENT_ID"]))
                        $componentCommentsParams["USER_CONSENT_ID"] = $arParams["USER_CONSENT_ID"];
                    if (isset($arParams["USER_CONSENT_IS_CHECKED"]))
                        $componentCommentsParams["USER_CONSENT_IS_CHECKED"] = $arParams["USER_CONSENT_IS_CHECKED"];
                    if (isset($arParams["USER_CONSENT_IS_LOADED"]))
                        $componentCommentsParams["USER_CONSENT_IS_LOADED"] = $arParams["USER_CONSENT_IS_LOADED"];
                    $APPLICATION->IncludeComponent(
                        'bitrix:catalog.comments',
                        'oshisha_catalog.commets',
                        $componentCommentsParams,
                        $component,
                        array('HIDE_ICONS' => 'Y')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php if ($arParams['BRAND_USE'] === 'Y') { ?>
    <div class="col-sm-4 col-md-3">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:catalog.brandblock',
            'bootstrap_v4',
            array(
                'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'ELEMENT_ID' => $arResult['ID'],
                'ELEMENT_CODE' => '',
                'PROP_CODE' => $arParams['BRAND_PROP_CODE'],
                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                'CACHE_TIME' => $arParams['CACHE_TIME'],
                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                'WIDTH' => '',
                'HEIGHT' => ''
            ),
            $component,
            array('HIDE_ICONS' => 'Y')
        ); ?>
    </div>
<?php } ?>
    <div class="row">
        <div class="col">
            <?php if ($arResult['CATALOG'] && $actualItem['CAN_BUY'] && ModuleManager::isModuleInstalled('sale')) {
                $APPLICATION->IncludeComponent(
                    'bitrix:sale.prediction.product.detail',
                    '',
                    array(
                        'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                        'BUTTON_ID' => $showBuyBtn ? $itemIds['BUY_LINK'] : $itemIds['ADD_BASKET_LINK'],
                        'POTENTIAL_PRODUCT_TO_BUY' => array(
                            'ID' => isset($arResult['ID']) ? $arResult['ID'] : null,
                            'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
                            'PRODUCT_PROVIDER_CLASS' => isset($arResult['~PRODUCT_PROVIDER_CLASS']) ? $arResult['~PRODUCT_PROVIDER_CLASS'] : '\Bitrix\Catalog\Product\CatalogProvider',
                            'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
                            'IBLOCK_ID' => isset($arResult['IBLOCK_ID']) ? $arResult['IBLOCK_ID'] : null,

                            'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][0]['ID']) ? $arResult['OFFERS'][0]['ID'] : null,
                            'SECTION' => array(
                                'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
                                'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
                                'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
                                'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null,
                            ),
                        )
                    ),
                    $component,
                    array('HIDE_ICONS' => 'Y')
                );
            }

            if ($arResult['CATALOG'] && $arParams['USE_GIFTS_DETAIL'] == 'Y' && ModuleManager::isModuleInstalled('sale')) { ?>
                <div data-entity="parent-container">
                    <?php if (!isset($arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE']) || $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'] !== 'Y') { ?>
                        <div class="catalog-block-header" data-entity="header" data-showed="false"
                             style="display: none; opacity: 0;">
                            <?= ($arParams['GIFTS_DETAIL_BLOCK_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_GIFT_BLOCK_TITLE_DEFAULT')) ?>
                        </div>
                        <?php
                    }
                    CBitrixComponent::includeComponentClass('bitrix:sale.products.gift');
                    $APPLICATION->IncludeComponent('bitrix:sale.products.gift', 'bootstrap_v4', array(
                        'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                        'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                        'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],

                        'PRODUCT_ROW_VARIANTS' => "",
                        'PAGE_ELEMENT_COUNT' => 0,
                        'DEFERRED_PRODUCT_ROW_VARIANTS' => Json::encode(
                            SaleProductsGiftComponent::predictRowVariants(
                                $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
                                $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT']
                            )
                        ),
                        'DEFERRED_PAGE_ELEMENT_COUNT' => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],

                        'SHOW_DISCOUNT_PERCENT' => $arParams['GIFTS_SHOW_DISCOUNT_PERCENT'],
                        'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
                        'SHOW_OLD_PRICE' => $arParams['GIFTS_SHOW_OLD_PRICE'],
                        'PRODUCT_DISPLAY_MODE' => 'Y',
                        'PRODUCT_BLOCKS_ORDER' => $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'],
                        'SHOW_SLIDER' => $arParams['GIFTS_SHOW_SLIDER'],
                        'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
                        'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',

                        'TEXT_LABEL_GIFT' => $arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'],

                        'LABEL_PROP_' . $arParams['IBLOCK_ID'] => array(),
                        'LABEL_PROP_MOBILE_' . $arParams['IBLOCK_ID'] => array(),
                        'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],

                        'ADD_TO_BASKET_ACTION' => (isset($arParams['ADD_TO_BASKET_ACTION']) ? $arParams['ADD_TO_BASKET_ACTION'] : ''),
                        'MESS_BTN_BUY' => $arParams['~GIFTS_MESS_BTN_BUY'],
                        'MESS_BTN_ADD_TO_BASKET' => $arParams['~GIFTS_MESS_BTN_BUY'],
                        'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
                        'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],

                        'SHOW_PRODUCTS_' . $arParams['IBLOCK_ID'] => 'Y',
                        'PROPERTY_CODE_' . $arParams['IBLOCK_ID'] => $arParams['LIST_PROPERTY_CODE'],
                        'PROPERTY_CODE_MOBILE' . $arParams['IBLOCK_ID'] => $arParams['LIST_PROPERTY_CODE_MOBILE'],
                        'PROPERTY_CODE_' . $arResult['OFFERS_IBLOCK'] => $arParams['OFFER_TREE_PROPS'],
                        'OFFER_TREE_PROPS_' . $arResult['OFFERS_IBLOCK'] => $arParams['OFFER_TREE_PROPS'],
                        'CART_PROPERTIES_' . $arResult['OFFERS_IBLOCK'] => $arParams['OFFERS_CART_PROPERTIES'],
                        'ADDITIONAL_PICT_PROP_' . $arParams['IBLOCK_ID'] => (isset($arParams['ADD_PICT_PROP']) ? $arParams['ADD_PICT_PROP'] : ''),
                        'ADDITIONAL_PICT_PROP_' . $arResult['OFFERS_IBLOCK'] => (isset($arParams['OFFER_ADD_PICT_PROP']) ? $arParams['OFFER_ADD_PICT_PROP'] : ''),

                        'HIDE_NOT_AVAILABLE' => 'Y',
                        'HIDE_NOT_AVAILABLE_OFFERS' => 'Y',
                        'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                        'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                        'PRICE_CODE' => $arParams['PRICE_CODE'],
                        'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],
                        'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                        'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                        'BASKET_URL' => $arParams['BASKET_URL'],
                        'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
                        'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
                        'PARTIAL_PRODUCT_PROPERTIES' => $arParams['PARTIAL_PRODUCT_PROPERTIES'],
                        'USE_PRODUCT_QUANTITY' => 'N',
                        'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                        'POTENTIAL_PRODUCT_TO_BUY' => array(
                            'ID' => isset($arResult['ID']) ? $arResult['ID'] : null,
                            'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
                            'PRODUCT_PROVIDER_CLASS' => isset($arResult['~PRODUCT_PROVIDER_CLASS']) ? $arResult['~PRODUCT_PROVIDER_CLASS'] : '\Bitrix\Catalog\Product\CatalogProvider',
                            'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
                            'IBLOCK_ID' => isset($arResult['IBLOCK_ID']) ? $arResult['IBLOCK_ID'] : null,

                            'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'])
                                ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']
                                : null,
                            'SECTION' => array(
                                'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
                                'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
                                'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
                                'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null,
                            ),
                        ),

                        'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                        'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                        'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
                    ),
                        $component,
                        array('HIDE_ICONS' => 'Y')
                    ); ?>
                </div>
                <?php
            }
            if ($arResult['CATALOG'] && $arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] == 'Y' && ModuleManager::isModuleInstalled('sale')) { ?>
                <div data-entity="parent-container">
                    <?php if (!isset($arParams['GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE']) || $arParams['GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE'] !== 'Y') { ?>
                        <div class="catalog-block-header" data-entity="header" data-showed="false"
                             style="display: none; opacity: 0;">
                            <?= ($arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_GIFTS_MAIN_BLOCK_TITLE_DEFAULT')) ?>
                        </div>
                        <?php
                    }

                    $APPLICATION->IncludeComponent('bitrix:sale.gift.main.products', 'bootstrap_v4',
                        array(
                            'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
                            'PAGE_ELEMENT_COUNT' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
                            'LINE_ELEMENT_COUNT' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
                            'HIDE_BLOCK_TITLE' => 'Y',
                            'BLOCK_TITLE' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],

                            'OFFERS_FIELD_CODE' => $arParams['OFFERS_FIELD_CODE'],
                            'OFFERS_PROPERTY_CODE' => $arParams['OFFERS_PROPERTY_CODE'],

                            'AJAX_MODE' => $arParams['AJAX_MODE'],
                            'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                            'IBLOCK_ID' => $arParams['IBLOCK_ID'],

                            'ELEMENT_SORT_FIELD' => 'ID',
                            'ELEMENT_SORT_ORDER' => 'DESC',
                            //'ELEMENT_SORT_FIELD2' => $arParams['ELEMENT_SORT_FIELD2'],
                            //'ELEMENT_SORT_ORDER2' => $arParams['ELEMENT_SORT_ORDER2'],
                            'FILTER_NAME' => 'searchFilter',
                            'SECTION_URL' => $arParams['SECTION_URL'],
                            'DETAIL_URL' => $arParams['DETAIL_URL'],
                            'BASKET_URL' => $arParams['BASKET_URL'],
                            'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
                            'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                            'SECTION_ID_VARIABLE' => $arParams['SECTION_ID_VARIABLE'],

                            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                            'CACHE_TIME' => $arParams['CACHE_TIME'],

                            'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                            'SET_TITLE' => $arParams['SET_TITLE'],
                            'PROPERTY_CODE' => $arParams['PROPERTY_CODE'],
                            'PRICE_CODE' => $arParams['PRICE_CODE'],
                            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                            'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],

                            'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                            'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                            'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                            'HIDE_NOT_AVAILABLE' => 'Y',
                            'HIDE_NOT_AVAILABLE_OFFERS' => 'Y',
                            'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
                            'PRODUCT_BLOCKS_ORDER' => $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'],

                            'SHOW_SLIDER' => $arParams['GIFTS_SHOW_SLIDER'],
                            'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
                            'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',

                            'ADD_PICT_PROP' => (isset($arParams['ADD_PICT_PROP']) ? $arParams['ADD_PICT_PROP'] : ''),
                            'LABEL_PROP' => (isset($arParams['LABEL_PROP']) ? $arParams['LABEL_PROP'] : ''),
                            'LABEL_PROP_MOBILE' => (isset($arParams['LABEL_PROP_MOBILE']) ? $arParams['LABEL_PROP_MOBILE'] : ''),
                            'LABEL_PROP_POSITION' => (isset($arParams['LABEL_PROP_POSITION']) ? $arParams['LABEL_PROP_POSITION'] : ''),
                            'OFFER_ADD_PICT_PROP' => (isset($arParams['OFFER_ADD_PICT_PROP']) ? $arParams['OFFER_ADD_PICT_PROP'] : ''),
                            'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : ''),
                            'SHOW_DISCOUNT_PERCENT' => (isset($arParams['SHOW_DISCOUNT_PERCENT']) ? $arParams['SHOW_DISCOUNT_PERCENT'] : ''),
                            'DISCOUNT_PERCENT_POSITION' => (isset($arParams['DISCOUNT_PERCENT_POSITION']) ? $arParams['DISCOUNT_PERCENT_POSITION'] : ''),
                            'SHOW_OLD_PRICE' => (isset($arParams['SHOW_OLD_PRICE']) ? $arParams['SHOW_OLD_PRICE'] : ''),
                            'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
                            'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
                            'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
                            'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),
                            'ADD_TO_BASKET_ACTION' => (isset($arParams['ADD_TO_BASKET_ACTION']) ? $arParams['ADD_TO_BASKET_ACTION'] : ''),
                            'SHOW_CLOSE_POPUP' => (isset($arParams['SHOW_CLOSE_POPUP']) ? $arParams['SHOW_CLOSE_POPUP'] : ''),
                            'DISPLAY_COMPARE' => (isset($arParams['DISPLAY_COMPARE']) ? $arParams['DISPLAY_COMPARE'] : ''),
                            'COMPARE_PATH' => (isset($arParams['COMPARE_PATH']) ? $arParams['COMPARE_PATH'] : ''),
                        )
                        + array(
                            'OFFER_ID' => empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'])
                                ? $arResult['ID']
                                : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'],
                            'SECTION_ID' => $arResult['SECTION']['ID'],
                            'ELEMENT_ID' => $arResult['ID'],

                            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                            'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
                        ),
                        $component,
                        array('HIDE_ICONS' => 'Y')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php if ($haveOffers) {
    $offerIds = array();
    $offerCodes = array();
    $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

    foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer) {
        $offerIds[] = (int)$jsOffer['ID'];
        $offerCodes[] = $jsOffer['CODE'];

        $fullOffer = $arResult['OFFERS'][$ind];
        $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

        $strAllProps = '';
        $strMainProps = '';
        $strPriceRangesRatio = '';
        $strPriceRanges = '';

        if ($arResult['SHOW_OFFERS_PROPS']) {
            if (!empty($jsOffer['DISPLAY_PROPERTIES'])) {
                foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property) {
                    $current = '<li class="product-item-detail-properties-item">
					<span class="product-item-detail-properties-name">' . $property['NAME'] . '</span>
					<span class="product-item-detail-properties-dots"></span>
					<span class="product-item-detail-properties-value">' . (
                        is_array($property['VALUE'])
                            ? implode(' / ', $property['VALUE'])
                            : $property['VALUE']
                        ) . '</span></li>';
                    $strAllProps .= $current;

                    if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']])) {
                        $strMainProps .= $current;
                    }
                }
                unset($current);
            }
        }

        $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
        $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
        $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
        $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
    }

    $templateData['OFFER_IDS'] = $offerIds;
    $templateData['OFFER_CODES'] = $offerCodes;
    unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => true,
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'SHOW_SKU_PROPS' => $arResult['SHOW_OFFERS_PROPS'],
            'OFFER_GROUP' => $arResult['OFFER_GROUP'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'VISUAL' => $itemIds,
        'DEFAULT_PICTURE' => array(
            'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
            'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
        ),
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'NAME' => $arResult['~NAME'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'BASKET_URL' => $arParams['BASKET_URL'],
            'SKU_PROPS' => $arResult['OFFERS_PROP_CODES'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        ),
        'OFFERS' => $arResult['JS_OFFERS'],
        'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
        'TREE_PROPS' => $skuProps
    );
} else {
    $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
    if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !$emptyProductProperties) { ?>
        <div id="<?= $itemIds['BASKET_PROP_DIV'] ?>" style="display: none;">
            <?php if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])) {
                foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propId => $propInfo) { ?>
                    <input type="hidden" name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
                           value="<?= htmlspecialcharsbx($propInfo['ID']) ?>">
                    <?php unset($arResult['PRODUCT_PROPERTIES'][$propId]);
                }
            }

            $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
            if (!$emptyProductProperties) { ?>
                <table>
                    <?php foreach ($arResult['PRODUCT_PROPERTIES'] as $propId => $propInfo) { ?>
                        <tr>
                            <td><?= $arResult['PROPERTIES'][$propId]['NAME'] ?></td>
                            <td>
                                <?php if ($arResult['PROPERTIES'][$propId]['PROPERTY_TYPE'] === 'L'
                                    && $arResult['PROPERTIES'][$propId]['LIST_TYPE'] === 'C') {
                                    foreach ($propInfo['VALUES'] as $valueId => $value) { ?>
                                        <label>
                                            <input type="radio"
                                                   name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
                                                   value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"checked"' : '') ?>>
                                            <?= $value ?>
                                        </label>
                                        <br>
                                        <?php
                                    }
                                } else { ?>
                                    <select name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]">
                                        <?php foreach ($propInfo['VALUES'] as $valueId => $value) { ?>
                                            <option value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"selected"' : '') ?>>
                                                <?= $value ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div>
        <?php
    }

    $count = count($actualItem['PICTURE']);
    $arraySlider = $actualItem['PICTURE'];

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'VISUAL' => $itemIds,
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'PICT' => reset($arraySlider),
            'NAME' => $arResult['~NAME'],
            'SUBSCRIPTION' => true,
            'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
            'ITEM_PRICES' => $arResult['ITEM_PRICES'],
            'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
            'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
            'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
            'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
            'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
            'SLIDER_COUNT' => $count,
            'SLIDER' => $arraySlider,
            'CAN_BUY' => $arResult['CAN_BUY'],
            'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
            'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
            'MAX_QUANTITY' => $arResult['PRODUCT']['QUANTITY'],
            'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
            'EMPTY_PROPS' => $emptyProductProperties,
            'BASKET_URL' => $arParams['BASKET_URL'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        )
    );
    unset($emptyProductProperties);
}

if ($arParams['DISPLAY_COMPARE']) {
    $jsParams['COMPARE'] = array(
        'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
        'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
        'COMPARE_PATH' => $arParams['COMPARE_PATH']
    );
} ?>
    </div>
    <script>
        BX.message({
            ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
            TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
            TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
            BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
            BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
            BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
            BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
            BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
            TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
            COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
            COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
            COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
            BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
            PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
            PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
            RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
            RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
            SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
        });

        let <?=$obName?> = new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
    </script>
<?php unset($actualItem, $itemIds, $jsParams);
