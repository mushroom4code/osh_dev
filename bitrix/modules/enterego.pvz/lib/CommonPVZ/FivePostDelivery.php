<?php

namespace CommonPVZ;

class FivePostDelivery extends CommonPVZ
{

    protected $delivery_name = 'FivePost';


    protected function connect()
    {

    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city)
    {

        $resultPVZ = [];

        try {
            if (!empty(\COption::GetOptionString('ipol.fivepost', 'apiKey'))) {
                $resultPVZ = \Enterego\EnteregoDBDelivery::getPoints5postForALLMap($code_city);
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();

        }

        if (count($resultPVZ) !== 0) {
            foreach ($resultPVZ as $key => $value) {
                $region = '';

                $features_obj['type'] = 'Feature';
                $features_obj['id'] = $id_feature;
                $id_feature += 1;
                $features_obj['geometry'] = [
                    'type' => 'Point',
                    'coordinates' => [
                        $value['ADDRESS_LAT'],
                        $value['ADDRESS_LNG']
                    ]
                ];
                if (!strripos($value['ADDRESS_REGION'], ' город')) {
                    $region = ', ' . $value['ADDRESS_REGION'];
                }
                $features_obj['properties'] = [
                    'code_pvz' => $value['POINT_GUID'],
                    'type' => 'POSTAMAT',
                    'fullAddress' => $value['ADDRESS_COUNTRY'] . ', ' . $value['ADDRESS_ZIP_CODE'] . $region . ', ' . $value['FULL_ADDRESS'],
                    'comment' => $value['ADDITIONAL'],
                    'deliveryName' => '5Post',
                    'fivepostZone' => $value['ID'],
                    'iconCaption' => '5Post',
                    'hintContent' => $value['FULL_ADDRESS'],
                    'openEmptyBalloon' => true,
                    'clusterCaption' => '5Post',
                ];
                $features_obj['options'] = [
                    'preset' => 'islands#darkBlueIcon'
                ];

                $result_array[] = $features_obj;
            }
        }
    }

    public function getPrice($array)
    {
        try {
            $pricePVZ = \Enterego\EnteregoDBDelivery::getPriceForPoint($array['fivepost_zone']);
            return json_encode(round($pricePVZ));
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;

    }
}