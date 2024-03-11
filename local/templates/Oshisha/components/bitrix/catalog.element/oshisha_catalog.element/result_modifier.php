<?php

use Bitrix\Main\Localization\Loc;
use Enterego\contragents\EnteregoContragents;
use Enterego\EnteregoBasket;
use Enterego\EnteregoGroupedProducts;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 * @var array $arResult
 */

global $USER;
$filter = ['USER_ID' => $USER->GetID(), 'ITEM_ID' => $arResult['ID']];
$queryObject = Bitrix\Catalog\SubscribeTable::getList(array(
    'select' => array('ID', 'ITEM_ID', 'USER_CONTACT'),
    'filter' => $filter
));
$listCurrentUserSubsriptions = array();
while ($subscribe = $queryObject->fetch()) {
    $arResult['ITEM_SUBSCRIPTION'] = $subscribe;
}

$is_key_found = (isset($arResult['ITEM_SUBSCRIPTION']) && ($arResult['ITEM_SUBSCRIPTION'] !== false)) ? true : false;
$arResult["IS_SUBSCRIPTION_KEY_FOUND"] = $is_key_found;

/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP start */
$arResult['GROUPED_PRODUCTS'] = $arResult['GROUPED_PROPS_DATA'] = [];
$listGroupedProduct = $arResult['PROPERTIES']['PRODUCTS_LIST_ON_PROP']['VALUE'];
$arResult = EnteregoGroupedProducts::getListGroupedProduct($arResult['ID'], $listGroupedProduct, $arResult);
/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP end */

$arResult['ADD_TO_BASKET'] = EnteregoContragents::getActiveContragentForUser($USER->GetID());
$arResult['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($arResult['ITEM_ALL_PRICES'][0],
    false, $arResult['ID']);

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');