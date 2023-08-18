<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('sale')) {
    exit();
}
$accountNumber = [];
$picture = [];

$arr = json_decode($_POST['arrOfDate'], true);

$minDate = current($arr);
$maxDate = next($arr);

$url = substr($arr['url'], 1, -2);

function sortByDate(string $field, string $value, string $minDate, string $maxDate): string
{
    $products = CSaleOrder::GetList(array("ID" => "DESC"),
        array('>=DATE_INSERT' => $minDate, '<=DATE_INSERT' => $maxDate, $field => $value),
        false, false, array('ACCOUNT_NUMBER', 'DATE_INSERT_FORMAT', 'PRICE', 'STATUS_ID'));
    while ($res = $products->Fetch()) {
        $accountNumber[] = $res;
    }

    for ($i = 0; $i < count($accountNumber); $i++) {
        $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $accountNumber[$i]['ACCOUNT_NUMBER']));
        if (!empty($ordersBasket)) {
            while ($result = $ordersBasket->Fetch()) {
                $my_elements = CIBlockElement::GetList(
                    array("ID" => "ASC"),
                    array("ID" => $result['PRODUCT_ID']),
                    false,
                    false,
                    array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                );
                $ar_fields = $my_elements->GetNext();
                $picture['url'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
                $accountNumber[$i]['PICTURE'] = array_slice($picture['url'], 0, 6, true);
            }
        }
    }

    return json_encode($accountNumber);
}

if ($url !== 'show_canceled' && $url !== 'show_delivery') {
    $products = CSaleOrder::GetList(array("ID" => "DESC"),
        array('>=DATE_INSERT' => $minDate, '<=DATE_INSERT' => $maxDate),
        false, false, array('ACCOUNT_NUMBER', 'DATE_INSERT_FORMAT', 'PRICE'));
    while ($res = $products->Fetch()) {
        $accountNumber[] = $res;
    }

    for ($i = 0; $i < count($accountNumber); $i++) {
        $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $accountNumber[$i]['ACCOUNT_NUMBER']));
        if (!empty($ordersBasket)) {
            while ($result = $ordersBasket->Fetch()) {
                $my_elements = CIBlockElement::GetList(
                    array("ID" => "ASC"),
                    array("ID" => $result['PRODUCT_ID']),
                    false,
                    false,
                    array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                );
                $ar_fields = $my_elements->GetNext();
                $picture['url'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
                $accountNumber[$i]['PICTURE'] = array_slice($picture['url'], 0, 6, true);
            }
        }
    }

    print_r(json_encode($accountNumber));
} else if ($url === 'show_canceled') {
    print_r(sortByDate('STATUS_ID', 'F', $minDate, $maxDate));
} else if ($url === 'show_delivery') {
    print_r(sortByDate('RESERVED', 'Y', $minDate, $maxDate));
}

