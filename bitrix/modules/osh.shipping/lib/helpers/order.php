<?php
namespace Osh\Delivery\Helpers;

use Bitrix\Sale\Internals\ShipmentTable,
    \Osh\Delivery\Logger;

class Order{
    public static function getByShipment($arParams = array()){
        $oContext = \Bitrix\Main\Context::getCurrent();
        $lang = $oContext->getLanguage();
        $params = array(
            'select' => array('ID','ORDER_ID','DELIVERY_DOC_NUM', 'DELIVERY_ID', 'TRACKING_NUMBER','TRACKING_DESCRIPTION','STATUS_ID', 'XML_ID'),
            'filter' => array(
                '=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => $lang,
                'DELIVERY_ID' => \COshDeliveryHelper::getDeliveries(),
                '!SYSTEM' => 'Y'
            ),
            'order' => array('ID' => 'DESC')
        );
        if(!empty($arParams['filter'])){
            $params['filter'] = array_merge($params['filter'], $arParams['filter']);
        }
        if(!empty($arParams['select'])){
            $params['select'] = $arParams['select'];
        }
        if(!empty($arParams['limit'])){
            $params['limit'] = $arParams['limit'];
        }
        $arShipments = ShipmentTable::getList($params)->fetchAll();
        return $arShipments;
    }
    public static function getShipment($order){
        $shipmentCollection = $order->getShipmentCollection();
        foreach($shipmentCollection as $shipment){
            if($shipment->isSystem()){
                continue;
            }
        }
        return $shipment;
    }
    public static function setPropertyValue(&$property, $value){
        if(!empty($property)){
            $property->setValue($value);
            return true;
        }else{
            return false;
        }
    }
    public static function getProperty($order, $propertyId){

        $propertyCollection = $order->getPropertyCollection();
        $property = $propertyCollection->getItemByOrderPropertyId($propertyId);

        return $property;
    }
    public static function setStatus(&$shipment, $status){
        $arRes = self::getStatusData($status);
        if($arRes !== null){
            if($arRes['TYPE'] == 'D'){
                $shipment->setField('STATUS_ID', $status);
            }else{
                $shipment->getCollection()->getOrder()->setField('STATUS_ID', $status);
            }
        }
    }
    public static function getStatusData($status){
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $dbRes = \Bitrix\Sale\Internals\StatusTable::getList(array(
                'select' => array('ID', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME', 'TYPE'),
                'filter' => array(
                    'ID' => $status,
                    '=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => $context->getLanguage(),
                ),
                'order' => array('SORT' => 'ASC')
        ));
        if($arRes = $dbRes->fetch()){
            return $arRes;
        }
        return null;
    }
}