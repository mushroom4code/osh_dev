<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("solverweb_sitemap")) return;

Class solverweb_sitemap extends CModule {
    var $MODULE_ID = "solverweb.sitemap";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct() {
        include(__DIR__ ."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = '1.0.0';
            $this->MODULE_VERSION_DATE = '2019-05-23 15:00:00';
        }

        $this->PARTNER_NAME = GetMessage("SITEMAP_SOLVERWEB");
        $this->PARTNER_URI = 'https://solverweb.ru';
        $this->MODULE_NAME = GetMessage("SITEMAP_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SITEMAP_MODULE_DESCRIPTION");
    }

    function DoInstall() {
        $this->InstallFiles();
        $this->InstallDB();
        $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage("SITEMAP_INSTALL_TITLE"), __DIR__ . '/step1.php');
    }

    function InstallFiles() {
		global $DOCUMENT_ROOT;
		CopyDirFiles(__DIR__ . "/admin", $DOCUMENT_ROOT."/bitrix/admin", true, true);
		CopyDirFiles(__DIR__ . "/images", $DOCUMENT_ROOT."/bitrix/images", true, true);
		CopyDirFiles(__DIR__ . "/themes", $DOCUMENT_ROOT."/bitrix/themes", true, true);
    }

    function InstallDB() {
        global $DB, $APPLICATION;

        $this->errors = false;
        if(!$DB->Query("SELECT 'x' FROM sw_sitemap_xml", true))
            $this->errors = $DB->RunSQLBatch(__DIR__ . "/db/".strtolower($DB->type)."/install.sql");

        if($this->errors !== false) {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }

        RegisterModule($this->MODULE_ID);
    }
	
    function DoUninstall() {
        global $DOCUMENT_ROOT, $APPLICATION, $step;
        $step = IntVal($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(GetMessage("SITEMAP_UNINSTALL_TITLE"), __DIR__ . "/unstep1.php");
        } else if ($step == 2) {
            $this->UnInstallDB(array(
                "savedata" => $_REQUEST["savedata"],
            ));
            $this->UnInstallFiles();
            $APPLICATION->IncludeAdminFile(GetMessage("SITEMAP_UNINSTALL_TITLE"), __DIR__ . "/unstep2.php");
        }
    }

    function UnInstallDB($arParams = Array()) {
        global $APPLICATION, $DB;
        $this->errors = false;
        if (!$arParams['savedata']) {
            $this->errors = $DB->RunSQLBatch(__DIR__ . "/db/".strtolower($DB->type)."/uninstall.sql");
        }

        if(!empty($this->errors)) {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }
		
		CAgent::RemoveModuleAgents($this->MODULE_ID);
        UnRegisterModule($this->MODULE_ID);
    }

    function UnInstallFiles($arParams = array()) {
        global $DOCUMENT_ROOT;
        DeleteDirFiles(__DIR__ ."/admin", $DOCUMENT_ROOT.BX_ROOT."/admin");
        DeleteDirFiles(__DIR__ ."/images", $DOCUMENT_ROOT.BX_ROOT."/images");
        DeleteDirFiles(__DIR__ . "/themes/.default/", $DOCUMENT_ROOT.BX_ROOT."/themes/.default");
    }
}