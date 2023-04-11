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
    case 'checkMoscowOrNot':
        exit(DeliveryHelper::checkMoscowOrNot($codeCity));
    case 'updatePickPointPoints':
        exit(json_encode(DeliveryHelper::updatePickPointPVZ()));
    case 'updateDellinPoints':
        exit(json_encode(DeliveryHelper::updateDellinPVZ()));
    case 'getPVZList':
        $response  = json_encode(DeliveryHelper::getAllPVZ($deliveries, $cityName, $codeCity));
        exit($response);
    case 'getPVZPrice':
        $dataToHandler = $request->get('dataToHandler');
        $data = [];
        foreach ($dataToHandler as $pointData) {
            if ($pointData['code_pvz'] === 'undefined') {
                $adr = $pointData['delivery'] . ': ' . $pointData['to'];
            } else {
                $adr = $pointData['delivery'] . ': ' . $pointData['to'] . ' #' . $pointData['code_pvz'];
            }
            $delivery = CommonPVZ::getInstanceObject($pointData['delivery']);
            $price = $delivery->getPrice($pointData);
            $data[] = ['id'=>$pointData['id'], 'price'=>$price];
        }
        exit(json_encode(['status'=>'success', 'data'=>$data]));
    default:
        exit(json_encode(['status'=>'error', 'errors'=>['not correct action']]));
}
