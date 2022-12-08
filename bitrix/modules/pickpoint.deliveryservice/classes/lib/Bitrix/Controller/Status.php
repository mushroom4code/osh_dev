<?php
namespace PickPoint\DeliveryService\Bitrix\Controller;

// Legacy for Request object
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';

/**
 * Class Status 
 * @package PickPoint\DeliveryService\Bitrix\Controller
 */
class Status extends AbstractController
{	
    public function __construct()
    {
        parent::__construct();
    }
	
	/**
	 * Get order states from PickPoint
	 *
	 * @param string $dateFrom 
	 * @param string $dateTo 
	 *
     * @return array, structure [array of InvoiceNumbers][array of BarCodes][array of States]
     */
	public function getStates($dateFrom = false, $dateTo = false)
	{
		$result = array();		
				
		if (strtotime($dateFrom) === false)
			$dateFrom = false;
		if (strtotime($dateTo) === false)
			$dateTo = false;				
				
		$time     = time();						
		$dateFrom = ($dateFrom) ? $dateFrom : date('d.m.y H:i', $time - 3600);
		$dateTo   = ($dateTo) ? $dateTo : date('d.m.y H:i', $time);
		
		// Legacy params from /bitrix/modules/pickpoint.deliveryservice/constants.php
        /** @global array $arServiceTypesCodes */
        /** @global array $arOptionDefaults */

		$request = new \PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);			
		$result = $request->getInvoicesChangeState($dateFrom, $dateTo);
			
		return $result;		
	}		
}