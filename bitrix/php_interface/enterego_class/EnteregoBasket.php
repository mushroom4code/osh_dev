<?php

namespace Enterego;

use Bitrix\Catalog\PriceTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main;
use Bitrix\Main\Context;
use CIBlockElement;
use CModule;
use CUser;
use Enterego\UserPrice\PluginStatic;
use Enterego\UserPrice\UserPriceHelperOsh;

Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleBasketBeforeSaved',
    array('Enterego\EnteregoBasket', 'OnSaleBasketBeforeSaved'));
Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleComponentOrderProperties',
    ['Enterego\EnteregoBasket', 'OnSaleBasketBeforeSaved']);

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

        $userId = CUser::GetID();
        $currentPriceTypeId = getCurrentPriceId();
        if (!empty($currentPriceTypeId)) {

            $origin_total_price = 0;

            /** @var \Bitrix\Sale\BasketItem $item */

            foreach ($basket_items as $item) {

                $product_id = $item->getProductId();
                $product_quantity = $item->getQuantity();


                if (!isset($product_prices[$product_id])) {
                    if (USE_CUSTOM_SALE_PRICE) {
                        $price_id = "CATALOG_PRICE_" . SALE_PRICE_TYPE_ID;
                        $result = CIBlockElement::GetList(array(), array("ID" => $product_id), false,
                            false, array("$price_id"));

                        if ($ar_res = $result->fetch()) {
                            if (!empty($ar_res["$price_id"])) {
                                $product_prices[$product_id] = $ar_res["$price_id"];
                            } else {
                                $price_ids = "CATALOG_PRICE_" . $currentPriceTypeId;
                                $res = CIBlockElement::GetList(array(), array("ID" => $product_id), false,
                                    false, array("$price_ids"));
                                if ($arData = $res->fetch()) {
                                    $product_prices[$product_id] = $arData["$price_ids"];
                                }
                            }
                        }
                    } else {
                        if (!isset($product_prices[$product_id])) {
                            $propsUseSale = CIBlockElement::GetProperty(12, $product_id,
                                array(), array('CODE' => 'USE_DISCOUNT'));
                            $newProp = $propsUseSale->Fetch();


                            $origin_price = self::loadProductPrice($product_id, BASIC_PRICE);
                            if ($origin_price) {
                                $origin_total_price += $product_quantity * $origin_price;
                                $product_prices[$product_id]['PRICE'] = $origin_price;
                                $product_prices[$product_id]['PRICE_TYPE'] = BASIC_PRICE;
                            }

                            if ($newProp['VALUE_XML_ID'] == 'true') {
                                $price_id = SALE_PRICE_TYPE_ID;
                            } else {
                                if ($origin_total_price <= 10000) {
                                    $price_id = RETAIL_PRICE;
                                } elseif ($origin_total_price <= 30000) {
                                    $price_id = BASIC_PRICE;
                                } else {
                                    $price_id = B2B_PRICE;
                                }
                            }

                            $res = CIBlockElement::GetList(array(), array("ID" => $product_id), false,
                                false, array("CATALOG_PRICE_$price_id"));
                            if ($ar_res = $res->fetch()) {
                                $product_prices[$product_id] = $ar_res["CATALOG_PRICE_$price_id"];
                            }
                        }
                    }
                }
                $new_basket_data[$product_id]['QUANTITY'] = $product_quantity;
            }

            /** @var \Bitrix\Sale\Basket $basket */

            foreach ($product_prices as $product_id => $price_data) {

                if (!$item = $basket->getExistsItem('catalog', $product_id)) {
                    $qty = $new_basket_data[$product_id]['QUANTITY'];
                    $item = $basket->createItem('catalog', $product_id);
                    $item->setFields([
                        'QUANTITY' => $qty,
                    ]);
                    $basket->save();
                }
                $propsUseSale = CIBlockElement::GetProperty(12, $product_id,
                    array(), array('CODE' => 'USE_DISCOUNT'));
                $newProp = $propsUseSale->Fetch();
                $price_id = USE_CUSTOM_SALE_PRICE || $newProp['VALUE_XML_ID'] == 'true' ?
                    SALE_PRICE_TYPE_ID : $currentPriceTypeId;
                $new_basket_data[$product_id]['PRICE'] = $price_data;
                $item->setFields([
                    'QUANTITY' => $new_basket_data[$product_id]['QUANTITY'],
                    'CURRENCY' => CurrencyManager::getBaseCurrency(),
                    'LID' => Context::getCurrent()->getSite(),
                    'PRICE' => $new_basket_data[$product_id]['PRICE'],
                    'CUSTOM_PRICE' => 'N',
                    'BASE_PRICE' => $new_basket_data[$product_id]['PRICE'],
                    'PRICE_TYPE_ID' => $price_id,
                ]);

            }
        }

    }

    private static function loadProductPrice($product_id, $price_type_id): ?int
    {
        $res = CIBlockElement::GetList(array(), array("ID" => $product_id), false, false, ["CATALOG_PRICE_$price_type_id"]);
        if ($ar_res = $res->Fetch()) {
            return $ar_res["CATALOG_PRICE_$price_type_id"];
        }
        return null;
    }
}
