<?php

/** @var $arParams */
/** @var $APPLICATION */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

Loader::includeModule("iblock");
Loader::includeModule("sale");
Loader::includeModule("catalog");

$nav = new \Bitrix\Main\UI\PageNavigation("nav-discounts");
$nav->allowAllRecords(true)
    ->setPageSize(10)
    ->initFromUri();

$res = Bitrix\Iblock\IblockTable::getList([
    'filter' => ['IBLOCK_TYPE_ID' => 'discounts', 'ACTIVE' => 'Y'],
    'order' => ['ID' => 'asc'],
]);

$discountsIds = [];
while ($iblock = $res->fetch()) {
    $arResult['DISCOUNTS_IBLOCKS'][$iblock['ID']] = $iblock;
    $discountsIds[$iblock["ID"]] = substr($iblock['CODE'], (strpos($iblock['CODE'], '_d') + 2));
}

$discountsRes = \Bitrix\Sale\Internals\DiscountTable::getList([
    'filter' => [
        'ID' => $discountsIds,
        'ACTIVE' => 'Y'
    ],
    'order' => [
        'ACTIVE_TO' => 'desc'
    ],
    'select' => [
        '*'
    ],
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
    'count_total' => true
]);

$nav->setRecordCount($discountsRes->getCount());

while ($discount = $discountsRes->fetch()) {
    $iblock_key = array_search($discount['ID'], $discountsIds);
    $arResult['DISCOUNTS'][$iblock_key] = $discount;
    $arResult['DISCOUNTS'][$iblock_key]['DISCOUNT_IBLOCK'] = $arResult['DISCOUNTS_IBLOCKS'][$iblock_key];
}

$this->IncludeComponentTemplate();

$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    "",
    array(
        "NAV_OBJECT" => $nav,
        "SEF_MODE" => "N"
    )
);