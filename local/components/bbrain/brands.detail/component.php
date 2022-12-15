<?php

/** @var $arParams */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader,
    Bitrix\Iblock;

Loader::includeModule("iblock");
Loader::includeModule("highloadblock");
Loader::includeModule("catalog");

if (!$arParams['HLBLOCK_NAME']) {
    return;
}

if ($arParams['CODE'] != '') {
    $result = HighloadBlockTable::getList(array('filter'=>array('=NAME'=>$arParams['HLBLOCK_NAME'])));
    if($row = $result->fetch())
    {
        $obEntity = HighloadBlockTable::compileEntity($row);
        $strEntityDataClass = $obEntity->getDataClass();
    } else {
        return;
    }

    $QUERY = [
        'select' => array('*'),
        'order' => array("UF_NAME" => "ASC"),
        'filter' => array('UF_CODE' => $arParams['CODE'])
    ];
    $obData = $strEntityDataClass::getList($QUERY);
    while ($arData = $obData->Fetch()) {

        $arResult = $arData;
    }
}

if (!$arResult['ID']) {
    Iblock\Component\Tools::process404(
        trim($arParams["MESSAGE_404"]) ?: 'Страница не найдена'
        , true
        , "Y"
        , "Y"
        , $arParams["FILE_404"]
    );
}
$this->IncludeComponentTemplate();

