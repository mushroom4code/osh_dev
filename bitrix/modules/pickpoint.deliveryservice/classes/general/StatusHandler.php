<?php
namespace PickPoint\DeliveryService;

use \PickPoint\DeliveryService\Bitrix\Controller\Status;
use \PickPoint\DeliveryService\Pickpoint\Statuses;
use \PickPoint\DeliveryService\OrderTable;
use \PickPoint\DeliveryService\Option;
use \Bitrix\Main\Type\DateTime;

/**
 * Class StatusHandler
 * @package PickPoint\DeliveryService
 */
class StatusHandler extends AbstractGeneral
{
	/**
	 * Refresh PickPoint and Bitrix order states
	 *
	 * @param string $dateFrom 
	 * @param string $dateTo 
     */	
	public static function refreshOrderStates($dateFrom = false, $dateTo = false)
    {		
        if (Option::get('status_sync_enabled') != 'Y')
			return;
		
		if ($states = self::getStates($dateFrom, $dateTo)) {
			$map = Statuses::getStatusMap();
			
			global $USER;
			if (!is_object($USER)) 
			   $USER = new \CUser();
				
			foreach ($states as $invoiceNumber => $barcodes) {
				foreach ($barcodes as $barcode => $statuses) {
					foreach ($statuses as $status) {
						// State 100 don't have VisualStateCode param
						$newVisualStateCode = ($status['STATE'] == '100') ? '10000' : $status['VISUAL_STATE_CODE'];
					
						if (isset($status['VISUAL_STATE_CODE']) && array_key_exists($newVisualStateCode, $map)) {
							$mappedStatus = $map[$newVisualStateCode];
							
							$ppOrder = OrderTable::getList(['filter' => ['=PP_INVOICE_ID' => $invoiceNumber]])->Fetch();						
							if (!$ppOrder)
								continue;
							
							// This status should already be processed
							if (isset($ppOrder['STATUS_DATE']) && strtotime($ppOrder['STATUS_DATE']) >= $status['TIMESTAMP'])
								continue;													
							
							$result = OrderTable::update($ppOrder['ID'], [
								'STATUS'      => $mappedStatus['STATE'], 
								'STATUS_CODE' => $newVisualStateCode,
								'STATUS_DATE' => new DateTime($status['DATE'], 'd.m.Y H:i:s'),
								]);
								
							if ($result->isSuccess()) {
								// Is status really changes ?
								if ($ppOrder['VISUAL_STATE_CODE'] != $newVisualStateCode) {
									$newBXStatus = Option::get("status_".$newVisualStateCode);
									if ($newBXStatus && strlen($newBXStatus) < 3) {
										$bxOrder = \CSaleOrder::GetByID($ppOrder['ORDER_ID']);
										if ($bxOrder && $bxOrder['STATUS_ID'] != $newBXStatus) {
											$bResult = \CSaleOrder::StatusOrder($ppOrder['ORDER_ID'], $newBXStatus);										
										}								
									}
									
									// onNewStatus module event
									foreach (GetModuleEvents(self::getMID(), "onNewStatus", true) as $arEvent)
										ExecuteModuleEventEx($arEvent, array($ppOrder['ORDER_ID'], $invoiceNumber, $barcode, $status));								
								}							
							}							
						}					
					}
					
					// Handle only one Barcode cause current /CreateShipment module call handle only one Place, so 1 Place = 1 Barcode
					break;
				}			
			}
		}
		
		Option::set('status_last_sync', time());		
    }
	
	/**
	 * Get order states
	 *
	 * @param string $dateFrom 
	 * @param string $dateTo 
     */	
	public static function getStates($dateFrom = false, $dateTo = false)
    {
		$controller = new Status();
		
        return $controller->getStates($dateFrom, $dateTo);
	}	
}