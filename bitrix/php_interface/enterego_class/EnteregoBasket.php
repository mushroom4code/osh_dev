<?php

namespace Enterego;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main;
use Bitrix\Main\Context;
use CIBlockElement;
use CModule;


Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleBasketBeforeSaved',
    array('Enterego\EnteregoBasket', 'OnSaleBasketBeforeSaved'));


class EnteregoBasket
{
    /** Формирование корзины идет после формирования свойств заказа
     *      поэтому в зависимости от профиля в заказе устанавливаем цены
     * @param $arUserResult
     * @param $request
     * @param $arParams
     * @param $arResult
     * @return void
     */
//    function OnSaleComponentOrderProperties(&$arUserResult, $request, &$arParams, &$arResult)
//    {
//        if ($arUserResult['PERSON_TYPE_ID']==PERSON_TYPE_BUYER && getUserType() && getCurrentPriceId()==B2B_PRICE) {
//            setCurrentPriceId(BASIC_PRICE);
//        }
//    }

    /**
     * @param \Bitrix\Main\Event $event
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function OnSaleBasketBeforeSaved(Main\Event $event)
    {
        //TODO на форме заказа вызывается на почти на любое действие
        CModule::IncludeModule('iblock') || die();
        CModule::IncludeModule('sale') || die();

        $product_prices = array();
        $new_basket_data = array();

        /** @var \Bitrix\Sale\Basket $basket */
        $basket = $event->getParameter("ENTITY");
        $basket_items = $basket->getBasketItems();
        $currentPriceTypeId = getCurrentPriceId();

        if (!empty($currentPriceTypeId)) {

            $origin_total_price = 0;

            /** @var \Bitrix\Sale\BasketItem $item */

            foreach ($basket_items as $item) {

                $product_id = $item->getProductId();
                $product_quantity = $item->getQuantity();

                $product_prices[$product_id]['QUANTITY'] = $product_quantity;
                $product_prices[$product_id]['ITEM'] = $item;
                $new_basket_data[$product_id]['QUANTITY'] = $product_quantity;

                $origin_price = self::loadProductPrice($product_id, RETAIL_PRICE);
                if ($origin_price) {
                    $new_basket_data[$product_id]['PRICE'] = $origin_price;
                    $origin_total_price += $product_quantity * $origin_price;
                    $product_prices[$product_id]['PRICE'] = $origin_price;
                    $product_prices[$product_id]['PRICE_TYPE'] = RETAIL_PRICE;
                }
            }

            if ($origin_total_price <= 10000) {
                $price_id = RETAIL_PRICE;
            } elseif ($origin_total_price <= 30000) {
                $price_id = BASIC_PRICE;
            } else {
                $price_id = B2B_PRICE;
            }


            foreach ($product_prices as $product_id => $price_data) {

                $propsUseSale = CIBlockElement::GetProperty(
                    IBLOCK_CATALOG,
                    $product_id,
                    array(),
                    array('CODE' => 'USE_DISCOUNT'));
                $newProp = $propsUseSale->Fetch();

                if (USE_CUSTOM_SALE_PRICE || $newProp['VALUE_XML_ID'] == 'true') {

                    $price_type = "CATALOG_PRICE_" . SALE_PRICE_TYPE_ID;
                    $result = CIBlockElement::GetList(
                        array(),
                        array("ID" => $product_id),
                        false,
                        false,
                        array("$price_type"));

                    if ($ar_res = $result->fetch()) {
                        if (((int)$price_data['PRICE'] > (int)$ar_res["$price_type"]) && !empty($ar_res["$price_type"])) {
                            $product_prices[$product_id]['PRICE'] = $ar_res["$price_type"];
                            $product_prices[$product_id]['PRICE_ID'] = SALE_PRICE_TYPE_ID;
                        } else {
                            $ids = USE_CUSTOM_SALE_PRICE ? $currentPriceTypeId : $price_id;
                            $price_ids = "CATALOG_PRICE_" . $ids;
                            $res = CIBlockElement::GetList(
                                array(),
                                array("ID" => $product_id),
                                false,
                                false,
                                array("$price_ids"));

                            if ($arData = $res->fetch()) {
                                $product_prices[$product_id]['PRICE'] = $arData["$price_ids"];
                                $product_prices[$product_id]['PRICE_ID'] = SALE_PRICE_TYPE_ID;
                            }
                        }
                    }
                } else {

                    $res = CIBlockElement::GetList(
                        array(),
                        array("ID" => $product_id),
                        false,
                        false,
                        array("CATALOG_PRICE_$price_id"));

                    if ($ar_res = $res->fetch()) {
                        $product_prices[$product_id]['PRICE'] = $ar_res["CATALOG_PRICE_$price_id"];
                        $product_prices[$product_id]['PRICE_ID'] = $price_id;
                    }
                }


                $item = $price_data['ITEM'];

                $item->setFields([
                    'QUANTITY' => $price_data['QUANTITY'],
                    'CURRENCY' => CurrencyManager::getBaseCurrency(),
                    'LID' => Context::getCurrent()->getSite(),
                    'PRICE' => $product_prices[$product_id]['PRICE'],
                    'CUSTOM_PRICE' => 'N',
                    'BASE_PRICE' => $product_prices[$product_id]['PRICE'],
                    'PRICE_TYPE_ID' => $product_prices[$product_id]['PRICE_ID'],
                ]);

            }
        }

    }

    /**
     * @param $product_id
     * @param $price_type_id
     * @return int|null
     */
    private static function loadProductPrice($product_id, $price_type_id): ?int
    {
        $res = CIBlockElement::GetList(
            array(),
            array("ID" => $product_id),
            false,
            false,
            ["CATALOG_PRICE_$price_type_id"]);

        if ($ar_res = $res->Fetch()) {
            return $ar_res["CATALOG_PRICE_$price_type_id"];
        }
        return null;
    }
}