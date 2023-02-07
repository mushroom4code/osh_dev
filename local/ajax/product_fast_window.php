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

if (!empty($product_id) && $action === 'get_info_on_product_id') {

    $productArray = Catalog\Model\Product::getList([
        'select' => [
            'ID', 'QUANTITY', 'VAT_ID', 'VAT_INCLUDED', 'CAN_BUY_ZERO', 'CAN_BUY_ZERO_ORIG', 'NEGATIVE_AMOUNT_TRACE',
            'NEGATIVE_AMOUNT_TRACE_ORIG', 'PRICE_TYPE', 'RECUR_SCHEME_TYPE', 'RECUR_SCHEME_LENGTH', 'TRIAL_PRICE_ID',
            'WITHOUT_ORDER', 'SELECT_BEST_PRICE', 'TMP_ID', 'PURCHASING_PRICE', 'PURCHASING_CURRENCY', 'BARCODE_MULTI',
            'SUBSCRIBE', 'SUBSCRIBE_ORIG', 'TYPE', 'BUNDLE', 'AVAILABLE', 'TIMESTAMP_X'
        ],
        'filter' => ['=ID' => $product_id]
    ]);
    $result = $productArray->fetch();
    $propertyArray = CIBlockElement::GetProperty('12', $product_id, array("sort" => "asc"), array("CODE" => "BREND"));
}