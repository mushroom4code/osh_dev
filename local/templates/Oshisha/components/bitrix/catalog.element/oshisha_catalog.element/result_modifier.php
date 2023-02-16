<? use Enterego\EnteregoBasket;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */
$useDiscount = $arResult['PROPERTIES']['USE_DISCOUNT'];
$arResult['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($arResult['ITEM_ALL_PRICES'][0], $useDiscount);
$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();