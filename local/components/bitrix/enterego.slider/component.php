<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['F_USER_ID'] = Fuser::getId($USER->GetID());

$arResult['PROP_SEE_IN_WINDOW'] = [];
$resQuery = Enterego\EnteregoSettings::getPropSetting(IBLOCK_CATALOG, 'SEE_POPUP_WINDOW');
if (!empty($resQuery)) {
    while ($collectionPropChecked = $resQuery->Fetch()) {
        $arResult['PROP_SEE_IN_WINDOW'][$collectionPropChecked['CODE']] = $collectionPropChecked;
    }
}

$item_id = [];
foreach ($arParams['SECTIONS_ITEMS'] as $section) {
    foreach ($section as $sectionItem) {
        $item_id[] = $sectionItem['ID'];
    }
}

$arResult['COUNT_LIKES'] = DataBase_like::getLikeFavoriteAllProduct($item_id, $arResult['F_USER_ID']);

$this->IncludeComponentTemplate();
