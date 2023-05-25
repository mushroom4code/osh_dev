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
$arResult['GROUPED_PROPS_DATA'] = [];
if (!empty($listGroupedProduct)) {
    $rsMainPropertyValues = CIBlockElement::GetProperty(IBLOCK_CATALOG, $arResult['ID'],
        [], ['CODE' => 'OSNOVNOE_SVOYSTVO_TP']);

    $refPropsCode = [];
    while ($arMainPropertyValue = $rsMainPropertyValues->GetNext()) {
        $xmlId = $arMainPropertyValue['VALUE_XML_ID'];
        $rsRefProperty = CIBlockProperty::GetList([], ['XML_ID' => $xmlId]);
        if ($arRefProperty = $rsRefProperty->Fetch()) {
            $refPropsCode[] = $arRefProperty['CODE'];
        }
    }

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
                "DETAIL_PICTURE",
                "DETAIL_PAGE_URL",

            ])->Fetch();

        $elem = &$arResult['GROUPED_PRODUCTS'][$elemProp];
        foreach ($refPropsCode as $propCode) {
            $groupProperty = [];
            $propList = CIBlockElement::GetProperty(IBLOCK_CATALOG, $elemProp,
                [], ['EMPTY' => 'N', 'ACTIVE' => "Y", 'CODE' => $propCode]);

            while ($props = $propList->GetNext()) {

                if (empty($elem['PROPERTIES'][$props['CODE']])) {
                    $elem['PROPERTIES'][$props['CODE']] = $props;
                }

                if ($props['PROPERTY_TYPE'] === 'L') {

                    /** Первый массив для группировки и вывода списка знач свой-в по значения,
                     * второй для js обработки при клике, разница в кол-ве элеметов
                     * (во втором все значения, даже те что дублируются, но принадлеэат разным товарам)
                     */
                    $groupProperty[$props['VALUE_ENUM']] = $elem['PROPERTIES'][$props['CODE']]['JS_PROP'][$props['VALUE_ENUM']] = [
                        'VALUE_ENUM' => $props['VALUE_ENUM'] ?? $props['VALUE'],
                        'VALUE_XML_ID' => $props['VALUE_XML_ID'],
                        'PROPERTY_VALUE_ID' => $props['PROPERTY_VALUE_ID'],
                        'CODE' => '/catalog/product/' . $elem['CODE'] . '/',
                        'SELECT' => (int)$arResult['ID'] === (int)$elem['ID'] ? 'selected' : '',
                        'PRODUCT_IDS' => $props['PROPERTY_VALUE_ID'],
                        'PREVIEW_PICTURE' => $elem['PREVIEW_PICTURE'] ?? $elem['DETAIL_PICTURE'],
                        'TYPE' => $props['CODE']
                    ];
                } else {
                    $elem['PROPERTIES'][$props['CODE']]['VALUES'] = [
                        'VALUE_ENUM' => $props['VALUE'],
                        'VALUE_XML_ID' => $props['VALUE_XML_ID'],
                        'PROPERTY_VALUE_ID' => $props['PROPERTY_VALUE_ID'],
                        'CODE' => '/catalog/product/' . $elem['CODE'] . '/',
                        'SELECT' => (int)$arResult['ID'] === (int)$elem['ID'] ? 'selected' : '',
                        'PRODUCT_IDS' => $props['PROPERTY_VALUE_ID'],
                        'PREVIEW_PICTURE' => $elem['PREVIEW_PICTURE'] ?? $elem['DETAIL_PICTURE']
                    ];
                }
            }

            $needAdd = true;
            foreach ($arResult['GROUPED_PROPS_DATA'][$propCode] as $currentGroupProperty) {

                if (count($currentGroupProperty) !== count($groupProperty)) {
                    continue;
                }

                $isDifferences = false;
                foreach ($groupProperty as $key => $currentValue) {
                    if (isset($currentGroupProperty[$key])) {
                        continue;
                    }
                    $isDifferences = true;
                }
                if (!$isDifferences) {
                    $needAdd = false;
                    break;
                }
            }
            if ($needAdd) {
                $arResult['GROUPED_PROPS_DATA'][$propCode][] = $groupProperty;
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