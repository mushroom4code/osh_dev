<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache,
    Bitrix\Main\Page\Asset;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
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

    public static function getActiveDeliveries($isPvz = false) {
        $deliveriesStatuses = [];
        $deliveriesStatuses = array_merge($deliveriesStatuses, DellinDelivery::getDeliveryStatus());
        $deliveriesStatuses = array_merge($deliveriesStatuses, FivePostDelivery::getDeliveryStatus());
        $deliveriesStatuses = array_merge($deliveriesStatuses, OshishaDelivery::getDeliveryStatus());
        $deliveriesStatuses = array_merge($deliveriesStatuses, PEKDelivery::getDeliveryStatus());
        $deliveriesStatuses = array_merge($deliveriesStatuses, PickPointDelivery::getDeliveryStatus());
        $deliveriesStatuses = array_merge($deliveriesStatuses, RussianPostDelivery::getDeliveryStatus($isPvz));
        $deliveriesStatuses = array_merge($deliveriesStatuses, SDEKDelivery::getDeliveryStatus());
        foreach ($deliveriesStatuses as $delivery => $status) {
            if ($status === 'N')
                unset($deliveriesStatuses[$delivery]);
        }
        return array_keys($deliveriesStatuses);
    }

    public static function getPackagesFromOrderBasket($orderBasket) {
        $packages = [];
        foreach ($orderBasket as $orderBasketItem) {
            $packageParams = array();
            $basketItemFields = $orderBasketItem->getFields();
            $productDimensions =  unserialize($basketItemFields['DIMENSIONS']);
            $packageParams['height'] = (int)$productDimensions['HEIGHT'];
            $packageParams['lenght'] = (int)$productDimensions['LENGTH'];
            $packageParams['width'] = (int)$productDimensions['WIDTH'];
            $packageParams['weight'] = (int)$basketItemFields['WEIGHT'];
            $packages[$basketItemFields['PRODUCT_ID']] = $packageParams;
        }
        return $packages;
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

    public static function checkMoscowOrNot($locationCode) {
        $result = DeliveryLocationTable::checkConnectionExists(DOOR_DELIVERY_ID, $locationCode,
            array(
                'LOCATION_LINK_TYPE' => 'AUTO'
            )
        );
        return $result;
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

    /** Обновляет ПВЗ для службы доставки Деловых линий
     * @return string[]
     */
    public static function updateDellinPVZ(): array
    {
        try {
            $pickPoint = new DellinDelivery();
            $pickPoint->updatePointsForDellin();
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
            $uniqueCacheString = 'pvz_'.$city_name;
            foreach ($deliveries as $delivery) {
                $uniqueCacheString .= $uniqueCacheString.'_'.$delivery;
            }
            if ($cache->initCache(7200, $uniqueCacheString, $cachePath)) {
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

        $params['curDeliveryId'] = $order->getField('DELIVERY_ID');
        $params['doorDeliveryId'] = DOOR_DELIVERY_ID;


        $PeriodDelivery = [];
        $start_json_day = Option::get(self::$MODULE_ID, 'Oshisha_timeDeliveryStartDay');
        $end_json_day = Option::get(self::$MODULE_ID, 'Oshisha_timeDeliveryEndDay');
        $start_json_night = Option::get(self::$MODULE_ID, 'Oshisha_timeDeliveryStartNight');
        $end_json_night = Option::get(self::$MODULE_ID, 'Oshisha_timeDeliveryEndNight');
        $start_day = json_decode($start_json_day);
        $end_day = json_decode($end_json_day);
        $start_night = json_decode($start_json_night);
        $end_night = json_decode($end_json_night);

        if (!empty($start_day) && !empty($end_day)) {
            foreach ($start_day as $key => $elems_start) {
                $PeriodDelivery[] = $elems_start . '-' . $end_day[$key];
            }
        }

        if (!empty($start_night) && !empty($end_night)) {
            foreach ($start_night as $keys => $elems_start_night) {
                $PeriodDelivery[] .= $elems_start_night . '-' . $end_night[$keys];
            }
        }

        $params['deliveryOptions']['DA_DATA_TOKEN'] = \CommonPVZ\OshishaDelivery::getOshishaDaDataToken();

        if (\CommonPVZ\OshishaDelivery::getDeliveryStatus()['Oshisha'] === 'Y') {
            $params['oshishaDeliveryStatus'] = true;
            $params['deliveryOptions']['PERIOD_DELIVERY'] = $PeriodDelivery;
            $params['deliveryOptions']['YA_API_KEY'] = \CommonPVZ\OshishaDelivery::getOshishaYMapsKey();
            $params['deliveryOptions']['DELIVERY_COST'] = \CommonPVZ\OshishaDelivery::getOshishaCost();
            $params['deliveryOptions']['START_COST'] = \CommonPVZ\OshishaDelivery::getOshishaStartCost();
            $params['deliveryOptions']['LIMIT_BASKET'] = \CommonPVZ\OshishaDelivery::getOshishaLimitBasket();
            $params['deliveryOptions']['CURRENT_BASKET'] = $order->getBasePrice();
            $params['deliveryOptions']['DA_DATA_ADDRESS'] = $_SESSION['Osh']['delivery_address_info']['address'] ?? '';
        } else {
            $params['oshishaDeliveryStatus'] = false;
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
            } elseif ($prop['CODE'] === 'ZIP') {
                $params['propZip'] = $prop['ID'];
            } elseif ($prop['CODE'] === 'CITY') {
                $params['propCity'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'FIAS') {
                $params['propFias'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'KLADR') {
                $params['propKladr'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'STREET_KLADR') {
                $params['propStreetKladr'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'LATITUDE') {
                $params['propLatitude'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'LONGITUDE') {
                $params['propLongitude'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'DATE_DELIVERY') {
                $params['propDateDelivery'] = $prop['ID'];
            }elseif ($prop['CODE'] === 'DELIVERYTIME_INTERVAL') {
                $params['propDeliveryTimeInterval'] = $prop['ID'];
            }
        }

        $params['shipmentCost'] = $order->getBasePrice();
        $params['packages'] = array();
        $orderBasket = $order->getBasket();
        $params['packages'] = self::getPackagesFromOrderBasket($orderBasket);
        ksort($params['packages']);
        $cAsset = Asset::getInstance();

        $cAsset->addJs('/bitrix/modules/enterego.pvz/lib/CommonPVZ/script.js', true);
        $cAsset->addJs('/bitrix/js/enterego.pvz/jquery.suggestions.min.js', true);
        $cAsset->addJs('/bitrix/js/enterego.pvz/async.js', true);
        $cAsset->addCss('/bitrix/modules/enterego.pvz/lib/CommonPVZ/style.css', true);
        $cAsset->addCss('/bitrix/modules/osh.shipping/install/css/suggestions.css', true);
        \CJSCore::Init(array("osh_pickup"));
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