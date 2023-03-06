<?php

use CommonPVZ\CommonPVZ;
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
    case 'updatePickPointPoints':
        exit(json_encode(DeliveryHelper::updatePickPointPVZ()));
    case 'getPVZList':
        $response  = json_encode(DeliveryHelper::getAllPVZ($deliveries, $cityName, $codeCity));
        exit($response);
    case 'getPVZPrice':
        $dataToHandler = $request->get('dataToHandler');
        if ($dataToHandler['code_pvz'] === 'undefined') {
            $adr = $dataToHandler['delivery'] . ': ' . $dataToHandler['to'];
        } else {
            $adr = $dataToHandler['delivery'] . ': ' . $dataToHandler['to'] . ' #' . $dataToHandler['code_pvz'];
        }
        $delivery = CommonPVZ::getInstanceObject($dataToHandler['delivery']);
        $price = $delivery->getPrice($dataToHandler);
        exit($price);
    default:
        exit(json_encode(['status'=>'error', 'errors'=>['not correct action']]));
}
