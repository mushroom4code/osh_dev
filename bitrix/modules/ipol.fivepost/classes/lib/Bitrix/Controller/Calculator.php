<?
namespace Ipol\Fivepost\Bitrix\Controller;

use \Bitrix\Main\Localization\Loc;
//use \Bitrix\Main\Error;
use \Bitrix\Sale\Shipment;

use \Ipol\Fivepost\Bitrix\Tools;
use \Ipol\Fivepost\Bitrix\Adapter;

use \Ipol\Fivepost\Core\Delivery\Tariff;
use \Ipol\Fivepost\Core\Delivery\Shipment           as CoreShipment;
use \Ipol\Fivepost\Core\Delivery\ShipmentCollection as CoreShipmentCollection;
use \Ipol\Fivepost\Bitrix\Adapter\Cargo             as BitrixCargo;
use \Ipol\Fivepost\Bitrix\Entity\DefaultGabarites;
use \Ipol\Fivepost\Bitrix\Entity\Profile;
use \Ipol\Fivepost\Bitrix\Entity\Profiles;
use \Ipol\Fivepost\Bitrix\Handler\GoodsPicker;

use \Ipol\Fivepost\Bitrix\Controller\TableCalculator;
use \Ipol\Fivepost\PvzWidgetHandler;

// Used for table delivery calculation instead of API call

Loc::loadMessages(__FILE__);

/**
 * Class Calculator
 * @package namespace Ipol\Fivepost\Bitrix\Controller
 */
class Calculator extends AbstractController
{
    /**
     * @var array of \Bitrix\Sale\Shipment
     */
    protected $bxShipments;

    /**
     * @var \Ipol\Fivepost\Core\Delivery\ShipmentCollection
     */
    protected $shipments;

    /**
     * @var string - profile in Bitrix
     */
    protected $profile;

    /**
     * @var \Ipol\Fivepost\Bitrix\Entity\Profiles
     *
     */
    protected $profiles;

    /**
     * @var bool|array
     * Bitrix tariff => delivery variant
     */
    protected $tariffDependency = false;

    /**
     * Payment variant
     * @var bool
     */
    protected $payment = false;

    /**
     * VAT rate
     * @var float
     */
    protected $vatRate = 0;

    /**
     * Selected point guid used for widget direct delivery calculation
     * @var string
     */
    protected $selectedPointGuid = false;

    /**
     * @param array $bxShipments of \Bitrix\Sale\Shipment
     */
    public function __construct($bxShipments = false)
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);

        if (is_array($bxShipments))
        {
            $this->bxShipments = $bxShipments;
            $this->makeShipments();
        }
    }

    /**
     * Generates Core Shipments from given Bitrix Orders
     */
    protected function makeShipments()
    {
        $this->shipments = new CoreShipmentCollection();

        /** @var Shipment $bxShipment */
        foreach ($this->bxShipments as $bxShipment)
        {
            $shipment = new CoreShipment();
            $items    = GoodsPicker::fromShipmentObject($bxShipment);

            $obCargo = $this->getCargo($items);
            $shipment->addCargo($obCargo->getCargo());

            // Location code check are in DeliveryHandlerPickup::calculateConcrete(), if we are there, some location code entered
            /** @var $order \Bitrix\Sale\Order */
            $order = $bxShipment->getCollection()->getOrder();
            $props = $order->getPropertyCollection();
            $deliveryLocationProp = $props->getDeliveryLocation();
            $deliveryLocation     = $deliveryLocationProp->getValue();

            // ZIP code unused now, but can be useful later
            $deliveryZipProp = $props->getDeliveryLocationZip();
            $deliveryZip     = isset($deliveryZipProp) ? $deliveryZipProp->getValue() : false;

            /*
            // Can we change it for something else, like module options ?
            $senderLocationId = \CSaleHelper::getShopLocationId($order->getSiteId());

            $locationFrom = Adapter::locationById($senderLocationId);
            */

            $locationTo   = Adapter::locationById($deliveryLocation);
            if ($locationTo->ready() && $deliveryZip)
            {
                $locationTo->getLocationLink()->getCms()->setZip($deliveryZip);
            }

            /*
            if ($locationFrom->ready())
                $shipment->setFrom($locationFrom->getLocationLink()->getApi());
            else
                $shipment->setError(true)->setErrorText('Undefined location from');
            */

            if ($locationTo->ready())
                $shipment->setTo($locationTo->getLocationLink()->getApi());
            else
                $shipment->setError(true)->setErrorText('Undefined location to');

            // Set selected point guid if exists cause we need to calculate delivery to this point
            if (array_key_exists('order', $_REQUEST) && ($pointGuid = Tools::getArrVal(PvzWidgetHandler::getSavingLink(), $_REQUEST['order']))) {
                $point = \Ipol\Fivepost\PointsTable::getByPointGuid($pointGuid, ['POINT_GUID', 'LOCALITY_FIAS_CODE']);
                if (is_array($point) && $point['LOCALITY_FIAS_CODE'])
                {
                    $possibleLoc = \Ipol\Fivepost\LocationsTable::getList(['select' => ['LOCALITY_FIAS_CODE', 'BITRIX_CODE'], 'filter' => ['=LOCALITY_FIAS_CODE' => $point['LOCALITY_FIAS_CODE']]])->fetch();
                    if (is_array($possibleLoc) && $possibleLoc['BITRIX_CODE'] &&
                        $possibleLoc['BITRIX_CODE'] == $locationTo->getLocationLink()->getCms()->getCode()) {
                        // Finally, we can add guid
                        $shipment->setDetails(array('pickupPointGuid' => $pointGuid));
                    }
                }
            }

            $this->shipments->addShipment($shipment);
        }
    }

    /**
     * @param $arItems
     * @return BitrixCargo
     */
    public function getCargo($arItems)
    {
        $obCargo = new BitrixCargo(new DefaultGabarites());
        $obCargo->set($arItems);
        return $obCargo;
    }

    /**
     * @return profiles
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * @return \Ipol\Fivepost\Core\Delivery\ShipmentCollection
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * Calculate delivery for all shipments using required profile. Create array of profiles
     * @return $this
     */
    public function calculate()
    {
        $this->shipments->reset();
        while ($shipment = $this->shipments->getNext())
        {
            // Drop tariff data
            $shipment->setTariff($this->getProfile())->resetSummary();

            // TODO: deal something with this errors
            if ($shipment->getError())
                continue;

            $this->calculateShipment($shipment);
        }

        if (empty($this->profiles))
            $this->profiles = new Profiles();
        // Place for params punishment

        $profile = new profile();

        try {
            // Divide tariffs for profiles using DeliveryMethod
            if ($this->getProfile() && is_array($this->tariffDependency) && array_key_exists($this->getProfile(), $this->tariffDependency))
            {
                $profile->setDetails(array());
                $profile->setTermIncrease((int)$this->getOptions()->fetchTermIncrease());

                $this->shipments->setVariantPriority($this->tariffDependency[$this->getProfile()]);

                foreach ($this->shipments->getShipmentsTariffs() as $tarifId)
                {
                    $arResultTarif = $this->shipments->setTariffPriority($tarifId)->merge();

                    $profile->setDetails(array_merge(
                        $profile->getDetails(),
                        array($tarifId => array('price' => $arResultTarif['price'], 'termMin' => $arResultTarif['termMin'], 'termMax' => $arResultTarif['termMax']))
                    ));

                }
            }
        }
        catch (\Exception $e)
        {
            // Not used - too much crap
            // $profile->setSuccess(false)->setDetails($e->getMessage());
        }

        $this->profiles->addProfile($profile->setId($this->getProfile()));

        return $this;
    }

    /**
     * Calculate delivery price for given CoreShipment
     * Add summary data with tariffs calculation results in CoreShipment object
     *
     * @param CoreShipment $shipment
     * @return $this
     */
    public function calculateShipment(&$shipment)
    {
        // Alternative point guid preset while direct delivery calculation used
        if ($guid = $this->getSelectedPointGuid())
            $shipment->setDetails(array('pickupPointGuid' => $guid));

        $tableCalculator = new TableCalculator();
        $tableCalculator->setShipment($shipment)
            ->setPaymentType($this->getPayment())
            ->setVatRate($this->getVatRate());

        $result = $tableCalculator->calculate();

        if (!$result->isSuccess())
        {
            $Tarif = new Tariff($this->getProfile());
            $Tarif->setError(true)->setErrorText(implode(', ', $result->getErrorMessages()));
            $shipment->getSummary()->add($Tarif);
        }

        // Log
        //\Bitrix\Main\Diag\Debug::WriteToFile([$shipment, $result], 'calculateShipment', '__fp_Delivery.log');
        // --

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTariffDependency()
    {
        return $this->tariffDependency;
    }

    /**
     * @param mixed $tariffDependency
     * @return $this
     */
    public function setTariffDependency($tariffDependency)
    {
        $this->tariffDependency = $tariffDependency;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param bool|string $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

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

    /**
     * @return string|false
     */
    public function getSelectedPointGuid()
    {
        return $this->selectedPointGuid;
    }

    /**
     * @param string $guid
     * @return $this
     */
    public function setSelectedPointGuid($guid)
    {
        $this->selectedPointGuid = $guid;

        return $this;
    }
}