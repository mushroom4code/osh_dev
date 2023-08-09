<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 * @var CMain $APPLICATION
 * @var array $arParams
 */

use Enterego\EnteregoHitsHelper;

global $USER;
$filter['USER_ID'] = $USER->GetID();

$sectionsArr = [];
$sectionsItemsArr = [];
$sectionsItemsArr = EnteregoHitsHelper::getHitsSectionsItemsArr($arParams, false);
$sectionsItemsArr = EnteregoHitsHelper::getHitsSectionsItemsByPopularity($sectionsItemsArr);
$sectionsArr = EnteregoHitsHelper::getHitsSections($sectionsItemsArr);

$arResult['SECTIONS_ITEMS'] = $sectionsItemsArr;
$arResult['SECTIONS'] = $sectionsArr;

foreach ($arResult['SECTIONS_ITEMS'] as $sectionKey => $sectionItems) {
    if (!isset($GLOBALS['HITS_FILTER_' . $sectionKey])) {
        $GLOBALS['HITS_FILTER_' . $sectionKey]['ID'] = [];
    }
    foreach ($sectionItems as $sectionItem) {
        array_push($GLOBALS['HITS_FILTER_' . $sectionKey]['ID'], $sectionItem['ID']);
    }
}

// Блокировка показа Вы смотрели если параметр установлен, если его не существует, присваиваем.
if (!isset($arParams['ACTIVE_BLOCK_YOU_SEE'])) {
    $arParams['ACTIVE_BLOCK_YOU_SEE'] = 'Y';
}
$this->IncludeComponentTemplate();
