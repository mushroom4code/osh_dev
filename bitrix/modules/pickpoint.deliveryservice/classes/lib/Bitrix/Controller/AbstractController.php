<?php
namespace PickPoint\DeliveryService\Bitrix\Controller;

/**
 * Class AbstractController
 * @package PickPoint\DeliveryService
 */
class AbstractController
{
    protected static $MODULE_ID  = PICKPOINT_DELIVERYSERVICE;
	protected static $MODULE_LBL = PICKPOINT_DELIVERYSERVICE_LBL;    
    
    public function __construct()
    {
    }
	
	/**
     * @return string
     */
    public static function getMID()
    {
        return self::$MODULE_ID;
    }
	
	/**
     * @return string
     */
    public static function getMLBL()
    {
        return self::$MODULE_LBL;
    }   
}