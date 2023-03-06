<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$dbBasketItems = CSaleBasket::GetList(
    array(
        "NAME" => "ASC",
        "ID" => "ASC"
    ),
    array(
        "FUSER_ID" => CSaleBasket::GetBasketUserID(),
        "LID" => SITE_ID,
        "ORDER_ID" => "NULL"
    ),
    false,
    false,
    array("ID", "CALLBACK_FUNC", "MODULE",
        "PRODUCT_ID", "QUANTITY", "DELAY",
        "CAN_BUY", "PRICE", "WEIGHT")
);

while ($arItems = $dbBasketItems->Fetch())
{
    $arResult['BASKET_ITEMS'][] = $arItems;
}


$component = $this->getComponent();