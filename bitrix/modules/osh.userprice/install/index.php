<?php

use Bitrix\Main\EventManager;
use Enterego\Osh\Loyalty\PluginStatic;

require_once(__DIR__.'/../include.php');

Class osh_userprice extends CModule
{
    var $MODULE_ID = "osh.userprice";
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

        $this->MODULE_NAME = "Ent - Индивидуальные цены";
        $this->MODULE_DESCRIPTION = "Индивидуальный тип цены для пользователей (для товара или категории)";
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

        $arSQL = [];
        $arSQL[] = "CREATE TABLE IF NOT EXISTS `ent_user_price_rule`
            (
                id                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id            INT UNSIGNED NOT NULL,
                iblock_section_id   VARCHAR(255) DEFAULT NULL,
                product_id          INT UNSIGNED DEFAULT NULL,
                catalog_price_id    INT UNSIGNED NOT NULL,
                is_primary          BOOL DEFAULT false NOT NULL,
                
                KEY user_id (user_id)
            )";

        $arSQL[] = "CREATE INDEX `idx_ent_user_price_rule_product_id` ON `ent_user_price_rule` (`product_id`)";
        $arSQL[] = "CREATE INDEX `idx_ent_user_price_rule_iblock_section_id` ON `ent_user_price_rule` (`iblock_section_id`)";
        $arSQL[] = "CREATE INDEX `idx_ent_user_price_rule_is_primary` ON `ent_user_price_rule` (`is_primary`)";

        foreach($arSQL as $sql) {
            $DB->Query($sql);
        }
    }

    function UnInstallDB()
    {
        global $DB;

        $arSQL = [
            "DROP INDEX `idx_ent_user_price_rule_iblock_section_id` ON `ent_user_price_rule`",
            "DROP INDEX `idx_ent_user_price_rule_product_id` ON `ent_user_price_rule`",
            "DROP TABLE IF EXISTS ent_user_price_rule"
        ];

        foreach($arSQL as $sql) {
            $DB->Query($sql);
        }
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