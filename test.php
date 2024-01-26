<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$_GET["mode"] = 'import';
$_GET["filename"] = 'contragents___8ccb7ce5-fae0-41dc-a74d-61c38c0dfbb7.xml';
COption::SetOptionString("sale", "secure_1c_exchange", "N");
global $USER;
$USER->Authorize('16407');
$APPLICATION->IncludeComponent("bitrix:sale.export.1c_ee", "", Array(
        "SITE_LIST" => COption::GetOptionString("sale", "1C_SALE_SITE_LIST", ""),
        "EXPORT_PAYED_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_PAYED_ORDERS", ""),
        "EXPORT_ALLOW_DELIVERY_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", ""),
        "EXPORT_FINAL_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_FINAL_ORDERS", ""),
        "CHANGE_STATUS_FROM_1C" => COption::GetOptionString("sale", "1C_CHANGE_STATUS_FROM_1C", ""),
        "FINAL_STATUS_ON_DELIVERY" => COption::GetOptionString("sale", "1C_FINAL_STATUS_ON_DELIVERY", "F"),
        "REPLACE_CURRENCY" => COption::GetOptionString("sale", "1C_REPLACE_CURRENCY", ""),
        "GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", "1")),
        "USE_ZIP" => COption::GetOptionString("sale", "1C_SALE_USE_ZIP", "Y"),
        "INTERVAL" => COption::GetOptionString("sale", "1C_INTERVAL", 30),
        "FILE_SIZE_LIMIT" => COption::GetOptionString("sale", "1C_FILE_SIZE_LIMIT", 200*1024),
        "SITE_NEW_ORDERS" => COption::GetOptionString("sale", "1C_SITE_NEW_ORDERS", "s1"),
        "IMPORT_NEW_ORDERS" => COption::GetOptionString("sale", "1C_IMPORT_NEW_ORDERS", "N"),
    )
);