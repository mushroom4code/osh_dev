<?php
/** @var $APPLICATION */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:breadcrumb", "oshisha_breadcrumb", array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => "s1"
    )
);

$APPLICATION->IncludeComponent(
    "bbrain:brands",
    "",
    array(
        "ID" => "BRANDS",
        "HLBLOCK_NAME" => 'BREND',
        'SEF_URL' => '/brands/',
        'NAME' => 'Бренды',
        'COUNT' => 20

    )
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
