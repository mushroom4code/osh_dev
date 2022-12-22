<?php

namespace CommonPVZ;

use PickPointSdk\Components\PackageSize;
use PickPointSdk\Components\SenderDestination;
use PickPointSdk\PickPoint\PickPointConf;
use PickPointSdk\PickPoint\PickPointConnector;

class PickPointDelivery
{
    public static function getPVZ($city_name, &$result_array, &$id_feature)
    {

        try {
            // TODO - брать из настроек СД и вынести в конструктор
            $config = [
                'host' => 'https://e-solution.pickpoint.ru/api/',
                'login' => 'hYdz3J',
                'password' => '6jUzhQ7iwfgj0',
                'ikn' => '9990000112',
            ];

            $pickPointConf = new PickPointConf($config['host'], $config['login'], $config['password'], $config['ikn']);
            $defaultPackageSize = new PackageSize(20, 20, 20); // может быть null
            $senderDestination = new SenderDestination('Москва', 'Московская обл.'); // Адрес отправителя
            $client = new PickPointConnector($pickPointConf, $senderDestination, $defaultPackageSize);

            $pickPoint_result = $client->getPoints();

            foreach ($pickPoint_result as $key => $value) {
                if ($value['HubCity'] === $city_name) {
                    $features_obj['type'] = 'Feature';
                    $features_obj['id'] = $id_feature;
                    $id_feature += 1;
                    $features_obj['geometry'] = [
                        'type' => 'Point',
                        'coordinates' => [
                            $value['Latitude'],
                            $value['Longitude']
                        ]
                    ];
                    $features_obj['properties'] = [
                        'code_pvz' => $value['Number'],
                        'fullAddress' => $value['HubCity'] . ', ' . $value['Address'],
                        'deliveryName' => 'PickPoint',
                        'iconContent' => 'PickPoint',
                        'hintContent' => $value['Address'],
                        "hubregion" => $value['HubRegion']
                    ];
                    $features_obj['options'] = [
                        'preset' => 'islands#darkOrangeIcon'
                    ];

                    $result_array[] = $features_obj;
                }
            }
        } catch (\Exception $e) {
            $result_array['Errors'][] = $e->getMessage();
        }
        return $result_array;
    }

    public static function getPrice($array) {

    }
}