<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

global $USER;
$filter['USER_ID'] = $USER->GetID();

$queryObject = Bitrix\Catalog\SubscribeTable::getList(array('select' => array('ID', 'ITEM_ID', 'USER_CONTACT'), 'filter' => $filter));

while ($subscribe = $queryObject->fetch())
{
    $arResult['CURRENT_USER_SUBSCRIPTIONS']['ITEMS_IDS'][] = $subscribe['ITEM_ID'];
    $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][] = $subscribe;
}


$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();