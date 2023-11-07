<?php

namespace Enterego\Subsidiary;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;

class Storage
{
    private static $_instanseStorageSettings = null;

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getSubsidiaryList(): array
    {
        $result = [];

        $rsSite = SiteTable::getList([
            'select'=>['NAME', 'LID'],
            'filter' => ['LID'=>SUBSIDIARY_SITE_LIST],
            'order' => ['SORT'=>'DESC']
        ]);
        while ($arSite = $rsSite->fetch()) {
            $result[] = $arSite;
        }

        return $result;
    }

    /**
     * @return int[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getSiteStorages():array {

        if (is_null(self::$_instanseStorageSettings)) {
            $result = [];
            $rsStore = StoreTable::getList(['select'=>['ID', 'SITE_ID'], 'filter' => ['SITE_ID'=>SUBSIDIARY_SITE_LIST]]);
            while ($arStore = $rsStore->fetch()) {
                $result[$arStore['SITE_ID']][] = $arStore['ID'];
            }
            self::$_instanseStorageSettings = $result;
        }

        return empty(self::$_instanseStorageSettings[SITE_ID])  ? [0] : self::$_instanseStorageSettings[SITE_ID];
    }
}