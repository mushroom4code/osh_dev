<?php

namespace Ipol\Fivepost;


/**
 * Class abstractGeneral
 * @package Ipol\Fivepost\
 * Из этого класса наследуются все Главные классы (по факту - просто добавляет лейбл и код модуля
 */
class AbstractGeneral
{
    protected static $MODULE_LBL = IPOL_FIVEPOST_LBL;
    protected static $MODULE_ID  = IPOL_FIVEPOST;

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
}