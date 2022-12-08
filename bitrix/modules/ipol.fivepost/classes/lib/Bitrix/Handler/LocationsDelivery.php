<?
namespace Ipol\Fivepost\Bitrix\Handler;

use \Ipol\Fivepost\PointsTable;
use \Ipol\Fivepost\LocationsTable;

/**
 * Class LocationsDelivery
 * @package namespace Ipol\Fivepost\Bitrix\Handler
 */
class LocationsDelivery
{
    /**
     * Get delivery service location data by bitrix code
     * Return array with data or empty array if fail
     *
     * @param string $code bitrix location code
     * @return array
     */
    public static function getByBitrixCode($code)
    {
        $location = LocationsTable::getList([
            'select' => ['ID', 'LOCALITY_FIAS_CODE', 'BITRIX_CODE'],
            'filter' => ['=BITRIX_CODE' => $code],
            'limit' => 1, // Just for sure
        ])->fetch();

        return self::getDataFromPointsTable($location);
    }

    /**
     * Get delivery service location data by FIAS guid
     * Return array with data or empty array if fail
     *
     * @param string $guid FIAS guid
     * @return array
     */
    public static function getByFiasGuid($guid)
    {
        $location = LocationsTable::getList([
            'select' => ['ID', 'LOCALITY_FIAS_CODE', 'BITRIX_CODE'],
            'filter' => ['=LOCALITY_FIAS_CODE' => $guid],
            'limit' => 1, // Just for sure
        ])->fetch();

        return self::getDataFromPointsTable($location);
    }

    /**
     * Try to get additional data about desired location
     * Return combined data or empty array
     *
     * @param array $location @see LocationsDelivery::getByBetrixCode()
     * @return array
     */
    private static function getDataFromPointsTable($location)
    {
        $result = array();

        if (!empty($location))
        {
            $pointData = PointsTable::getList([
                'select' => ['LOCALITY_FIAS_CODE', 'ADDRESS_COUNTRY', 'ADDRESS_REGION', 'ADDRESS_CITY'],
                'filter' => ['=LOCALITY_FIAS_CODE' => $location['LOCALITY_FIAS_CODE'], '=SYNC_IS_ACTIVE' => 'Y'], // Cause we need only active points
                'limit' => 1, // Assume all locations with same FIAS code has equal data otherwise GTFO
            ])->fetch();

            if (!empty($pointData))
            {
                $result = array(
                    'ID'            => $location['ID'],
                    'LOCALITY_CODE' => $location['LOCALITY_FIAS_CODE'],
                    'BITRIX_CODE'   => $location['BITRIX_CODE'],
                    'NAME'          => $pointData['ADDRESS_CITY'],
                    'COUNTRY'       => $pointData['ADDRESS_COUNTRY'],
                    'REGION'        => $pointData['ADDRESS_REGION'],
                    // Cause no data about this in 5Post API
                    'AREA'          => false,
                    'PARENT_ID'     => false,
                );
            }
        }

        return $result;
    }
}