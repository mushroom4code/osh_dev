<?php

namespace CommonPVZ;

use Bitrix\Main\Data\Cache;
use Bitrix\Sale\Location\LocationTable;

require_once 'PickPointDelivery.php';

class DeliveryHelper
{
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

    public static function getCityName($locationCode)
    {
        $city = LocationTable::getByCode(
            $locationCode,
            [
                'filter' => array('=TYPE.ID' => '5', '=NAME.LANGUAGE_ID' => LANGUAGE_ID),
                'select' => ['ID', 'LOCATION_NAME' => 'NAME.NAME']
            ]
        )->fetch();

        return $city['LOCATION_NAME'];
    }

    public static function getAllPVZ($city_name)
    {
        $id_feature = 0;
        $result_array = [];
        $points_Array = [];
        $cache = Cache::createInstance();
        $cachePath = '/getAllPVZPoints';

        if ($cache->initCache(7200, 'pvz_' . $city_name, $cachePath)) {
            $points_Array = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $delivery = new PickPointDelivery();
            $delivery->getPVZ($city_name, $points_Array, $id_feature);

            $cache->endDataCache($points_Array);
        }

        $result_array['type'] = 'FeatureCollection';
        $result_array['features'] = $points_Array;

        return $result_array;
    }

    public static function getPrice($req_data)
    {
        if ($req_data['delivery'] === 'PickPoint') {
            $delivery = new PickPointDelivery();
            return $delivery->getPrice($req_data);
        }
    }


    // TODO
    public static function refreshJSComponent()
    {

    }

}