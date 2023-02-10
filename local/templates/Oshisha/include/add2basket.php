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
            if ($product_data->QUANTITY > 0) {
                $product_quantity = $product_data->QUANTITY;
            } else {
                $product_quantity = 0;
            }
            // Получение корзины для текущего пользователя
            $basket = Sale\Basket::loadItemsForFUser(
                Fuser::getId(),
                Context::getCurrent()->getSite()
            );

            // Скидки
            $arFilter = array(
                '=ID' => $product_id,
                'ACTIVE' => 'Y',
            );
            $resU = CIBlockElement::GetList(array(), $arFilter, false, false, array('PROPERTY_USE_DISCOUNT'));
            while ($rProd = $resU->Fetch()) {
                $useDiscount = $rProd['PROPERTY_USE_DISCOUNT_VALUE'];
            }

            if (USE_CUSTOM_SALE_PRICE || $useDiscount == 'Да') {
                $price_id = SALE_PRICE_TYPE_ID;
            } else {
                $price_id = $GLOBALS['PRICE_TYPE_ID'];
            }

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
        $answer['QUANTITY'] = round(array_sum($basket->getQuantityList()));
        $answer['SUM_PRICE'] = round($basket->getPrice());
        $answer['STATUS'] = 'success';
    }
}

header('Content-Type: application/json');
echo json_encode($answer);
