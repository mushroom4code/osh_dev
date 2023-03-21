<?php

use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use DataBase_like;
use Enterego\EnteregoBasket;

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

$intRowsCount = count($arResult['ITEMS']);
$strRand = $this->randString();
$strContID = 'cat_top_cont_' . $strRand;
$item_id = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
foreach ($arResult['ITEMS'] as $rowKey => $arOneRow) {
    foreach($arOneRow as $item) {
        $item_id[] = $item['ID'];
    }
}

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
    '~ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
    '~BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
    '~COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
    '~COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
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

$resQuery = Enterego\EnteregoSettings::getPropSetting(IBLOCK_CATALO, 'SEE_POPUP_WINDOW');
if (!empty($resQuery)) {
    while ($collectionPropChecked = $resQuery->Fetch()) {
        $prop_see_in_window[$collectionPropChecked['CODE']] = $collectionPropChecked;
    }
}

$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);

?>
<div id="<?= $strContID; ?>"
     class="bx_catalog_tile_home_type_2 col2 <?= $templateData['TEMPLATE_CLASS']; ?>">
    <div class="bx_catalog_tile_section" data-init="<?= count($arResult['ITEMS']) ?>">
        <?php
        $boolFirst = true;
        $arRowIDs = array();
        foreach ($arResult['ITEMS'] as $keyRow => $arOneRow) {
        $strRowID = 'cat-top-' . $keyRow . '_' . $strRand;
        $arRowIDs[] = $strRowID;

        foreach ($arOneRow

        as $keyItem => $arItem) {
            $strRowID = 'cat-top-' . $keyRow . '_' . $strRand;
//        $arRowIDs[] = $strRowID;
            $areaIds = array();
            $uniqueId = $arItem['ID'] . '_' . md5($this->randString() . $component->getAction());
            $areaIds[$arItem['ID']] = $this->GetEditAreaId($uniqueId);
            $arItem['COUNT_FAV'] = '';
            $arItem['COUNT_LIKES'] = '';
            $arItem['COUNT_LIKE'] = '';
            foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                if ($keyLike == $arItem['ID']) {
                    $arItem['COUNT_LIKES'] = $count;
                }
            }
            foreach ($count_likes['USER'] as $keyLike => $count) {
                if ($keyLike == $arItem['ID']) {
                    $arItem['COUNT_LIKE'] = $count['Like'][0];
                    $arItem['COUNT_FAV'] = $count['Fav'][0];
                }
            }
            ?>
            <div class="product-item-small-card">
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
                            'F_USER_ID' => $FUser_id,
                            'ID_PROD' => $arItem['ID'],
                            'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                            'POPUP_PROPS' => $prop_see_in_window,
                            'COUNT_FAV' => $arItem['COUNT_FAV'],
                            'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                        ),
                        'PARAMS' => $generalParams
                            + array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
                    ),
                    $component,
                    array('HIDE_ICONS' => 'Y')
                );
                ?>
                <div id="result_box"></div>
            </div>
            <?php
        }
    $boolFirst = false;
    } ?>
</div>
<?php
if (1 < $intRowsCount) {
    $arJSParams = array(
        'cont' => $strContID,
        'left' => array(
            'id' => $strContID . '_left_arr',
            'className' => 'bx_catalog_tile_slider_arrow_left',
            'classNameIcon' => 'fa fa-angle-left',
        ),
        'right' => array(
            'id' => $strContID . '_right_arr',
            'className' => 'bx_catalog_tile_slider_arrow_right',
            'classNameIcon' => 'fa fa-angle-right',
        ),
        'rows' => $arRowIDs,
        'rotate' => (0 < $arParams['ROTATE_TIMER']),
        'rotateTimer' => $arParams['ROTATE_TIMER']
    );
    if ('Y' == $arParams['SHOW_PAGINATION']) {
        $arJSParams['pagination'] = array(
            'id' => $strContID . '_pagination',
            'className' => 'bx_catalog_tile_slider_pagination'
        );
    }

} ?>
</div>
