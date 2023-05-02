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
            $res = LocationTable::getList(array(
                'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
                'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
            ));

            $listLocation = [];
            while ($arLocation = $res->fetch()) {
                $listLocation[] = $arLocation;
            }

            FivePostPointsTable::deleteAll();

            $totalPages = 1;
            for ($i = 0; $i < $totalPages; $i++) {
                $result = $this->fivepost_client->getPvzList($i, 2000);
                foreach ($result['content'] as $point) {
                    $curLocation = null;
                    foreach ($listLocation as $location) {
                        if ($location['NAME_RU'] === explode(' ', $point['address']['city'])[0]) {
                            $curLocation = $location;
                            break;
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
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions)
    {
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