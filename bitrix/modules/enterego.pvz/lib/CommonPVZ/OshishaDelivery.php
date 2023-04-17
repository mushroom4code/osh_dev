<?php

namespace CommonPVZ;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class OshishaDelivery extends CommonPVZ
{
    public string $delivery_name = 'Oshisha';

    protected function connect()
    {

    }

    /**
     * @param string $city_name
     * @param array $result_array
     * @param array $id_feature
     * @param string $code_city
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city)
    {
        $value = [];

        $params = ['filter' => ['ACTIVE'=>'Y', 'ISSUING_CENTER' => 'Y', '!=GPS_N'=>'0', '!=GPS_S'=>'0'] ];
        $rsRes = StoreTable::getList($params);

        if ($code_city!=='0000073738') {
            return;
        }

        while ($arStore = $rsRes->fetch()) {
            $features_obj = [];
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $arStore['GPS_N'],
                    $arStore['GPS_S']
                ]
            ];

            $features_obj['properties'] = [
                'code_pvz' => $arStore['ID'],
                'type' => 'PVZ',
                'fullAddress' => $arStore['ADDRESS'],
                'comment' => $arStore['DESCRIPTION'],
                'deliveryName' => 'OSHISHA',
                'iconCaption' => 'OSHISHA',
                'hintContent' => $arStore['ADDRESS'],
                'openEmptyBalloon' => true,
                'clusterCaption' => 'OSHISHA',

            ];
            $features_obj['options'] = [
                'iconImageSize' => [64, 64],
                'iconImageOffset' => [-30, -60],
                'iconLayout'=> 'default#imageWithContent',
                'iconImageHref'=> '/bitrix/modules/enterego.pvz/assets/images/osh.png',
            ];

            $result_array[] = $features_obj;
        }
    }

    public function getPrice($array)
    {
        return 0;
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            $cost = \HelperAllDeliveries::getOshishaCost();
            $startCost = \HelperAllDeliveries::getOshishaStartCost();
            $distance = ceil(($_SESSION['Osh']['delivery_address_info']['distance'] ?? 0) - 0.8);
            $noMarkup = $_SESSION['Osh']['delivery_address_info']['no_markup'];

            $limitBasket = \HelperAllDeliveries::getOshishaLimitBasket();
            if (intval($params['shipment_cost']) >= $limitBasket && $noMarkup === 'false') {
                $delivery_price = max($distance - 5, 0) * $cost;
            } else {
                if ($noMarkup === 'true') {
                    $delivery_price = $startCost;
                } else {
                    $delivery_price = $startCost + $distance * $cost;
                }
            }
            return $delivery_price;
        } catch(\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}