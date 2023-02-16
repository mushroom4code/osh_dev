<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */


global $USER;
$filter = ['USER_ID' => $USER->GetID(), 'ITEM_ID' => $arResult['ID']];
$queryObject = Bitrix\Catalog\SubscribeTable::getList(array('select' => array('ID', 'ITEM_ID', 'USER_CONTACT'), 'filter' => $filter));
$listCurrentUserSubsriptions = array();
while ($subscribe = $queryObject->fetch())
{
    $arResult['ITEM_SUBSCRIPTION'] = $subscribe;
}

//$subscription_item_ids = array_column($listCurrentUserSubsriptions["SUBSCRIPTIONS"], 'ITEM_ID');
//$found_key = array_search((string)$arResult['ID'], $subscription_item_ids);
$is_key_found = (isset($arResult['ITEM_SUBSCRIPTION']) && ($arResult['ITEM_SUBSCRIPTION'] !== false)) ? true : false;

//$arResult["CURRENT_USER_SUBSCRIPTION"] = $listCurrentUserSubsriptions;
//$arResult["SUBSCRIPTION_KEY"] = $found_key;
$arResult["IS_SUBSCRIPTION_KEY_FOUND"] =$is_key_found;

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();