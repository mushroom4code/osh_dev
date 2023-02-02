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

    public static function getConfigs() {
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
        $delName = '0';

        try {
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
        } catch (\Exception $e) {
            $result_array['errors'][$delName] = $e->getMessage();
        }

        $result_array['type'] = 'FeatureCollection';
        $result_array['features'] = $points_Array;

        return $result_array;
    }

    public static function getPrice($req_data)
    {
        global $CONFIG_DELIVERIES;

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

    public static function addAssets($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        $delID = 96;
        $params = [];

        foreach ($arDeliveryServiceAll as $k => $v) {
            if ($v->getHandlerCode() === self::$MODULE_ID) {
                $params['delID'] = $k;
            }
        }

        $dbRes = \Bitrix\Sale\Property::getList([
            'select' => ['ID', 'CODE'],
            'filter' => [
                'REL_DLV.ENTITY_ID' => $delID,
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ReferenceField(
                    'REL_DLV',
                    '\Bitrix\Sale\Internals\OrderPropsRelationTable',
                    array("=this.ID" => "ref.PROPERTY_ID", "=ref.ENTITY_TYPE" => new \Bitrix\Main\DB\SqlExpression('?', 'D')),
                    array("join_type"=>"left")
                ),
            ],
            'group' => ['ID'],
            'order' => ['ID' => 'DESC']
        ]);

        while ($property = $dbRes->fetch())
        {
            if ($property['CODE'] === 'ADDRESS') {
                $params['arPropsAddr'][] = $property['ID'];
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
                    });
                </script>",
            true
        );
    }
}