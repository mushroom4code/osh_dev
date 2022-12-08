<?php
namespace PickPoint\DeliveryService\Bitrix;

use \PickPoint\DeliveryService\Pickpoint\Handler\Enumerations;
use \Bitrix\Main\Localization\Loc;

/**
 * Class Adapter
 * @package PickPoint\DeliveryService\Bitrix 
 */
class Adapter
{
	/**
     * Get courier call time intervals	 
     * 
     * @return array
     */		
	public static function getCourierTimeIntervals()
	{
		$intervals = Enumerations::getCourierTimeIntervals();        
		$result = array();
        foreach ($intervals as $key => $val) {
            $result[$key] = Loc::getMessage('PICKPOINT_DELIVERYSERVICE_COURIER_TIME_INTERVAL_'.$key);
        }
        return $result;	
	}
	
	/**
     * Get getting types variants
     * 
     * @return array
     */		
	public static function getGettingTypes()
	{
		$gettingTypes = Enumerations::getGettingTypes();
		$result = array();
        foreach ($gettingTypes as $key => $val) {
            $result[$val] = Loc::getMessage('PICKPOINT_DELIVERYSERVICE_VARIANT_getting_type_'.$val);
        }
        return $result;
	}
	
	/**
     * Get postage types variants
     * 
     * @return array
     */		
	public static function getPostageTypes()
	{
		$postageTypes = Enumerations::getPostageTypes();
		$result = array();
        foreach ($postageTypes as $key => $val) {
            $result[$val] = Loc::getMessage('PICKPOINT_DELIVERYSERVICE_VARIANT_postage_type_'.$val);
        }
        return $result;
	}
}