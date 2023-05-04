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
                "PREVIEW_PICTURE",
                "CATALOG_QUANTITY",
                "DETAIL_PAGE_URL",

            ])->Fetch();

        $propList = CIBlockElement::GetProperty(IBLOCK_CATALOG, $elemProp, [], ['EMPTY' => 'N', 'ACTIVE' => "Y"]);
        while ($props = $propList->GetNext()) {
            $elem = &$arResult['GROUPED_PRODUCTS'][$elemProp];

            if ($props['PROPERTY_TYPE'] === 'L') {
                if (empty($elem['PROPERTIES'][$props['CODE']])) {
                    $elem['PROPERTIES'][$props['CODE']] = $props;
                }
                $elem['PROPERTIES'][$props['CODE']]['VALUES'][$props['PROPERTY_VALUE_ID']]['VALUE_ENUM'] = $props['VALUE_ENUM'] ?? $props['VALUE'];
                $elem['PROPERTIES'][$props['CODE']]['VALUES'][$props['PROPERTY_VALUE_ID']]['VALUE_XML_ID'] = $props['VALUE_XML_ID'];
            } else {
                $elem['PROPERTIES'][$props['CODE']] = $props;
            }
        }

        foreach ($arResult['GROUPED_PRODUCTS'][$elemProp]['PROPERTIES'] as $prop) {
            $propOsnId = $arResult['GROUPED_PRODUCTS'][$elemProp]['PROPERTIES']['OSNOVNOE_SVOYSTVO_TP']['VALUES'];
            foreach ($propOsnId as $prop_val) {
                if ($prop['XML_ID'] === $prop_val['VALUE_ENUM']) {

                    if ($prop['PROPERTY_TYPE'] === 'L') {
                        if (empty($elem['OSNOVNOE_SVOYSTVO_TP'][$prop['ID']])) {
                            $elem['OSNOVNOE_SVOYSTVO_TP'][$prop['ID']] = $prop;
                        }
                        $elem['OSNOVNOE_SVOYSTVO_TP'][$prop['ID']]['VALUES'][$prop['PROPERTY_VALUE_ID']]['VALUE_ENUM'] = $prop['VALUE_ENUM'];
                        $elem['OSNOVNOE_SVOYSTVO_TP'][$prop['ID']]['VALUES'][$prop['PROPERTY_VALUE_ID']]['VALUE_XML_ID'] = $prop['VALUE_XML_ID'];
                        $arResult['GROUPED_PROPS'][$elem['ID']]['PRODUCT'] = $elem;
                        $arResult['GROUPED_PROPS'][$elem['ID']]['PROPS'][$prop['CODE']] = $prop['VALUES'];
                        $arResult['GROUPED_PROD_PROP'][$elem['ID']][$prop['ID']][$prop['PROPERTY_VALUE_ID']] = $prop['VALUES'];
                    } else {
                        $elem['OSNOVNOE_SVOYSTVO_TP'][$prop['ID']] = $prop;
                        $arResult['GROUPED_PROD_PROP']['PROPS'][$prop['ID']][$prop['PROPERTY_VALUE_ID']] = $prop['VALUE'];
                        $arResult['GROUPED_PROPS'][$elem['ID']] = [
                            'PRODUCT' => $elem,
                            'PROP' => $prop['CODE'],
                            'VALUE' => $prop['VALUE'],
                        ];
                    }

                }
            }

        }
    }
}
/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP end */

$arResult['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate(
    $arResult['ITEM_ALL_PRICES'][0],
    $arResult['PROPERTIES']['USE_DISCOUNT']
);

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();