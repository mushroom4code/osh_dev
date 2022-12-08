<?php
namespace PickPoint;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;

use \PickPoint\DeliveryService\OrderTable;
	
/**
 * Class Request
 * @package PickPoint
 */
class Request extends Sql
{
    const MODULE_ID = 'pickpoint.deliveryservice';
    const FLOAT_NUMBER_FORMAT = 2;

    private $sessionId;
    private $arServiceTypesCodes;
    private $arOptionDefaults;	

    /**
     * Request constructor
     * @param array $arServiceTypesCodes
     * @param array $arOptionDefaults
     * @param array $arEnclosingTypesCodes - deprecated and useless now
     * @throws SystemException
     */
    public function __construct($arServiceTypesCodes, $arOptionDefaults, $arEnclosingTypesCodes = array())
    {
        $this->sessionId = $this->login();

        // const
        $this->arOptionDefaults = $arOptionDefaults;
        $this->arServiceTypesCodes = $arServiceTypesCodes;        
        // ---
    }

    /**
     * Request destructor
     * @throws SystemException
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Makes request to PickPoint API
     * @param string $method
     * @param array $arQuery
     * @return mixed
     * @throws SystemException
     */
    private function query($method, $arQuery, $raw = false)
    {
        $MODULE_ID = self::MODULE_ID;

        $bpp_test_mode = Option::get($MODULE_ID, 'pp_test_mode', '');
        if ($bpp_test_mode) {
            $apiUrl = '/apitest/';
        } else {
            $apiUrl = '/api/';
        }

        if (!(defined('BX_UTF') && BX_UTF == true)) {
            $arQuery = Encoding::convertEncoding($arQuery, 'windows-1251', 'utf-8');
        }

        $httpClient = new HttpClient();
        $httpClient->setHeader('Content-Type', 'application/json', true);

        $response = $httpClient->post('https://e-solution.pickpoint.ru'.$apiUrl.$method, json_encode($arQuery));

		if (!$raw)
			$response = json_decode($response, true);			

        //\Bitrix\Main\Diag\Debug::WriteToFile([$method, $arQuery, $response], __FUNCTION__, '__PickPoint_Debug.log');
		
        return $response;
    }

    /**
     * Do auth and returns sessionId if success
     * @return string|false
     * @throws SystemException
     */
    private function login()
    {
        $login = Option::get(self::MODULE_ID, 'pp_api_login', '');
        $password = Option::get(self::MODULE_ID, 'pp_api_password', '');

        $arQuery = array(
            'Login' => $login,
            'Password' => $password,
        );
        $response = self::query('login', $arQuery);

        if (!$response['ErrorMessage'] && $response['SessionId']) {
            return $response['SessionId'];
        } else {
            return false;
        }
    }

    /**
     * Do logout
     * @throws SystemException
     */
    private function logout()
    {
        $arQuery = array('SessionId' => $this->sessionId);
        self::query('logout', $arQuery);
    }
	
	/**
	 * Get Zebra barcodes
	 * @param array $invoices	 
     * @return mixed
     */
	public function makeZLabel($invoices)
	{
		$arQuery = array(
            'SessionId' => $this->sessionId,
            'Invoices'  => $invoices,            
        );
		$queryResult = self::query('makeZLabel', $arQuery, true);	
		
		return $queryResult;
	}	
		
	/**
	 * Get invoices states
	 * @param string $dateFrom
	 * @param string $dateTo
     * @return array
     */
	public function getInvoicesChangeState($dateFrom, $dateTo)
	{
		$states = array();
		
		$arQuery = array(
            'SessionId' => $this->sessionId,
            'DateFrom'  => $dateFrom,
            'DateTo'    => $dateTo,            
        );
		$queryResult = self::query('getInvoicesChangeState', $arQuery);
		if (!is_array($queryResult))
			$queryResult = [];
		
		// Sort by ChangeDT ASC
		usort($queryResult, function ($a, $b) {return strcmp(strval(strtotime($a['ChangeDT'])), strval(strtotime($b['ChangeDT'])));});				
		
		foreach ($queryResult as $res) {
			$timestamp = strtotime($res['ChangeDT']);
		
			$states[$res['InvoiceNumber']][$res['BarCode']][] = array(
				'DATE'              => \FormatDate('d.m.Y H:i:s', $timestamp),
				'TIMESTAMP'         => $timestamp,
				'STATE'             => $res['State'],
				'VISUAL_STATE_CODE' => $res['VisualStateCode'],
				'VISIAL_STATE'      => $res['VisualState'],				
				'MESSAGE'           => $res['StateMessage'],
				'COMMENT'           => $res['Comment'],
			);			
		}
		
		return $states;
	}

	/**
	 * Courier call request
	 * @param array $data	 
     * @return mixed
     */
	public function courier($data)
	{
		$arQuery = array(
            'SessionId'  => $this->sessionId,
            'IKN'        => $data['IKN'],
            'City'       => $data['City'],
            'Address'    => $data['Address'],
			'FIO'        => $data['FIO'],
            'Phone'      => $data['Phone'],
            'Date'       => $data['Date'], // YYYY.MM.DD
            'TimeStart'  => $data['TimeStart'], // minutes from 00:00
            'TimeEnd'    => $data['TimeEnd'], // minutes from 00:00
            'Number'     => $data['Number'],
            'Weight'     => $data['Weight'],
            'Comment'    => $data['Comment'],
        );
		$queryResult = self::query('courier', $arQuery);

		return $queryResult;
	}	
	
	/**
	 * Make Reestr request
	 * @param array $data	 
     * @return mixed
     */
	public function makeReestr($data)
	{
		$arQuery = array(
            'SessionId'  => $this->sessionId,
            'CityName'   => $data['CityName'],
            'RegionName' => $data['RegionName'],
            'Invoices'   => $data['Invoices'],			
        );
		$queryResult = self::query('makereestr', $arQuery, true);							
	
		return $queryResult;
	}
	
	/**
	 * Get Reestr Number request
	 * @param string $invoice
     * @return mixed
     */
	public function getReestrNumber($invoice)
	{
		$arQuery = array(
            'SessionId'     => $this->sessionId,
            'InvoiceNumber' => $invoice,            	
        );
		$queryResult = self::query('getreestrnumber', $arQuery);							
	
		return $queryResult;
	}
	
    /**
     * Cancel previously created invoice
     * @param int $invoiceNumber
     * @param int $orderId
     * @return array
     * @throws SystemException
     */
    public function cancelInvoice($invoiceNumber, $orderId)
    {
        $iknNumber = Option::get(self::MODULE_ID, 'pp_ikn_number', '');
		
		$orderData = self::getOrderData($orderId);			
		
        $arQuery = array(
            'SessionId' => $this->sessionId,
            'IKN' => $iknNumber,
            'InvoiceNumber' => $invoiceNumber,
            'GCInvoiceNumber' => $orderData['ORDER_NUMBER'],
        );
        $queryResult = self::query('cancelInvoice', $arQuery);

        if ($queryResult['Result']) {
            self::setCanceledInvoiceStatus($orderId);

            return [
                'STATUS' => true,
            ];
        } else {
            return [
                'STATUS' => false,
                'TEXT' => $queryResult['Error']
            ];
        }
    }

    /**
     * Requests invoice status
     * @param $invoiceNumber
     * @param $orderId
     * @return bool
     * @throws SystemException
     */
    private function getInvoiceStatus($invoiceNumber, $orderId)
    {
		$orderData = self::getOrderData($orderId);
		
        $arQuery = array(
            'SessionId' => $this->sessionId,
            'InvoiceNumber' => $invoiceNumber,
            'SenderInvoiceNumber' => $orderData['ORDER_NUMBER'],
        );
        $queryResult = self::query('tracksending', $arQuery);

        if ($queryResult[0]['State']) {
            return $queryResult[0]['State'];
        } else {
            return false;
        }
    }

    /**
     * Makes order data for order edit form
     * @param int $orderId
     * @param array $statusTable
     * @return array|bool
     * @throws SystemException
     */
    public function getOrderDataToMakeUpdateForm($orderId, $statusTable)
    {
        $invoiceData = self::getInvoiceData($orderId);
        $dimensionData = self::getDimensionsData($orderId);

        if ($invoiceData) {
            if ($invoiceData['PP_INVOICE_ID']) {
                $ppInvoiceStatus = $this->getInvoiceStatus($invoiceData['PP_INVOICE_ID'], $orderId);

                return [
                    'DATA' => $invoiceData,
                    'FIELDS' => $statusTable[$ppInvoiceStatus],
                    'ORDER_PRICE' => self::getOrderPrice($orderId),
                    'INVOICE_SEND' => true,
                    'DIMENSION' => $dimensionData
                ];
            } else {
                return [
                    'DATA' => $invoiceData,
                    'FIELDS_ALL' => true,
                    'ORDER_PRICE' => self::getOrderPrice($orderId),
                    'INVOICE_SEND' => false,
                    'DIMENSION' => $dimensionData
                ];
            }
        } else {
            return false;
        }
    }

    /**
     * Fetch price from Bitrix order
     * @param int $orderId
     * @return int
     * @throws SystemException
     */
    private function getOrderPrice($orderId)
    {
        $orderQuery = Order::getList(['filter' => ['ID' => $orderId], 'select' => ['PRICE']]);
        $order = $orderQuery->fetch();

        return $order['PRICE'];
    }

    /**
     * Change invoice data
     * @param false|int $invoiceNumber
     * @param int $orderId
     * @param array $arFields
     * @throws SystemException
     */
    public function changeInvoice($orderId, $arFields, $invoiceNumber = false)
    {		
        if ($invoiceNumber) {
            // Already sent to Pickpoint
            $oldInvoiceData = self::getInvoiceData($orderId);	
			$orderData = self::getOrderData($orderId);			

            $arQuery = array(
                'SessionId' => $this->sessionId,
                'InvoiceNumber' => $invoiceNumber,
                'GCInvoiceNumber' => $orderData['ORDER_NUMBER'],
            );

            if (!empty($arFields['PP_PHONE']) && $arFields['PP_PHONE'] != $oldInvoiceData['SMS_PHONE']) {
                $arQuery = array_merge($arQuery, array('Phone' => $arFields['PP_PHONE']));
                self::updateInvoice($orderId, 'SMS_PHONE="'.$arFields['PP_PHONE'].'"');
            }

            if (!empty($arFields['PP_NAME']) && $arFields['PP_NAME'] != $oldInvoiceData['NAME']) {
                $arQuery = array_merge($arQuery, array('RecipientName' => $arFields['PP_NAME']));
                self::updateInvoice($orderId, 'NAME="'.$arFields['PP_NAME'].'"');
            }

            if (!empty($arFields['PP_EMAIL']) && $arFields['PP_EMAIL'] != $oldInvoiceData['EMAIL']) {
                $arQuery = array_merge($arQuery, array('Email' => $arFields['PP_EMAIL']));
                self::updateInvoice($orderId, 'EMAIL="'.$arFields['PP_EMAIL'].'"');

            }

            if (!empty($arFields['PP_POSTAMAT_ID']) && $arFields['PP_POSTAMAT_ID'] != $oldInvoiceData['POSTAMAT_ID']) {
                $arQuery = array_merge($arQuery, array('PostamatNumber' => $arFields['PP_POSTAMAT_ID']));
                self::updateInvoice($orderId, 'POSTAMAT_ID="'.$arFields['PP_POSTAMAT_ID'].'"');
            }

            self::query('updateInvoice', $arQuery);

        } else {
            // Not sent
            $oldInvoiceData = self::getInvoiceData($orderId);
			
			$settings = unserialize($oldInvoiceData['SETTINGS']);
			if (!is_array($settings))
				$settings = array();
			
            if (!empty($arFields['PP_PHONE']) && $arFields['PP_PHONE'] != $oldInvoiceData['SMS_PHONE']) {
                self::updateInvoice($orderId, 'SMS_PHONE="'.$arFields['PP_PHONE'].'"');
            }

            if (!empty($arFields['PP_NAME']) && $arFields['PP_NAME'] != $oldInvoiceData['NAME']) {
                self::updateInvoice($orderId, 'NAME="'.$arFields['PP_NAME'].'"');
            }

            if (!empty($arFields['PP_EMAIL']) && $arFields['PP_EMAIL'] != $oldInvoiceData['EMAIL']) {
                self::updateInvoice($orderId, 'EMAIL="'.$arFields['PP_EMAIL'].'"');
            }

            if (!empty($arFields['PP_POSTAMAT_ID']) && $arFields['PP_POSTAMAT_ID'] != $oldInvoiceData['POSTAMAT_ID']) {
                self::updateInvoice($orderId, 'POSTAMAT_ID="'.$arFields['PP_POSTAMAT_ID'].'"');
            }

            if (!empty($arFields['PP_WIDTH']) && $arFields['PP_WIDTH'] != $oldInvoiceData['WIDTH']) {
                self::updateInvoice($orderId, 'WIDTH="'.$arFields['PP_WIDTH'].'"');
            }

            if (!empty($arFields['PP_HEIGHT']) && $arFields['PP_HEIGHT'] != $oldInvoiceData['HEIGHT']) {
                self::updateInvoice($orderId, 'HEIGHT="'.$arFields['PP_HEIGHT'].'"');
            }

            if (!empty($arFields['PP_DEPTH']) && $arFields['PP_DEPTH'] != $oldInvoiceData['DEPTH']) {
                self::updateInvoice($orderId, 'DEPTH="'.$arFields['PP_DEPTH'].'"');
            }
			
			if (isset($arFields['ASSESSED_COST'])) {
				$settings['ASSESSED_COST'] = $arFields['ASSESSED_COST'];
				$settings = serialize($settings);
				self::updateInvoice($orderId, "SETTINGS='".$settings."'");
			}			
        }
    }

    /**
     * Send cool new invoice to Pickpoint
     * @param array $ordersId
     * @return array
     * @throws SystemException
     */
    public function sendInvoice($ordersId)
    {
        $storeData = OrderOptions::getStoreOptionValues();
        $answers = [];

        $arQuery = [
            'SessionId' => $this->sessionId,
			'Source'    => 3, // Order from Bitrix module - this param added by PP support request
            'Sendings'  => []
        ];

        foreach ($ordersId as $key => $orderId) {				
            $orderData = self::getOrderData($orderId);
            $dimensionsData = $this->getDimensionsData($orderId);
			$goodsData = OrderOptions::getBasketItemsData($orderId);
			
			$invoiceData = self::getInvoiceData($orderId);
			$settings = unserialize($invoiceData['SETTINGS']);
			if (!is_array($settings))
				$settings = array();
						
			// Assessed cost and insuare value
			$insuareValue = 0;
			if (array_key_exists('ASSESSED_COST', $settings)) {
				$insuareValue = $settings['ASSESSED_COST'];
			} else {
				if (Option::get(self::MODULE_ID, 'set_assessed_cost', 'N') == 'Y')
					$insuareValue = $orderData['PRICE'];
				else
					$insuareValue = 0;				
			}			
			
			// Delivery fee
			$deliveryFee = 0;			
			if ($orderData['PRICE'] > 0) {
				$goodsPrice = 0;				
				foreach ($goodsData as $item)
					$goodsPrice += $item['Price'] * $item['Quantity'];				
					
				$deliveryFee = floatval($orderData['PRICE'] - $goodsPrice);
				if ($deliveryFee < 0)
					$deliveryFee = 0;				
			}
			
			// Delivery VAT option to request conversion
			$deliveryVat = null;
			switch ($storeData['DELIVERY_VAT']) {
				case 'VATNONE':				
					$deliveryVat = null; break;	
				case 'VAT0':  
					$deliveryVat = 0; break;
				case 'VAT10': 
					$deliveryVat = 10; break;
				case 'VAT20': 
					$deliveryVat = 20; break;							
			}			
			
            $arQuery['Sendings'][$key] = [
                'EDTN' => $orderId,
                'IKN' => $storeData['IKN'],
                'Invoice' => [
                    'SenderCode' => $orderData['ORDER_NUMBER'],
                    'Description' => $storeData['ENCLOSURE'],
                    'RecipientName' => $orderData['OPTIONS']['FIO'] ? $orderData['OPTIONS']['FIO'] : $orderData['NAME'],
                    'PostamatNumber' => $orderData['PostamatNumber'],
                    'MobilePhone' => $orderData['PHONE'],
                    'Email' => $orderData['OPTIONS']['EMAIL'] ? $orderData['OPTIONS']['EMAIL'] : $orderData['EMAIL'],
                    'PostageType' => $orderData['PostageType'],
                    'GettingType' => intval($orderData['GettingType']),
                    'DeliveryVat' => $deliveryVat,
					'DeliveryFee' => $deliveryFee,
                    'PayType' => 1,
                    'Sum' => $orderData['PRICE'],
					'InsuareValue' => round(floatval($insuareValue), 2),
                    'Places' => [
                        0 => [
                            'Width' => $dimensionsData['WIDTH'],
                            'Height' => $dimensionsData['HEIGHT'],
                            'Depth' => $dimensionsData['DEPTH'],
                            'SubEncloses' => $goodsData
                        ]
                    ]
                ]
            ];

            if ($this->checkArrayByEmpty($storeData['returnAddress'])){
                $arQuery['Sendings'][$key]['Invoice']['ClientReturnAddress'] = $storeData['returnAddress'];
                $arQuery['Sendings'][$key]['Invoice']['UnclaimedReturnAddress'] = $storeData['returnAddress'];
            }
			
			// Save order getting type and postage type
			$result = OrderTable::update($invoiceData['ID'], [
				'GETTING_TYPE' => $orderData['GettingType'], 
				'POSTAGE_TYPE' => $orderData['PostageType'],								
				]);
				
			/*
			if (!$result->isSuccess())
			    \Bitrix\Main\Diag\Debug::WriteToFile([$result->getErrorMessages()], __FUNCTION__, '__PickPoint_Debug.log');
			*/
        }

        $queryAnswer = self::query('CreateShipment', $arQuery);
	   
		// Is tracking number set enabled		 
		$setDeliveryId = (bool) Option::get(self::MODULE_ID, 'set_delivery_id', 'N') == 'Y';
		if ($setDeliveryId)
			\CModule::IncludeModule('sale');		
		
        foreach ($queryAnswer['CreatedSendings'] as $createdSending) {
            // self::setOrderInvoice($createdSending['EDTN'], $createdSending['InvoiceNumber']);
			
			// Save order invoice number and dispatch date
			$invoiceData = self::getInvoiceData($createdSending['EDTN']);
			
			$result = OrderTable::update($invoiceData['ID'], [
				'PP_INVOICE_ID' => $createdSending['InvoiceNumber'], 
				'DISPATCH_DATE' => new \Bitrix\Main\Type\DateTime(),								
				]);			
			
            $answers[$createdSending['EDTN']]['STATUS'] = true;
			
			// Set tracking number
			if ($setDeliveryId)
				\CSaleOrder::Update(intval($createdSending['EDTN']), array('TRACKING_NUMBER' => intval($createdSending['InvoiceNumber'])));				
        }

        foreach ($queryAnswer['RejectedSendings'] as $rejectedSending) {
            $rejectedError = self::checkErrors($rejectedSending);
            $answers[$rejectedSending['EDTN']]['STATUS'] = false;

            if ($rejectedError) {
                $answers[$rejectedSending['EDTN']]['ERRORS'][] =  $rejectedError;
            }
        }

		// Deprecated method
        // $this->updateAllInvoicesStatus();

        return $answers;
    }

    /**
     * Get order data compiled from Bitrix order and module options
     * @param int $orderId
     * @return array|false
     * @throws SystemException
     */
    private function getOrderData($orderId)
    {
        $orderData = [];
        $order = Order::load($orderId);

        $personType = $order->getPersonTypeId();

		$orderData['ORDER_NUMBER'] = $order->getField('ACCOUNT_NUMBER');
		
        $orderData['OPTIONS'] = OrderOptions::getOrderOptions($orderId, $personType, $this->arOptionDefaults);
        $orderPaySystemId = $order->getPaymentSystemId();

        if (Helper::CheckPPPaySystem($orderPaySystemId[0])) {
            $orderData['PostageType'] = $this->arServiceTypesCodes[1];
            $orderData['PRICE'] = $order->getPrice();
        } else {
            $orderData['PostageType'] = $this->arServiceTypesCodes[0];
            $orderData['PRICE'] = 0;
        }

        $ppData = $this->getInvoiceData($orderId);

        $orderData['PostamatNumber'] = $ppData['POSTAMAT_ID'];
        $orderData['NAME'] = $ppData['NAME'];
        $orderData['EMAIL'] = $ppData['EMAIL'];
        $orderData['PHONE'] = $ppData['SMS_PHONE'] ? $ppData['SMS_PHONE'] : $orderData['OPTIONS']['ADDITIONAL_PHONES'];
		
		// Legacy bullshit, but who cares...
        $orderData['GettingType'] = $_REQUEST['EXPORT'][$orderId]['GETTING_TYPE'];

        return $orderData;
    }

    /**
     * Checks if error messages detected in server answer
     * @param $response
     * @return false|string
     */
    private static function checkErrors($response)
    {
        if ($response['ErrorMessage']) {
            if (defined('BX_UTF') && BX_UTF == true) {
                return $response['ErrorMessage'];
            } else {
                return Encoding::convertEncoding($response['ErrorMessage'], 'utf-8', 'windows-1251');
            }
        }

        return false;
    }

    /**
     * Checks array values for emptiness
     * @param array $array
     * @return bool
     */
    private function checkArrayByEmpty($array)
    {
        foreach ($array as $item) {
            if (empty($item)){
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $orderId
     * @return array
     * @throws SystemException
     */
    public static function getDimensionsData($orderId)
    {
        $dimensions = [];
        $invoiceData = self::getInvoiceData($orderId);

        if (!empty($invoiceData['WIDTH'])){
            $dimensions['WIDTH'] = $invoiceData['WIDTH'];
        } else {
            $dimensions['WIDTH'] = Option::get(self::MODULE_ID, 'pp_dimension_width', '50');
        }

        if (!empty($invoiceData['HEIGHT'])){
            $dimensions['HEIGHT'] = $invoiceData['HEIGHT'];
        } else {
            $dimensions['HEIGHT'] = Option::get(self::MODULE_ID, 'pp_dimension_height', '50');
        }

        if (!empty($invoiceData['DEPTH'])){
            $dimensions['DEPTH'] = $invoiceData['DEPTH'];
        } else {
            $dimensions['DEPTH'] = Option::get(self::MODULE_ID, 'pp_dimension_depth', '50');
        }

        return $dimensions;
    }

    /**
	 * Deprecated method, will be deleted
	 * Use StatusHandler::refreshOrderStates() instead
	 * 	
	 * @deprecated
     * @throws SystemException
     */
    public function updateAllInvoicesStatus()
    {
        $invoices = self::getAllInvoices();
        $invoicesId = [];
        $statusArray = [];

        foreach ($invoices as $invoice) {
            if ($invoice['PP_INVOICE_ID']) {
                $invoicesId[] = $invoice['PP_INVOICE_ID'];
            }
        }

        $arQuery = [
            'SessionId' => $this->sessionId,
            'Invoices' => $invoicesId
        ];
        $answer = self::query('tracksendings', $arQuery);

        foreach ($answer['Invoices'] as $invoice) {
            if ($invoice['States']) {
                $statusArray[$invoice['SenderInvoiceNumber']] = end($invoice['States']);
            }
        }

        foreach ($invoices as $invoice) {
            if ($invoice['STATUS'] != $statusArray[$invoice['ORDER_ID']]){
                self::updateInvoice($invoice['ORDER_ID'], 'STATUS="'.$statusArray[$invoice['ORDER_ID']]['State'].'"');
            }
        }
    }

    /**
     * @param $orderId
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function changeArchiveStatus($orderId)
    {
        $invoiceData = self::getInvoiceData($orderId);

        if ($invoiceData['ARCHIVE']) {
            self::updateInvoiceArchiveStatus($orderId , 0);
        } else {
            self::updateInvoiceArchiveStatus($orderId , 1);
        }
    }
}