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
    $elem = [];
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

            if (empty($elem['PROPERTIES'][$props['CODE']])) {
                $elem['PROPERTIES'][$props['CODE']] = $props;
            }

            if ($props['PROPERTY_TYPE'] === 'L') {

                /** Первый массив для группировки и вывода списка знач свой-в по значения,
                 * второй для js обработки при клике, разница в кол-ве элеметов
                 * (во втором все значения, даже те что дублируются, но принадлеэат разным товарам)
                 */
                $elem['PROPERTIES'][$props['CODE']]['VALUES'][$props['VALUE_XML_ID']] =
                $elem['PROPERTIES'][$props['CODE']]['JS_PROP'][$props['PROPERTY_VALUE_ID']] = [
                    'VALUE_ENUM' => $props['VALUE_ENUM'] ?? $props['VALUE'],
                    'VALUE_XML_ID' => $props['VALUE_XML_ID'],
                    'PROPERTY_VALUE_ID' => $props['PROPERTY_VALUE_ID'],
                    'CODE' => '/catalog/product/' . $elem['CODE'] . '/',
                    'SELECT' => (int)$arResult['ID'] === (int)$elem['ID'] ? 'selected' : '',
                    'PRODUCT_IDS' => $props['PROPERTY_VALUE_ID']
                ];
                $elem['PROPERTIES'][$props['CODE']]['PRODUCT_IDS'][$props['PROPERTY_VALUE_ID']] = [
                    'PROP_ID' => $props['PROPERTY_VALUE_ID'],
                    'PROD_ID' => $elem['ID']
                ];
                $elem['PROPERTIES'][$props['CODE']]['JS_PROP'][$props['PROPERTY_VALUE_ID']]['PRODUCT_ID'] = $elem['ID'];

            } else {
                $elem['PROPERTIES'][$props['CODE']]['VALUES'] = [
                    'VALUE_ENUM' => $props['VALUE'],
                    'VALUE_XML_ID' => $props['VALUE_XML_ID'],
                    'PROPERTY_VALUE_ID' => $props['PROPERTY_VALUE_ID'],
                    'CODE' => '/catalog/product/' . $elem['CODE'] . '/',
                    'SELECT' => (int)$arResult['ID'] === (int)$elem['ID'] ? 'selected' : '',
                    'PRODUCT_IDS' => $props['PROPERTY_VALUE_ID']
                ];;
            }
        }

        foreach ($arResult['GROUPED_PRODUCTS'][$elemProp]['PROPERTIES'] as $propGreat) {
            $propOsnovnoeId = $arResult['GROUPED_PRODUCTS'][$elemProp]['PROPERTIES']['OSNOVNOE_SVOYSTVO_TP']['VALUES'];

            foreach ($propOsnovnoeId as $prop_val) {
                if ($propGreat['XML_ID'] === $prop_val['VALUE_XML_ID']) {
                    $propParent = &$arResult['GROUPED_PROD_PROP'][$propGreat['ID']];
                    if ($propGreat['PROPERTY_TYPE'] === 'L') {
                        if (empty($elem['OSNOVNOE_SVOYSTVO_TP'][$propGreat['ID']])) {
                            $elem['OSNOVNOE_SVOYSTVO_TP'][$propGreat['ID']] = $propGreat;
                        }
                        $elem['OSNOVNOE_SVOYSTVO_TP'][$propGreat['ID']]['VALUES'][$propGreat['VALUE_XML_ID']]['VALUE_ENUM'] = $propGreat['VALUE_ENUM'];

                        if (count($propGreat['VALUES']) > 1) {
                            $str = implode('_', array_keys($propGreat['VALUES']));
                            $propParent['PROPS_DATA'][$str] = $propGreat['VALUES'];
                        } else {
                            if (empty($propParent['PROPS_DATA'])) {
                                $propParent['PROPS_DATA'] = $propGreat['VALUES'];
                            } else {
                                foreach ($propGreat['VALUES'] as $keyPropValue => $valueProp) {
                                    $propParent['PROPS_DATA'][$keyPropValue] = $propGreat['VALUES'][$keyPropValue];
                                    $propParent['PRODUCT_IDS'][$propGreat['PROPERTY_VALUE_ID']] = $propGreat['PRODUCT_IDS'][$propGreat['PROPERTY_VALUE_ID']];
                                }
                            }
                        }
                        $propParent['PROPS'] = $propGreat['CODE'];
                        $propParent['PRODUCT_IDS'][$propGreat['PROPERTY_VALUE_ID']] = $propGreat['PRODUCT_IDS'][$propGreat['PROPERTY_VALUE_ID']];
                    } else {
                        $elem['OSNOVNOE_SVOYSTVO_TP'][$propGreat['ID']] = $propGreat;
                        $propParent['PROPS_DATA'][] = $propGreat['VALUES'];
                        $propParent['PROPS'] = $propGreat['CODE'];
                        $propParent['PRODUCT_IDS'][$propGreat['PROPERTY_VALUE_ID']] = $propGreat['PRODUCT_IDS'][$propGreat['PROPERTY_VALUE_ID']];
                    }
                    $arResult['JS_PROP'][$propGreat['PROPERTY_VALUE_ID']]['PROP'] = $propGreat['CODE'];
                    $arResult['JS_PROP'][$propGreat['PROPERTY_VALUE_ID']]['VALUES'] = $propGreat['VALUES'];
                }
            }
        }
//        Показ скрытие ссылки на товар, если свой-во в ед числе - то должно вести сразу на товар.
        if (isset($elem['ID']) && (int)$arResult['ID'] === (int)$elem['ID']) {
            $arResult['LINK_GROUPED_PRODUCT'] = 'N';
            if (count($elem['OSNOVNOE_SVOYSTVO_TP']) === 1) {
                $arResult['LINK_GROUPED_PRODUCT'] = 'Y';
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