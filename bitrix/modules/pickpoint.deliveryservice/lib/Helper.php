<?php
namespace PickPoint;

use CSaleOrderProps;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\LocationTable;

Loc::loadMessages(__FILE__);

/**
 * Class Helper
 * @package PickPoint
 */
class Helper
{
    const MODULE_ID = 'pickpoint.deliveryservice';

    /**
     * Get Bitrix order props
     *
     * @param bool $isLocation
     * @return array
     */
    public static function getOrderProps($isLocation = false)
    {
        $arAllProps = [];
        $arUTAllProps = [];

        $arFilter = [
            'ACTIVE' => 'Y',
        ];

        if ($isLocation) {
            $arFilter = array_merge($arFilter, [
                'IS_LOCATION' => 'Y'
            ]);
        }

        $hProps = CSaleOrderProps::GetList(
            [
                'SORT' => 'ASC'
            ],
            $arFilter,
            false,
            false,
            []
        );

        while ($row = $hProps->Fetch()) {
            $arAllProps[$row['ID']] = $row;
            $arUTAllProps[$row['PERSON_TYPE_ID']][$row['ID']] = $row;
        }

        return [
            'ALL_PROPS' => $arAllProps,
            'PERSON_PROPS' => $arUTAllProps
        ];
    }

    /**
     * Checks if given Bitrix location exists in cities.csv
     *
     * @param mixed $location Bitrix location ID or code
     * @return array
     */
    public static function checkPPCityAvailable($location)
    {       		
		// Check if it's location code or location id
		if (strcmp(strval(intval($location)), strval($location)) === 0)
			$filter = ['ID' => $location];		
		else
			$filter = ['CODE' => $location];
		
		$filter[] = ['=NAME.LANGUAGE_ID' => LANGUAGE_ID];
		
		$locationData = LocationTable::getList(
            [
                'filter' => $filter,
                'select' => [
                    'NAME_RU' => 'NAME.NAME'
                ]
            ]
        )->fetchAll()[0];

        if (self::isCityAvailable($locationData['NAME_RU'])) {
            return [
                'STATUS' => true,
                'DATA' => $locationData
            ];
        } else {
            return [
                'STATUS' => false
            ];
        }
    }

    /**
     * Get english city name from cities.csv, used in postamat widget as 'cities' param
     *
     * @param string $ruCityNameOrig
     * @param string $ruCityNameLang
     * @return false|string
     */
    public static function getEngCityName($ruCityNameOrig, $ruCityNameLang = '')
    {
        if (($citiesHandle = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/cities.csv', 'r')) !== false) {
			$convertedCityNameToYO = str_replace(
                Loc::getMessage('EPP_E'),
                Loc::getMessage('EPP_E_CONVERTED'),
                $ruCityNameOrig
            );
	
			$convertedCityNameToYE = str_replace(
                Loc::getMessage('EPP_E_CONVERTED'),
				Loc::getMessage('EPP_E'),                
                $ruCityNameOrig
            );			
			
            while (($row = fgets($citiesHandle)) !== false) {
                $data = array_map('trim', explode(';', $row));
                if ($ruCityNameOrig == $data[1] || $ruCityNameLang == $data[1] || $convertedCityNameToYO == $data[1] || $convertedCityNameToYE == $data[1]) {
                    return $data[2];
                }
            }
            fclose($citiesHandle);
        }
        return false;
    }

    /**
     * Get location by GeoIP
     *
     * @return int
     */
    public static function getLocationIdByGeoPosition()
    {
        $ipAddress = GeoIp\Manager::getRealIp();

        return \Bitrix\Sale\Location\GeoIp::getLocationId($ipAddress);
    }

    /**
     * Checks if given city name exists in cities.csv
     *
     * @param string $cityName
     * @return bool
     */
    public static function isCityAvailable($cityName)
    {
        if (($citiesHandle = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/cities.csv', 'r')) !== false) {
            $convertedCityNameToYO = str_replace(
                Loc::getMessage('EPP_E'),
                Loc::getMessage('EPP_E_CONVERTED'),
                $cityName
            );
	
			$convertedCityNameToYE = str_replace(
                Loc::getMessage('EPP_E_CONVERTED'),
				Loc::getMessage('EPP_E'),                
                $cityName
            );

            while (($row = fgets($citiesHandle)) !== false) {
                $data = explode(';', $row);
                if ($cityName == $data[1] || $convertedCityNameToYO == $data[1] || $convertedCityNameToYE == $data[1]) {
                    return true;
                }
            }
            fclose($citiesHandle);
        }

        return false;
    }

    /**
     * @param int $paySystemId
     * @return bool
     * @throws \Bitrix\Main\SystemException
     */
    public static function checkPPPaySystem($paySystemId)
    {
        $dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getList(
            [
                'filter' => [
                    'ID' => $paySystemId,
                ],
                'select' => [
                    'ACTION_FILE'
                ]

            ]
        );

        if ($arPaySystem = $dbRes->fetch()) {
            if (substr_count($arPaySystem['ACTION_FILE'], 'pickpoint.deliveryservice')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     * @throws \Bitrix\Main\SystemException
     */
    public static function setPageElementsCount($value = 50)
    {
        Option::set(self::MODULE_ID, 'show_elements_count', $value);
    }
}