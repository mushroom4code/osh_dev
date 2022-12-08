<?php
namespace PickPoint\DeliveryService\Pickpoint;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Statuses 
 * Handle specific information about statuses 
 *
 * @package PickPoint\DeliveryService\Pickpoint
 */ 
class Statuses 
{		
	/**
     * Get final order statuses
     * 
     * @return array
     */		
	public static function getFinalStatuses()
    {
        return array('11301', '11302');
    }
	
	/**
     * Get allowed states for registry creation
     * 
     * @return array
     */		
	public static function getRegistryStates()
    {
        return array('101', '108');
    }	
	
	/**
     * Checking if current state allow getting a barcode
     * 
	 * @param string $state - PickPoint state
     * @return bool
     */		
	public static function canGetBarcodeCheckByState($state)
    {		
        return in_array($state, array('101', '102', '103', '104'));		
    }	
	
	/**
     * Checking if current visual state allow getting a barcode
     * 
	 * @param string $state - PickPoint visual state
     * @return bool
     */		
	public static function canGetBarcodeCheckByVisualState($visualState)
    {		
        return in_array($visualState, array('10101', '10201', '10202', '10301', '10302', '10401', '10402'));		
    }		
	
	/**
     * Get all available statuses
	 * BEWARE: state 100 has no 'VisualStateCode' param in PP system, only 'State', so it represented as 10000 for uniformity
     * 
     * @return array
     */		
	public static function getStatusMap()
	{
		return array(
			'10000' => ['STATE' => 100, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10000')],
			'10101' => ['STATE' => 101, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10101')],		
			
			'10201' => ['STATE' => 102, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10201')],
			'10202' => ['STATE' => 102, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10202')],
			
			'10301' => ['STATE' => 103, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10301')],
			'10302' => ['STATE' => 103, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10302')],
			
			'10401' => ['STATE' => 104, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10401')],
			'10402' => ['STATE' => 104, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10402')],
			
			'10501' => ['STATE' => 105, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10501')],
			'10502' => ['STATE' => 105, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10502')],
			
			'10601' => ['STATE' => 106, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10601')],
			'10602' => ['STATE' => 106, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10602')],
			'10603' => ['STATE' => 106, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10603')],
			'10604' => ['STATE' => 106, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10604')],
			
			'10701' => ['STATE' => 107, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10701')],
			'10801' => ['STATE' => 108, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10801')],

			'10901' => ['STATE' => 109, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10901')],
			'10902' => ['STATE' => 109, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_10902')],

			'11001' => ['STATE' => 110, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11001')],
			'11002' => ['STATE' => 110, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11002')],
			'11003' => ['STATE' => 110, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11003')],
			'11004' => ['STATE' => 110, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11004')],

			'11101' => ['STATE' => 111, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11101')],
			'11201' => ['STATE' => 112, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11201')],
			
			'11301' => ['STATE' => 113, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11301')],
			'11302' => ['STATE' => 113, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11302')],
			
			'11401' => ['STATE' => 114, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11401')],
			'11402' => ['STATE' => 114, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11402')],
			
			'11501' => ['STATE' => 115, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11501')],
			'11502' => ['STATE' => 115, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11502')],
			
			'11601' => ['STATE' => 116, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11601')],
			'11602' => ['STATE' => 116, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11602')],

			'11801' => ['STATE' => 118, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_11801')],
			'12801' => ['STATE' => 128, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_12801')],
			'13101' => ['STATE' => 131, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_13101')],
			'13701' => ['STATE' => 137, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_13701')],
			'13901' => ['STATE' => 139, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_13901')],
			'14201' => ['STATE' => 142, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_14201')],
			'14701' => ['STATE' => 147, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_14701')],
			'14801' => ['STATE' => 148, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_14801')],
			'15001' => ['STATE' => 150, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_15001')],

			'15201' => ['STATE' => 152, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_15201')],
			'15202' => ['STATE' => 152, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_15202')],

			'15301' => ['STATE' => 153, 'VISUALSTATE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_OPT_status_15301')],		
		);		
	}
} 