<?php

use Bitrix\Main\Loader;
use Enterego\EnteregoHelper;
use Enterego\EnteregoSettings;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
Loader::includeModule('main');

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$action = $request->get('action');
if ($action === 'groupedProduct') {
    $prodId = $request->get('prodId');
    $listGroupedProduct = $request->get('prodIDS');
    $arItems['GROUPED_PRODUCTS'] = $arItems['GROUPED_PROPS_DATA'] = $arResult = [];
    if (!empty($prodId)) {
        $arResult = EnteregoHelper::getListGroupedProduct($prodId, $listGroupedProduct, $arItems);
        $arResult['SETTING'] = EnteregoSettings::getDataPropOffers();
    }
    echo json_encode($arResult);
    exit();
} else {
    echo '';
    exit();
}