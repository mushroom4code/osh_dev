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
        $rsRes = CIBlockElement::GetList([], ['ID' => $productId, 'SECTION_ID' => CATALOG_GIFT_ID]);
        return $rsRes->SelectedRowsCount() > 0;
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
