<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists("ctweb_smsauth"))
    return;

Class ctweb_smsauth extends CModule
{
    var $MODULE_ID = "ctweb.smsauth";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = "";

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME         = GetMessage("CTWEB_SMSAUTH_INSTALL_NAME");
        $this->MODULE_DESCRIPTION  = GetMessage("CTWEB_SMSAUTH_INSTALL_DESCRIPTION");
        $this->PARTNER_NAME        = GetMessage("CTWEB_SMSAUTH_PARTNER_NAME");
        $this->PARTNER_URI         = GetMessage("CTWEB_SMSAUTH_PARTNER_URI");
    }

    function InstallDB()
    {
        global $APPLICATION;
        $this->errors = false;

        ModuleManager::registerModule($this->MODULE_ID);

		COption::SetOptionString($this->MODULE_ID, "CSR_PHONE_FIELD", "PERSONAL_PHONE");
        return true;
    }

    function UnInstallDB()
    {
        global $APPLICATION;
        $this->errors = false;

        COption::RemoveOption($this->MODULE_ID);

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {	
    	CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/assets/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID . "/", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/assets/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . $this->MODULE_ID . "/", true, true);
        
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }
       
        return true;
    }

    function UnInstallFiles()
    {

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item))
                        continue;

                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/components/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }

        return true;
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
        $GLOBALS["errors"] = $this->errors;

        $GLOBALS["APPLICATION"]->IncludeAdminFile(Loc::getMessage("CTWEB_SMSAUTH_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
    }

    function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
    }
}

?>
