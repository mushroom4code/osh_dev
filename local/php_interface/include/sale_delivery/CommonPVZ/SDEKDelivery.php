<?php

namespace CommonPVZ;

use CommonPVZ\CommonPVZ;
use Symfony\Component\HttpClient\Psr18Client;
use CdekSDK2\Client;

class SDEKDelivery extends CommonPVZ
{

    protected $configs = [
        'setAccount' => 'Qzidnsik5xW7cyJ86Vu89zwshwTq6iQF',
        'setSecure' => 'eyQN5S9Kvn5xcJWIHjWQFMGxVH6FfMbZ'
    ];

    protected function connect()
    {
        try {
            $client = new Psr18Client();
            $this->client = new Client($client);
            $this->client->setAccount($this->configs['setAccount']);
            $this->client->setSecure($this->configs['setSecure']);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function getPVZ($city_name, &$result_array, &$id_feature)
    {
        try {
            $result = $this->client->cities()->getFiltered(['country_codes' => 'RU', 'city' => $city_name]);
            if ($result->isOk()) {
                $cities = $this->client->formatResponseList($result, \CdekSDK2\Dto\CityList::class);
            }
            $result = $this->client->offices()->getFiltered(['city_code' => $cities->items[0]->code]);
            if ($result->isOk()) {
                $sdek_result = $this->client->formatResponseList($result, \CdekSDK2\Dto\PickupPointList::class);
            }

            foreach ($sdek_result->items as $key => $value) {
                $features_obj['type'] = 'Feature';
                $features_obj['id'] = $id_feature;
                $id_feature += 1;
                $features_obj['geometry'] = [
                    'type' => 'Point',
                    'coordinates' => [
                        $value->location->latitude,
                        $value->location->longitude
                    ]
                ];
                $features_obj['properties'] = [
                    'code_pvz' => $value->code,
                    'fullAddress' => $value->location->address_full,
                    'deliveryName' => 'СДЭК',
                    'iconContent' => 'СДЭК',
                    'hintContent' => $value->location->address
                ];
                $features_obj['options'] = [
                    'preset' => 'islands#darkGreenIcon'
                ];
                $result_array[] = $features_obj;
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }

    }
}