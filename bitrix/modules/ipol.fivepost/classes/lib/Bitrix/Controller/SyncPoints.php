<?
namespace Ipol\Fivepost\Bitrix\Controller;

use \Ipol\Fivepost\PointsTable;
use \Ipol\Fivepost\RatesTable;
use \Ipol\Fivepost\Bitrix\Adapter;

use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;

/**
 * Class SyncPoints
 * @package Ipol\Fivepost\Bitrix\Controller
 */
class SyncPoints extends AbstractController
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
     * Get points data from 5Post API
     *
     * @param int $pagesize number of points in one page
     * @param int $pagenumber number of current page (from 0)
     * @return \Bitrix\Main\Result
     */
    public function getPickupPoints($pagesize = 1000, $pagenumber = 0)
    {
        $result = new Result();

        // Cause default 6 sec is too small
        $this->application->setTimeout(30);

        $this->application->setCache(null);
        $answer = $this->application->getPickupPoints($pagenumber, $pagesize);

        if ($answer->isSuccess())
        {
            $data = $answer->getResponse();

            $result->setData([
                'TOTAL_PAGES'        => $data->getTotalPages(),
                'TOTAL_ELEMENTS'     => $data->getTotalElements(),
                'NUMBER_OF_ELEMENTS' => $data->getNumberOfElements(),
                'CONTENT'            => $data->getContent()->getFields(),
            ]);
        }
        else
        {
            if ($this->application->getErrorCollection())
            {
                $this->application->getErrorCollection()->reset();
                while ($error = $this->application->getErrorCollection()->getNext())
                {
                    $result->addError(new Error('Error while getting points data from API: '.$error->getMessage()));
                }
            }
            else
                $result->addError(new Error('Error while getting points data from API, but no error messages get from application.'));
        }

        return $result;
    }

    /**
     * Convert point work hours data, drop unused info
     *
     * @param array $workHours
     * @return array
     */
    public static function preparePointWorkHours($workHours)
    {
        $days = array_flip(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']);
        $result = [];

        foreach ($workHours as $day)
        {
            // Fuck seconds
            $tmpO = explode(':', $day['opensAt']);
            unset($tmpO[2]);

            $tmpC = explode(':', $day['closesAt']);
            unset($tmpC[2]);

            $result[$days[$day['day']]] = ['O' => implode(':', $tmpO), 'C' => implode(':', $tmpC)];
        }

        // Cause da order of days in API response cannot be trusted
        ksort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * Convert point data from Application to DB format
     *
     * @param array $point
     * @return array
     */
    public static function preparePointData($point)
    {
        $data = array(
            'POINT_GUID'                => $point['id'],
            'NAME'                      => $point['name'],
            'PARTNER_NAME'              => $point['partnerName'],
            'TYPE'                      => $point['type'],
            'ADDITIONAL'                => $point['additional'],
            'WORK_HOURS'                => serialize(self::preparePointWorkHours($point['workHours'])), // Drop unused info
            'FULL_ADDRESS'              => $point['fullAddress'],
            'ADDRESS_COUNTRY'           => $point['address']['country'],
            'ADDRESS_ZIP_CODE'          => $point['address']['zipCode'],
            'ADDRESS_REGION'            => $point['address']['region'],
            'ADDRESS_REGION_TYPE'       => $point['address']['regionType'],
            'ADDRESS_CITY'              => $point['address']['city'],
            'ADDRESS_CITY_TYPE'         => $point['address']['cityType'],
            'ADDRESS_STREET'            => $point['address']['street'],
            'ADDRESS_HOUSE'             => $point['address']['house'],
            'ADDRESS_BUILDING'          => $point['address']['building'],
            'ADDRESS_LAT'               => $point['address']['lat'],
            'ADDRESS_LNG'               => $point['address']['lng'],
            'ADDRESS_METRO_STATION'     => $point['address']['metroStation'],
            'LOCALITY_FIAS_CODE'        => $point['localityFiasCode'],
            'MAX_CELL_WIDTH'            => (int)$point['cellLimits']['maxCellWidth'],     // mm
            'MAX_CELL_HEIGHT'           => (int)$point['cellLimits']['maxCellHeight'],    // mm
            'MAX_CELL_LENGTH'           => (int)$point['cellLimits']['maxCellLength'],    // mm
            'MAX_CELL_WEIGHT'           => (int)($point['cellLimits']['maxWeight'] / 1000), // g
            'RETURN_ALLOWED'            => ($point['returnAllowed'] ? 'Y' : 'N'),
            'PHONE'                     => $point['phone'],
            'CASH_ALLOWED'              => ($point['cashAllowed'] ? 'Y' : 'N'),
            'CARD_ALLOWED'              => ($point['cardAllowed'] ? 'Y' : 'N'),
            'LOYALTY_ALLOWED'           => ($point['loyaltyAllowed'] ? 'Y' : 'N'),
            'EXT_STATUS'                => $point['extStatus'],
            'DELIVERY_SL'               => serialize($point['deliverySL']),
            'LASTMILEWAREHOUSE_ID'      => $point['lastMileWarehouse']['id'],
            'LASTMILEWAREHOUSE_NAME'    => $point['lastMileWarehouse']['name'],
        );

        $data['SYNC_HASH']         = Adapter::makeSyncHash($data);
        $data['SYNC_IS_ACTIVE']    = 'Y';
        $data['SYNC_IS_UPDATABLE'] = 'Y';
        $data['SYNC_LAST_DATE']    = new DateTime();

        $data['MAX_CELL_DIMENSIONS_HASH'] = Adapter::makeDimensionsHash($data['MAX_CELL_WIDTH'], $data['MAX_CELL_HEIGHT'], $data['MAX_CELL_LENGTH']);

        return $data;
    }

    /**
     * Convert rate data from Application to DB format
     *
     * @param array $rate
     * @return array
     */
    public static function prepareRateData($rate)
    {
        $data = array(
            'ZONE'                      => $rate['zone'],
            'RATE_TYPE'                 => $rate['rateType'],
            'RATE_CURRENCY'             => $rate['rateCurrency'],
            'RATE_VALUE'                => $rate['rateValue'],
            'RATE_EXTRA_VALUE'          => $rate['rateExtraValue'],
            'VAT'                       => $rate['vat'],
            'RATE_VALUE_WITH_VAT'       => $rate['rateValueWithVat'],
            'RATE_EXTRA_VALUE_WITH_VAT' => $rate['rateExtraValueWithVat'],
        );

        $data['SYNC_HASH'] = Adapter::makeSyncHash($data);

        return $data;
    }

    /**
     * Refresh point and rates data in DB. Add or update existing points and rates. Delete unused rates for currently active points.
     *
     * @param int $pagesize number of points in one page to refresh
     * @param int $pagenumber number of current page (from 0)
     * @return \Bitrix\Main\Result
     */
    public function refreshPointsAndRates($pagesize = 10, $pagenumber = 0)
    {
        $pointsResult = $this->getPickupPoints($pagesize, $pagenumber);
        if ($pointsResult->isSuccess())
        {
            $data = $pointsResult->getData();
            if (is_array($data['CONTENT']) && !empty($data['CONTENT']) && $data['TOTAL_ELEMENTS'] > 0)
            {
                $points = [];
                foreach ($data['CONTENT'] as $point)
                {
                    $points[$point['id']]['DATA'] = self::preparePointData($point);

                    $rates = [];
                    foreach ($point['rate'] as $rate)
                    {
                        $rates[] = self::prepareRateData($rate);
                    }
                    $points[$point['id']]['RATES'] = $rates;
                }

                // Get existing points data from DB
                $existedPointsDB = PointsTable::getList(['select' => ['ID', 'POINT_GUID', 'SYNC_IS_UPDATABLE', 'SYNC_HASH'], 'filter' => ['=POINT_GUID' => array_keys($points)]]);
                $existedPoints = [];
                $existedPointsIDs = []; // Point IDs for rate table selection
                while ($tmp = $existedPointsDB->fetch())
                {
                    $existedPoints[$tmp['POINT_GUID']] = array('ID' => $tmp['ID'], 'SYNC_IS_UPDATABLE' => $tmp['SYNC_IS_UPDATABLE'], 'SYNC_HASH' => $tmp['SYNC_HASH']);
                    $existedPointsIDs[] = $tmp['ID'];
                }

                // Get existing rates data from DB
                $existedRatesDB = RatesTable::getList(['select' => ['ID', 'POINT_ID', 'RATE_TYPE', 'SYNC_HASH'], 'filter' => ['=POINT_ID' => $existedPointsIDs]]);
                $existedRates = [];
                while ($tmp = $existedRatesDB->fetch())
                {
                    $existedRates[$tmp['POINT_ID']][$tmp['RATE_TYPE']] = array('ID' => $tmp['ID'], 'SYNC_HASH' => $tmp['SYNC_HASH']);
                }

                /* Log */ $this->toLog(['STATE' => 'Point loading start', 'DATE' => (new DateTime())->toString(), "PAGESIZE" => $pagesize, "PAGENUMBER" => $pagenumber]);

                foreach ($points as $guid => $point)
                {
                    // Existing point
                    if (array_key_exists($guid, $existedPoints))
                    {
                        $pointPrimaryId = $existedPoints[$guid]['ID'];

                        // Skip point data refresh if it's marked as not updatable
                        if ($existedPoints[$guid]['SYNC_IS_UPDATABLE'] !== 'Y')
                        {
                            /* Log */ $this->toLog(['STATE' => 'Not updatable point detected', 'EXISTED' => $existedPoints[$guid], 'PRETENDER' => $point]);

                            unset($existedRates[$pointPrimaryId]); // Prevent rates update / delete too
                            continue;
                        }

                        $toUpdate = ($existedPoints[$guid]['SYNC_HASH'] == $point['DATA']['SYNC_HASH']) ? ['SYNC_IS_ACTIVE' => 'Y', 'SYNC_LAST_DATE' => $point['DATA']['SYNC_LAST_DATE']] : $point['DATA'];
                        $result = PointsTable::update($pointPrimaryId, $toUpdate);
                        $this->collectPossibleErrors($result, 'Point '.$guid);

                        /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Point update', 'POINT_GUID' => $guid, 'POINT_DATA' => $toUpdate]);

                        // Rates sync for existing points. Linked by ipol_fivepost_points.ID <> ipol_fivepost_rates.POINT_ID
                        foreach ($point['RATES'] as $rate)
                        {
                            // Rate exists
                            if (is_array($existedRates[$pointPrimaryId]) &&
                                array_key_exists($rate['RATE_TYPE'], $existedRates[$pointPrimaryId]))
                            {
                                // Update existing rate data
                                if ($rate['SYNC_HASH'] !== $existedRates[$pointPrimaryId][$rate['RATE_TYPE']]['SYNC_HASH'])
                                {
                                    $result = RatesTable::update($existedRates[$pointPrimaryId][$rate['RATE_TYPE']]['ID'], $rate);
                                    $this->collectPossibleErrors($result, 'Point '.$guid.', rate '.$rate['RATE_TYPE']);

                                    /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Rate update', 'POINT_GUID' => $guid, 'RATE_DATA' => $rate]);
                                }
                                unset($existedRates[$pointPrimaryId][$rate['RATE_TYPE']]); // Unset updated or unchanged rate
                            }
                            else
                            {
                                // New rate to add
                                $result = RatesTable::add(array_merge(['POINT_ID' => $pointPrimaryId], $rate));
                                $this->collectPossibleErrors($result, 'Point '.$guid.', rate '.$rate['RATE_TYPE']);

                                /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Rate add', 'POINT_GUID' => $guid, 'RATE_DATA' => $rate]);
                            }
                        }

                        // Unmake old rates for current point if exists
                        if (is_array($existedRates[$pointPrimaryId]))
                        {
                            foreach ($existedRates[$pointPrimaryId] as $rate)
                            {
                                $result = RatesTable::delete($rate['ID']);
                                $this->collectPossibleErrors($result, 'Point '.$guid.', rate '.$rate['RATE_TYPE']);

                                /* Log */ $this->logRefreshResult($result, true, ['STATE' => 'Rate delete', 'POINT_GUID' => $guid, 'RATE_ID' => $rate['ID']]);
                            }
                            unset($existedRates[$pointPrimaryId]);
                        }
                    }
                    else
                    {
                        // New point to add
                        $result = PointsTable::add($point['DATA']);
                        $this->collectPossibleErrors($result, 'Point '.$point['DATA']['POINT_GUID']);

                        /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Point add', 'POINT_GUID' => $point['DATA']['POINT_GUID'], 'POINT_DATA' => $point]);

                        if ($result->isSuccess())
                        {
                            $addedPointId = $result->getId();
                            foreach ($point['RATES'] as $rate)
                            {
                                $result = RatesTable::add(array_merge(['POINT_ID' => $addedPointId], $rate));
                                $this->collectPossibleErrors($result, 'Point '.$point['DATA']['POINT_GUID'].', rate '.$rate['RATE_TYPE']);

                                /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Rate add', 'POINT_GUID' => $point['DATA']['POINT_GUID'], 'RATE_DATA' => $rate]);
                            }
                        }
                    }
                    // break;
                }

                /* Log */ $this->toLog(['STATE' => 'Point loading end', 'DATE' => (new DateTime())->toString()]);
            }
            else
                $this->result->addError(new \Bitrix\Main\Error('No data content while getting points from API on page '.$pagenumber));

            $total = $data['TOTAL_PAGES'];
            $this->result->setData(['IS_LAST' => (($pagenumber < $total - 1) ? false : true), 'TOTAL_PAGES' => $total, 'NEXT_PAGE' => $pagenumber + 1]);

        }
        else
            $this->errors->add($pointsResult->getErrorCollection()->toArray());

        if (!$this->errors->isEmpty())
        {
            foreach ($this->errors as $err)
                $this->result->addError($err);
        }

        return $this->result;
    }

    /**
     * Toggle unused points inactive
     *
     * @param int $lastDate timestamp
     * @return \Bitrix\Main\Result
     */
    public function toggleInactivePoints($lastDate)
    {
        $syncLastDate = DateTime::createFromTimestamp($lastDate);

        $points = [];
        $existedPointsDB = PointsTable::getList(['select' => ['ID', 'SYNC_IS_UPDATABLE', 'SYNC_LAST_DATE'], 'filter' => ['<SYNC_LAST_DATE' => $syncLastDate]]);
        while ($tmp = $existedPointsDB->fetch())
        {
            $points[$tmp['ID']] = $tmp;
        }

        foreach ($points as $pointPrimaryId => $point)
        {
            if ($point['SYNC_IS_UPDATABLE'] !== 'Y')
            {
                /* Log */ $this->toLog(['STATE' => 'Not updatable point detected', 'EXISTED' => $point]);
                continue;
            }

            $result = PointsTable::update($pointPrimaryId, ['SYNC_IS_ACTIVE' => 'N', 'SYNC_LAST_DATE' => $syncLastDate]);
            $this->collectPossibleErrors($result, 'Point '.$pointPrimaryId);

            /* Log */ $this->logRefreshResult($result, false, ['STATE' => 'Point toggle inactive', 'POINT_ID' => $pointPrimaryId, 'POINT_DATA' => $point]);
        }

        if (!$this->errors->isEmpty())
        {
            foreach ($this->errors as $err)
                $this->result->addError($err);
        }

        $this->result->setData(['TOTAL_POINTS_FOUND' => count($points)]);

        return $this->result;
    }

    /**
     * Make statistic info about loaded points
     *
     * @return \Bitrix\Main\Result
     */
    public static function makeStatistic()
    {
        $result = new Result();
        $result->setData(['POINTS_LOADED' => PointsTable::getDataCount(true)]);

        return $result;
    }
}