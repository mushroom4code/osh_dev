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
    ->setPageSize(3)
    ->initFromUri();

$res = Bitrix\Iblock\IblockTable::getList([
    'filter' => ['IBLOCK_TYPE_ID' => 'discounts'],
    'count_total' => true,
    'order' => ['ID' => 'desc'],
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
]);

$nav->setRecordCount($res->getCount());

$discountsIds = [];
while ($iblock = $res->fetch()) {
    $arResult['DISCOUNTS_IBLOCKS'][$iblock['ID']] = $iblock;
    $discountsIds[$iblock["ID"]] = substr($iblock['CODE'], (strpos($iblock['CODE'], '_d') + 2));
}

$discountsRes = \Bitrix\Sale\Internals\DiscountTable::getList([
    'filter' => [
        'ID' => $discountsIds,
    ],
    'select' => [
        "*"
    ]
]);

while($discount = $discountsRes->fetch()) {

    $arResult['DISCOUNTS'][array_search($discount['ID'], $discountsIds)] = $discount;
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