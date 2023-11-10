<?php

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var array $item */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
/** @var array $skuTemplate */

/** @var array $templateData */
Loader::includeModule('sale');
Loader::includeModule('catalog');

$this->setFrameMode(true);

if (isset($arParams['SECTIONS_ITEMS'])) {
    $positionClassMap = array(
        'left' => 'product-item-label-left',
        'center' => 'product-item-label-center',
        'right' => 'product-item-label-right',
        'bottom' => 'product-item-label-bottom',
        'middle' => 'product-item-label-middle',
        'top' => 'product-item-label-top'
    );

    $discountPositionClass = '';
    if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
        foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
            $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
        }
    }

    $labelPositionClass = '';
    if (!empty($arParams['LABEL_PROP_POSITION'])) {
        foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
            $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
        }
    }

    $generalParams = array(
        'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
        'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
        'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
        'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
        'MESS_SHOW_MAX_QUANTITY' => $arParams['~MESS_SHOW_MAX_QUANTITY'],
        'MESS_RELATIVE_QUANTITY_MANY' => $arParams['~MESS_RELATIVE_QUANTITY_MANY'],
        'MESS_RELATIVE_QUANTITY_FEW' => $arParams['~MESS_RELATIVE_QUANTITY_FEW'],
        'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
        'USE_PRODUCT_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
        'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
        'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
        'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
        'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
        'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'],
        'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
        'COMPARE_PATH' => $arParams['COMPARE_PATH'],
        'COMPARE_NAME' => $arParams['COMPARE_NAME'],
        'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
        'PRODUCT_BLOCKS_ORDER' => $arParams['PRODUCT_BLOCKS_ORDER'],
        'LABEL_POSITION_CLASS' => $labelPositionClass,
        'DISCOUNT_POSITION_CLASS' => $discountPositionClass,
        'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
        'SLIDER_PROGRESS' => $arParams['SLIDER_PROGRESS'],
        '~BASKET_URL' => $arParams['~BASKET_URL'],
        '~ADD_URL_TEMPLATE' => $arParams['~ADD_URL_TEMPLATE'],
        '~BUY_URL_TEMPLATE' => $arParams['~BUY_URL_TEMPLATE'],
        '~COMPARE_URL_TEMPLATE' => $arParams['~COMPARE_URL_TEMPLATE'],
        '~COMPARE_DELETE_URL_TEMPLATE' => $arParams['~COMPARE_DELETE_URL_TEMPLATE'],
        'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
        'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
        'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
        'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY'],
        'MESS_BTN_BUY' => $arParams['~MESS_BTN_BUY'],
        'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
        'MESS_BTN_COMPARE' => $arParams['~MESS_BTN_COMPARE'],
        'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
        'MESS_BTN_ADD_TO_BASKET' => $arParams['~MESS_BTN_ADD_TO_BASKET'],
        'MESS_NOT_AVAILABLE' => $arParams['~MESS_NOT_AVAILABLE'],
        'PRICE_CODE' => $arParams['PRICE_CODE'],
        'FILL_ITEM_ALL_PRICES' => 'Y'
    );

    foreach ($arParams['SECTIONS_ITEMS'] as $sectionId => $section) { ?>
        <div class="by-card max-w-full">
            <?php
            $intRowsCount = 1;
            $strRand = $this->randString();
            $strContID = 'cat_top_cont_' . $strRand;
            ?>
            <div id="<?= $strContID; ?>"
                 class="col2 <?= $templateData['TEMPLATE_CLASS']; ?>">
                <div class="bx_catalog_tile_section max-w-full md:p-10 p-2" id="hits_slider_<?=$strRand?>">
                    <?php
                    $boolFirst = true;
                    $arRowIDs = array();

                    foreach ($section as $keyItem => $arItem) {
                        $strRowID = 'cat-top-' . $sectionId . '_' . $strRand;
                        $areaIds = array();
                        $uniqueId = $arItem['ID'] . '_' . md5($this->randString());
                        $areaIds[$arItem['ID']] = $this->GetEditAreaId($uniqueId);
                        $arItem['COUNT_FAV'] = '';
                        $arItem['COUNT_LIKES'] = '';
                        $arItem['COUNT_LIKE'] = '';
                        foreach ($arResult['COUNT_LIKES']['ALL_LIKE'] as $keyLike => $count) {
                            if ($keyLike == $arItem['ID']) {
                                $arItem['COUNT_LIKES'] = $count;
                            }
                        }

                        foreach ($arResult['COUNT_LIKES']['USER'] as $keyLike => $count) {
                            if ($keyLike == $arItem['ID']) {
                                $arItem['COUNT_LIKE'] = $count['Like'][0];
                                $arItem['COUNT_FAV'] = $count['Fav'][0];
                            }
                        }
                        ?>
                        <div class="product-item-small-card lg:w-72 md:w-1/3 w-1/2 h-96 pr-4 mb-7">
                            <?php $APPLICATION->IncludeComponent(
                                'bitrix:catalog.item',
                                'oshisha_catalog.item',
                                array(
                                    'RESULT' => array(
                                        'ITEM' => $arItem,
                                        'AREA_ID' => $areaIds[$item['ID']],
                                        'TYPE' => 'CARD',
                                        'BIG_LABEL' => 'N',
                                        'BIG_DISCOUNT_PERCENT' => 'N',
                                        'BIG_BUTTONS' => 'Y',
                                        'SCALABLE' => 'N',
                                        'AR_BASKET' => $arParams['BASKET_ITEMS'],
                                        'F_USER_ID' => $arResult['F_USER_ID'],
                                        'ID_PROD' => $arItem['ID'],
                                        'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                        'POPUP_PROPS' => $arResult['PROP_SEE_IN_WINDOW'],
                                        'COUNT_FAV' => $arItem['COUNT_FAV'],
                                        'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                                    ),
                                    'PARAMS' => $generalParams
                                        + array('SKU_PROPS' => $arParams['SKU_PROPS'][$item['IBLOCK_ID']])
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            );
                            ?>
                            <div id="result_box"></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}?>
<script>
    if ($('#hits_slider_<?=$strRand?>').is('.bx_catalog_tile_section')) {
        let count = 4,
            variableWidth = false;
            screenWidth = window.screen.width;
        if (screenWidth <= 1380) {
            count = 4;
        }
        if (screenWidth <= 1080) {
            count = 3;
        }
        if (screenWidth <= 746) {
            count = 2;
        }
        $('#hits_slider_<?=$strRand?>').slick({
            slidesToShow: count,
            arrows: true,
            infinite: false,
            variableWidth: variableWidth,
            prevArrow: '<span class="text-6xl cursor-pointer text-gray-slider-arrow flex items-center absolute inset-y-0 left-0"  aria-hidden="true"><i class="fa fa-angle-left"'
                + ' aria-hidden="true"></i></span>',
            nextArrow: '<span class="text-6xl cursor-pointer inset-y-0 right-0 flex items-center absolute dark:text-white" aria-hidden="true"><i class="fa fa-angle-right"'
                + ' aria-hidden="true"></i></span>',
        })
    }
</script>
