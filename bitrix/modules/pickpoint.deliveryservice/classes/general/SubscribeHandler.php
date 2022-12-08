<?php
namespace PickPoint\DeliveryService;

/**
 * Class SubscribeHandler
 * @package PickPoint\DeliveryService
 */
class SubscribeHandler extends AbstractGeneral
{ 
	/**
	 * Router for AJAX calls
	 *
	 * @param string $action method to call
     */	
    public static function getAjaxAction($action)
	{	
        if (method_exists('\PickPoint\DeliveryService\PrintHandler', $action))
            \PickPoint\DeliveryService\PrintHandler::$action($_POST);
        else if (method_exists('\PickPoint\DeliveryService\StatusHandler', $action))
            \PickPoint\DeliveryService\StatusHandler::$action($_POST);
		else if (method_exists('\PickPoint\DeliveryService\CourierHandler', $action))
            \PickPoint\DeliveryService\CourierHandler::$action($_POST);
		else if (method_exists('\PickPoint\DeliveryService\RegistryHandler', $action))
            \PickPoint\DeliveryService\RegistryHandler::$action($_POST);
    }
}