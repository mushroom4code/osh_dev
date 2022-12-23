<?php

namespace CommonPVZ;

use Symfony\Component\HttpClient\Psr18Client;
use CdekSDK2\Client;

class SDEKDelivery
{
    // TODO вынести в настройки
    private $config = [
        'setAccount' => 'Qzidnsik5xW7cyJ86Vu89zwshwTq6iQF',
        'setSecure' => 'eyQN5S9Kvn5xcJWIHjWQFMGxVH6FfMbZ'
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
            $client = new Psr18Client();
            $this->client = new Client($client);
            $this->client->setAccount($this->config['setAccount']);
            $this->client->setSecure($this->config['setSecure']);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getPVZ($city_name, &$result_array, &$id_feature)
    {
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
    }
}