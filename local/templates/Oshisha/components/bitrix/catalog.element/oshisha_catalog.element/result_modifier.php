<? use Enterego\EnteregoBasket;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
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
$arResult['GROUPED_PRODUCTS'] = [];
$listGroupedProduct = $arResult['PROPERTIES']['PRODUCTS_LIST_ON_PROP']['VALUE'];
if (!empty($listGroupedProduct)) {
	foreach ($listGroupedProduct as $elemProp) {
		$arResult['GROUPED_PRODUCTS'][$elemProp] = CIBlockElement::GetList([],
			['ID' => $elemProp, 'IBLOCK_CATALOG_ID' => IBLOCK_CATALOG, 'ACTIVE' => 'Y'], false, false,
			[
				"ID",
				"ACTIVE",
				"NAME",
				"IBLOCK_SECTION_ID",
				"XML_ID",
				"PREVIEW_PICTURE",
				"DETAIL_PICTURE",
				"IBLOCK_ID",
				"SECTION_ID",
				"SECTION_CODE",
				"CATALOG_QUANTITY",
				"CATALOG_AVAILABLE",
				"PROPERTY_" . OSNOVNOE_SVOYSTVO_TP ?? 'OSNOVNOE_SVOYSTVO_TP',
				"PROPERTY_USE_DISCOUNT",
				"DETAIL_PAGE_URL",
				"CATALOG_PRICE_" . RETAIL_PRICE,
				"CATALOG_PRICE_" . BASIC_PRICE,
				"CATALOG_PRICE_" . B2B_PRICE,
				"CATALOG_PRICE_" . SALE_PRICE_TYPE_ID

			])->Fetch();

		$propList = CIBlockElement::GetProperty(IBLOCK_CATALOG, $elemProp, [], ['EMPTY' => 'N', 'ACTIVE' => "Y"]);
		while ($props = $propList->Fetch()) {
			$elem = $arResult['GROUPED_PRODUCTS'][$elemProp];
			$propOsnId = $elem['PROPERTY_OSNOVNOE_SVOYSTVO_TP_VALUE'];
			$elem['PROPERTIES'][$props['CODE']] = $props;
			if (!empty($propOsnId) && $props['XML_ID'] === $propOsnId) {
				$elem['OSNOVNOE_SVOYSTVO_TP'] = $props;
			}
		}

	}
}
/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP end */
$useDiscount = $arResult['PROPERTIES']['USE_DISCOUNT'];
$arResult['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($arResult['ITEM_ALL_PRICES'][0],
	$useDiscount);
if (!empty($arResult['OFFERS'])) {
	foreach ($arResult['OFFERS'] as &$offer) {
		$useDiscount = $arResult['PROPERTIES']['USE_DISCOUNT'];
		$offer['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($offer['ITEM_ALL_PRICES'][0],
			$useDiscount);
	}
}

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();