<?php
function updateMinSortPrice()
{
    CModule::IncludeModule('catalog') || die();
    $products = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => IBLOCK_CATALOG],
        false,
        false,
        [
            'ID',
            'CATALOG_PRICE_' . CATALOG_BASE_PRICE,
            'CATALOG_PRICE_' . CATALOG_STOCK_PRICE,
            'PROPERTY_' . SORT_PRICE,
            'PROPERTY_' . IS_DISCOUNT
        ]
    );

    while ($product = $products->Fetch()) {
        $id = (int)$product['ID'];
        $basePrice = (int)$product['CATALOG_PRICE_' . CATALOG_BASE_PRICE] ?? 0;
        $stockPrice = (int)$product['CATALOG_PRICE_' . CATALOG_STOCK_PRICE] ?? 0;
        $currSortPrice = (int)$product['PROPERTY_' . SORT_PRICE . '_VALUE'] ?? 0;
        $sortPrice = $basePrice;

        if ($product['PROPERTY_' . IS_DISCOUNT . '_VALUE'] == "Да") {
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
                    SORT_PRICE => $sortPrice
                ]
            );
        }
    }

    return 'updateMinSortPrice();';
}
