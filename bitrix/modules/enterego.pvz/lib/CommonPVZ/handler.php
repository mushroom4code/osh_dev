<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use CommonPVZ\CommonPVZ;
use CommonPVZ\DeliveryHelper;
use Exception;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('enterego.pvz'))
    return;

class CommonPVZHandler extends Base
{
    protected $handlerCode = 'enterego.pvz';
    protected static $canHasProfiles = true;
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;

    /**
     * @param array $initParams
     * @throws ArgumentTypeException|SystemException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    /**
     * @param Shipment|null $shipment
     * @return CalculationResult
     * @throws ArgumentException
     */
    protected function calculateConcrete(Shipment $shipment)
    {
        throw new SystemException('Only profiles can calculate concrete');
    }

    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
    }

    public static function getChildrenClassNames()
    {
        return array(
            '\CommonPVZ\DoorDeliveryProfile',
            '\CommonPVZ\PVZDeliveryProfile',
        );
    }

    public function getProfilesList()
    {
        return array(
            Loc::getMessage("DOOR_DELIVERY_PROFILE_TITLE"),
            "Общая карта с ПВЗ."
        );
    }

    /**
     * @return string Class, service description.
     */
    public static function getClassDescription()
    {
        return Loc::getMessage("COMMONPVZ_DESCRIPTION");
    }

    /**
     * @return string Class title.
     */
    public static function getClassTitle()
    {
        return Loc::getMessage("COMMONPVZ_TITLE");
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getConfigStructure()
    {
        return array();
    }

    /**
     * @return array
     * @throws ArgumentException
     */
    protected function getLocationGroups()
    {
        $result = [];


        return $result;
    }


    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

}