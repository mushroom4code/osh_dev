<?php
namespace PickPoint\DeliveryService\Bitrix\Handler;

/**
 * Class Statuses - handle Bitrix order and shipment statuses
 *
 * @package PickPoint\DeliveryService
 */
class Statuses
{ 
	/**
     * Order statuses getter
     * 
     * @return array
     */	
	public static function getOrderStatuses()
	{
        return self::getDBStatuses(array('TYPE' => 'O', 'LID' => 'ru'));
    }

	/**
     * Shipment statuses getter
     * 
     * @return array
     */	
    public static function getShipmentStatuses()
	{
        return self::getDBStatuses(array('TYPE' => 'D', 'LID' => 'ru'));
    }

	/**
     * Get statuses from BX DB
     * 
     * @return array
     */	
    protected static function getDBStatuses($arFilter = array())
	{
		$arStatuses = array();
		
        $dbStatuses = \CSaleStatus::GetList(array('SORT' => 'asc'), $arFilter, false, false, array('ID', 'TYPE', 'NAME'));
		while ($arStatus = $dbStatuses->Fetch()) {
            $arStatuses[$arStatus['ID']] = $arStatus['NAME']." [{$arStatus['ID']}]";
        }
		
		return $arStatuses;
    }
}