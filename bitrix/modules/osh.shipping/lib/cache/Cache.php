<?php

namespace Osh\Delivery\Cache;

use Bitrix\Main\Application,
    Osh\Delivery\Cache\Module,
    Osh\Delivery\Options\Config;

class Cache
{
    const HOUR = 3600;
    const DAY = 86400;
    const WEEK = 604800;
    const MONTH = 2419200;
    const YEAR = 31557600;
    const PVZ_NAME = 'OSH_DELIVERY_PVZ';
    const PRICE_CALC_NAME = 'OSH_DELIVERY_PRICE_CALC';
    const PRICE_CALC_INT_NAME = 'OSH_DELIVERY_PRICE_INT_CALC';
    const METHODS_NAME = 'OSH_DELIVERY_METHODS';
    const PICKUP_NAME = 'OSH_DELIVERY_PICKUP';
    const DAYS_OFF = 'OSH_DELIVERY_DOFF';
    const DELIERY_TIMES = 'OSH_DELIVERY_DTIMES';
    const COUNTIRES = 'OSH_COUNTRIES';
    const STOCKS = 'OSH_STOCKS';
    const STATUS_LIST = 'OSH_STATUSES';

    protected static function getCacheId($params)
    {
        return md5(serialize($params));
    }

    protected static function getCache($ttl, $cacheId)
    {
        $result = false;
        switch (Config::getCacheMechanism()) {
            case Config::CACHE_MEC_NATIVE:
                $cacheManager = Application::getInstance()->getManagedCache();

                if ($cacheManager->read($ttl, $cacheId)) {
                    $result = $cacheManager->get($cacheId);
                }
                break;
            case Config::CACHE_MEC_MODULE:
                $result = Module::getInstance()->get($ttl, $cacheId);
                break;
        }
        return $result;
    }

    protected static function setCache($cacheId, $value)
    {
        switch (Config::getCacheMechanism()) {
            case Config::CACHE_MEC_NATIVE:
                $cacheManager = Application::getInstance()->getManagedCache();
                $cacheManager->set($cacheId, $value);
                break;
            case Config::CACHE_MEC_MODULE:
                Module::getInstance()->set($cacheId, $value);
                break;
        }
    }

    protected static function cleanCache($cacheId)
    {
        switch (Config::getCacheMechanism()) {
            case Config::CACHE_MEC_NATIVE:
                $cacheManager = Application::getInstance()->getManagedCache();
                $cacheManager->clean($cacheId);
                break;
            case Config::CACHE_MEC_MODULE:
                Module::getInstance()->delete($cacheId);
                break;
        }
    }

    public static function cleanAll()
    {
        switch (Config::getCacheMechanism()) {
            case Config::CACHE_MEC_NATIVE:
                Application::getInstance()->getManagedCache()->cleanAll();
                break;
            case Config::CACHE_MEC_MODULE:
                Module::getInstance()->cleanAll();
                break;
        }
    }

    public static function getLocationData($code)
    {
        return self::getCache(self::DAY, 'location_code' . $code);
    }

    public static function setLocationData($code, $values)
    {
        $cacheId = 'location_code' . $code;
        return self::setCache($cacheId, $values);
    }

    public static function getPvz($params)
    {
        $ts4am = self::get4amMTZ();
        $tsy4am = self::getY4amMTZ();
        $tsNow = self::getNowMTZ();

        $secondsAfter4Am = $tsNow - $ts4am;
        if ($secondsAfter4Am >= 0) {
            $ttl = $secondsAfter4Am;
        } else {
            $ttl = $tsNow - $tsy4am;
        }
        return self::getCache($ttl, self::PVZ_NAME . self::getCacheId($params));
    }

    public static function setPvz($params, $values)
    {
        $cacheId = self::PVZ_NAME . self::getCacheId($params);
        return self::setCache($cacheId, $values);
    }

    public static function getDeliveryPrice($params)
    {
        return self::getCache(self::WEEK, self::PRICE_CALC_NAME . self::getCacheId($params));
    }

    public static function setDeliveryPrice($params, $values)
    {
        $cacheId = self::PRICE_CALC_NAME . self::getCacheId($params);
        return self::setCache($cacheId, $values);
    }

    public static function getDeliveryPriceInternational($params)
    {
        return self::getCache(self::WEEK, self::PRICE_CALC_INT_NAME . self::getCacheId($params));
    }

    public static function setDeliveryPriceInternational($params, $values)
    {
        $cacheId = self::PRICE_CALC_INT_NAME . self::getCacheId($params);
        return self::setCache($cacheId, $values);
    }

    public static function getShippingMethods()
    {
        return self::getCache(self::DAY, self::METHODS_NAME);
    }

    public static function setShippingMethods($values)
    {
        return self::setCache(self::METHODS_NAME, $values);
    }

    public static function cleanShippingMethods()
    {
        return self::cleanCache(self::METHODS_NAME);
    }

    public static function getCountries()
    {
        return self::getCache(self::MONTH, self::COUNTIRES);
    }

    public static function setCountries($arCountries)
    {
        return self::setCache(self::COUNTIRES, $arCountries);
    }

    public static function cleanCountries()
    {
        return self::cleanCache(self::COUNTIRES);
    }

    public static function getStocks()
    {
        return self::getCache(self::DAY, self::STOCKS);
    }

    public static function setStocks($arStocks)
    {
        return self::setCache(self::STOCKS, $arStocks);
    }

    public static function cleanStocks()
    {
        return self::cleanCache(self::STOCKS);
    }

    public static function getPickupTimes($courier)
    {
        return self::getCache(self::WEEK, self::PICKUP_NAME . $courier);
    }

    public static function setPickupTimes($courier, $values)
    {
        $cacheId = self::PICKUP_NAME . $courier;
        return self::setCache($cacheId, $values);
    }

    public static function getDoff($params)
    {
        return self::getCache(self::WEEK, self::DAYS_OFF . self::getCacheId($params));
    }

    public static function setDoff($params, $values)
    {
        $cacheId = self::DAYS_OFF . self::getCacheId($params);
        return self::setCache($cacheId, $values);
    }

    public static function getDeliveryTimes()
    {
        return self::getCache(self::DAY, self::DELIERY_TIMES);
    }

    public static function setDeliveryTimes($values)
    {
        return self::setCache(self::DELIERY_TIMES, $values);
    }

    public static function cleanDeliveryTimes()
    {
        return self::cleanCache(self::DELIERY_TIMES);
    }

    public static function getStatusList()
    {
        return self::getCache(self::MONTH, self::STATUS_LIST);
    }

    public static function setStatusList($values)
    {
        return self::setCache(self::STATUS_LIST, $values);
    }

    public static function cleanStatusList()
    {
        return self::cleanCache(self::STATUS_LIST);
    }

    private function get4amMTZ()
    {
        return self::getTsMTZ('today 4:00');
    }

    private function getY4amMTZ()
    {
        return self::getTsMTZ('yesterday 4:00');
    }

    protected function getNowMTZ()
    {
        return self::getTsMTZ('now');
    }

    protected function getTsMTZ($dateString)
    {
        return (new \DateTime($dateString, new \DateTimeZone('Europe/Moscow')))->getTimestamp();
    }
}
