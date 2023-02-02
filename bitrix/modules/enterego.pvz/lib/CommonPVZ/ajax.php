<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!Bitrix\Main\Loader::includeModule('enterego.pvz'))
    return;

use CommonPVZ\DeliveryHelper;

$CONFIG_DELIVERIES = DeliveryHelper::getConfigs();

$deliveries = [];
foreach ($CONFIG_DELIVERIES as $k => $v) {
    $deliveries[] = $k;
}

if ($_POST['action'] === 'getCityName') {
    exit(\CommonPVZ\DeliveryHelper::getCityName($_POST['codeCity']));
}

if ($_POST['action'] === 'getPVZList') {
    exit(json_encode(\CommonPVZ\DeliveryHelper::getAllPVZ($deliveries, $_POST['cityName'], $_POST['codeCity'])));
}
