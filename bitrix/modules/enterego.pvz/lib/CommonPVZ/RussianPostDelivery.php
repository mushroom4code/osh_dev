<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Location\LocationTable;
use \LapayGroup\RussianPost\Providers\OtpravkaApi;
use \LapayGroup\RussianPost\Enum\OpsObjectType;
use OSRM\Exception;

class RussianPostDelivery extends CommonPVZ
{
    public string $delivery_name = 'RussianPost';
    public string $delivery_type = '';
    private string $russian_post_id_postfix = '_delivery_price';

    public function __construct(string $delivery_type = 'RussianPost')
    {
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

    public static function getInstanceForDoor($deliveryParams): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'RussianPost_door_active') === 'Y') {
                return [
                    new RussianPostDelivery('RussianPostEms'),
                    new RussianPostDelivery('RussianPostFirstClass'),
                    new RussianPostDelivery('RussianPostRegular'),
                ];
            }
            return [];
    }

    public static function getInstanceForPVZ(): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'RussianPost_pvz_active') === 'Y') {
                return [
                    new RussianPostDelivery()
                ];
        }
            return [];
    }

    protected function connect()
    {
        return true;
    }

    public function updatePointsForRussianPost()
    {
            try {
                $memory_limit = ini_get('memory_limit');
                $time_limit = ini_get('max_execution_time');
                ini_set('memory_limit', '-1');
                set_time_limit(0);
                $otpravkaConfig = [];
                $otpravkaConfig['auth']['otpravka']['token'] = $this->configs['authtoken'];
                $otpravkaConfig['auth']['otpravka']['key'] = $this->configs['authkey'];

                $otpravkaApi = new OtpravkaApi($otpravkaConfig);
                $pvz_list_zip = $otpravkaApi->getPostOfficeFromPassport(OpsObjectType::ALL);
                $pvz_list_zip->moveTo(dirname(__DIR__).'/russian_post_ops_passport.zip');
                $zip = new \ZipArchive();
                if ($zip->open(dirname(__DIR__).'/russian_post_ops_passport.zip')) {
                    $pvz_list = json_decode($zip->getFromIndex(0), true);
                    $zip->close();
                    unlink(dirname(__DIR__).'/russian_post_ops_passport.zip');
                    RussianPostPointsTable::deleteAll();

                    $res = LocationTable::getList(array(
                        'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
                        'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
                    ));

                    $listLocation = [];
                    while ($arLocation = $res->fetch()) {
                        $listLocation[] = $arLocation;
                    }

                    foreach ($pvz_list['passportElements'] as $ops_id => $ops) {
                        $curLocation = null;
                        foreach ($listLocation as $location) {
                            if ($location['NAME_RU'] === explode(' ', $ops['address']['place'])[1]) {
                                $curLocation = $location;
                                break;
                            }
                        }

                        if ($curLocation===null) {
                            continue;
                        }

                        if (!empty($ops['latitude'] && !empty($ops['longitude']))) {
                            $point = new RussianPostPointsTable();
                            $response = $point->add([
                                'ID' => $ops_id,
                                'BITRIX_CODE' => $curLocation['CODE'],
                                'INDEX' => $ops['address']['index'],
                                'WORK_TIME' => $ops['workTime'] ? implode('; ', $ops['workTime']) : '',
                                'FULL_ADDRESS' => $ops['addressFias']['ads'],
                                'ADDRESS_LAT' => $ops['latitude'],
                                'ADDRESS_LNG' => $ops['longitude'],
                                'IS_PVZ' => $ops['type'] === 'Почтомат' ? 'false' : 'true',
                                'IS_ECOM' => $ops['ecom'] === '1' ? 'true' : 'false'
                            ]);
                        }
                    }
                } else {
                    throw new Exception('zip архив с опс не был открыт');
                }
                ini_set('memory_limit', $memory_limit);
                set_time_limit(intval($time_limit));
            } catch (\Throwable $e) {

            }
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash)
    {
            $arParams = ['filter' => ['BITRIX_CODE' => $code_city]];
            $res = RussianPostPointsTable::getList($arParams);
            while ($point = $res->fetch()) {
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
                    'type' => $point['IS_PVZ'] === 'true' ? 'PVZ' : 'POSTAMAT',
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
                $params = [
                    'weight' => intval($array['weight']),
                    'sumoc' => intval($array['cost'] . '00'),
                    'from' => $this->configs['fromzip'],
                    'to' => $array['postindex']

                ];

                $hashed_values = array($params['weight'], $params['sumoc'], $params['from'], $params['to'], 'pickup');
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

                $TariffCalculation = new \LapayGroup\RussianPost\TariffCalculation();

                if ($array['type_pvz'] === 'POSTAMAT') {
//                    $objectId = 54020;
                    $objectId = 23090;
                    $params['group'] = 0;
                    $calcInfo = $TariffCalculation->calculate($objectId, $params);
                    $finalPrice = $calcInfo->getGroundNds();
                } else {
                    $objectId = 4020;
                    $calcInfo = $TariffCalculation->calculate($objectId, $params);
                    $finalPrice = $calcInfo->getGroundNds();
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
                if (!empty($params['fias'])) {
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
                        'sumoc' => intval($params['shipment_cost'].'00'),
                        'from' => $this->configs['fromzip'],
                        'to' => $params['zip_to']
                    ];

                    $hashed_values = array($params['weight'], $params['sumoc'], $params['from'], $params['to'], 'courier');
                    $hash_string = md5(implode('', $hashed_values));

                    $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
                    if ($cache->initCache(3600, $this->delivery_type . $this->russian_post_id_postfix)) { // проверяем кеш и задаём настройки
                        $cached_vars = $cache->getVars();
                        if (!empty($cached_vars)) {
                            foreach ($cached_vars as $varKey => $var) {
                                if ($varKey === $hash_string) {
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
