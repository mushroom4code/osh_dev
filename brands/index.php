<?php
/** @var $APPLICATION */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:breadcrumb", "oshisha_breadcrumb", array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => SITE_ID
    )
);

$iBlockId = IBLOCK_CATALOG;
$arFilterBrand = $filter = [];
if (SITE_ID === SITE_EXHIBITION) {
    $iBlockId = IBLOCK_CATALOG_EX;
    $arData = CIBlockSection::GetList([], ['IBLOCK_ID' => $iBlockId]);
    while ($brand = $arData->GetNext()) {
     if ((int)$brand['DEPTH_LEVEL'] == 2 &&  $brand['ACTIVE'] === 'Y') {
            $arFilterBrand[$brand['NAME']] = $brand['ID'];
     }
    }
    $filter = ['UF_NAME'=> array_keys($arFilterBrand)];
}

$APPLICATION->IncludeComponent(
    "bbrain:brands",
    "",
    array(
        "ID" => "BRANDS",
        "HLBLOCK_NAME" => 'BREND',
        "IBLOCK_ID" => $iBlockId,
        "AR_FILTER" => $filter,
        'SEF_URL' => '/brands/',
        'NAME' => 'Бренды',
        'COUNT' => 20

    )
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
