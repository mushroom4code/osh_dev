<? use Enterego\EnteregoBasket;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

$is_key_found = (isset($arResult['ITEM_SUBSCRIPTION']) && ($arResult['ITEM_SUBSCRIPTION'] !== false)) ? true : false;

$arResult["IS_SUBSCRIPTION_KEY_FOUND"] =$is_key_found;

$useDiscount = $arResult['PROPERTIES']['USE_DISCOUNT'];
$arResult['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($arResult['ITEM_ALL_PRICES'][0], $useDiscount);
if(!empty($arResult['OFFERS'])){
    foreach($arResult['OFFERS']  as &$offer){
        $useDiscount = $offer['PROPERTIES']['USE_DISCOUNT'];
        $offer['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($offer['ITEM_ALL_PRICES'][0], $useDiscount)['PRICE_DATA'];
    }
}

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();