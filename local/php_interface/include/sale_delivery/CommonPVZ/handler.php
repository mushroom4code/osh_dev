<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Delivery\CalculationResult;
use \Bitrix\Sale\Location;
use Bitrix\Main\Error;
use CIBlockElement;
use CIBlockSection;
use Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule("ipol.cdek");
\Bitrix\Main\Loader::includeModule("sale");


/*
 * @package Bitrix\Sale\Delivery\Services
 */

class CommonPVZHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    protected static $canHasProfiles = false;
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;
    protected static $locationCode = 0;

    /**
     * @param array $initParams
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment = null)
    {
        $result = new \Bitrix\Sale\Delivery\CalculationResult();

        if ($_POST['soa-action'] === 'setPVZPrice') {
            echo '<pre>';
            print_r($_POST);
            echo '</pre>';
            die();
        }

        // получаем код города
        $order = $shipment->getCollection()->getOrder();
        $props = $order->getPropertyCollection();
        $locationCode = $props->getDeliveryLocation()->getValue();
        $_POST['code_city'] = \CommonPVZ\DeliveryHelper::getCityName($locationCode);

        $resp = \CommonPVZ\DeliveryHelper::mainRequest($_POST);

        if ($resp['price'] === 0) {

            return $result->addError(new Error(Loc::getMessage("COMMONPVZ_CHOOSE_PVZ")));
        }

        $weight = $shipment->getWeight(); // вес отгрузки

        // местоположение
        echo '<pre>';
        print_r($resp);
        echo '</pre>';
        die();
        $result = new \Bitrix\Sale\Delivery\CalculationResult();
        $result->setDeliveryPrice(
            roundEx(
                500,
                SALE_VALUE_PRECISION
            )
        );
        $result->setPeriodDescription('2-3 days');

        return $result;


    }

    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
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
     * @throws \Exception
     */
    protected function getConfigStructure()
    {
        $result = [];


        return $result;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
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