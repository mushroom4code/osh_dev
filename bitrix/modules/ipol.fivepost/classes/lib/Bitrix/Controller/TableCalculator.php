<?
namespace Ipol\Fivepost\Bitrix\Controller;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

use \Ipol\Fivepost\PointsTable;
use \Ipol\Fivepost\RatesTable;

use \Ipol\Fivepost\Bitrix\Adapter;
use \Ipol\Fivepost\Core\Delivery\Shipment;
use \Ipol\Fivepost\Core\Delivery\Tariff;
use \Ipol\Fivepost\Core\Delivery\Tools;
use \Ipol\Fivepost\Core\Entity\Money;

/**
 * Class TableCalculator
 * Used for table delivery calculation instead of API call
 *
 * @package namespace Ipol\Fivepost\Bitrix\Controller
 */
class TableCalculator extends AbstractController
{
    /**
     * @var \Ipol\Fivepost\Core\Delivery\Shipment
     */
    protected $shipment = null;

    /**
     * Payment type
     * @var bool|string
     */
    protected $paymentType = false;

    /**
     * VAT rate
     * @var float
     */
    protected $vatRate = 0;

    public function __construct()
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);
    }

    /**
     * Check if VAT rates are equal
     *
     * @param float $vatA
     * @param float $vatB
     * @return bool
     */
    public static function checkVatEquality($vatA, $vatB)
    {
        return ((int)$vatA === (int)$vatB);
    }

    /**
     * Calculate delivery price using tables
     * If succeeded available tariffs will be added to shipment data
     *
     * @return $result \Bitrix\Main\Result
     */
    public function calculate()
    {
        $result = new Result();

        if ($this->getShipment())
        {
            $pointFilter = $this->preparePointFilter();
            $points = $this->getPossiblePoints($pointFilter);

            if (!empty($points))
            {
                $ratesFilter = $this->prepareRatesFilter(array_keys($points));
                $rates = $this->getPossibleRates($ratesFilter);

                if (!empty($rates))
                {
                    $availableRatesCount = 0;
                    foreach ($rates as $zoneId => $zone)
                    {
                        foreach ($zone as $rateTypeId => $rateType)
                        {
                            // Checks if rate values are useful. Otherwise client must ask 5Post manager about delivery rates
                            if (!($rateType['RATE_VALUE_WITH_VAT'] > 0 && $rateType['RATE_EXTRA_VALUE_WITH_VAT'] > 0))
                            {
                                $rates[$zoneId][$rateTypeId]['RATE_VALUES_NOT_LOADED'] = 'Y';
                                continue;
                            }
                            $availableRatesCount++;

                            // Add delivery period from / to use points data
                            $periodMin = PHP_INT_MAX;
                            $periodMax = PHP_INT_MIN;

                            foreach ($rateType['POINT_IDS'] as $pointId)
                            {
                                $period = (int)$points[$pointId]['PERIOD'];

                                if ($period < $periodMin)
                                    $periodMin = $period;

                                if ($period > $periodMax)
                                    $periodMax = $period;
                            }

                            // Additional check for bad period data
                            $rates[$zoneId][$rateTypeId]['DELIVERY_FROM'] = ($periodMin < PHP_INT_MAX) ? $periodMin : 0;
                            $rates[$zoneId][$rateTypeId]['DELIVERY_TO']   = ($periodMax > PHP_INT_MIN) ? $periodMax : 0;

                            // Always set delivery price with VAT, no matter which VAT rate configured in delivery handler profile
                            // It's official 5Post answer
                            $rates[$zoneId][$rateTypeId]['DELIVERY_PRICE'] = $this->calculateByWeight($rateType['RATE_VALUE_WITH_VAT'], $rateType['RATE_EXTRA_VALUE_WITH_VAT']);

                            /*
                            // Disabled VAT-based rate math

                            // Set delivery price without using VAT rate by default
                            $rates[$zoneId][$rateTypeId]['DELIVERY_PRICE'] = $this->calculateByWeight($rateType['RATE_VALUE'], $rateType['RATE_EXTRA_VALUE']);

                            // Deal with VAT rate if given
                            if ($this->getVatRate())
                            {
                                // Checks we have rate values with VAT loaded
                                if ($rateType['VAT'] > 0 && ($rateType['RATE_VALUE_WITH_VAT'] > 0 && $rateType['RATE_EXTRA_VALUE_WITH_VAT'] > 0))
                                {
                                    // Checks VAT rate in Bitrix profile handler and rate table are the same
                                    if (self::checkVatEquality($this->getVatRate(), $rateType['VAT']))
                                    {
                                        $rates[$zoneId][$rateTypeId]['DELIVERY_PRICE'] = $this->calculateByWeight($rateType['RATE_VALUE_WITH_VAT'], $rateType['RATE_EXTRA_VALUE_WITH_VAT']);
                                    }
                                }
                            }
                            */
                        }
                    }
                    // Now all rate data are belong to us

                    // Cause we may have some rates but with no rate values (all fields are zero) and can't determine delivery price
                    if ($availableRatesCount)
                    {
                        // Deal with Tariffs
                        foreach ($rates as $zoneId => $zone)
                        {
                            foreach ($zone as $rateTypeId => $rateType)
                            {
                                if ($rateType['RATE_VALUES_NOT_LOADED'] === 'Y')
                                    continue;

                                // Use zone + rate type as tariff Id
                                $tariffID = $zoneId.'-'.$rateTypeId;

                                $details = new Result();
                                $details->setData(array(
                                    'RATE_CURRENCY'           => $rateType['RATE_CURRENCY'],
                                    'VAT'                     => $rateType['VAT'],
                                    'DELIVERY_FROM'           => $rateType['DELIVERY_FROM'],
                                    'DELIVERY_TO'             => $rateType['DELIVERY_TO'],

                                    // 'POINT_IDS'               => $rateType['POINT_IDS'], // Debug reasons
                                ));

                                $tariff = new Tariff($tariffID);

                                $money = new Money($rateType['DELIVERY_PRICE']);

                                $tariff->setPrice($money)
                                    ->setVariant('pickup')
                                    ->setTerm(Tools::getTerm($rateType['DELIVERY_FROM'], $rateType['DELIVERY_TO']))
                                    ->setDetails($details);

                                $this->getShipment()->getSummary()->add($tariff);
                            }
                        }

                        $result->setData(['AVAILABLE_RATES_COUNT' => $availableRatesCount]);
                    }
                    else
                    {
                        $result->addError(new Error('No loaded rates, all rate values are zero. Contact your 5Post manager about creating rate data for you'));
                    }

                    // Log -------------------
                    /*
                    \Bitrix\Main\Diag\Debug::WriteToFile([
                        //'PF' => $pointFilter,
                        //'P' => $points,
                        //'RF' => $ratesFilter,
                        'R' => $rates
                        ], 'TableCalculator calculate', '__fp_Delivery.log');
                    */
                    // -----------------------
                }
                else
                {
                    $result->addError(new Error('No possible rates for this shipment'));
                }
            }
            else
            {
                $result->addError(new Error('No available points for this shipment'));
            }

            if (!$result->isSuccess())
            {
                $this->getShipment()->setError(true)->setErrorText(implode(', ', $result->getErrorMessages()));
            }
        }
        else
        {
            $result->addError(new Error('No shipment given for delivery calculation'));
        }

        return $result;
    }

    /**
     * Calculate delivery price based on shipment weight
     *
     * @param float $rateValue base rate value
     * @param float $rateExtraValue overweight rate value
     * @return float
     */
    protected function calculateByWeight($rateValue, $rateExtraValue)
    {
        $totalWeight = $this->getShipment()->getCargoes()->getTotalWeight();
        $totalWeight = ceil($totalWeight / 1000); // To kg

        // From module options
        $base  = $this->getOptions()->fetchBasicTarif();
        $extra = $this->getOptions()->fetchOverweight();

        $extraWeight = $totalWeight - $base;
        if ($extraWeight > 0)
        {
            $deliveryPrice = $rateValue + ceil($extraWeight / $extra) * $rateExtraValue;
        }
        else
        {
            $deliveryPrice = $rateValue;
        }

        return $deliveryPrice;
    }

    /**
     * Prepare filter for points search using Shipment data
     *
     * @return array filter for ORM table
     */
    protected function preparePointFilter()
    {
        $filter = array();

        $filter['SYNC_IS_ACTIVE']      = 'Y';
        $filter['=LOCALITY_FIAS_CODE'] = $this->getShipment()->getTo()->getCode();

        // Add possible pickup point guid from shipment details for pickup tariff
        if ($this->getShipment()->getTariff() === 'pickup')
        {
            $details = $this->getShipment()->getDetails();
            if (is_array($details) && array_key_exists('pickupPointGuid', $details) && $details['pickupPointGuid'])
                $filter['=POINT_GUID'] = $details['pickupPointGuid'];
        }

        // Deal with payment types
        switch($this->getPaymentType())
        {
            case 'CASH':
                $filter['CASH_ALLOWED'] = 'Y';
                break;
            case 'CARD':
                $filter['CARD_ALLOWED'] = 'Y';
                break;
            case 'LOYALTY':
                $filter['LOYALTY_ALLOWED'] = 'Y';
                break;
            default: // Skip filter by payment type allowed if no paymentType given
                break;
        }

        // Deal with Cargos
        $totalWeight = $this->getShipment()->getCargoes()->getTotalWeight();
        $cargoDimensions = $this->getShipment()->getCargoes()->getTotalDimensions();

        $filter['>=MAX_CELL_WEIGHT'] = $totalWeight;
        $filter['>=MAX_CELL_DIMENSIONS_HASH'] = Adapter::makeDimensionsHash($cargoDimensions['L'], $cargoDimensions['W'], $cargoDimensions['H']);

        return $filter;
    }

    /**
     * Prepare filter for rates search
     *
     * @param array $pointIds list of points
     * @return array filter for ORM table
     */
    protected function prepareRatesFilter($pointIds)
    {
        $filter = array();

        $filter['=POINT_ID'] = $pointIds;

        return $filter;
    }

    /**
     * Get possible points for current filter
     *
     * @param array $filter @see Ipol\Fivepost\Bitrix\Controller\TableCalculator::preparePointFilter()
     * @return array
     */
    protected function getPossiblePoints($filter)
    {
        static $cache = [];

        $cacheId = md5(serialize($filter));

        if (isset($cache[$cacheId]))
            return $cache[$cacheId];

        $cacheTime     = 86400;
        $cachePath     = '/'.IPOL_FIVEPOST.'/getPossiblePoints';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath))
        {
            $points = $cacheInstance->GetVars();
        }
        else
        {
            $pointsDB = PointsTable::getList([
                'select' => ['ID', 'POINT_GUID', /*'MAX_CELL_WEIGHT', 'MAX_CELL_DIMENSIONS_HASH', 'CASH_ALLOWED', 'CARD_ALLOWED', 'LOYALTY_ALLOWED',*/ 'DELIVERY_SL'],
                'filter' => $filter,
                'order'  => ['ID' => 'ASC'],
            ]);

            $points = [];
            while ($tmp = $pointsDB->fetch())
            {
                // Deal with delivery periods
                $deliveryPeriods = unserialize($tmp['DELIVERY_SL']);

                $points[$tmp['ID']] = array(
                    'POINT_GUID' => $tmp['POINT_GUID'],
                    'PERIOD' => $deliveryPeriods[0]['Sl'], // Can be changed later, only one period can get from API now
                );
            }
            unset ($pointsDB);

            if (!empty($points) && $cacheInstance->startDataCache())
            {
                $cacheInstance->endDataCache($points);
            }
        }

        return $cache[$cacheId] = $points;
    }

    /**
     * Get possible rates for current filter
     *
     * @param array $filter @see Ipol\Fivepost\Bitrix\Controller\TableCalculator::prepareRatesFilter()
     * @return array
     */
    protected function getPossibleRates($filter)
    {
        static $cache = [];

        $cacheId = md5(serialize($filter));

        if (isset($cache[$cacheId]))
            return $cache[$cacheId];

        $cacheTime     = 86400;
        $cachePath     = '/'.IPOL_FIVEPOST.'/getPossibleRates';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath))
        {
            $rates = $cacheInstance->GetVars();
        }
        else
        {
            $ratesDB = RatesTable::getList([
                'select' => ['ID', 'POINT_ID', 'ZONE', 'RATE_TYPE', 'RATE_CURRENCY', /*'RATE_VALUE', 'RATE_EXTRA_VALUE',*/ 'VAT', 'RATE_VALUE_WITH_VAT', 'RATE_EXTRA_VALUE_WITH_VAT'],
                'filter' => $filter,
                //'order'  => ['ID' => 'ASC'],
                'order'  => ['RATE_VALUE_WITH_VAT' => 'DESC'],
            ]);

            $rates = [];
            while ($tmp = $ratesDB->fetch())
            {
                //$rates[$tmp['ZONE']][$tmp['RATE_TYPE']][$tmp['RATE_VALUE']]['RATE_CURRENCY']    = $tmp['RATE_CURRENCY'];
                //$rates[$tmp['ZONE']][$tmp['RATE_TYPE']][$tmp['RATE_VALUE']]['RATE_EXTRA_VALUE'] = $tmp['RATE_EXTRA_VALUE'];
                //$rates[$tmp['ZONE']][$tmp['RATE_TYPE']][$tmp['RATE_VALUE']]['POINT_IDS_TO_DELIVERY_TERM'][$tmp['POINT_ID']] = '';

                // Assume that within one Zone for one Rate Type all points have equal Rate values and other params
                //$rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['RATE_VALUE']                = $tmp['RATE_VALUE'];
                //$rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['RATE_EXTRA_VALUE']          = $tmp['RATE_EXTRA_VALUE'];
                $rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['VAT']                       = $tmp['VAT'];
                $rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['RATE_VALUE_WITH_VAT']       = $tmp['RATE_VALUE_WITH_VAT'];
                $rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['RATE_EXTRA_VALUE_WITH_VAT'] = $tmp['RATE_EXTRA_VALUE_WITH_VAT'];
                $rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['RATE_CURRENCY']             = $tmp['RATE_CURRENCY'];

                $rates[$tmp['ZONE']][$tmp['RATE_TYPE']]['POINT_IDS'][]               = $tmp['POINT_ID'];
            }
            unset ($ratesDB);

            if (!empty($rates) && $cacheInstance->startDataCache())
            {
                $cacheInstance->endDataCache($rates);
            }
        }

        return $cache[$cacheId] = $rates;
    }

    /**
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @param Shipment $shipment
     * @return $this
     */
    public function setShipment(&$shipment)
    {
        $this->shipment = $shipment;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param bool|string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @param float $vatRate
     * @return $this
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;

        return $this;
    }
}