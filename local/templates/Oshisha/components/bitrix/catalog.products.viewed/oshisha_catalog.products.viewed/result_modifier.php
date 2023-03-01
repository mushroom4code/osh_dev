<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */
Loader::includeModule('sale');
Loader::includeModule('catalog');

$arResult['BASKET_ITEMS'] = array();

$dbBasketItems = CSaleBasket::GetList(
    array("NAME" => "ASC", "ID" => "ASC"),
    array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL"),
    false,
    false,
    array("ID", "PRODUCT_ID", "QUANTITY",)
);
while ($arItems = $dbBasketItems->Fetch()) {
    if (strlen($arItems["CALLBACK_FUNC"]) > 0) {
        CSaleBasket::UpdatePrice($arItems["ID"],
            $arItems["CALLBACK_FUNC"],
            $arItems["MODULE"],
            $arItems["PRODUCT_ID"],
            $arItems["QUANTITY"]);
        $arItems = CSaleBasket::GetByID($arItems["ID"]);
    }

    $arResult['BASKET_ITEMS'][$arItems["PRODUCT_ID"]] = $arItems["QUANTITY"];
}

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();