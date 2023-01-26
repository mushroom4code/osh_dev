<?php
function updateProductPopularity()
{
    $toChange = [];
    $arFilter = ['IBLOCK_ID' => IBLOCK_CATALOG];
    $arSelect = ['IBLOCK_ID', 'ID', 'PROPERTY_SERVICE_FIELD_POPULARITY'];
    $res = CIBlockElement::GetList(false, $arFilter, $arSelect);

    while ($el = $res->GetNextElement()) {
        $fields = $el->GetFields();
        $id = $fields['ID'];
        $likes = DataBase_like::getLikeFavoriteAllProduct([$id], false);

        if ((int)$fields['PROPERTY_SERVICE_FIELD_POPULARITY_VALUE'] != (int)$likes['ALL_LIKE'][$id]) {
            $oldCount = $fields['PROPERTY_SERVICE_FIELD_POPULARITY_VALUE'] != null ? $fields['PROPERTY_SERVICE_FIELD_POPULARITY_VALUE'] : 0;
            $toChange[$id] = [
                'new' => $likes['ALL_LIKE'][$id],
                'old' => $oldCount
            ];
        }
    }

    foreach ($toChange as $id => $elem) {
        CIBlockElement::SetPropertyValuesEx(
            $id,
            IBLOCK_CATALOG,
            [
                'SERVICE_FIELD_POPULARITY' => $elem['new'],
            ]
        );
    }

    return 'updateProductPopularity()';
}