<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CMain $APPLICATION */
/** @var $arResult array */
?>
<div id="basket-empty" class="bx-sbb-empty-cart-container <?= $arResult['EMPTY_BASKET'] ? '' : 'd-none'; ?>">
    <div class="mb-lg-5 mb-md-5 mb-2"><h4 class="font-m-21"><b>В вашей корзине нет товаров</b></h4></div>
    <div class="bx-sbb-empty-cart-image">
        <div class="banners_box">
            <div class="banners_container">
                <div class="d-flex flex-column mt-3">
                    <h2 class="mb-lg-5 mb-md-5 mb-2 font-m-14 font-w-m-600">Начните покупки прямой сейчас!</h2>
                    <p class="mb-5"><b class="font-16 font-w-m-300">Можете воспользоваться поиском, рекомендуемыми<br>
                            товарами или вернуться в каталог</b>
                    </p>
                    <div class="box-image-empty-basket-mobile"></div>
                    <a href="/catalog/kalyany/" class="bx-advertisingbanner-btn btn font-w-m-400">
                        Вернуться к покупкам</a>
                </div>

                <div class="banner_small_basket">
                    <?php
                    $APPLICATION->IncludeComponent(
                        "bitrix:advertising.banner",
                        "oshisha_banners",
                        array(
                            "BS_ARROW_NAV" => "N",
                            "BS_BULLET_NAV" => "N",
                            "BS_CYCLING" => "N",
                            "BS_EFFECT" => "fade",
                            "BS_HIDE_FOR_PHONES" => "Y",
                            "BS_HIDE_FOR_TABLETS" => "N",
                            "BS_KEYBOARD" => "Y",
                            "BS_WRAP" => "Y",
                            "CACHE_TIME" => "0",
                            "CACHE_TYPE" => "A",
                            "COMPONENT_TEMPLATE" => "oshisha_banners",
                            "DEFAULT_TEMPLATE" => "bootstrap_v4",
                            "NOINDEX" => "N",
                            "QUANTITY" => "1",
                            "TYPE" => "BANNER_BASKET_SMALL"
                        )
                    ); ?>
                </div>
            </div>
        </div>
    </div>
	<?/*
    <h3 class="mb-lg-5 mb-md-5 mb-0 mt-5 font-m-20"><b>Рекомендуемые товары </b></h3>
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
            "CACHE_TYPE" => "A",
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
            "HIDE_NOT_AVAILABLE" => "Y",
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
            "FILL_ITEM_ALL_PRICES"=>"Y",
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
    );?>
	*/?>
</div>

