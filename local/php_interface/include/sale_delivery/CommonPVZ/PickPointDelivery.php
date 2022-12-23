<?php

namespace CommonPVZ;

use PickPointSdk\Components\PackageSize;
use PickPointSdk\Components\SenderDestination;
use PickPointSdk\PickPoint\PickPointConf;
use PickPointSdk\PickPoint\PickPointConnector;
use PickPointSdk\Components\ReceiverDestination;

class PickPointDelivery
{
    // TODO вынести в настройки
    private $config = [
        'host' => 'https://e-solution.pickpoint.ru/api/',
        'login' => 'hYdz3J',
        'password' => '6jUzhQ7iwfgj0',
        'ikn' => '9990000112',
    ];

    private $client = null;
    public $error = null;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        try {
            $pickPointConf = new PickPointConf(
                $this->config['host'],
                $this->config['login'],
                $this->config['password'],
                $this->config['ikn']
            );
            $defaultPackageSize = new PackageSize(20, 20, 20); // может быть null
            $senderDestination = new SenderDestination('Москва', 'Московская обл.'); // Адрес отправителя
            $this->client = new PickPointConnector($pickPointConf, $senderDestination, $defaultPackageSize);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getPVZ($city_name, &$result_array, &$id_feature)
    {

        try {
            $pickPoint_result = $this->client->getPoints();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

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
    }

    public function getPrice($array)
    {
        $receiverDestination = new ReceiverDestination($array['name_city'], $array['hubregion']);
        $tariffPrice = $this->client->calculateObjectedPrices($receiverDestination); // Вернет объект с ценами
        $commonStandardPrice = $tariffPrice->getStandardCommonPrice(); // получить общую цену с тарифом стандарт
        return json_encode(round($commonStandardPrice));
    }
}