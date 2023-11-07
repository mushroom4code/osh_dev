<?php

use Enterego\EnteregoBasket;
use Enterego\EnteregoHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/handlers/discountpreset/simpleproduct.php");

$item = &$arResult['ITEM'];
$idPick = $item["PREVIEW_PICTURE"]["ID"] ?? false;
if (empty($idPick)) {
    $idPick = $item["PREVIEW_PICTURE"];
}
if ($idPick) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $idPick,
            array(
                'width' => 160,
                'height' => 160
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL
        ),
        CASE_UPPER
    );

} elseif ($item["DETAIL_PICTURE"]["ID"]) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $item["DETAIL_PICTURE"]["ID"],
            array(
                'width' => 160,
                'height' => 160
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL
        ),
        CASE_UPPER
    );
}

$useDiscount = ($item['PROPERTIES']['USE_DISCOUNT']['VALUE'] ?? 'Нет') === 'Да';
$item['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($item['ITEM_ALL_PRICES'][0],
    $useDiscount, $item['ID']);

$arResult['USED_DISCOUNTS'] = [];
if (!empty($arParams['DISCOUNTS'])) {
    foreach ($arParams['DISCOUNTS'] as $discount) {
        if (in_array($item['ID'], $discount['PRODUCTS'])) {
            $arResult['USED_DISCOUNTS'][$discount['ID']] = $discount;
        }
    }
}


$measureRatio = \Bitrix\Catalog\MeasureRatioTable::getList(array(
    'select' => array('RATIO'),
    'filter' => array('=PRODUCT_ID' => $item['ID'])
))->fetch()['RATIO'];

$item['MEASURE_RATIO'] = $item['ITEM_MEASURE_RATIOS'][$item['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];

EnteregoHelper::setProductsActiveUnit($item);