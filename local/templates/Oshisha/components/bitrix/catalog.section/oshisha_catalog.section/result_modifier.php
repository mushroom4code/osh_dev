<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 * @var array $arParams
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

$curDateTime = new \Bitrix\Main\Type\DateTime();
$iblocksDiscountsRes = Bitrix\Iblock\IblockTable::getList([
    'filter' => ['IBLOCK_TYPE_ID' => 'discounts', 'ACTIVE' => 'Y'],
    'count_total' => true,
    'order' => ['ID' => 'asc'],
]);

$discountsIds = [];
$iblocksDiscountsAr = [];
while ($iblock = $iblocksDiscountsRes->fetch()) {
    $iblocksDiscountsAr[$iblock['ID']] = $iblock;
    $discountsIds[$iblock["ID"]] = substr($iblock['CODE'], (strpos($iblock['CODE'], '_d') + 2));
}

$discountsRes = \Bitrix\Sale\Internals\DiscountTable::getList([
    'filter' => [
        'ID' => $discountsIds, 'ACTIVE' => 'Y'
    ],
    'order' => [
        'ACTIVE_TO' => 'desc'
    ],
    'select' => [
        "*"
    ],
    'count_total' => true
]);

while ($discount = $discountsRes->fetch()) {
    $iblockId = array_search($discount['ID'], $discountsIds);
    $discount['IBLOCK_DISCOUNT'] = $iblocksDiscountsAr[$iblockId];
    if (!empty($disount['ACTIVE_TO'])) {
        if (!empty($disount['ACTIVE_FROM'])
            && $disount['ACTIVE_FROM'] <= $curDateTime) {
            if ($disount['ACTIVE_TO'] >= $curDateTime) {
                $arResult['DISCOUNTS'][$iblockId] = $discount;
            }
        } else {
            $arResult['DISCOUNTS'][$iblockId] = $discount;
        }
    } else {
        $arResult['DISCOUNTS'][$iblockId] = $discount;
    }
}
foreach ($arResult['DISCOUNTS'] as &$discountItem) {
    $EnteregoDiscountObject = new \Enterego\EnteregoDiscountHelper();
    $discountItem['ACTIONS_LIST_MODIFIED'] = $discountItem['ACTIONS_LIST'];
    $EnteregoDiscountObject->recursiveActionListModifying($discountItem['ACTIONS_LIST']['CHILDREN'], $discountItem['ACTIONS_LIST_MODIFIED']);
    $parsedActionsList = $EnteregoDiscountObject->parseCondition(
        $discountItem['ACTIONS_LIST_MODIFIED'],
        [
            'INCLUDE_SUBSECTIONS' => 'Y',
            'HIDE_NOT_AVAILABLE_OFFERS' => 'N'
        ]
    );
    $discountItem['PRODUCTS'] = [];
    $discountProductsRes = CIBlockElement::GetList([],[$parsedActionsList], false, false, ['ID']);
    while ($discountProduct = $discountProductsRes->fetch()) {
        $discountItem['PRODUCTS'][] = $discountProduct['ID'];
    }
}