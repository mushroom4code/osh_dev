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
use CModule;
use CSaleOrderProps;
use CSaleOrderPropsValue;

Loc::loadMessages(__FILE__);


class CommonPVZHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    protected static $canHasProfiles = false;
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;

    /**
     * @param array $initParams
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    // TODO так как непонял почему у физлица не возвращается свойство ADDRESS вот такой костыль
    private function setAddress($orderId, $address)
    {
        if (CModule::IncludeModule('sale')) {
            if ($prop = CSaleOrderProps::GetList([], ['CODE' => 'ADDRESS'])->Fetch()) {

                $ii = CSaleOrderPropsValue::Add([
                    'NAME' => $prop['NAME'],
                    'CODE' => $prop['CODE'],
                    'ORDER_PROPS_ID' => $prop['ID'],
                    'ORDER_ID' => $orderId,
                    'VALUE' => $address
                ]);
            }
        }
    }

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment)
    {
        if (isset($_POST['price'])) {
            $_SESSION['pricePVZ'] = $_POST['price'];
            $_SESSION['addressPVZ'] = $_POST['address'];
        }

        $price = $_SESSION['pricePVZ'] ?? 0;
        $address = $_SESSION['addressPVZ'] ?? '';

        $order = $shipment->getCollection()->getOrder();
        $propertyCollection = $order->getPropertyCollection();
        $adressProperty = $propertyCollection->getAddress();

        if ($adressProperty === null) {
            self::setAddress($order->getId(), $address);
        } else {
            $adressProperty->setValue($address);
        }

        $result = new \Bitrix\Sale\Delivery\CalculationResult();
        $result->setDeliveryPrice(
            roundEx(
                $price,
                SALE_VALUE_PRECISION
            )
        );

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
        return array();
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