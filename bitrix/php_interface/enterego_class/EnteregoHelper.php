<?php

namespace Enterego;

use CCatalogSku;
use CFile;
use CIBlockElement;
use CIBlockProperty;
use CIBlockSection;
use CModule;
use CSaleBasket;
use CCatalogMeasure;
use Bitrix\Sale\Order;
use Bitrix\Highloadblock\HighloadBlockTable;
use DateInterval;
use DateTime;
use Ipol\Fivepost\Admin\BitrixLoggerController;


/**
 * Class EnteregoHelper
 * @package Enterego
 */
class EnteregoHelper
{
    /**
     * @param $hlName
     * @param $arParams
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getHeadBlock($hlName, $arParams)
    {
        if (CModule::IncludeModule('highloadblock')) {
            $result = HighloadBlockTable::getList(array('filter' => array('=NAME' => $hlName)));
            if ($row = $result->fetch()) {
                $obEntity = HighloadBlockTable::compileEntity($row);
                $strEntityDataClass = $obEntity->getDataClass();
            } else {
                return [];
            }

            $rsData = $strEntityDataClass::getList($arParams);
            while ($arItem = $rsData->Fetch()) {

                if ($arItem['UF_FILE']) {
                    $img = \CFile::ResizeImageGet($arItem['UF_FILE'], array("width" => 360, "height" => 210), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false)['src'];
                    $arItem['UF_FILE'] = $img;
                } else if ($arItem['UF_IMG']) {
                    $img = \CFile::ResizeImageGet($arItem['UF_IMG'], array("width" => 360, "height" => 210), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false)['src'];
                    $arItem['UF_IMG'] = $img;
                } else if ($arItem['UF_FILE_CATEGORY']) {
                    $img = \CFile::ResizeImageGet($arItem['UF_FILE_CATEGORY'], array("width" => 360, "height" => 210), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false)['src'];
                    $arItem['UF_FILE_CATEGORY'] = $img;
                }
                $arItems[] = $arItem;
            }

            return $arItems;
        }
    }

    public static function getItems($id, $method)
    {
        $arItems = [];

        if ($method === 'CSaleBasket') {
            $res = CSaleBasket::GetList(array(), array("ORDER_ID" => $id));
            while ($arItem = $res->Fetch()) {
                $arItems['ITEM'][] = $arItem;
            }
        } else if ($method === 'Order') {
            $parameters = [
                'filter' => [
                    "USER_ID" => $id,
                ],
            ];
            $dbRes = Order::getList($parameters);
            while ($order = $dbRes->fetch()) {
                $arItems['ORDERS_ID'][] = $order['ACCOUNT_NUMBER'];
            }
        } else if ($method === 'file') {
            $item = CIBlockElement::GetByID($id);
            while ($image = $item->GetNext()) {
                echo CFile::GetPath($image['PREVIEW_PICTURE']);
            }
        } else if ($method === 'PROPERTIES') {
            $res = CIBlockElement::GetList(array(), array('ID' => $id), false, false, array('PROPERTIES'));
            while ($props = $res->Fetch()) {
                $arItems['PROPERTIES'] = $props;
            }
        } else if ($method === PROPERTY_KEY_VKUS) {
            $rsElement = CIBlockElement::GetList(array(), array('ID' => $id), false, false, array());
            while ($arElement = $rsElement->Fetch()) {
                $res = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $id, false, array('CODE' => PROPERTY_KEY_VKUS));
                while ($results = $res->Fetch()) {
                    if ($results['CODE'] === PROPERTY_KEY_VKUS && !empty($results['VALUE_XML_ID'])) {
                        $colorId = explode('#', $results['VALUE_XML_ID']);
                        $arItems[PROPERTY_KEY_VKUS][] = ['NAME' => $results['VALUE_ENUM'],
                            'VALUE' => $colorId[1],
                            'ID' => $colorId[0]];
                    }
                }
            }
        }

        return $arItems;
    }

    public static function getParentElementId($iblock_id, $iblock_section_id)
    {
        $scRes = \CIBlockSection::GetNavChain(
            $iblock_id,
            $iblock_section_id,
            array("ID", "DEPTH_LEVEL", "NAME")
        );

        $name = [];
        while ($arGrp = $scRes->Fetch()) {
            if ($arGrp['DEPTH_LEVEL'] == 1) {
                $name = $arGrp;
            }
        }
        return $name;
    }

    public static function basketCustomSort(&$basketResult, &$arResult)
    { //Сортировка в корзине
        $data = $result = [];

        //recalculate for storages rest
        $basketResult['NOT_AVAILABLE_BASKET_ITEMS_COUNT'] = 0;
        if (!empty($arResult)) {
            foreach ($arResult as $key_basket => &$basket_item) {
                $id = $basket_item['PRODUCT_ID'];
                $showDiscountPrice = (float)$basket_item['DISCOUNT_PRICE'] > 0;

                $price = [];
                $show_product_prices = false;
                $rsElement = CIBlockElement::GetList(
                    array(),
                    array("ID" => $id),
                    false,
                    false,
                    array(
                        'IBLOCK_SECTION_ID',
                        'IBLOCK_ID',
                        'ID',
                        "QUANTITY",
                        "PRICE_" . RETAIL_PRICE,
                        "PRICE_" . B2B_PRICE,
                        'PRICE_' . BASIC_PRICE,
                        "PRICE_" . SALE_PRICE_TYPE_ID,
                        "PROPERTY_USE_DISCOUNT"
                    )
                );

                if ($arElement = $rsElement->Fetch()) {
                    //set available quantity from storage quantity
                    $basket_item['AVAILABLE_QUANTITY'] =  $arElement['QUANTITY'];
                    $basket_item['NOT_AVAILABLE'] = $arElement['QUANTITY'] == 0;
                    $basket_item['CAN_BUY'] = $basket_item['NOT_AVAILABLE'] === true ? 'N' : 'Y';
                    if ($basket_item['NOT_AVAILABLE'] === true ) {
                        $basketResult['NOT_AVAILABLE_BASKET_ITEMS_COUNT']++;
                    }

                    $str_product_prices = '';
                    $product_prices_sql = $arElement["PRICE_" . BASIC_PRICE];
                    if (($arElement['PROPERTY_USE_DISCOUNT_VALUE'] == 'Да' || USE_CUSTOM_SALE_PRICE) && (!empty($arElement["PRICE_" . SALE_PRICE_TYPE_ID])
                            && ((int)$product_prices_sql > (int)$arElement["PRICE_" . SALE_PRICE_TYPE_ID])) && !$showDiscountPrice) {
                        $str_product_prices = explode('.', $product_prices_sql);
                        $price['SALE_PRICE'] = $str_product_prices[0] . ' ₽';
                        $show_product_prices = true;

                    } else {
                        if ((int)$basket_item['PRICE_TYPE_ID'] == BASIC_PRICE && !$showDiscountPrice) {
                            $show_product_prices = true;
                            $str_product_prices = explode('.', $arElement["PRICE_" . RETAIL_PRICE]);
                        } else if ((int)$basket_item['PRICE_TYPE_ID'] == B2B_PRICE && !$showDiscountPrice) {
                            $show_product_prices = true;
                            $str_product_prices = explode('.', $arElement["PRICE_" . BASIC_PRICE]);
                        }
                    }

                    if (!empty($arElement["PRICE_" . RETAIL_PRICE])) {
                        $price['PRICE_DATA'][0]['VAL'] = explode('.', $arElement["PRICE_" . RETAIL_PRICE])[0] * $basket_item['MEASURE_RATIO'];
                        $price['PRICE_DATA'][0]['NAME'] = 'Розничная (до 10к)';
                    }
                    if (!empty($arElement["PRICE_" . BASIC_PRICE])) {
                        $price['PRICE_DATA'][1]['VAL'] = explode('.', $arElement["PRICE_" . BASIC_PRICE])[0] * $basket_item['MEASURE_RATIO'];
                        $price['PRICE_DATA'][1]['NAME'] = 'Основная (до 30к)';
                    }
                    if (!empty($arElement["PRICE_" . B2B_PRICE])) {
                        $price['PRICE_DATA'][2]['VAL'] = explode('.', $arElement["PRICE_" . B2B_PRICE])[0] * $basket_item['MEASURE_RATIO'];
                        $price['PRICE_DATA'][2]['NAME'] = 'b2b (от 30к)';
                    }

                    $product_prices = $str_product_prices[0] . '₽';
                    $sum_sale = (((round($basket_item['QUANTITY']) / $basket_item['MEASURE_RATIO']) * $price['PRICE_DATA'][0]['VAL']) - round($basket_item['SUM_VALUE']));
                    $sum_old = ((round($basket_item['QUANTITY']) / $basket_item['MEASURE_RATIO']) * $price['PRICE_DATA'][0]['VAL']);

                    $basket_item['PRICES_NET'] = $price;
                    $basket_item['SHOW_DISCOUNT_PRICE'] = $showDiscountPrice;
                    $basket_item['SALE_PRICE_VAL'] = $sum_sale;
                    $basket_item['SALE_PRICE'] = $product_prices;
                    $basket_item["SUM_OLD"] = $sum_old;
                    $basket_item['SHOW_SALE_PRICE'] = $show_product_prices;

                    if (!empty($arElement['IBLOCK_SECTION_ID'])) {
                        $section_id = $arElement['IBLOCK_SECTION_ID'];
                        $iblock_id = $arElement['IBLOCK_ID'];
                    } else {
                        $product = CCatalogSKU::GetProductInfo($arElement['ID']);
                        $prod_id = $product['ID'];
                        $section_id = CIBlockElement::GetList(
                            array(),
                            array('ID' => $prod_id),
                            false,
                            false,
                            array('IBLOCK_SECTION_ID')
                        )->Fetch()['IBLOCK_SECTION_ID'];
                        $iblock_id = $product['IBLOCK_ID'];
                    }

                    $cat_info = self::getParentElementId($iblock_id, $section_id);
                    $parent_name = $cat_info['NAME'];
                    $parent_id = $cat_info['ID'];
                    if (trim($parent_name) == 'Кальян') $parent_name = 'Кальяны';

                    if ($basket_item['CAN_BUY'] !== 'Y') {
                        $basketParent = "Нет в наличии_NotAvailable";
                    } else {
                        $basketParent = $parent_name . '_' . $parent_id;
                    }

                    $basket_item['BASKET_KEY'] = $key_basket;
                    $data[$basketParent][] = $basket_item;
                    if (self::productIsGift($id)) {
                        $basket_item['GIFT'] = true;
                        $basket_item['SHOW_DISCOUNT_PRICE'] = false;
                        $basket_item['SHOW_MAX_PRICE'] = false;
                    }

                    $result[$basketParent][] = (string)$key_basket;
                }
            }
        }

        return $result;
    }

    /** Return product type for discount gift
     * @param int $productId
     * @return false
     */
    public static function productIsGift(int $productId): bool
    {
        if (defined('IS_MERCH_PROPERTY')) {
            $rsRes = CIBlockElement::GetList([], ['ID' => $productId, 'PROPERTY_'.IS_MERCH_PROPERTY.'_VALUE' => 'Да']);
            return $rsRes->SelectedRowsCount() > 0;
        } else {
            return false;
        }
    }

    /** Return array of parammeters that are common to every catalog call
     * @return array
     */
    public static function getDefaultCatalogParameters(): array
    {
        if (SITE_ID === SITE_EXHIBITION) {
            $iblock_id = IBLOCK_CATALOG_EX;
            $priceCodes = [0 => "b2b"];
        }
        return array(
            "IBLOCK_TYPE" => "1c_catalog",
            "IBLOCK_ID" => isset($iblock_id) ? $iblock_id : IBLOCK_CATALOG,
            "TEMPLATE_THEME" => "site",
            "DETAIL_SHOW_MAX_QUANTITY" => "Y",
            "HIDE_NOT_AVAILABLE" => "L",
            "BASKET_URL" => "/personal/cart/",
            "ACTION_VARIABLE" => "action",
            "PRODUCT_ID_VARIABLE" => "id",
            "SECTION_ID_VARIABLE" => "SECTION_ID",
            "PRODUCT_QUANTITY_VARIABLE" => "quantity",
            "PRODUCT_PROPS_VARIABLE" => "prop",
            "SEF_MODE" => "Y",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "AJAX_OPTION_HISTORY" => "N",
            "CACHE_TYPE" => "N",
            "CACHE_TIME" => "",
            "CACHE_FILTER" => "Y",
            "CACHE_GROUPS" => "Y",
            "SET_TITLE" => "Y",
            "ADD_SECTION_CHAIN" => "Y",
            "ADD_ELEMENT_CHAIN" => "Y",
            "SET_STATUS_404" => "Y",
            "DETAIL_DISPLAY_NAME" => "N",
            "USE_ELEMENT_COUNTER" => "Y",
            "USE_FILTER" => "Y",
            "FILTER_VIEW_MODE" => "VERTICAL",
            "USE_COMPARE" => "N",
            "PRICE_CODE" => isset($priceCodes) ? $priceCodes : \BXConstants::PriceCode(),
            "FILL_ITEM_ALL_PRICES" => "Y",
            "USE_PRICE_COUNT" => "N",
            "SHOW_PRICE_COUNT" => "100",
            "PRICE_VAT_INCLUDE" => "Y",
            "PRICE_VAT_SHOW_VALUE" => "N",
            "PRODUCT_PROPERTIES" => array(
                0 => "VKUS",
                1 => "DISKONT",
                2 => "KHIT",
            ),
            "USE_PRODUCT_QUANTITY" => "Y",
            "CONVERT_CURRENCY" => "N",
            "QUANTITY_FLOAT" => "N",
            "OFFERS_CART_PROPERTIES" => array(
                0 => "SIZES_SHOES",
                1 => "SIZES_CLOTHES",
                2 => "COLOR_REF",
            ),
            "SHOW_TOP_ELEMENTS" => "N",
            "SECTION_COUNT_ELEMENTS" => "Y",
            "SECTION_TOP_DEPTH" => "1",
            "SECTIONS_VIEW_MODE" => "TILE",
            "SECTIONS_SHOW_PARENT_NAME" => "N",
            "PAGE_ELEMENT_COUNT" => "16",
            "LINE_ELEMENT_COUNT" => "3",
            "LIST_PROPERTY_CODE" => array(
                0 => "",
                1 => "NEWPRODUCT",
                2 => "SALELEADER",
                3 => "SPECIALOFFER",
                4 => "",
            ),
            "INCLUDE_SUBSECTIONS" => "Y",
            "LIST_META_KEYWORDS" => "-",
            "LIST_META_DESCRIPTION" => "-",
            "LIST_BROWSER_TITLE" => "-",
            "LIST_OFFERS_FIELD_CODE" => array(
                0 => "NAME",
                1 => "PREVIEW_PICTURE",
                2 => "DETAIL_PICTURE",
                3 => "GRAMMOVKA_G",
                4 => "SHTUK_V_UPAKOVKE",
                5 => "TSVET",
            ),
            "LIST_OFFERS_PROPERTY_CODE" => array(
                0 => "MORE_PHOTO",
                1 => "SIZES_SHOES",
                7 => "TSVET",
                2 => "GRAMMOVKA_G",
                3 => "COLOR_REF",
                4 => "ARTNUMBER",
                5 => "SIZES_CLOTHES",
                6 => "SHTUK_V_UPAKOVKE",
            ),
            "SECTION_BACKGROUND_IMAGE" => "-",
            "DETAIL_PROPERTY_CODE" => array(
                0 => "BREND",
                1 => "MATERIAL",
                2 => "NEWPRODUCT",
                3 => "MANUFACTURER",
                4 => "",
            ),
            "DETAIL_META_KEYWORDS" => "-",
            "DETAIL_META_DESCRIPTION" => "-",
            "DETAIL_BROWSER_TITLE" => "-",
            "DETAIL_OFFERS_FIELD_CODE" => array(
                0 => "NAME",
                1 => "GRAMMOVKA_G",
                4 => "SHTUK_V_UPAKOVKE",
                5 => "TSVET",
                8 => "MORE_PHOTO",
            ),
            "DETAIL_OFFERS_PROPERTY_CODE" => array(
                0 => "MORE_PHOTO",
                1 => "ARTNUMBER",
                2 => "SIZES_SHOES",
                3 => "SIZES_CLOTHES",
                4 => "COLOR_REF",
                5 => "GRAMMOVKA_G",
                6 => "SHTUK_V_UPAKOVKE",
                7 => "TSVET",
            ),
            "DETAIL_BACKGROUND_IMAGE" => "-",
            "LINK_IBLOCK_TYPE" => "",
            "LINK_IBLOCK_ID" => "",
            "LINK_PROPERTY_SID" => "",
            "LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
            "USE_ALSO_BUY" => "Y",
            "ALSO_BUY_ELEMENT_COUNT" => "4",
            "ALSO_BUY_MIN_BUYES" => "1",
            "OFFERS_SORT_FIELD" => "sort",
            "OFFERS_SORT_ORDER" => "desc",
            "OFFERS_SORT_FIELD2" => "id",
            "OFFERS_SORT_ORDER2" => "desc",
            "PAGER_TEMPLATE" => "round",
            "DISPLAY_TOP_PAGER" => "N",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "PAGER_TITLE" => "Товары",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
            "PAGER_SHOW_ALL" => "N",
            "ADD_PICT_PROP" => "MORE_PHOTO",
            "LABEL_PROP" => array(
                0 => PROPERTY_KEY_VKUS,
            ),
            "PRODUCT_DISPLAY_MODE" => "Y",
            "OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
            "OFFER_TREE_PROPS" => array(
                0 => "SIZES_SHOES",
                1 => "SIZES_CLOTHES",
                2 => "COLOR_REF",
                3 => "",
            ),
            "SHOW_DISCOUNT_PERCENT" => "Y",
            "SHOW_OLD_PRICE" => "Y",
            "MESS_BTN_BUY" => "Купить",
            "MESS_BTN_ADD_TO_BASKET" => "Забронировать",
            "MESS_BTN_COMPARE" => "Сравнение",
            "MESS_BTN_DETAIL" => "Подробнее",
            "MESS_NOT_AVAILABLE" => "Нет в наличии",
            "DETAIL_USE_VOTE_RATING" => "Y",
            "DETAIL_VOTE_DISPLAY_AS_RATING" => "rating",
            "DETAIL_USE_COMMENTS" => "Y",
            "DETAIL_BLOG_USE" => "Y",
            "DETAIL_VK_USE" => "N",
            "DETAIL_FB_USE" => "Y",
            "AJAX_OPTION_ADDITIONAL" => "",
            "USE_STORE" => "Y",
            "BIG_DATA_RCM_TYPE" => "personal",
            "FIELDS" => array(
                0 => "SCHEDULE",
                1 => "STORE",
                2 => "",
            ),
            "USE_MIN_AMOUNT" => "N",
            "STORE_PATH" => "/store/#store_id#",
            "MAIN_TITLE" => "Наличие на складах",
            "MIN_AMOUNT" => "10",
            "DETAIL_BRAND_USE" => "Y",
            "DETAIL_BRAND_PROP_CODE" => array(
                0 => "",
                1 => "BRAND_REF",
                2 => "",
            ),
            "COMPATIBLE_MODE" => "N",
            "SIDEBAR_SECTION_SHOW" => "Y",
            "SIDEBAR_DETAIL_SHOW" => "Y",
            "SIDEBAR_PATH" => "/catalog/sidebar.php",
            "COMPONENT_TEMPLATE" => "oshisha_catalog.catalog",
            "HIDE_NOT_AVAILABLE_OFFERS" => "N",
            "LABEL_PROP_MOBILE" => array(),
            "LABEL_PROP_POSITION" => "top-left",
            "COMMON_SHOW_CLOSE_POPUP" => "N",
            "PRODUCT_SUBSCRIPTION" => "Y",
            "DISCOUNT_PERCENT_POSITION" => "bottom-right",
            "SHOW_MAX_QUANTITY" => "Y",
            "MESS_BTN_SUBSCRIBE" => "Подписаться",
            "SIDEBAR_SECTION_POSITION" => "right",
            "SIDEBAR_DETAIL_POSITION" => "right",
            "USER_CONSENT" => "N",
            "USER_CONSENT_ID" => "0",
            "USER_CONSENT_IS_CHECKED" => "Y",
            "USER_CONSENT_IS_LOADED" => "N",
            "USE_MAIN_ELEMENT_SECTION" => "N",
            "DETAIL_STRICT_SECTION_CHECK" => "N",
            "SET_LAST_MODIFIED" => "N",
            "ADD_SECTIONS_CHAIN" => "Y",
            "USE_SALE_BESTSELLERS" => "Y",
            "FILTER_HIDE_ON_MOBILE" => "N",
            "INSTANT_RELOAD" => "N",
            "ADD_PROPERTIES_TO_BASKET" => "Y",
            "PARTIAL_PRODUCT_PROPERTIES" => "N",
            "USE_COMMON_SETTINGS_BASKET_POPUP" => "N",
            "COMMON_ADD_TO_BASKET_ACTION" => "ADD",
            "TOP_ADD_TO_BASKET_ACTION" => "ADD",
            "SECTION_ADD_TO_BASKET_ACTION" => "ADD",
            "DETAIL_ADD_TO_BASKET_ACTION" => array(
                0 => "ADD",
            ),
            "DETAIL_ADD_TO_BASKET_ACTION_PRIMARY" => array(
                0 => "ADD",
            ),
            "SEARCH_PAGE_RESULT_COUNT" => "50",
            "SEARCH_RESTART" => "Y",
            "SEARCH_NO_WORD_LOGIC" => "Y",
            "SEARCH_USE_LANGUAGE_GUESS" => "Y",
            "SEARCH_CHECK_DATES" => "Y",
            "SECTIONS_HIDE_SECTION_NAME" => "N",
            "LIST_PROPERTY_CODE_MOBILE" => array(),
            "LIST_PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
            "LIST_PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
            "LIST_ENLARGE_PRODUCT" => "PROP",
            "LIST_SHOW_SLIDER" => "Y",
            "LIST_SLIDER_INTERVAL" => "3000",
            "LIST_SLIDER_PROGRESS" => "N",
            "DETAIL_SET_CANONICAL_URL" => "N",
            "DETAIL_CHECK_SECTION_ID_VARIABLE" => "N",
            "SHOW_DEACTIVATED" => "N",
            "DETAIL_MAIN_BLOCK_PROPERTY_CODE" => array(),
            "DETAIL_BLOG_URL" => "catalog_comments",
            "DETAIL_BLOG_EMAIL_NOTIFY" => "N",
            "DETAIL_FB_APP_ID" => "",
            "DETAIL_IMAGE_RESOLUTION" => "16by9",
            "DETAIL_PRODUCT_INFO_BLOCK_ORDER" => "sku,props",
            "DETAIL_PRODUCT_PAY_BLOCK_ORDER" => "rating,price,priceRanges,quantityLimit,quantity,buttons",
            "DETAIL_SHOW_SLIDER" => "N",
            "DETAIL_DETAIL_PICTURE_MODE" => array(
                0 => "POPUP",
                1 => "MAGNIFIER",
            ),
            "DETAIL_ADD_DETAIL_TO_SLIDER" => "N",
            "DETAIL_DISPLAY_PREVIEW_TEXT_MODE" => "E",
            "MESS_PRICE_RANGES_TITLE" => "Цены",
            "MESS_DESCRIPTION_TAB" => "Описание",
            "MESS_PROPERTIES_TAB" => "Характеристики",
            "MESS_COMMENTS_TAB" => "Комментарии",
            "DETAIL_SHOW_POPULAR" => "Y",
            "DETAIL_SHOW_VIEWED" => "Y",
            "USE_GIFTS_DETAIL" => "Y",
            "USE_GIFTS_SECTION" => "Y",
            "USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
            "GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "3",
            "GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
            "GIFTS_DETAIL_BLOCK_TITLE" => "Выберите один из подарков",
            "GIFTS_DETAIL_TEXT_LABEL_GIFT" => "Подарок",
            "GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT" => "3",
            "GIFTS_SECTION_LIST_HIDE_BLOCK_TITLE" => "N",
            "GIFTS_SECTION_LIST_BLOCK_TITLE" => "Подарки к товарам этого раздела",
            "GIFTS_SECTION_LIST_TEXT_LABEL_GIFT" => "Подарок",
            "GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
            "GIFTS_SHOW_OLD_PRICE" => "Y",
            "GIFTS_SHOW_NAME" => "Y",
            "GIFTS_SHOW_IMAGE" => "Y",
            "GIFTS_MESS_BTN_BUY" => "Выбрать",
            "GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "3",
            "GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
            "GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "Выберите один из товаров, чтобы получить подарок",
            "STORES" => array(
                0 => "",
                1 => "",
            ),
            "USER_FIELDS" => array(
                0 => "",
                1 => "",
            ),
            "SHOW_EMPTY_STORE" => "Y",
            "SHOW_GENERAL_STORE_INFORMATION" => "N",
            "USE_BIG_DATA" => "N",
            "USE_ENHANCED_ECOMMERCE" => "N",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "LAZY_LOAD" => "Y",
            "LOAD_ON_SCROLL" => "N",
            "SHOW_404" => "Y",
            "MESSAGE_404" => "",
            "DISABLE_INIT_JS_IN_COMPONENT" => "N",
            "DETAIL_SET_VIEWED_IN_COMPONENT" => "N",
            "LIST_ENLARGE_PROP" => "-",
            "MESS_BTN_LAZY_LOAD" => "Показать ещё",
            "MESS_SHOW_MAX_QUANTITY" => "Наличие",
            "SHOW_SKU_DESCRIPTION" => "N",
            "DETAIL_MAIN_BLOCK_OFFERS_PROPERTY_CODE" => "",
            "FILE_404" => "",
            "SEF_URL_TEMPLATES" => array(
                "sections" => "",
                "element" => "product/#ELEMENT_CODE#/",
                "section" => "#SECTION_CODE#/",
                "compare" => "compare/",
                "smart_filter" => "#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/",
		    )
        );
    }
    public static function setProductsActiveUnit(array &$item, bool $isRawElement = false): void
    {
        if (defined('PROPERTY_ACTIVE_UNIT')) {
            if ($isRawElement && defined('IBLOCK_CATALOG')) {
                $db_props = CIBlockElement::GetProperty(IBLOCK_CATALOG, $item['PRODUCT_ID'] ?? $item['ID'], array("sort" => "asc"), array("CODE" => PROPERTY_ACTIVE_UNIT));
                if ($ar_props = $db_props->Fetch()) {
                    $activeUnitId = IntVal($ar_props["VALUE"]);
                } else {
                    $activeUnitId = false;
                }
            } else {
                $activeUnitId = $item['PROPERTIES'][PROPERTY_ACTIVE_UNIT]['VALUE'] ?? false;
            }
            if (!empty($activeUnitId)) {
                $item['ACTIVE_UNIT'] = CCatalogMeasure::GetList(array(), array("CODE" => $activeUnitId))->fetch();
                if (!empty($item['ACTIVE_UNIT'])) {
                    $item['ACTIVE_UNIT_FULL'] = $item['ACTIVE_UNIT']['MEASURE_TITLE'];
                    $item['ACTIVE_UNIT'] = $item['ACTIVE_UNIT']['SYMBOL_RUS'];
                    $isActiveUnitSet = true;
                }
            }
        }
        if (!isset($isActiveUnitSet)) {
            $item['ACTIVE_UNIT_FULL'] = 'Штука';
            $item['ACTIVE_UNIT'] = 'шт';
        }
    }
}
