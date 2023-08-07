<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('sale')) {
    exit();
}

$arParams = json_decode($_POST['newValues']);

$user = new CUser();

$userId = (int)$arParams->ID;
$active = $arParams->ACTIVE;

$user->Update($userId, array('ACTIVE' => $active));
