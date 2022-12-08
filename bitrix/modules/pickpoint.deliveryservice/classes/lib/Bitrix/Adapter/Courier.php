<?php
namespace PickPoint\DeliveryService\Bitrix\Adapter;

use \PickPoint\DeliveryService\Pickpoint\Handler\Enumerations;

/**
 * Class Courier
 * @package PickPoint\DeliveryService\Bitrix\Adapter
 */
class Courier 
{
	public function __construct()
    {
        return $this;
    }
	
	/**
     * Prepare data from courier call form request
	 * @return array 
     */
    public function prepareRequest($request)
    {
		$intervals = Enumerations::getCourierTimeIntervals();
		
		// Legacy bullshit
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.PICKPOINT_DELIVERYSERVICE.'/cities.csv', 'r');
		$arCities = array();

		while ($sStr = fgets($file)) {
			$arStr = explode(';', $sStr);			
			if ('true' === trim($arStr[4])) {
				$arCities[trim($arStr[0])] = trim($arStr[1]);
			}
		}
		// --
		
		return array(
			'IKN'       => $request['courier_ikn'],
            'City'      => $arCities[$request['courier_city']],
            'Address'   => trim($request['courier_address']),
			'FIO'       => trim($request['courier_fio']),
            'Phone'     => trim($request['courier_phone']),
            'Date'      => \FormatDate('Y.m.d', strtotime($request['courier_date'])), // YYYY.MM.DD
            'TimeStart' => $intervals[$request['courier_time']]['timeStart'], // minutes from 00:00
            'TimeEnd'   => $intervals[$request['courier_time']]['timeEnd'], // minutes from 00:00
            'Number'    => intval(trim($request['courier_number'])),
            'Weight'    => intval(trim($request['courier_weight'])),
            'Comment'   => trim($request['courier_comment']),		
		);	
	}
}