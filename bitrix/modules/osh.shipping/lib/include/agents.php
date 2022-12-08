<?php

namespace Osh\Delivery;

use Osh\Delivery\OshService,
    Osh\Delivery\Options\Config,
    Osh\Delivery\Helpers\Order,
    Bitrix\Main\Localization\Loc,
    Osh\Delivery\Logger,
    Osh\Delivery\Cache\Cache;

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/osh.shipping/lib/include.php');

class Agents
{
    const MODULE_ID = 'osh.shipping';

    public static function clearKladr()
    {
        OshService::getInstance()->flush();
        return __CLASS__ . "::" . __FUNCTION__ . "();";
    }

    public static function sendOrders()
    {
        global $USER;
        if (!is_object($USER)) {
            $GLOBALS['USER'] = new \CUser();
        }
        $arShipments = Order::getByShipment(array('filter' => array('TRACKING_NUMBER' => null, 'CANCELED' => 'N')));
        $arData2Log = array();
        $isAutomatic = Config::isAutomaticUpload();
        if (!$isAutomatic) {
            return __CLASS__ . "::" . __FUNCTION__ . "();";
        }
        foreach ($arShipments as $arItem) {
            $orderId = $arItem['ORDER_ID'];
            $id = $arItem['ID'];
            if (empty($orderId)) {
                continue;
            }
            $currentOrder = \Bitrix\Sale\Order::load($orderId);
            $shipment = $currentOrder->getShipmentCollection()->getItemById($id);
            if (!$shipment) {
                continue;
            }
            try {
                $errorText = "";
                $oDelivery = $shipment->getDelivery();
                if ($oDelivery->isDirect()) {
                    throw new \Exception(Loc::getMessage("OSH_API_ERROR_NO_DIRECT_SHIPPING"));
                }
                $isExists = !!$orderId;
                if (/*$shipment->getField('XML_ID') ||*/ $shipment->getField('TRACKING_NUMBER')) {
                    throw new \Exception(Loc::getMessage("OSH_API_ERROR_ALREADY_SHIPPED"));
                }
                if (!$shipment->isAllowDelivery()) {
                    throw new \Exception(Loc::getMessage("OSH_API_ERROR_SHIP_NOT_ALLOWED"));
                }
                if ($isExists && strlen($errorText) == 0) {
                    $result = \COshDeliveryHelper::sendOrder($shipment);
                }
            } catch (\Exception $e) {
                $errorText = $e->getMessage();
                Logger::exception($e);
                if (!!$shipment) {
                    $shipment->setField("MARKED", "Y");
                    $shipment->setField("REASON_MARKED", $errorText);
                    $res = $shipment->save();
                }
                $arData2Log[] = "#" . $orderId . ": " . $errorText;
            }
        }
        if (count($arData2Log) > 0) {
            $sDataLog = implode("\r\n", $arData2Log);
            Logger::force($sDataLog);
        }
        return __CLASS__ . "::" . __FUNCTION__ . "();";
    }

    public static function checkOrders()
    {
        return false;
        global $USER;
        if (!is_object($USER)) {
            $GLOBALS['USER'] = new \CUser();
        }
        $arStatuses = Config::getTrackingStatuses();
        $arOrderStatuses = Config::getTrackingStatusesOrder();
        $directDeliveries = \COshDeliveryHelper::getDirectDeliveries();
        $commonDeliveries = \COshDeliveryHelper::getCommonDeliveries();
        $arShipments = Order::getByShipment(array(
            'filter' => array(
                '>=DATE_INSERT' => (new \DateTime('-3 month'))->format('d.m.Y'),
                'CANCELED' => 'N',
                array(
                    'LOGIC' => 'OR',
                    array('!TRACKING_DESCRIPTION' => array('lost', 'returned', 'delivered'), 'DELIVERY_ID' => $directDeliveries),
                    array('!TRACKING_DESCRIPTION' => array('resent', 'lost', 'disbanded', 'recycled', 'removed', 'returned'), 'DELIVERY_ID' => $commonDeliveries),
                    array('TRACKING_DESCRIPTION' => null, 'DELIVERY_ID' => \COshDeliveryHelper::getDeliveries(), '!DELIVERY_DOC_NUM' => null)
                )
            )
        ));
        $arData2Log = array();
        $lastDate = self::getAgentLastDate(__FUNCTION__);
        $arPackages = \COshDeliveryHelper::getStatuses($arShipments, $lastDate);
        //wfDump($arPackages);
        if (empty($arPackages)) {
            Logger::force(Loc::getMessage('OSH_CHECK_AGENT_NO_PACKAGES'));
            return __CLASS__ . "::" . __FUNCTION__ . "();";
        }
        foreach ($arShipments as $arItem) {
            $orderId = $arItem['ORDER_ID'];
            $orderXmlId = $arItem['XML_ID'];
            $id = $arItem['ID'];
            $bIsCommon = in_array($arItem['DELIVERY_ID'], $commonDeliveries);
            $bIsDirect = in_array($arItem['DELIVERY_ID'], $directDeliveries);
            if (empty($orderId)) {
                continue;
            }
            $currentOrder = \Bitrix\Sale\Order::load($orderId);
            $shipmentCollection = $currentOrder->getShipmentCollection();
            $shipment = $shipmentCollection->getItemById($id);
            if (!$shipment) continue;
            $currentPackage = $arPackages[$orderXmlId];
            if (empty($currentPackage)) {
                continue;
            }
            $shipment->setField('TRACKING_DESCRIPTION', $currentPackage['status']);
            //wfDump('Заказ '.$orderId.' статус Шиптор '.$currentPackage['status']);
            //wfDump('Текущий статус заказа '.$currentOrder->getField('STATUS_ID'));
            //wfDump('Ассоциированный статус заказа '.$arOrderStatuses[$currentPackage['status']]);
            if (!empty($arOrderStatuses[$currentPackage['status']]) && $arOrderStatuses[$currentPackage['status']] != $currentOrder->getField('STATUS_ID')) {
                $isOrderSave = true;
                //wfDump('Установка статуса заказа');
                \Osh\Delivery\Helpers\Order::setStatus($shipment, $arOrderStatuses[$currentPackage['status']]);
            } else {
                $isOrderSave = false;
            }
            //wfDump('Текущий статус отгрузки '.$shipment->getField('STATUS_ID'));
            //wfDump('Ассоциированный статус отгрузки '.$arStatuses[$currentPackage['status']]);
            if (!empty($arStatuses[$currentPackage['status']]) && $arStatuses[$currentPackage['status']] != $shipment->getField('STATUS_ID')) {
                //wfDump('Установка статуса отгрузки');
                \Osh\Delivery\Helpers\Order::setStatus($shipment, $arStatuses[$currentPackage['status']]);
            }
            $res = $shipment->save();
            if ($isOrderSave) {
                $shipment->getCollection()->getOrder()->save();
            }
            if ($res) {
                $arData2Log[] = Loc::getMessage("OSH_ORDER_CHANGED_STATUS", array("#ID#" => $orderId, "#STATUS#" => $currentPackage["status"]));
            } else {
                $arData2Log[] = Loc::getMessage('OSH_CHECK_AGENT_SHIPMENT_SAVE_FAIL', array('#ID#' => $orderId));
            }
        }
        if (count($arData2Log) > 0) {
            $sDataLog = implode("\r\n", $arData2Log);
            Logger::force($sDataLog);
        }
        return __CLASS__ . "::" . __FUNCTION__ . "();";
    }

    public static function importWares()
    {
        $page = 1;
        $processed = 0;
        try {
            do {
                $wareData = \Osh\Delivery\Helpers\Ware::getProductsQuantity($page);
                $page++;
                if ($wareData === false) {
                    break;
                } else {
                    $processed += count($wareData);
                }
                usleep(500000);
            } while ($wareData !== false);
            Logger::info(Loc::getMessage('OSH_UPDATED_WARES', array('#NUM_WARES#' => $processed)));
        } catch (\Exception $e) {
            Logger::info(Loc::getMessage('OSH_UPDATED_WARES_FAIL', array('#NUM_WARES#' => $processed)));
        }
        return __CLASS__ . "::" . __FUNCTION__ . "();";
    }

    public static function createCheckOrders()
    {
        global $DB, $USER;
        $found = self::getCheckOrders();
        if (!$found) {
            $date2Run = date($DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")), strtotime("+1day 01:30:00"));
            $agentId = \CAgent::AddAgent("\Osh\Delivery\Agents::checkOrders();", self::MODULE_ID, "N", Cache::DAY, $date2Run, "Y", $date2Run, 100, $USER->GetID());
        } else {
            $agentId = $found["ID"];
            if (!$found["USER_ID"]) {
                \CAgent::Update($found["ID"], array("USER_ID" => $USER->GetID()));
            }
        }
        return $agentId;
    }

    public static function createSendOrders($forceActive = null)
    {
        global $DB, $USER;
        $found = self::getSendOrders();
        if ($forceActive === true) {
            $active = 'Y';
        }
        if ($forceActive === false) {
            $active = 'N';
        }
        if (!$found) {
            $date2Run = date($DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")), strtotime("+1day 01:50:00"));
            $agentId = \CAgent::AddAgent("\Osh\Delivery\Agents::sendOrders();", self::MODULE_ID, "N", Cache::DAY, $date2Run, $active, $date2Run, 100, $USER->GetID());
        } else {
            $agentId = $found["ID"];
            $arUpdateAgent = [];
            if (!$found["USER_ID"]) {
                $arUpdateAgent = array("USER_ID" => $USER->GetID());
            }
            if (isset($active)) {
                $arUpdateAgent['ACTIVE'] = $active;
            }
            if (!empty($arUpdateAgent)) {
                \CAgent::Update($found["ID"], $arUpdateAgent);
            }
        }
        return $agentId;
    }

    public static function createImportWares()
    {
        global $DB, $USER;
        $found = self::getImportWares();
        if (!$found) {
            $date2Run = date($DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")), strtotime("+1day 03:00:00"));
            $agentId = \CAgent::AddAgent("\Osh\Delivery\Agents::importWares();", self::MODULE_ID, "N", Cache::DAY, $date2Run, "Y", $date2Run, 100, $USER->GetID());
        } else {
            $agentId = $found["ID"];
            if (!$found["USER_ID"]) {
                \CAgent::Update($found["ID"], array("USER_ID" => $USER->GetID()));
            }
        }
        return $agentId;
    }

    public static function getCheckOrders()
    {
        global $DB;
        $found = $DB->Query("select ID, ACTIVE, USER_ID from b_agent where MODULE_ID='" . self::MODULE_ID . "' and NAME like '%Agents::checkOrders();'")->Fetch();
        return $found;
    }

    public static function getSendOrders()
    {
        global $DB;
        $found = $DB->Query("select ID, ACTIVE, USER_ID from b_agent where MODULE_ID='" . self::MODULE_ID . "' and NAME like '%Agents::sendOrders();'")->Fetch();
        return $found;
    }

    public static function getImportWares()
    {
        global $DB;
        $found = $DB->Query("select ID, ACTIVE, USER_ID from b_agent where MODULE_ID='" . self::MODULE_ID . "' and NAME like '%Agents::importWares();'")->Fetch();
        return $found;
    }

    public static function createClearKladr()
    {
        global $DB, $USER;
        $found = $DB->Query("select ID, ACTIVE, USER_ID from b_agent where MODULE_ID='" . self::MODULE_ID . "' and NAME like '%Agents::clearKladr();'")->Fetch();
        if (!$found) {
            $date2Run = date($DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")), strtotime("+1day 01:40:00"));
            $agentId = \CAgent::AddAgent("\Osh\Delivery\Agents::clearKladr();", self::MODULE_ID, "N", Cache::MONTH, $date2Run, "Y", $date2Run, 100, $USER->GetID());
        } else {
            $agentId = $found["ID"];
            if (!$found["USER_ID"]) {
                \CAgent::Update($found["ID"], array("USER_ID" => $USER->GetID()));
            }
        }
        return $agentId;
    }

    public static function createIfNone()
    {
        self::createCheckOrders();
        self::createSendOrders();
        self::createClearKladr();
    }

    public static function getAgentLastDate($agentName)
    {
        global $DB;
        $found = $DB->Query("select LAST_EXEC from b_agent where MODULE_ID='" . self::MODULE_ID . "' and NAME like '%Agents::{$agentName}();'")->Fetch();
        if ($found['LAST_EXEC']) {
            return $found['LAST_EXEC'];
        }
        return false;
    }
}