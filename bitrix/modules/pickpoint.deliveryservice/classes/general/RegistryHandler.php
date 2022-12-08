<?php
namespace PickPoint\DeliveryService;

use \PickPoint\DeliveryService\Bitrix\Tools;
use \PickPoint\DeliveryService\Bitrix\Controller\Printer;
use \PickPoint\DeliveryService\Bitrix\Adapter\Registry as RegistryAdapter;
use \PickPoint\DeliveryService\RegistryTable;
use \PickPoint\DeliveryService\OrderTable;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class RegistryHandler
 * @package PickPoint\DeliveryService
 */
class RegistryHandler extends AbstractGeneral
{ 
	/**
	 * Make Registry request
	 *
     * @param array $request  
	 * @return bool
     */
	public static function makeRegistryRequest($request)
    {	
		if ($request[self::getMLBL().'action'])
			$request = Tools::encodeFromUTF($request);
        
		$adapter = new RegistryAdapter();
		$preparedData = $adapter->prepareRequest($request);
		
		$result = self::makeRegistry($preparedData);		
		
		if ($result['success'] == 'Y') {
			$invoice = $preparedData['Invoices'][0];
			$getNumResult = self::getRegistryNumber($invoice); 
			
			$number = '';
			if (empty(trim($getNumResult['ErrorMessage'])) && isset($getNumResult['Number']))
				$number = $getNumResult['Number'];
			
			$res = RegistryTable::add([
				'REGISTRY_NUMBER'    => $number,
				'DATE'               => new DateTime(),
				'GETTING_TYPE'       => $preparedData['GettingType'],
				'IKN'                => $preparedData['IKN'],
				'TRANSFER_CITY'      => $preparedData['CityName'],
				'TRANSFER_CITY_LINK' => 0, // For future using
				'FILENAME'           => $result['result']
				]);
			
			if ($res->isSuccess()) {
				$registryID = $res->getId();				
				foreach ($preparedData['OrderIDs'] as $id) {
					$resOrder = OrderTable::update($id, ['REGISTRY_ID' => $registryID]);					
				}				
				
				if ($request[self::getMLBL().'action'])
					echo json_encode(Tools::encodeToUTF(array('success' => 'Y', 'registryNumber' => $number, 'url' => $result['result'])));
				return true;
			} else {
				$errors = array_merge([Loc::getMessage('PICKPOINT_DELIVERYSERVICE_ERROR_REGISTRY_NOTSAVED')], $res->getErrorMessages());
				if ($request[self::getMLBL().'action'])
					echo json_encode(Tools::encodeToUTF(array('success' => 'N', 'error' => $errors)));
				return false;
			}			
		} else {
			$errors = $result['result'];
            if (!is_array($errors))
                $errors = array($errors);
			
			if ($request[self::getMLBL().'action'])
                echo json_encode(Tools::encodeToUTF(array('success' => 'N', 'error' => $errors)));
            return false;			
		}	
    }	
	
	/**
	 * Make Registry
	 *
	 * @param array $data
     * @return array
     */	
    public static function makeRegistry($data)
    {
        $controller = new Printer();
		
        return $controller->makeRegistry($data);
    }
	
	/**
	 * Get registry number for invoice
	 *
	 * @param string $invoice
     * @return array
     */	
    public static function getRegistryNumber($invoice)
    {
        $controller = new Printer();
		
        return $controller->getRegistryNumber($invoice);
    }

	/**
	 * Delete old registers from DB
     */
	public static function unmakeOldRegisters()
	{
		$date = new DateTime();
		//$date->add("-1 day");
		$date->add("-3 months");
		
		$registers = RegistryTable::getList(['select' => ['ID'], 'filter' => ["<DATE" => $date]])->fetchAll();
		foreach ($registers as $registry) {
			$res = RegistryTable::delete($registry['ID']);
		}	
	}
	
	/**
	 * Get cities for create registry page
	 * BEWARE: legacy code inside
	 * @deprecated
	 *
     * @return array
     */
	public static function getRegistryCityList()
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
	 * Get city ID by name 
	 * BEWARE: legacy code inside
	 * @deprecated
	 *
     * @return mixed
     */
	public static function getRegistryCityByName($city)
	{
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::getMID().'/cities.csv', 'r');
		$arCities = array();

		while ($sStr = fgets($file)) {
			$arStr = explode(';', $sStr);			
			if ('true' === trim($arStr[4])) {
				if (trim($arStr[1]) == $city) {
					return trim($arStr[0]);
				}
			}
		}
		
		return false;		
	}
}