<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SDEKDelivery extends CommonPVZ
{
    static $MODULE_ID = 'enterego.pvz';
    public string $delivery_name = 'SDEK';
    private $cdek_cache_id = 'sdek_delivery_prices';
    static $sdek_tarifs = array(136,137,138,139,233,234,1,3,5,10,11,12,15,16,17,18,57,58,59,60,61,62,63,483,482,481,480,83,378,376,368,366,363,361,486,485);
    private $cdek_client;

    public static function getInstanceForDoor($deliveryParams): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'SDEK_door_active') === 'Y') {
            return [new SDEKDelivery()];
        }
        return [];
    }

    public static function getInstanceForPvz(): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'SDEK_pvz_active') === 'Y') {
            return [new SDEKDelivery()];
        }
        return [];
    }

    static function getSdekExtraTarifs(){
        IncludeModuleLangFile('bitrix/modules/enterego.pvz/lang/ru/include.php');
        $arTarifs = self::$sdek_tarifs;
        $svdOpts = self::sdekGet('SDEK_tarifs');
        $arReturn = array();
        foreach($arTarifs as $tarifId)
            $arReturn[$tarifId] = array(
                'NAME'  => GetMessage("ENTEREGO_PVZ_SDEK_tarif_".$tarifId."_NAME")." (".$tarifId.")",
                'DESC'  => GetMessage("ENTEREGO_PVZ_SDEK_tarif_".$tarifId."_DESCR"),
                'SHOW'  => (array_key_exists($tarifId, $svdOpts) && array_key_exists('SHOW', $svdOpts[$tarifId]) && $svdOpts[$tarifId]['SHOW']) ? $svdOpts[$tarifId]['SHOW'] : "N",
                'BLOCK' => (array_key_exists($tarifId, $svdOpts) && array_key_exists('BLOCK', $svdOpts[$tarifId]) && $svdOpts[$tarifId]['BLOCK']) ? $svdOpts[$tarifId]['BLOCK']: "N",
            );
        return $arReturn;
    }

    public static function sdekGet($option,$noRemake = true)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID,$option,self::sdekGetDefault($option));

        if($self && $noRemake) {
            $handlingType = self::sdekGetHandling($option);
            switch ($handlingType) {
                case 'serialize' :
                    $self = unserialize($self);
                    break;
                case 'json'      :
                    $self = json_decode($self,true);
                    break;
            }
        }

        return $self;
    }

    public static function sdekGetDefault($option)
    {
        $opt = self::sdekCollection();
        if(array_key_exists($option,$opt))
            return $opt[$option]['default'];
        return false;
    }

    public static function sdekGetHandling($option)
    {
        $opt = self::sdekCollection();
        if(array_key_exists($option,$opt) && array_key_exists('handling',$opt[$option]))
            return $opt[$option]['handling'];
        return false;
    }

    public static function sdekCollection()
    {
        $arOptions = array(
            'SDEK_tarifs' => array(
                'group' => 'addingService',
                'hasHint' => '',
                'default' => 'a:0:{}', // Empty array
                'type' => "special",
                'handling' => 'serialize'
            ),
        );
        return $arOptions;
    }

    static function getSdekTarifList($params=array()){
        $arList = array(
            'pickup'  => array(
                'usual'   => array(234,136,138),
                'heavy'   => array(15,17),
                'express' => array(483,481,62,63,5,10,12)
            ),
            'courier' => array(
                'usual'   => array(233,137,139),
                'heavy'   => array(16,18),
                'express' => array(482,480,11,1,3,61,60,59,58,57,83)
            ),
            'postamat' => array(
                'usual' => array(378,376,368,366),
                'express' => array(363,361,486,485)
            )
        );
        $blocked = self::sdekGet('SDEK_tarifs');
        if($blocked && count($blocked) && (!array_key_exists('fSkipCheckBlocks',$params) || !$params['fSkipCheckBlocks'])){
            foreach($blocked as $key => $val)
                if(!array_key_exists('BLOCK',$val))
                    unset($blocked[$key]);
            if(count($blocked))
                foreach($arList as $tarType => $arTars)
                    foreach($arTars as $tarMode => $arTarIds)
                        foreach($arTarIds as $key => $arTarId)
                            if(array_key_exists($arTarId,$blocked))
                                unset($arList[$tarType][$tarMode][$key]);
        }
        $answer = $arList;
        if($params['type']){
            if(is_numeric($params['type'])) $type = ($params['type']==136)?$type='pickup':$type='courier';
            else $type = $params['type'];
            $answer = $answer[$type];

            if((array_key_exists('mode', $params) && $params['mode']) && array_key_exists($params['mode'], $answer))
                $answer = $answer[$params['mode']];
        }

        if(array_key_exists('answer',$params)){
            $answer = self::sdekArrVals($answer);
            if($params['answer'] == 'string'){
                $answer = implode(',',$answer);
                $answer = substr($answer,0,strlen($answer));
            }
        }
        return $answer;
    }

    static function sdekArrVals($arr){
        $return = array();
        foreach($arr as $key => $val)
            if(is_array($val))
                $return = array_merge($return,self::sdekArrVals($val));
            else
                $return []= $val;
        return $return;
    }

    protected function connect()
    {
        try {
            $this->cdek_client = new \AntistressStore\CdekSDK2\CdekClientV2($this->configs['setaccount'], $this->configs['setsecure']);
            return true;
        } catch (\Throwable $e) {
            return false;
        }

    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions)
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

            $tariffPriority = self::getSdekTarifList(array(
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

            $tariffPriority = self::getSdekTarifList(array('type' => 'courier', 'answer' => 'array'));

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