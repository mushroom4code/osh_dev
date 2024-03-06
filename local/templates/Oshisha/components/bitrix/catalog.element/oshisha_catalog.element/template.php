<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Fuser;
use Enterego\EnteregoGroupedProducts;

CModule::IncludeModule("highloadblock");

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
global $option_site;
$arIskCode = explode(",", $SETTINGS['arIskCode']);

$currencyList = '';
$article = $arResult['PROPERTIES']['CML2_TRAITS'];


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

$haveOffers = !empty($arResult['OFFERS']);
$mainId = $this->GetEditAreaId($arResult['ID']);
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

if ($haveOffers) {
    $actualItem = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] ?? reset($arResult['OFFERS']);
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
$rowResHidePrice = $arResult['PROPERTIES']['SEE_PRODUCT_AUTH']['VALUE'];
$price = $actualItem['PRICES_CUSTOM'];
$newProduct = $arResult['PROPERTIES'][PROP_NEW];
$hitProduct = $arResult['PROPERTIES'][PROP_HIT];
$priceCalculate = $price['PRICE_DATA']['PRICE'];
$price_new = $price['PRICE_DATA']['PRINT_PRICE'];

if (!empty($price['USER_PRICE']['PRICE'])) {
    $specialPrice = $price['USER_PRICE'];
}

if ((USE_CUSTOM_SALE_PRICE) && !empty($price['SALE_PRICE']['PRINT_PRICE'])
    && (!isset($specialPrice) || $price['SALE_PRICE']['PRICE'] < $specialPrice['PRICE'])) {

    $specialPrice = $price['SALE_PRICE'];
}
if (isset($specialPrice)) {
    $priceCalculate = $specialPrice['PRICE'];
}

if (intval($SETTINGS['MAX_QUANTITY']) > 0 && $SETTINGS['MAX_QUANTITY'] < $actualItem['PRODUCT']['QUANTITY']) {
    $actualItem['PRODUCT']['QUANTITY'] = $SETTINGS['MAX_QUANTITY'];
}

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

$priceBasket = $priceCalculate = 0;

if (!empty($arParams['BASKET_ITEMS'][$arResult["ID"]])) {
    $priceBasket = $arParams['BASKET_ITEMS'][$arResult["ID"]];
}

$actualItem['PICTURE'] = [];
if (!empty($actualItem['DETAIL_PICTURE'])) {
    $actualItem['PICTURE'][] = $actualItem['DETAIL_PICTURE'];
}

if (!empty($actualItem['MORE_PHOTO'])) {
    foreach ($actualItem['MORE_PHOTO'] as $item) {
        if (!in_array($item['SRC'] ?? [], $actualItem['PICTURE'][0] ?? [])) {
            $actualItem['PICTURE'][] = $item;
        }
    }
}

$show_price = true;
if ($rowResHidePrice == 'Нет' && !$USER->IsAuthorized()) {
    $show_price = false;
}

?>
    <div class="bx-catalog-element cat-det mb-20"
         id="<?= $itemIds['ID'] ?>">
        <div class="mb-3" id="navigation">
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
        <?php if ($rowResHidePrice == 'Нет' && !empty($option_site->text_rospetrebnadzor_product)) { ?>
            <p class="message_for_user_minzdrav text-sm text-grayButton dark:text-grayIconLights font-light mb-8">
                <?= $option_site->text_rospetrebnadzor_product; ?></p>
        <?php } ?>
        <div class="box_with_photo_product flex md:flex-row flex-col mb-20">
            <?php $count = count($actualItem['PICTURE']);
            $arraySlider = $actualItem['PICTURE'];
            require_once(__DIR__ . '/slider/template.php'); ?>
            <div class="md:w-1/2 w-full md:mt-0 mt-7 flex flex-col catalog-item-product
				not-input-parse">
                <div class="head-title xl:text-3xl text-xl mb-4 text-lightGrayBg font-semibold dark:font-light
                dark:text-textDarkLightGray flex flex-row justify-between items-start relative">
                    <?= $name ?>

                    <?php if ($actualItem['PRODUCT']['QUANTITY'] == '0') { ?>
                        <div class="flex justify-between items-center product-toggle bx_catalog_item_controls">
                            <div class="not_product detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                  <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>">
                                <svg width="34" height="33" class="ml-3 <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ?
                                    'subscribed stroke-light-red dark:stroke-white' :
                                    'dark:stroke-tagFilterGray stroke-black' ?>"
                                     viewBox="0 0 34 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M25.5762 11.0001C25.5762 8.81209 24.6884 6.71367 23.1081 5.16649C21.5279 3.61932 19.3846 2.75012 17.1498 2.75012C14.915 2.75012 12.7717 3.61932 11.1915 5.16649C9.61121 6.71367 8.72344 8.81209 8.72344 11.0001C8.72344 20.6251 4.51025 23.3751 4.51025 23.3751H29.7894C29.7894 23.3751 25.5762 20.6251 25.5762 11.0001Z"
                                          stroke-width="3" stroke-linecap="round"
                                          stroke-linejoin="round"></path>
                                    <path d="M19.5794 28.875C19.3325 29.2917 18.9781 29.6376 18.5517 29.8781C18.1253 30.1186 17.6419 30.2451 17.1498 30.2451C16.6577 30.2451 16.1743 30.1186 15.7479 29.8781C15.3215 29.6376 14.9671 29.2917 14.7202 28.875"
                                          stroke-width="3" stroke-linecap="round"
                                          stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div id="popup_mess"
                                 class="popup_mess_prods catalog_popup absolute z-20 w-auto top-full left-0 <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>"
                                 data-subscription_id="<?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? $arResult['ITEM_SUBSCRIPTION']['ID'] : '' ?>"
                                 data-product_id="<?= $arResult['ID']; ?>"></div>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($isGift) { ?>
                    <div>
                        <h4 class="xl:text-3xl text-xl mb-4 text-light-red font-semibold dark:font-light
                           dark:text-hover-red  flex flex-row justify-between items-start">
                            Данная продукция не продается отдельно</h4>
                    </div>
                    <?php
                } else { ?>
                    <div class="flex flex-col">
                        <?php
                        $height = 10;
                        $strong = 0;
                        $color = '';
                        if (isset($arResult['PROPERTIES'][PROP_STRONG_CODE]) && !empty($arResult['PROPERTIES'][PROP_STRONG_CODE]['VALUE'])) {
                            switch ($arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE_SORT']) {
                                case "1":
                                    $strong = 1;
                                    $color = "#07AB66";
                                    break;
                                case "2":
                                    $strong = 2;
                                    $color = "#FFC700";
                                    break;
                                case "3":
                                    $strong = 3;
                                    $color = "#FF7A00";
                                    break;
                            } ?>
                            <div style="color: <?= $color ?>" class="column mt-1 mb-7">
                                <p class="condensation_text mb-2">
                                    Крепость : <?= $arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE'] ?> </p>
                                <div class="flex flex-row">
                                    <?php for ($i = 0; $i < 3; $i++) { ?>
                                        <div style="border-color: <?= $color ?>;
                                        <?= ($strong - $i) >= 1 ? "background-color: $color" : ''; ?>"
                                             class="condensation w-20 h-4 rounded-full border mr-2">
                                            <?php if ($strong - $i == 1) { ?>
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
                        /** Enterego grouped product */
                        if (!empty($arResult['GROUPED_PROPS_DATA']) && count($arResult['GROUPED_PRODUCTS']) > 1 &&
                            (int)$actualItem['PRODUCT']['QUANTITY'] > 0) { ?>
                            <div class="flex flex-col mb-7 box-offers-auto" data-entity="sku-line-block">
                                <?php $propsForOffers = EnteregoGroupedProducts::getDataPropOffers();
                                $productSelect = $arResult['GROUPED_PRODUCTS'][$arResult['ID']]['PROPERTIES'];
                                foreach ($arResult['GROUPED_PROPS_DATA'] as $keyCODE => $productGrouped) {
                                    if ($keyCODE !== 'USE_DISCOUNT') { ?>
                                        <div class="flex flex-row overflow-auto mb-5 w-full">
                                            <?php foreach ($productGrouped as $group) {
                                                $link = 'javascript:void(0)';
                                                $prop_value = 'Пустое значение';
                                                $tasted = $grouped = [];
                                                $type = $propsForOffers[$keyCODE]['TYPE'] ?? 'text';
                                                $title = 'Товар';
                                                $select = 'selected';
                                                $arrayEl = $productSelect[$keyCODE]['JS_PROP'] ?? [];
                                                $count = array_diff_assoc($arrayEl, $group);
                                                if (!empty($count) && (count($count) > 0 || count($arrayEl) !== count($group))) {
                                                    $select = '';
                                                }


                                                foreach ($group as $name => $prop) {
                                                    if (empty($prop['VALUE_ENUM'])) {
                                                        continue;
                                                    }

                                                    $prop_value = $prop['VALUE_ENUM'] . $propsForOffers[$keyCODE]['PREF'];

                                                    if (count($arResult['GROUPED_PROPS_DATA']) === 1) {
                                                        $link = $prop['CODE'];
                                                    }

                                                    if ($type === 'colorWithText') {
                                                        $tasted[$name] = [
                                                            'color' => '#' . explode('#',
                                                                    $prop['VALUE_XML_ID'])[1],
                                                            'name' => $prop['VALUE_ENUM'],
                                                        ];
                                                    } else {
                                                        $grouped[$name] = [
                                                            'xml_id' => $prop['VALUE_XML_ID'],
                                                            'name' => $prop['VALUE_ENUM']
                                                        ];
                                                    }
                                                    $title = $prop['NAME'];
                                                }

                                                if (!empty($prop_value)) {
                                                    if ($type === 'text') {
                                                        if (count($grouped) > 1) { ?>
                                                            <a href="<?= $link ?>" class="offer-link">
                                                                <div class="red_button_cart p-10
                                                                 width-fit-content mb-lg-2 m-md-2 m-1 offer-box
                                                                 cursor-pointer <?= $select ?>"
                                                                     title="<?= $offer['NAME'] ?>"
                                                                     data-active="<?= !empty($select) ? 'true' : 'false' ?>"
                                                                     data-prop_code="<?= $keyCODE ?>"
                                                                     data-prop_group="<?= htmlspecialchars(json_encode($group)) ?>"
                                                                     data-product_id="<?= '' ?>">
                                                                    <?php foreach ($grouped as $elemProp) { ?>
                                                                        <span class="br-100"><?= $elemProp['name'] ?></span>
                                                                    <?php } ?>
                                                                </div>
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="<?= $link ?>" class="offer-link <?= $select ?>">
                                                                <div class="red_button_cart w-fit rounded-full px-5 py-2
                                                                bg-white border-2 <?= !empty($select)
                                                                    ? ' border-light-red text-light-red dark:border-white
                                                                     dark:bg-grayButton dark:text-white dark:border' :
                                                                    ' border-textDarkLightGray text-dark
                                                                     dark:text-textDarkLightGray dark:bg-darkBox dark:border-0' ?>
                                                                     min-w-20 m-1 offer-box cursor-pointer font-medium
                                                                     dark:font-normal text-sm font-bolder"
                                                                     title="<?= $offer['NAME'] ?>"
                                                                     data-active="<?= !empty($select) ? 'true' : 'false' ?>"
                                                                     data-prop_group="<?= htmlspecialchars(json_encode($group)) ?>"
                                                                     data-prop_code="<?= $keyCODE ?>"
                                                                     data-onevalue="<?= $prop['VALUE_ENUM_ID'] ?>">
                                                                    <?= $prop_value ?>
                                                                </div>
                                                            </a>
                                                        <?php }
                                                    } elseif ($type === 'color') { ?>
                                                        <a href="<?= $link ?>" class="offer-link <?= $select ?>">
                                                            <div title="<?= $title ?>"
                                                                 data-active="<?= !empty($select) ? 'true' : 'false' ?>"
                                                                 data-prop_group="<?= htmlspecialchars(json_encode($group)) ?>"
                                                                 data-prop_code="<?= $keyCODE ?>"
                                                                 data-onevalue="<?= $prop['VALUE_ENUM_ID'] ?>"
                                                                 class="mr-2 offer-box color-hookah br-10 mb-1 <?= $select ?> ">
                                                                <img src="<?= $prop['PREVIEW_PICTURE'] ?>"
                                                                     class="w-20 h-20 rounded-md dark:border-0 border-2
                                                                     <?= $select ? 'border-light-red '
                                                                         : 'dark:opacity-50 border-textDarkLightGray' ?>"
                                                                     alt="<?= $title ?>"
                                                                     loading="lazy"/>
                                                            </div>
                                                        </a>
                                                    <?php } elseif ($type === 'colorWithText') {
                                                        if (!empty($tasted)) { ?>
                                                            <a href="<?= $link ?>" class="offer-link <?= $select ?>">
                                                                <div class="red_button_cart taste variation_taste
                                                                 w-fit p-3 mb-2 mr-1 offer-box rounded-md flex flex-row
                                                                    border-2 <?= !empty($select) ? 'border-light-red
                                                                     dark:border-white dark:border dark:bg-grayButton' :
                                                                    'border-textDarkLightGray dark:border-0' ?> min-w-20 offer-box
                                                                    cursor-pointer dark:bg-darkBox"
                                                                     title="<?= $title ?>"
                                                                     data-active="<?= !empty($select) ? 'true' : 'false' ?>"
                                                                     data-prop_code="<?= $keyCODE ?>"
                                                                     data-prop_group="<?= htmlspecialchars(json_encode($group)) ?>">
                                                                    <?php foreach ($tasted as $elem_taste) { ?>
                                                                        <span class="taste px-2.5 mr-1 py-1 text-xs rounded-full"
                                                                              data-background="<?= $elem_taste['color'] ?>"
                                                                              style="background-color: <?= $elem_taste['color'] ?>;
                                                                                      border-color: <?= $elem_taste['color'] ?>;">
                                                                            <?= $elem_taste['name'] ?>
                                                                        </span>
                                                                    <?php } ?>
                                                                </div>
                                                            </a>
                                                        <?php }
                                                    }
                                                }
                                            } ?>
                                        </div>
                                    <?php }
                                } ?>
                            </div>
                            <input type="hidden"
                                   value="<?= htmlspecialchars(json_encode($arResult['GROUPED_PRODUCTS'])) ?>"
                                   id="product_prop_data"/>
                            <?php /** Enterego grouped product */
                        } ?>
                        <?php if ($show_price) {
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
                                        <span class="product-item-detail-info-container-title">
                                            <?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:</span>
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
                        } ?>
                        <div class="mb-4 flex flex-col dark:text-textDark text-textLight">
                            <div class="mb-3 d-flex flex-row align-items-center">
                                <div class="product-item-detail-price-current text-3xl mb-2 font-semibold
                            dark:font-medium text-lightGrayBg dark:text-textDarkLightGray"
                                     id="<?= $itemIds['PRICE_ID'] ?>">
                                    <?= $specialPrice['PRINT_PRICE'] ?? $price['PRICE_DATA']['PRINT_PRICE']; ?>
                                </div>
                                <?php if (isset($specialPrice)) {
                                    $styles = 'price-discount';
                                    $old_sum = (int)$price['PRICE_DATA']['PRICE'] - (int)$specialPrice['PRICE'] ?? 0; ?>
                                    <span class="font-14 ml-3">
                                    <b class="decoration-color-red mr-2"><?= $price['PRICE_DATA']['PRINT_PRICE']; ?></b>
                                    <b class="sale-percent"> - <?= $old_sum ?> руб.</b>
                                </span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="dark:border-darkBox rounded-xl border-2 border-textDark">
                            <div class="new_box xl:p-5 p-3 dark:bg-darkBox rounded-t-xl bg-textDark">
                                <p class="flex flex-row items-center dark:text-grayIconLights text-dark">
                                    <svg width="45" height="48" class="mr-3 w-fit" viewBox="0 0 39 42" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M36.6667 12.3835L20 3.8418L3.33337 12.3835V29.4668L20 38.0085L36.6667 29.4668V12.3835Z"
                                              stroke="#BFBFBF" stroke-width="1.5" stroke-linejoin="round"/>
                                        <path d="M3.33337 12.3828L20 20.9245" stroke="#BFBFBF" stroke-width="1.5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 38.0081V20.9248" stroke="#BFBFBF" stroke-width="1.5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M36.6667 12.3828L20 20.9245" stroke="#BFBFBF" stroke-width="1.5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M28.3333 8.1123L11.6666 16.654" stroke="#BFBFBF" stroke-width="1.5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="xl:text-sm text-xs font-medium dark:font-light">
                                    Наличие товара, варианты и стоимость доставки будут указаны далее при оформлении
                                    заказа.
                                </span>
                                </p>
                            </div>
                            <div class="flex md:flex-row flex-col items-center xl:p-5 p-3 justify-between relative">
                                <?php if ($USER->IsAuthorized()) {
                                    if ($actualItem['PRODUCT']['QUANTITY'] != '0' && $show_price && $arResult['ADD_TO_BASKET']) { ?>
                                        <div class="md:w-auto w-full relative">
                                            <div class="mb-lg-3 mb-md-3 mb-4 flex flex-row items-center md:justify-start justify-between bx_catalog_item bx_catalog_item_controls"
                                                <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                                 data-entity="quantity-block">
                                                <div class="product-item-amount-field-contain mr-3 flex flex-row items-center">
                                    <span class="btn-minus rounded-full md:py-0 md:px-0 py-3.5 px-1.5
                                                dark:bg-dark md:dark:bg-darkBox bg-none no-select add2basket
                                                cursor-pointer flex items-center justify-center md:h-full h-auto md:w-full w-auto"
                                          data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                          data-product_id="<?= $arResult['ID']; ?>"
                                          id="<?= $itemIds['QUANTITY_DOWN_ID'] ?>"
                                          data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>">
                                        <svg width="30" height="2.3" viewBox="0 0 22 2" fill="none"
                                             class="stroke-dark dark:stroke-white stroke-2"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1H21" stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                                    <div class="product-item-amount-field-block">
                                                        <input class="product-item-amount card_element cat-det
                                         dark:bg-grayButton bg-textDarkLightGray cursor-pointer
                                    focus:border-none text-center border-none text-base
                                     shadow-none py-3.5 px-3 mx-2 outline-none rounded-md w-16"
                                                               id="<?= $itemIds['QUANTITY_ID'] ?>"
                                                               type="number" value="<?= $priceBasket ?>"
                                                               data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                                               max="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"
                                                               data-product_id="<?= $arResult['ID']; ?>"
                                                               data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"/>
                                                    </div>
                                                    <span class=" cursor-pointer flex items-center justify-center
                                                    rounded-full md:p-0 p-3 dark:bg-dark md:dark:bg-darkBox
                                                    bg-none md:h-full h-auto md:w-full w-auto btn-plus no-select
                                                     plus_icon add2basket basket_prod_detail"
                                                          data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                                          data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"
                                                          data-product_id="<?= $arResult['ID']; ?>"
                                                          id="<?= $itemIds['QUANTITY_UP_ID'] ?>">
                                         <svg width="20" height="20" viewBox="0 0 20 20"
                                              class="fill-light-red dark:fill-white"
                                              xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18.8889 11.111H1.11111C0.503704 11.111 0 10.6073 0 9.9999C0 9.3925 0.503704 8.88879 1.11111 8.88879H18.8889C19.4963 8.88879 20 9.3925 20 9.9999C20 10.6073 19.4963 11.111 18.8889 11.111Z"/>
                                            <path d="M10 20C9.39262 20 8.88892 19.4963 8.88892 18.8889V1.11111C8.88892 0.503704 9.39262 0 10 0C10.6074 0 11.1111 0.503704 11.1111 1.11111V18.8889C11.1111 19.4963 10.6074 20 10 20Z"/>
                                        </svg>
                                    </span>
                                                </div>
                                                <a id="<?= $arResult['BUY_LINK']; ?>" href="javascript:void(0)"
                                                   rel="nofollow"
                                                   class="add2basket basket_prod_detail btn red_button_cart
                                   dark:bg-dark-red bg-light-red py-3 px-4 rounded-5"
                                                   data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                                   data-product_id="<?= $arResult['ID']; ?>"
                                                   title="Добавить в корзину">
                                                    <svg width="24" height="28" viewBox="0 0 18 22" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13.6017 18.9561V15.8498M13.6017 15.8498H16.4364M13.6017 15.8498H10.767M13.6017 15.8498V14.1413V12.5881"
                                                              stroke="white" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M12.9978 7.09848H14.1597C15.2019 7.09848 16.0701 7.97388 16.1567 9.11199M12.9978 7.09848H4.98235M12.9978 7.09848V6.00055C12.9978 4.83579 12.5756 3.71874 11.8239 2.89513C11.0724 2.07153 10.053 1.60883 8.99007 1.60883C7.92712 1.60883 6.90778 2.07153 6.15618 2.89513C5.4046 3.71874 4.98235 4.83579 4.98235 6.00055V7.09848M12.9978 7.09848V9.51393M9.76502 20.2737H3.15243C1.98009 20.2737 1.05814 19.1756 1.1555 17.8954L1.82345 9.11199C1.91 7.97388 2.7782 7.09848 3.82039 7.09848H4.98235M4.98235 7.09848V9.51393"
                                                              stroke="white" stroke-width="2" stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                <div id="result_box"></div>
                                                <div id="popup_mess"></div>
                                            </div>
                                            <div class="alert_quantity absolute md:p-4 p-2 text-xs left-0 top-12
                                                 bg-filterGray dark:bg-tagFilterGray w-full shadow-lg rounded-md z-20 hidden"
                                                 data-id="<?= $arResult['ID'] ?>"></div>
                                        </div>
                                        <div class="ganerate_price_wrap mb-0 <?= ($priceBasket > 0) ? 'flex flex-row' : 'hidden' ?>">
                                            <div class="flex flex-row">
                                            <span class="text-2xl font-semibold text-dark dark:font-normal
                                             dark:text-textDarkLightGray mr-4">Итого:</span>
                                                <div class="text-2xl font-semibold text-dark dark:font-normal
                                            dark:text-textDarkLightGray ganerate_price">
                                                    <?= (round($priceCalculate) * $priceBasket) . ' ₽'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else if (!$item['ADD_TO_BASKET']) { ?>
                                        <div>
                                <span class="btn red_button_cart text-xs dark:text-textDark text-white font-medium
                                dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 open-popup md:w-auto w-full"
                                      onclick="showHidePopupPrice(this)">Подробнее</span>
                                            <div class="text-black font-extralight text-center hidden absolute p-5 shadow-lg popup-window-price
                                bg-filterGray dark:text-textDarkLightGray text-xs dark:bg-grayButton rounded-lg w-72
                                 z-50 left-0">
                                                <p class="flex justify-center">
                                                    <svg width="20" height="20" viewBox="0 0 24 24"
                                                         class="mb-3 stroke-light-red dark:stroke-white"
                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="12" cy="12" r="11" stroke-width="2"/>
                                                        <line x1="12" y1="11" x2="12" y2="18" stroke-width="2"
                                                              stroke-linecap="round" stroke-linejoin="round"/>
                                                        <line x1="12" y1="7" x2="12" y2="6" stroke-width="2"
                                                              stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                </p>
                                                У вас нет активных контрагентов для совершения покупок на этом
                                                сайте!<br>
                                                Вы можете
                                                <a href="/personal/contragents/"
                                                   class="text-light-red dark:text-white font-medium">Создать
                                                    контрагента</a>
                                                и обратиться к менеджеру <br>
                                                или перейти на наш
                                                <a href="https://oshisha.net"
                                                   class="text-light-red dark:text-white font-medium">Розничный сайт</a>
                                                <span class="absolute -right-2 -top-2 cursor-pointer"
                                                      onclick="$(this).closest('div').toggleClass('hidden')">
                                        <svg width="25" height="25" viewBox="0 0 60 60" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.7"
                                                  d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z"
                                                  fill="#676767"></path>
                                            <path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z"
                                                  fill="white"></path>
                                        </svg>
                                    </span>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="bx_catalog_item_controls mb-5 flex flex-row items-center bx_catalog_item"
                                            <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                             data-entity="quantity-block">
                                            <div class="d-flex flex-row align-items-center mr-3">
                                                <div class="product-item-amount-field-contain">
                                                                                            <span class=" no-select minus_icon add2basket basket_prod_detail mr-3"
                                                                                                  style="pointer-events: none;">
                                                                                            </span>
                                                    <div class="product-item-amount-field-block">
                                                        <input class="product-item-amount"
                                                               id="<?= $itemIds['QUANTITY_ID'] ?>"
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
                                        </div>
                                        <div class="flex justify-between items-center product-toggle bx_catalog_item_controls">
                                            <div class="not_product detail_popup text-xs dark:text-textDark text-white font-medium
                                                flex justify-center flex-row items-center dark:bg-dark-red bg-light-red py-2 px-4
                                                rounded-full text-center w-auto  <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                                                            <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>">
                                                <svg width="18" height="17" class="mr-1 stroke-white
                                                    <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>"
                                                     viewBox="0 0 34 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M25.5762 11.0001C25.5762 8.81209 24.6884 6.71367 23.1081 5.16649C21.5279 3.61932 19.3846 2.75012 17.1498 2.75012C14.915 2.75012 12.7717 3.61932 11.1915 5.16649C9.61121 6.71367 8.72344 8.81209 8.72344 11.0001C8.72344 20.6251 4.51025 23.3751 4.51025 23.3751H29.7894C29.7894 23.3751 25.5762 20.6251 25.5762 11.0001Z"
                                                          stroke-width="3" stroke-linecap="round"
                                                          stroke-linejoin="round"></path>
                                                    <path d="M19.5794 28.875C19.3325 29.2917 18.9781 29.6376 18.5517 29.8781C18.1253 30.1186 17.6419 30.2451 17.1498 30.2451C16.6577 30.2451 16.1743 30.1186 15.7479 29.8781C15.3215 29.6376 14.9671 29.2917 14.7202 28.875"
                                                          stroke-width="3" stroke-linecap="round"
                                                          stroke-linejoin="round"></path>
                                                </svg>
                                                Нет в наличии
                                            </div>
                                            <div id="popup_mess"
                                                 class="popup_mess_prods catalog_popup absolute z-20 w-auto top-full left-0 <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : '' ?>"
                                                 data-subscription_id="<?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? $arResult['ITEM_SUBSCRIPTION']['ID'] : '' ?>"
                                                 data-product_id="<?= $arResult['ID']; ?>"></div>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <div class="text-center md:text-base text-sm font-medium text-lightGrayBg dark:text-textDarkLightGray
                                    dark:font-normal w-full box_with_basket_login">
                                        Для покупки товара вам необходимо
                                        <a href="javascript:void(0)"
                                           class="text-light-red underline font-semibold dark:text-white ink_header link_header_box ">
                                            Авторизоваться
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
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
            <?php }
        } ?>
        <div class="tab-box">
            <ul class="nav nav-fill flex flex-row flex-wrap justify-content-between mb-3 mt-5"
                role="tablist">
                    <li class="nav-item text-center w-1/3 md:p-4 p-2 border-b-2 border-light-red dark:border-white pointer">
                        <a class="nav-link text-center tab-product active" id="pills-profile"
                           onclick="openTabContent(this)" href="javascript:void(0)">
                        <span class="xl:text-xl text-xs mb-4 text-lightGrayBg font-normal dark:font-light
                        dark:text-textDarkLightGray"><?= $arParams['MESS_PROPERTIES_TAB'] ?></span>
                        </a>
                    </li>
                    <?php
                if ($showDescription) { ?>
                    <li class="nav-item link text-center w-1/3 border-b-2 border-grey-line-order md:p-4 p-2
                dark:border-grayButton pointer">
                        <a class="nav-link text-center tab-product" id="pills-home" href="javascript:void(0)"
                           onclick="openTabContent(this)">
                        <span class="xl:text-xl text-xs mb-4 text-lightGrayBg font-normal dark:font-light
                        dark:text-textDarkLightGray"><?= $arParams['MESS_DESCRIPTION_TAB'] ?></span></a>
                    </li>
                    <?php
                }
                if ($arParams['USE_COMMENTS'] === 'Y') { ?>
                    <li class="nav-item link text-center w-1/3 border-b-2 border-grey-line-order md:p-4 p-2
                dark:border-grayButton pointer">
                        <a class="nav-link tab-product" id="pills-contact"
                           href="javascript:void(0)" onclick="openTabContent(this)">
                            <i class="fa fa-comment-o" aria-hidden="true"></i>
                            <span class="xl:text-xl text-xs mb-4 text-lightGrayBg font-normal dark:font-light
                        dark:text-textDarkLightGray"><?= $arParams['MESS_COMMENTS_TAB'] ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <div class="tab-content mt-5">
                <?php if ($showDescription) { ?>
                    <div class="tab-pane hidden md:mt-8 mt-5" id="pills-home">
                        <?php if ($arResult['DETAIL_TEXT'] != '') {
                            echo $arResult['DETAIL_TEXT_TYPE'] === 'html' ? $arResult['DETAIL_TEXT'] :
                                '<p class="xl:text-sm text-xs mb-4 text-lightGrayBg font-normal dark:font-light
                        dark:text-textDarkLightGray">' . $arResult['DETAIL_TEXT'] . '</p>';
                        } ?>
                    </div>
                    <?php
                }

                if (!empty($arResult['PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                    include(__DIR__ . '/props/template.php');
                }
                if ($arParams['USE_COMMENTS'] === 'Y') { ?>
                    <div class="tab-pane hidden md:mt-8 mt-5" id="pills-contact">
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
                        );
                        if (isset($arParams["USER_CONSENT"])) {
                            $componentCommentsParams["USER_CONSENT"] = $arParams["USER_CONSENT"];
                        }
                        if (isset($arParams["USER_CONSENT_ID"])) {
                            $componentCommentsParams["USER_CONSENT_ID"] = $arParams["USER_CONSENT_ID"];
                        }
                        if (isset($arParams["USER_CONSENT_IS_CHECKED"])) {
                            $componentCommentsParams["USER_CONSENT_IS_CHECKED"] = $arParams["USER_CONSENT_IS_CHECKED"];
                        }
                        if (isset($arParams["USER_CONSENT_IS_LOADED"])) {
                            $componentCommentsParams["USER_CONSENT_IS_LOADED"] = $arParams["USER_CONSENT_IS_LOADED"];
                        }
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
    <div>
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
                                            <option
                                                    value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"selected"' : '') ?>>
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
            ECONOMY_INFO_MESSAGE: '<?= GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2') ?>',
            TITLE_ERROR: '<?= GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR') ?>',
            TITLE_BASKET_PROPS: '<?= GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS') ?>',
            BASKET_UNKNOWN_ERROR: '<?= GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR') ?>',
            BTN_SEND_PROPS: '<?= GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS') ?>',
            BTN_MESSAGE_BASKET_REDIRECT: '<?= GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT') ?>',
            BTN_MESSAGE_CLOSE: '<?= GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE') ?>',
            BTN_MESSAGE_CLOSE_POPUP: '<?= GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP') ?>',
            TITLE_SUCCESSFUL: '<?= GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK') ?>',
            COMPARE_MESSAGE_OK: '<?= GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK') ?>',
            COMPARE_UNKNOWN_ERROR: '<?= GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR') ?>',
            COMPARE_TITLE: '<?= GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE') ?>',
            BTN_MESSAGE_COMPARE_REDIRECT: '<?= GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT') ?>',
            PRODUCT_GIFT_LABEL: '<?= GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL') ?>',
            PRICE_TOTAL_PREFIX: '<?= GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX') ?>',
            RELATIVE_QUANTITY_MANY: '<?= CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY']) ?>',
            RELATIVE_QUANTITY_FEW: '<?= CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW']) ?>',
            SITE_ID: '<?= CUtil::JSEscape($component->getSiteId()) ?>'
        });

        let <?= $obName ?> = new JCCatalogElement(<?= CUtil::PhpToJSObject($jsParams, false, true) ?>);


    </script>
<?php unset($actualItem, $itemIds, $jsParams);
