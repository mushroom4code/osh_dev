<?php

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Loader;
use Enterego\EnteregoBasket;
use Enterego\EnteregoHelper;
use Enterego\EnteregoSettings;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
Loader::includeModule('main');

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$action = $request->get('action');
if ($action === 'groupedProduct') {
    $prodId = $request->get('prodId');
    $prices = [];
    $listGroupedProduct = $request->get('prodIDS');
    $arItems['GROUPED_PRODUCTS'] = $arItems['GROUPED_PROPS_DATA'] = $arResult = [];
    if (!empty($prodId)) {
        $arResult = EnteregoHelper::getListGroupedProduct($prodId, $listGroupedProduct, $arItems);
        $arResult['SETTING'] = EnteregoSettings::getDataPropOffers();
        $rsPrice = PriceTable::getList([
            'select' => ['PRODUCT_ID', 'PRICE','CATALOG_GROUP_ID','CATALOG_GROUP'],
            'filter' => [
                'PRODUCT_ID' => $listGroupedProduct,
                'CATALOG_GROUP_ID' => [SALE_PRICE_TYPE_ID, BASIC_PRICE, B2B_PRICE, RETAIL_PRICE],
            ],
        ])->fetchAll();
        foreach ($rsPrice as $price){
            $prices[$price['PRODUCT_ID']]['PRICES'][$price['CATALOG_GROUP_ID']] = $price;
        }
        foreach($prices as $productId => $product ){
            $arResult['GROUPED_PRODUCTS'][$productId]['PRICES'] = EnteregoBasket::getPricesArForProductTemplate($product,
                false, $productId);
        }
    }
    echo json_encode($arResult);
    exit();
} else {
    echo '';
    exit();
}