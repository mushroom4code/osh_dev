<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

global $USER;
$filter['USER_ID'] = $USER->GetID();

$queryObject = Bitrix\Catalog\SubscribeTable::getList(array('select' => array('ID', 'ITEM_ID', 'USER_CONTACT'), 'filter' => $filter));

while ($subscribe = $queryObject->fetch()) {
    $arResult['CURRENT_USER_SUBSCRIPTIONS']['ITEMS_IDS'][] = $subscribe['ITEM_ID'];
    $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][] = $subscribe;
}

$component = $this->getComponent();

if ($APPLICATION->GetCurPage() === '/hit/') {
    $sectionsArr = [];
    $brandsItemsArr = [];
    $sectionsItemsArr = [];

    foreach ($arResult['ITEMS'] as $item) {
        $brandsItemsArr[$item['IBLOCK_SECTION_ID']][$item['ID']] = $item;
    }

    $brandsRes = CIBlockSection::GetList([], ['ID' => array_keys($brandsItemsArr), 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y']);
    while ($brand = $brandsRes->fetch()) {
        $initialBrandId = $brand['ID'];
        while ($brand['DEPTH_LEVEL'] > 2) {
            $brandRes = CIBlockSection::GetByID($brand['IBLOCK_SECTION_ID']);
            $brand = $brandRes->fetch();
        }
        if ($brand['IBLOCK_SECTION_ID']) {
            $sectionsItemsArr[$brand['IBLOCK_SECTION_ID'] ?? $brand['ID']]
                = $sectionsItemsArr[$brand['IBLOCK_SECTION_ID']]
                ? ($sectionsItemsArr[$brand['IBLOCK_SECTION_ID']] + $brandsItemsArr[$initialBrandId])
                : $brandsItemsArr[$initialBrandId];
        } else {
            $sectionsItemsArr[$brand['ID']]
                = $sectionsItemsArr[$brand['ID']]
                ? ($sectionsItemsArr[$brand['ID']] + $brandsItemsArr[$initialBrandId])
                : $brandsItemsArr[$initialBrandId];
        }
    }

    foreach ($sectionsItemsArr as &$sectionItems) {
        foreach ($sectionItems as &$sectionItem) {
            $sectionItem['SERVICE_FIELD_POPULARITY'] = intval($sectionItem['PROPERTIES']['SERVICE_FIELD_POPULARITY']['VALUE']);
        }
    }

    foreach ($sectionsItemsArr as $sectionItemsKey => &$sectionItems) {
        $popularity = array();
        foreach ($sectionItems as $key => $row) {
            $popularity[$key] = $row['SERVICE_FIELD_POPULARITY'];
        }
        array_multisort($popularity, SORT_DESC, $sectionItems);
    }

    $tempSectionsItemsArr = $sectionsItemsArr;
    foreach ($tempSectionsItemsArr as $sectionKey => $sectItems) {
        foreach ($sectItems as $sectionItemKey => $sectItem) {
            unset($sectionsItemsArr[$sectionKey][$sectionItemKey]);
            $sectionsItemsArr[$sectionKey][$sectItem['ID']] = $sectItem;
        }
    }

    $sectionsRes = CIBlockSection::GetList([], ['ID' => array_keys($sectionsItemsArr), 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y']);
    while ($section = $sectionsRes->fetch()) {
        $sectionsArr[$section['ID']] = $section;
    }

    $arResult['SECTIONS_ITEMS'] = $sectionsItemsArr;
    $arResult['SECTIONS'] = $sectionsArr;
}

$arParams = $component->applyTemplateModifications();

// Блокировка показа Вы смотрели если параметр установлен, если его не существует, присваиваем.
if (!isset($arParams['ACTIVE_BLOCK_YOU_SEE'])) {
    $arParams['ACTIVE_BLOCK_YOU_SEE'] = 'Y';
}