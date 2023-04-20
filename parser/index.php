<?php

use Bitrix\Catalog\Model\Price;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
try {
    if (!Loader::includeModule('catalog')) {
        die();
    }
} catch (LoaderException $e) {
    die();
}

try {
    if (!Loader::includeModule('search')) {
        die();
    }
} catch (LoaderException $e) {
    die();
}

$request = Application::getInstance()->getContext()->getRequest();
$token = $request->getHeader('token');
if (empty($token) || $token !== OSHISHA_PARSER_TOKEN) {
    exit();
}

$result = [];
$productName  = $request->get('name');
$productId  = $request->get('osh_id');
if (!empty($productId)){
    $result = _getPriceInfoForParser($productId);
} else {

    $arParams = [0 => [['=MODULE_ID' => 'iblock', 'PARAM1' => '1c_catalog'], 'LOGIC' => 'OR']];
    $obSearch = new CSearch();

    //When restart option is set we will ignore error on query with only stop words
    $obSearch->SetOptions(array(
        "ERROR_ON_EMPTY_STEM" => $arParams["RESTART"] != "Y",
        "NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"] == "Y",
    ));


    $exFILTER = [['=MODULE_ID' => 'iblock', 'PARAM1' => '1c_catalog', 'PARAM2' => [12]]];
    $arFilter = [
        'SITE_ID' => SITE_ID,
        'QUERY' => $productName,
        'TAGS' => false,
        'CHECK_DATE' => 'Y'
    ];

    $_arPhrase = stemming_split($productName, LANGUAGE_ID);
    $obSearch->Search($arFilter, [], $exFILTER);
    foreach ($obSearch->arResult as $item) {
        $result = _getPriceInfoForParser($item['item']);
        if (!empty($result)) {
            break;
        }
    }
}

print_r(empty($result) ? '' : json_encode($result));
exit();

/**
 * @throws ObjectNotFoundException
 * @throws ArgumentException
 * @throws ObjectPropertyException
 * @throws SystemException
 */
function _getPriceInfoForParser($productId): array
{
    $res = CIBlockElement::GetByID($productId);

    if ($arRes = $res->GetNext()) {
        $resPrice = Price::getList(['filter'=>[
            'PRODUCT_ID' => $arRes['ID'],
            'CATALOG_GROUP_ID' => BASIC_PRICE,
        ]]);

        if ($arResPrice = $resPrice->fetch()) {
            return[
                'name' => $arRes['NAME'],
                'id' => $arRes['ID'],
                'price' => $arResPrice['PRICE']
            ];
        }
    }
    return [];
}


