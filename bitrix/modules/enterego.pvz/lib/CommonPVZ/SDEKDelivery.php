<?php

namespace CommonPVZ;

class SDEKDelivery extends CommonPVZ
{
    public string $delivery_name = 'SDEK';
    private $cdek_client;

    protected function connect()
    {
        $this->cdek_client = new \AntistressStore\CdekSDK2\CdekClientV2($this->configs['setaccount'], $this->configs['setsecure']);
        return true;
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city)
    {
        $sdek_city_code = $this->getSDEKCityCode($city_name);
        $requestPvz = (new \AntistressStore\CdekSDK2\Entity\Requests\DeliveryPoints())
            ->setCityCode($sdek_city_code);
        $sdek_result = $this->cdek_client->getDeliveryPoints($requestPvz);

        foreach ($sdek_result as $value) {
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $value->getLocation()->getLatitude(),
                    $value->getLocation()->getLongitude()
                ]
            ];
            $features_obj['properties'] = [
                'code_pvz' => $value->getCode(),
                'type' => $value->getType() === 'PVZ' ? "PVZ" : 'POSTAMAT',
                'workTime'=> $value->getWorkTime(),
                'comment' => $value->getAddressComment(),
                'fullAddress' => $value->getLocation()->getAddressFull(),
                'deliveryName' => 'СДЭК',
                'iconCaption' => 'СДЭК',
                'hintContent' => $value->getLocation()->getAddress(),
                "openEmptyBalloon" => true,
                "clusterCaption" => 'СДЭК',
            ];
            if (!empty($value->phones)) {
                $features_obj['properties']['phone'] = $value->getPhones()[0]->getNumber();
            }
            $features_obj['options'] = [
                'preset' => 'islands#darkGreenIcon'
            ];

            $result_array[] = $features_obj;
        }
    }

    /** TODO find by name not unambiguous result
     * @param $cityName
     * @return false|string
     */
    private function getSDEKCityCode($cityName)
    {
        $location = (new \AntistressStore\CdekSDK2\Entity\Requests\Location())
            ->setCountryCodes('RU')
            ->setCity($cityName);
        $locationList = $this->cdek_client->getCities($location);
        return $locationList[0]->getCode();
    }

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    public function getPrice($array)
    {
        try {
            $sdek_city_code = $this->getSDEKCityCode($array['name_city']);

            if ($this->token !== '' && $sdek_city_code) {
                $searchParams = [
                    'tariff_code' => $this->configs['tariff'],
                    'from_location' => ['code'=>$this->configs['from']],
                    'to_location' => ['code'=>$sdek_city_code, 'address'=>$array['to']],
                    'packages' => ['weight'=>$array['weight']],
                ];

                $resp = $this->request('https://api.cdek.ru/v2/calculator/tariff',
                    json_encode($searchParams), 'POST');

                //todo errors calculation
                if (isset($resp->errors)) {
                    return false;
                }
                if (isset($resp->total_sum)) {
                    return round($resp->total_sum);
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            $location_to = $this->getSDEKCityCode($params['location_name']);

            $tariff = (new \AntistressStore\CdekSDK2\Entity\Requests\Tariff())
                ->setTariffCode($this->configs['tariff'])
                ->setCityCodes($this->configs['from'], $location_to)
                ->addServices(['PART_DELIV']);

            foreach ($params['packages'] as $package) {
                $tariff->setPackages((new \AntistressStore\CdekSDK2\Entity\Requests\Package())
                    ->setHeight(!empty($package['height']) ? $package['height'] : 0)
                    ->setLength(!empty($package['length']) ? $package['length'] : 0)
                    ->setWidth(!empty($package['width']) ? $package['width'] : 0)
                    ->setWeight(!empty($package['weight']) ? $package['weight'] : 0));
            }

            $calculatedTariff = $this->cdek_client->calculateTariff($tariff);
            $delivery_price = $calculatedTariff->getTotalSum();
            return $delivery_price;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;
    }
}