<?php

use CommonPVZ\DeliveryHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!Bitrix\Main\Loader::includeModule('enterego.pvz'))
    return;

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$CONFIG_DELIVERIES = DeliveryHelper::getConfigs();

$deliveries = [];
foreach ($CONFIG_DELIVERIES as $k => $v) {
    $deliveries[] = $k;
}

$action = $request->get('action');
$codeCity = $request->get('codeCity');
$cityName = $request->get('cityName');

switch ($action) {
    case 'getCityName':
        exit(DeliveryHelper::getCityName($codeCity));
    case 'getPVZList':
        exit(json_encode(DeliveryHelper::getAllPVZ($deliveries, $cityName, $codeCity)));
    default:
        exit(json_encode(['status'=>'error', 'errors'=>['not correct action']]));
}
