<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Enterego\EnteregoHelper;
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

foreach ($arResult['BASKET'] as &$basketItem) {
    EnteregoHelper::setProductsActiveUnit($basketItem, true);
    $basketItem['MEASURE_RATIO'] = \Bitrix\Catalog\MeasureRatioTable::getList(array(
        'select' => array('RATIO'),
        'filter' => array('=PRODUCT_ID' => $basketItem['PRODUCT_ID'])
    ))->fetch()['RATIO'];

    $basketItem['QUANTITY_WITH_RATIO'] = $basketItem['QUANTITY'] / $basketItem['MEASURE_RATIO'];
}
unset($basketItem);

$component = $this->getComponent();