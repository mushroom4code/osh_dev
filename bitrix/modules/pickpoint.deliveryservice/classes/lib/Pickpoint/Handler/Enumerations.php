<?php
namespace PickPoint\DeliveryService\Pickpoint\Handler;

/**
 * Class Enumerations - some PP-specific information
 *
 * @package PickPoint\DeliveryService\Pickpoint\Handler 
 */
class Enumerations
{
	/**
     * Get courier call time intervals	 
     * 
     * @return array
     */	
    public static function getCourierTimeIntervals()
    {
        return array(
			'9-18'  => ['timeStart' => 540, 'timeEnd' => 1080],
			'9-14'  => ['timeStart' => 540, 'timeEnd' => 840],
			'14-18' => ['timeStart' => 840, 'timeEnd' => 1080],			
		);
    }
	
	/**
     * Return GettingType variants
     * 
     * @see https://pickpoint.ru/sales/api/#_Toc24018639
     * @return array
     */	
    public static function getGettingTypes()
    {
        return array(101, 102, 103, 104);
    }
	
	/**
     * Return PostageType variants
     * 
     * @see https://pickpoint.ru/sales/api/#_Toc24018639
     * @return array
     */	
    public static function getPostageTypes()
    {		
        return array(10001, 10003, 10002, 10004);
    }
}