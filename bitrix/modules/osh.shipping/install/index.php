<?php
use Bitrix\Main\ModuleManager,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Sale\Delivery\Services\Table as DST,
    Osh\Delivery\OshHandler;

Loc::loadMessages(__FILE__);

if (class_exists("osh_shipping"))
    return;

Class osh_shipping extends CModule {
    const MODULE_ID = 'osh.shipping';
    var $MODULE_ID = 'osh.shipping';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";
    var $strError = '';
    var $arHandlers = array("sale" => array(
            "onSaleDeliveryHandlersClassNamesBuildList" => array('COshDeliveryHandler', 'addCustomDeliveryServices'),
            "OnSaleOrderBeforeSaved" => array('COshDeliveryHandler', 'saveInNewOrderMethodPVZ'),
//            "OnSaleDeliveryServiceCalculate" => array('COshDeliveryHandler', 'onDeliveryServiceCalculate'),
//            "OnBeforeSaleShipmentSetField" => array('COshDeliveryHandler', 'sendOrderToOsh'),
//            "OnSaleComponentOrderShowAjaxAnswer" => array('COshDeliveryHandler', 'showAjaxAnswer'),
            "OnSaleComponentOrderCreated" => array('COshDeliveryHandler', 'showCreateAnswer'),
//            "OnSaleComponentOrderProperties" => array('COshDeliveryHandler', 'getPropData'),
            "onSaleDeliveryRestrictionsClassNamesBuildList" => array("COshDeliveryHandler", "addCustomRestrictions"),
            "onSaleDeliveryExtraServicesClassNamesBuildList" => array("COshDeliveryHandler", "addCustomExtraServices"),
        ),
//        "main" => array(
//            "OnEpilog" => array("COshDeliveryHandler", "onEpilog")
//        )
    );

    function __construct() {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("OSH_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("OSH_MODULE_DESC");

        $this->PARTNER_NAME = GetMessage("OSH_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("OSH_PARTNER_URI");
        $this->MODULE_CSS = "/bitrix/modules/" . $this->MODULE_ID . "/osh_admin.css";
    }

    function InstallEvents() {
        $eventManager = EventManager::getInstance();
        foreach($this->arHandlers as $moduleTo => $arEvents){
            foreach($arEvents as $eventName => $eventValues){
                $className = $eventValues[0];
                $funcName = $eventValues[1];
                $eventManager->registerEventHandler($moduleTo,$eventName,$this->MODULE_ID,$className,$funcName);
            }
        }
        return true;
    }

    function UnInstallEvents() {
        COption::RemoveOption($this->MODULE_ID);
        $eventManager = EventManager::getInstance();
        foreach($this->arHandlers as $moduleTo => $arEvents){
            foreach($arEvents as $eventName => $eventValues){
                $className = $eventValues[0];
                $funcName = $eventValues[1];
                $eventManager->unRegisterEventHandler($moduleTo,$eventName,$this->MODULE_ID,$className,$funcName);
            }
        }
        return true;
    }

    function InstallFiles() {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/tools", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/" . $this->MODULE_ID, true, true);
//        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/" . $this->MODULE_ID, true, true);
//        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
//        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/gadgets", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/gadgets/" . $this->MODULE_ID, true, true);

//        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/admin')) {
//            if ($dir = opendir($p)) {
//                while (false !== $item = readdir($dir)) {
//                    if ($item == '..' || $item == '.' || $item == 'menu.php')
//                        continue;
//                    file_put_contents($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/' . $item . '");?' . '>');
//                }
//                closedir($dir);
//            }
//        }
        return true;
    }

    function UnInstallFiles() {
        DeleteDirFilesEx("/bitrix/css/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/tools/" . $this->MODULE_ID . "/");
//        DeleteDirFilesEx("/bitrix/images/" . $this->MODULE_ID . "/");
//        DeleteDirFilesEx("/bitrix/gadgets/" . $this->MODULE_ID . "/");
//        DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID . "/"); //icons
//        DeleteDirFilesEx("/bitrix/themes/.default/start_menu/" . $this->MODULE_ID . "/"); //start_menu

//        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/admin')) {
//            if ($dir = opendir($p)) {
//                while (false !== $item = readdir($dir)) {
//                    if ($item == '..' || $item == '.'){
//                        continue;
//                    }
//                    unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
//                }
//                closedir($dir);
//            }
//        }
        return true;
    }

    function DoInstall() {
        CModule::IncludeModule('sale');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/include/helper.php');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/options/Config.php');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/OshHandler.php');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/ProfileHandler.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/Logger.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/COshAPI.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/cache/Cache.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/cache/Module.php');

        $this->InstallFiles();

//        $arParams = [
//            "filter" => ["CLASS_NAME" => "%OshHandler", 'ACTIVE' => 'Y'],
//            "select" => ["CONFIG", "ID", 'CURRENCY']
//        ];
//        if($result = DST::getList($arParams)->fetch()){
//            $_SESSION['_OSH'] = $result["CONFIG"];
//        }else{
//            $_SESSION['_OSH'] = OshHandler::getDefaultConfigValues();
//        }

        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallEvents();

//        $this->createOshDeliveryIfNone(false, false);
//
//        unset($_SESSION['_OSH']);
    }

    function DoUninstall() {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

//    private function createOshDeliveryIfNone($isDirect = false, $forceActive = false){
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/OshHandler.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/COshAPI.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/options/Config.php');
//        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/Logger.php');
//
//        $arFields = array("PARENT_ID" => 0, 'NAME' => OshHandler::getClassTitle(),
//            'DESCRIPTION' => OshHandler::getClassDescription(), 'ACTIVE' => 'Y',
//            "CLASS_NAME" => '\Osh\Delivery\OshHandler', 'CURRENCY' => 'RUB',
//            'ALLOW_EDIT_SHIPMENT' => 'Y', 'CONFIG' => 'Y', 'SORT' => 100);
//        $arFields['CONFIG'] = OshHandler::getDefaultConfigValues($isDirect);
//        $arConfig = $arFields['CONFIG'];
//        $result = DST::add($arFields);
//        if($result->isSuccess()){
//            $parentId = $result->getId();
//            OshHandler::createAllProfiles($parentId, 'RUB', $isDirect, $forceActive);
//        }
//
//        if(!empty($parentId)){
//            $_SESSION['_OSH']["PARENT_ID"] = $parentId;
//            $arConfig['MAIN']['LENGTH_VALUE'] = $_SESSION['_OSH']['LENGTH_VALUE'];
//            $arConfig['MAIN']['WIDTH_VALUE'] = $_SESSION['_OSH']['WIDTH_VALUE'];
//            $arConfig['MAIN']['HEIGHT_VALUE'] = $_SESSION['_OSH']['HEIGHT_VALUE'];
//            $arConfig['MAIN']['WEIGHT_VALUE'] = $_SESSION['_OSH']['WEIGHT_VALUE'];
//            $arConfig['MAIN']['CALC_ALGORITM'] = $_SESSION['_OSH']['CALC_ALGORITM'];
//            DST::update($parentId,array("CONFIG" => $arConfig));
//        }
//    }
}