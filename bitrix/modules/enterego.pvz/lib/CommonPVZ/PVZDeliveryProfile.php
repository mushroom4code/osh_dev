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

        $order = $shipment->getCollection()->getOrder();
        $propertyCollection = $order->getPropertyCollection();

        $cache = Cache::createInstance();
        $cachePath = '/getAllPVZpr';

        if (isset($_POST['dataToHandler'])) {
            if ($_POST['dataToHandler']['code_pvz'] === 'undefined') {
                $adr = $_POST['dataToHandler']['delivery'] . ': ' . $_POST['dataToHandler']['to'];
            } else {
                $adr = $_POST['dataToHandler']['delivery'] . ': ' . $_POST['dataToHandler']['to'] . ' #' . $_POST['dataToHandler']['code_pvz'];
            }
            $f = serialize($adr);
            if ($cache->initCache(7200, 'pvz_price_' . $f, $cachePath)) {
                $price = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                $delivery = CommonPVZ::getInstanceObject($_POST['dataToHandler']['delivery']);
                $price = $delivery->getPrice($_POST['dataToHandler']);
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
            }
        } else {
            foreach ($propertyCollection as $item) {
                if ($item->getField('CODE') == "COMMON_PVZ") {
                    $adr = $item->getValue();
                    break;
                }
            }
        }

        if ($price === 0) {
            $f = serialize($adr);
            if ($cache->initCache(7200, 'pvz_price_' . $f, $cachePath)) {
                $price = $cache->getVars();
            }
        }

//        $result->setDescription(DeliveryHelper::getButton());
        $result->setDeliveryPrice(
            roundEx(
                $price,
                SALE_VALUE_PRECISION
            )
        );

        return $result;
    }

}