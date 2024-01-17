<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CMain $APPLICATION */
/** @var $arResult array */
?>
<div id="basket-empty" class="bx-sbb-empty-cart-container">
    <?php if (!empty($arResult['EMPTY_BASKET'])) { ?>
        <div class="bx-sbb-empty-cart-image">
            <div class="banners_box">
                <div class="banners_container flex md:flex-row flex-col py-3 px-4">
                    <div class="flex flex-col mt-3 md:w-1/2 w-full justify-between md:mb-0 mb-4">
                        <h1 class="md:text-3xl text-2xl mb-5 md:font-semibold font-medium dark:font-medium text-textLight dark:text-textDarkLightGray">
                            В вашей корзине нет товаров
                        </h1>
                        <div>
                            <h2 class="mb-5 md:text-2xl text-xl font-medium dark:font-light text-textLight dark:text-textDarkLightGray">
                                Начните покупки прямой сейчас!</h2>
                            <p class="md:text-lg text-xs mr-5 mb-5 font-normal dark:font-light text-textLight dark:text-textDarkLightGray">
                                Можете воспользоваться поиском, рекомендуемыми <br class="md:block hidden">
                                товарами или вернуться в каталог
                            </p>
                        </div>
                        <a href="/catalog/kalyany/" class="bx-advertisingbanner-btn text-lightGrayBg dark:text-white w-fit
                            bg-textDark dark:bg-grayButton dark:font-normal md:mb-0 mb-8 flex flex-row items-center
                            md:p-3 p-2 rounded-lg font-semibold md:shadow-md shadow-sm shadow-shadowDark">
                            <svg width="25" height="26" viewBox="0 0 34 35" class="mr-2"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path class="fill-light-red dark:fill-white"
                                      d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"></path>
                            </svg>
                            <span class="font-semibold dark:font-light md:text-md text-sm dark:text-textDarkLightGray text-lightGrayBg">Продолжить покупки</span></a>
                    </div>

                    <div class="banner_small_basket md:w-1/2 w-full">
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
        <? /*
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
	*/ ?>
    <?php } ?>
</div>

