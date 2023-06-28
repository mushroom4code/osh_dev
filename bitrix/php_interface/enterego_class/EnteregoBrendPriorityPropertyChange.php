<?php

function brendPriorityPropertyChangeOnProductUpdate($productId, $arFields) {
    $product = CIBlockElement::GetList(
        [],
        ['ID'=>$productId],
        false,
        false,
        ['PROPERTY_BREND'])->fetch();
    $brendsHighload = Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['NAME' => 'BREND']])->fetch();
    $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($brendsHighload);
    $entity_data_class = $entity->getDataClass();
    $brend = $entity_data_class::getList(['filter' => ['UF_XML_ID' => $product['PROPERTY_BREND_VALUE']]])->fetch();
    CIBlockElement::SetPropertyValueCode($productId, 'SERVICE_SORT_BREND', $brend['UF_SORT']);
}

function brendPriorityPropertyChangeOnBrendsUpdate($brendId, $arFields) {
    $productsRes = CIBlockElement::GetList(
        [],
        ['PROPERTY_BREND' => $arFields['UF_XML_ID']],
        false,
        false,
        []);
    while($productRes = $productsRes->fetch()) {
        CIBlockElement::SetPropertyValueCode($productRes['ID'], 'SERVICE_SORT_BREND', $arFields['UF_SORT']);
    }
}