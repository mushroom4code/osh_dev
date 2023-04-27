<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Location\LocationTable;
use Exception;
use PickPointSdk\Components\PackageSize;
use PickPointSdk\Components\SenderDestination;
use PickPointSdk\PickPoint\PickPointConf;
use PickPointSdk\PickPoint\PickPointConnector;
use PickPointSdk\Components\ReceiverDestination;

class PickPointDelivery extends CommonPVZ
{
    public string $delivery_name = 'PickPoint';

    public static function getDeliveryStatus() {
        return array('PickPoint' => Option::get(DeliveryHelper::$MODULE_ID, 'PickPoint_active'));
    }

    protected function connect()
    {
        try {
            $pickPointConf = new PickPointConf(
                $this->configs['host'],
                $this->configs['login'],
                $this->configs['password'],
                $this->configs['ikn']
            );
            $defaultPackageSize = new PackageSize(20, 20, 20); // может быть null
            $senderDestination = new SenderDestination('Москва', 'Московская обл.'); // Адрес отправителя
            $this->client = new PickPointConnector($pickPointConf, $senderDestination, $defaultPackageSize);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * @throws Exception
     */
    public function updatePointsForPickPoint()
    {
        $pickPoint_result = $this->client->getPoints();
        PickPointPointsTable::deleteAll();

        $res = LocationTable::getList(array(
            'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
            'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
        ));
        $listLocation = [];
        while ($arLocation = $res->fetch()) {
            $listLocation[] = $arLocation;
        }
        foreach ($pickPoint_result as $value) {
            $curLocation = null;
            foreach ($listLocation as $location) {
                if ($location['NAME_RU'] === $value['CitiName']) {
                    $curLocation = $location;
                    break;
                }
            }

            if ($curLocation===null) {
                continue;
            }
            $point = new PickPointPointsTable();
            $point->add([
                'ID'=>$value['Id'],
                'BITRIX_CODE' => $curLocation['CODE'],
                'CODE'=>$value['Number'],
                'ADDRESS_REGION'=>$value['HubRegion'] ?? '',
                'FULL_ADDRESS'=>$value['HubRegion'] . ', ' . $value['CitiName'] . ', ' . $value['Address'],
                'ADDRESS_LAT'=>$value['Latitude'],
                'ADDRESS_LNG'=>$value['Longitude'],
            ]);
        }
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash)
    {
        $arParams = ['filter'=>['BITRIX_CODE'=>$code_city]];
        $res = PickPointPointsTable::getList($arParams);
        while ($point = $res->fetch()){
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $point['ADDRESS_LAT'],
                    $point['ADDRESS_LNG'],
                ]
            ];
            $features_obj['properties'] = [
                'code_pvz' => $point['CODE'],
                'type' => 'POSTAMAT',
                'fullAddress' => $point['FULL_ADDRESS'],
                'deliveryName' => 'PickPoint',
                'iconCaption' => 'PickPoint',
                'hintContent' => $point['FULL_ADDRESS'],
                "hubregion" => $point['ADDRESS_REGION'],
                "openEmptyBalloon" => true,
                "clusterCaption" => 'PickPoint',
            ];
            $features_obj['options'] = [
                'preset' => 'islands#darkOrangeIcon'
            ];

            $result_array[] = $features_obj;
        }
    }

    public function getPrice($array)
    {
        try {
            $receiverDestination = new ReceiverDestination($array['name_city'], $array['hubregion']);
            $tariffPrice = $this->client->calculateObjectedPrices($receiverDestination); // Вернет объект с ценами
            $commonStandardPrice = $tariffPrice->getStandardCommonPrice(); // получить общую цену с тарифом стандарт
            return json_encode(round($commonStandardPrice));
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;
    }

    public function getPriceDoorDelivery($params)
    {
        return 100;
    }


}