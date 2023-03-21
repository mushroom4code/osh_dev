<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


$obUser = new CUser;

global $USER;

if ($USER->IsAuthorized() && $_REQUEST['action'] == 'setConsent') {
    $arFields = ['UF_USER_COOKIE_CONSENT'=>'1'];
    if ($obUser->Update($USER->GetId(), $arFields)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo "noauth";
}