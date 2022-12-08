<?php
use \PickPoint\DeliveryService\Option;

CModule::IncludeModule('sale');
CModule::IncludeModule('pickpoint.deliveryservice');

include GetLangFileName(dirname(__FILE__).'/', '/delivery_pickpoint.php');

class CDeliveryPickPoint
{
	private static $isRequiredOptionsSet = null;
	
    public static function Init()
    {
        return array(
            'SID' => 'pickpoint',
            'NAME' => GetMessage('PP_NAME'),
            'DESCRIPTION' => GetMessage('DESCRIPTION'),
            'DESCRIPTION_INNER' => GetMessage('DESCRIPTION_INNER'),
            'BASE_CURRENCY' => COption::GetOptionString('sale', 'default_currency', 'RUB'),
            'HANDLER' => __FILE__,
            'GETEXTRAINFOPARAMS' => 'CDeliveryPickPoint::GetExtraInfoParams',
            'DBGETSETTINGS' => array(
                'CDeliveryPickPoint',
                'GetSettings',
            ),
            'DBSETSETTINGS' => array(
                'CDeliveryPickPoint',
                'SetSettings',
            ),
            'GETCONFIG' => array(
                'CDeliveryPickPoint',
                'GetConfig',
            ),

            'COMPABILITY' => array(
                'CDeliveryPickPoint',
                'Compability',
            ),
            'CALCULATOR' => array(
                'CDeliveryPickPoint',
                'Calculate',
            ),
            'PROFILES' => array(
                'postamat' => array(
                    'TITLE' => GetMessage('PICKPOINT_MAIN'),
                    'DESCRIPTION' => GetMessage('PICKPOINT_SMALL_DESCRIPTION'),

                    'RESTRICTIONS_WEIGHT' => array(0),
                    'RESTRICTIONS_SUM' => array(0),
                ),
            ),
        );
    }

    public static function GetConfig()
    {
        $arConfig = array(
            'CONFIG_GROUPS' => array(
                'postamat' => GetMessage('PICKPOINT_MAIN'),
            ),
            'CONFIG' => array(),
        );

        return $arConfig;
    }

    public static function GetSettings($strSettings)
    {
        return unserialize($strSettings);
    }

    public static function SetSettings($arSettings)
    {
        foreach ($arSettings as $key => $value) {
            if (strlen($value) > 0) {
                $arSettings[$key] = doubleval($value);
            } else {
                unset($arSettings[$key]);
            }
        }

        return serialize($arSettings);
    }

    /**
     * @deprecated
     */
    public static function __GetLocationPrice($LOCATION_ID, $arConfig)
    {
        if (!CheckPickpointLicense(COption::GetOptionString('pickpoint.deliveryservice', 'pp_ikn_number', ''))) {
            return false;
        }

        $obCity = CPickpoint::SelectCityByBXID($LOCATION_ID);
        if ($arCity = $obCity->Fetch()) {
            if ($arCity['ACTIVE'] == 'Y') {
                return floatval($arCity['PRICE']);
            }
        }

        return false;
    }

    /**
     * @deprecated
     */
    public static function __GetPrice($arOrder)
    {
        return CPickpoint::Calculate($arOrder);
    }

    public static function Compability($arOrder, $arConfig)
    {
        // Check required options
        if (is_null(self::$isRequiredOptionsSet))
            self::$isRequiredOptionsSet = Option::isRequiredOptionsSet();

        if (!self::$isRequiredOptionsSet)
            return array();

        $status = PickPoint\Helper::checkPPCityAvailable($arOrder['LOCATION_TO']);

        if ($status['STATUS']) {
            return array('postamat');
        } else {
            return array();
        }
    }
	
    public static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
		$arReturn = array();
		
		// Check required options
		if (is_null(self::$isRequiredOptionsSet))
			self::$isRequiredOptionsSet = Option::isRequiredOptionsSet();		
		
		if (!self::$isRequiredOptionsSet)
		{
			$arReturn = array("RESULT" => "ERROR", "TEXT" => GetMessage("PP_DELIVERY_ERROR_MODULE_OPTIONS_NOT_SET"));		
		}
		else
		{
			$arReturn = array("RESULT" => "OK", "VALUE" => CPickpoint::Calculate($arOrder));
								
			// Delivery time
			if (isset($_SESSION["PICKPOINT"]["PP_DELIVERY_MIN"]) && isset($_SESSION["PICKPOINT"]["PP_DELIVERY_MAX"]))
			{
				$termInc = intval(Option::get('pp_term_inc'));
				
				$deliveryMin = intval($_SESSION["PICKPOINT"]["PP_DELIVERY_MIN"]) + $termInc;
				$deliveryMax = intval($_SESSION["PICKPOINT"]["PP_DELIVERY_MAX"]) + $termInc;				
				
				$arReturn["TRANSIT"] = ($deliveryMin == $deliveryMax) ? $deliveryMax : $deliveryMin.'-'.$deliveryMax;
								
				if ($deliveryMax > 4 && $deliveryMax < 21 || $deliveryMax == 0)
					$arReturn["TRANSIT"] .= ' '.GetMessage("PP_DELIVERY_DAYS");
				else
				{
					$lst = $deliveryMax % 10;
					if ($lst == 1)
						$arReturn["TRANSIT"] .= ' '.GetMessage("PP_DELIVERY_DAY");
					elseif ($lst < 5)
						$arReturn["TRANSIT"] .= ' '.GetMessage("PP_DELIVERY_DAYA");
					else
						$arReturn["TRANSIT"] .= ' '.GetMessage("PP_DELIVERY_DAYS");
				}				
			}			
		}
		
		foreach (GetModuleEvents("pickpoint.deliveryservice", "onCalculate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, Array(&$arReturn, $profile, $arConfig, $arOrder));		
		
        return $arReturn;
    }

    public static function isCityAvailable($cityName, $regionName)
    {
        $cities = array();
        $isInRegion = strlen($regionName) > 0;
        if (($citiesHandle = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/cities.csv', 'r'))
            !== false
        ) {
            while (($row = fgets($citiesHandle)) !== false) {
                $data = explode(';', $row);
                if ($isInRegion) {
                    $cities[$data[3]][$data[1]] = ('true' === trim($data[4]));
                } else {
                    $cities[$data[1]] = ('true' === trim($data[4]));
                }
            }
            fclose($citiesHandle);
        }

        if ($isInRegion) {
            if (isset($cities[$regionName][$cityName])) {
                return true;
            }
            $regionName = str_replace(
                GetMessage('PICKPOINT_REGION_WORD_FULL'),
                GetMessage('PICKPOINT_REGION_WORD_SHORT'),
                $regionName
            );
            if (isset($cities[$regionName][$cityName])) {
                return true;
            }
            $regionName = str_replace(
                GetMessage('PICKPOINT_REGION_WORD_SHORT'),
                GetMessage('PICKPOINT_REGION_WORD_FULL'),
                $regionName
            );
            if (isset($cities[$regionName][$cityName])) {
                return true;
            }
        } else {
            if (isset($cities[$cityName])) {
                return true;
            }
        }

        return false;
    }
}

AddEventHandler(
    'sale',
    'onSaleDeliveryHandlersBuildList',
    array(
        'CDeliveryPickPoint',
        'Init',
    )
);
