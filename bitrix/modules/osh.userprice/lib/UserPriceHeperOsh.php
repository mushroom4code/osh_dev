<?php

namespace Enterego\UserPrice;

use Bitrix\Main\Db\SqlQueryException;
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
     * @param string|int $PRODUCT_ID
     * @return null|special
     */
    public function getForProduct($PRODUCT_ID): ?Special
    {
        $PRODUCT_ID = intval($PRODUCT_ID);
        $IBLOCK_SECTION_ID = $this->productIdToSectionId[$PRODUCT_ID];
        $USER_ID = intval($this->user());
        $price_id = PluginStatic::GetPriceIdFromRule($PRODUCT_ID, $IBLOCK_SECTION_ID, $USER_ID);

        if(!$price_id) {
            return null;
        }

        $result = new Special();

        $result->PRODUCT_ID = $PRODUCT_ID;
        $result->IBLOCK_SECTION_ID = $IBLOCK_SECTION_ID;
        $result->priceId = $price_id;

        if(isset($this->priceIdToPrice[$PRODUCT_ID]))
        {
            if (!isset($this->priceIdToPrice[$PRODUCT_ID][$price_id]))
            {
                // Индивидуальная цена задана,
                // Но она не была получена ранее в массив priceIdToPrice
                // тогда получим её из базы.
                // TODO FIXME WIP

                return null;    // у нас есть подходящее правило,
                // но нет такой заданной цены в этом контексте (у товара)
            }

            $result->priceName = $this->priceIdToName[$PRODUCT_ID][$price_id];
            $result->price = $this->priceIdToPrice[$PRODUCT_ID][$price_id];
        }

        if($result->priceId)
        {
            if(!$result->price)
            {
                global $DB;

                $res = $DB->Query("SELECT `PRICE` FROM b_catalog_price WHERE
                                PRODUCT_ID = {$PRODUCT_ID} AND CATALOG_GROUP_ID = {$price_id} LIMIT 1");

                if ($arPrice = $res->GetNext()) {
                    $result->price = $arPrice['PRICE'];
                }
            }
        }

        return $result;
    }
}