<?php

namespace Enterego;

use CFile;
use CIBlockElement;
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
            $result = HighloadBlockTable::getList(array('filter'=>array('=NAME'=>$hlName)));
            if($row = $result->fetch())
            {
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
        } else if ($method === 'VKUS') {
            $rsElement = CIBlockElement::GetList(array(), array('ID' => $id), false, false, array());
            while ($arElement = $rsElement->Fetch()) {
                $res = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $id, false, array('CODE' => 'VKUS'));
                while ($results = $res->Fetch()) {
                    if ($results['CODE'] === 'VKUS' && !empty($results['VALUE_XML_ID'])) {
                        $colorId = explode('#', $results['VALUE_XML_ID']);
                        $arItems['VKUS'][] = ['NAME' => $results['VALUE_ENUM'],
                            'VALUE' => $colorId[1],
                            'ID' => $colorId[0]];
                    }
                }
            }
        }

        return $arItems;
    }


    public static function getParentElementName($iblock_id, $iblock_section_id)
    {
        $scRes = \CIBlockSection::GetNavChain(
            $iblock_id,
            $iblock_section_id,
            array("NAME", "DEPTH_LEVEL")
        );

        $name = '';
        while ($arGrp = $scRes->Fetch()) {
            if ($arGrp['DEPTH_LEVEL'] == 1) {
                $name = $arGrp['NAME'];
            }
        }
        return $name;
    }

    public static function getParentElementId($iblock_id, $iblock_section_id)
    {
        $scRes = \CIBlockSection::GetNavChain(
            $iblock_id,
            $iblock_section_id,
            array("ID", "DEPTH_LEVEL")
        );

        $name = '';
        while ($arGrp = $scRes->Fetch()) {
            if ($arGrp['DEPTH_LEVEL'] == 1) {
                $name = $arGrp['ID'];
            }
        }
        return $name;
    }

    public static function basketCustomSort($arResult, $type = 'basket')
    { //Сортировка в корзине
        $data = $result = [];

        if (!empty($arResult)) {
            foreach ($arResult as $key_basket => $basket_item) {
                $id = $type == 'basket' ? $basket_item['PRODUCT_ID'] : $basket_item['data']['PRODUCT_ID'];

                \CModule::IncludeModule("iblock");
                $rsElement = CIBlockElement::GetList(array(), array('ID' => $id), false, false);
                if ($arElement = $rsElement->Fetch()) {
                    $parent_name = self::getParentElementName($arElement['IBLOCK_ID'], $arElement['IBLOCK_SECTION_ID']);
                    $parent_id = self::getParentElementId($arElement['IBLOCK_ID'], $arElement['IBLOCK_SECTION_ID']);
                    if (trim($parent_name) == 'Кальян') $parent_name = 'Кальяны';

                    if ($type == 'basket') {
                        $basket_item['BASKET_KEY'] = $key_basket;
                        $data[$parent_name . '_' . $parent_id][] = $basket_item;
                    } else {
                        $data[$parent_name . '_' . $parent_id][$basket_item['id']] = $basket_item;
                    }
                }
            }
            if (!empty($data)) ksort($data);

            foreach ($data as $main_brand_name => $data_item) {
                $temp_ar = [];
                foreach ($data_item as $product_item) {
                    if ($main_brand_name == 'presents') $product_item['DETAIL_PAGE_URL'] = 'javascript:void()';

                    $rsData = CIBlockElement::GetList(array(), array('ID' => $product_item['PRODUCT_ID']),
                        false, false, array('IBLOCK_SECTION_ID'));
                    if ($arData = $rsData->Fetch()) {
                        $parentNameCategory = EnteregoHelper::getParentElementName($product_item['PRODUCT_ID'], $arData['IBLOCK_SECTION_ID']);
                        $parent_id = self::getParentElementId($arData['IBLOCK_ID'], $arData['IBLOCK_SECTION_ID']);
                        if (!empty($parent_id)) {
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
                        if (self::productIsGift($product['PRODUCT_ID'])) {
                            $product['GIFT'] = true;
                            $product['SHOW_DISCOUNT_PRICE'] = false;
                            $product['SHOW_MAX_PRICE'] = false;
                        }

                        array_push($result[$main_brand_name], $product);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string $duringTime
     * @return bool
     * @throws \Exception
     */
    public static function checkNewProduct(string $duringTime): bool
    {
        $nowDate = date("d.m.Y H:i:s");
        $date = new DateTime($nowDate);
        $dateAMonthAgo = $date->sub(new DateInterval('P0Y1M0DT0H0M0S'))->format('d.m.Y H:i:s');
        if (strtotime($duringTime) < strtotime($dateAMonthAgo)) {
            return false;
        }
        return true;
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
