<?php

use CommonPVZ\CommonPVZ;
use CommonPVZ\DeliveryHelper;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule('enterego.pvz')
    || !CModule::IncludeModule('sale')) {

    return;
}

$request = Bitrix\Main\Context::getCurrent()->getRequest();

if ($USER->IsAuthorized()) {
    if (check_bitrix_sessid()) {
        $CONFIG_DELIVERIES = DeliveryHelper::getConfigs();

        $action = $request->get('action');
        $codeCity = $request->get('codeCity');
        $cityName = $request->get('cityName');
        $packages = $request->get('orderPackages');

        switch ($action) {
            case 'getCityName':
                exit(DeliveryHelper::getCityName($codeCity));
            case 'getSavedOshishaDelivery':
                exit(json_encode(DeliveryHelper::getSavedOshishaDelivery($request->get('latitude'), $request->get('longitude'))));
            case 'getNoMarkupDaysOshisha':
                exit(json_encode((new \CommonPVZ\OshishaDelivery())->getNoMarkupDays()));
            case 'saveOshishaDelivery':
                exit(json_encode(DeliveryHelper::SaveOshishaDelivery($request->get('params'))));
            case 'getDaData':
                $address = $request->get('address');
                $daData = DeliveryHelper::getDaDataAddressInfo($address);
                if (!empty($daData['value'])) {
                    $daData['status'] = 'success';
                    exit(json_encode($daData));
                } else {
                    exit(json_encode(['status' => 'not find address']));
                }
            case 'reverseGeocodeAddress':
                $daData = DeliveryHelper::getDaDataAddressByGeolocation($request->get('latitude'), $request->get('longitude'));
                if (!empty($daData['value'])) {
                    $daData['status'] = 'success';
                    exit(json_encode($daData));
                } else {
                    exit(json_encode(['status' => 'not find address']));
                }
            case 'updatePickPointPoints':
                exit(json_encode(DeliveryHelper::updatePickPointPVZ()));
            case 'updateDellinPoints':
                exit(json_encode(DeliveryHelper::updateDellinPVZ()));
            case 'updateRussianPostPoints':
                exit(json_encode(DeliveryHelper::updateRussianPostPVZ()));
            case 'updateFivePostPoints':
                exit(json_encode(DeliveryHelper::updateFivePostPVZ()));
            case 'updateOshishaRegionRestrictions':
                exit(json_encode(DeliveryHelper::updateOshishaRegionRestrictions()));
            case 'getPVZList':
                $deliveries = DeliveryHelper::getActivePvzDeliveryInstance(array('codeCity' => $codeCity));
                $response = json_encode(DeliveryHelper::getAllPVZ($deliveries, $cityName, $codeCity, $packages));
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

                    if (empty($price['errors'])) {
                        $data[] = ['id' => $pointData['id'], 'price' => $price];
                    } else {
                        $data[] = ['id' => $pointData['id'], 'error' => $price['errors']];
                    }
                }
                exit(json_encode(['status' => 'success', 'data' => $data]));
            default:
                exit(json_encode(['status' => 'error', 'errors' => ['not correct action']]));
        }
    } else {
        exit(json_encode(['status' => 'error', 'errors' => ['session error']]));
    }
} else {
    exit(json_encode(['status' => 'error', 'errors' => ['session error']]));
}