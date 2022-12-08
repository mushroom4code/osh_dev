<?
namespace Ipol\Fivepost\Bitrix\Controller;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

use \Ipol\Fivepost\DeliveryHandler;
use \Ipol\Fivepost\Bitrix\Adapter;
use \Ipol\Fivepost\Core\Delivery\CargoItem;

use \Ipol\Fivepost\PointsTable;
use \Ipol\Fivepost\RatesTable;

/**
 * Class Referencer
 * Used to get data about points and another table-based entities
 *
 * @package namespace Ipol\Fivepost\Bitrix\Controller
 */
class Referencer extends AbstractController
{
    public function __construct()
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);
    }

    /**
     * Get points data from points table
     *
     * @param CargoItem|false $cargo order object as one deliverable item
     * @param string|false $paySystem selected payment type @see DeliveryHandler::PAYSYSTEM_CASH and other constants
     * @param string|false $possiblyId bitrix location Id or Code
     * @param array $select array of selected fields, point guid and name returned by default
     * @return \Bitrix\Main\Result
     */
    public function getPoints($cargo = false, $paySystem = false, $possiblyId = false, $select = array())
    {
        $filter = [];
        $result = new Result();

        // Cause we need only active points
        $filter['SYNC_IS_ACTIVE'] = 'Y';

        // Cargo
        if ($cargo && $cargo->ready())
        {
            $filter['>=MAX_CELL_WEIGHT'] = $cargo->getWeight();
            $filter['>=MAX_CELL_DIMENSIONS_HASH'] = Adapter::makeDimensionsHash($cargo->getLength(), $cargo->getWidth(), $cargo->getHeight());
        }

        // Payment system
        if ($paySystem)
        {
            switch($paySystem)
            {
                case DeliveryHandler::PAYSYSTEM_CASH:
                    $filter['CASH_ALLOWED'] = 'Y';
                    break;
                case DeliveryHandler::PAYSYSTEM_CARD:
                    $filter['CARD_ALLOWED'] = 'Y';
                    break;
                case DeliveryHandler::PAYSYSTEM_LOYALTY:
                    $filter['LOYALTY_ALLOWED'] = 'Y';
                    break;
            }
        }

        // Location
        if ($possiblyId)
        {
            $location = Adapter::locationById($possiblyId);
            if ($location->ready())
            {
                $filter['=LOCALITY_FIAS_CODE'] = $location->getLocationLink()->getApi()->getCode();
            }
        }

        $cacheId = md5(serialize($filter));
        $cacheTime     = 10800;
        $cachePath     = '/'.IPOL_FIVEPOST.'/getCityPoints';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath))
        {
            $points = $cacheInstance->GetVars();
        }
        else
        {
            $pointsDB = PointsTable::getList([
                    'select' => array_merge($select, ['POINT_GUID', 'NAME']), // Minimum selected fields
                    'filter' => $filter,
                    'order' => ['ID' => 'ASC']]
            );

            $points = [];
            while ($tmp = $pointsDB->fetch())
            {
                // Deal with delivery periods
                if (array_key_exists('DELIVERY_SL', $tmp))
                {
                    $deliveryPeriods = unserialize($tmp['DELIVERY_SL']);
                    $tmp['DELIVERY_SL'] = $deliveryPeriods[0]['Sl']; // Can be changed later, only one period can get from API now
                }

                if (array_key_exists('WORK_HOURS', $tmp)) {
                    $tmp['WORK_HOURS'] = unserialize($tmp['WORK_HOURS']);
                }

                $points[$tmp['POINT_GUID']] = $tmp;
            }
            unset ($pointsDB);

            if (!empty($points) && $cacheInstance->startDataCache())
            {
                $cacheInstance->endDataCache($points);
            }
        }

        if (empty($points))
            $result->addError(new \Bitrix\Main\Error('Available points not found'));

        $result->setData([
            // Debug reasons
            'LOCATION_LINK' => $location,
            'SELECT' => $select,
            'FILTER' => $filter,
            // --
            'POINTS' => $points,
        ]);

        return $result;
    }

    /**
     * Get all existing rates from rates table
     *
     * @return \Bitrix\Main\Result
     */
    public function getExistingRates()
    {
        $result = new Result();

        $cacheId = md5(serialize([__CLASS__, __METHOD__]));
        $cacheTime = 3600;
        $cachePath = '/' . IPOL_FIVEPOST . '/getExistingRates';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath)) {
            $rates = $cacheInstance->GetVars();
        } else {
            $ratesDB = RatesTable::getList([
                'select' => ['RATE_TYPE'],
                'order' => ['RATE_TYPE' => 'ASC'],
                'group' => ['RATE_TYPE'],
            ]);

            $rates = [];
            while ($tmp = $ratesDB->fetch()) {
                $rates[(string)$tmp['RATE_TYPE']] = $tmp;
            }
            unset ($ratesDB);

            if (!empty($rates) && $cacheInstance->startDataCache()) {
                $cacheInstance->endDataCache($rates);
            }
        }

        if (empty($rates))
            $result->addError(new Error('Available rates not found'));

        $result->setData(['RATES' => $rates]);

        return $result;

    }
}