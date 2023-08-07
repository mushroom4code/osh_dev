<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('sale')) {
    exit();
}

$arParams = json_decode($_POST['newValues']);

$userId = (int)$arParams->ID;
$newUserName = trim($arParams->NAME);
$newUserEmail = trim($arParams->EMAIL);
$newUserPhone = trim($arParams->PHONE);


$userTable = new CUser();

$userTable->Update($userId, array(
    'NAME' => $newUserName,
    'LOGIN' => $newUserEmail,
    'EMAIL' => $newUserEmail,
    'PERSONAL_PHONE' => $newUserPhone
));
