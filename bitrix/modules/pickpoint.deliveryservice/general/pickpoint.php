<?php
use \PickPoint\DeliveryService\Option;

use Bitrix\Main\Application;
use Bitrix\Main\Text\Encoding;
use PickPoint\OrderOptions;

class CPickpoint extends CAllPickpoint
{
    private static $processedOrders = array();

    public function PHPArrayToJS($arDest, $sName)
    {
        return "<script>{$sName} = ".self::PHPArrayToJS_in($arDest).'</script>';
    }

    public function PHPArrayToJS_in($arDest)
    {
        if (is_array($arDest)) {
            foreach ($arDest as $k => $v) {
                $arDest[$k] = '"'.$k.'":'.self::PHPArrayToJS_in($v);
            }
            $arDest = '{'.implode(',', $arDest).'}';
        } else {
            $arDest = '"'.self::js_escape($arDest).'"';
        }

        return $arDest;
    }

    public function js_escape($str)
    {
        $obLocation = CSaleLocation::GetList(
            array(
                'SORT' => 'ASC',
                'CITY_NAME_ORIG' => 'ASC',
            ),
            array(
                'COUNTRY_NAME' => 'Russia',
                '!CITY_ID' => null,
                'CITY_LID' => 'en',
                'CITY_NAME' => $str,
            ),
            false,
            false,
            array(
                'ID',
                'CITY_ID',
                'CITY_NAME',
                'CITY_NAME_ORIG',
            )
        );

        return $obLocation->Fetch();
    }

    public static function GetCity($arFields)
    {
        $iPPID = intval($arFields['PP_ID']) ? intval($arFields['PP_ID']) : 0;

        $iID = 0;
        $sCode = strlen($arFields['CODE']) ? $arFields['CODE'] : '';
        $iPrice = 0;
        $sActive = 'N';
        if ($iPPID) {
            $obPPCity = self::SelectCityByPPID($iPPID);
            if ($arPPCity = $obPPCity->Fetch()) {
                $iBXID = $arPPCity['BX_ID'];
                if ($arPPCity['ACTIVE'] == 'Y') {
                    $sActive = 'Y';
                }
                $arBXCity = self::SelectCityByID($iBXID);
                $iPrice = floatval($arPPCity['PRICE']);
                $sCode = $arBXCity['CITY_NAME'];
            } else {
                $arCity = self::SelectCityByCode($sCode);
                $iBXID = $arCity['ID'];
            }
        } else {
            $arCity = self::SelectCityByCode($sCode);
            $iBXID = $arCity['ID'];
            $iPPID = 0;
        }

        return array(
            'ID' => $iID,
            'CODE' => $sCode,
            'PP_ID' => $iPPID,
            'BX_ID' => $iBXID,
            'PRICE' => $iPrice,
            'ACTIVE' => $sActive,
        );
    }

    public static function SelectCityByCode($sCode)
    {
        $obLocation = CSaleLocation::GetList(
            array(
                'SORT' => 'ASC',
                'CITY_NAME_ORIG' => 'ASC',
            ),
            array(
                'COUNTRY_NAME' => 'Russia',
                '!CITY_ID' => null,
                'CITY_LID' => 'en',
                'CITY_NAME' => $sCode,
            ),
            false,
            false,
            array(
                'ID',
                'CITY_ID',
                'CITY_NAME',
                'CITY_NAME_ORIG',
            )
        );

        return $obLocation->Fetch();
    }

    public static function SelectCityByID($iBXID)
    {
        $obLocation = CSaleLocation::GetList(
            array(
                'SORT' => 'ASC',
                'CITY_NAME_ORIG' => 'ASC',
            ),
            array(
                'COUNTRY_NAME' => 'Russia',
                '!CITY_ID' => null,
                'CITY_LID' => 'en',
                'CITY_ID' => $iBXID,
            ),
            false,
            false,
            array(
                'ID',
                'CITY_ID',
                'CITY_NAME',
                'CITY_NAME_ORIG',
            )
        );

        return $obLocation->Fetch();
    }

    public static function CheckRequest()
    {
        $parameters = array(
            'PP_ID',
            'PP_ADDRESS',
            'PP_NAME',
            'PP_SMS_PHONE',
            'PP_ZONE',
            'PP_COEFF',
            'PP_DELIVERY_MIN',
            'PP_DELIVERY_MAX',
        );

        if (isset($_REQUEST['order']))
        {
            $request = Application::getInstance()->getContext()->getRequest();
            $orderData = $request->get("order");
			
			// Order can mean Sort order or something else, not necessary Sale order
			if (!is_array($orderData))
				return;
			
            if(!Application::getInstance()->isUtfMode())
            {
                $orderData= Encoding::convertEncoding($orderData, 'UTF-8', SITE_CHARSET);
            }
            
            foreach ($parameters as $parameter) {
                if (strlen($orderData[$parameter]) > 0)
                {
                    $_SESSION['PICKPOINT'][$parameter] = $orderData[$parameter];
                }
            }
        }
        else {
            foreach ($parameters as $parameter) {
                if (isset($_REQUEST[$parameter])) {
                    $_SESSION['PICKPOINT'][$parameter] = (Application::getInstance()->isUtfMode()) ?
                        $_REQUEST[$parameter] : Encoding::convertEncoding($_REQUEST[$parameter], 'UTF-8', SITE_CHARSET);
                }
            }
        }

    }

    private static function processOrderAdd($orderId, $deliveryId)
    {
        if (in_array($orderId, static::$processedOrders)) {
            return;
        }

        $MODULE_ID = static::$moduleId;

        $deliveryCode = self::getProfileCodeById($deliveryId);

        $smsPhone = '';
        $orderPhone = COption::GetOptionString('pickpoint.deliveryservice', 'pp_order_phone');

        $order = CSaleOrder::GetByID($orderId);
        $orderProps = OrderOptions::getOrderOptions(
            $orderId,
            $order['PERSON_TYPE_ID'],
            []
        );

        $email = $orderProps['EMAIL'];
        $fio = $orderProps['FIO'];

        if  ($orderPhone) {
            $smsPhone = $orderProps['ORDER_PHONE'];
        } else {
            $smsPhone = $_SESSION['PICKPOINT']['PP_SMS_PHONE'];
        }

        if ('pickpoint:postamat' == $deliveryCode) {
            $arToAdd = array(
                'ORDER_ID' => $orderId,
                'POSTAMAT_ID' => $_SESSION['PICKPOINT']['PP_ID'],
                'ADDRESS' => $_SESSION['PICKPOINT']['PP_ADDRESS'],
                'SMS_PHONE' => $smsPhone,
                'EMAIL' => $email,
                'NAME' => $fio,
            );
            self::AddOrderPostamat($arToAdd);
            if (COption::GetOptionString($MODULE_ID, 'pp_add_info', '')) {
                $saleOrder = new CSaleOrder();
                $orderData = $saleOrder->GetByID($orderId);
                $sDescription = "{$orderData['USER_DESCRIPTION']}\n\n"
                    ."{$_SESSION['PICKPOINT']['PP_ID']}\n"
                    ."{$_SESSION['PICKPOINT']['PP_ADDRESS']}\n"
                    ."{$smsPhone}\n"
                    ."{$email}\n"
                    ."{$fio}";

                $saleOrder->Update($orderId, array('USER_DESCRIPTION' => $sDescription, 'COMMENTS' => $sDescription));
            }
        }

        unset($_SESSION['PICKPOINT']);
        static::$processedOrders[] = $orderId;
    }

    public static function OnOrderAdd($orderId, $arFields)
    {
        static::processOrderAdd($orderId, $arFields['DELIVERY_ID']);
    }

    public static function OnSaleOrderSaved(\Bitrix\Main\Event $event)
    {
        if (!$event->getParameter('IS_NEW')) {
            return;
        }
        /** @var \Bitrix\Sale\Order $order */
        $order = $event->getParameter('ENTITY');

        $array = $order->getDeliverySystemId();
        static::processOrderAdd($order->getId(), reset($array));
    }

    public static function OnOrderAddV15($orderId, $arFields)
    {
        static::processOrderAdd($orderId, $arFields['DELIVERY_ID']);
    }

    protected static function getProfileCodeById($profileId)
    {
        //bitrix v16+ check profile ID
        if ($profileId !== 'pickpoint:postamat' && class_exists('Bitrix\Sale\Delivery\Services\Table')) {
            $arDelivery = Bitrix\Sale\Delivery\Services\Table::getList(
                array(
                    'filter' => array(
                        'ID' => $profileId,
                    ),
                    'select' => array('CODE'),
                )
            )->fetch()
            ;

            if ($arDelivery['CODE'] == 'pickpoint:postamat') {
                return $arDelivery['CODE'];
            }
        } else {
            return $profileId;
        }
    }

    public static function Calculate($arOrder)
    {
        $MODULE_ID = static::$moduleId;

        $selectedZone = array_key_exists('PICKPOINT', $_SESSION) ? intval($_SESSION['PICKPOINT']['PP_ZONE']) : 0;
        $ppzoneID     = $selectedZone + 2;

        $obZone = self::SelectZoneByID($ppzoneID);
        $price = 0;
        $minOrderFreePrice = 0;
        if ($arZone = $obZone->Fetch()) {
            $price = $arZone['PRICE'];
            $minOrderFreePrice = $arZone['FREE'];
        }

        if (COption::GetOptionString($MODULE_ID, 'pp_use_coeff', '')) {
            if (array_key_exists('PICKPOINT', $_SESSION) && doubleval($_SESSION['PICKPOINT']['PP_COEFF']) > 1) {
                if (!$coeff = COption::GetOptionString($MODULE_ID, 'pp_custom_coeff', '')) {
                    $coeff = doubleval($_SESSION['PICKPOINT']['PP_COEFF']);
                }

                $price *= $coeff;
            }
        }

        if (intval($price) > 0) {
            if (intval($minOrderFreePrice) > 0 && $arOrder['PRICE'] >= $minOrderFreePrice) {
                $price = 0;
            }
        }

        return $price;
    }

    public static function CheckPPPaySystem($iPSID, $iPTID)
    {
        $arPS = (CSalePaySystem::GetByID($iPSID, $iPTID));
        if (substr_count($arPS['PSA_ACTION_FILE'], 'pickpoint.deliveryservice')) {
            return 1;
        }

        return 0;
    }

    public static function GetOrdersArray()
    {
        $obOrdersPostamat = self::SelectOrderPostamat();
        $arItems = array();
        while ($arOrderPostamat = $obOrdersPostamat->Fetch()) {
            $obOrder = CSaleOrder::GetList(
                array(),
                array(
                    'ID' => $arOrderPostamat['ORDER_ID'],
                    '!STATUS_ID' => 'F',
                    'CANCELED' => 'N',
                ),
                false,
                false,
                array(
                    'ID',
                    'PAY_SYSTEM_ID',
                    'PERSON_TYPE_ID',
                    'DATE_INSERT',
                    'PRICE',
                    'DELIVERY_ID',
                )
            );
            if ($arOrder = $obOrder->Fetch()) {
                if (strpos($arOrder['DELIVERY_ID'], 'pickpoint') !== false) {
                    $arSettings = unserialize($arOrderPostamat['SETTINGS']);
                    $arItem = array(
                        'ORDER_ID' => $arOrder['ID'],
                        'ORDER_DATE' => $arOrder['DATE_INSERT'],
                        'PAYED_BY_PP' => self::CheckPPPaySystem(
                            $arOrder['PAY_SYSTEM_ID'],
                            $arOrder['PERSON_TYPE_ID']
                        ),
                        'PRICE' => $arOrder['PRICE'],
                        'PP_ADDRESS' => $arOrderPostamat['ADDRESS'],
                        'INVOICE_ID' => $arOrderPostamat['PP_INVOICE_ID'],
                        'SETTINGS' => $arSettings,
                        'CANCELED' => $arOrderPostamat['CANCELED']
                    );

                    if (!empty($arOrderPostamat['WIDTH'])){
                        $arItem['WIDTH'] = $arOrderPostamat['WIDTH'];
                    } else {
                        $arItem['WIDTH'] = Option::get('pp_dimension_width');
                    }

                    if (!empty($arOrderPostamat['HEIGHT'])){
                        $arItem['HEIGHT'] = $arOrderPostamat['HEIGHT'];
                    } else {
                        $arItem['HEIGHT'] = Option::get('pp_dimension_height');
                    }

                    if (!empty($arOrderPostamat['DEPTH'])){
                        $arItem['DEPTH'] = $arOrderPostamat['DEPTH'];
                    } else {
                        $arItem['DEPTH'] = Option::get('pp_dimension_depth');
                    }

                    $arItems[] = $arItem;
                }
            }
        }

        return $arItems;
    }

    public static function GetParam($iOrderID, $iPersonType, $sPPField)
    {
        require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';
        $arTableOptions = (unserialize(COption::GetOptionString('pickpoint.deliveryservice', 'OPTIONS')));
        if (!isset($arTableOptions[$iPersonType][$sPPField])) {
            /** @var array $arOptionDefaults - from constants */
            $arData = array($arOptionDefaults[$sPPField]);
        } else {
            $arData = $arTableOptions[$iPersonType][$sPPField];
        }
        $arReturn = array();
        foreach ($arData as $arOption) {
            switch ($arOption['TYPE']) {
                case 'ANOTHER':
                    $arReturn[] = $arOption['VALUE'];
                    break;
                case 'USER':
                    $obOrder = CSaleOrder::GetList(
                        array(),
                        array('ID' => $iOrderID),
                        false,
                        false,
                        array(
                            'ID',
                            'USER_ID',
                        )
                    );
                    $arOrder = $obOrder->Fetch();
                    $obUser = CUser::GetByID($arOrder['USER_ID']);
                    if ($arUser = $obUser->Fetch()) {
                        if ($arOption['VALUE'] == 'USER_FIO') {
                            $arReturn[] = $arUser['LAST_NAME'].($arUser['NAME']
                                    ? ' '.$arUser['NAME']
                                    :
                                    '').($arUser['SECOND_NAME'] ? ' '.$arUser['SECOND_NAME'] : '');
                        } elseif (strlen($arUser[$arOption['VALUE']])) {
                            $arReturn[] = $arUser[$arOption['VALUE']];
                        }
                    }
                    break;
                case 'ORDER':
                    $arOrder = CSaleOrder::GetByID($iOrderID);
                    $arReturn[] = $arOrder[$arOption['VALUE']];
                    break;
                case 'PROPERTY':
                    $obProperty = CSaleOrderPropsValue::GetList(
                        array(),
                        array(
                            'ORDER_ID' => $iOrderID,
                            'ORDER_PROPS_ID' => $arOption['VALUE'],
                        ),
                        false,
                        false,
                        array('VALUE')
                    );
                    if ($arProperty = $obProperty->Fetch()) {
                        if (strlen($arProperty['VALUE']) > 0) {
                            $arReturn[] = $arProperty['VALUE'];
                        }
                    }
                    break;
            }
        }

        return $arReturn;
    }

	/**
     * Unused legacy - will be removed
     * 
     * @deprecated
     */	
    public static function ExportXML($arIDs)
    {
        require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';
        global $APPLICATION;

        $sReturn = '<'."?xml version='1.0' encoding='UTF-8'?".">\n<documents>\n";
        foreach ($arIDs as $iOrderID) {
            $obOrder = CSaleOrder::GetList(
                array(),
                array('ID' => $iOrderID),
                false,
                false,
                array(
                    'ID',
                    'PERSON_TYPE_ID',
                    'PAY_SYSTEM_ID',
                )
            );
            if ($arOrder = $obOrder->Fetch()) {
                $obData = self::SelectOrderPostamat($arOrder['ID']);
                $arData = $obData->Fetch();
                $sReturn .= "\t<document>\n";
                $arFIO = self::GetParam($arOrder['ID'], $arOrder['PERSON_TYPE_ID'], 'FIO');
                $sFIO = current($arFIO);
                $sReturn .= "\t\t<fio>{$sFIO}</fio>\n";
                $sReturn .= "\t\t<sms_phone>{$arData['SMS_PHONE']}</sms_phone>\n";
                $arEmail = self::GetParam($arOrder['ID'], $arOrder['PERSON_TYPE_ID'], 'EMAIL');
                $sEmail = current($arEmail);
                $sReturn .= "\t\t<email>{$sEmail}</email>\n";
                $arAdditionalPhones = self::GetParam(
                    $arOrder['ID'],
                    $arOrder['PERSON_TYPE_ID'],
                    'ADDITIONAL_PHONES'
                );
                $sReturn .= "\t\t<additional_phones>\n";
                foreach ($arAdditionalPhones as $sPhone) {
                    $sReturn .= "\t\t\t<phone>{$sPhone}</phone>\n";
                }
                $sReturn .= "\t\t</additional_phones>\n";
                $sReturn .= "\t\t<order_id>{$arOrder['ID']}</order_id>\n";
                if (self::CheckPPPaySystem($arOrder['PAY_SYSTEM_ID'], $arOrder['PERSON_TYPE_ID'])) {
                    $iPrice = number_format($_REQUEST['EXPORT'][$arOrder['ID']]['PAYED'], 2, '.', '');
                } else {
                    $iPrice = 0;
                }
                /** @var array $arServiceTypes - from constants */
                /** @var array $arSizes - from constants */
                $sReturn .= "\t\t<summ_rub>{$iPrice}</summ_rub>\n";
                $sReturn .= "\t\t<terminal_id>{$arData['POSTAMAT_ID']}</terminal_id>\n";
                $sReturn .= "\t\t<type_service>{$arServiceTypes[$_REQUEST['EXPORT'][$arOrder['ID']]['SERVICE_TYPE']]}</type_service>\n";
                $sReturn .= "\t\t<type_reception>{$_REQUEST['EXPORT'][$arOrder['ID']]['GETTING_TYPE']}</type_reception>\n";
                $sEmbed = COption::GetOptionString('pickpoint.deliveryservice', 'pp_enclosure', '');
                $sReturn .= "\t\t<embed>{$sEmbed}</embed>\n";
                $sReturn .= "\t\t<size_x>{$arSizes[$_REQUEST['EXPORT'][$arOrder['ID']]['SIZE']]['SIZE_X']}</size_x>\n";
                $sReturn .= "\t\t<size_y>{$arSizes[$_REQUEST['EXPORT'][$arOrder['ID']]['SIZE']]['SIZE_Y']}</size_y>\n";
                $sReturn .= "\t\t<size_z>{$arSizes[$_REQUEST['EXPORT'][$arOrder['ID']]['SIZE']]['SIZE_Z']}</size_z>\n";
                $sReturn .= "\t</document>\n";
            }
        }
        $sReturn .= '</documents>';
        $APPLICATION->RestartBuffer();
        ob_start();
        echo $sReturn;
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="pickpoint_export.xml"');
        header('Content-Length: '.strlen($contents));
        echo $contents;
        die();
    }

	/**
     * Unused legacy - will be removed
     * 
     * @deprecated
     */	
    public static function ExportOrders($arIDs)
    {
        global $APPLICATION;
        $MODULE_ID = static::$moduleId;
        $api_login = COption::GetOptionString($MODULE_ID, 'pp_api_login', '');
        $api_password = COption::GetOptionString($MODULE_ID, 'pp_api_password', '');
        $authResult = self::Login($api_login, $api_password);
        if (!is_array($authResult['ERROR'])) {
            $sessionId = $authResult;
            if (strlen($sessionId) > 0) {
                require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';
                $ikn_number = COption::GetOptionString($MODULE_ID, 'pp_ikn_number', '');
                $store_city = COption::GetOptionString($MODULE_ID, 'pp_from_city', '');
                $store_address = COption::GetOptionString($MODULE_ID, 'pp_store_address', '');
                $store_phone = COption::GetOptionString($MODULE_ID, 'pp_store_phone', '');
                $sEmbed = COption::GetOptionString($MODULE_ID, 'pp_enclosure', '');
                $ClientReturnAddress = array(
                    'PhoneNumber' => $store_phone,
                    'CityName' => $store_city,
                    'Address' => $store_address,
                );
                $arQuery = array('SessionId' => $sessionId);
                foreach ($arIDs as $iOrderID) {
                    $arSending = array('IKN' => $ikn_number);
                    $arInvoice = array();
                    $obOrder = CSaleOrder::GetList(
                        array(),
                        array('ID' => $iOrderID),
                        false,
                        false,
                        array(
                            'ID',
                            'PRICE',
                            'PERSON_TYPE_ID',
                            'PAY_SYSTEM_ID',
                        )
                    );
                    if ($arOrder = $obOrder->Fetch()) {
                        $obData = self::SelectOrderPostamat($arOrder['ID']);
                        $arData = $obData->Fetch();

                        $arFIO = self::GetParam($arOrder['ID'], $arOrder['PERSON_TYPE_ID'], 'FIO');
                        $sFIO = current($arFIO);

                        $arSending['EDTN'] = $arOrder['ID'];
                        $arInvoice['SenderCode'] = $arOrder['ID'];
                        $arInvoice['Description'] = $sEmbed;
                        $arInvoice['RecipientName'] = $sFIO;
                        $arInvoice['PostamatNumber'] = $arData['POSTAMAT_ID'];
                        $arInvoice['MobilePhone'] = $arData['SMS_PHONE'];

                        $arEmail = self::GetParam($arOrder['ID'], $arOrder['PERSON_TYPE_ID'], 'EMAIL');
                        $sEmail = current($arEmail);

                        $arInvoice['Email'] = $sEmail;
                        /** @var array $arServiceTypesCodes - from constants */
                        if (self::CheckPPPaySystem($arOrder['PAY_SYSTEM_ID'], $arOrder['PERSON_TYPE_ID'])) {
                            $arInvoice['PostageType'] = $arServiceTypesCodes[1];
                        } else {
                            $arInvoice['PostageType'] = $arServiceTypesCodes[0];
                        }
                        $arInvoice['GettingType']
                            = $_REQUEST['EXPORT'][$arOrder['ID']]['GETTING_TYPE'];
                        $arInvoice['PayType'] = 1;

                        if (self::CheckPPPaySystem($arOrder['PAY_SYSTEM_ID'], $arOrder['PERSON_TYPE_ID'])
                            || ($_REQUEST['EXPORT'][$arOrder['ID']]['PAYED']) > 0
                        ) {
                            //$iPrice = number_format($_REQUEST["EXPORT"][$arOrder["ID"]]["PAYED"],2,".","");
                            $iPrice = number_format($arOrder['PRICE'], 2, '.', '');
                        } else {
                            $iPrice = 0;
                        }
                        $arInvoice['Sum'] = $iPrice;
                        $arInvoice['ClientReturnAddress'] = $ClientReturnAddress;

                        $arInvoice['UnclaimedReturnAddress'] = $ClientReturnAddress;

                        $arSending['Invoice'] = $arInvoice;
                        $arQuery['Sendings'][] = $arSending;
                    }
                }
                if (count($arQuery['Sendings']) > 0) {
                    $response = self::Query('createsending', $arQuery);

                    foreach ($response->CreatedSendings as $key => $createdSendings) {
                        if ($createdSendings->ErrorMessage) {
                            self::checkErrors($createdSendings);
                        } elseif (intval($createdSendings->InvoiceNumber) > 0) {
                            self::SetOrderInvoice(
                                $arQuery['Sendings'][$key]['Invoice']['SenderCode'],
                                $createdSendings->InvoiceNumber
                            );
                        }
                    }
                    foreach ($response->RejectedSendings as $rejectedSending) {
                        self::checkErrors($rejectedSending);
                    }
                }

                self::Logout($sessionId);
            }
        } else {
            return $authResult;
        }
    }

    public static function SaveOrderOptions($orderID)
    {
        if (is_array($_REQUEST['EXPORT'][$orderID])) {
            $settings = serialize($_REQUEST['EXPORT'][$orderID]);
            self::SetOrderSettings($orderID, $settings);
        }
    }

    private static function checkErrors($response)
    {
        global $APPLICATION;

        if ($response->ErrorMessage) {
            if (defined('BX_UTF') && BX_UTF == true) {
                $APPLICATION->ThrowException($response->ErrorMessage);
            } else {
                $APPLICATION->ThrowException(
                    $APPLICATION->ConvertCharset($response->ErrorMessage, 'utf-8', 'windows-1251')
                );
            }
        }
    }

    private static function Query($method, $arQuery)
    {
        global $APPLICATION;
        $MODULE_ID = static::$moduleId;
        $bpp_test_mode = COption::GetOptionString($MODULE_ID, 'pp_test_mode', '');
        if ($bpp_test_mode) {
            $apiUrl = '/apitest/';
        } else {
            $apiUrl = '/api/';
        }
        if (!(defined('BX_UTF') && BX_UTF == true)) {
            $arQuery = $APPLICATION->ConvertCharsetArray($arQuery, 'windows-1251', 'utf-8');
        }
        $error_number = 0;
        $error_text   = '';
        $response = QueryGetData(
            'e-solution.pickpoint.ru',
            '80',
            $apiUrl.$method,
            json_encode($arQuery),
            $error_number,
            $error_text,
            'POST',
            '',
            'application/json'
        );

        $response = json_decode($response);

        self::checkErrors($response);

        return $response;
    }

    private static function Login($login, $password)
    {
        $arQuery = array(
            'Login' => $login,
            'Password' => $password,
        );

        $response = self::Query('login', $arQuery);

        if (!$response->ErrorMessage && $response->SessionId) {
            return $response->SessionId;
        } else {
            return array('ERROR' => $response->ErrorMessage);
        }
    }

    private static function Logout($sessionId)
    {
        $arQuery = array('SessionId' => $sessionId);
        $response = self::Query('logout', $arQuery);
    }

    public static function GetCitiesCSV()
    {
        $MODULE_ID = static::$moduleId;
        $iTimeDelta = 86400; //Next Day

        if (@fopen(PP_CSV_URL, 'r')) {
            $sFileData = file_get_contents(PP_CSV_URL);
        }
        if (defined('BX_UTF') && BX_UTF == true) {
            $sFileData = iconv('windows-1251', 'utf-8', $sFileData);
        }
        if (strlen($sFileData) > 0) 
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/cities.csv', $sFileData);			
        else 
		    $iTimeDelta = 3600;
        
        COption::SetOptionInt($MODULE_ID, 'pp_city_download_timestamp', time() + $iTimeDelta);
    }

    public static function GetZonesArray()
    {
        $arZones = array();
        $obZone = self::SelectZones();

        while ($arZone = $obZone->Fetch()) {
            $arZones[$arZone['ZONE_ID']] = $arZone;
        }

        return $arZones;
    }

    public static function OnSCOrderOneStepDeliveryHandler(&$arResult, &$arUserResult)
    {
        global $DB;

        if (empty($arResult['DELIVERY'])) {
            return;
        }

        $content = '';

        $widgetParamsString = '';
        $str_from_city = COption::GetOptionString('pickpoint.deliveryservice', 'pp_from_city');
        $pp_ikn_number = COption::GetOptionString('pickpoint.deliveryservice', 'pp_ikn_number');
        $autoLocation = COption::GetOptionString('pickpoint.deliveryservice', 'pp_city_location');
        $cityFromProp = COption::GetOptionString('pickpoint.deliveryservice', 'pp_order_city_status');
        $orderPhone = COption::GetOptionString('pickpoint.deliveryservice', 'pp_order_phone');

        $cityNamePP = '';
        $phonePP = '';

        if ($cityFromProp)
        {
            $propsString = COption::GetOptionString('pickpoint.deliveryservice', 'OPTIONS');
            $props = unserialize($propsString);

            // Standard SOA
            $locationId = $arUserResult['ORDER_PROP'][$props[$arUserResult['PERSON_TYPE_ID']]['ORDER_LOCATION'][0]['VALUE']];

            // Logic for dresscode:sale.basket.basket component
            if (!$locationId && isset($arUserResult['ORDER_PROP']['PROPERTIES']))
                $locationId = $arUserResult['ORDER_PROP']['PROPERTIES'][$props[$arUserResult['PERSON_TYPE_ID']]['ORDER_LOCATION'][0]['VALUE']]['LOCATION']['CODE'];
            // --

            $ppCityData = PickPoint\Helper::checkPPCityAvailable($locationId);

            if ($ppCityData['STATUS']) {
                $cityNamePP = PickPoint\Helper::getEngCityName($ppCityData['DATA']['NAME_RU']);
            }
        } elseif ($autoLocation) {

            $locationId = PickPoint\Helper::getLocationIdByGeoPosition();

            $ppCityData = PickPoint\Helper::checkPPCityAvailable($locationId);

            if ($ppCityData['STATUS']) {
                $cityNamePP = PickPoint\Helper::getEngCityName($ppCityData['DATA']['NAME_RU']);
            }
        }
		
        if (!empty($cityNamePP) || !empty($str_from_city)){
            if ($str_from_city){
                $widgetParamsString .= ", { fromcity: '". $str_from_city . "'";
                $widgetParamsString .= ", ikn: '". $pp_ikn_number . "'";
            }
            if ($cityNamePP){
                $widgetParamsString .= ", cities:['" . $cityNamePP . "']";
            }
            $widgetParamsString .= '}';
        }

        ob_start();
        require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/block.php';
        $content = ob_get_contents();
        ob_end_clean();

        $keys = ['PP_ID', 'PP_ADDRESS', 'PP_ZONE', 'PP_NAME', 'PP_COEFF', 'PP_DELIVERY_MIN', 'PP_DELIVERY_MAX'];
        foreach ($keys as $key) {
            $_REQUEST[$key] = (array_key_exists($key, $_REQUEST) && strlen($_REQUEST[$key]) > 0) ? $_REQUEST[$key] :
                (array_key_exists('PICKPOINT', $_SESSION) ? $_SESSION['PICKPOINT'][$key] : '');
        }

        $content .= '<input id="pp_address" type="hidden" name="PP_ADDRESS" value="'.$_REQUEST['PP_ADDRESS'].'"/>
			<input id="pp_zone" type="hidden" name="PP_ZONE" value="'.$_REQUEST['PP_ZONE'].'"/>
			<input id="pp_name" type="hidden" name="PP_NAME" value="'.$_REQUEST['PP_NAME'].'"/>
			<input id="pp_coeff" type="hidden" name="PP_COEFF" value="'.$_REQUEST['PP_COEFF'].'"/>
			<input id="pp_delivery_min" type="hidden" name="PP_DELIVERY_MIN" value="'.$_REQUEST['PP_DELIVERY_MIN'].'"/>
			<input id="pp_delivery_max" type="hidden" name="PP_DELIVERY_MAX" value="'.$_REQUEST['PP_DELIVERY_MAX'].'"/>';

        // Check if sale module was converted to new structure, introduced in v15.5.10
        if (!$DB->TableExists('b_sale_delivery_srv')) 
		{ 
			//old version
            if (isset($arResult['DELIVERY']['pickpoint'])) 
			{
                $arResult['DELIVERY']['pickpoint']['PROFILES']['postamat']['DESCRIPTION'] .= $content;
                $arResult['DELIVERY']['pickpoint']['PROFILES']['postamat']['DESCRIPTION']
                    .= '<input id="pp_id" type="hidden" name="PP_ID" value="'.$_REQUEST['PP_ID'].'" data-delivery-id="pickpoint:postamat"/>';
            }
        } 
		else 
		{ 
			//new version
            foreach ($arResult['DELIVERY'] as &$arDelivery) 
			{
				if (isset($arDelivery['ID']))
					$arFilter = array('ID' => $arDelivery['ID']);
				elseif (isset($arDelivery['SID']))
					$arFilter = array('CODE' => $arDelivery['SID']);
				// Some strange issues with no data in $arResult['DELIVERY'] subarray
                if (empty($arFilter))
                    continue;

                $serviceRes = Bitrix\Sale\Delivery\Services\Table::getList(
                    array(
                        'filter' => $arFilter,
                        'select' => array('CODE'),
                    )
                );
                $arDeliveryCode = $serviceRes->fetch();

                if (strpos($arDeliveryCode['CODE'], 'pickpoint') !== false) 
				{
					// Fix for case while OnSaleComponentOrderOneStepDelivery event called multiple times
					$isAdded = ((function_exists('mb_stripos') && defined("BX_UTF")) ? mb_stripos($arDelivery['DESCRIPTION'], 'id="pp_id"') : stripos($arDelivery['DESCRIPTION'], 'id="pp_id"'));
					if ($isAdded !== false)
						continue;					
					// --
					
                    $arDelivery['DESCRIPTION'] .= $content;
                    $arDelivery['DESCRIPTION'] .= '<input id="pp_id" type="hidden" name="PP_ID" value="'.$_REQUEST['PP_ID'].'" data-delivery-id="'.$arDelivery['ID'].'"/>';
						
					// Support for old sale.order.ajax component with new sale module
					if (array_key_exists('PROFILES', $arDelivery) && $arDelivery['PROFILES']['postamat']['CHECKED'] == 'Y')
					{
						$arDelivery['PROFILES']['postamat']['DESCRIPTION'] .= str_replace(array("\r\n", "\n"), "", $content);						
						$arDelivery['PROFILES']['postamat']['DESCRIPTION'] .= '<input id="pp_id" type="hidden" name="PP_ID" value="'.$_REQUEST['PP_ID'].'" data-delivery-id="'.$arDelivery['ID'].'"/>';
					}	
                }
            }
        }
    }

    public static function addPickpointJs()
    {
        global $APPLICATION;
        $MODULE_ID = static::$moduleId;
		$jsParams = array();

        // Pickpoint widget JS
        $APPLICATION->AddHeadString('<script type="text/javascript" src="//pickpoint.ru/select/postamat.js" charset="utf-8"></script>');
		
        /*if (defined('BX_UTF') && BX_UTF == true) {
            $APPLICATION->AddHeadScript("/bitrix/js/{$MODULE_ID}/script_utf.js");
        } else {
            $APPLICATION->AddHeadScript("/bitrix/js/{$MODULE_ID}/script.js");
        }*/        
		
		$jsParams["postamatAddressInputs"] = array();
				
		$addressProps = CSaleOrderProps::GetList(array(), array('CODE' => Option::get('pp_postamat_picker')));		
		while ($prop = $addressProps->Fetch())
			$jsParams["postamatAddressInputs"][] = $prop['ID'];				
		
		// Custom handlers for JS
		$jsParams["handlers"] = array('onAfterPostamatSelected' => '');
		
		foreach (GetModuleEvents($MODULE_ID, "onJSHandlersSet", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$jsParams["handlers"]));		
		
		// Module JS extension
		$ppds = array(	
			"js" => "/bitrix/js/{$MODULE_ID}/pickpoint_deliveryservice.js",		
			"lang" => "/bitrix/modules/{$MODULE_ID}/lang/".LANGUAGE_ID."/js/pickpoint_deliveryservice.php",	
			"rel" => array("jquery"),
			);
			
		// Block JQuery loading in some cases
		if (defined('PPDS_DONT_LOAD_JQUERY') || (method_exists('CJSCore', 'isExtensionLoaded') && (CJSCore::isExtensionLoaded("jquery") || CJSCore::isExtensionLoaded("jquery2") || CJSCore::isExtensionLoaded("jquery3"))))	
			unset($ppds['rel']);
		
		if (!CJSCore::IsExtRegistered("pickpoint_deliveryservice"))
			CJSCore::RegisterExt("pickpoint_deliveryservice", $ppds);		
			
		if (method_exists('CJSCore', 'isExtensionLoaded') && !CJSCore::isExtensionLoaded("pickpoint_deliveryservice"))	
			CJSCore::Init(array("pickpoint_deliveryservice"));		
		else // No method = no check
			CJSCore::Init(array("pickpoint_deliveryservice"));		
		
		// Init extension with params
		$APPLICATION->AddHeadString("<script type=\"text/javascript\">$(document).ready(function() {PickpointDeliveryservice.init(".CUtil::PhpToJSObject($jsParams).");});</script>");
    }
}
