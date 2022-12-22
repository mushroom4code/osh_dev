<?php

namespace CommonPVZ;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\LocationTable;

Loc::loadMessages(__FILE__);

class DeliveryHelper
{
    protected $arDeliveries = array(
            'pickpoint'
    );
    public static function getButton()
    {
        ob_start();
        ?>
        <style>
            .btn_pvz {
                display: block;
                margin-top: 8px;
                max-width: 320px
            }
        </style>
        <a class="btn btn_basket btn_pvz"
           onclick="BX.SaleCommonPVZ.openMap(); return false;">
            <?= GetMessage('COMMONPVZ_BTN_CHOOSE') ?>
        </a>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public static function getDeliveryID($arDeliveries)
    {
        foreach ($arDeliveries as $id => $del) {
            if ($del['NAME'] === GetMessage('COMMONPVZ_TITLE')) {
                return $id;
            }
        }
        return 0;
    }

    public static function mainRequest($action, $locationCode)
    {
        $result = [];
        if ($action === 'refreshOrderAjax') {
            $result['price'] = 0;
            $city = LocationTable::getByCode(
                $locationCode,
                [
                    'filter' => array('=TYPE.ID' => '5', '=NAME.LANGUAGE_ID' => LANGUAGE_ID),
                    'select' => ['ID', 'LOCATION_NAME' => 'NAME.NAME']
                ]
            )->fetch();
            $city_name = $city['LOCATION_NAME'];
            self::getAllPVZ($city_name);


        }
        return $result;

    }

    private static function getAllPVZ($city_name) {

    }

    function getSdekInfo($city_name)
    {
        // СДЭК
        global $result_array;
        try {
            $client = new Psr18Client();
            $cdek = new Client($client);
            $cdek->setAccount('Qzidnsik5xW7cyJ86Vu89zwshwTq6iQF');
            $cdek->setSecure('eyQN5S9Kvn5xcJWIHjWQFMGxVH6FfMbZ');

            $result = $cdek->cities()->getFiltered(['country_codes' => 'RU', 'city' => $city_name]);
            if ($result->isOk()) {
                $cities = $cdek->formatResponseList($result, \CdekSDK2\Dto\CityList::class);
            }

            if ($_POST['delivery']) {
                if ($_POST['delivery'] === 'СДЭК') {
                    $sdekk = new Enterego\EnteregoDeliveries;
                    $sdekk::auth();
                    $sdek_price = $sdekk::getSDEKPrice($_POST['weight'], $cities->items[0]->code, $_POST['to']);
                    $sdekk::setDelPrice($sdek_price->total_sum);
                    $pricePVZ = json_encode(round($sdek_price->total_sum));
                    exit($pricePVZ);
                }
            } else {
                $result = $cdek->offices()->getFiltered(['city_code' => $cities->items[0]->code]);
                if ($result->isOk()) {
                    $sdek_result = $cdek->formatResponseList($result, \CdekSDK2\Dto\PickupPointList::class);
                }
                $EEiconContent = addslashes("<div 
                            style='
                            position: absolute;
                            top: 0;
                            left: 50%;
                            padding: 3px;
                            background-color: #fff;'>СДЭК</div>");
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

                    /*$result = json_encode($features_obj);
                    $queryStr = "INSERT INTO enterego_all_pvz (`CITY_CODE`,`CITY_NAME`,`SDEK`) VALUES ($code_city,'" . $city_name . "','" . $result . "');";
                    $connection->queryExecute($queryStr);*/

                    $result_array['features'][] = $features_obj;
                }
            }
        } catch (\Exception $e) {
            $result_array['Errors'][] = $e->getMessage();
        }
    }

    private static function getPickPointInfo($city_name)
    {
        // pickPoint
        try {

            $config = [
                'host' => 'https://e-solution.pickpoint.ru/api/',
                'login' => 'hYdz3J',
                'password' => '6jUzhQ7iwfgj0',
                'ikn' => '9990000112',
            ];

            $pickPointConf = new PickPointConf($config['host'], $config['login'], $config['password'], $config['ikn']);
            $defaultPackageSize = new PackageSize(20, 20, 20); // может быть null
            $senderDestination = new SenderDestination('Москва', 'Московская обл.'); // Адрес отправителя
            $client = new PickPointConnector($pickPointConf, $senderDestination, $defaultPackageSize);
            if ($_POST['delivery']) {
                if ($_POST['delivery'] === 'PickPoint') {
                    $receiverDestination = new ReceiverDestination($city_name, $_POST['hubregion']);
                    $tariffPrice = $client->calculateObjectedPrices($receiverDestination); // Вернет объект с ценами
                    $commonStandardPrice = $tariffPrice->getStandardCommonPrice(); // получить общую цену с тарифом стандарт
                    $pricePVZ = json_encode(round($commonStandardPrice));
                    exit($pricePVZ);
                }
            } else {
                $pickPoint_result = $client->getPoints();

                foreach ($pickPoint_result as $key => $value) {
                    if ($value['HubCity'] === $city_name) {
                        $features_obj['type'] = 'Feature';
                        $features_obj['id'] = $id_feature;
                        $id_feature += 1;
                        $features_obj['geometry'] = [
                            'type' => 'Point',
                            'coordinates' => [
                                $value['Latitude'],
                                $value['Longitude']
                            ]
                        ];
                        $features_obj['properties'] = [
                            'code_pvz' => $value['Number'],
                            'fullAddress' => $value['HubCity'] . ', ' . $value['Address'],
                            'deliveryName' => 'PickPoint',
                            'iconContent' => 'PickPoint',
                            'hintContent' => $value['Address'],
                            "hubregion" => $value['HubRegion']
                        ];
                        $features_obj['options'] = [
                            'preset' => 'islands#darkOrangeIcon'
                        ];

                        $result_array['features'][] = $features_obj;
                    }
                }
            }
        } catch (\Exception $e) {
            $result_array['Errors'][] = $e->getMessage();
        }
    }





}