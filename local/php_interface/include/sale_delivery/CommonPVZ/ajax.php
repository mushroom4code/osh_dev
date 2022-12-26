<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$delHelper = new \CommonPVZ\DeliveryHelper();

if ($_POST['action'] === 'getCityName') {
    exit(\CommonPVZ\DeliveryHelper::getCityName($_POST['codeCity']));
}

if ($_POST['action'] === 'getPVZList') {
    exit(json_encode($delHelper->getAllPVZ($_POST['cityName'])));
}

if ($_POST['action'] === 'getPrice') {
    exit(json_encode(\CommonPVZ\DeliveryHelper::getPrice($_POST)));
}