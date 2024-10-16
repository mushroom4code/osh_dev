<?php

/** @var $arParams */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

Loader::includeModule("iblock");
Loader::includeModule("sale");
Loader::includeModule("highloadblock");
Loader::includeModule("catalog");

if(!isset($arParams['HLBLOCK_NAME'])) {
    return;
}

$result = HighloadBlockTable::getList(array('filter'=>array('=NAME'=>$arParams['HLBLOCK_NAME'])));
if($row = $result->fetch())
{
    $obEntity = HighloadBlockTable::compileEntity($row);
    $strEntityDataClass = $obEntity->getDataClass();
} else {
    return;
}

$QUERY = [
    'filter' => $arParams['AR_FILTER'],
    'select' => array('*'),
    'order' => array("UF_NAME" => "ASC"),
];

$obData = $strEntityDataClass::getList($QUERY);
while ($arData = $obData->Fetch()) {
    $arSections = explode("#", $arData['UF_SECTIONS']);
    foreach ($arSections as $_sect) {
        $sectionName = empty($_sect) ? 'Остальные' : $_sect;
        $arResult[$sectionName][$arData['ID']] = $arData;
    }
}

$this->IncludeComponentTemplate();

