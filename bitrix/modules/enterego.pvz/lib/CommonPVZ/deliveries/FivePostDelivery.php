<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Location\LocationTable;
use LapayGroup\FivePostSdk\Client;
use function Sodium\add;

class FivePostDelivery extends CommonPVZ
{

    public string $delivery_name = 'FivePost';
    private $fivepost_cache_id = 'fivepost_delivery_prices';
    private $fivepost_client;

    public static function getInstanceForDoor($deliveryParams): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'FivePost_door_active') === 'Y') {
            return [new FivePostDelivery()];
        }
        return [];
    }

    public static function getInstanceForPvz(): array
    {
        if (Option::get(DeliveryHelper::$MODULE_ID, 'FivePost_pvz_active') === 'Y') {
            return [new FivePostDelivery()];
        }
        return [];
    }

    protected function connect()
    {
        try {
            $this->fivepost_client = new Client($this->configs['apikey'], 60, \LapayGroup\FivePostSdk\Client::API_URI_PROD);
            $jwt = $this->fivepost_client->getJwt();
            $this->fivepost_client->setJwt($jwt);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function preparePointWorkHours($workHours)
    {
        $days = array_flip(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']);
        $result = [];

        foreach ($workHours as $day)
        {
            $tmpO = explode(':', $day['opensAt']);
            unset($tmpO[2]);

            $tmpC = explode(':', $day['closesAt']);
            unset($tmpC[2]);

            $result[$days[$day['day']]] = ['O' => implode(':', $tmpO), 'C' => implode(':', $tmpC)];
        }

        // Cause da order of days in API response cannot be trusted
        ksort($result, SORT_NUMERIC);

        return $result;
    }

    public function updatePointsForFivePost() {
        try {
            $memory_limit = ini_get('memory_limit');
            $time_limit = ini_get('max_execution_time');
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $resCity = LocationTable::getList(array(
                'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
                'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
            ));
            $resVillage = LocationTable::getList(array(
                'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    '=PARENT.PARENT.NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '6'),
                'select' => array('*', 'NAME_RU' => 'NAME.NAME',
                    'PARENT_PARENT_NAME_RU' => 'PARENT.PARENT.NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
            ));

            $listCityLocation = [];
            $listVillageLocation = [];
            while ($arLocation = $resCity->fetch()) {
                $listCityLocation[] = $arLocation;
            }
            while ($arLocation = $resVillage->fetch()) {
                $listVillageLocation[] = $arLocation;
            }

            FivePostPointsTable::deleteAll();

            $totalPages = 1;
            for ($i = 0; $i < $totalPages; $i++) {
                $result = $this->fivepost_client->getPvzList($i, 2000);
                foreach ($result['content'] as $point) {
                    $curLocation = null;
                    $nameLocationPointWithoutPostfix = substr($point['address']['city'], 0, strrpos($point['address']['city'], " "));
                    $nameRegionPointWithoutPostfix = substr($point['address']['region'], 0, strrpos($point['address']['region'], " "));
                    if ($point['address']['cityType'] == 'г') {
                        foreach ($listCityLocation as $location) {
                            if ($location['NAME_RU'] === $nameLocationPointWithoutPostfix) {
                                $curLocation = $location;
                                break;
                            }
                        }
                    } else {
                        foreach ($listVillageLocation as $location) {
                            $nameLocationWithoutPostfix = substr($location['NAME_RU'], 0, strrpos($location['NAME_RU'], " "));
                            $nameRegionWithoutPrefixPostfix = explode(' ', $location['PARENT_PARENT_NAME_RU']);
                            if ($nameRegionWithoutPrefixPostfix[array_key_last($nameRegionWithoutPrefixPostfix)] === 'область'
                                || $nameRegionWithoutPrefixPostfix[array_key_last($nameRegionWithoutPrefixPostfix)] === 'край')
                                array_pop($nameRegionWithoutPrefixPostfix);
                            else
                                array_shift($nameRegionWithoutPrefixPostfix);
                            $nameRegionWithoutPrefixPostfix = implode($nameRegionWithoutPrefixPostfix);
                            if ($nameLocationWithoutPostfix == $nameLocationPointWithoutPostfix
                                && $nameRegionWithoutPrefixPostfix == $nameRegionPointWithoutPostfix) {
                                $curLocation = $location;
                                break;
                            }
                        }
                    }

                    if ($curLocation === null) {
                        continue;
                    }

                    $pointsTable = new FivePostPointsTable();
                    $pointsTable->add([
                        'POINT_GUID' => $point['id'],
                        'BITRIX_CODE' => $curLocation['CODE'],
                        'NAME' => $point['name'],
                        'PARTNER_NAME' => $point['partnerName'],
                        'TYPE' => $point['type'],
                        'ADDITIONAL' => $point['additional'],
                        'WORK_HOURS' => serialize(self::preparePointWorkHours($point['workHours'])), // Drop unused info
                        'FULL_ADDRESS' => $point['fullAddress'],
                        'ADDRESS_COUNTRY' => $point['address']['country'],
                        'ADDRESS_ZIP_CODE' => $point['address']['zipCode'],
                        'ADDRESS_REGION' => $point['address']['region'],
                        'ADDRESS_REGION_TYPE' => $point['address']['regionType'],
                        'ADDRESS_CITY' => $point['address']['city'],
                        'ADDRESS_CITY_TYPE' => $point['address']['cityType'],
                        'ADDRESS_STREET' => $point['address']['street'],
                        'ADDRESS_HOUSE' => $point['address']['house'],
                        'ADDRESS_BUILDING' => $point['address']['building'],
                        'ADDRESS_LAT' => $point['address']['lat'],
                        'ADDRESS_LNG' => $point['address']['lng'],
                        'ADDRESS_METRO_STATION' => $point['address']['metroStation'],
                        'LOCALITY_FIAS_CODE' => $point['localityFiasCode'],
                        'MAX_CELL_WIDTH' => (int)$point['cellLimits']['maxCellWidth'],     // mm
                        'MAX_CELL_HEIGHT' => (int)$point['cellLimits']['maxCellHeight'],    // mm
                        'MAX_CELL_LENGTH' => (int)$point['cellLimits']['maxCellLength'],    // mm
                        'MAX_CELL_WEIGHT' => (int)($point['cellLimits']['maxWeight'] / 1000), // g
                        'RETURN_ALLOWED' => ($point['returnAllowed'] ? 'Y' : 'N'),
                        'PHONE' => $point['phone'],
                        'CASH_ALLOWED' => ($point['cashAllowed'] ? 'Y' : 'N'),
                        'CARD_ALLOWED' => ($point['cardAllowed'] ? 'Y' : 'N'),
                        'LOYALTY_ALLOWED' => ($point['loyaltyAllowed'] ? 'Y' : 'N'),
                        'EXT_STATUS' => $point['extStatus'],
                        'DELIVERY_SL' => serialize($point['deliverySL']),
                        'LASTMILEWAREHOUSE_ID' => $point['lastMileWarehouse']['id'],
                        'LASTMILEWAREHOUSE_NAME' => $point['lastMileWarehouse']['name'],
                        'MAX_CELL_DIMENSIONS_HASH' => DeliveryHelper::makeDimensionsHash(
                            (int)$point['cellLimits']['maxCellWidth'],
                            (int)$point['cellLimits']['maxCellHeight'],
                            (int)$point['cellLimits']['maxCellLength']),
                        'RATE' => serialize($point['rate'])
                    ]);
                }

                if (!empty($result['totalPages']))
                    $totalPages = $result['totalPages'];// Заносим количество страниц из ответа

            }
            ini_set('memory_limit', $memory_limit);
            set_time_limit(intval($time_limit));
        } catch (\Throwable $e) {
            ini_set('memory_limit', $memory_limit);
            set_time_limit(intval($time_limit));
            return $e->getMessage();
        }
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions)
    {
        try {
            $arParams = ['filter' => ['BITRIX_CODE' => $code_city]];
            $res = FivePostPointsTable::getList($arParams);

            while ($point = $res->fetch()) {
                if ($point['MAX_CELL_DIMENSIONS_HASH'] >= $dimensionsHash
                    && !empty(unserialize($point['RATE'])[0]['rateValue'])
                    && !empty(unserialize($point['RATE'])[0]['zone'])){
                    $features_obj['type'] = 'Feature';
                    $features_obj['id'] = $id_feature;
                    $id_feature += 1;
                    $features_obj['geometry'] = [
                        'type' => 'Point',
                        'coordinates' => [
                            $point['ADDRESS_LAT'],
                            $point['ADDRESS_LNG']
                        ]
                    ];
                    if (!strripos($point['ADDRESS_REGION'], ' город')) {
                        $region = ', ' . $point['ADDRESS_REGION'];
                    }
                    $features_obj['properties'] = [
                        'code_pvz' => $point['POINT_GUID'],
                        'type' => $point['TYPE'] === 'POSTAMAT' ? 'POSTAMAT' : 'PVZ',
                        'fullAddress' => $point['FULL_ADDRESS'],
                        'comment' => $point['ADDITIONAL'],
                        'deliveryName' => '5Post',
                        'fivepostRate' => $point['RATE'],
                        'fivepostDeliverySl' => $point['DELIVERY_SL'],
                        'fivepostMaxWeight' => $point['MAX_CELL_WEIGHT'],
                        'iconCaption' => '5Post',
                        'hintContent' => $point['FULL_ADDRESS'],
                        'openEmptyBalloon' => true,
                        'clusterCaption' => '5Post',
                    ];
                    $features_obj['options'] = [
                        'preset' => 'islands#grayIcon'
                    ];

                    $result_array[] = $features_obj;
                }
            }
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }

    public function getPrice($array)
    {
        try {
            $rate = unserialize($array['fivepost_rate'])[0];
            if ($array['weight'] > $array['fivepost_max_weight']) {
                $price = $rate['rateValue']  + ((($array['weight'] - $array['fivepost_max_weight']) / 1000) * $rate['rateExtraValue']);
                if ($rate['vat']) {
                    $finalPrice = $price / 100 * $rate['vat'] + $price;
                } else {
                    $finalPrice = $price;
                }
            } else {
                $finalPrice = $rate['rateValueWithVat'];
            }
            return $finalPrice;
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }

    public function getPriceDoorDelivery($params)
    {
        // TODO: Implement getPriceDoorDelivery() method.
        $this->errors[] = 'no pricedoordelivery implementation';
        return array('errors' => $this->errors);
    }


}