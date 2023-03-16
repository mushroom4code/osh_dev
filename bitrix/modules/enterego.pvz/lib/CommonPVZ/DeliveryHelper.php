<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache,
    Bitrix\Main\Page\Asset;
use Bitrix\Sale\Location\LocationTable;
use \Bitrix\Main\Localization\Loc;
use CUtil;

Loc::loadMessages(__FILE__);

class DeliveryHelper
{
    public static $MODULE_ID = 'enterego.pvz';

    public static function getConfigs()
    {
        $arConfgs = Option::getForModule(self::$MODULE_ID, SITE_ID);
        $CONFIG_DELIVERIES = [];
        foreach ($arConfgs as $k => $v) {
            $arDel = explode('_', $k);
            $CONFIG_DELIVERIES[$arDel[0]][$arDel[1]] = $v;
        }
        return $CONFIG_DELIVERIES;
    }


    public static function getButton($address = '')
    {
        $content = "<a class='btn btn_basket btn_pvz btn-default'
           onclick='BX.SaleCommonPVZ.openMap(); return false;'>";
        $content .= Loc::getMessage('COMMONPVZ_BTN_CHOOSE');
        $content .= " </a>";
        $content .= "<span id='pvz_address'></span>";

        return $content;
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

    /** Обновляет ПВЗ для службы доставки PickPoint
     * @return string[]
     */
    public static function updatePickPointPVZ(): array
    {
        try {
            $pickPoint = new PickPointDelivery();
            $pickPoint->updatePointsForPickPoint();
        } catch (\Exception $e) {
            return ['status'=>'failed'];
        }
        return ['status'=>'success'];
    }

    public static function getAllPVZ($deliveries, $city_name, $codeCity)
    {
        $id_feature = 0;
        $result_array = [];
        $points_Array = [];
        $cache = Cache::createInstance();
        $cachePath = '/getAllPVZPoints';
        $delName = '0';

        try {
            if ($cache->initCache(7200, 'pvz_' . $city_name, $cachePath)) {
                $points_Array = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                foreach ($deliveries as $delName) {
                    $delivery = CommonPVZ::getInstanceObject($delName);
                    if ($delivery!=null) {
                        $delivery->getPVZ($city_name, $points_Array, $id_feature, $codeCity);
                        $result_array['errors'][$delName] = $delivery->errors;
                    }
                }
                $cache->endDataCache($points_Array);
            }
        } catch (\Exception $e) {
            $result_array['errors'][$delName] = $e->getMessage();
        }

        $result_array['type'] = 'FeatureCollection';
        $result_array['features'] = $points_Array;

        return $result_array;
    }

    public static function addAssets($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        $params = [];
        $params['delID'] = 96;

        foreach ($arDeliveryServiceAll as $k => $v) {
            if ($v->getHandlerCode() === self::$MODULE_ID) {
                $params['delID'] = $k;
            }
        }

        $propertyCollection = $order->getPropertyCollection();
        foreach ($propertyCollection as $orderProp)
        {
            $prop = $orderProp->getProperty();
            if ($prop['CODE'] === 'COMMON_PVZ') {
                $params['propCommonPVZ'] = $prop['ID'];
            } elseif ($prop['CODE'] === 'ADDRESS') {
                $params['propAddress'] = $prop['ID'];
            } elseif ($prop['CODE'] === 'TYPE_DELIVERY') {
                $params['propTypeDelivery'] = $prop['ID'];
            }
        }

        $cAsset = Asset::getInstance();

        $cAsset->addJs('/bitrix/modules/enterego.pvz/lib/CommonPVZ/script.js', true);
        $cAsset->addCss('/bitrix/modules/enterego.pvz/lib/CommonPVZ/style.css', true);
        $cAsset->addString(
            "<script id='' data-params=''>
                    window.addEventListener('load', function () {
                        BX.SaleCommonPVZ.init({
                            params: " . CUtil::PhpToJSObject($params) . "
                        });
                         if (typeof BX !== 'undefined' && BX.addCustomEvent)
                            BX.addCustomEvent('onAjaxSuccess', BX.SaleCommonPVZ.update);
                        });
                </script>",
            true
        );
    }
}