<?php

namespace CommonPVZ;

use Bitrix\Main\Data\Cache;
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

    public static function mainRequest($req, $locationCode)
    {
        $result = [];
        $result['price'] = 0;

        if ($req['soa-action'] === 'refreshOrderAjax') {
            $city = LocationTable::getByCode(
                $locationCode,
                [
                    'filter' => array('=TYPE.ID' => '5', '=NAME.LANGUAGE_ID' => LANGUAGE_ID),
                    'select' => ['ID', 'LOCATION_NAME' => 'NAME.NAME']
                ]
            )->fetch();
            $city_name = $city['LOCATION_NAME'];

            $result = self::getAllPVZ($city_name);
        } elseif ($req['soa-action'] === 'getPVZPrice') {

        }

        return $result;

    }

    public static function getAllPVZ($city_name)
    {
        $id_feature = 0;
        $result_array = [];
        $cache = Cache::createInstance();

        if ($cache->initCache(7200, "all_pvz")) {
            $result_array = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            PickPointDelivery::getPVZ($city_name, $result_array, $id_feature);

            $cache->endDataCache($result_array);
        }

        return $result_array;
    }

}