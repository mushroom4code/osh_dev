<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!Bitrix\Main\Loader::includeModule('enterego.pvz'))
    return;

$delHelper = new \CommonPVZ\DeliveryHelper();

if ($_POST['action'] === 'getCityName') {
    $_SESSION['pricePVZ'] = 0;
    $_SESSION['addressPVZ'] = '';

    exit(\CommonPVZ\DeliveryHelper::getCityName($_POST['codeCity']));
}

if ($_POST['action'] === 'getPVZList') {
    exit(json_encode($delHelper->getAllPVZ($_POST['cityName'], $_POST['codeCity'])));
}

if ($_POST['action'] === 'getPrice') {
    exit(json_encode(\CommonPVZ\DeliveryHelper::getPrice($_POST)));
}