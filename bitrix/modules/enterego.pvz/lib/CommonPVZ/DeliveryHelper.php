<?php

namespace CommonPVZ;

use Bitrix\Main\Data\Cache,
    Bitrix\Main\Page\Asset;
use Bitrix\Sale\Location\LocationTable;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DeliveryHelper
{

    public static function getButton($address = '')
    {

        ob_start();
        ?>
        <a class="btn btn_basket btn_pvz btn-default"
           onclick="BX.SaleCommonPVZ.openMap(); return false;">
            <?= Loc::getMessage('COMMONPVZ_BTN_CHOOSE') ?>
        </a>
        <?php

        $content = ob_get_contents();
        ob_end_clean();

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

    public static function getAllPVZ($deliveries, $city_name, $codeCity)
    {
        $id_feature = 0;
        $result_array = [];
        $points_Array = [];
        $cache = Cache::createInstance();
        $cachePath = '/getAllPVZPoints';

        if ($cache->initCache(7200, 'pvz_' . $city_name, $cachePath)) {
            $points_Array = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            foreach ($deliveries as $delName) {
                $deliveryClass = '\CommonPVZ\\' . $delName . 'Delivery';
                $delivery = new $deliveryClass();
                $delivery->getPVZ($city_name, $points_Array, $id_feature, $codeCity);
                $result_array['errors'][$delName] = $delivery->errors;
            }
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
        } elseif ($req_data['delivery'] === 'СДЭК') {
            $delivery = new SDEKDelivery();
            return $delivery->getPrice($req_data);
        } elseif ($req_data['delivery'] === 'ПЭК') {
            $delivery = new PEKDelivery();
            return $delivery->getPrice($req_data);
        } elseif ($req_data['delivery'] === '5Post') {
            $delivery = new FivePostDelivery();
            return $delivery->getPrice($req_data);
        }
    }

    public function registerJSComponent($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        $cAsset = Asset::getInstance();
        $cAsset->addJs('/bitrix/modules/enterego.pvz/lib/CommonPVZ/script.js', true);
        $cAsset->addCss('/bitrix/modules/enterego.pvz/lib/CommonPVZ/style.css', true);
    }
}