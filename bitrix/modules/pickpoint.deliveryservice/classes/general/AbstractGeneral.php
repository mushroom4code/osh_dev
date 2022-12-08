<?php
namespace PickPoint\DeliveryService;

/**
 * Class AbstractGeneral
 * @package PickPoint\DeliveryService
 */
class AbstractGeneral
{
    protected static $MODULE_ID  = 'pickpoint.deliveryservice';
	protected static $MODULE_LBL = 'PICKPOINT_DELIVERYSERVICE_';

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