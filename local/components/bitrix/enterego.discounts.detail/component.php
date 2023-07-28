<?php
/**
 * @var $arParams
 * @var $APPLICATION
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;


$discount_id = substr($arParams['CODE'], (strpos($arParams['CODE'], '_d') + 2));
$aaa = 'aaa';
Bitrix\Main\Loader::includeModule('sale');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/handlers/discountpreset/simpleproduct.php");

$arResult['DISCOUNT_IBLOCK'] = Bitrix\Iblock\IblockTable::getList([
    'filter' => ['IBLOCK_TYPE_ID' => 'discounts', 'CODE' => $arParams['CODE']],
])->fetch();

$arDiscounts = [];
$arProductDiscounts = \Bitrix\Sale\Internals\DiscountTable::getList([
    'filter' => [
        'ID' => $discount_id,
    ],
    'select' => [
        "*"
    ]
])->fetch();

$discountObj = new Sale\Handlers\DiscountPreset\SimpleProduct();
$discount = $discountObj->generateState($arProductDiscounts);

$arDiscounts['PRODUCTS'] = $discount['discount_product']; // товары
$arDiscounts['TYPE'] = $discount['discount_type'];
$arDiscounts['GROUPS'] = $discount['discount_section'];
$arDiscounts['VALUE'] = $discount['discount_value'];

$arResult['RAW_DISCOUNT'] = $arProductDiscounts;
$arResult['DISCOUNT'] = $arDiscounts;

$this->IncludeComponentTemplate();

