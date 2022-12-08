<?
namespace Ipol\Fivepost;

use \Ipol\Fivepost\Bitrix\Controller\SyncLocations;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use Ipol\Fivepost\Bitrix\Handler\LocationsDelivery;

/**
 * Class LocationsHandler
 * @package Ipol\Fivepost
 */
class LocationsHandler extends AbstractGeneral
{
    /**
     * Refresh location data
     *
     * @return \Bitrix\Main\Result
     */
    public static function refreshLocations()
    {
        $controller = new SyncLocations();

        $result = $controller->refreshLocations();
        return $result;
    }

    /**
     * Refresh locations file
     *
     * @return \Bitrix\Main\Result
     */
    public static function loadLocationsFile()
    {
        $controller = new SyncLocations();

        $result = $controller->loadLocationsFile();
        return $result;
    }

    /**
     * Make statistic info about loaded locations
     *
     * @return \Bitrix\Main\Result
     */
    public static function makeStatistic()
    {
        return SyncLocations::makeStatistic();
    }



    public static function getCities()
    {
        $arCities = array();

        $obLocations = LocationsTable::getList();
        while($obLocation = $obLocations->Fetch()){
            $req = LocationsDelivery::getByBitrixCode($obLocation['BITRIX_CODE']);

            if($req && !empty($req)){
                $arCities [$req['LOCALITY_CODE']]= array(
                    'NAME'    => $req['NAME'],
                    'COUNTRY' => $req['COUNTRY'],
                    'REGION'  => $req['REGION'],
                    'CODE'    => $req['LOCALITY_CODE'],
                );
            }
        }

        return $arCities;
    }
}