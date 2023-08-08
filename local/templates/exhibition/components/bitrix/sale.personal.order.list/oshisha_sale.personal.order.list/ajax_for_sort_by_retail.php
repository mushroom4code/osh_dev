<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('sale')) {
    exit();
}

function sortByField(string  $user_type,
                     int     $user_tye_value,
                     ?string $element = null,
                     ?string $index = null): string
{
    $accountNumber = [];
    $picture = [];

    $products = CSaleOrder::GetList(array(), array($user_type => $user_tye_value, $element => $index),
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

$typeSort = json_decode($_POST['sort'], true)['type'];
$typeOrder = substr(json_decode($_POST['sort'], true)['url'], 1, -2);

switch ($typeSort) {
    case "retail":
        if ($typeOrder !== 'show_canceled' && $typeOrder !== 'show_delivery') {
            print_r(sortByField("PERSON_TYPE_ID", 1));
        } else if ($typeOrder === 'show_canceled') {
            print_r(sortByField("PERSON_TYPE_ID", 1, 'STATUS_ID', 'F'));
        } else if ($typeOrder === 'show_delivery') {
            print_r(sortByField("PERSON_TYPE_ID", 1, 'RESERVED', 'Y'));
        }
        break;
    case "wholesale":
        if ($typeOrder !== 'show_canceled' && $typeOrder !== 'show_delivery') {
            print_r(sortByField("PERSON_TYPE_ID", 2));
        } else if ($typeOrder === 'show_canceled') {
            print_r(sortByField("PERSON_TYPE_ID", 2, 'STATUS_ID', 'F'));
        } else if ($typeOrder === 'show_delivery') {
            print_r(sortByField("PERSON_TYPE_ID", 2, 'RESERVED', 'Y'));
        }
        break;
}