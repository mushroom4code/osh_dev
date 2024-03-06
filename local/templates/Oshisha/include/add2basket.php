<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\Product\Basket;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Sale;
use Bitrix\Sale\Fuser;

$answer = ['QUANTITY' => 0, 'SUM_PRICE' => 0, 'STATUS' => ''];

if (CModule::IncludeModule("iblock") and CModule::IncludeModule("sale") and
    CModule::IncludeModule("catalog")) {
    if (!empty($_POST['product_data'])) {
        $product_data = json_decode($_POST['product_data']);
        if (!empty($product_data->ID)) {

            $product_id = $product_data->ID;
            $product_quantity = 0;

            if ($product_data->QUANTITY > 0) {
                $product_quantity = $product_data->QUANTITY;
            }
            // Получение корзины для текущего пользователя
            $basket = Sale\Basket::loadItemsForFUser(
                Fuser::getId(),
                Context::getCurrent()->getSite()
            );

            $price_id = USE_CUSTOM_SALE_PRICE ? SALE_PRICE_TYPE_ID : B2B_PRICE;

            if ($item = $basket->getExistsItem('catalog', $product_id)) {
                if ($product_quantity !== 0) {
                    $item->setField('QUANTITY', $product_quantity);
                } else {
                    $item->delete();
                }
            } else {

                $item = $basket->createItem('catalog', $product_id);
                $arFields = array(
                    'QUANTITY' => $product_quantity,
                    'CURRENCY' => CurrencyManager::getBaseCurrency(),
                    'LID' => Context::getCurrent()->getSite(),
                    'PRODUCT_PROVIDER_CLASS' => Basket::getDefaultProviderName(),
                    'PRICE_TYPE_ID' => $price_id,
                );

                $item->setFields($arFields);
            }

            $basket->save();
        }
        try {
            $answer['QUANTITY'] = round(array_sum($basket->getQuantityList() ?? 0));
        } catch (\Bitrix\Main\ArgumentNullException $e) {
            $answer['QUANTITY'] = 0;
        }
        try {
            $answer['SUM_PRICE'] = round($basket->getPrice() ?? 0);
        } catch (\Bitrix\Main\ArgumentNullException $e) {
            $answer['SUM_PRICE']= 0;
        }
        $answer['STATUS'] = 'success';
    }
}

header('Content-Type: application/json');
echo json_encode($answer);
