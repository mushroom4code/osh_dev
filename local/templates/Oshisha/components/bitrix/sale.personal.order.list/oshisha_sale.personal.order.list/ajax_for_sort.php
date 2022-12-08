<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('sale')) {
    exit();
}

function sortByField(string $field, string $params, ?string $element = null, ?string $index = null): string
{
    $accountNumber = [];
    $picture = [];

    $products = CSaleOrder::GetList(array($field => $params), array($element => $index),
        false, false, array('ACCOUNT_NUMBER', 'DATE_INSERT_FORMAT', 'PRICE', 'STATUS_ID'));

    while ($res = $products->Fetch()) {
        $accountNumber[] = $res;
    }

    for ($i = 0; $i < count($accountNumber); $i++) {
        $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $accountNumber[$i]['ACCOUNT_NUMBER']));
        if (!empty($ordersBasket)) {
            while ($result = $ordersBasket->Fetch()) {
                $my_elements = CIBlockElement::GetList(
                    array(),
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

$url = substr(json_decode($_POST['url'], true), 1, -2);

switch (json_decode($_POST['typeSort'], true)) {
    case "Дешёвые":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            print_r(sortByField("PRICE", 'ASC'));
        } else if ($url === 'show_canceled') {
            print_r(sortByField("PRICE", 'ASC', 'STATUS_ID', 'F'));
        } else if ($url === 'show_delivery') {
            print_r(sortByField("PRICE", 'ASC', 'RESERVED', 'Y'));
        }
        break;
    case "Дорогие":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            print_r(sortByField("PRICE", 'DESC'));
        } else if ($url === 'show_canceled') {
            print_r(sortByField("PRICE", 'DESC', 'STATUS_ID', 'F'));
        } else if ($url === 'show_delivery') {
            print_r(sortByField("PRICE", 'DESC', 'RESERVED', 'Y'));
        }
        break;
    case "Старые":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            print_r(sortByField("DATE_INSERT", 'ASC'));
        } else if ($url === 'show_canceled') {
            print_r(sortByField("DATE_INSERT", 'ASC', 'STATUS_ID', 'F'));
        } else if ($url === 'show_delivery') {
            print_r(sortByField("DATE_INSERT", 'ASC', 'RESERVED', 'Y'));
        }
        break;
    case "Новые":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            print_r(sortByField("DATE_INSERT", 'DESC'));
        } else if ($url === 'show_canceled') {
            print_r(sortByField("DATE_INSERT", 'DESC', 'STATUS_ID', 'F'));
        } else if ($url === 'show_delivery') {
            print_r(sortByField("DATE_INSERT", 'DESC', 'RESERVED', 'Y'));
        }
        break;
}

