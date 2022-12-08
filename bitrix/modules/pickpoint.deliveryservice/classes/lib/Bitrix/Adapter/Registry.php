<?php
namespace PickPoint\DeliveryService\Bitrix\Adapter;

use \PickPoint\DeliveryService\OrderTable;

/**
 * Class Registry
 * @package PickPoint\DeliveryService\Bitrix\Adapter
 */
class Registry 
{
	public function __construct()
    {
        return $this;
    }
	
	/**
     * Prepare data from create registry grid request
	 * @return array 
     */
    public function prepareRequest($request)
    {				
		// Legacy bullshit
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.PICKPOINT_DELIVERYSERVICE.'/cities.csv', 'r');
		$arCities = array();

		while ($sStr = fgets($file)) {
			$arStr = explode(';', $sStr);			
			if ('true' === trim($arStr[4])) {
				$arCities[trim($arStr[0])] = ["CityName" => trim($arStr[1]), "RegionName" => trim($arStr[3])];
			}
		}
		// --
				
		$invoices = [];
		$orderIDs = [];
		$orders = OrderTable::getList(['select' => ['ID', 'PP_INVOICE_ID'], 'filter' => ["=ID" => $request['orders']]])->fetchAll();
		if (!empty($orders)) {
			foreach ($orders as $val) {
				$invoices[] = $val['PP_INVOICE_ID'];
				$orderIDs[] = $val['ID'];
			}
		}
		
		return array(
			'GettingType' => $request['registry_getting_type'],
			'IKN'         => $request['registry_ikn'],
			'CityName'    => $arCities[$request['registry_transfer_city']]['CityName'],
            'RegionName'  => $arCities[$request['registry_transfer_city']]['RegionName'],
			'Invoices'    => $invoices,
			'OrderIDs'    => $orderIDs,
		);	
	}
}