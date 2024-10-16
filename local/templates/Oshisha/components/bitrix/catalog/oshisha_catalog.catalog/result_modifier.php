<?php

/** @var $arResult array */

use Enterego\EnteregoDiscountHitsSelector;

//const CATALOG_GIFT_ID = 1873;
const CATALOG_GIFT_ID = 478;

if ($arResult['FOLDER'] === '/diskont/') {
    $cat = new EnteregoDiscountHitsSelector();
    $arResult['SECTION_LIST'] = $cat->getSectionProductsForFilter('diskont', $arParams);
} elseif ($arResult['FOLDER'] === '/hit/') {
    $cat = new EnteregoDiscountHitsSelector();
    $arFilterS = array('ACTIVE' => 'Y', 'IBLOCK_ID' => IBLOCK_CATALOG, 'GLOBAL_ACTIVE' => 'Y',);
    $GLOBALS[$arParams['FILTER_NAME']] = array_merge($GLOBALS[$arParams['FILTER_NAME']], $arFilterS);
    $arResult['SECTION_LIST'] = $cat->getSectionProductsForFilter('hit', $arParams);
} else {
    $arOrderS = array('DEPTH_LEVEL' => 'ASC', 'SORT' => 'ASC',);
    $arFilterS = array('ACTIVE' => 'Y', 'IBLOCK_ID' => IBLOCK_CATALOG, 'GLOBAL_ACTIVE' => 'Y');
    //TODO local redirect for category 18+
    if (CATEGORY_DISABLED) {
        $arFilterS['PROPERTY'] = array('SEE_PRODUCT_AUTH_VALUE' => 26185);
    }

    $arSelectS = array('*');

    $rsSections = CIBlockSection::GetList($arOrderS, $arFilterS, false, $arSelectS);
    $sectionLinc = array();
    $arResult['ROOT'] = array();
    $sectionLinc[0] = &$arResult['ROOT'];
    $arSections = [];
    while ($arSection = $rsSections->GetNext()) {
        if ($arSection['DEPTH_LEVEL'] <= 2) {
            if (CIBlockSection::GetSectionElementsCount($arSection['ID'], ['CNT_ACTIVE' => 'Y']) > 0) {
                $arSection['TEXT'] = $arSection['NAME'];
                $arSection['LINK'] = $arSection['CODE'];
                $arSections[$arSection['ID']] = $arSection;
            }
        }
    }

    foreach ($arSections as $arSection) {
        $sectionLinc[intval($arSection['IBLOCK_SECTION_ID'])]['CHILDS'][$arSection['ID']] = $arSection;
        $sectionLinc[$arSection['ID']] = &$sectionLinc[intval($arSection['IBLOCK_SECTION_ID'])]['CHILDS'][$arSection['ID']];
    }

    $arResult['SECTION_LIST'] = $sectionLinc[0]['CHILDS'];
}