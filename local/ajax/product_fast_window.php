<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Catalog;

CModule::IncludeModule("iblock");
Loader::includeModule('main');

$request = Context::getCurrent()->getRequest();
$product_id = $request->get('product_id');
$action = $request->get('action');
$product = [];

if (!empty($product_id) && $action === 'get_info_on_product_id') {

    $res = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        ['IBLOCK_ID' => 12, 'ID' => $product_id],
        false,
        [],
        ['ID', 'QUANTITY', 'PRICE_TYPE', 'DETAIL_PAGE_URL', 'CHECK_QUANTITY', 'MAX_QUANTITY', 'STEP_QUANTITY',
            'QUANTITY_FLOAT', 'ITEM_PRICE_MODE', 'ITEM_PRICES', 'PROPERTY_MORE_PHOTO_VALUE', 'DETAIL_PICTURE', 'QUANTITY_ACTUAL', 'ACTUAL_BASKET',
            'CATALOG_PRICE_' . SALE_PRICE_TYPE_ID, 'CATALOG_PRICE_' . BASIC_PRICE, 'CATALOG_PRICE_' . RETAIL_PRICE,
            'CATALOG_PRICE_' . B2B_PRICE, 'PROPERTY_ADVANTAGES_PRODUCT_VALUE', 'PICTURE', 'BUY_LINK', 'QUANTITY_ID', 'DETAIL_PICTURE',
            'PROPERTY_USE_DISCOUNT_VALUE', 'NAME']
    );
//    foreach ($actualItem['ITEM_ALL_PRICES'] as $key => $PRICE) {
//
//        foreach ($PRICE['PRICES'] as $price_key => $price_val) {
//
//
//            if (USE_CUSTOM_SALE_PRICE || $useDiscount['VALUE_XML_ID'] == 'true') {
//                if ($price_key == SALE_PRICE_TYPE_ID) {
//                    $price['SALE_PRICE'] = $price_val;
//                }
//            }
//
//            if ((int)$price_val['PRICE_TYPE_ID'] === RETAIL_PRICE) {
//                $price['PRICE_DATA'][0] = $price_val;
//                $price['PRICE_DATA'][0]['NAME'] = 'Розничная (до 10к)';
//            } else if ((int)$price_val['PRICE_TYPE_ID'] === BASIC_PRICE) {
//                $price['PRICE_DATA'][1] = $price_val;
//                $price['PRICE_DATA'][1]['NAME'] = 'Основная (до 30к)';
//            } elseif ((int)$price_val['PRICE_TYPE_ID'] === B2B_PRICE) {
//                $price['PRICE_DATA'][2] = $price_val;
//                $price['PRICE_DATA'][2]['NAME'] = 'b2b (от 30к)';
//            }
//            ksort($price['PRICE_DATA']);
//        }
//    }
    $result = $res->Fetch();
    $result['DETAIL_PICTURE'] =  CFile::GetPath($result["DETAIL_PICTURE"]);
    $product['product'] = $result;

    $product['result'] = 'success';
}

echo json_encode($product);
exit();