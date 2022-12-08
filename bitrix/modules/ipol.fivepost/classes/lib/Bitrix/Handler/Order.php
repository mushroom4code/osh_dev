<?php
namespace Ipol\Fivepost\Bitrix\Handler;


class Order
{
    protected static $cache;

    protected static function setCache()
    {
        if(!isset(self::$cache))
        {
            self::$cache = array('orders'=>array());
        }
    }

    /**
     * @param $bitrixId
     * @return null|\Bitrix\Sale\Order
     */
    public static function getOrderById($bitrixId)
    {
        self::setCache();

        if(!array_key_exists($bitrixId,self::$cache['orders']))
        {
            self::$cache['orders'][$bitrixId] = self::getOrderByIdEx($bitrixId);
        }

        return self::$cache['orders'][$bitrixId];
    }

    public static function getOrderByIdEx($bitrixId)
    {
        return \Bitrix\Sale\Order::load($bitrixId);
    }


    public static function getOrderNumber($bitrixId)
    {
        $orderNumber = false;

        if ($order = self::getOrderById($bitrixId)) {
            $orderNumber = $order->getField('ACCOUNT_NUMBER');
        }

        if (!$orderNumber) {
            $orderNumber = $bitrixId;
        }

        return $orderNumber;
    }

    public static function getOrderIdFromNumber($number)
    {
        $orderId = false;

        $order = \Bitrix\Sale\Order::loadByAccountNumber($number);
        if($order) {
            $orderId = $order->getId();
        }

        return $orderId;
    }

    public static function getOrderDate($bitrixId,$full=false)
    {
        $orderDate = self::getOrderById($bitrixId)->getField('DATE_INSERT');
        $orderDateFormat = $orderDate->toString();
        $orderDateFormat = trim(substr($orderDate,0,strpos($orderDate,' ')));
        $orderDateTimestapm = $orderDate->getTimestamp();

        return ($full) ? array('timestamp'=>$orderDateTimestapm,'sign'=>$orderDateFormat) : $orderDateFormat;
    }

    public static function markPayed($bitrixId)
    {
        $order = self::getOrderById($bitrixId);
        if($order && is_object($order)) {
            $paymentCollection = $order->getPaymentCollection();
            foreach ($paymentCollection as $payment) {
                if (!$payment->isPaid()) {
                    $payment->setPaid("Y");
                    $order->save();
                }
            }
        }
    }

    public static function addTracking($bitrixId,$tracking)
    {
        \CSaleOrder::Update($bitrixId,array('TRACKING_NUMBER'=>$tracking));
    }
}