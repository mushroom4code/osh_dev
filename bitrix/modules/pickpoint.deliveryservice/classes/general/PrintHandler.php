<?php
namespace PickPoint\DeliveryService;

use \PickPoint\DeliveryService\Bitrix\Controller\Printer;

/**
 * Class PrintHandler
 * @package PickPoint\DeliveryService
 */
class PrintHandler extends AbstractGeneral
{ 
	/**
	 * Request barcodes file
	 *
     * @param array $request  
     */
	public static function getBarcodesRequest($request)
    {
		if (!isset($request['invoices'])) {
			echo json_encode(array('success' => false, 'url' => false));
			return;
		}
		
        $result = self::getBarcodes($request['invoices']);

        echo json_encode(array('success' => boolval($result), 'url' => $result));
    }	
	
	/**
	 * Get barcodes file 
	 *
	 * @param array $invoices
     * @return string
     */	
    public static function getBarcodes($invoices)
    {
        $controller = new Printer();
		
        return $controller->getBarcodes($invoices);
    }

	/**
	 * Delete old files	 
	 *
	 * @param string $prefix
	 * @param int $lifetime in seconds
     */
    public static function unmakeOldFiles($prefix = '', $lifetime = 3600)
    {
        $path  = Printer::getFilePath();		
        $files = scandir($path);
		$time  = time();
		
        foreach ($files as $file) {
			if (in_array($file, array(".", "..")))
				continue;
			
			if ($prefix && strpos($file, $prefix) === false)
				continue;			
			
			$filePath = $path.$file;
            if (is_dir($filePath)) 
				continue;
			            
            if ($time - filectime($filePath) > $lifetime)
				unlink($filePath);            
        }
    }
}