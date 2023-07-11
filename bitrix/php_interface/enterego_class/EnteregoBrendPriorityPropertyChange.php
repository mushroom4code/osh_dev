<?php

function brendPriorityPropertyChangeOnProductUpdate($productId, $arFields) {
        $product = CIBlockElement::GetList(
            [],
            ['ID' => $productId],
            false,
            false,
            ['PROPERTY_'.PROPERTY_BREND, 'PROPERTY_'.SORT_BREND])->fetch();
        $brendsHighload = Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['NAME' => PROPERTY_BREND]])->fetch();
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($brendsHighload);
        $entity_data_class = $entity->getDataClass();
        $brend = $entity_data_class::getList(['filter' => ['UF_XML_ID' => $product['PROPERTY_'.PROPERTY_BREND.'_VALUE']]])->fetch();
        if ($brend['UF_SORT'] != $product['PROPERTY_'.SORT_BREND.'_VALUE']) {
            CIBlockElement::SetPropertyValueCode($productId, SORT_BREND, $brend['UF_SORT']);
        }
}

function brendPriorityPropertyChangeOnBrendsUpdate($brendId, $arFields) {
    $productsRes = CIBlockElement::GetList(
        [],
        ['PROPERTY_'.PROPERTY_BREND => $arFields['UF_XML_ID']],
        false,
        false,
        []);
    while($productRes = $productsRes->fetch()) {
        CIBlockElement::SetPropertyValueCode($productRes['ID'], SORT_BREND, $arFields['UF_SORT']);
    }
}

function brendPriorityPropertyChangeForAll() {
    $products = [];
    $productsRes = CIBlockElement::GetList(
        [],
        [],
        false,
        false,
        ['ID', 'PROPERTY_'.PROPERTY_BREND, 'PROPERTY_'.SORT_BREND]);
    while ($product = $productsRes->fetch()) {
        $products[$product['ID']] = $product;
    }
    $brendsHighload = Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['NAME' => PROPERTY_BREND]])->fetch();
    $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($brendsHighload);
    $entity_data_class = $entity->getDataClass();
    $brends = [];
    $brendsRes = $entity_data_class::getList(['select' => ['UF_XML_ID', 'UF_SORT']]);
    while ($brend = $brendsRes->fetch()) {
        $brends[$brend['UF_XML_ID']] = $brend;
    }
    foreach ($products as $product) {
        if ($product['PROPERTY_'.SORT_BREND.'_VALUE'] != $brends[$product['PROPERTY_'.PROPERTY_BREND.'_VALUE']]['UF_SORT']) {
            CIBlockElement::SetPropertyValueCode($product['ID'], SORT_BREND, $brends[$product['PROPERTY_'.PROPERTY_BREND.'_VALUE']]['UF_SORT']);
        }
    }
    return "brendPriorityPropertyChangeForAll();";
}

AddEventHandler('catalog', 'OnProductUpdate', 'brendPriorityPropertyChangeOnProductUpdate');
AddEventHandler('', 'BRENDOnUpdate', 'brendPriorityPropertyChangeOnBrendsUpdate');