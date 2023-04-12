<?php

namespace CommonPVZ;

class SDEKDelivery extends CommonPVZ
{
    public string $delivery_name = 'SDEK';
    private $cdek_cache_id = 'sdek_delivery_prices';
    private $cdek_client;

    protected function connect()
    {
        try {
            $this->cdek_client = new \AntistressStore\CdekSDK2\CdekClientV2($this->configs['setaccount'], $this->configs['setsecure']);
            return true;
        } catch (\Throwable $e) {
            return false;
        }

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
            $hashed_values = array($array['name_city']);
            foreach ($array['packages'] as $package) {
                $hashed_values[] = $package['weight'];
            }
            $hashed_values[] = $array['type_pvz'] === "POSTAMAT" ? 'postamat' : 'pickup';
            $hash_string = md5(implode('', $hashed_values));

            $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
            if ($cache->initCache(3600, $this->cdek_cache_id)) { // проверяем кеш и задаём настройки
                $cached_vars = $cache->getVars();
                if (!empty($cached_vars)) {
                    foreach ($cached_vars as $varKey => $var) {
                        if($varKey === $hash_string) {
                            return $var;
                        }
                    }
                }
            }

            $tariffPriority = \HelperAllDeliveries::getSdekTarifList(array(
                'type' => $array['type_pvz'] === "POSTAMAT" ? 'postamat' : 'pickup', 'answer' => 'array'));
            $location_to = $this->getSDEKCityCode($array['name_city']);
            $location_to = \AntistressStore\CdekSDK2\Entity\Requests\Location::withCode($location_to);
            $location_to->setAddress($array['to']);
            $location_from = \AntistressStore\CdekSDK2\Entity\Requests\Location::withCode($this->configs['from']);
            $tariff = (new \AntistressStore\CdekSDK2\Entity\Requests\Tariff())
                ->setFromLocation($location_from)
                ->setToLocation($location_to);

            foreach ($array['packages'] as $package) {
                $packageObj = new \AntistressStore\CdekSDK2\Entity\Requests\Package();
                !empty($package['weight']) ? $packageObj->setWeight($package['weight']) : '';
                $tariff->setPackages($packageObj);
            }

            $calculatedTariffList = $this->cdek_client->calculateTariffList($tariff);

            $calculatedTariffs = [];
            foreach ($calculatedTariffList as $tariff) {
                $calculatedTariffs[$tariff->getTariffCode()] = [
                    "price"           => $tariff->getDeliverySum()
                ];
            }

            foreach ($tariffPriority as $tariffCode) {
                if (array_key_exists($tariffCode, $calculatedTariffs)) {
                    if (!isset($finalPrice)) {
                        $finalPrice = $calculatedTariffs[$tariffCode]['price'];
                    } else if($calculatedTariffs[$tariffCode]['price'] < $finalPrice) {
                        $finalPrice = $calculatedTariffs[$tariffCode]['price'];
                    }
                }
            }

            $cache->forceRewriting(true);
            if ($cache->startDataCache()) {
                $cache->endDataCache((isset($cached_vars) && !empty($cached_vars))
                    ? array_merge($cached_vars, array($hash_string => $finalPrice))
                    : array($hash_string => $finalPrice));
            }
            return $finalPrice;
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            $hashed_values = array($params['location_name']);
            foreach ($params['packages'] as $package) {
                $hashed_values[] = $package['weight'];
            }
            $hashed_values[] = 'courier';
            $hash_string = md5(implode('', $hashed_values));

            $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
            if ($cache->initCache(3600, $this->cdek_cache_id)) { // проверяем кеш и задаём настройки
                $cached_vars = $cache->getVars();
                if (!empty($cached_vars)) {
                    foreach ($cached_vars as $varKey => $var) {
                        if($varKey === $hash_string) {
                            return $var;
                        }
                    }
                }
            }

            $location_to = $this->getSDEKCityCode($params['location_name']);
            $location_to = \AntistressStore\CdekSDK2\Entity\Requests\Location::withCode($location_to);
            $location_to->setAddress($params['address']);
            $location_from = \AntistressStore\CdekSDK2\Entity\Requests\Location::withCode($this->configs['from']);

            $tariffPriority = \HelperAllDeliveries::getSdekTarifList(array('type' => 'courier', 'answer' => 'array'));

            $tariff = (new \AntistressStore\CdekSDK2\Entity\Requests\Tariff())
                ->setFromLocation($location_from)
                ->setToLocation($location_to);

            foreach ($params['packages'] as $package) {
                $packageObj = new \AntistressStore\CdekSDK2\Entity\Requests\Package();
                !empty($package['weight']) ? $packageObj->setWeight($package['weight']) : '';
                $tariff->setPackages($packageObj);
            }

            $calculatedTariffList = $this->cdek_client->calculateTariffList($tariff);

            $calculatedTariffs = [];
            foreach ($calculatedTariffList as $tariff) {
                $calculatedTariffs[$tariff->getTariffCode()] = [
                    "price"           => $tariff->getDeliverySum()
                ];
            }

            foreach ($tariffPriority as $tariffCode) {
                if (array_key_exists($tariffCode, $calculatedTariffs)) {
                    if (!isset($finalPrice)) {
                        $finalPrice = $calculatedTariffs[$tariffCode]['price'];
                    } else if($calculatedTariffs[$tariffCode]['price'] < $finalPrice) {
                        $finalPrice = $calculatedTariffs[$tariffCode]['price'];
                    }
                }
            }

            $cache->forceRewriting(true);
            if ($cache->startDataCache()) {
                $cache->endDataCache((isset($cached_vars) && !empty($cached_vars))
                    ? array_merge($cached_vars, array($hash_string => $finalPrice))
                    : array($hash_string => $finalPrice));
            }
            return $finalPrice;
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}