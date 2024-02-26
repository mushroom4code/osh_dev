<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("OSHISHA - Главная");

if (IsModuleInstalled("advertising")):?>
    <div class="mt-3 xl:container container">
        <div class="flex lg:flex-row flex-col-reverse justify-between w-full relative mb-10">
            <div class="banner_small lg:w-1/4 flex lg:flex-col flex-row justify-between lg:mr-8 mr-0">
                <div class="w-full mb-5 rounded-lg lg:max-h-auto max-h-64">
                    <?php $APPLICATION->IncludeComponent(
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
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "Y",
                            "COMPONENT_TEMPLATE" => "oshisha_banners",
                            "DEFAULT_TEMPLATE" => "bootstrap_v4",
                            "NOINDEX" => "N",
                            "QUANTITY" => "1",
                            "TYPE" => "NEW_MINI_TOP"
                        ),
                        false
                    ); ?>
                </div>
                <div class="w-full rounded-lg lg:max-h-auto max-h-64">
                    <?php $APPLICATION->IncludeComponent(
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
                            "BS_WRAP" => "N",
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "Y",
                            "COMPONENT_TEMPLATE" => "oshisha_banners",
                            "DEFAULT_TEMPLATE" => "-",
                            "NOINDEX" => "N",
                            "QUANTITY" => "2",
                            "TYPE" => "NEW_MINI_BOTTOM"
                        ),
                        false
                    ); ?>
                </div>
            </div>
            <div class="max-w-6xl lg:w-3/4 w-full lg:mb-0 mb-5 rounded-lg relative flex justify-center items-center overflow-hidden">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:advertising.banner",
                    "oshisha_banners",
                    array(
                        "BS_ARROW_NAV" => "Y",
                        "BS_BULLET_NAV" => "Y",
                        "BS_CYCLING" => "N",
                        "BS_INTERVAL" => "4000",
                        "BS_EFFECT" => "fade",
                        "BS_HIDE_FOR_PHONES" => "N",
                        "BS_HIDE_FOR_TABLETS" => "N",
                        "BS_KEYBOARD" => "Y",
                        "BS_PAUSE" => "Y",
                        "BS_WRAP" => "Y",
                        "CACHE_TIME" => "3600",
                        "CACHE_TYPE" => "A",
                        "COMPONENT_TEMPLATE" => "oshisha_banners",
                        "DEFAULT_TEMPLATE" => "bootstrap_v4",
                        "NOINDEX" => "Y",
                        "QUANTITY" => "5",
                        "TYPE" => "MAIN"
                    ),
                    false
                ); ?>
            </div>
        </div>

        <?php

        $SectionRes = CIBlockElement::GetList(array(),
            array('ACTIVE' => 'Y', 'IBLOCK_ID' => 21),
            false, array("CODE", 'NAME', 'ID', 'PREVIEW_PICTURE', 'DESCRIPTION')
        );
        if (!empty($SectionRes)) {
            while ($arSection = $SectionRes->GetNext()) { ?>
                <div>
                    <h5 class="text-2xl font-semibold dark:font-medium text-textLight mb-8 dark:text-textDarkLightGray"><?= $arSection['NAME'] ?></h5>
                    <div class="mb-5">
                        <?= $arSection['NAME']?>
                    </div>
                </div>
            <?php }
        }
        ?>
    </div>
<?php endif;
if (SITE_ID !== SITE_EXHIBITION) { ?>
    </div>
    </div>
    </div>
    </div>
    <div class="box_with_banner_dop mb-7 w-full left-0 right-0">
        <?php $APPLICATION->IncludeComponent(
            "bitrix:advertising.banner",
            "oshisha_banners",
            array(
                "BS_ARROW_NAV" => "N",
                "BS_BULLET_NAV" => "N",
                "BS_CYCLING" => "N",
                "BS_EFFECT" => "fade",
                "BS_HIDE_FOR_PHONES" => "N",
                "BS_HIDE_FOR_TABLETS" => "N",
                "BS_KEYBOARD" => "N",
                "BS_WRAP" => "N",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "N",
                "DEFAULT_TEMPLATE" => "-",
                "NOINDEX" => "N",
                "QUANTITY" => "1",
                "TYPE" => "BANNERS_HOME_1",
                "COMPONENT_TEMPLATE" => "oshisha_banners"
            ),
            false
        ); ?>
    </div>
    <div class="section_wrapper min-h-550 flex flex-col items-center">
    <div class="container md:mb-8 mb-0 px-4 md:px-0">
    <div class="flex md:flex-row flex-col mb-7 justify-between ">
        <div class="md:w-6/12 w-full mb-2 md:pr-4 pr-0">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:advertising.banner",
                "oshisha_banners",
                array(
                    "ANIMATION_DURATION" => "500",
                    "ARROW_NAV" => "1",
                    "BS_ARROW_NAV" => "N",
                    "BS_BULLET_NAV" => "N",
                    "BS_CYCLING" => "N",
                    "BS_EFFECT" => "fade",
                    "BS_HIDE_FOR_PHONES" => "Y",
                    "BS_HIDE_FOR_TABLETS" => "N",
                    "BS_KEYBOARD" => "N",
                    "BS_PAUSE" => "Y",
                    "BS_WRAP" => "N",
                    "BULLET_NAV" => "2",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "Y",
                    "COMPONENT_TEMPLATE" => "oshisha_banners",
                    "CONTROL_NAV" => "Y",
                    "CYCLING" => "N",
                    "DEFAULT_TEMPLATE" => "-",
                    "DIRECTION_NAV" => "Y",
                    "EFFECT" => "random",
                    "EFFECTS" => "",
                    "HEIGHT" => "400",
                    "JQUERY" => "Y",
                    "KEYBOARD" => "N",
                    "NOINDEX" => "Y",
                    "PARALL_HEIGHT" => "400",
                    "QUANTITY" => "1",
                    "SCALE" => "N",
                    "SPEED" => "500",
                    "TYPE" => "NEW_MINI_FOOTER",
                    "WRAP" => "1"
                ),
                false,
                array(
                    "ACTIVE_COMPONENT" => "Y"
                )
            ); ?>
        </div>
        <div class="md:w-6/12 w-full mb-7 md:pl-4 pl-0">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:advertising.banner",
                "oshisha_banners",
                array(
                    "ANIMATION_DURATION" => "500",
                    "ARROW_NAV" => "1",
                    "BS_ARROW_NAV" => "N",
                    "BS_BULLET_NAV" => "N",
                    "BS_CYCLING" => "N",
                    "BS_EFFECT" => "slide",
                    "BS_HIDE_FOR_PHONES" => "Y",
                    "BS_HIDE_FOR_TABLETS" => "N",
                    "BS_KEYBOARD" => "N",
                    "BS_PAUSE" => "Y",
                    "BS_WRAP" => "N",
                    "BULLET_NAV" => "2",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "N",
                    "COMPONENT_TEMPLATE" => "oshisha_banners",
                    "CONTROL_NAV" => "Y",
                    "CYCLING" => "N",
                    "DEFAULT_TEMPLATE" => "bootstrap_v4",
                    "DIRECTION_NAV" => "Y",
                    "EFFECT" => "random",
                    "EFFECTS" => "",
                    "HEIGHT" => "400",
                    "JQUERY" => "Y",
                    "KEYBOARD" => "N",
                    "NOINDEX" => "Y",
                    "PARALL_HEIGHT" => "400",
                    "QUANTITY" => "1",
                    "SCALE" => "N",
                    "SPEED" => "500",
                    "TYPE" => "NEW_MINI_FOOTER_2",
                    "WRAP" => "1"
                ),
                false,
                array(
                    "ACTIVE_COMPONENT" => "Y"
                )
            ); ?>
        </div>
    </div>

    <?php if (defined('PROPERTY_USE_ON_MAIN_PAGE')) { ?>
        <div class="dark:text-textDarkLightGray dark:font-thin font-medium text-textLight text-5xl mb-4">Распродажа
        </div>
        <div class="by-card mb-7">
            <?php

            $GLOBALS['FILTER_SALE'] = array(
                'PROPERTY_' . PROPERTY_USE_ON_MAIN_PAGE . '_VALUE' => 'Да'
            );
            $APPLICATION->IncludeComponent(
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
                    "ELEMENT_COUNT" => "16",
                    "ELEMENT_SORT_FIELD" => "PROPERTY_" . SORT_POPULARITY,
                    "ELEMENT_SORT_FIELD2" => "ID",
                    "ELEMENT_SORT_ORDER" => "desc",
                    "ELEMENT_SORT_ORDER2" => "desc",
                    "ENLARGE_PRODUCT" => "PROP",
                    "ENLARGE_PROP" => "-",
                    "FILTER_NAME" => "FILTER_SALE",
                    "HIDE_NOT_AVAILABLE" => "Y",
                    "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                    "IBLOCK_ID" => IBLOCK_CATALOG,
                    "IBLOCK_TYPE" => "1c_catalog",
                    "LABEL_PROP" => "",
                    "LABEL_PROP_MOBILE" => "",
                    "LABEL_PROP_POSITION" => "top-left",
                    "LINE_ELEMENT_COUNT" => "20",
                    "MESS_BTN_ADD_TO_BASKET" => "Забронировать",
                    "MESS_BTN_BUY" => "Купить",
                    "MESS_BTN_COMPARE" => "Сравнить",
                    "MESS_BTN_DETAIL" => "Подробнее",
                    "MESS_NOT_AVAILABLE" => "Нет в наличии",
                    "OFFERS_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "OFFERS_LIMIT" => "20",
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
                    "PROPERTY_CODE" => array(
                        0 => "USE_DISCOUNT",
                        1 => "",
                    ),
                    "PRODUCT_PROPERTIES" => array(
                        "USE_DISCOUNT"
                    )
                ),
                false
            );
            ?>
        </div>
    <?php }
} ?>
    </div>
    </div>
<?php
// TODO - обработка лайки
//$update = new Enterego\EnteregoProcessing();
//$update->update_like_in_new_table();
//$update->update_favorites_product_users();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
