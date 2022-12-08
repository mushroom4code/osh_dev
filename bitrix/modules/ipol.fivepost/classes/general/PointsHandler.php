<?
namespace Ipol\Fivepost;

use \Ipol\Fivepost\Bitrix\Controller\SyncPoints;
use \Ipol\Fivepost\Bitrix\Controller\Referencer;
use \Ipol\Fivepost\Core\Delivery\CargoItem;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

/**
 * Class PointsHandler
 * @package Ipol\Fivepost
 */
class PointsHandler extends AbstractGeneral
{
    /**
     * Refresh point and rates data
     *
     * @param int $pageSize
     * @param int $pageNumber
     * @return \Bitrix\Main\Result
     */
    public static function refreshPointsAndRates($pageSize, $pageNumber)
    {
        $controller = new SyncPoints();

        $result = $controller->refreshPointsAndRates($pageSize, $pageNumber);
        return $result;
    }

    /**
     * Toggle unused points inactive
     *
     * @param int $lastDate timestamp
     * @return \Bitrix\Main\Result
     */
    public static function toggleInactivePoints($lastDate)
    {
        $controller = new SyncPoints();

        $result = $controller->toggleInactivePoints($lastDate);
        return $result;
    }

    /**
     * Make statistic info about loaded points
     *
     * @return \Bitrix\Main\Result
     */
    public static function makeStatistic()
    {
        return SyncPoints::makeStatistic();
    }

    /**
     * Get points data.
     * All params are optional, if set to false, points returned regardless of this param
     *
     * @param CargoItem|false $cargo order object as one deliverable item
     * @param string|false $paySystem selected payment type @see DeliveryHandler::PAYSYSTEM_CASH and other constants
     * @param string|false $possiblyId bitrix location Id or Code
     * @return \Bitrix\Main\Result
     */
    public static function getPoints($cargo = false, $paySystem = false, $possiblyId = false)
    {
        $controller = new Referencer();

        /*
        // Selected fields can be
            'ID',
            'POINT_GUID', 
            'NAME',
            'PARTNER_NAME',
            'TYPE',
            'ADDITIONAL'
            'WORK_HOURS',
            'FULL_ADDRESS',
            'ADDRESS_COUNTRY',
            'ADDRESS_ZIP_CODE',
            'ADDRESS_REGION',
            'ADDRESS_REGION_TYPE',
            'ADDRESS_CITY',
            'ADDRESS_CITY_TYPE',
            'ADDRESS_STREET',
            'ADDRESS_HOUSE',
            'ADDRESS_BUILDING',
            'ADDRESS_LAT',
            'ADDRESS_LNG',
            'ADDRESS_METRO_STATION',
            'LOCALITY_FIAS_CODE',
            'MAX_CELL_WIDTH',
            'MAX_CELL_HEIGHT',
            'MAX_CELL_LENGTH',
            'MAX_CELL_WEIGHT',
            'MAX_CELL_DIMENSIONS_HASH',
            'RETURN_ALLOWED',            
            'PHONE',
            'CASH_ALLOWED',
            'CARD_ALLOWED',
            'LOYALTY_ALLOWED',	
            'EXT_STATUS',
            'DELIVERY_SL',
            'LASTMILEWAREHOUSE_ID',
            'LASTMILEWAREHOUSE_NAME',
        */

        // Typical select for PVZ widget
        $select = [
            'POINT_GUID',
            'NAME',
            'TYPE',
            'ADDITIONAL',
            'WORK_HOURS',
            'FULL_ADDRESS',
            'ADDRESS_LAT',
            'ADDRESS_LNG',
            'ADDRESS_METRO_STATION',
            // Debug reasons
            'LOCALITY_FIAS_CODE',
            'MAX_CELL_WEIGHT',
            'MAX_CELL_DIMENSIONS_HASH',
            // --
            'PHONE',
            'CASH_ALLOWED',
            'CARD_ALLOWED',
            'LOYALTY_ALLOWED',
            'DELIVERY_SL', // Delivery time inside, but without termIncrease (raw value from table)
        ];

        $result = $controller->getPoints($cargo, $paySystem, $possiblyId, $select);
        return $result;
    }

    /**
     * Get all existing rates from rates table
     *
     * @return \Bitrix\Main\Result
     */
    public static function getExistingRates()
    {
        $controller = new Referencer();
        $result = $controller->getExistingRates();

        return $result;
    }
}