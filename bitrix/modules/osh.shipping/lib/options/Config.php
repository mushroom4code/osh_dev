<?php

namespace Osh\Delivery\Options;

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Config\Option;
use COption;

Loader::includeModule("sale");
if (!empty($_POST['setting_data'])) {
    COption::SetOptionString('osh.shipping', 'time_period_val', $_POST['setting_data']);
}

class Config
{
    const MODULE_ID = "osh.shipping";
    const ADDRESS_SIMPLE = "simple";
    const ADDRESS_COMPLEX = "complex";
//    const DATE_NEAR = "near";
//    const DATE_DELAY = "delay";
//    const SORT_PROFILES_DAYS = "days";
//    const SORT_PROFILES_PRICE = "price";
//    const DEPARTURE_TYPE_AUTO = "auto";
//    const DEPARTURE_TYPE_MAN = "man";
//    const ROUND_TYPE_NONE = 'n';
//    const ROUND_TYPE_MATH = 'm';
//    const ROUND_TYPE_FLOOR = 'f';
//    const ROUND_TYPE_CEIL = 'c';
    const CACHE_MEC_NATIVE = 'nat';
    const CACHE_MEC_MODULE = 'mod';
//    const AS_EXPRESS_GATHERING = 'express-gathering';
//    const AS_ADDITIONAL_PACK = 'additional-pack';
//    const AS_INSURANCE = 'package-insurance';
//    const STOCK_NONE = '0';
    private $fields = array(
        'active',
        'pvzStrict',
        'address_type',
        'ymaps_key',
        'da_data_token',
        'active_discount_holiday',
        'pickup_discount_holiday',
        'pickpoint_discount_holiday',
        'sdek_discount_holiday',
        'fivepost_discount_holiday',
        'timeDeliveryEndNight',
        'timeDeliveryStartNight',
        'timeDeliveryStartDay',
        'timeDeliveryEndDay',
        'cost',
//        'deduct',
//        'bitrix_stock',
//        'quantity_override'
    );

    private $data;

    public function __construct()
    {
        $this->getOptionsData();
    }

    public function getOptionsData()
    {
        $this->data = array();
        foreach ($this->fields as $field) {
            $this->data[$field] = $this->getDataValue($field);
        }
//        $this->data['debug'] = boolval($this->data['debug']);
//        $this->data['direct'] = boolval($this->data['direct']);
        $arPersonTypes = \COshDeliveryHelper::getPersonTypes();
        $isAddressSimple = boolval($this->data["address_type"] != self::ADDRESS_COMPLEX);
//        if($isAddressSimple) {
//            $this->data["mirror_pvz_address"] = boolval($this->getDataValue('mirror_pvz_address'));
//        }
        foreach ($arPersonTypes as $id => $name) {
//            $this->fields[] = 'pvz_prop_'.$id;
//            $this->data['pvz_prop_'.$id] = $this->getDataValue('pvz_prop_'.$id);
            if ($isAddressSimple) {
                $this->fields[] = 'address_prop_id_' . $id;
                $this->fields[] = 'time_period_' . $id;
                $this->data['address_prop_id_' . $id] = $this->getDataValue('address_prop_id_' . $id);
                $this->data['time_period_' . $id] = $this->getDataValue('time_period_' . $id);

            } else {
                $this->fields[] = 'street_prop_id_' . $id;
                $this->data['street_prop_id_' . $id] = $this->getDataValue('street_prop_id_' . $id);
                $this->fields[] = 'corp_prop_id_' . $id;
                $this->data['corp_prop_id_' . $id] = $this->getDataValue('corp_prop_id_' . $id);
                $this->fields[] = 'bld_prop_id_' . $id;
                $this->data['bld_prop_id_' . $id] = $this->getDataValue('bld_prop_id_' . $id);
                $this->fields[] = 'flat_prop_id_' . $id;
                $this->data['flat_prop_id_' . $id] = $this->getDataValue('flat_prop_id_' . $id);
            }
        }
    }

    public function getDataValue($name)
    {
        return Option::get(self::MODULE_ID, "osh_{$name}");
    }

    public function getDataValueNew($name)
    {
        return Option::get(self::MODULE_ID, "{$name}");
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function saveSettings()
    {
        $request = Context::getCurrent()->getRequest();
        if ($request->isPost()) {
            foreach ($this->getFields() as $field) {
                if ($field === 'timeDeliveryStartDay' || $field === 'timeDeliveryEndDay'
                    || $field === 'timeDeliveryStartNight' || $field === 'timeDeliveryEndNight') {
                    $saveParam = json_encode($request[$field]);
                } else {
                    $saveParam = $request[$field];
                }
                $this->saveParam($field, $saveParam);
            }
        }
        $this->drawSettingsForm();
    }

    public function saveParam($name, $value)
    {
        $this->data[$name] = $value;
        switch ($name) {
            case "cashPaymentsIds":
                $value = implode("|", $value);
                $this->data[$name] = $value;
                break;
            case "tracking_map_statuses":
            case 'tracking_map_statuses_order':
                $value = serialize($value);
                break;
        }
        Option::set(self::MODULE_ID, "osh_" . $name, $value);
    }

    public function drawSettingsForm()
    {
        $oshSettings = $this->data;
        include(__DIR__ . '/../../templates/drawSettingsForm.php');
    }

    public static function isModuleActive()
    {
        $active = self::getDataValue("active");
        return $active == 'Y' ? true : false;
    }

    public static function isDirect()
    {
        $direct = self::getDataValue("direct");
        return boolval($direct);
    }

    public static function isAddressSimple()
    {
        $addressType = self::getDataValue("address_type");
        return boolval($addressType != self::ADDRESS_COMPLEX);
    }

    public static function isDebug()
    {
        $debug = self::getDataValue("debug");
        return boolval($debug);
    }

    public static function isMirrorPvz()
    {
        $mirror = self::getDataValue("mirror_pvz_address");
        return boolval($mirror);
    }

    public static function isCheckPvz()
    {
        $checkPvz = self::getDataValue('check_pvz');
        return !($checkPvz === "0");
    }

    public static function isPvzHaunt()
    {
        $isPvzHaunt = self::getDataValue("is_pvz_haunt");
        return !($isPvzHaunt === "0");
    }

    public static function isIncludeYaMaps()
    {
        $isIncludeYamaps = self::getDataValue("include_yamaps");
        return !($isIncludeYamaps === "0");
    }

    public static function isFulfilment()
    {
        $isFulfilment = self::getDataValue("is_fulfilment");
        return !($isFulfilment === "0");
    }

    public static function isDateTimeMirror()
    {
        $isFulfilment = self::getDataValue("is_date_time_mirror");
        return !($isFulfilment === "0");
    }

    public static function isDeduct()
    {
        $isDeduct = self::getDataValue("deduct");
        return !($isDeduct === "0");
    }

    public static function isQuantityOverride()
    {
        $isOverride = self::getDataValue("quantity_override");
        return !($isOverride === "0");
    }

    public static function getApiKey()
    {
        return self::getDataValue("adminApiKey");
    }

    public static function getAddressPropId($personTypeId)
    {
        return self::getDataValue("address_prop_id_" . $personTypeId);
    }

    public static function getPeriodPropId($personTypeId)
    {
        return self::getDataValue("time_period_" . $personTypeId);
    }

    public static function getStreetPropId($personTypeId)
    {
        return self::getDataValue("street_prop_id_" . $personTypeId);
    }

    public static function getBldPropId($personTypeId)
    {
        return self::getDataValue("bld_prop_id_" . $personTypeId);
    }

    public static function getCorpPropId($personTypeId)
    {
        return self::getDataValue("corp_prop_id_" . $personTypeId);
    }

    public static function getFlatPropId($personTypeId)
    {
        return self::getDataValue("flat_prop_id_" . $personTypeId);
    }

    public static function getPvzPropId($personTypeId)
    {
        return self::getDataValue("pvz_prop_" . $personTypeId);
    }

    public static function getSortProfiles()
    {
        return self::getDataValue("sortProfiles");
    }

    public static function getCashPayments()
    {
        $cashPayments = self::getDataValue("cashPaymentsIds");
        return explode("|", $cashPayments);
    }

    public static function isAutomaticUpload()
    {
        $uploadType = self::getDataValue("departure_type");
        return (bool)($uploadType == self::DEPARTURE_TYPE_AUTO);
    }

    public static function getTriggerStatus()
    {
        $triggerStatus = self::getDataValue("departure_status");
        return $triggerStatus;
    }

    public static function getChangeStatus()
    {
        $changeStatus = self::getDataValue("change_status");
        return $changeStatus;
    }

    public static function getBitrixStock()
    {
        $bitrixStockId = self::getDataValue("bitrix_stock");
        return $bitrixStockId;
    }

    public static function getTrackingStatuses()
    {
        $trackingStatuses = self::getDataValue("tracking_map_statuses");
        $arStatuses = unserialize($trackingStatuses);
        return array_flip($arStatuses);
    }

    public static function getTrackingStatusesOrder()
    {
        $trackingStatuses = self::getDataValue("tracking_map_statuses_order");
        $arStatuses = unserialize($trackingStatuses);
        return array_flip($arStatuses);
    }

    public static function getRoundingType()
    {
        return self::getDataValue("rounding_type");
    }

    public static function getRoundingPrecision()
    {
        return self::getDataValue("rounding_precision");
    }

    public static function getCacheMechanism()
    {
        return self::getDataValue("cache_mec");
    }
//    public static function isNativeCache(){
//        $cacheMec = self::getCacheMechanism();
//        return boolval($cacheMec == self::CACHE_MEC_NATIVE);
//    }
//    public static function isModuleCache(){
//        $cacheMec = self::getCacheMechanism();
//        return boolval($cacheMec == self::CACHE_MEC_MODULE);
//    }
    public static function getCheckoutUrl()
    {
        return self::getDataValue("checkout");
    }

//    public static function getDefaultStock()
//    {
//        return self::getDataValue("default_stock");
//    }

    public static function getYMapsKey()
    {
        return self::getDataValue("ymaps_key");
    }

    public static function getDaDataToken()
    {
        return self::getDataValue("da_data_token");
    }

    public static function getActiveDiscountHoliday()
    {
        return self::getDataValue("active_discount_holiday");
    }

    public static function getPickupDiscountHoliday()
    {
        return self::getDataValue("pickup_discount_holiday");
    }

    public static function getPickpointDiscountHoliday()
    {
        return self::getDataValue("pickpoint_discount_holiday");
    }

    public static function getSdekDiscountHoliday()
    {
        return self::getDataValue("sdek_discount_holiday");
    }

    public static function getFivepostDiscountHoliday()
    {
        return self::getDataValue("fivepost_discount_holiday");
    }

    public static function getCost()
    {
        return self::getDataValue("cost");
    }

    public static function getStartCost()
    {
        return 299;
    }

    public static function getLimitBasket(){
        return 4000;
    }
}