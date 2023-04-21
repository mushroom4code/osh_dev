<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Location\LocationTable;
use \LapayGroup\RussianPost\Providers\OtpravkaApi;
use \LapayGroup\RussianPost\ParcelInfo;

class RussianPostDelivery extends CommonPVZ
{
    public string $delivery_name = 'RussianPost';
    public string $delivery_type = '';
    private string $russian_post_id_postfix = '_delivery_price';

    public function __construct(string $delivery_type = 'RussianPost') {
        parent::__construct();
        $this->delivery_type = $delivery_type;
        switch ($delivery_type) {
            case 'RussianPost':
                $this->delivery_name = 'Почта России';
                break;
            case 'RussianPostEms':
                $this->delivery_name = 'Почта России (EMS)';
                break;
            case 'RussianPostFirstClass':
                $this->delivery_name = 'Почта России (Посылка 1 класса)';
                break;
            case 'RussianPostRegular':
                $this->delivery_name = 'Почта России (Посылка обычная)';
                break;
        }
    }

    public static function getDeliveryStatus($isPvz = false) {
        $opt = Option::get(DeliveryHelper::$MODULE_ID, 'RussianPost_active');
        if ($isPvz)
            return array('RussianPost' => $opt);
        else
            return array('RussianPostEms' => $opt, 'RussianPostFirstClass' => $opt, 'RussianPostRegular' => $opt);
    }

    public static function getInstance($deliveryParams): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'RussianPost_active') === 'Y') {
            return [
                new RussianPostDelivery('RussianPostEms'),
                new RussianPostDelivery('RussianPostFirstClass'),
                new RussianPostDelivery('RussianPostRegular'),
            ];
        }
        return [];
    }

    protected function connect()
    {
        return true;
    }

    public function updatePointsForRussianPost() {
        $otpravkaConfig = [];
        $otpravkaConfig['auth']['otpravka']['token'] = $this->configs['authtoken'];
        $otpravkaConfig['auth']['otpravka']['key'] = $this->configs['authkey'];

        $otpravkaApi = new OtpravkaApi($otpravkaConfig);
        $pvz_list = $otpravkaApi->getPvzList();

        RussianPostPointsTable::deleteAll();

        $res = LocationTable::getList(array(
            'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
            'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
        ));
        $listLocation = [];
        while ($arLocation = $res->fetch()) {
            $listLocation[] = $arLocation;
        }
        foreach ($pvz_list as $value) {
            $curLocation = null;
            foreach ($listLocation as $location) {
                if ('г '.$location['NAME_RU'] === $value['address']['place']) {
                    $curLocation = $location;
                    break;
                }
            }

            if ($curLocation===null) {
                continue;
            }

            $point = new RussianPostPointsTable();

            $response = $point->add([
                'ID' => $value['id'],
                'BITRIX_CODE' => $curLocation['CODE'],
                'CODE' => $value['id'],
                'INDEX' => $value['delivery-point-index'],
                'PHONE_NUMBER' => $value['phone'] ?? '',
                'WORK_TIME' => $value['work-time'] ? implode(' ', $value['work-time']) : '',
                'COMMENT' => $value['getto'],
                'FULL_ADDRESS' => $value['address']['place'].', '.$value['address']['street'].', '.$value['address']['house'],
                'ADDRESS_LAT' => $value['latitude'],
                'ADDRESS_LNG' => $value['longitude'],
                'IS_PVZ' => $value['delivery-point-type'] === 'DELIVERY_POINT' ? 'true' : 'false'
            ]);
        }
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city)
    {
        $arParams = ['filter'=>['BITRIX_CODE'=>$code_city]];
        $res = RussianPostPointsTable::getList($arParams);
        while ($point = $res->fetch()){
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $point['ADDRESS_LAT'],
                    $point['ADDRESS_LNG'],
                ]
            ];
            $features_obj['properties'] = [
                'code_pvz' => $point['CODE'],
                'type' => $point['IS_PVZ'] == 'true' ? 'PVZ' : 'POSTAMAT',
                'fullAddress' => $point['FULL_ADDRESS'],
                'phone' => $point['PHONE_NUMBER'],
                'workTime' => $point['WORK_TIME'],
                'comment' => $point['COMMENT'],
                'postindex' => $point['INDEX'],
                'deliveryName' => 'Почта России',
                'iconCaption' => 'Почта России',
                'hintContent' => $point['FULL_ADDRESS'],
                "openEmptyBalloon" => true,
                "clusterCaption" => 'Почта России',
            ];
            $features_obj['options'] = [
                'preset' => 'islands#darkBlueIcon'
            ];

            $result_array[] = $features_obj;
        }
    }

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    public function getPrice($array)
    {
        try {
                $objectId = 4020;

                $params = [
                    'weight' => $array['weight'],
                    'sumoc' => intval($array['cost']),
                    'from' => $this->configs['fromzip'],
                    'to' => $array['postindex']
                ];

                $hashed_values = array($params['weight'], $params['sumoc'], $params['from'], $params['to']);
                $hash_string = md5(implode('', $hashed_values));

//                $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
//                if ($cache->initCache(3600, $this->delivery_type.$this->russian_post_id_postfix)) { // проверяем кеш и задаём настройки
//                    $cached_vars = $cache->getVars();
//                    if (!empty($cached_vars)) {
//                        foreach ($cached_vars as $varKey => $var) {
//                            if($varKey === $hash_string) {
//                                return $var;
//                            }
//                        }
//                    }
//                }

                $TariffCalculation = new \LapayGroup\RussianPost\TariffCalculation();
                $calcInfo = $TariffCalculation->calculate($objectId, $params);
                $finalPrice = $calcInfo->getGroundNds();

//                $cache->forceRewriting(true);
//                if ($cache->startDataCache()) {
//                    $cache->endDataCache((isset($cached_vars) && !empty($cached_vars))
//                        ? array_merge($cached_vars, array($hash_string => $finalPrice))
//                        : array($hash_string => $finalPrice));
//                }

                return $finalPrice;

        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            if(!empty($params['fias'])){
                $objectId = false;
                switch ($this->delivery_type) {
                    case 'RussianPostEms':
                        $objectId = 7020;
                        break;
                    case 'RussianPostFirstClass':
                        $objectId = 47020;
                        break;
                    case 'RussianPostRegular':
                        $objectId = 4020;
                        break;
                }

                $params = [
                    'weight' => $params['shipment_weight'],
                    'sumoc' => intval($params['shipment_cost']),
                    'from' => $this->configs['fromzip'],
                    'to' => $params['zip_to']
                ];

                $hashed_values = array($params['weight'], $params['sumoc'], $params['from'], $params['to']);
                $hash_string = md5(implode('', $hashed_values));

                $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
                if ($cache->initCache(3600, $this->delivery_type.$this->russian_post_id_postfix)) { // проверяем кеш и задаём настройки
                    $cached_vars = $cache->getVars();
                    if (!empty($cached_vars)) {
                        foreach ($cached_vars as $varKey => $var) {
                            if($varKey === $hash_string) {
                                return $var;
                            }
                        }
                    }
                }

                if ($this->delivery_type === 'RussianPostEms') {
                    $params['group'] = 0;
                }

                $TariffCalculation = new \LapayGroup\RussianPost\TariffCalculation();
                $calcInfo = $TariffCalculation->calculate($objectId, $params);
                $finalPrice = $calcInfo->getGroundNds();

                $cache->forceRewriting(true);
                if ($cache->startDataCache()) {
                    $cache->endDataCache((isset($cached_vars) && !empty($cached_vars))
                        ? array_merge($cached_vars, array($hash_string => $finalPrice))
                        : array($hash_string => $finalPrice));
                }

                return $finalPrice;
            } else {
                $this->errors[] = 'empty fias';
                return array('errors' => $this->errors);
            }
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}