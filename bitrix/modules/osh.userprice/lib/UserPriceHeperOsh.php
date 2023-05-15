<?php

namespace Enterego\UserPrice;

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockElement;
use CModule;
use CPrice;
use EntUserPriceGlobal;

/**
 * Вынесенная логика для поиска цены/типа цены индивидуально под пользователя.
 *
 * @package Enterego\Userprice
 */
class UserPriceHelperOsh
{
    private $priceIdToName = [];

    private $priceIdToPrice = [];

    private $productIdToSectionId = [];

    private $user_id = false;

    /**
     * @param $PRODUCT_ID
     * @return array
     */
    public static function GetPrices($PRODUCT_ID)
    {
        $PRODUCT_ID = intval(trim($PRODUCT_ID));

        $getValue = function() use ($PRODUCT_ID) {
            $rsPrices = CPrice::GetList([], compact('PRODUCT_ID'));

            $result = [];

            while ($arPrice = $rsPrices->GetNext()) {
                $priceTypeId = intval(trim($arPrice['CATALOG_GROUP_ID']));
                $result[$priceTypeId] = $arPrice;
            }

            return $result;
        };

        return EntUserPriceGlobal::GetCached($getValue, 'arProductPrice', $PRODUCT_ID);
    }

    /**
     * @param $PRODUCT_ID
     * @param $CATALOG_GROUP_ID
     * @return false|array
     */
    public static function GetPrice($PRODUCT_ID, $CATALOG_GROUP_ID)
    {
        $priceTypeId = intval(trim($CATALOG_GROUP_ID));

        $prices = self::GetPrices($PRODUCT_ID);
        $arPrice = &$prices[$priceTypeId];
        if(isset($arPrice)) {
            return $arPrice;
        }

        return false;
    }

    /**
     * Получение ID группы товара по
     *
     * @param $PRODUCT_ID
     * @return mixed
     * @throws SqlQueryException
     */
    public static function GetSectionID($PRODUCT_ID)
    {
        $PRODUCT_ID = intval(trim($PRODUCT_ID));

        $getValue = function() use ($PRODUCT_ID) {
            return PluginStatic::QueryCorrelatedID(
                'b_iblock_element',
                'id',
                $PRODUCT_ID,
                'IBLOCK_SECTION_ID'
            );
        };

        return EntUserPriceGlobal::GetCached($getValue, 'ProductSectionID', $PRODUCT_ID);
    }

    /**
     * Установить USER_ID в текущем контексте
     * (если не передан ID - записать ID текущего пользователя).
     *
     * @param null $USER_ID
     * @return false|int|null
     */
    public function user($USER_ID = null)
    {
        $USER_ID = intval($USER_ID);

        if($USER_ID) {
            return $this->user_id = $USER_ID;
        }

        if($this->user_id === false)
        {
            CModule::IncludeModule('sale');

            global $USER;

            if($USER) {
                $this->user_id = intval($USER->GetID());
            }
        }

        return $this->user_id;
    }

    /***
     * Добавить в контексте информацию о цене продукта,
     * которая ему установлена.
     *
     * @param string|int $PRODUCT_ID
     * @param string|int $price_type_id
     * @param string $name
     * @param null|string|int|float $price
     * @param null|string|int $IBLOCK_SECTION_ID
     */
    public function addProduct($PRODUCT_ID, $price_type_id, string $name, $price, $IBLOCK_SECTION_ID)
    {
        $PRODUCT_ID = intval($PRODUCT_ID);
        $IBLOCK_SECTION_ID = intval($IBLOCK_SECTION_ID);
        $price_type_id = intval($price_type_id);
        $price = floatval($price);

        if(!$PRODUCT_ID || !$price_type_id || !$name) {
            return;
        }

        $this->priceIdToName[$PRODUCT_ID][$price_type_id] = $name;
        $this->priceIdToPrice[$PRODUCT_ID][$price_type_id] = $price;
        $this->productIdToSectionId[$PRODUCT_ID] = $IBLOCK_SECTION_ID;
    }

    /**
     * Получить специальную цену на пользователя, если она задана,
     * из всех продуктов, сохраненных в контексте,
     * для пользователя с указанным user() ID (или пользователем по умолчанию)
     *
     * @param $productId
     * @return float|null
     * @throws SqlQueryException
     */
    public static function getForProduct($productId): ?float
    {
        $price_id = PluginStatic::GetPriceIdFromRule($productId);

        if (empty($price_id)) {
            return null;
        }

        try {
            $rsPriceList = PriceTable::getList([
                'filter' => ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $price_id]
            ]);
        } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
            return null;
        }
        if ($arPriceItem = $rsPriceList->fetch()) {
            return (float) $arPriceItem['PRICE'];
        }

        return null;
    }
}