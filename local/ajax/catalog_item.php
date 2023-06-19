<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Context;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Enterego\EnteregoBasket;
use Enterego\EnteregoHelper;
use Enterego\EnteregoSettings;

CModule::IncludeModule("iblock");
Loader::includeModule('main');

$request = Context::getCurrent()->getRequest();
$action = $request->get('action');
if ($action === 'groupedProduct') {
    $prodId = $request->get('prodId');
    $prices = $rsPrice = [];
    $listGroupedProduct = $request->get('prodIDS');
    $arItems['GROUPED_PRODUCTS'] = $arItems['GROUPED_PROPS_DATA'] = $arResult = [];
    if (!empty($prodId)) {
        $arResult = EnteregoHelper::getListGroupedProduct($prodId, $listGroupedProduct, $arItems);
        $arResult['SETTING'] = EnteregoSettings::getDataPropOffers();
        $arResult['PRICE_GREAT'] = BASIC_PRICE;
        $arResult['SALE'] = USE_CUSTOM_SALE_PRICE;

        $rsPrice = PriceTable::getList([
            'select' => ['PRODUCT_ID', 'PRICE', 'CATALOG_GROUP_ID', 'CATALOG_GROUP'],
            'filter' => [
                'PRODUCT_ID' => $listGroupedProduct,
                'CATALOG_GROUP_ID' => [SALE_PRICE_TYPE_ID, BASIC_PRICE, B2B_PRICE, RETAIL_PRICE],
            ],
        ])->fetchAll();

        foreach ($rsPrice as $price) {
            $prices[$price['PRODUCT_ID']]['PRICES'][$price['CATALOG_GROUP_ID']] = $price;
        }

        foreach ($prices as $productId => $product) {
            if (isset($arResult['GROUPED_PRODUCTS'][$productId])) {
                try {
                    $useDiscount = $arResult['GROUPED_PRODUCTS'][$productId]['PROPERTIES']['USE_DISCOUNT']['VALUE_XML_ID'];
                    $arResult['GROUPED_PRODUCTS'][$productId]['PRICES'] =
                        EnteregoBasket::getPricesArForProductTemplate($product, $useDiscount, $productId);
                } catch (SqlQueryException|LoaderException $e) {
                    $arResult['GROUPED_PRODUCTS'][$productId]['PRICES'] = [];
                }
            }
        }

        $dbBasketItems = CSaleBasket::GetList(
            array("NAME" => "ASC", "ID" => "ASC"),
            array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL"),
            false,
            false,
            array("ID", "PRODUCT_ID", "QUANTITY",)
        );
        while ($arItems = $dbBasketItems->Fetch()) {
            if(isset($arResult['GROUPED_PRODUCTS'][$arItems["PRODUCT_ID"]])){
                $arResult['GROUPED_PRODUCTS'][$arItems["PRODUCT_ID"]]['ACTUAL_BASKET'] = $arItems["QUANTITY"];
            }
        }
    }
    echo json_encode($arResult);
} else {
    echo '';
}
exit();