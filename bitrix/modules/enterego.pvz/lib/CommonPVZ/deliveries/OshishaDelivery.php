<?php

namespace CommonPVZ;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Internals\PersonTypeTable;



class OshishaDelivery extends CommonPVZ
{
    static $MODULE_ID = 'enterego.pvz';
    public string $delivery_name = 'Oshisha';
    public string $delivery_code = 'oshisha';
    const OSHISHA_ADDRESS_SIMPLE = "simple";
    const OSHISHA_ADDRESS_COMPLEX = "complex";
    static $oshisha_fields = array(
        'active',
        'pvzStrict',
        'address_type',
        'ymaps_key',
        'da_data_token',
        'timeDeliveryEndNight',
        'timeDeliveryStartNight',
        'timeDeliveryStartDay',
        'timeDeliveryEndDay',
        'cost',
//        'deduct',
//        'bitrix_stock',
//        'quantity_override'
    );

    public static function checkMoscowOrNot($locationCode)
    {
        $result = DeliveryLocationTable::checkConnectionExists(DOOR_DELIVERY_ID, $locationCode,
            array(
                'LOCATION_LINK_TYPE' => 'AUTO'
            )
        );
        return $result;
    }

    public static function updateOshishaRegionRestrictions() {
        $arLocation = ["L" => ["0000028025","0000073738"]];
        DeliveryLocationTable::resetMultipleForOwner(DOOR_DELIVERY_ID, $arLocation);
    }

    public static function getDeliveryStatus() {
        return array('Oshisha' => 'Y');
    }

    public static function getInstanceForDoor($deliveryParams): array
    {
        //TODO validate moscow and region!!!!
        $res = self::checkMoscowOrNot($deliveryParams['location'] ?? '');

       if ($res && Option::get(DeliveryHelper::$MODULE_ID, 'Oshisha_door_active')) {
           return [new OshishaDelivery()];
       }
       return [];
    }

    public static function getInstanceForPvz($deliveryParams): array
    {
        //TODO validate moscow and region!!!!
        $res = self::checkMoscowOrNot($deliveryParams['codeCity'] ?? '');

        if (Option::get(DeliveryHelper::$MODULE_ID, 'Oshisha_pvz_active')) {
            return [new OshishaDelivery()];
        }
        return [];
    }

    public static function getOshishaDataValue($name)
    {
        return Option::get(self::$MODULE_ID, "Oshisha_{$name}");
    }

    public static function getOshishaYMapsKey()
    {
        return self::getOshishaDataValue('ymapskey');
    }

    public static function getOshishaDaDataToken()
    {
        return self::getOshishaDataValue('dadatatoken');
    }

    public static function getOshishaDaDataSecret()
    {
        return self::getOshishaDataValue('dadata_secret');
    }

    public static function getOshishaCost()
    {
        return self::getOshishaDataValue('cost');
    }

    public static function getOshishaStartCost()
    {
        return 299;
    }

    public static function getOshishaLimitBasket(){
        return 4000;
    }

    public function getNoMarkupDays() {
        $result = [];
        $result['northdays'] = explode(',', $this->configs['northdays']);
        $result['southeastdays'] = explode(',', $this->configs['southeastdays']);
        $result['southwestdays'] = explode(',', $this->configs['southwestdays']);
        return $result;
    }

    public static function getOshishaPersonTypes(){
        $arSelectPt = array("ID","NAME_WS");
        $arOrderPt = array("SORT" => "ASC");
        $arFilter = array("ACTIVE" => "Y");
        $arRuntime = array(new Entity\ExpressionField('NAME_WS', "CONCAT('(',%s,') ',%s)",array("LID","NAME")));
        $dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelectPt, "order" => $arOrderPt,
            "filter" => $arFilter, "runtime" => $arRuntime));
        $arPersonTypes = array();
        while($arPersonType = $dbPersonTypes->fetch()){
            $arPersonTypes[$arPersonType["ID"]] = array("NAME" => $arPersonType["NAME_WS"]);
        }
        return $arPersonTypes;
    }

    public static function getOshishaOptionsData()
    {
        $data = array();
//        foreach (self::$oshisha_fields as $field) {
//            $data[$field] = self::getOshishaDataValue($field);
//        }
////        $this->data['debug'] = boolval($this->data['debug']);
////        $this->data['direct'] = boolval($this->data['direct']);
//        $arPersonTypes = self::getOshishaPersonTypes();
//        $isAddressSimple = boolval($data["address_type"] != self::OSHISHA_ADDRESS_COMPLEX);
////        if($isAddressSimple) {
////            $this->data["mirror_pvz_address"] = boolval($this->getDataValue('mirror_pvz_address'));
////        }
//        foreach ($arPersonTypes as $id => $name) {
////            $this->fields[] = 'pvz_prop_'.$id;
////            $this->data['pvz_prop_'.$id] = $this->getDataValue('pvz_prop_'.$id);
//            if ($isAddressSimple) {
//                self::$oshisha_fields[] = 'address_prop_id_' . $id;
//                self::$oshisha_fields[] = 'time_period_' . $id;
//                $data['address_prop_id_' . $id] = self::getOshishaDataValue('address_prop_id_' . $id);
//                $data['time_period_' . $id] = self::getOshishaDataValue('time_period_' . $id);
//
//            } else {
//                self::$oshisha_fields[] = 'street_prop_id_' . $id;
//                $data['street_prop_id_' . $id] = self::getOshishaDataValue('street_prop_id_' . $id);
//                self::$oshisha_fields[] = 'corp_prop_id_' . $id;
//                $data['corp_prop_id_' . $id] = self::getOshishaDataValue('corp_prop_id_' . $id);
//                self::$oshisha_fields[] = 'bld_prop_id_' . $id;
//                $data['bld_prop_id_' . $id] = self::getOshishaDataValue('bld_prop_id_' . $id);
//                self::$oshisha_fields[] = 'flat_prop_id_' . $id;
//                $data['flat_prop_id_' . $id] = self::getOshishaDataValue('flat_prop_id_' . $id);
//            }
//            return $data;
//        }
    }

    public static function generate($arOptions, $arConfigData)
    {
        foreach ($arOptions as $optionName => $arOption):?>
            <tr>
            <?php if ($arOption['type'] === 'news') { ?>
                <tr class="heading" dataId="<?= $arOption["id"] ?>">
                    <td colspan="2">
                        <?= $arOption["name"] ?>
                    </td>
                </tr>
            <?php } ?>
            <? if ($arOption["type"] == "NOTE"): ?>
                <td colspan="2" align="center">
                    <?= BeginNote(); ?>
                    <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;"/><?= $arOption["name"] ?>
                    <?= EndNote(); ?>
                </td>
            <? elseif ($arOption["type"] == "heading"): ?>
                <td colspan="2" class="heading">
                    <?= $arOption["name"] ?>
                </td>
            <?
            else:
                $value = isset($arConfigData[$optionName]) ? $arConfigData[$optionName] : $arOption["default"];

                if ($arOption["type"] !== 'news') {
                    ?>
                    <td class="field-name" style="width:45%"><?= $arOption["name"] ?>:</td>
                <?php } else { ?><td class="field-name" style="width:45%"></td><?php } ?>
                <td>
                    <? switch ($arOption["type"]):
                        case "text":
                        case "email":
                        case "number";
                            $arOption["name"] = $optionName;
                            $arOption["value"] = $value;
                            echo self::input($arOption, '');
                            break;
                        case "textarea":
                            $arParams = array(
                                "ATTRS" => array(
                                    "name" => $optionName
                                ),
                                "VALUE" => $value
                            );
                            echo self::pairTag($arOption["type"], $arParams);
                            break;
                        case "news":
                            $elem = '';
                            $id = $arOption["id"];
                            if ($id === 'dayDelivery') {
                                $start_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryStartDay');
                                $end_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryEndDay');
                            } else {
                                $start_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryStartNight');
                                $end_json = Option::get(DeliveryHelper::$MODULE_ID, 'osh_timeDeliveryEndNight');
                            }

                            $start = json_decode($start_json);
                            $end = json_decode($end_json);

                            $delete = "<a href='javascript:void(0)' class='flex-align-items' 
                                        onClick='settingsDeleteRow(this)'> 
                                       <img src='/bitrix/themes/.default/images/actions/delete_button.gif' 
                                        border='0' width='20' height='20'/></a>";

                            if (count($start) == 0) {
                                $start[] = '';
                                $end[] = '';
                            }
                            $dayItem = $arOption["elems"][0];
                            $nightItem = $arOption["elems"][1];

                            foreach ($start as $key => $elems_start) {

                                $elem .= "<div class='flex-row d-flex padding-10'>";
                                $dayItem['value'] = $elems_start;
                                $elem .= self::input($dayItem, 'elems');

                                $nightItem['value'] = $end[$key];
                                $elem .= self::input($nightItem, 'elems');

                                $elem .= $delete;
                                $elem .= "</div>";
                            }
                            $dop_insert = "<a href='javascript:void(0)' onclick='settingsAddRights(this)' 
                                    hidefocus='true' class='button_red' dataId='$id'>Добавить поле </a>";

                            echo "<div class='flex-justify-content flex-column'><div data-type='$id' id='$id'>$elem</div>
                              <div><div class='margin-left-10'>$dop_insert</div> 
                              </div></div>";


                            break;
                        case "select":
                            if ($arOption["multiple"]) {
                                $values = explode("|", $value);
                            } else {
                                $values = [$value];
                            }
                            ?>

                            <select name="<?= $optionName ?><? if ($arOption["multiple"]): ?>[]<? endif ?>"
                                <?php if ($arOption["multiple"]): ?>
                                    multiple
                                    <?php if ($arOption["size"]): ?>
                                        size="<?php echo $arOption["size"] ?>"
                                    <? endif ?>
                                <? endif ?>
                                    <? if ($arOption["onchange"]): ?>onChange="<?= $arOption["onchange"] ?>"<? endif ?>
                            >
                                <? foreach ($arOption["options"] as $id => $text): ?>
                                    <option <? if (in_array($id, $values)) echo " selected " ?>
                                        value="<?= $id ?>"><?= $text ?></option>
                                <? endforeach ?>

                            </select>
                            <? break;
                    endswitch ?>
                    <? if (!empty($arOption["hint"])): ?>
                        <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;cursor:pointer"
                             title="<?= $arOption["hint"] ?>"
                            <? if (!empty($arOption["href"])): ?>
                                onclick="window.open('<?= $arOption["href"] ?>');"
                            <? endif ?>
                        />
                    <? endif ?>
                    <? if (!empty($arOption["link"])): ?>
                        <a href="<?= $arOption["link"]['href'] ?>" target="blank"
                           style="padding-left: 10px;"><?= $arOption["link"]['text'] ?></a>
                    <? endif ?>
                </td>
            <? endif ?>

            </tr><?
        endforeach;
    }

    static function input($arParams, string $param)
    {
        if ($param === 'elems') {
            return self::singleTag("input", $arParams);
        } else {
            return self::generateHtml("input", $arParams);
        }
    }

    public
    static function singleTag($tag, $tagParams)
    {
        $params = array();
        foreach ($tagParams as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }

        return "<$tag " . implode(" ", $params) . " class='margin-right-20' />";
    }

    public
    static function generateHtml($tagName, $tagParams)
    {
        $params = array();
        foreach ($tagParams as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }

        return "<{$tagName} " . implode(" ", $params) . " />";
    }

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
    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions)
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
            $point = OshishaSavedDeliveriesTable::getRow(array('filter' => array('LATITUDE' => $params['latitude'],
                'LONGITUDE' => $params['longitude'])));
            if ($point) {
                $cost = self::getOshishaCost();
                $startCost = self::getOshishaStartCost();
                $distance = ceil(($point['DISTANCE'] ?? 0) - 0.8);
                $noMarkup = false;
                $dayDateDelivery = $params['date_delivery'] ? date('w' ,strtotime($params['date_delivery']))
                    : date('w', strtotime('+1 day'));
                if ($point['ZONE'] == 'NORTH') {
                    foreach (explode(',', $this->configs['northdays']) as $noMarkupDay) {
                        if ($noMarkupDay == $dayDateDelivery) {
                            $noMarkup = true;
                        }
                    }
                } else if ($point['ZONE'] == 'SOUTHEAST') {
                    foreach (explode(',', $this->configs['southeastdays']) as $noMarkupDay) {
                        if ($noMarkupDay == $dayDateDelivery) {
                            $noMarkup = true;
                        }
                    }
                } else if ($point['ZONE'] == 'SOUTHWEST') {
                    foreach (explode(',', $this->configs['southwestdays']) as $noMarkupDay) {
                        if ($noMarkupDay == $dayDateDelivery) {
                            $noMarkup = true;
                        }
                    }
                }
                $limitBasket = self::getOshishaLimitBasket();

                if (intval($params['shipment_cost']) >= $limitBasket && !$noMarkup) {
                    $delivery_price = max($distance - 5, 0) * $cost;
                }
                else {
                    if ($noMarkup) {
                        $delivery_price = $startCost;
                    } else {
                        $delivery_price = $startCost + $distance * $cost;
                    }
                }

                return $delivery_price;
            } else {
//                return 'доставка не может быть расчитана';
                $this->errors[] = 'no data found on point';
                return array('errors' => $this->errors);
            }
        } catch(\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}