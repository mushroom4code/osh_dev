<?php

namespace CommonPVZ;


use Bitrix\Main\Config\Option;

class PEKDelivery extends CommonPVZ
{
    public string $delivery_code = 'PEK';

    public string $delivery_name = 'PEK';

    public static string $code = 'PEK';

    public static function getDeliveryStatus() {
        return array('PEK' => Option::get(DeliveryHelper::$MODULE_ID, 'PEK_active'));
    }

    protected function connect()
    {
        try {
            $this->client = new \PecomKabinet(
                $this->configs['login'],
                $this->configs['password']
            );

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions)
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
            $this->errors[] = $e->getMessage();
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
                'type' => 'PVZ',
                'fullAddress' => $value['warehouses'][0]['addressDivision'],
                'deliveryName' => $this::$code,
                'iconCaption' => 'ПЭК',
                'hintContent' => $value['warehouses'][0]['address'],
                'phone' => $value['warehouses'][0]['telephone'],
                'comment' => $value['warehouses'][0]['pointerDescription'],
                'clusterCaption' => 'ПЭК',
            ];
            $features_obj['options'] = [
                'preset' => 'islands#nightIcon'
            ];

            $result_array[] = $features_obj;
        }
    }

    public function getPrice($array)
    {
        try {
            $pek_result = $this->client->call('BRANCHES', 'FINDZONEBYADDRESS', // поиск uid города по названию города
                array(
                    "address" => $array['name_city']
                )
            );
            $pek_bitrix_id = $pek_result->bitrixId;
            $pek_result = $this->client->call('CALCULATOR', 'CALCULATEPRICE',
                array(
                    "senderCityId" => $this->configs['sendercityid'],
                    "receiverCityId" => $pek_bitrix_id,
                    "senderDistanceType" => $this->configs['senderdistancetype'],
                    "cargos" => array(
                        array(
                            'weight' => $array['weight'] / 1000
                        )
                    ),
                    'calcDate' => date('Y-m-d')
                )
            );

            $this->client->close();
            return json_encode($pek_result->transfers[0]->costTotal);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;
    }

    public function getPriceDoorDelivery($params)
    {
        $this->errors[] = 'ПЭК не имеет доставки до двери';
        return array('errors' => $this->errors);
    }


    public static function getInstanceForPvz($deliveryParams): array
    {
        return [];
    }

    public static function getInstanceForDoor($deliveryParams): array
    {
        return [];
    }
}