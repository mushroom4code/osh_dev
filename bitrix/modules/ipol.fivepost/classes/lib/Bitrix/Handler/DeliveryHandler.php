<?
namespace Ipol\Fivepost\Bitrix\Handler;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Sale\Shipment;
use \Bitrix\Sale\Delivery\CalculationResult;
use \Bitrix\Sale\Delivery\Services\Manager;

use \Ipol\Fivepost\PointsHandler;
use \Ipol\Fivepost\LocationsHandler;
use \Ipol\Fivepost\ProfileHandler;
use \Ipol\Fivepost\Bitrix\Tools;
use \Ipol\Fivepost\Bitrix\Entity\Options;

Loc::loadMessages(__FILE__);

/**
 * Class DeliveryHandler
 * @package namespace Ipol\Fivepost\Bitrix\Handler
 */
class DeliveryHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    /**
     * Calculate price immediately
     * @var bool
     */
    protected static $isCalculatePriceImmediately = true;

    /**
     * Can has profiles
     * @var bool
     */
    protected static $canHasProfiles = true;

    /**
     * Uses extra services
     * @var bool
     */
    protected static $whetherAdminExtraServicesShow = false;

    /**
     * @param array $initParams
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    /**
     * @return string Class title
     */
    public static function getClassTitle()
    {
        return Tools::getMessage("DELIVERY_NAME");
    }

    /**
     * @return string Class, service description
     */
    public static function getClassDescription()
    {
        return Tools::getMessage("DELIVERY_DESCRIPTION");
    }

    /**
     * @return bool
     */
    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
    }

    /**
     * @return array Class names for profiles
     */
    public static function getChildrenClassNames()
    {
        return array_values(ProfileHandler::getProfileClasses());
    }

    /**
     * @return array Profiles list
     */
    public function getProfilesList()
    {
        return ProfileHandler::getProfilesList();
    }

    /**
     * @return array Profiles params
     */
    public function getProfilesDefaultParams()
    {
        $options = new Options();

        $common = array(
            "PARENT_ID" => $this->id,
            "ACTIVE" => $this->active ? "Y" : "N",
            "SORT" => $this->sort,
            "CURRENCY" => $this->currency,
            "CONFIG" => array()
        );

        // Set VAT for profiles if corresponded rate exists
        if (($vatId = self::getDesiredVatId((int)$options->fetchDesired_vat_rate())) !== false)
            $common['VAT_ID'] = $vatId;

        return ProfileHandler::makeDefaultParams($common);
    }

    /**
     * Try to get VAT id for desired VAT rate
     * Return id or false if failed
     *
     * @param $rate
     * @return false|int
     */
    public static function getDesiredVatId($rate)
    {
        if (Loader::includeModule('catalog'))
        {
            $possibleVat = \Bitrix\Catalog\VatTable::getList(['filter' => ['=RATE' => (int)$rate], 'select' => ['ID', 'RATE']])->fetch();
            if (is_array($possibleVat))
                return $possibleVat['ID'];
        }
        return false;
    }

    /**
     * Compatibility check
     *
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return bool
     */
    public function isCompatible(Shipment $shipment)
    {
        // Always compatible, otherwise handler hides in the shadows from delivery list while manager try to change delivery service in admin interface
        // Ask Bitrix about this shit logic
        return true;
    }

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     */
    protected function calculateConcrete(Shipment $shipment = null)
    {
        $result = new CalculationResult;

        // Cause only profiles calculations allowed
        $result->addError(new Error(Tools::getMessage('IPOL_FIVEPOST_DELIVERY_CALC_ERROR_NO_DIRECT_CALL'), 'DELIVERY_CALCULATION'));
        return $result;
    }

    // Disabled cause no additional config needed
    /**
     * @return array
     * @throws \Exception
     */
    /*protected function getConfigStructure()
    {
        $options = new Options();
        $apikey = $options->fetchApiKey() ?: '-';

        $result = array(
            "MAIN" => array(
                "TITLE" => Tools::getMessage('DELIVERY_HANDLER_MAIN_TAB_TITLE'),
                "DESCRIPTION" => Tools::getMessage('DELIVERY_HANDLER_MAIN_TAB_DESCR'),
                "ITEMS" => array(
                    "APIKEY" => array(
                        "TYPE" => "DELIVERY_READ_ONLY",
                        "NAME" => Tools::getMessage('DELIVERY_HANDLER_MAIN_TAB_APIKEY'),
                        "VALUE" => $apikey,
                        "VALUE_VIEW" => $apikey,
                    ),
                )
            )
        );

        return $result;
    }
    */

    /**
     * Show message on delivery service edit page
     *
     * @return array
     * @see \CAdminMessage::CAdminMessage
     */
    public function getAdminMessage()
    {
        $options = new Options();

        if (!(\Ipol\Fivepost\authHandler::isAuthorized()))
            return array(
                "MESSAGE" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_AUTH_TITLE'),
                "DETAILS" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_AUTH_DESCR'),
                "TYPE" => "ERROR",
                "HTML" => true
            );

        if ($options->fetchSync_data_completed() !== 'Y')
            return array(
                "MESSAGE" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_SYNC_TITLE'),
                "DETAILS" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_SYNC_DESCR'),
                "TYPE" => "ERROR",
                "HTML" => true
            );

        return array();
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    /**
     * @return bool
     */
    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    /**
     * Check if shipment has some data about shipped items. Cause in some cases there are no items in shipment.
     *
     * @param \Bitrix\Sale\Shipment $shipment
     * @return bool
     */
    public static function checkShipmentItems(Shipment $shipment)
    {
        return (is_object($shipment) && is_object($shipment->getShipmentItemCollection()) && !$shipment->isEmpty());
    }

    /**
     * Add delivery handler profiles after parent handler entity was added
     *
     * @param int $handlerId
     * @param array $fields
     * @return bool
     */
    public static function onAfterAdd($handlerId, array $fields = array())
    {
        if ($handlerId <= 0)
            return false;

        $result = true;

        $fields["ID"] = $handlerId;
        $handler = new self($fields);
        $profiles = $handler->getProfilesDefaultParams();

        if (is_array($profiles))
        {
            foreach ($profiles as $profile)
            {
                $res = Manager::add($profile);
                $result = $result && $res->isSuccess();
            }
        }

        return $result;
    }

    /**
     * Add additional tab with module statistic
     *
     * @return array
     */
    public function getAdminAdditionalTabs()
    {
        $options = new Options();

        $content = '';
        $content .= self::makeStatusTableRow(Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_APIKEY'), ($options->fetchApiKey() ?: '-'));

        $syncData = ($options->fetchSync_data_completed() === 'Y') ? Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA_Y') :
            Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA_N');

        $content .= self::makeStatusTableRow(Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA'), $syncData);

        $prStat = PointsHandler::makeStatistic()->getData();
        $content .= self::makeStatusTableRow(Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_POINTS_LOADED'), $prStat['POINTS_LOADED']);

        $locStat = LocationsHandler::makeStatistic()->getData();
        $content .= self::makeStatusTableRow(Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_LOCATIONS_LOADED'), $locStat['LOCATIONS_LOADED']);

        return array(
            array(
                "TAB"     => Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_TITLE'),
                "TITLE"   => Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_DESCR'),
                "CONTENT" => $content
            )
        );
    }

    /**
     * Make one row for table on status tab
     *
     * @param $name string param name
     * @param $value string param value
     * @return string
     */
    public static function makeStatusTableRow($name, $value)
    {
        return '<tr><td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l">'.$name.'</td><td width="60%" class="adm-detail-valign-top adm-detail-content-cell-r">'.$value.'</td></tr>';
    }
}