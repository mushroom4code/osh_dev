<?php

// no namespace

class EntUserPriceGlobal
{
    public const AUDIT_TYPE = 'ENT_USERPRICE_LOG';

    /**
     * Общенная функция для кэширования операций
     *
     * @param callable $getValue
     * @param $cacheId
     * @param $cacheKey
     * @param int $cacheTtl
     * @return mixed
     */
    public static function GetCached(callable $getValue, $cacheId, $cacheKey, $cacheTtl = 33333333)
    {
        global $OSH_CACHE;

        if(!$OSH_CACHE) {
            $OSH_CACHE = \Bitrix\Main\Application::getInstance()->getManagedCache();
        }

        if ($OSH_CACHE->read($cacheTtl, $cacheId)) {
            $vars = $OSH_CACHE->get($cacheId);
            if(isset($vars[$cacheKey])) {
                return $vars[$cacheKey];
            }
        }

        $value = $getValue();

        $OSH_CACHE->set($cacheId, array($cacheKey => $value));

        return $value;
    }

    /**
     * Стирание кэша с ID.
     *
     * @param $cacheId
     * @throws \Bitrix\Main\SystemException
     */
    private static function EraseCache($cacheId)
    {
        global $OSH_CACHE;

        if(!$OSH_CACHE) {
            $OSH_CACHE = \Bitrix\Main\Application::getInstance()->getManagedCache();
        }

        $OSH_CACHE->clean($cacheId);
    }

    /**
     * Очистка известных кэшей для случаев значительного обновления каталога/БД.
     */
    public static function EraseCaches()
    {
        try {
            self::EraseCache('arProductPrice');
        } catch (\Exception $ex) {
            $class = self::class;

            \CEventLog::Add(array(
                "SEVERITY" => "ERROR",
                "AUDIT_TYPE_ID" => \EntUserPriceGlobal::AUDIT_TYPE,
                "MODULE_ID" => "ent_userprice",
                "DESCRIPTION" => "Ошибка очистки кэшей в {$class}: {$ex->getMessage()}",
            ));
        }
    }

    public static function OnEventLogGetAuditTypes()
    {
        return [
            self::AUDIT_TYPE => 'Модуль Индивидуальные Цены'
        ];
    }
}