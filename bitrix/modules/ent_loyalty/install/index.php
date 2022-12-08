<?php

use Bitrix\Main\EventManager;
use Enterego\Osh\Loyalty\PluginStatic;

require_once(__DIR__.'/../lib/include.php');

Class ent_loyalty extends CModule
{
    var $MODULE_ID = "ent_loyalty";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    public function __construct()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = "Адаптация бонусной и реферальной системы Oshisha";
        $this->MODULE_DESCRIPTION = "Адаптация плагина skyweb24.loyaltyprogram к 1с импорту/экспорту Oshisha";
    }

    /**
     * CModule implementation to "install" module
     */
    function DoInstall()
    {
        global $APPLICATION;

        $this->InstallDB();
        $this->InstallEvents();

        RegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile($this->MODULE_NAME, __DIR__."/step.php");
    }

    /**
     * CModule implementation to "uninstall" module
     */
    function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallEvents();
        $this->UnInstallDB();

        UnRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile($this->MODULE_NAME, __DIR__."/unstep.php");
    }

    /**
     * SQL schema/data installation
     */
    function InstallDB()
    {
        global $DB;

        $sql = "CREATE TABLE IF NOT EXISTS ent_bonus_log
  (
    id INT NOT NULL AUTO_INCREMENT,
    1c_user_id      VARCHAR(255) DEFAULT NULL,
    bitrix_order_id VARCHAR(255) DEFAULT NULL,
    status INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY status (status)
  )
";
        $DB->Query($sql);
    }

    function UnInstallDB()
    {
        global $DB;

        $sql = "DROP TABLE IF EXISTS ent_bonus_log";
        $DB->Query($sql);
    }

    function InstallEvents()
    {
        Cmodule::IncludeModule('sale');

        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
            'sale',
            \Bitrix\Sale\Cashbox\Check::EVENT_ON_CHECK_PREPARE_DATA,
            $this->MODULE_ID,
            PluginStatic::class,
            'OnSampleEvent'
        );
    }

    /**
     * @return bool
     */
    function UnInstallEvents(){

        $events = [
            'sale' => [
                'OnSampleEvent',
            ],
        ];

        foreach($events as $moduleName => $arEvents)
        {
            foreach($arEvents as $eventName)
            {
                $handlers = EventManager::getInstance()->findEventHandlers($moduleName, $eventName);
                foreach($handlers as $arHandler)
                {
                    if($arHandler['TO_CLASS'] == PluginStatic::class)
                    {
                        EventManager::getInstance()->unRegisterEventHandler(
                            $moduleName,
                            $eventName,
                            $this->MODULE_ID,
                            $arHandler['TO_CLASS'],
                            $arHandler['TO_METHOD']
                        );
                    }
                }
            }
        }

        return true;
    }
}
?>