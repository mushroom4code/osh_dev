<?
namespace Ipol\Fivepost\Bitrix\Controller;

use \Ipol\Fivepost\LocationsTable;
use \Ipol\Fivepost\Bitrix\Tools;
use \Ipol\Fivepost\Bitrix\Adapter;

use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;

/**
 * Class SyncLocations
 * @package Ipol\Fivepost\Bitrix\Controller
 */
class SyncLocations extends AbstractController
{
    // Common sync helpers
    use SyncUtilities;

    // \Bitrix\Main\ErrorCollection
    public $errors;

    // \Bitrix\Main\Result
    public $result;

    public function __construct()
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);
        $this->errors = new \Bitrix\Main\ErrorCollection();
        $this->result = new \Bitrix\Main\Result();
    }

    /**
     * Convert location data from Application to DB format
     *
     * @param array $location
     * @return array
     */
    public static function prepareLocationData($location)
    {
        $data = array(
            'LOCALITY_FIAS_CODE'        => $location['LOCALITY_FIAS_CODE'],
            'BITRIX_CODE'               => $location['BITRIX_CODE'],
        );

        $data['SYNC_HASH']         = Adapter::makeSyncHash($data);
        $data['SYNC_IS_UPDATABLE'] = 'Y';
        $data['SYNC_LAST_DATE']    = new DateTime();

        return $data;
    }

    /**
     * Refresh locations data in DB.
     *
     * @return \Bitrix\Main\Result
     */
    public function refreshLocations()
    {
        $result = $this->getLocations();

        if ($result->isSuccess())
        {
            $data = $result->getData();

            $locations = [];
            foreach ($data['LOCATIONS'] as $location)
            {
                $locations[$location['LOCALITY_FIAS_CODE']] = self::prepareLocationData($location);
            }

            // Get existing locations data from DB
            $existedLocationsDB = LocationsTable::getList(['select' => ['ID', 'LOCALITY_FIAS_CODE', 'SYNC_IS_UPDATABLE', 'SYNC_HASH'], 'filter' => ['=LOCALITY_FIAS_CODE' => array_keys($locations)]]);
            $existedLocations = [];
            while ($tmp = $existedLocationsDB->fetch())
            {
                $existedLocations[$tmp['LOCALITY_FIAS_CODE']] = array('ID' => $tmp['ID'], 'SYNC_IS_UPDATABLE' => $tmp['SYNC_IS_UPDATABLE'], 'SYNC_HASH' => $tmp['SYNC_HASH']);
            }

            /* Log */ $this->toLog(['STATE' => 'Locations loading start', 'DATE' => (new DateTime())->toString()]);

            foreach ($locations as $guid => $location)
            {
                // Existing location
                if (array_key_exists($guid, $existedLocations))
                {
                    $locationPrimaryId = $existedLocations[$guid]['ID'];

                    // Skip location data refresh if it's marked as not updatable
                    if ($existedLocations[$guid]['SYNC_IS_UPDATABLE'] !== 'Y')
                    {
                        /* Log */ $this->toLog(['STATE' => 'Not updatable location detected', 'EXISTED' => $existedLocations[$guid], 'PRETENDER' => $location]);
                        continue;
                    }

                    $toUpdate = ($existedLocations[$guid]['SYNC_HASH'] == $location['SYNC_HASH']) ? ['SYNC_LAST_DATE' => $location['SYNC_LAST_DATE']] : $location;
                    $result = LocationsTable::update($locationPrimaryId, $toUpdate);
                    $this->collectPossibleErrors($result, 'Location '.$guid);

                    /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Location update', 'LOCATION_GUID' => $guid, 'LOCATION_DATA' => $toUpdate]);
                }
                else
                {
                    // New location to add
                    $result = LocationsTable::add($location);
                    $this->collectPossibleErrors($result, 'Location '.$location['LOCALITY_FIAS_CODE']);

                    /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Location add', 'LOCATION_GUID' => $location['LOCALITY_FIAS_CODE'], 'LOCATION_DATA' => $location]);
                }
                //break;
            }

            /* Log */ $this->toLog(['STATE' => 'Locations loading end', 'DATE' => (new DateTime())->toString()]);
        }
        else
            $this->errors->add($result->getErrorCollection()->toArray());

        if (!$this->errors->isEmpty())
        {
            foreach ($this->errors as $err)
                $this->result->addError($err);
        }

        return $this->result;
    }

    /**
     * Get locations data from external source
     *
     * @return \Bitrix\Main\Result
     */
    public function getLocations()
    {
        $result = new Result();
        $existedLocations = [];

        /*
        // Get existing locations data
        $existedLocationsDB = \Ipol\Fivepost\LocationDadataTable::getList(['select' => ['LOCALITY_FIAS_CODE' => 'FIVEPOST_FIAS_GUID', 'BITRIX_CODE'], 'filter' => ['!=BITRIX_CODE' => '-'], 'order' => ['ID' => 'ASC']]);
        while ($tmp = $existedLocationsDB->fetch())
        {
            $existedLocations[] = $tmp;
        }
        */

        $tmp = self::tryLocationsFile();

        foreach ($tmp as $fias => $bitrix)
            $existedLocations[] = array('LOCALITY_FIAS_CODE' => $fias, 'BITRIX_CODE' => $bitrix);

        if (empty($existedLocations))
        {
            $result->addError(new Error('Can not get locations data, zero locations found.'));
        }
        else
        {
            $result->setData(['LOCATIONS' => $existedLocations]);
        }

        return $result;
    }

    /**
     * Get path to Locations JSON file
     *
     * @return string
     */
    public static function getLocationsFilePath()
    {
        return $_SERVER['DOCUMENT_ROOT'].Tools::getJSPath().'locations.json';
    }

    /**
     * Try checks if content of current locations data file usable for sync
     * Return array with data or empty array if fail
     *
     * @return array
     */
    public static function tryLocationsFile()
    {
        if ($file = file_get_contents(self::getLocationsFilePath()))
        {
            $test = json_decode($file, true);
            return ((is_null($test) || json_last_error() !== JSON_ERROR_NONE) ? array() : $test);
        }
        return array();
    }

    /**
     * Make and store locally locations data file
     *
     * @return \Bitrix\Main\Result
     */
    public function makeLocationsFile()
    {
        $existedLocationsDB = LocationsTable::getList(['select' => ['ID', 'LOCALITY_FIAS_CODE', 'BITRIX_CODE'], 'order' => ['ID' => 'ASC']]);
        $existedLocations = [];
        while ($tmp = $existedLocationsDB->fetch())
        {
            $existedLocations[$tmp['LOCALITY_FIAS_CODE']] = $tmp['BITRIX_CODE'];
        }

        if (!empty($existedLocations))
        {
            if (file_put_contents(self::getLocationsFilePath(), json_encode($existedLocations)) === false)
                $this->result->addError(new Error('Can not write locations file.'));
            else
                $this->result->setData(['LOCATIONS_STORED' => count($existedLocations)]);
        }
        else
        {
            $this->result->addError(new Error('Zero locations found in LocationsTable.'));
        }

        return $this->result;
    }

    /**
     * Load locations data file from server
     *
     * @return \Bitrix\Main\Result
     */
    public function loadLocationsFile()
    {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, 'https://ipol.ru/webService/fivepost/locations.json');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if (!in_array($code, ['200', '304']))
        {
            $this->result->addError(new Error('Can not load locations file from server.'));
        }
        else if (empty($result))
        {
            $this->result->addError(new Error('Locations file empty, skip loading.'));
        }
        else
        {
            $test = json_decode($result, true);
            if (is_null($test) || json_last_error() !== JSON_ERROR_NONE)
            {
                $this->result->addError(new Error('Locations file structure broken, skip loading.'));
            }
            else
            {
                if (file_put_contents(self::getLocationsFilePath(), $result) === false)
                    $this->result->addError(new Error('Can not save locations file after loading.'));
                else
                    $this->result->setData(['LOCATIONS_FILE_LOADED' => 'Y']);
            }
        }
        return $this->result;
    }

    /**
     * Make statistic info about loaded locations
     *
     * @return \Bitrix\Main\Result
     */
    public static function makeStatistic()
    {
        $result = new Result();
        $result->setData(['LOCATIONS_LOADED' => LocationsTable::getDataCount()]);

        return $result;
    }
}