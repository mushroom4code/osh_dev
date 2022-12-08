<?php
namespace PickPoint\DeliveryService;

use \PickPoint\DeliveryService\Bitrix\Tools;
use \PickPoint\DeliveryService\Bitrix\Controller\Courier;
use \PickPoint\DeliveryService\Bitrix\Adapter\Courier as CourierAdapter;
use \PickPoint\DeliveryService\CourierTable;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CourierHandler
 * @package PickPoint\DeliveryService
 */
class CourierHandler extends AbstractGeneral
{ 
	/**
	 * Call Courier request
	 *
     * @param array $request  
	 * @return bool
     */
	public static function courierCallRequest($request)
    {	
		if ($request[self::getMLBL().'action'])
			$request = Tools::encodeFromUTF($request);
        
		$adapter = new CourierAdapter();
		$preparedData = $adapter->prepareRequest($request);
		
		$result = self::getCourierCall($preparedData);
		
		if (!$result['CourierRequestRegistred']) {
			$errors = $result['ErrorMessage'];
            if (!is_array($errors))
                $errors = array($errors);
			
			if ($request[self::getMLBL().'action'])
                echo json_encode(Tools::encodeToUTF(array('success' => 'N', 'error' => $errors)));
            return false;
		} else {
			$res = CourierTable::add([
				'ORDER_NUMBER' => $result['OrderNumber'],
				'IKN'          => $preparedData['IKN'],				
				'CITY'         => $preparedData['City'],
				'CITY_LINK'    => 0, // For future using
				'ADDRESS'      => $preparedData['Address'],
				'FIO'          => $preparedData['FIO'],
				'PHONE'        => $preparedData['Phone'],
				'DATE'         => new DateTime($preparedData['Date'], 'Y.m.d'),
				'TIME_START'   => $preparedData['TimeStart'],
				'TIME_END'     => $preparedData['TimeEnd'],
				'NUMBER'       => $preparedData['Number'],
				'WEIGHT'       => $preparedData['Weight'],
				'COMMENT'      => $preparedData['Comment'],
				]);
			
			if ($res->isSuccess()) {
				if ($request[self::getMLBL().'action'])
					echo json_encode(Tools::encodeToUTF(array('success' => 'Y', 'OrderNumber' => $result['OrderNumber'])));
				return true;
			} else {
				$errors = array_merge([Loc::getMessage('PICKPOINT_DELIVERYSERVICE_ERROR_COURIER_NOTSAVED')], $res->getErrorMessages());
				if ($request[self::getMLBL().'action'])
					echo json_encode(Tools::encodeToUTF(array('success' => 'N', 'error' => $errors)));
				return false;
			}			
		}
    }	
	
	/**
	 * Make courier call 
	 *
	 * @param array $data
     * @return array
     */	
    public static function getCourierCall($data)
    {
        $controller = new Courier();
		
        return $controller->getCourierCall($data);
    }

	/**
	 * Delete old courier calls from DB
     */
	public static function unmakeOldCourierCalls()
	{
		$date = new DateTime();
		//$date->add("-1 day");
		$date->add("-3 months");
		
		$calls = CourierTable::getList(['select' => ['ID'], 'filter' => ["<DATE" => $date]])->fetchAll();
		foreach ($calls as $call) {
			$res = CourierTable::delete($call['ID']);
		}	
	}	
	
	/**
	 * Get cities for courier call form
	 * BEWARE: legacy code inside
	 * @deprecated
	 *
     * @return array
     */
	public static function getCourierCityList()
	{
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::getMID().'/cities.csv', 'r');
		$arCities = array();

		while ($sStr = fgets($file)) {
			$arStr = explode(';', $sStr);			
			if ('true' === trim($arStr[4])) {
				$arCities[trim($arStr[0])] = trim($arStr[1]).', '.trim($arStr[3]);
			}
		}
		
		return $arCities;		
	}	
	
	/**
	 * Return id of Moscow region cities for courier call form
	 * BEWARE: legacy code inside
	 * @deprecated
	 *
     * @return array
     */
	public static function getMoscowRegionCityIDs()
	{
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::getMID().'/cities.csv', 'r');
		$result = array();

		while ($sStr = fgets($file)) {
			$arStr = explode(';', $sStr);			
			if (Loc::getMessage('PICKPOINT_DELIVERYSERVICE_CITY_LIST_MO') === trim($arStr[3]) && Loc::getMessage('PICKPOINT_DELIVERYSERVICE_CITY_LIST_MOSCOW') !== trim($arStr[1])) {
				$result[] = trim($arStr[0]);
			}
		}
		
		return $result;		
	}
}