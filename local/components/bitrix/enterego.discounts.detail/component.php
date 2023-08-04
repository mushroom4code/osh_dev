<?php
/**
 * @var $arParams
 * @var $APPLICATION
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

Bitrix\Main\Loader::includeModule('sale');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/handlers/discountpreset/simpleproduct.php");

$discount_id = substr($arParams['CODE'], (strpos($arParams['CODE'], '_d') + 2));
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

$arResult['DISCOUNT'] = $arProductDiscounts;
$arResult['DISCOUNT']['ACTIONS_LIST_MODIFIED'] = $arResult['DISCOUNT']['ACTIONS_LIST'];
$arResult['DISCOUNT']['ACTIONS_LIST_MODIFIED']['CHILDREN'] = [];
$EnteregoDiscountHelperObject = new \Enterego\EnteregoDiscountHelper();
$EnteregoDiscountHelperObject->recursiveActionListModifying($arResult['RAW_DISCOUNT']['ACTIONS_LIST']['CHILDREN'], $arResult['RAW_DISCOUNT']['ACTIONS_LIST_MODIFIED']);
$arResult['DISCOUNT'] = $arDiscounts;

$GLOBALS['DISCOUNT_FILTER'][] = $EnteregoDiscountHelperObject->parseCondition(
    $arResult['RAW_DISCOUNT']['ACTIONS_LIST_MODIFIED'],
    [
        'INCLUDE_SUBSECTIONS' => "Y",
        'HIDE_NOT_AVAILABLE_OFFERS' => "N"
    ]
);

$this->IncludeComponentTemplate();