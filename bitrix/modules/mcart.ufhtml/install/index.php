<?
IncludeModuleLangFile(__FILE__);

if (class_exists("mcart_ufhtml")) return;

class mcart_ufhtml extends CModule
{
    const MODULE_ID = "mcart.ufhtml";
    var $MODULE_ID = "mcart.ufhtml";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    var $errors;

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = GetMessage("ufhtml_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("ufhtml_MODULE_DESCRIPTION");

        $this->PARTNER_NAME = GetMessage("MCART_PARTNER_NAME");
        $this->PARTNER_URI  = "http://mcart.ru/";
    }

    function DoInstall()
    {
        if (!IsModuleInstalled(self::MODULE_ID)) {
            global $DOCUMENT_ROOT, $APPLICATION;
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
            $APPLICATION->IncludeAdminFile(GetMessage("ufhtml_STEP"), $DOCUMENT_ROOT . "/bitrix/modules/" . self::MODULE_ID . "/install/step.php");
        }
        return true;
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $APPLICATION->IncludeAdminFile(GetMessage("ufhtml_UNSTEP"), $DOCUMENT_ROOT . "/bitrix/modules/" . self::MODULE_ID . "/install/unstep.php");


        return true;
    }
    protected function getEventsArray()
    {
        return [
            ["main", "OnUserTypeBuildList", "CmcartUserFieldHtml", "GetUserTypeDescription"]
        ];
    }

    function InstallDB()
    {
        \Bitrix\Main\ModuleManager::RegisterModule(self::MODULE_ID);

        return true;
    }

    function UnInstallDB()
    {
        \Bitrix\Main\ModuleManager::UnRegisterModule(self::MODULE_ID);

        return true;
    }
    function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach ($this->getEventsArray() as $row) {
            list($module, $event_name, $class, $function, $sort) = $row;
            $eventManager->RegisterEventHandler($module, $event_name, $this->MODULE_ID, $class, $function, $sort);
        }
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach ($this->getEventsArray() as $row) {
            list($module, $event_name, $class, $function,) = $row;
            $eventManager->UnRegisterEventHandler($module, $event_name, $this->MODULE_ID, $class, $function);
        }
        return true;
    }

    function InstallFiles()
    {
        $dir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/mcart/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        \CopyDirFiles(__DIR__ . "/components/", $dir, true, true);

        return true;
    }
    function UnInstallFiles()
    {
        \DeleteDirFilesEx("/bitrix/components/mcart/html.field.field");

        return true;
    }
}
