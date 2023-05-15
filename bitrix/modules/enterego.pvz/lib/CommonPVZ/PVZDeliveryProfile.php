<?php

namespace CommonPVZ;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use CommonPVZ\CommonPVZ;
use CommonPVZ\DeliveryHelper;
use Exception;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('enterego.pvz'))
    return;

class PVZDeliveryProfile extends Base
{
    protected $handlerCode = 'enterego.pvz';

    protected static $isProfile = true;
    protected static $parent = null;

    /**
     * @param array $initParams
     * @throws ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
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

    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function isProfile()
    {
        return self::$isProfile;
    }


    /**
     * @param Shipment|null $shipment
     * @return CalculationResult
     * @throws ArgumentException
     */
    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new CalculationResult();
        $price = 0;
        $adr = '';

        $order = $shipment->getOrder();
        $propertyCollection = $order->getPropertyCollection();

        $cache = Cache::createInstance();
        $cachePath = '/getAllPVZpr';

            if ($_POST['dataToHandler']['code_pvz'] === 'undefined') {
                $adr = $_POST['dataToHandler']['delivery'] . ': ' . $_POST['dataToHandler']['to'];
            } else {
                $adr = $_POST['dataToHandler']['delivery'] . ': ' . $_POST['dataToHandler']['to'] . ' #' . $_POST['dataToHandler']['code_pvz'];
            }
            $f = serialize($adr);
            foreach ($propertyCollection as $propertyItem) {
                $prop = $propertyItem->getProperty();
                if ($prop['CODE'] === 'TYPE_DELIVERY') {
                    $deliveryParams['delivery'] = $propertyItem->getValue();
                }
                if ($prop['CODE'] === 'TYPE_PVZ') {
                    $deliveryParams['type_pvz'] = $propertyItem->getValue();
                }
                if ($prop['CODE'] === 'LOCATION') {
                    $deliveryParams['code_city'] = $propertyItem->getValue();
                    $deliveryParams['name_city'] = json_decode(DeliveryHelper::getCityName($deliveryParams['code_city']), true)['LOCATION_NAME'];
                }
                if($prop['CODE'] === 'DEFAULT_ADDRESS_PVZ') {
                    $deliveryParams['to'] = $propertyItem->getValue();
                }
                if ($prop['CODE'] === 'ZIP') {
                    $deliveryParams['postindex'] = $propertyItem->getValue();
                }
                if ($prop['CODE'] === 'COMMON_PVZ') {
                    $deliveryParams['code_pvz'] = $propertyItem->getValue();
                }

            }
            $deliveryParams['weight'] = $shipment->getWeight();
            $deliveryParams['cost'] = $shipment->getOrder()->getBasePrice();

            $orderBasket = $shipment->getOrder()->getBasket();
            $deliveryParams['packages'] = DeliveryHelper::getPackagesFromOrderBasket($orderBasket);
            ksort($deliveryParams['packages']);
            if ($cache->initCache(7200, 'pvz_price_' . $f, $cachePath)) {
                $price = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                if (!empty($deliveryParams['delivery'])){
                    $delivery = CommonPVZ::getInstanceObject($deliveryParams['delivery']);
                    $price = $delivery->getPrice($deliveryParams);
                    if ($price === false) {
                        return $result->addError(
                            new Error(
                                Loc::getMessage('SALE_DLVR_BASE_DELIVERY_PRICE_CALC_ERROR'),
                                'DELIVERY_CALCULATION'
                            ));
                    }
                    if ($price !== false && is_numeric($price) && $price !== '0' && (int)$price > 0)
                        $cache->endDataCache($price);
                    else
                        $price = 0;
                } else {
                    return $result->addError(
                        new Error(
                            Loc::getMessage('SALE_DLVR_BASE_DELIVERY_PRICE_CALC_ERROR'),
                            'DELIVERY_CALCULATION'
                        ));
                }
            }


        if ($price === 0) {
            $f = serialize($adr);
            if ($cache->initCache(7200, 'pvz_price_' . $f, $cachePath)) {
                $price = $cache->getVars();
            }
        }

        $result->setDeliveryPrice(
            roundEx(
                $price,
                SALE_VALUE_PRECISION
            )
        );

        return $result;
    }

}