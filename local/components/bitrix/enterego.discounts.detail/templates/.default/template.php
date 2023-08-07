<?php
/** @var $arParams */
/** @var $arResult */
/** @var $APPLICATION */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$APPLICATION->SetTitle($arResult['DISCOUNT']['NAME']);
$APPLICATION->AddChainItem($arResult['DISCOUNT']['NAME'], '/discounts/' . $arResult['DISCOUNT']['NAME'] . '/');
$curDateTime = new \Bitrix\Main\Type\DateTime();
?>

<div class="discount_detail">
    <div class="discount_detail_first">
        <?php if ($arResult['DISCOUNT_IBLOCK']['PICTURE']): ?>
            <?php $iblockImage = CFile::GetByID($arResult['DISCOUNT_IBLOCK']['PICTURE'])->fetch(); ?>
            <div class="discount_logo mt-5 mb-5">
                <img src="<?= $iblockImage['SRC'] ?>" class="detail_discount_image">
            </div>
        <?php endif; ?>
        <div class="discount_name mb-5">
            <h1><?= $arResult['DISCOUNT']['NAME'] ?></h1>
            <?php if (!empty($arResult['DISCOUNT']["ACTIVE_TO"])): ?>
                <h5 class="mb-4">Акция <?= ($arResult['DISCOUNT']["ACTIVE_FROM"] <= $curDateTime)
                        ? (($arResult['DISCOUNT']["ACTIVE_TO"] >= $curDateTime) ? 'проводится' : 'проводилась')
                        : 'будет проводиться' ?>
                    с <?= $arResult['DISCOUNT']["ACTIVE_FROM"]->format('d.m.Y') ?>
                    по <?= $arResult['DISCOUNT']["ACTIVE_TO"]->format('d.m.Y') ?></h5>
            <?php endif; ?>
            <span><?= $arResult['DISCOUNT_IBLOCK']['DESCRIPTION'] ?></span>
        </div>
    </div>
</div>
<div class="discount_products">
    <?php
    $arParams = array(
        'IBLOCK_TYPE' => '1c_catalog',
        'IBLOCK_ID' => IBLOCK_CATALOG,
        'FILL_ITEM_ALL_PRICES' => 'Y',
        'ELEMENT_SORT_FIELD2' => 'SORT',
        'ELEMENT_SORT_ORDER2' => 'ASC',
        'ELEMENT_SORT_FIELD' => SORT_POPULARITY,
        'ELEMENT_SORT_ORDER' => 'DESC',
        'PROPERTY_CODE' =>
            array(
                0 => '',
                1 => 'NEWPRODUCT',
                2 => 'SALELEADER',
                3 => 'SPECIALOFFER',
                4 => '',
            ),
        'PROPERTY_CODE_MOBILE' => '',
        'META_KEYWORDS' => '-',
        'META_DESCRIPTION' => '-',
        'BROWSER_TITLE' => '-',
        'SET_LAST_MODIFIED' => 'N',
        'INCLUDE_SUBSECTIONS' => 'Y',
        'BASKET_URL' => '/personal/cart/',
        'ACTION_VARIABLE' => 'action',
        'PRODUCT_ID_VARIABLE' => 'id',
        'SECTION_ID_VARIABLE' => 'SECTION_ID',
        'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
        'PRODUCT_PROPS_VARIABLE' => 'prop',
        'FILTER_NAME' => 'DISCOUNT_FILTER',
        'CACHE_TYPE' => 'N',
        'CACHE_TIME' => '',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'Y',
        'SET_TITLE' => 'Y',
        'MESSAGE_404' => '',
        'SET_STATUS_404' => 'Y',
        'SHOW_404' => 'Y',
        'FILE_404' => '',
        'DISPLAY_COMPARE' => 'N',
        'PAGE_ELEMENT_COUNT' => '36',
        'LINE_ELEMENT_COUNT' => '3',
        'PRICE_CODE' =>
            array(
                3 => 'Сайт скидка',
                2 => 'Основная',
                9 => 'b2b',
                4 => 'Розничная',
            ),
        'USE_PRICE_COUNT' => 'N',
        'SHOW_PRICE_COUNT' => '100',
        'PRICE_VAT_INCLUDE' => 'Y',
        'USE_PRODUCT_QUANTITY' => 'Y',
        'ADD_PROPERTIES_TO_BASKET' => 'Y',
        'PARTIAL_PRODUCT_PROPERTIES' => 'N',
        'PRODUCT_PROPERTIES' =>
            array(),
        'DISPLAY_TOP_PAGER' => 'N',
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'PAGER_TITLE' => 'Товары',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_TEMPLATE' => 'round',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000000',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'PAGER_BASE_LINK' => NULL,
        'PAGER_PARAMS_NAME' => NULL,
        'LAZY_LOAD' => 'Y',
        'MESS_BTN_LAZY_LOAD' => 'Показать ещё',
        'LOAD_ON_SCROLL' => 'N',
        'OFFERS_CART_PROPERTIES' =>
            array(),
        'OFFERS_FIELD_CODE' =>
            array(
                0 => 'NAME',
                1 => 'PREVIEW_PICTURE',
                2 => 'DETAIL_PICTURE',
                3 => 'GRAMMOVKA_G',
                4 => 'SHTUK_V_UPAKOVKE',
                5 => 'TSVET',
            ),
        'OFFERS_PROPERTY_CODE' =>
            array(
                0 => 'MORE_PHOTO',
                1 => 'SIZES_SHOES',
                6 => 'SHTUK_V_UPAKOVKE',
                7 => 'TSVET',
                2 => 'GRAMMOVKA_G',
                3 => 'COLOR_REF',
                4 => 'ARTNUMBER',
                5 => '',
            ),
        'OFFERS_SORT_FIELD' => 'sort',
        'OFFERS_SORT_ORDER' => 'desc',
        'OFFERS_SORT_FIELD2' => 'id',
        'OFFERS_SORT_ORDER2' => 'desc',
        'OFFERS_LIMIT' => '5',
        'SECTION_URL' => '/catalog/#SECTION_CODE#/',
        'DETAIL_URL' => '/catalog/product/#ELEMENT_CODE#/',
        'USE_MAIN_ELEMENT_SECTION' => 'N',
        'CONVERT_CURRENCY' => 'N',
        'CURRENCY_ID' => NULL,
        'HIDE_NOT_AVAILABLE' => 'L',
        'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
        'LABEL_PROP' =>
            array(
                0 => 'VKUS',
            ),
        'LABEL_PROP_MOBILE' => '',
        'LABEL_PROP_POSITION' => 'top-left',
        'ADD_PICT_PROP' => 'MORE_PHOTO',
        'PRODUCT_DISPLAY_MODE' => 'Y',
        'PRODUCT_BLOCKS_ORDER' => 'price,props,sku,quantityLimit,quantity,buttons',
        'PRODUCT_ROW_VARIANTS' => '[{\'VARIANT\':\'3\',\'BIG_DATA\':false},{\'VARIANT\':\'3\',\'BIG_DATA\':false},{\'VARIANT\':\'3\',\'BIG_DATA\':false},{\'VARIANT\':\'3\',\'BIG_DATA\':false}]',
        'ENLARGE_PRODUCT' => 'PROP',
        'ENLARGE_PROP' => '-',
        'SHOW_SLIDER' => 'Y',
        'SLIDER_INTERVAL' => '3000',
        'SLIDER_PROGRESS' => 'N',
        'OFFER_ADD_PICT_PROP' => 'MORE_PHOTO',
        'OFFER_TREE_PROPS' =>
            array(),
        'PRODUCT_SUBSCRIPTION' => 'Y',
        'SHOW_DISCOUNT_PERCENT' => 'Y',
        'DISCOUNT_PERCENT_POSITION' => 'bottom-right',
        'SHOW_OLD_PRICE' => 'Y',
        'SHOW_MAX_QUANTITY' => 'Y',
        'MESS_SHOW_MAX_QUANTITY' => 'Наличие',
        'RELATIVE_QUANTITY_FACTOR' => '',
        'MESS_RELATIVE_QUANTITY_MANY' => '',
        'MESS_RELATIVE_QUANTITY_FEW' => '',
        'MESS_BTN_BUY' => 'Купить',
        'MESS_BTN_ADD_TO_BASKET' => 'Забронировать',
        'MESS_BTN_SUBSCRIBE' => 'Подписаться',
        'MESS_BTN_DETAIL' => 'Подробнее',
        'MESS_NOT_AVAILABLE' => 'Нет в наличии',
        'MESS_BTN_COMPARE' => 'Сравнение',
        'USE_ENHANCED_ECOMMERCE' => 'N',
        'DATA_LAYER_NAME' => '',
        'BRAND_PROPERTY' => '',
        'TEMPLATE_THEME' => 'site',
        'ADD_SECTIONS_CHAIN' => 'Y',
        'ADD_TO_BASKET_ACTION' => 'ADD',
        'SHOW_CLOSE_POPUP' => 'N',
        'COMPARE_PATH' => '/catalog/compare/',
        'COMPARE_NAME' => NULL,
        'USE_COMPARE_LIST' => 'Y',
        'BACKGROUND_IMAGE' => '-',
        'COMPATIBLE_MODE' => 'N',
        'DISABLE_INIT_JS_IN_COMPONENT' => 'N',
    );

    $APPLICATION->IncludeComponent(
        "bitrix:catalog.section",
        "oshisha_catalog.section",
        $arParams,
        false
    );
    ?>
</div>