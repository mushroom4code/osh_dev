<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

function updateMinSortPrice()
{
    CModule::IncludeModule('catalog') || die();
    $products = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => IBLOCK_CATALOG],
        false,
        false,
        ['ID', 'CATALOG_PRICE_2', 'CATALOG_PRICE_3', 'PROPERTY_SERVICE_SORT_PRICE', 'PROPERTY_USE_DISCOUNT']
    );

    while ($product = $products->Fetch()) {
        $id = (int)$product['ID'];
        $basePrice = (int)$product['CATALOG_PRICE_2'] ?? 0;
        $stockPrice = (int)$product['CATALOG_PRICE_3'] ?? 0;
        $currSortPrice = (int)$product['PROPERTY_SERVICE_SORT_PRICE_VALUE'] ?? 0;
        $sortPrice = $basePrice;

        if ($product['PROPERTY_USE_DISCOUNT_VALUE'] == "Да") {
            $sortPrice = $stockPrice ?? $sortPrice;
        }

        if ($stockPrice == 0) {
            $sortPrice = $basePrice;
        }

        if ($sortPrice != $currSortPrice) {
            CIBlockElement::SetPropertyValuesEx(
                $id,
                IBLOCK_CATALOG,
                [
                    'SERVICE_SORT_PRICE' => $sortPrice
                ]
            );
        }
    }

    return 'updateMinSortPrice()';
}
