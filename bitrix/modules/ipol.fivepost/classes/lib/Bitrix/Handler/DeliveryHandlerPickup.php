<?
namespace Ipol\Fivepost\Bitrix\Handler;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Error;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Sale\Shipment;
use \Bitrix\Sale\Delivery\CalculationResult;
use \Bitrix\Sale\Delivery\Services\Manager;

use \Ipol\Fivepost\DeliveryHandler;
use \Ipol\Fivepost\PointsHandler;
use \Ipol\Fivepost\ProfileHandler;
use \Ipol\Fivepost\Bitrix\Tools;
use \Ipol\Fivepost\Bitrix\Controller\Calculator;
use \Ipol\Fivepost\Bitrix\Entity\Profiles;

Loc::loadMessages(__FILE__);

/**
 * Class DeliveryHandlerPickup
 * @package namespace Ipol\Fivepost\Bitrix\Handler
 */
class DeliveryHandlerPickup extends \Bitrix\Sale\Delivery\Services\Base
{
    /**
     * System rate type for default variant 'select one with lowest price'
     */
    const RATE_TYPE_MIN_PRICE = 'RT_MIN_PRICE';

    // Extra charge types
    const EXTRA_CHARGE_TYPE_PERCENT = '%';
    const EXTRA_CHARGE_TYPE_BASKET  = 'B';
    const EXTRA_CHARGE_TYPE_FIXED   = 'F';

    /**
     * This is profile
     * @var bool
     */
    protected static $isProfile = true;

    /**
     * Parent delivery object
     */
    protected $parent = null;

    /**
     * Profile code like old automatic delivery services
     */
    protected static $profileCode = 'pickup';

    /**
     * @var bool|\Ipol\Fivepost\Bitrix\Controller\Calculator
     */
    protected $calculator = false;

    /**
     * VAT rate configured in Bitrix for this profile
     */
    protected $configuredVatRate = 0;

    /**
     * Selected point guid used for widget calculations
     */
    protected $selectedPointGuid = false;

    /*
     * Rate type used in delivery calculation
     */
    protected $rateType = self::RATE_TYPE_MIN_PRICE;

    /**
     * @param array $initParams
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->parent = Manager::getObjectById($this->parentId);

        $this->configuredVatRate = self::getVatRateFromConfig($initParams['VAT_ID']);

        if (isset($this->config['MAIN']['RATE_TYPE']) && !empty($this->config['MAIN']['RATE_TYPE']))
            $this->rateType = $this->config['MAIN']['RATE_TYPE'];
    }

    /**
     * @return string Class title
     */
    public static function getClassTitle()
    {
        return ProfileHandler::getProfileName(self::getProfileCode());
    }

    /**
     * @return string Class, service description
     */
    public static function getClassDescription()
    {
        return ProfileHandler::getProfileDescription(self::getProfileCode());
    }

    /**
     * @return parent delivery object
     */
    public function getParentService()
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public static function isProfile()
    {
        return self::$isProfile;
    }

    /**
     * @return string
     */
    public static function getProfileCode()
    {
        return self::$profileCode;
    }

    /**
     * @return bool|Calculator
     */
    public function getCalculator()
    {
        return $this->calculator;
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately()
    {
        return $this->getParentService()->isCalculatePriceImmediately();
    }

    /**
     * @return array
     */
    protected function getConfigStructure()
    {
        $result = array(
            "MAIN" => array(
                "TITLE" => Tools::getMessage('DELIVERY_HANDLER_PICKUP_MAIN_TAB_TITLE'),
                "DESCRIPTION" => Tools::getMessage('DELIVERY_HANDLER_PICKUP_MAIN_TAB_DESCR'),
                "ITEMS" => array(
                    "DS_RATE" => array(
                        "TYPE" => "DELIVERY_SECTION",
                        "NAME" => Tools::getMessage('DELIVERY_HANDLER_PICKUP_MAIN_TAB_DS_RATE'),
                    ),
                    "RATE_TYPE" => array(
                        //"TYPE" => "DELIVERY_READ_ONLY",
                        "TYPE" => "ENUM",
                        "NAME" => Tools::getMessage('DELIVERY_HANDLER_PICKUP_MAIN_TAB_RATE_TYPE'),
                        "DEFAULT" => self::RATE_TYPE_MIN_PRICE,
                        "OPTIONS" => self::getAvailableRateTypes(),
                    ),
                    "DS_EXTRA_CHARGE_ROUND" => array(
                        "TYPE" => "DELIVERY_SECTION",
                        "NAME" => Tools::getMessage('DELIVERY_HANDLER_PICKUP_MAIN_TAB_DS_EXTRA_CHARGE_ROUND'),
                    ),
                    "EXTRA_CHARGE_TYPE" => array(
                        "TYPE" => "ENUM",
                        "NAME" => Tools::getMessage("DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE"),
                        "DEFAULT" => self::EXTRA_CHARGE_TYPE_PERCENT,
                        "OPTIONS" => self::getExtraChargeTypeVariants(),
                    ),
                    "EXTRA_CHARGE_VALUE" => array(
                        "TYPE" => "STRING",
                        "NAME" => Tools::getMessage("DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_VALUE"),
                        "DEFAULT" => 0,
                    ),
                    "ROUND_TO" => array(
                        "TYPE" => "STRING",
                        "NAME" => Tools::getMessage("DELIVERY_HANDLER_PROFILE_MAIN_TAB_ROUND_TO"),
                        "DEFAULT" => 0,
                    ),
                )
            )
        );

        return $result;
    }

    /**
     * Get available rate types including default variant
     *
     * @return array
     */
    public static function getAvailableRateTypes()
    {
        // System rate type for default variant 'select one with lowest price'
        $rateTypes = [self::RATE_TYPE_MIN_PRICE => Tools::getMessage('DELIVERY_HANDLER_PICKUP_MAIN_TAB_RATE_TYPE_MIN_PRICE')];

        $result = PointsHandler::getExistingRates();
        if ($result->isSuccess()) {
            $data = $result->getData();
            foreach ($data['RATES'] as $rateType => $val)
                $rateTypes[$rateType] = $rateType;
        }

        return $rateTypes;
    }

    /**
     * Get extra charge type variants
     *
     * @return array
     */
    public static function getExtraChargeTypeVariants()
    {
        return [
            self::EXTRA_CHARGE_TYPE_PERCENT => Tools::getMessage('DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_PERCENT'),
            self::EXTRA_CHARGE_TYPE_BASKET  => Tools::getMessage('DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_BASKET'),
            self::EXTRA_CHARGE_TYPE_FIXED   => Tools::getMessage('DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_FIXED'),
        ];
    }

    /**
     * Try to get VAT rate by VAT id
     *
     * @return float
     */
    public static function getVatRateFromConfig($vatId = 0)
    {
        $vatRate = 0;

        if (($vatId > 0) && Loader::includeModule('catalog'))
        {
            $possibleVat = \Bitrix\Catalog\VatTable::getList(['filter' => ['ID' => $vatId], 'select' => ['ID', 'NAME', 'RATE']])->fetch();
            if (is_array($possibleVat))
            {
                $vatRate = $possibleVat['RATE'];
            }
        }
        return $vatRate;
    }

    /**
     * Getter for configured Vat rate
     *
     * @return float
     */
    public function getConfiguredVatRate()
    {
        return $this->configuredVatRate;
    }

    /**
     * Get selected points guid
     *
     * @return string
     */
    public function getSelectedPointGuid()
    {
        return $this->selectedPointGuid;
    }

    /**
     * Set selected points guid
     *
     * @param string $guid Fivepost point guid
     */
    public function setSelectedPointGuid($guid)
    {
        $this->selectedPointGuid = $guid;
    }

    /**
     * Get configured rate type
     *
     * @return string
     */
    public function getRateType()
    {
        return $this->rateType;
    }

    /**
     * Compatibility check
     *
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return bool
     */
    public function isCompatible(Shipment $shipment)
    {
        /*
        // PaymentCollection empty on D2P SOA, but seems useful on P2D
        //
        $order = $shipment->getCollection()->getOrder();
        $payments = $order->getPaymentCollection();
        */

        $result = $this->checkRequiredData($shipment);
        if (!$result->isSuccess())
            return false;

        try
        {
            $this->calculator = new Calculator(array($shipment));

            $this->calculator->setTariffDependency(ProfileHandler::profilesToCalculator())
                ->setPayment(DeliveryHandler::definePaysystem())
                ->setProfile(self::getProfileCode())
                ->setVatRate($this->getConfiguredVatRate())
                // ->setSelectedPointGuid($this->getSelectedPointGuid()) // Do we need to set point guid there?
                ->calculate();

            if (!$this->calculator->getProfiles())
                throw new \Exception('No available delivery profiles found for this shipment');

            // Check compatibility against desired tariff if it's set in handler options
            $desiredTariff = false;
            if ($this->getRateType() !== self::RATE_TYPE_MIN_PRICE)
                $desiredTariff = $this->getRateType();

            $arProfiles = $this->calculator->getProfiles()->getCompability($desiredTariff);

            if (!in_array(self::getProfileCode(), $arProfiles))
                throw new \Exception('Profile ' . self::getProfileCode() . ' not available for this shipment');

            // onCompabilityBefore module event
            $event = new Event(IPOL_FIVEPOST, "onCompabilityBefore", array(
                'DELIVERY_ID' => $this->getId(),
                'SHIPMENT' => $shipment,
                'PROFILES' => $arProfiles
            ));
            $event->send();

            $results = $event->getResults();
            if (is_array($results) && !empty($results))
            {
                foreach ($results as $eventResult)
                {
                    if ($eventResult->getType() != EventResult::SUCCESS)
                        continue;

                    $params = $eventResult->getParameters();
                    if (isset($params["PROFILES"]))
                        $arProfiles = array_unique($params["PROFILES"]);
                }
            }
            // --
        }
        catch (\Exception $e)
        {
            $result->addError(new Error($e->getMessage(), 'DELIVERY_CALCULATION'));
            return false;
        }

        return (is_array($arProfiles) && !empty($arProfiles));
    }

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     */
    protected function calculateConcrete(Shipment $shipment = null)
    {
        $result = new CalculationResult;

        $check = $this->checkRequiredData($shipment);
        if (!$check->isSuccess())
            return $check;
        else
        {
            // Case: make order copy in admin interface, calculateConcrete called before isCompatible and no shipped items given at first calls
            // Ask Bitrix about this shit logic
            if (!$this->getParentService()::checkShipmentItems($shipment))
            {
                // No shipped items = zero values returned
                $result->setDeliveryPrice(0);
                $result->setPeriodDescription('');
                $result->setPeriodFrom(0);
                $result->setPeriodTo(0);
                return $result;
            }
        }

        if (empty($this->calculator))
        {
            $this->calculator = new Calculator(array($shipment));
            try
            {
                $this->calculator->setTariffDependency(ProfileHandler::profilesToCalculator())
                    ->setPayment(DeliveryHandler::definePaysystem())
                    ->setProfile(self::getProfileCode())
                    ->setVatRate($this->getConfiguredVatRate())
                    ->setSelectedPointGuid($this->getSelectedPointGuid())
                    ->calculate();
            }
            catch (\Exception $e)
            {
                $result->addError(new Error($e->getMessage(), 'DELIVERY_CALCULATION'));
                return $result;
            }
        }

        try
        {
            /*
            // Some order tariff magic here

            // TODO put in controller
            $service = serviceWidjetController::defineProfileTarif(self::getProfileCode(), $this->calculator->getShipments()->getShipmentsTarifs());
            // editing order - need to get is't tarif

            if(!$service && Tools::isAdminSection()){
                $orderId = false;
                if(is_array($_REQUEST)){
                    switch (true){
                        case (array_key_exists('ORDER_ID',$_REQUEST) && $_REQUEST['ORDER_ID']) : $orderId = $_REQUEST['ORDER_ID']; break;
                        case (array_key_exists('ID',$_REQUEST) && $_REQUEST['ID'])             : $orderId = $_REQUEST['ID']; break;
                        case (array_key_exists('formData',$_REQUEST) && array_key_exists('ID',$_REQUEST['formData']) && $_REQUEST['formData']['ID']) :
                            $orderId = $_REQUEST['formData']['ID']; break;
                    }
                }

                if($orderId){
                    $service = orderHandler::getOrderTarif($orderId);
                }
            }
            */

            // Choose desired tariff if it's set in handler options
            $desiredTariff = false;
            if ($this->getRateType() !== self::RATE_TYPE_MIN_PRICE)
                $desiredTariff = $this->getRateType();

            $arResult = $this->calculator->getProfiles()->getCalculate(self::getProfileCode(), $desiredTariff, Profiles::TARIFF_ID_SUBSTRING);

            if ($arResult['RESULT'] == 'OK')
            {
                $itemsPrice = ($collection = $shipment->getShipmentItemCollection()) ? $collection->getPrice() : 0;

                // Add extra charge
                $arResult['VALUE'] += $this->getExtraCharge($arResult['VALUE'], $itemsPrice);

                // Do round
                $arResult['VALUE'] = $this->roundPrice($arResult['VALUE']);
            }

            // onCalculate module event
            $event = new Event(IPOL_FIVEPOST, "onCalculate", array(
                'DELIVERY_ID' => $this->getId(),
                'SHIPMENT'    => $shipment,
                'PROFILE'     => self::getProfileCode(),
                'RESULT'      => $arResult
            ));
            $event->send();

            $results = $event->getResults();
            if (is_array($results) && !empty($results))
            {
                foreach ($results as $eventResult)
                {
                    if ($eventResult->getType() != EventResult::SUCCESS)
                        continue;

                    $params = $eventResult->getParameters();
                    if (isset($params["RESULT"]))
                        $arResult = $params["RESULT"];
                }
            }
            // --

            if ($arResult['RESULT'] == 'ERROR')
            {
                throw new \Exception('No available delivery profiles found for this shipment');
            }
            else
            {
                $result->setDeliveryPrice($arResult['VALUE']); // float
                $result->setPeriodDescription($arResult['TRANSIT']); // string
                $result->setPeriodFrom((int)$arResult['periodFrom']); // int
                $result->setPeriodTo((int)$arResult['periodTo']); // int
                $result->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);
            }
        }
        catch (\Exception $e)
        {
            $result->addError(new Error($e->getMessage(), 'DELIVERY_CALCULATION'));
            return $result;
        }

        //\Bitrix\Main\Diag\Debug::WriteToFile([$result], 'calculateConcrete pickup', '__fp_Delivery.log');

        return $result;
    }

    /**
     * Check minimum required data used for delivery calculation, also checks module auth
     *
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     */
    protected function checkRequiredData(Shipment $shipment)
    {
        $result = new CalculationResult;

        if (!(\Ipol\Fivepost\authHandler::isAuthorized()))
        {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_AUTH'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        $order = $shipment->getCollection()->getOrder();

        if (!$props = $order->getPropertyCollection())
        {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_PROPS'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        if (!$locationProp = $props->getDeliveryLocation())
        {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_LOCATION_PROP'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        if (!$locationCode = $locationProp->getValue())
        {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_LOCATION_CODE'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        return $result;
    }

    /**
     * Get delivery extra charge based on profile config
     *
     * @param float $deliveryPrice
     * @param float $itemsPrice
     * @return float
     */
    protected function getExtraCharge($deliveryPrice, $itemsPrice)
    {
        $extraChargeType = (is_array($this->config["MAIN"]) && array_key_exists('EXTRA_CHARGE_TYPE', $this->config["MAIN"])) ?
            $this->config["MAIN"]["EXTRA_CHARGE_TYPE"] : self::EXTRA_CHARGE_TYPE_PERCENT;

        $extraChargeValue = (is_array($this->config["MAIN"]) && array_key_exists('EXTRA_CHARGE_VALUE', $this->config["MAIN"])) ?
            (float)$this->config["MAIN"]["EXTRA_CHARGE_VALUE"] : 0;

        switch ($extraChargeType)
        {
            case self::EXTRA_CHARGE_TYPE_PERCENT:
                $result = $deliveryPrice * $extraChargeValue / 100;
                break;
            case self::EXTRA_CHARGE_TYPE_BASKET:
                $result = $itemsPrice * $extraChargeValue / 100;
                break;
            case self::EXTRA_CHARGE_TYPE_FIXED:
                $result = $extraChargeValue;
                break;
        }

        return $result;
    }

    /**
     * Do round da given price based on profile config
     *
     * @param float $price
     * @return float
     */
    protected function roundPrice($price)
    {
        $roundTo = (is_array($this->config["MAIN"]) && array_key_exists('ROUND_TO', $this->config["MAIN"])) ?
            (int)$this->config["MAIN"]["ROUND_TO"] : 0;

        return (($roundTo > 0) ? ceil($price / $roundTo) * $roundTo : $price);
    }
}
