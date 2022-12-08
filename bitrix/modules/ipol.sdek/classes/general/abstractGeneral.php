<?php

namespace Ipolh\SDEK;


use Ipolh\SDEK\Bitrix\Entity\cache;
use Ipolh\SDEK\Bitrix\Entity\encoder;
use Ipolh\SDEK\Legacy\transitApplication;
use Ipolh\SDEK\SDEK\SdekApplication;

class abstractGeneral
{
    protected static $MODULE_LBL = IPOLH_SDEK_LBL;
    protected static $MODULE_ID  = IPOLH_SDEK;

    /**
     * @return string
     */
    public static function getMODULELBL()
    {
        return self::$MODULE_LBL;
    }

    /**
     * @return string
     */
    public static function getMODULEID()
    {
        return self::$MODULE_ID;
    }

    public static function makeApplication($account,$password)
    {
        $encoder = new encoder();
        $cache   = new cache();
        $timeout = option::get('dostTimeout');

        return (self::isNewApp()) ? new SdekApplication(
            $account,
            $password,
            false,
            $timeout,
            $encoder,
            $cache
        ) : new transitApplication($account,$password);
    }

    public static function isNewApp()
    {
        return (option::get('useOldApi') !== 'Y');
    }
}