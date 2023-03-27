<?php

namespace Enterego;

use CCatalogSku;
use CFile;
use CIBlockElement;
use CIBlockSection;
use CModule;
use CSaleBasket;
use Bitrix\Sale\Order;
use Bitrix\Highloadblock\HighloadBlockTable;
use DateInterval;
use DateTime;


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
            array("ID", "DEPTH_LEVEL","NAME")
        );

        $name = [];
        while ($arGrp = $scRes->Fetch()) {
            if ($arGrp['DEPTH_LEVEL'] == 1) {
                $name = $arGrp;
            }
        }
        return $name;
    }

    public static function basketCustomSort(&$arResult, $type = 'basket')
    { //Сортировка в корзине
        $data = $result = [];

        if (!empty($arResult)) {
            foreach ($arResult as $key_basket => &$basket_item) {
                $id = $type == 'basket' ? $basket_item['PRODUCT_ID'] : $basket_item['data']['PRODUCT_ID'];

                \CModule::IncludeModule("iblock");
                $rsElement = CIBlockElement::GetList(array(), array('ID' => $id), false, false);
                if ($arElement = $rsElement->Fetch()) {
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

                    if ($type == 'basket') {
                        $basket_item['BASKET_KEY'] = $key_basket;
                        $data[$parent_name . '_' . $parent_id][] = $basket_item;
                        if (self::productIsGift($id)) {
                            $basket_item['GIFT'] = true;
                            $basket_item['SHOW_DISCOUNT_PRICE'] = false;
                            $basket_item['SHOW_MAX_PRICE'] = false;
                        }
                    } else {
                        $data[$parent_name . '_' . $parent_id][$basket_item['id']] = $basket_item;
                    }
                }
            }
            if (!empty($data));

            foreach ($data as $main_brand_name => $data_item) {
                $temp_ar = [];
                foreach ($data_item as $product_item) {
                    if ($main_brand_name == 'presents') $product_item['DETAIL_PAGE_URL'] = 'javascript:void()';
//                    $product_item['NOT_AVAILABLE'] = 'N';
                    $rsData = CIBlockElement::GetList(
                        array(),
                        array('ID' => $product_item['PRODUCT_ID']),
                        false,
                        false,
                        array('IBLOCK_SECTION_ID')
                    );
                    if ($arData = $rsData->Fetch()) {
                        if (!empty($arData['IBLOCK_SECTION_ID'])) {
                            $section_id = $arData['IBLOCK_SECTION_ID'];
                            $iblock_id = $arData['IBLOCK_ID'];
                        } else {
                            $product = CCatalogSKU::GetProductInfo($product_item['PRODUCT_ID']);
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
                        $itemInfo = self::getParentElementId((string)$iblock_id, (string)$section_id);
                        $parentNameCategory = $itemInfo['NAME'];
                        $parent_id = $itemInfo['ID'];
                        if (!empty($parent_id) && !empty($parentNameCategory)) {
                            $temp_ar[$parentNameCategory . '_' . $parent_id][] = $product_item['ID'];
                        } else {
                            $temp_ar["Без категории"][] = $product_item;
                        }
                    }
                }
                ksort($temp_ar);

                $result[$main_brand_name] = [];
                foreach ($temp_ar as $products) {
                    foreach ($products as $product) {
                        array_push($result[$main_brand_name], $product);
                    }
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
}
