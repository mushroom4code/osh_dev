<?php
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


$useDiscount = ($arResult['PROPERTIES']['USE_DISCOUNT']['VALUE'] ?? 'Нет') === 'Да' ;
$arResult['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($arResult['ITEM_ALL_PRICES'][0],
    $useDiscount, $arResult['ID']);

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();