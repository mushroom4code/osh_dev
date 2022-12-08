<?php
namespace PickPoint\DeliveryService\Bitrix\Controller;

// Legacy for Request object
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';

/**
 * Class Courier 
 * @package PickPoint\DeliveryService\Bitrix\Controller
 */
class Courier extends AbstractController
{	
    public function __construct()
    {
        parent::__construct();
    }
   
	/**
	 * Make courier call 
	 *
	 * @param array $data 	
     * @return array
     */
	public function getCourierCall($data)
	{								
		// Legacy params from /bitrix/modules/pickpoint.deliveryservice/constants.php
        /** @global array $arServiceTypesCodes */
        /** @global array $arOptionDefaults */

		$request = new \PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);			
		$result = $request->courier($data);		
			
		return $result;
	}
}