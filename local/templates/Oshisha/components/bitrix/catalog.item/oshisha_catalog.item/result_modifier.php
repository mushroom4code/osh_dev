<? use Enterego\EnteregoBasket;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$item = &$arResult['ITEM'];

if ($item["PREVIEW_PICTURE"]["ID"]) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $item["PREVIEW_PICTURE"]["ID"],
            array(
                'width' => 160,
                'height' => 160
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL
        ),
        CASE_UPPER
    );

} elseif ($item["DETAIL_PICTURE"]["ID"]) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $item["DETAIL_PICTURE"]["ID"],
            array(
                'width' => 160,
                'height' => 160
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL
        ),
        CASE_UPPER
    );
}


/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP start */
$item['GROUPED_PRODUCTS'] = [];
$listGroupedProduct = $item['PROPERTIES']['PRODUCTS_LIST_ON_PROP']['VALUE'];
$item['GROUPED_PROPS_DATA'] = [];
if (!empty($listGroupedProduct)) {
    $rsMainPropertyValues = CIBlockElement::GetProperty(IBLOCK_CATALOG, $item['ID'],
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
        $item['GROUPED_PRODUCTS'][$elemProp] = CIBlockElement::GetList([],
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

        $elem = &$item['GROUPED_PRODUCTS'][$elemProp];
        if (!empty($elem)) {
            foreach ($refPropsCode as $propCode) {
                $groupProperty = [];
                $propList = CIBlockElement::GetProperty(IBLOCK_CATALOG, $elemProp,
                    [], ['EMPTY' => 'N', 'ACTIVE' => "Y", 'CODE' => $propCode]);

                while ($props = $propList->GetNext()) {

                    if (empty($elem['PROPERTIES'][$props['CODE']])) {
                        $elem['PROPERTIES'][$props['CODE']] = $props;
                    }

                    /** Первый массив для группировки и вывода списка знач свой-в по значения,
                     * второй для js обработки при клике, разница в кол-ве элеметов
                     * (во втором все значения, даже те что дублируются, но принадлеэат разным товарам)
                     */
                    $groupProperty[$props['VALUE_ENUM']] = $elem['PROPERTIES'][$props['CODE']]['JS_PROP'][$props['VALUE_ENUM']] = [
                        'VALUE_ENUM' => $props['VALUE_ENUM'] ?? $props['VALUE'],
                        'VALUE_XML_ID' => $props['VALUE_XML_ID'],
                        'PROPERTY_VALUE_ID' => $props['PROPERTY_VALUE_ID'],
                        'CODE' => '/catalog/product/' . $elem['CODE'] . '/',
                        'PRODUCT_IDS' => $elem['ID'],
                        'PREVIEW_PICTURE' => $elem['PREVIEW_PICTURE'] ?? $elem['DETAIL_PICTURE'],
                        'TYPE' => $props['CODE'],
                        'NAME' => $elem['NAME']
                    ];
                }

                $needAdd = true;
                foreach ($item['GROUPED_PROPS_DATA'][$propCode] as $currentGroupProperty) {

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
                    $item['GROUPED_PROPS_DATA'][$propCode][] = $groupProperty;
                }
            }
        }
    }

}
/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP end */


$useDiscount = $item['PROPERTIES']['USE_DISCOUNT'];
$item['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($item['ITEM_ALL_PRICES'][0], $useDiscount);

foreach($item['OFFERS']  as &$offer){
    $useDiscount = $item['PROPERTIES']['USE_DISCOUNT'];
    $offer['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($offer['ITEM_ALL_PRICES'][0], $useDiscount);
}
