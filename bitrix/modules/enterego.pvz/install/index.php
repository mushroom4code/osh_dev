<?php

use Bitrix\Main\ModuleManager,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

if (class_exists("enterego_pvz"))
    return;

class enterego_pvz extends CModule
{
    var $MODULE_ID = 'enterego.pvz';
    var $MODULE_GROUP_RIGHTS = "Y";

    var $strError = '';
    var $arHandlers = array("sale" => array(
        "OnSaleComponentOrderCreated" => array("\CommonPVZ\DeliveryHelper", "addAssets")
    )
    );

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("EE_PVZ_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("EE_PVZ_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("EE_PVZ_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("EE_PVZ_PARTNER_URI");
        //$this->MODULE_CSS = "/bitrix/modules/" . $this->MODULE_ID . "/osh_admin.css";
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        foreach ($this->arHandlers as $moduleTo => $arEvents) {
            foreach ($arEvents as $eventName => $eventValues) {
                $className = $eventValues[0];
                $funcName = $eventValues[1];
                $eventManager->registerEventHandler($moduleTo, $eventName, $this->MODULE_ID, $className, $funcName);
            }
        }
        return true;
    }

    function UnInstallEvents()
    {
        COption::RemoveOption($this->MODULE_ID);
        $eventManager = EventManager::getInstance();
        foreach ($this->arHandlers as $moduleTo => $arEvents) {
            foreach ($arEvents as $eventName => $eventValues) {
                $className = $eventValues[0];
                $funcName = $eventValues[1];
                $eventManager->unRegisterEventHandler($moduleTo, $eventName, $this->MODULE_ID, $className, $funcName);
            }
        }
        return true;
    }

    function InstallDB(){
        global $DB, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/installTables.sql");
        if($this->errors !== false){
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }

        return true;
    }

    function UnInstallDB(){
        global $DB, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/uninstallTables.sql");
        if(!empty($this->errors)){
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }

        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/CommonPVZ/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_delivery/CommonPVZ/", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/php_interface/include/sale_delivery/CommonPVZ/");
        return true;
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
        ModuleManager::registerModule($this->MODULE_ID);
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}