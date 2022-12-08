<?php
namespace PickPoint\DeliveryService\Bitrix\Controller;

use \Bitrix\Main\Text\BinaryString;

// Legacy for Request object
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';

/**
 * Class Printer 
 * @package PickPoint\DeliveryService\Bitrix\Controller
 */
class Printer extends AbstractController
{	
    public function __construct()
    {
        parent::__construct();
    }
   
	/**
	 * Make Registry, return registry file
	 *
	 * @param array $data
     * @return array
     */
	public function makeRegistry($data)
	{				
		// Legacy params from /bitrix/modules/pickpoint.deliveryservice/constants.php
        /** @global array $arServiceTypesCodes */
        /** @global array $arOptionDefaults */

		$request = new \PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);
		$result = $request->makeReestr($data);
				
		if (BinaryString::getSubstring($result, 0, 4) == '%PDF') {
			return ['success' => 'Y', 'result' => $this->saveToFile($result, 'registry_'.$this->makeHash($data['Invoices']).'.pdf')];
		} else {
			return ['success' => 'N', 'result' => $result];
		}
	}

	/**
	 * Get registry number for invoice
	 *
	 * @param string $invoice
     * @return array
     */
	public function getRegistryNumber($invoice)
	{				
		// Legacy params from /bitrix/modules/pickpoint.deliveryservice/constants.php
        /** @global array $arServiceTypesCodes */
        /** @global array $arOptionDefaults */

		$request = new \PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);
		$result = $request->getReestrNumber($invoice);
				
		return $result;
	}
   
	/**
	 * Get barcode file
	 *
	 * @param array $invoices
     * @return string
     */
	public function getBarcodes($invoices)
	{	
		if (!is_array($invoices))
			$invoices = array($invoices);
						
		// Legacy params from /bitrix/modules/pickpoint.deliveryservice/constants.php
        /** @global array $arServiceTypesCodes */
        /** @global array $arOptionDefaults */

		$request = new \PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);
		$result = $request->makeZLabel($invoices);
			
		return $this->saveToFile($result, 'barcode_'.$this->makeHash($invoices).'.pdf');
	}	   

    /**
	 * Make and return files upload directory
	 *
	 * @param bool $noDocumentRoot
     * @return string
     */	
    public static function getFilePath($noDocumentRoot = false)
    {
		$uploadPath = '/upload/'.self::getMID();
		
        if (!file_exists($_SERVER['DOCUMENT_ROOT'].$uploadPath))
            mkdir($_SERVER['DOCUMENT_ROOT'].$uploadPath);
        
        if (!file_exists($_SERVER['DOCUMENT_ROOT'].$uploadPath.'/print/'))
            mkdir($_SERVER['DOCUMENT_ROOT'].$uploadPath.'/print/');
        
        return (($noDocumentRoot) ? '' : $_SERVER['DOCUMENT_ROOT']).$uploadPath.'/print/';
    }
	
	/**
	 * Save data to file
	 *
	 * @param mixed $data
	 * @param string $hash
     * @return string
     */		
    protected function saveToFile($data, $hash)
    {
        file_put_contents(self::getFilePath().$hash, $data);

        return self::getFilePath(true).$hash;
    }

    /**
     * Make hash which used as filename
     * 
     * @param array|int $ids
     * @return string     
     */
    protected function makeHash($ids)
    {
		if (!is_array($ids))
			$ids = array($ids);		
		
        return md5(implode('|', $ids).time());
    }
}