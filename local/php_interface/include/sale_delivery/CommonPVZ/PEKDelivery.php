<?php

namespace CommonPVZ;

require_once('pecom_kabinet.php');

class PEKDelivery extends CommonPVZ
{
    protected $configs = [
        'login' => 'smaksultan',
        'password' => 'C78CCD5137422CCE292210C7F1AADF57284D7320',
    ];

    protected function connect()
    {
        try {
            $client = new PecomKabinet(
                $this->configs['login'],
                $this->configs['password']
            );

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function getPVZ($city_name, &$result_array, &$id_feature)
    {

        try {
            $pek_result = $this->client->call('BRANCHES', 'FINDZONEBYADDRESS', // поиск uid города по названию города
                array(
                    "address" => $city_name
                )
            );
            $pek_result = $this->client->call('BRANCHES', 'ALL', // список ПВЗ ПЭК узнаем по uid города
                array(
                    "branchId" => $pek_result->branchUID
                ), true // результат ждем в виде массива
            );
            $this->client->close();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        foreach ($pek_result['divisions'] as $key => $value) {
            $coords_array = explode(',', $value['warehouses'][0]['coordinates']);
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $coords_array[0],
                    $coords_array[1]
                ]
            ];
            $features_obj['properties'] = [
                'fullAddress' => $value['warehouses'][0]['addressDivision'],
                'deliveryName' => 'ПЭК',
                'iconContent' => 'ПЭК',
                'hintContent' => $value['warehouses'][0]['address']
            ];
            $features_obj['options'] = [
                'preset' => 'islands#nightIcon'
            ];

            $result_array['features'][] = $features_obj;
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