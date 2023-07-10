<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 * @var CMain $APPLICATION
 */

use Enterego\EnteregoHitsHelper;

global $USER;
$filter['USER_ID'] = $USER->GetID();

$queryObject = Bitrix\Catalog\SubscribeTable::getList(array('select' => array('ID', 'ITEM_ID', 'USER_CONTACT'), 'filter' => $filter));

while ($subscribe = $queryObject->fetch()) {
    $arResult['CURRENT_USER_SUBSCRIPTIONS']['ITEMS_IDS'][] = $subscribe['ITEM_ID'];
    $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][] = $subscribe;
}

$component = $this->getComponent();

if (EnteregoHitsHelper::checkIfHits($APPLICATION)) {
    $sectionsArr = [];
    $sectionsItemsArr = [];
    $sectionsItemsArr = EnteregoHitsHelper::getHitsSectionsItemsArr($arResult['ITEMS'], $arParams, false);
    $sectionsItemsArr = EnteregoHitsHelper::getHitsSectionsItemsByPopularity($sectionsItemsArr);
    $sectionsArr = EnteregoHitsHelper::getHitsSections($sectionsItemsArr);

    $arResult['SECTIONS_ITEMS'] = $sectionsItemsArr;
    $arResult['SECTIONS'] = $sectionsArr;
}

$arParams = $component->applyTemplateModifications();

// Блокировка показа Вы смотрели если параметр установлен, если его не существует, присваиваем.
if (!isset($arParams['ACTIVE_BLOCK_YOU_SEE'])) {
    $arParams['ACTIVE_BLOCK_YOU_SEE'] = 'Y';
}