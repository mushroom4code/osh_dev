<?php

namespace CommonPVZ;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Shipment;
use CommonPVZ\CommonPVZ;
use CommonPVZ\DeliveryHelper;
use http\Params;
use Bitrix\Sale\Delivery\DeliveryLocationTable;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('enterego.pvz'))
    return;

class DoorDeliveryProfile extends Base
{
    protected static $isProfile = true;
    protected static $parent = null;
    protected static $doorDeliveryId = DOOR_DELIVERY_ID;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->parent = Manager::getObjectById($this->parentId);
    }

    public static function getClassTitle()
    {
        return Loc::getMessage("DOOR_DELIVERY_PROFILE_TITLE");
    }

    public static function getClassDescription()
    {
        return 'My custom handler for Yet Another Delivery Service profile';
    }

    public function getParentService()
    {
        return $this->parent;
    }

    public function isCalculatePriceImmediately()
    {
        return $this->getParentService()->isCalculatePriceImmediately();
    }

    public static function isProfile()
    {
        return self::$isProfile;
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {
        $result = new CalculationResult();

        $description = '';
        $deliveries = DeliveryHelper::getActiveDeliveries();
        $currentDelivery = '';
        $propTypeDeliveryId = '';
        $deliveryParams = array();
        $propertyCollection = $shipment->getOrder()->getPropertyCollection();
        foreach ($propertyCollection as $propertyItem) {
            $prop = $propertyItem->getProperty();
            if ($prop['CODE'] === 'TYPE_DELIVERY') {
                $propTypeDeliveryId = $prop['ID'];
                $currentDelivery = $propertyItem->getValue();
            }
            if ($prop['CODE'] === 'LOCATION') {
                $deliveryParams['location_name'] = DeliveryHelper::getCityName($propertyItem->getValue());
                $location_check = DeliveryLocationTable::checkConnectionExists(self::$doorDeliveryId,$propertyItem->getValue(),
                    array(
                        'LOCATION_LINK_TYPE' => 'AUTO'
                    )
                );
            }
            if ($prop['CODE'] === 'ADDRESS') {
                $deliveryParams['address'] = $propertyItem->getValue();
            }
            if ($prop['CODE'] === 'ZIP') {
                $deliveryParams['zip_to'] = $propertyItem->getValue();
            }
            if ($prop['CODE'] === 'STREET_KLADR') {
                $deliveryParams['street_kladr_to'] = $propertyItem->getValue();
            }
        }
        $deliveryParams['shipment_weight'] = $shipment->getWeight();
        $deliveryParams['shipment_cost'] = $shipment->getOrder()->getBasePrice();
        $deliveryParams['packages'] = array();
        $orderBasket = $shipment->getOrder()->getBasket();
        $deliveryParams['packages'] = DeliveryHelper::getPackagesFromOrderBasket($orderBasket);
        ksort($deliveryParams['packages']);
        if ($location_check === false)
            unset($deliveries[array_search('Oshisha', $deliveries)]);
        if ($propTypeDeliveryId) {
            foreach ($deliveries as $delivery) {
                $deliveryInstance = CommonPVZ::getInstanceObject($delivery);
                $price = $deliveryInstance->getPriceDoorDelivery($deliveryParams);

                if ($currentDelivery===$deliveryInstance->delivery_name) {
                    $result->setDeliveryPrice(
                        roundEx(
                            $price,
                            SALE_VALUE_PRECISION
                        )
                    );
                    $checked = 'checked';
                    if (!empty($price['errors'])) {
                        $result->addError(new Error(
                            Loc::getMessage('SALE_DLVR_BASE_DELIVERY_PRICE_CALC_ERROR'),
                            'DELIVERY_CALCULATION'
                        ));
                    }
                } else {
                    $checked = '';
                }

                if (empty($price['errors'])){
                    $description .= "<div class=\"bx-soa-pp-company-graf-container  box_with_delivery mb-3\">
                    <input id=\"TYPE_DELIVERY_{$delivery['name']}\"   onclick='BX.Sale.OrderAjaxComponent.sendRequest();' 
                        name=\"ORDER_PROP_$propTypeDeliveryId\" type=\"radio\" $checked value='{$deliveryInstance->delivery_name}' >
                    <div class=\"bx-soa-pp-company-smalltitle color_black font_weight_600\">{$deliveryInstance->delivery_name} - $price</div>
                    </div>";
                }
            }

            $result->setDescription($description);
        }

        return $result;
    }
}