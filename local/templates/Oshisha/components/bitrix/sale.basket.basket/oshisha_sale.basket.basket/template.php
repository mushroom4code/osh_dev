<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load("ui.fonts.ruble");

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 * @var Main\IO\File $jsTemplate
 */

if (!isset($arParams['DISPLAY_MODE']) || !in_array($arParams['DISPLAY_MODE'], array('extended', 'compact'))) {
    $arParams['DISPLAY_MODE'] = 'extended';
}

$arParams['USE_DYNAMIC_SCROLL'] = isset($arParams['USE_DYNAMIC_SCROLL']) && $arParams['USE_DYNAMIC_SCROLL'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_FILTER'] = isset($arParams['SHOW_FILTER']) && $arParams['SHOW_FILTER'] === 'N' ? 'N' : 'Y';
$arParams['PRICE_DISPLAY_MODE'] = isset($arParams['PRICE_DISPLAY_MODE']) && $arParams['PRICE_DISPLAY_MODE'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['TOTAL_BLOCK_DISPLAY']) || !is_array($arParams['TOTAL_BLOCK_DISPLAY'])) {
    $arParams['TOTAL_BLOCK_DISPLAY'] = array('top');
}

if (empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
    $arParams['PRODUCT_BLOCKS_ORDER'] = 'props,sku,columns';
}

if (is_string($arParams['PRODUCT_BLOCKS_ORDER'])) {
    $arParams['PRODUCT_BLOCKS_ORDER'] = explode(',', $arParams['PRODUCT_BLOCKS_ORDER']);
}

$arParams['USE_PRICE_ANIMATION'] = isset($arParams['USE_PRICE_ANIMATION']) && $arParams['USE_PRICE_ANIMATION'] === 'N' ? 'N' : 'Y';
$arParams['EMPTY_BASKET_HINT_PATH'] = isset($arParams['EMPTY_BASKET_HINT_PATH']) ? (string)$arParams['EMPTY_BASKET_HINT_PATH'] : '/';
$arParams['USE_ENHANCED_ECOMMERCE'] = isset($arParams['USE_ENHANCED_ECOMMERCE']) && $arParams['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
$arParams['DATA_LAYER_NAME'] = isset($arParams['DATA_LAYER_NAME']) ? trim($arParams['DATA_LAYER_NAME']) : 'dataLayer';
$arParams['BRAND_PROPERTY'] = isset($arParams['BRAND_PROPERTY']) ? trim($arParams['BRAND_PROPERTY']) : '';

if ($arParams['USE_GIFTS'] === 'Y') {
    $arParams['GIFTS_BLOCK_TITLE'] = isset($arParams['GIFTS_BLOCK_TITLE']) ? trim((string)$arParams['GIFTS_BLOCK_TITLE']) : Loc::getMessage('SBB_GIFTS_BLOCK_TITLE');

    CBitrixComponent::includeComponentClass('bitrix:sale.products.gift.basket');

    $giftParameters = array(
        'SHOW_PRICE_COUNT' => 1,
        'PRODUCT_SUBSCRIPTION' => 'N',
        'PRODUCT_ID_VARIABLE' => 'id',
        'USE_PRODUCT_QUANTITY' => 'N',
        'ACTION_VARIABLE' => 'actionGift',
        'ADD_PROPERTIES_TO_BASKET' => 'Y',
        'PARTIAL_PRODUCT_PROPERTIES' => 'Y',

        'BASKET_URL' => $APPLICATION->GetCurPage(),
        'APPLIED_DISCOUNT_LIST' => $arResult['APPLIED_DISCOUNT_LIST'],
        'FULL_DISCOUNT_LIST' => $arResult['FULL_DISCOUNT_LIST'],

        'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
        'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_SHOW_VALUE'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],

        'BLOCK_TITLE' => $arParams['GIFTS_BLOCK_TITLE'],
        'HIDE_BLOCK_TITLE' => $arParams['GIFTS_HIDE_BLOCK_TITLE'],
        'TEXT_LABEL_GIFT' => $arParams['GIFTS_TEXT_LABEL_GIFT'],

        'DETAIL_URL' => isset($arParams['GIFTS_DETAIL_URL']) ? $arParams['GIFTS_DETAIL_URL'] : null,
        'PRODUCT_QUANTITY_VARIABLE' => $arParams['GIFTS_PRODUCT_QUANTITY_VARIABLE'],
        'PRODUCT_PROPS_VARIABLE' => $arParams['GIFTS_PRODUCT_PROPS_VARIABLE'],
        'SHOW_OLD_PRICE' => $arParams['GIFTS_SHOW_OLD_PRICE'],
        'SHOW_DISCOUNT_PERCENT' => $arParams['GIFTS_SHOW_DISCOUNT_PERCENT'],
        'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
        'MESS_BTN_BUY' => $arParams['GIFTS_MESS_BTN_BUY'],
        'MESS_BTN_DETAIL' => $arParams['GIFTS_MESS_BTN_DETAIL'],
        'CONVERT_CURRENCY' => $arParams['GIFTS_CONVERT_CURRENCY'],
        'HIDE_NOT_AVAILABLE' => $arParams['GIFTS_HIDE_NOT_AVAILABLE'],

        'PRODUCT_ROW_VARIANTS' => '',
        'PAGE_ELEMENT_COUNT' => 0,
        'DEFERRED_PRODUCT_ROW_VARIANTS' => \Bitrix\Main\Web\Json::encode(
            SaleProductsGiftBasketComponent::predictRowVariants(
                $arParams['GIFTS_PAGE_ELEMENT_COUNT'],
                $arParams['GIFTS_PAGE_ELEMENT_COUNT']
            )
        ),
        'DEFERRED_PAGE_ELEMENT_COUNT' => $arParams['GIFTS_PAGE_ELEMENT_COUNT'],

        'ADD_TO_BASKET_ACTION' => 'BUY',
        'PRODUCT_DISPLAY_MODE' => 'Y',
        'PRODUCT_BLOCKS_ORDER' => isset($arParams['GIFTS_PRODUCT_BLOCKS_ORDER']) ? $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'] : '',
        'SHOW_SLIDER' => isset($arParams['GIFTS_SHOW_SLIDER']) ? $arParams['GIFTS_SHOW_SLIDER'] : '',
        'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
        'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',
        'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],

        'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
        'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
        'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
    );
}

\CJSCore::Init(array('fx', 'popup', 'ajax'));

$this->addExternalJs($templateFolder . '/js/mustache.js');
$this->addExternalJs($templateFolder . '/js/action-pool.js');
$this->addExternalJs($templateFolder . '/js/filter.js');
$this->addExternalJs($templateFolder . '/js/component.js');

$mobileColumns = isset($arParams['COLUMNS_LIST_MOBILE'])
    ? $arParams['COLUMNS_LIST_MOBILE']
    : $arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);
$res = CIBlockSection::GetList(array(), array(), false, $arSelect = ['ID', 'LEFT_MARGIN', 'DEPTH_LEVEL', 'NAME', 'ACTIVE']);
$jsTemplates = new Main\IO\Directory(Main\Application::getDocumentRoot() . $templateFolder . '/js-templates');

foreach ($jsTemplates->getChildren() as $jsTemplate) {
    include($jsTemplate->getPath());
}

$displayModeClass = $arParams['DISPLAY_MODE'] === 'compact' ? ' basket-items-list-wrapper-compact' : '';


if (empty($arResult['ERROR_MESSAGE'])) {
    if ($arParams['USE_GIFTS'] === 'Y' && $arParams['GIFTS_PLACE'] === 'TOP') { ?>
        <div data-entity="parent-container">
            <div class="catalog-block-header"
                 data-entity="header"
                 data-showed="false"
                 style="display: none; opacity: 0;">
                <?= $arParams['GIFTS_BLOCK_TITLE'] ?>
            </div>
            <?php $APPLICATION->IncludeComponent(
                'bitrix:sale.products.gift.basket',
                'oshisha_gift',
                $giftParameters,
                $component
            ); ?>
        </div>
        <?php
    }
    if ($arResult['BASKET_ITEM_MAX_COUNT_EXCEEDED']) { ?>
        <div id="basket-item-message">
            <?= Loc::getMessage('SBB_BASKET_ITEM_MAX_COUNT_EXCEEDED', array('#PATH#' => $arParams['PATH_TO_BASKET'])) ?>
        </div>
    <?php } ?>
    <div id="basket-root" class="bx-basket bx-<?= $arParams['TEMPLATE_THEME'] ?> bx-step-opacity flex lg:flex-row flex-col"
         style="opacity: 0;">
        <div class="md:w-2/3 w-full">
            <div class="row">
                <div class="alert alert-warning alert-dismissable" id="basket-warning" style="display: none;">
                    <span class="close" data-entity="basket-items-warning-notification-close">&times;</span>
                    <div data-entity="basket-general-warnings"></div>
                    <div data-entity="basket-item-warnings">
                        <?= Loc::getMessage('SBB_BASKET_ITEM_WARNING') ?>
                    </div>
                </div>
            </div>
            <div class="mb-3 basket-items-list-wrapper basket-items-list-wrapper-height-fixed md:pr-7 p-3 basket-items-list-wrapper-light<?= $displayModeClass ?>"
                 id="basket-items-list-wrapper">
                <div class="basket-items-list-header mb-4" data-entity="basket-items-list-header">
                    <div class="flex flex-row items-center basket-items-search-field"
                         data-entity="basket-filter">
                        <div class="relative max-w-md w-full mr-4">
                        <input type="text" class="form-control text-sm basket_input search_input dark:bg-gray-box-dark
                        bg-white border w-full border-textDarkLightGray dark:border-gray-box-dark outline-none
                   dark:text-grayIconLights py-3 pl-9 rounded-7 text-textLight"
                               data-entity="basket-filter-input" placeholder="Введите название товара"/>
                            <svg width="16" height="16" class="absolute left-2.5 h-full flex items-center top-0"
                                 viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.2583 14.075L12.425 11.25C13.3392 10.0854 13.8352 8.64722 13.8333 7.16667C13.8333 5.84813 13.4423 4.5592 12.7098 3.46287C11.9773 2.36654 10.9361 1.51206 9.71789 1.00747C8.49972 0.502889 7.15927 0.370866 5.86607 0.628101C4.57286 0.885336 3.38497 1.52027 2.45262 2.45262C1.52027 3.38497 0.885336 4.57286 0.628101 5.86607C0.370866 7.15927 0.502889 8.49972 1.00747 9.71789C1.51206 10.9361 2.36654 11.9773 3.46287 12.7098C4.5592 13.4423 5.84813 13.8333 7.16667 13.8333C8.64722 13.8352 10.0854 13.3392 11.25 12.425L14.075 15.2583C14.1525 15.3364 14.2446 15.3984 14.3462 15.4407C14.4477 15.4831 14.5567 15.5048 14.6667 15.5048C14.7767 15.5048 14.8856 15.4831 14.9872 15.4407C15.0887 15.3984 15.1809 15.3364 15.2583 15.2583C15.3364 15.1809 15.3984 15.0887 15.4407 14.9872C15.4831 14.8856 15.5048 14.7767 15.5048 14.6667C15.5048 14.5567 15.4831 14.4477 15.4407 14.3462C15.3984 14.2446 15.3364 14.1525 15.2583 14.075ZM2.16667 7.16667C2.16667 6.17776 2.45991 5.21106 3.00932 4.38882C3.55873 3.56657 4.33962 2.92571 5.25325 2.54727C6.16688 2.16883 7.17222 2.06982 8.14212 2.26274C9.11203 2.45567 10.0029 2.93187 10.7022 3.63114C11.4015 4.3304 11.8777 5.22131 12.0706 6.19122C12.2635 7.16112 12.1645 8.16646 11.7861 9.08009C11.4076 9.99372 10.7668 10.7746 9.94452 11.324C9.12228 11.8734 8.15558 12.1667 7.16667 12.1667C5.84059 12.1667 4.56882 11.6399 3.63114 10.7022C2.69345 9.76452 2.16667 8.49275 2.16667 7.16667Z" fill="black" class="fill-lightGrayBg dark:fill-textDarkLightGray"></path>
                            </svg>
                        </div>
                        <div class="d-flex flex-row box_select">
                            <?/* <select class="select_sort_basket">
                                <option>Сортировать по</option>
                                <option value="price_min">Цене: самые дешевые</option>
                                <option value="price_max">Цене: самые дорогие</option>
                            </select>*/ ?>
                            <div class="d-flex flex-row">
                                <div class="icon_sort_bar sort" id="basket-card" data-sort="grid"
                                     style="display:none;"></div>
                                <div class="icon_sort_line sort" id="basket-line" data-sort="line"
                                     style="display:none;"></div>
                                <form action="" method="POST" class="col-xs-6 BasketClearForm">
                                    <button type="submit" class="clear-cart py-2 px-3 dark:bg-lightGrayBg rounded-lg w-fit
                                    bg-textDark flex flex-row items-center dark:text-textDarkLightGray text-textLight" name="BasketClear">
                                        <svg width="30" height="30" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="0.637329" y="0.416748" width="26" height="26" rx="13" fill="#393939"/>
                                            <path d="M7.37639 6.90784L20.0664 19.5978M17.9514 9.02284L17.6694 13.2528M17.4225 16.956L17.3868 17.4919C17.3373 18.2337 17.3126 18.6046 17.1524 18.8858C17.0113 19.1334 16.7986 19.3324 16.5422 19.4567C16.2509 19.5978 15.8792 19.5978 15.1358 19.5978H12.307C11.5636 19.5978 11.1919 19.5978 10.9006 19.4567C10.6442 19.3324 10.4314 19.1334 10.2904 18.8858C10.1302 18.6046 10.1054 18.2337 10.056 17.4919L9.49139 9.02284H8.08139M16.5414 9.02284L16.1578 7.87195C15.9659 7.29619 15.4271 6.90784 14.8201 6.90784H12.6227C12.2708 6.90784 11.9418 7.0384 11.69 7.26034M13.4488 9.02284H19.3614M15.1314 14.6628V16.7778M12.3114 11.8428V16.7778"
                                                  stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="ml-2 text-sm font-medium">Очистить корзину</span>
                                    </button>
                                </form>
                                <?
                                if (isset($_POST["BasketClear"]) && CModule::IncludeModule("sale")) {
                                    CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
                                    header("Location: " . $_SERVER['REQUEST_URI']);
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="basket-items-list-container" id="basket-items-list-container">
                    <div class="basket-items-list-overlay" id="basket-items-list-overlay"
                         style="display: none;"></div>
                    <div class="basket-items-list" id="basket-item-list">
                        <div class="basket-search-not-found" id="basket-item-list-empty-result"
                             style="display: none;">
                            <div class="basket-search-not-found-icon"></div>
                            <div class="basket-search-not-found-text">
                                <?= Loc::getMessage('SBB_FILTER_EMPTY_RESULT') ?>
                            </div>
                        </div>
                        <div class="accordion mt-4" id="accordionBasket">
                            <?php
                            if (!empty($arResult['DELETED_ITEMS'])) {
                                ?>
                                <div class="box" id="deleted_products_box">
                                    <div class="card-header basket_category" id="openDeletedProducts">
                                        <button class="btn btn-link btn-block flex justify-between
                                           btn_basket_collapse" type="button"
                                                data-id-category="DeletedProducts">
                                            <span>Удаленные товары</span>
                                            <i class="fa fa-angle-down" aria-hidden="true"
                                               style="transform:rotate(180deg);">
                                                <svg width="14" height="9" viewBox="0 0 14 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0.890196 0.829872C0.503276 1.26416 0.503276 1.96835 0.890196 2.40263L5.73716 7.83798C6.51114 8.70588 7.76524 8.70554 8.53882 7.83731L13.3839 2.39863C13.7709 1.96435 13.7709 1.26016 13.3839 0.825857C12.997 0.391544 12.3697 0.391544 11.9828 0.825857L7.83588 5.48074C7.44899 5.91514 6.82164 5.91503 6.43475 5.48074L2.29133 0.829872C1.90442 0.395558 1.27711 0.395558 0.890196 0.829872Z" fill="#8B8B8B"/>
                                                </svg>
                                            </i>
                                        </button>
                                    </div>
                                    <div id="openCategoryDeletedProducts" class="category"
                                         data-id-block-category="DeletedProducts">
                                        <div class="card-body basket-items-list-table"></div>
                                    </div>
                                </div>
                                <?php
                            }
                             if (!empty($arResult['ITEMS']['nAnCanBuy'])) { ?>
                                <div class="box" id=NotAvailable">
                                    <div class="card-header basket_category alert alert-danger" id="openDNotAvailableProducts">
                                        <button class="btn btn-link btn-block d-flex justify-content-between btn_basket_collapse" type="button"
                                                data-id-category="NotAvailable">
                                            <span class="font-weight-bold">Нет в наличии </span>
                                            <i class="fa fa-angle-down" aria-hidden="true"
                                               style="transform:rotate(180deg);"></i>
                                        </button>
                                    </div>
                                    <div id="openCategoryNotAvailableProducts" class="category"
                                         data-id-block-category="NotAvailable">
                                        <div class="card-body basket-items-list-table"></div>
                                    </div>
                                </div>
                                <?php
                            }
                            $i = 0;
                            foreach ($arResult['BASKET_ITEM_RENDER_DATA_CUSTOM'] as $key => $item) {
                                $classCat = '';
                                $styleIcon = '';
                                $styleIcon = 'style="transform:rotate(180deg);"';
                                if ($i === 0 && empty($arResult['DELETED_ITEMS'])) {
                                    $styleIcon = 'style="transform:rotate(180deg);"';
                                } else {
                                    $styleIcon = 'style="transform:rotate(0deg);"';
                                    $classCat = 'hidden';
                                }
                                $newName = explode('_', $key);
                                if ($newName[1]==='NotAvailable') {
                                    continue;
                                } ?>
                                <div class="box w-full" id="<?= $newName[1] ?>">
                                    <div class="card-header basket_category py-3 px-5 dark:bg-lightGrayBg rounded-lg w-full
                                    bg-textDark"
                                         id="open<?= $newName[1] ?>">
                                        <button class="btn btn-link btn-block flex justify-between flex-row items-center
                                            btn_basket_collapse w-full" type="button"
                                                data-id-category="<?= $newName[1] ?>">
                                            <span class="dark:text-textDarkLightGray text-textLight"><?= $newName[0] ?></span>
                                            <i class="fa fa-angle-down" aria-hidden="true"
                                                <?= $styleIcon ?>>
                                                <svg width="17" height="12" viewBox="0 0 14 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0.890196 0.829872C0.503276 1.26416 0.503276 1.96835 0.890196 2.40263L5.73716 7.83798C6.51114 8.70588 7.76524 8.70554 8.53882 7.83731L13.3839 2.39863C13.7709 1.96435 13.7709 1.26016 13.3839 0.825857C12.997 0.391544 12.3697 0.391544 11.9828 0.825857L7.83588 5.48074C7.44899 5.91514 6.82164 5.91503 6.43475 5.48074L2.29133 0.829872C1.90442 0.395558 1.27711 0.395558 0.890196 0.829872Z" fill="#8B8B8B"/>
                                                </svg>
                                            </i>
                                        </button>
                                    </div>
                                    <div id="openCategory<?= $newName[1] ?>" class="category <?= $classCat ?>"
                                         data-id-block-category="<?= $newName[1] ?>">
                                        <div class="card-body basket-items-list-table"></div>
                                    </div>
                                </div>
                                <?php ++$i;
                            } ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php if ($arParams['BASKET_WITH_ORDER_INTEGRATION'] !== 'Y' && in_array('bottom', $arParams['TOTAL_BLOCK_DISPLAY'])) { ?>
            <div class="row">
                <div class="col" data-entity="basket-total-block"></div>
            </div>
        <?php }
        if ($arParams['BASKET_WITH_ORDER_INTEGRATION'] !== 'Y' && in_array('top', $arParams['TOTAL_BLOCK_DISPLAY'])) { ?>
            <div class="md:w-1/3 w-full basket-items-list-wrapper h-fit">
                <div data-entity="basket-total-block" class="mb-lg-0 mb-md-0 mb-5">
                </div>
            </div>
        <?php } ?>
    </div>
    <?php if (!empty($arResult['CURRENCIES']) && Main\Loader::includeModule('currency')) {
        CJSCore::Init('currency'); ?>
        <script>
            BX.Currency.setCurrencies(<?=CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)?>);
        </script>
    <?php }
    $signer = new \Bitrix\Main\Security\Sign\Signer;
    $signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
    $signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
    $messages = Loc::loadLanguageFile(__FILE__); ?>
    <script>
        BX.message(<?=CUtil::PhpToJSObject($messages)?>);
        BX.Sale.BasketComponent.init({
            result: <?=CUtil::PhpToJSObject($arResult, false, false, true)?>,
            params: <?=CUtil::PhpToJSObject($arParams)?>,
            template: '<?=CUtil::JSEscape($signedTemplate)?>',
            signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
            siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
            siteTemplateId: '<?=CUtil::JSEscape($component->getSiteTemplateId())?>',
            templateFolder: '<?=CUtil::JSEscape($templateFolder)?>'
        });
    </script>
    <?php if ($arParams['USE_GIFTS'] === 'Y' && $arParams['GIFTS_PLACE'] === 'BOTTOM') { ?>
        <div data-entity="parent-container">
            <div class="catalog-block-header"
                 data-entity="header"
                 data-showed="false"
                 style="display: none; opacity: 0;">
                <?= $arParams['GIFTS_BLOCK_TITLE'] ?>
            </div>
            <?php $APPLICATION->IncludeComponent(
                'bitrix:sale.products.gift.basket',
                'bootstrap_v4',
                $giftParameters,
                $component
            ); ?>
        </div>
        <?/*
        <h3 class="mb-5 mt-5 d-lg-block d-md-block d-none" ><b>Рекомендуемые товары </b></h3>
        <div class="d-lg-block d-md-block d-none">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:catalog.top",
                "oshisha_catalog.top",
                array(
                    "ACTION_VARIABLE" => "action",
                    "ADD_PICT_PROP" => "-",
                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                    "ADD_TO_BASKET_ACTION" => "ADD",
                    "BASKET_URL" => "/personal/basket.php",
                    "CACHE_FILTER" => "N",
                    "CACHE_GROUPS" => "Y",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "N",
                    "COMPARE_NAME" => "CATALOG_COMPARE_LIST",
                    "COMPATIBLE_MODE" => "Y",
                    "COMPONENT_TEMPLATE" => "oshisha_catalog.top",
                    "CONVERT_CURRENCY" => "N",
                    "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                    "DETAIL_URL" => "",
                    "DISPLAY_COMPARE" => "N",
                    "ELEMENT_COUNT" => "20",
                    "ELEMENT_SORT_FIELD" => "sort",
                    "ELEMENT_SORT_FIELD2" => "id",
                    "ELEMENT_SORT_ORDER" => "asc",
                    "ELEMENT_SORT_ORDER2" => "desc",
                    "ENLARGE_PRODUCT" => "PROP",
                    "ENLARGE_PROP" => "-",
                    "FILTER_NAME" => "arrFilter",
                    "HIDE_NOT_AVAILABLE" => "N",
                    "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                    "IBLOCK_ID" => IBLOCK_CATALOG,
                    "IBLOCK_TYPE" => "1c_catalog",
                    "LABEL_PROP" => array(),
                    "LABEL_PROP_MOBILE" => "",
                    "LABEL_PROP_POSITION" => "top-left",
                    "LINE_ELEMENT_COUNT" => "20",
                    "MESS_BTN_ADD_TO_BASKET" => "В корзину",
                    "MESS_BTN_BUY" => "Купить",
                    "MESS_BTN_COMPARE" => "Сравнить",
                    "MESS_BTN_DETAIL" => "Подробнее",
                    "MESS_NOT_AVAILABLE" => "Нет в наличии",
                    "OFFERS_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "OFFERS_LIMIT" => "1",
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
                    "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'0','BIG_DATA':false}]",
                    "PRODUCT_SUBSCRIPTION" => "Y",
                    "PROPERTY_CODE_MOBILE" => array(),
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
                    "VIEW_MODE" => "SLIDER"
                ),
                false
            ); ?></div>*/ ?>
        <?php
    }
}
include(Main\Application::getDocumentRoot() . $templateFolder . '/empty.php');

if ($arResult['ERROR_MESSAGE'] && !$arResult['EMPTY_BASKET']) {
    ShowError($arResult['ERROR_MESSAGE']);
}
?>
<?php if (!$USER->IsAuthorized()): ?>
    <script>
        $(document).ready(function () {
// лайки
            document.querySelector('.overlay-box').target.classList.toggle('hidden')
            $('.ctweb-smsauth-menu-block').show();
        });
    </script>
<? endif; ?>
<script>
    $(document).ready(function () {
        <?global $rowFavData;
        foreach( $rowFavData as $key =>$dataEl )
        {
        ?>
        $('.box_with_like[data-product-id="<?=$key?>"] .fa-star-o').css('color', 'red');
        $('.box_with_like[data-product-id="<?=$key?>"] .product-item__favorite-star').attr('data-fav-controls', 'true');

        <?
        }
        ?>
    });
</script>

