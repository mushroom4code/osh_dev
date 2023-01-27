<?php
function updateProductPopularity()
{
    $updates = [];
    $products = [];
    $arFilter = ['IBLOCK_ID' => IBLOCK_CATALOG];
    $arSelect = ['IBLOCK_ID', 'ID', 'PROPERTY_SERVICE_FIELD_POPULARITY'];
    $res = CIBlockElement::GetList(false, $arFilter, $arSelect);

    while ($el = $res->GetNextElement()) {
        $fields = $el->GetFields();
        $products[$fields['ID']] = $fields['PROPERTY_SERVICE_FIELD_POPULARITY_VALUE'];
    }

    $actualLikes = DataBase_like::getLikeFavoriteAllProduct(array_keys($products), false);
    print_r($actualLikes);
    echo '<br><br>';

    foreach ($products as $id => $popularity) {
        echo PHP_EOL;
        if ($popularity != $actualLikes['ALL_LIKE'][$id]) {
            $updates[$id] = $actualLikes['ALL_LIKE'][$id];
        }
    }
    print_r($updates);
    echo '<br><br>';

    foreach ($updates as $id => $updElem) {
        CIBlockElement::SetPropertyValuesEx(
            $id,
            IBLOCK_CATALOG,
            [
                'SERVICE_FIELD_POPULARITY' => $updElem['new'],
            ]
        );
    }

    return 'updateProductPopularity()';
}