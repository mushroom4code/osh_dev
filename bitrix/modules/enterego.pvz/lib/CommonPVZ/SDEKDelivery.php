<?php

namespace CommonPVZ;

class SDEKDelivery extends CommonPVZ
{
    public string $delivery_name = 'SDEK';
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
            $location_to = $this->getSDEKCityCode($array['name_city']);
            $location_to = \AntistressStore\CdekSDK2\Entity\Requests\Location::withCode($location_to);
            $location_to->setAddress($array['to']);
            $location_from = \AntistressStore\CdekSDK2\Entity\Requests\Location::withCode($this->configs['from']);
            $tariff = (new \AntistressStore\CdekSDK2\Entity\Requests\Tariff())
                ->setTariffCode($this->configs['tariff'])
                ->setFromLocation($location_from)
                ->setToLocation($location_to)
                ->setPackageWeight($array['weight'])
                ->addServices(['PART_DELIV']);

            $calculatedTariff = $this->cdek_client->calculateTariff($tariff);
            $delivery_price = $calculatedTariff->getTotalSum();
            return $delivery_price;
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            $location_to = $this->getSDEKCityCode($params['location_name']);
            $tariffPriority = array(137,482,420);

            $tariff = (new \AntistressStore\CdekSDK2\Entity\Requests\Tariff())
                ->setCityCodes($this->configs['from'], $location_to)
                ->addServices(['PART_DELIV']);

            foreach ($params['packages'] as $package) {
                $packageObj = new \AntistressStore\CdekSDK2\Entity\Requests\Package();
                !empty($package['height']) ? $packageObj->setHeight($package['height']) : '';
                !empty($package['length']) ? $packageObj->setLength($package['length']) : '';
                !empty($package['width']) ? $packageObj->setWidth($package['width']) : '';
                !empty($package['weight']) ? $packageObj->setWeight($package['weight']) : '';
                $tariff->setPackages($packageObj);
            }

            $calculatedTariffList = $this->cdek_client->calculateTariffList($tariff);

            $calculatedTariffs = [];
            foreach ($calculatedTariffList as $tariff) {
                $calculatedTariffs[$tariff->getTariffCode()] = [
                    "price"           => $tariff->getDeliverySum(),
                    "termMin"         => $tariff->getPeriodMin(),
                    "termMax"         => $tariff->getPeriodMax(),
                    "tarif"           => $tariff->getTariffCode()
                ];
            }

            foreach ($tariffPriority as $tariffCode) {
                if (array_key_exists($tariffCode, $calculatedTariffs)) {
                    $arReturn = $calculatedTariffs[$tariffCode];
                    break;
                }
            }

            return $arReturn['price'];
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}