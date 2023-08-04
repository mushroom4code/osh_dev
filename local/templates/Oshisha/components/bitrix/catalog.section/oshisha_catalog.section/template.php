<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Sale\Fuser;
use Bitrix\Catalog;
use DataBase_like;


/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var $USER CAllUser|CUser
 * @var string $componentPath
 *
 *  _________________________________________________________________________
 * |    Attention!
 * |    The following comments are for system use
 * |    and are required for the component to work correctly in ajax mode:
 * |    <!-- items-container -->
 * |    <!-- pagination-container -->
 * |    <!-- component-end -->
 * /     <!-- ajax_filter -->
 */

$this->setFrameMode(true);
if (\Enterego\EnteregoHitsHelper::checkIfHits($APPLICATION)) {
    $APPLICATION->IncludeComponent(
        "bitrix:enterego.slider",
        ".default",
        array_merge($arParams, $arResult),
        false
    );

    return;
} 

if (!empty($arResult['NAV_RESULT'])) {
    $navParams = array(
        'NavPageCount' => $arResult['NAV_RESULT']->NavPageCount,
        'NavPageNomer' => $arResult['NAV_RESULT']->NavPageNomer,
        'NavNum' => $arResult['NAV_RESULT']->NavNum
    );
} else {
    $navParams = array(
        'NavPageCount' => 1,
        'NavPageNomer' => 1,
        'NavNum' => $this->randString()
    );
}

$showTopPager = false;
$showBottomPager = false;
$showLazyLoad = false;

if ($arParams['PAGE_ELEMENT_COUNT'] > 0 && $navParams['NavPageCount'] > 1) {
    $showTopPager = $arParams['DISPLAY_TOP_PAGER'];
    $showBottomPager = $arParams['DISPLAY_BOTTOM_PAGER'];
    $showLazyLoad = $arParams['LAZY_LOAD'] === 'Y' && $navParams['NavPageNomer'] != $navParams['NavPageCount'];
}

$templateLibrary = array('popup', 'ajax', 'fx');
$currencyList = '';

if (!empty($arResult['CURRENCIES'])) {
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = array(
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList
);
unset($currencyList, $templateLibrary);

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));

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

$arParams['~MESS_BTN_BUY'] = $arParams['~MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_BUY');
$arParams['~MESS_BTN_DETAIL'] = $arParams['~MESS_BTN_DETAIL'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_DETAIL');
$arParams['~MESS_BTN_COMPARE'] = $arParams['~MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_COMPARE');
$arParams['~MESS_BTN_SUBSCRIBE'] = $arParams['~MESS_BTN_SUBSCRIBE'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_SUBSCRIBE');
$arParams['~MESS_BTN_ADD_TO_BASKET'] = $arParams['~MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET');
$arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE');
$arParams['~MESS_SHOW_MAX_QUANTITY'] = $arParams['~MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCS_CATALOG_SHOW_MAX_QUANTITY');
$arParams['~MESS_RELATIVE_QUANTITY_MANY'] = $arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['~MESS_RELATIVE_QUANTITY_FEW'] = $arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_FEW');

$arParams['MESS_BTN_LAZY_LOAD'] = $arParams['MESS_BTN_LAZY_LOAD'] ?: Loc::getMessage('CT_BCS_CATALOG_MESS_BTN_LAZY_LOAD');

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
    'FILL_ITEM_ALL_PRICES' => 'Y',
    'DISCOUNTS' => $arResult['DISCOUNTS'],
    'IBLOCKS_DISCOUNTS' => $arResult['IBLOCKS_DISCOUNTS']
);

$obName = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($navParams['NavNum']));
$containerName = 'container-' . $navParams['NavNum'];

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';


$fUser = CSaleBasket::GetBasketUserID();
$arBasketItems = array();
$dbBasketItems = CSaleBasket::GetList(
    array("NAME" => "ASC", "ID" => "ASC"),
    array("FUSER_ID" => $fUser, "LID" => SITE_ID, "ORDER_ID" => "NULL"),
    false,
    false,
    array("ID", "PRODUCT_ID", "QUANTITY",)
);
while ($arItems = $dbBasketItems->Fetch()) {
    if (strlen($arItems["CALLBACK_FUNC"]) > 0) {
        CSaleBasket::UpdatePrice($arItems["ID"],
            $arItems["CALLBACK_FUNC"],
            $arItems["MODULE"],
            $arItems["PRODUCT_ID"],
            $arItems["QUANTITY"]);
        $arItems = CSaleBasket::GetByID($arItems["ID"]);
    }

    $arBasketItems[$arItems["PRODUCT_ID"]] = $arItems["QUANTITY"];
}
// Печатаем массив, содержащий актуальную на текущий момент корзину

global $arrFilterTop;
$arrFilterTop = array();

$basketUserId = (int)$fUser;
if ($basketUserId <= 0) {
    $ids = array();
}
$ids = array_values(Catalog\CatalogViewedProductTable::getProductSkuMap(
    IBLOCK_CATALOG,
    $arResult['VARIABLES']['SECTION_ID'],
    $basketUserId,
    $arParams['SECTION_ELEMENT_ID'],
    $arParams['PAGE_ELEMENT_COUNT'],
    $arParams['DEPTH']
));

$arrFilterTop['ID'] = $ids;

// получение лайков и избранного для всех элементов каталога НАЧАЛО

$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
$item_id = $prop_see_in_window = [];

foreach ($arResult['ITEMS'] as $item) {
    $item_id[] = $item['ID'];
}

$iblock_id = IBLOCK_CATALOG;
$resQuery = Enterego\EnteregoSettings::getPropSetting($iblock_id, 'SEE_POPUP_WINDOW');
if (!empty($resQuery)) {
    while ($collectionPropChecked = $resQuery->Fetch()) {
        $prop_see_in_window[$collectionPropChecked['CODE']] = $collectionPropChecked;
    }
}

$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);
// получение лайков и избранного для всех элементов каталога КОНЕЦ


    ?>
<div class="row<?= $themeClass ?>">
    <div class="col p-0">

        <?php

        //region Pagination
        if ($showTopPager) {
            ?>
            <div class="row mb-4">
                <div class="col text-center" data-pagination-num="<?= $navParams['NavNum'] ?>">
                    <!-- pagination-container -->
                    <?= $arResult['NAV_STRING'] ?>
                    <!-- pagination-container -->
                </div>
            </div>
            <?php
        }
        //endregion

        $col_orientation = $_COOKIE['orientation'] === 'line' ? 'by-line' : 'by-card';
        $classPosition = '';
        $classOpt = '';
        if ($GLOBALS['UserTypeOpt'] === true) {
            $classPosition = 'justify-content-between';
            $classOpt = 'opt';
        } else {
            $classPosition = 'justify-content-end';
        } ?>

        <div class="mb-4 catalog-section <?= $col_orientation . ' ' . $classOpt ?>" data-entity="<?= $containerName ?>">
            <!-- items-container -->
            <?php if (!empty($arResult['ITEMS']) && !empty($arResult['ITEM_ROWS'])) {

                    $areaIds = array();
                    global $option_site;
                    foreach ($arResult['ITEMS'] as &$elem) {

                        if ($elem['PROPERTIES']['SEE_PRODUCT_AUTH']['VALUE'] == 'Нет') {
                            if ($GLOBALS['SEE_PRODUCT_AUTH_' . $arResult['ID']] !== 'Нет'){
                                $GLOBALS['SEE_PRODUCT_AUTH_' . $arResult['ID']] = 'Нет'; ?>
                                <script type="application/javascript">
                                    $(document).find('.message_for_user_minzdrav').text('<?=$option_site->text_rospetrebnadzor_catalog?>');
                                </script>
                            <?php
                            }
                        }

                        $uniqueId = $elem['ID'] . '_' . md5($this->randString() . $component->getAction());
                        $areaIds[$elem['ID']] = $this->GetEditAreaId($uniqueId);
                        $this->AddEditAction($uniqueId, $elem['EDIT_LINK'], $elementEdit);
                        $this->AddDeleteAction($uniqueId, $elem['DELETE_LINK'], $elementDelete, $elementDeleteParams);
                    }
            foreach ($arResult['ITEM_ROWS'] as $rowData) {
            $rowItems = array_splice($arResult['ITEMS'], 0, $rowData['COUNT']);

            if ($_COOKIE['items'] === 'line') {
                $cols = 'col-md-12 col-lg-12';
                $rowData['CLASS'] = 'product-item-list-col-12';
            } else {
                $cols = 'col-md-5 col-lg-3';
            }?>
                <div class="row <?= $rowData['CLASS'] ?> products_box" data-entity="items-row">
                    <?php
                    switch ($rowData['VARIANT']) {
                        case 0:
                            ?>
                            <div class="col product-item-big-card bx_catalog_item double">
                                <?php
                                $item = reset($rowItems);
                                $APPLICATION->IncludeComponent(
                                    'bitrix:catalog.item',
                                    'oshisha_catalog.item',
                                    array(
                                        'RESULT' => array(
                                            'ITEM' => $item,
                                            'AREA_ID' => $areaIds[$item['ID']],
                                            'TYPE' => $rowData['TYPE'],
                                            'BIG_LABEL' => 'N',
                                            'BIG_DISCOUNT_PERCENT' => 'N',
                                            'BIG_BUTTONS' => 'N',
                                            'SCALABLE' => 'N',
                                            'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                            'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
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
                            break;

                        case 1:
                            foreach ($rowItems as $item) {
                                ?>
                                <div class="col-6 product-item-big-card bx_catalog_item double">

                                    <?php

                                    $APPLICATION->IncludeComponent(
                                        'bitrix:catalog.item',
                                        'oshisha_catalog.item',
                                        array(
                                            'RESULT' => array(
                                                'ITEM' => $item,
                                                'AREA_ID' => $areaIds[$item['ID']],
                                                'TYPE' => $rowData['TYPE'],
                                                'BIG_LABEL' => 'N',
                                                'BIG_DISCOUNT_PERCENT' => 'N',
                                                'BIG_BUTTONS' => 'N',
                                                'SCALABLE' => 'N',
                                                'AR_BASKET' => $arBasketItems,
                                                'F_USER_ID' => $FUser_id,
                                                'ID_PROD' => $item['ID'],
                                                'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
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
                            break;

                        case 2:
                        case 3:
                            foreach ($rowItems as $item) {
                                foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                                    if ($keyLike == $item['ID']) {
                                        $item['COUNT_LIKES'] = $count;
                                    }
                                }
                                foreach ($count_likes['USER'] as $keyLike => $count) {
                                    if ($keyLike == $item['ID']) {
                                        $item['COUNT_LIKE'] = $count['Like'][0];
                                        $item['COUNT_FAV'] = $count['Fav'][0];
                                    }
                                }
                                ?>
                                <div class="product-item-small-card">
                                    <?php $APPLICATION->IncludeComponent(
                                        'bitrix:catalog.item',
                                        'oshisha_catalog.item',
                                        array(
                                            'RESULT' => array(
                                                'ITEM' => $item,
                                                'AREA_ID' => $areaIds[$item['ID']],
                                                'TYPE' => $rowData['TYPE'],
                                                'BIG_LABEL' => 'N',
                                                'BIG_DISCOUNT_PERCENT' => 'N',
                                                'BIG_BUTTONS' => 'Y',
                                                'SCALABLE' => 'N',
                                                'AR_BASKET' => $arBasketItems,
                                                'F_USER_ID' => $FUser_id,
                                                'ID_PROD' => $item['ID'],
                                                'COUNT_LIKE' => $item['COUNT_LIKE'],
                                                'POPUP_PROPS' => $prop_see_in_window,
                                                'COUNT_FAV' => $item['COUNT_FAV'],
                                                'COUNT_LIKES' => $item['COUNT_LIKES'],
                                                'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
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
                            break;

                        case 4:
                            $rowItemsCount = count($rowItems);
                            foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                                if ($keyLike == $item['ID']) {
                                    $item['COUNT_LIKES'] = $count;
                                }
                            }
                            foreach ($count_likes['USER'] as $keyLike => $count) {
                                if ($keyLike == $item['ID']) {
                                    $item['COUNT_LIKE'] = $count['Like'][0];
                                    $item['COUNT_FAV'] = $count['Fav'][0];
                                }
                            }
                            ?>
                            <div class="col-sm-6 product-item-big-card bx_catalog_item double">
                                <?php
                                $item = array_shift($rowItems);
                                $APPLICATION->IncludeComponent(
                                    'bitrix:catalog.item',
                                    'oshisha_catalog.item',
                                    array(
                                        'RESULT' => array(
                                            'ITEM' => $item,
                                            'AREA_ID' => $areaIds[$item['ID']],
                                            'TYPE' => $rowData['TYPE'],
                                            'BIG_LABEL' => 'N',
                                            'BIG_DISCOUNT_PERCENT' => 'N',
                                            'BIG_BUTTONS' => 'Y',
                                            'SCALABLE' => 'Y',
                                            'AR_BASKET' => $arBasketItems,
                                            'F_USER_ID' => $FUser_id,
                                            'ID_PROD' => $item['ID'],
                                            'POPUP_PROPS' => $prop_see_in_window,
                                            'COUNT_LIKE' => $item['COUNT_LIKE'],
                                            'COUNT_FAV' => $item['COUNT_FAV'],
                                            'COUNT_LIKES' => $item['COUNT_LIKES'],
                                            'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                            'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                        ),
                                        'PARAMS' => $generalParams
                                            + array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                unset($item);
                                ?>
                                <div id="result_box"></div>
                            </div>
                            <div class="col-sm-6 product-item-small-card bx_catalog_item double">
                                <div class="row">
                                    <?php
                                    for ($i = 0; $i < $rowItemsCount - 1; $i++) {
                                        ?>
                                        <div class="col-6">
                                            <?php
                                            $APPLICATION->IncludeComponent(
                                                'bitrix:catalog.item',
                                                'oshisha_catalog.item',
                                                array(
                                                    'RESULT' => array(
                                                        'ITEM' => $rowItems[$i],
                                                        'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
                                                        'TYPE' => $rowData['TYPE'],
                                                        'BIG_LABEL' => 'N',
                                                        'BIG_DISCOUNT_PERCENT' => 'N',
                                                        'BIG_BUTTONS' => 'N',
                                                        'SCALABLE' => 'N',
                                                        'AR_BASKET' => $arBasketItems,
                                                        'F_USER_ID' => $FUser_id,
                                                        'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                        'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                                    ),
                                                    'PARAMS' => $generalParams
                                                        + array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
                                                ),
                                                $component,
                                                array('HIDE_ICONS' => 'Y')
                                            );
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div id="result_box"></div>
                            </div>
                            <?php
                            break;

                        case 5:
                            $rowItemsCount = count($rowItems);
                            ?>
                            <div class="col-sm-6 col-12 product-item-small-card bx_catalog_item double">
                                <div class="row">
                                    <?php
                                    for ($i = 0; $i < $rowItemsCount - 1; $i++) {
                                        ?>
                                        <div class="col-6">
                                            <?php
                                            $APPLICATION->IncludeComponent(
                                                'bitrix:catalog.item',
                                                'oshisha_catalog.item',
                                                array(
                                                    'RESULT' => array(
                                                        'ITEM' => $rowItems[$i],
                                                        'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
                                                        'TYPE' => $rowData['TYPE'],
                                                        'BIG_LABEL' => 'N',
                                                        'BIG_DISCOUNT_PERCENT' => 'N',
                                                        'BIG_BUTTONS' => 'N',
                                                        'SCALABLE' => 'N',
                                                        'AR_BASKET' => $arBasketItems,
                                                        'F_USER_ID' => $FUser_id,
                                                        'ID_PROD' => $item['ID'],
                                                        'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                        'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                                    ),
                                                    'PARAMS' => $generalParams
                                                        + array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
                                                ),
                                                $component,
                                                array('HIDE_ICONS' => 'Y')
                                            );
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div id="result_box"></div>
                            </div>
                            <div class="col-sm-6 product-item-big-card bx_catalog_item double">
                                <?php
                                $item = end($rowItems);
                                $APPLICATION->IncludeComponent(
                                    'bitrix:catalog.item',
                                    'oshisha_catalog.item',
                                    array(
                                        'RESULT' => array(
                                            'ITEM' => $item,
                                            'AREA_ID' => $areaIds[$item['ID']],
                                            'TYPE' => $rowData['TYPE'],
                                            'BIG_LABEL' => 'N',
                                            'BIG_DISCOUNT_PERCENT' => 'N',
                                            'BIG_BUTTONS' => 'Y',
                                            'SCALABLE' => 'Y',
                                            'AR_BASKET' => $arBasketItems,
                                            'F_USER_ID' => $FUser_id,
                                            'ID_PROD' => $item['ID'],
                                            'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                            'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                        ),
                                        'PARAMS' => $generalParams
                                            + array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                unset($item);
                                ?>
                                <div id="result_box"></div>
                            </div>
                            <?php
                            break;

                        case 6:
                            foreach ($rowItems as $item) {
                                foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                                    if ($keyLike == $item['ID']) {
                                        $item['COUNT_LIKES'] = $count;
                                    }
                                }
                                foreach ($count_likes['USER'] as $keyLike => $count) {
                                    if ($keyLike == $item['ID']) {
                                        $item['COUNT_LIKE'] = $count['Like'][0];
                                        $item['COUNT_FAV'] = $count['Fav'][0];
                                    }
                                }
                                ?>
                                <div class="col-6 col-sm-4 col-md-4 col-lg-2 product-item-small-card bx_catalog_item double">
                                    <?php
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:catalog.item',
                                        'oshisha_catalog.item',
                                        array(
                                            'RESULT' => array(
                                                'ITEM' => $item,
                                                'AREA_ID' => $areaIds[$item['ID']],
                                                'TYPE' => $rowData['TYPE'],
                                                'BIG_LABEL' => 'N',
                                                'BIG_DISCOUNT_PERCENT' => 'N',
                                                'BIG_BUTTONS' => 'N',
                                                'SCALABLE' => 'N',
                                                'AR_BASKET' => $arBasketItems,
                                                'F_USER_ID' => $FUser_id,
                                                'ID_PROD' => $item['ID'],
                                                'POPUP_PROPS' => $prop_see_in_window,
                                                'COUNT_LIKE' => $item['COUNT_LIKE'],
                                                'COUNT_FAV' => $item['COUNT_FAV'],
                                                'COUNT_LIKES' => $item['COUNT_LIKES'],
                                                'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
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

                            break;

                        case 7:
                            $rowItemsCount = count($rowItems);
                            ?>
                            <div class="col-sm-6 col-12 product-item-big-card bx_catalog_item double">
                                <?php
                                $item = array_shift($rowItems);
                                $APPLICATION->IncludeComponent(
                                    'bitrix:catalog.item',
                                    'oshisha_catalog.item',
                                    array(
                                        'RESULT' => array(
                                            'ITEM' => $item,
                                            'AREA_ID' => $areaIds[$item['ID']],
                                            'TYPE' => $rowData['TYPE'],
                                            'BIG_LABEL' => 'N',
                                            'BIG_DISCOUNT_PERCENT' => 'N',
                                            'BIG_BUTTONS' => 'Y',
                                            'SCALABLE' => 'Y',
                                            'AR_BASKET' => $arBasketItems,
                                            'F_USER_ID' => $FUser_id,
                                            'ID_PROD' => $item['ID'],
                                            'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                            'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                        ),
                                        'PARAMS' => $generalParams
                                            + array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                unset($item);
                                ?>
                                <div id="result_box"></div>
                            </div>
                            <div class="col-sm-6 col-12 product-item-small-card">
                                <div class="row">
                                    <?php
                                    for ($i = 0; $i < $rowItemsCount - 1; $i++) {
                                        ?>
                                        <div class="col-6 col-md-4">
                                            <?php
                                            $APPLICATION->IncludeComponent(
                                                'bitrix:catalog.item',
                                                'oshisha_catalog.item',
                                                array(
                                                    'RESULT' => array(
                                                        'ITEM' => $rowItems[$i],
                                                        'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
                                                        'TYPE' => $rowData['TYPE'],
                                                        'BIG_LABEL' => 'N',
                                                        'BIG_DISCOUNT_PERCENT' => 'N',
                                                        'BIG_BUTTONS' => 'N',
                                                        'SCALABLE' => 'N',
                                                        'AR_BASKET' => $arBasketItems,
                                                        'F_USER_ID' => $FUser_id,
                                                        'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                        'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                                    ),
                                                    'PARAMS' => $generalParams
                                                        + array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
                                                ),
                                                $component,
                                                array('HIDE_ICONS' => 'Y')
                                            );
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                            break;

                        case 8:
                            $rowItemsCount = count($rowItems);
                            ?>
                            <div class="col-sm-6 col-12 product-item-small-card">
                                <div class="row">
                                    <?php
                                    for ($i = 0; $i < $rowItemsCount - 1; $i++) {
                                        ?>
                                        <div class="col-6 col-md-4">
                                            <?php
                                            $APPLICATION->IncludeComponent(
                                                'bitrix:catalog.item',
                                                'oshisha_catalog.item',
                                                array(
                                                    'RESULT' => array(
                                                        'ITEM' => $rowItems[$i],
                                                        'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
                                                        'TYPE' => $rowData['TYPE'],
                                                        'BIG_LABEL' => 'N',
                                                        'BIG_DISCOUNT_PERCENT' => 'N',
                                                        'BIG_BUTTONS' => 'N',
                                                        'SCALABLE' => 'N',
                                                        'AR_BASKET' => $arBasketItems,
                                                        'F_USER_ID' => $FUser_id,
                                                        'ID_PROD' => $item['ID'],
                                                        'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                                        'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                                    ),
                                                    'PARAMS' => $generalParams
                                                        + array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
                                                ),
                                                $component,
                                                array('HIDE_ICONS' => 'Y')
                                            );
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12 product-item-big-card">
                                <?php
                                $item = end($rowItems);
                                $APPLICATION->IncludeComponent(
                                    'bitrix:catalog.item',
                                    'oshisha_catalog.item',
                                    array(
                                        'RESULT' => array(
                                            'ITEM' => $item,
                                            'AREA_ID' => $areaIds[$item['ID']],
                                            'TYPE' => $rowData['TYPE'],
                                            'BIG_LABEL' => 'N',
                                            'BIG_DISCOUNT_PERCENT' => 'N',
                                            'BIG_BUTTONS' => 'Y',
                                            'SCALABLE' => 'Y',
                                            'AR_BASKET' => $arBasketItems,
                                            'F_USER_ID' => $FUser_id,
                                            'ID_PROD' => $item['ID'],
                                            'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                            'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                        ),
                                        'PARAMS' => $generalParams
                                            + array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                unset($item);
                                ?>
                            </div>
                            <?php
                            break;

                        case 9:
                            foreach ($rowItems as $item) {
                                foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                                    if ($keyLike == $item['ID']) {
                                        $item['COUNT_LIKES'] = $count;
                                    }
                                }
                                foreach ($count_likes['USER'] as $keyLike => $count) {
                                    if ($keyLike == $item['ID']) {
                                        $item['COUNT_LIKE'] = $count['Like'][0];
                                        $item['COUNT_FAV'] = $count['Fav'][0];
                                    }
                                }
                                ?>
                                <div class="col product-item-line-card">
                                    <?php $APPLICATION->IncludeComponent('bitrix:catalog.item', 'oshisha_catalog.item:line', array(
                                        'RESULT' => array(
                                            'ITEM' => $item,
                                            'AREA_ID' => $areaIds[$item['ID']],
                                            'TYPE' => $rowData['TYPE'],
                                            'BIG_LABEL' => 'N',
                                            'BIG_DISCOUNT_PERCENT' => 'N',
                                            'BIG_BUTTONS' => 'N',
                                            'AR_BASKET' => $arBasketItems,
                                            'F_USER_ID' => $FUser_id,
                                            'ID_PROD' => $item['ID'],
                                            'POPUP_PROPS' => $prop_see_in_window,
                                            'COUNT_LIKE' => $item['COUNT_LIKE'],
                                            'COUNT_FAV' => $item['COUNT_FAV'],
                                            'COUNT_LIKES' => $item['COUNT_LIKES'],
                                            'IS_SUBSCRIPTION_PAGE'=>$arParams['IS_SUBSCRIPTION_PAGE'],
                                            'CURRENT_USER_SUBSCRIPTIONS' => $arResult['CURRENT_USER_SUBSCRIPTIONS']
                                        ),
                                        'PARAMS' => $generalParams
                                            + array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
                                    ),
                                        $component,
                                        array('HIDE_ICONS' => 'Y')
                                    );
                                    ?>
                                </div>
                                <?php
                            }

                            break;
                    }
                    ?>
                </div>
                <?php
            }
                unset($generalParams, $rowItems);

            } else { ?>
                <p> В этой категроии сейчас нет товаров</p>
            <?php } ?>
            <!-- items-container -->
        </div>
        <?php

        //region LazyLoad Button
        if ($showLazyLoad) {
            ?>
            <div class="text-center mb-4" data-entity="lazy-<?= $containerName ?>">
                <button type="button"
                        class="btn text_catalog_button link_red_button btn-md"
                        style="margin: 15px;"
                        data-use="show-more-<?= $navParams['NavNum'] ?>">
                    <?= $arParams['MESS_BTN_LAZY_LOAD'] ?>
                </button>
            </div>
            <?php
        }
        //endregion

        //region Pagination
        if ($showBottomPager) {
            ?>
            <div class=" mb-4 col_navigation d-flex justify-content-end">
                <div class=" text-center" data-pagination-num="<?= $navParams['NavNum'] ?>">
                    <!-- pagination-container -->
                    <?= $arResult['NAV_STRING'] ?>
                    <!-- pagination-container -->
                </div>
            </div>
            <?php
        }
        //endregion
        ?>
        <? if ($USER->IsAuthorized()) {
            if (!empty($arrFilterTop['ID']) && $arParams['ACTIVE_BLOCK_YOU_SEE'] == 'Y') { ?>
                <div class="mb-5 mt-5">
                    <div data-entity="parent-container">
                        <div data-entity="header" data-showed="false">
                            <h4 class="font-19"><b>Вы смотрели</b></h4>
                        </div>
                        <div class="by-card viewed-slider">
                            <?php $APPLICATION->IncludeComponent(
                                "bitrix:catalog.top",
                                "oshisha_catalog.top",
                                array(
                                    "ACTION_VARIABLE" => "action",
                                    "PRODUCTS_VIEWED" => "Y",
                                    "ADD_PICT_PROP" => "-",
                                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                                    "ADD_TO_BASKET_ACTION" => "ADD",
                                    "BASKET_URL" => "/personal/basket.php",
                                    "CACHE_FILTER" => "N",
                                    "CACHE_GROUPS" => "Y",
                                    "CACHE_TIME" => "36000000",
                                    "CACHE_TYPE" => "A",
                                    "COMPARE_NAME" => "CATALOG_COMPARE_LIST",
                                    "COMPATIBLE_MODE" => "Y",
                                    "COMPONENT_TEMPLATE" => "oshisha_catalog.top",
                                    "CONVERT_CURRENCY" => "N",
                                    "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                                    "DETAIL_URL" => "",
                                    "DISPLAY_COMPARE" => "N",
                                    "ELEMENT_COUNT" => "16",
                                    "ELEMENT_SORT_FIELD" => "timestamp_x",
                                    "ELEMENT_SORT_FIELD2" => "id",
                                    "ELEMENT_SORT_ORDER" => "asc",
                                    "ELEMENT_SORT_ORDER2" => "desc",
                                    "ENLARGE_PRODUCT" => "PROP",
                                    "ENLARGE_PROP" => "-",
                                    "FILTER_NAME" => "arrFilterTop",
                                    "HIDE_NOT_AVAILABLE" => "Y",
                                    "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                                    "IBLOCK_ID" => IBLOCK_CATALOG,
                                    "IBLOCK_TYPE" => "1c_catalog",
                                    "LABEL_PROP" => array(),
                                    "LABEL_PROP_MOBILE" => "",
                                    "LABEL_PROP_POSITION" => "top-left",
                                    "LINE_ELEMENT_COUNT" => "4",
                                    "MESS_BTN_ADD_TO_BASKET" => "Забронировать",
                                    "MESS_BTN_BUY" => "Купить",
                                    "MESS_BTN_COMPARE" => "Сравнить",
                                    "MESS_BTN_DETAIL" => "Подробнее",
                                    "MESS_NOT_AVAILABLE" => "Нет в наличии",
                                    "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
                                        "OFFERS_FIELD_CODE" => $arParams["OFFERS_FIELD_CODE"],
                                    "OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],
                                    "OFFERS_LIMIT" => "4",
                                    "OFFERS_SORT_FIELD" => "sort",
                                    "OFFERS_SORT_FIELD2" => "id",
                                    "OFFERS_SORT_ORDER" => "asc",
                                    "OFFERS_SORT_ORDER2" => "desc",
                                    "OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
                                    "PARTIAL_PRODUCT_PROPERTIES" => "N",
                                    "PRICE_CODE" => BXConstants::PriceCode(),
                                    "FILL_ITEM_ALL_PRICES" => "Y",
                                    "PRICE_VAT_INCLUDE" => "Y",
                                    "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
                                    "PRODUCT_DISPLAY_MODE" => "Y",
                                    "PRODUCT_ID_VARIABLE" => "id",
                                    "PRODUCT_PROPS_VARIABLE" => "prop",
                                    "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                                    "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
                                    "PRODUCT_SUBSCRIPTION" => "Y",
                                    "PROPERTY_CODE_MOBILE" => "",
                                    "ROTATE_TIMER" => "30",
                                    "SECTION_URL" => "",
                                    "SEF_MODE" => "N",
                                    "SHOW_CLOSE_POPUP" => "N",
                                    "SHOW_DISCOUNT_PERCENT" => "N",
                                    "SHOW_MAX_QUANTITY" => "N",
                                    "SHOW_OLD_PRICE" => "N",
                                    "SHOW_PAGINATION" => "Y",
                                    "SHOW_PRICE_COUNT" => "1",
                                    "SHOW_SLIDER" => "Y",
                                    "SLIDER_INTERVAL" => "3000",
                                    "SLIDER_PROGRESS" => "N",
                                    "TEMPLATE_THEME" => "blue",
                                    "USE_ENHANCED_ECOMMERCE" => "N",
                                    "USE_PRICE_COUNT" => "N",
                                    "USE_PRODUCT_QUANTITY" => "N",
                                    "VIEW_MODE" => "SLIDER",
                                    "BASKET_ITEMS" => $arBasketItems
                                ),
                                false
                            ); ?>
                        </div>
                    </div>
                </div>
            <? }
        }
        //region Description
        if (($arParams['HIDE_SECTION_DESCRIPTION'] !== 'Y') && !empty($arResult['DESCRIPTION'])) {
            ?>
            <div class="row mb-4">
                <div class="col catalog-section-description">
                    <p><?= $arResult['DESCRIPTION'] ?></p>
                </div>
            </div>
            <?php
        }
        //endregion
        $signer = new Signer;
        $signedTemplate = $signer->sign($templateName, 'catalog.section');
        $signedParams = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'catalog.section');

        //enterego filter for special group category
        $staticFilter = [];
        if (!empty($GLOBALS[$arParams['FILTER_NAME']]['PROPERTY_USE_DISCOUNT_VALUE'])) {
            $staticFilter['PROPERTY_USE_DISCOUNT_VALUE'] = $GLOBALS[$arParams['FILTER_NAME']]['PROPERTY_USE_DISCOUNT_VALUE'];
        }

        ?>
        <!-- ajax_filter --><?php
        if ($_REQUEST['ajax_filter'] && $_REQUEST['ajax_filter'] === 'y') {
            echo(CUtil::JSEscape($signedParams));
        }
        ?><!-- ajax_filter -->
        <!-- nav_ajax_filter --><?php
        if ($_REQUEST['ajax_filter'] && $_REQUEST['ajax_filter'] === 'y') {
            echo(json_encode($navParams));
        }
        ?><!-- nav_ajax_filter -->
        <script>
            BX.message({
                BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
                BASKET_URL: '<?=$arParams['BASKET_URL']?>',
                ADD_TO_BASKET_OK: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
                TITLE_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_TITLE_ERROR')?>',
                TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCS_CATALOG_TITLE_BASKET_PROPS')?>',
                TITLE_SUCCESSFUL: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
                BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_BASKET_UNKNOWN_ERROR')?>',
                BTN_MESSAGE_SEND_PROPS: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_SEND_PROPS')?>',
                BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE')?>',
                BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
                COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_OK')?>',
                COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
                COMPARE_TITLE: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_TITLE')?>',
                PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCS_CATALOG_PRICE_TOTAL_PREFIX')?>',
                RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
                RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
                BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
                BTN_MESSAGE_LAZY_LOAD: '<?=CUtil::JSEscape($arParams['MESS_BTN_LAZY_LOAD'])?>',
                BTN_MESSAGE_LAZY_LOAD_WAITER: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_LAZY_LOAD_WAITER')?>',
                SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
            });
            let <?=$obName?> = new JCCatalogSectionComponent({
                siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
                componentPath: '<?=CUtil::JSEscape($componentPath)?>',
                navParams: <?=CUtil::PhpToJSObject($navParams)?>,
                deferredLoad: false, // enable it for deferred load
                initiallyShowHeader: '<?=!empty($arResult['ITEM_ROWS'])?>',
                bigData: <?=CUtil::PhpToJSObject($arResult['BIG_DATA'])?>,
                lazyLoad: !!'<?=$showLazyLoad?>',
                loadOnScroll: !!'<?=($arParams['LOAD_ON_SCROLL'] === 'Y')?>',
                template: '<?=CUtil::JSEscape($signedTemplate)?>',
                ajaxId: '<?=CUtil::JSEscape($arParams['AJAX_ID'])?>',
                parameters: '<?=CUtil::JSEscape($signedParams)?>',
                container: '<?=$containerName?>',
                //enterego filter for special group category
                staticFilter: <?= CUtil::PhpToJSObject($staticFilter) ?>,
                //enterego sort selector
                sortCatalogId: <?= CUtil::PhpToJSObject('.js__catalog-sort-item') ?>,
            });
        </script>

    </div>
</div><? //end wrapper?>
<!-- component-end -->
