<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use CommonPVZ\DeliveryHelper;

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('enterego.pvz'))
    return;

class CommonPVZHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    protected $handlerCode = 'enterego.pvz';
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

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     * @return CalculationResult
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment)
    {
        $result = new \Bitrix\Sale\Delivery\CalculationResult();
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
                $price = DeliveryHelper::getPrice($_POST['dataToHandler']);
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

        $result->setDescription(DeliveryHelper::getButton());
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