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
                $deliveryParams['location'] = $propertyItem->getValue();
                $deliveryParams['location_name'] = DeliveryHelper::getCityName($deliveryParams['location']);
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
            if ($prop['CODE'] === 'FIAS') {
                $deliveryParams['fias'] = $propertyItem->getValue();
            }
            if ($prop['CODE'] === 'LATITUDE') {
                $deliveryParams['latitude'] = $propertyItem->getValue();
            }
            if ($prop['CODE'] === 'LONGITUDE') {
                $deliveryParams['longitude'] = $propertyItem->getValue();
            }
            if ($prop['CODE'] === 'DATE_DELIVERY') {
                $deliveryParams['date_delivery'] = $propertyItem->getValue();
            }
        }
        $deliveryParams['shipment_weight'] = $shipment->getWeight();
        $deliveryParams['shipment_cost'] = $shipment->getOrder()->getBasePrice();

        $orderBasket = $shipment->getOrder()->getBasket();
        $deliveryParams['packages'] = DeliveryHelper::getPackagesFromOrderBasket($orderBasket);
        ksort($deliveryParams['packages']);

        $deliveries = DeliveryHelper::getActiveDoorDeliveryInstance($deliveryParams);
        $order_price = false;
        $resDescription = [];

        if ($propTypeDeliveryId) {
            foreach ($deliveries as $delivery) {
                if ($deliveryParams['location'] != '0000073738' || $delivery->delivery_code === 'oshisha') {
                    $price = $delivery->getPriceDoorDelivery($deliveryParams);

                    if ($currentDelivery===$delivery->delivery_code) {
                        $result->setDeliveryPrice(
                            roundEx(
                                $delivery->delivery_code == 'oshisha' ? $price['price'] : $price,
                                SALE_VALUE_PRECISION
                            )
                        );
                        $checked = 'checked';
                        if (!empty($price['errors'])) {
                            $result->addError(new Error(
                                Loc::getMessage('SALE_DLVR_BASE_DELIVERY_PRICE_CALC_ERROR'),
                                'DELIVERY_CALCULATION'
                            ));
                        } else {
                            $order_price = $delivery->delivery_code == 'oshisha' ? $price['price'] : $price;
                        }
                    } else {
                        $checked = '';
                    }

                    if (empty($price['errors'])){
                        $resDescription[] = [
                            'code' => $delivery->delivery_code,
                            'checked' => !empty($checked),
                            'name' => $delivery->delivery_name,
                            'price' => $delivery->delivery_code == 'oshisha' ? $price['price'] : $price,
                            'noMarkup' => $delivery->delivery_code == 'oshisha' ? $price['noMarkup'] : false
                        ];
                    } else {
                        $resDescription[] = [
                            'code' => $delivery->delivery_code,
                            'name' => $delivery->delivery_name,
                            'error' => $price['errors']
                        ];
                    }
                }
            }

            $result->setDescription(json_encode($resDescription));
        }
        if (!$order_price && $order_price !== 0) {
            $result->addError(new Error(
                'Не выбрана служба доставки',
                'DELIVERY_CALCULATION'
            ));
        }
        return $result;
    }
}