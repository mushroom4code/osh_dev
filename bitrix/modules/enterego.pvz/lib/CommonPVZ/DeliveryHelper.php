<?php

namespace CommonPVZ;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache,
    Bitrix\Main\Page\Asset;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Location\LocationTable;
use \Bitrix\Sale\Location\TypeTable;
use \Bitrix\Main\Localization\Loc;
use CUtil;
use Dadata\DadataClient;

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

    public static function makeDimensionsHash($a, $b, $c)
    {
        $arr = [$a, $b, $c];

        array_walk($arr, function (&$val, $key) {$val = (int)floor($val / 10);});
        sort($arr);

        return ($arr[0] + $arr[1]*1000 + $arr[2]*1000000);
    }

    /**
     * @param $arGoods array(array(size1,size2,size3,quantity))
     * @return array of type ('L' => <length>, 'W' => <width>, 'H' => <height>)
     */
    public static function getSumDimensions($arGoods)
    {
        if(!is_array($arGoods) || !count($arGoods))
            return array('L'=>0,'W'=>0,'H'=>0);

        $arWork = array();
        foreach($arGoods as $good) {
            $good = array_values($good);
            $arWork []= self::sumSizeOneGoods($good[0], $good[1], $good[2], $good[3]);
        }

        return self::sumSize($arWork);
    }

    protected static function sumSizeOneGoods($xi, $yi, $zi, $qty)
    {
        $ar = array($xi, $yi, $zi);
        sort($ar);
        if ($qty<=1) return (array('X'=>$ar[0],'Y'=>$ar[1],'Z'=>$ar[2]));

        $x1 = 0;
        $y1 = 0;
        $z1 = 0;
        $l = 0;

        $max1 = floor(Sqrt($qty));
        for($y=1;$y<=$max1;$y++){
            $i = ceil($qty/$y);
            $max2 = floor(Sqrt($i));
            for($z=1;$z<=$max2;$z++){
                $x = ceil($i/$z);
                $l2 = $x*$ar[0] + $y*$ar[1] + $z*$ar[2];
                if(($l==0)||($l2<$l)){
                    $l = $l2;
                    $x1 = $x;
                    $y1 = $y;
                    $z1 = $z;
                }
            }
        }
        return (array('X'=>$x1*$ar[0],'Y'=>$y1*$ar[1],'Z'=>$z1*$ar[2]));
    }

    protected static function sumSize($a)
    {
        $n = count($a);
        if (!($n>0)) return(array('L'=>'0','W'=>'0','H'=>'0'));
        for($i3=1;$i3<$n;$i3++){
            // sort sizes big to small
            for($i2=$i3-1;$i2<$n;$i2++){
                for($i=0;$i<=1;$i++){
                    if($a[$i2]['X']<$a[$i2]['Y']){
                        $a1 = $a[$i2]['X'];
                        $a[$i2]['X'] = $a[$i2]['Y'];
                        $a[$i2]['Y'] = $a1;
                    }
                    if(($i==0) && ($a[$i2]['Y']<$a[$i2]['Z'])){
                        $a1 = $a[$i2]['Y'];
                        $a[$i2]['Y'] = $a[$i2]['Z'];
                        $a[$i2]['Z'] = $a1;
                    }
                }
                $a[$i2]['Sum'] = $a[$i2]['X'] + $a[$i2]['Y'] + $a[$i2]['Z']; // sum of sides
            }
            // sort cargo from small to big
            for($i2=$i3;$i2<$n;$i2++)
                for($i=$i3;$i<$n;$i++)
                    if($a[$i-1]['Sum']>$a[$i]['Sum']){
                        $a2 = $a[$i];
                        $a[$i] = $a[$i-1];
                        $a[$i-1] = $a2;
                    }
            // calculate sum dimensions of two smallest cargoes
            if($a[$i3-1]['X']>$a[$i3]['X']) $a[$i3]['X'] = $a[$i3-1]['X'];
            if($a[$i3-1]['Y']>$a[$i3]['Y']) $a[$i3]['Y'] = $a[$i3-1]['Y'];
            $a[$i3]['Z'] = $a[$i3]['Z'] + $a[$i3-1]['Z'];
            $a[$i3]['Sum'] = $a[$i3]['X'] + $a[$i3]['Y'] + $a[$i3]['Z']; // sum of sides
        }

        $a = array(
            Round($a[$n-1]['X'],2),
            Round($a[$n-1]['Y'],2),
            Round($a[$n-1]['Z'],2)
        );
        rsort($a);

        return array(
            'L' => $a[0],
            'W' => $a[1],
            'H' => $a[2]
        );
    }

    public static function getActivePvzDeliveryInstance($deliveryParams)
    {
        $deliveryInstance = array_merge(
            OshishaDelivery::getInstanceForPvz($deliveryParams),
            DellinDelivery::getInstanceForPvz(),
            RussianPostDelivery::getInstanceForPvz(),
            SDEKDelivery::getInstanceForPvz(),
            FivePostDelivery::getInstanceForPvz()
        );
        return $deliveryInstance;
    }

    public static function getActiveDoorDeliveryInstance($deliveryParams){
        $deliveryInstance = array_merge(
            OshishaDelivery::getInstanceForDoor($deliveryParams),
            DellinDelivery::getInstanceForDoor($deliveryParams),
            RussianPostDelivery::getInstanceForDoor($deliveryParams),
            SDEKDelivery::getInstanceForDoor($deliveryParams),
        );
        return $deliveryInstance;
    }

    public static function getPackagesFromOrderBasket($orderBasket) {
        $packages = [];
        foreach ($orderBasket as $orderBasketItem) {
            $packageParams = array();
            $basketItemFields = $orderBasketItem->getFields();
            $productDimensions =  unserialize($basketItemFields['DIMENSIONS']);
            $packageParams['length'] = (int)$productDimensions['LENGTH']
                ? (int)$productDimensions['LENGTH']
                : (int)Option::get(self::$MODULE_ID, 'Common_defaultlength');
            $packageParams['width'] = (int)$productDimensions['WIDTH']
                ? (int)$productDimensions['WIDTH']
                : (int)Option::get(self::$MODULE_ID, 'Common_defaultwidth');
            $packageParams['height'] = (int)$productDimensions['HEIGHT']
                ? (int)$productDimensions['HEIGHT']
                : (int)Option::get(self::$MODULE_ID, 'Common_defaultheight');
            $packageParams['quantity'] = (int)$basketItemFields['QUANTITY'];
            $packageParams['weight'] = (int)$basketItemFields['WEIGHT']
                ? (int)$basketItemFields['WEIGHT']
                : (int)Option::get(self::$MODULE_ID, 'Common_defaultweight');

            $packages[$basketItemFields['PRODUCT_ID']] = $packageParams;
        }
        return $packages;
    }

    public static function getSavedOshishaDelivery($latitude, $longitude) {
        $point = OshishaSavedDeliveriesTable::getRow(array('filter' => array(
            'LATITUDE' => number_format($latitude, 4, '.', ''),
            'LONGITUDE' => number_format($longitude, 4, '.', ''))));
        if ($point)
            return true;
        else
            return false;
    }

    public static function saveOshishaDelivery($params) {
        $dbResultError = false;
        if (!OshishaSavedDeliveriesTable::getRow(array('filter' => array('LATITUDE' => number_format($params['latitude'], 4, '.', ''),
            'LONGITUDE' => number_format($params['longitude'], 4, '.', ''))))) {
            $result = OshishaSavedDeliveriesTable::add(array('fields' => array(
                'LATITUDE' => number_format($params['latitude'], 4, '.', ''),
                'LONGITUDE' => number_format($params['longitude'], 4, '.', ''),
                'ZONE' => $params['zone'],
                'DISTANCE' => $params['distance']
            )));
            if (!$result->isSuccess()) {
                $dbResultError = true;
            }
        }
        if ($dbResultError) {
            return false;
        } else {
            return true;
        }
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

    public static function getDayOfTheWeekString($dayIndex) {
        return ["sunday", "monday","tuesday","wednesday","thursday","friday","saturday"][$dayIndex];
    }

    public static function getCityName($locationCode)
    {
        $res = \Bitrix\Sale\Location\TypeTable::getList(array(
            'select' => array('*', 'NAME_RU' => 'NAME.NAME'),
            'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID)
        ));
        $types = [];
        while ($item = $res->fetch()){
            $types[] = $item;
        }
        $city = LocationTable::getByCode(
            $locationCode,
            [
                'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=PARENT.NAME.LANGUAGE_ID' => LANGUAGE_ID),
                'select' => ['ID', 'TYPE_ID', 'LOCATION_NAME' => 'NAME.NAME',
                    'PARENT_LOCATION_NAME' => 'PARENT.NAME.NAME']
            ]
        )->fetch();

        if ((int)$city['TYPE_ID'] === 6) {
            $areaNameArray = LocationTable::getByCode(
                $locationCode,
                [
                    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=PARENT.PARENT.NAME.LANGUAGE_ID' => LANGUAGE_ID),
                    'select' => ['ID','AREA_NAME' => 'PARENT.PARENT.NAME.NAME']
                ]
            )->fetch();
            $city["AREA_NAME"] = $areaNameArray['AREA_NAME'];

            $locationName = explode(' ', $city['LOCATION_NAME']);
            array_pop($locationName);
            $city['LOCATION_NAME'] = implode(' ', $locationName);

            $parentLocationName = explode(' ', $city['PARENT_LOCATION_NAME']);
            if (count($parentLocationName) > 1)
                array_pop($parentLocationName);
            $city['PARENT_LOCATION_NAME'] = implode(' ', $parentLocationName);
        } else {
            $city['AREA_NAME'] = '';
        }


        return json_encode(array('LOCATION_NAME' => $city['LOCATION_NAME'],
            'PARENT_LOCATION_NAME' => $city['PARENT_LOCATION_NAME'],
            'AREA_NAME' => $city['AREA_NAME'],
            'TYPE' => $city['TYPE_ID']));
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
            return ['status'=>'failed', 'error' => $e->getMessage()];
        }
        return ['status'=>'success'];
    }

    /** Обновляет ПВЗ для службы доставки Деловых линий
     * @return string[]
     */
    public static function updateDellinPVZ(): array
    {
        try {
            $dellin = new DellinDelivery();
            $dellin->updatePointsForDellin();
        } catch (\Exception $e) {
            return ['status'=>'failed', 'error' => $e->getMessage()];
        }
        return ['status'=>'success'];
    }

    /** Обновляет ПВЗ для службы доставки Почты России
     * @return string[]
     */
    public static function updateRussianPostPVZ(): array
    {
        try {
            $russianPost = new RussianPostDelivery();
            $russianPost->updatePointsForRussianPost();
        } catch (\Exception $e) {
            return ['status'=>'failed', 'error' => $e->getMessage()];
        }
        return ['status'=>'success'];
    }

    /** Обновляет ПВЗ для службы доставки 5post
     * @return string[]
     */
    public static function updateFivePostPVZ(): array
    {
        try {
            $fivePost = new FivePostDelivery();
            $fivePost->updatePointsForFivePost();
        } catch (\Exception $e) {
            return ['status'=>'failed', 'error' => $e->getMessage()];
        }
        return ['status'=>'success'];
    }

    /** Обновляет региональные ограничения для курьерской доставки oshisha
     * @return string[]
     */
    public static function updateOshishaRegionRestrictions(): array
    {
        try {
            OshishaDelivery::updateOshishaRegionRestrictions();
        } catch (\Exception $e) {
            return ['status'=>'failed', 'error' => $e->getMessage()];
        }
        return ['status'=>'success'];
    }

    public static function getAllPVZ($deliveries, $city_name, $codeCity, $packages)
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
                $uniqueCacheString .= $uniqueCacheString.'_'.$delivery->delivery_name;
            }

            $sumDimensions = self::getSumDimensions($packages);
            $dimensionsHash =  self::makeDimensionsHash($sumDimensions['W'], $sumDimensions['H'], $sumDimensions['L']);

            $uniqueCacheString .= $dimensionsHash;
            //TODO DEBUG
//            if (false && $cache->initCache(7200, $uniqueCacheString, $cachePath)) {
//                $points_Array = $cache->getVars();
//            } elseif ($cache->startDataCache()) {
                foreach ($deliveries as $delivery) {
                    if ($delivery!=null) {
                        $delivery->getPVZ($city_name, $points_Array, $id_feature, $codeCity, $packages, $dimensionsHash, $sumDimensions);
                        $result_array['errors'][$delName] = $delivery->errors;
                    }
                }
//                $cache->endDataCache($points_Array);
//            }
        } catch (\Throwable $e) {
            $result_array['errors'][$delName] = $e->getMessage();
        }

        $result_array['type'] = 'FeatureCollection';
        $result_array['features'] = $points_Array;

        return $result_array;
    }

    public static function addAssets($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        $params = [];
        foreach ($arDeliveryServiceAll as $deliveryService) {
            if ($deliveryService instanceof  PVZDeliveryProfile) {
                $params['pvzDeliveryId'] = $deliveryService->getId();
            }
            if ($deliveryService instanceof  DoorDeliveryProfile) {
                $params['doorDeliveryId'] = $deliveryService->getId();
            }
        }

        $params['curDeliveryId'] = $order->getField('DELIVERY_ID');

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

        $params['deliveryOptions']['DA_DATA_TOKEN'] = OshishaDelivery::getOshishaDaDataToken();

        $params['deliveryOptions']['PERIOD_DELIVERY'] = $PeriodDelivery;
        $params['deliveryOptions']['YA_API_KEY'] = OshishaDelivery::getOshishaYMapsKey();
        $params['deliveryOptions']['DELIVERY_COST'] = OshishaDelivery::getOshishaCost();
        $params['deliveryOptions']['START_COST'] = OshishaDelivery::getOshishaStartCost();
        $params['deliveryOptions']['LIMIT_BASKET'] = OshishaDelivery::getOshishaLimitBasket();
        $params['deliveryOptions']['CURRENT_BASKET'] = $order->getBasePrice();
        $params['deliveryOptions']['DA_DATA_ADDRESS'] = $_SESSION['Osh']['delivery_address_info']['address'] ?? '';

        if ($order->getField('PRICE_DELIVERY')) {
            $params['shipmentCost'] = $order->getDeliveryPrice();
        }

        $orderBasket = $order->getBasket();
        $params['packages'] = self::getPackagesFromOrderBasket($orderBasket);
        ksort($params['packages']);
        $cAsset = Asset::getInstance();

        $apiKey = htmlspecialcharsbx(Option::get('enterego.pvz','Oshisha_ymapskey', ''));
        $locale = 'ru-RU';
        if (empty($apiKey)) {
            Asset::getInstance()->addJs('//api-maps.yandex.ru/2.1.79/?load=package.standard&mode=release&lang=' . $locale);
        } else {
            Asset::getInstance()->addJs('///api-maps.yandex.ru/2.1.79/?apikey=' . $apiKey . '&lang=' . $locale);
        }

        $apiKey = htmlspecialcharsbx(Option::get('enterego.pvz','Oshisha_ymapskey', ''));
        $locale = 'ru-RU';
        if (empty($apiKey)) {
            Asset::getInstance()->addJs('//api-maps.yandex.ru/2.1.79/?load=package.standard&mode=release&lang=' . $locale);
        } else {
            Asset::getInstance()->addJs('///api-maps.yandex.ru/2.1.79/?apikey=' . $apiKey . '&lang=' . $locale);
        }

        $cAsset->addJs('/bitrix/modules/enterego.pvz/lib/CommonPVZ/script.js', true);
        $cAsset->addJs('/bitrix/js/enterego.pvz/jquery.suggestions.min.js', true);
        $cAsset->addJs('/bitrix/js/enterego.pvz/async.js', true);
        $cAsset->addCss('/bitrix/modules/enterego.pvz/lib/CommonPVZ/style.css', true);
        $cAsset->addCss('/bitrix/modules/enterego.pvz/install/css/suggestions.css', true);
        \CJSCore::Init(array("saved_delivery_profiles"));
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

    /**
     * @param string $address
     * @return mixed
     */
    public static function getDaDataAddressInfo(string $address)
    {
        $token = OshishaDelivery::getOshishaDaDataToken();
        $secret = OshishaDelivery::getOshishaDaDataSecret();

        $daData = new DadataClient($token, $secret);
        $res = $daData->suggest('address', $address);
        if (count($res) !== 0 ) {
            return $res[0];
        } else {
            return [];
        }

    }

    public static function getDaDataAddressByGeolocation($latitude, $longitude) {
        $token = OshishaDelivery::getOshishaDaDataToken();
        $secret = OshishaDelivery::getOshishaDaDataSecret();

        $daData = new DadataClient($token, $secret);
        $res = $daData->geolocate('address', $latitude, $longitude);
        if ($res) {
            if (count($res) !== 0 ) {
                return $res[0];
            } else {
                return [];
            }
        }
    }
}