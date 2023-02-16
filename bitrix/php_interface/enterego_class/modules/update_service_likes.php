<?php
function updateProductPopularity()
{
    $products = [];
    $arFilter = ['IBLOCK_ID' => IBLOCK_CATALOG];
    $arSelect = ['IBLOCK_ID', 'ID', 'PROPERTY_SERVICE_FIELD_POPULARITY'];
    $res = CIBlockElement::GetList(false, $arFilter, $arSelect);

    while ($el = $res->GetNextElement()) {
        $fields = $el->GetFields();
        $products[$fields['ID']] = $fields['PROPERTY_SERVICE_FIELD_POPULARITY_VALUE'];
    }

    $actualLikes = DataBase_like::getLikeFavoriteAllProduct(array_keys($products), false);

    foreach ($products as $id => $popularity) {
        if ($popularity != $actualLikes['ALL_LIKE'][$id] ?? 0) {
            CIBlockElement::SetPropertyValuesEx(
                $id,
                IBLOCK_CATALOG,
                [
                    'SERVICE_FIELD_POPULARITY' => $actualLikes['ALL_LIKE'][$id] ?? 0,
                ]
            );
        }
    }

    return 'updateProductPopularity();';
}