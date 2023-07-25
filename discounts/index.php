<?php
/** @global CMain $APPLICATION */

define("HIDE_SIDEBAR", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Акции");
$APPLICATION->SetTitle("Хиты");
$APPLICATION->IncludeComponent("bitrix:breadcrumb", "oshisha_breadcrumb", array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => "s1"
    )
);

//function getDiscountP($id)
//{
//    Bitrix\Main\Loader::includeModule('sale');
//    require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/discountpreset/simpleproduct.php");
//
//    $arDiscounts = [];
//    $arProductDiscounts = \Bitrix\Sale\Internals\DiscountTable::getList([
//        'filter' => [
//            'ID' => $id,
//        ],
//        'select' => [
//            "*"
//        ]
//    ]) -> fetch();
//
//    $discountObj = new Sale\Handlers\DiscountPreset\SimpleProduct();
//    $discount =  $discountObj->generateState($arProductDiscounts);
//
//    $arDiscounts['PRODUCTS'] = $discount['discount_product']; // товары
//    $arDiscounts['TYPE'] = $discount['discount_type'];
//    $arDiscounts['GROUPS'] = $discount['discount_section'];
//    $arDiscounts['VALUE'] = $discount['discount_value'];
//
//    return $arDiscounts;
//}


/**
 * @var  CAllMain|CMain $APPLICATION
 */
$nowDate = date("Y-m-d H:i:s");
$date = new DateTime($nowDate);

//$rrrr= getDiscountP(88);
//$sasRes = CCatalogDiscount::GetList([]);
//
//$sasAr = [];
//
//while ($s = $sasRes->fetch()) {
//    $sasAr[] = $s;
//}
//
//$res = CIBlock::GetList(
//    Array('id' => 'desc'),
//    Array(
//        'TYPE'=>'discounts',
//        'SITE_ID'=>SITE_ID,
//        'ACTIVE'=>'Y',
//        "CNT_ACTIVE"=>"Y"
//    ), true
//);
//$iblocks = [];
//while ($iblock = $res->fetch()) {
//    $iblocks[$iblock['ID']] = $iblock;
//}
//$sas = 'sas';


$APPLICATION->IncludeComponent(
    "bitrix:enterego.discounts",
    "",
    array(
        'SEF_URL' => '/discounts/',
        'NAME' => 'Акции',
        'COUNT' => 20
    )
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
