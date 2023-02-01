<?php
function updateMinSortPrice()
{
    CModule::IncludeModule('catalog') || die();
    $products = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => IBLOCK_CATALOG],
        false,
        false,
        ['ID', CATALOG_BASE_PRICE, CATALOG_STOCK_PRICE, SORT_PRICE_PROPERTY, IS_DISCOUNT_PROPERTY]
    );

    while ($product = $products->Fetch()) {
        $id = (int)$product['ID'];
        $basePrice = (int)$product[CATALOG_BASE_PRICE] ?? 0;
        $stockPrice = (int)$product[CATALOG_STOCK_PRICE] ?? 0;
        $currSortPrice = (int)$product[SORT_PRICE_PROPERTY_VALUE] ?? 0;
        $sortPrice = $basePrice;

        if ($product[IS_DISCOUNT_VALUE] == "Да") {
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
                    SORT_PRICE_CODE => $sortPrice
                ]
            );
        }
    }

    return 'updateMinSortPrice()';
}
