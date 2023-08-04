<?php
use Enterego\EnteregoBasket;
use Enterego\EnteregoHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/handlers/discountpreset/simpleproduct.php");

$item = &$arResult['ITEM'];

if ($item["PREVIEW_PICTURE"]["ID"]) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $item["PREVIEW_PICTURE"]["ID"],
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
