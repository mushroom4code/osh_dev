<?php
/** @global CMain $APPLICATION */

define("HIDE_SIDEBAR", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Акции");
$APPLICATION->SetTitle("Акции");
$APPLICATION->IncludeComponent("bitrix:breadcrumb", "oshisha_breadcrumb", array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => "s1"
    )
);

/**
 * @var  CAllMain|CMain $APPLICATION
 */
$nowDate = date("Y-m-d H:i:s");
$date = new DateTime($nowDate);

$APPLICATION->IncludeComponent(
    "bitrix:enterego.discounts",
    "",
    array(
        'SEF_URL' => '/akcii/',
        'NAME' => 'Акции',
        'COUNT' => 20
    )
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
