<?
global $MESS;
IncludeModuleLangFile(__FILE__);

class webdebug_utilities extends CModule {
	var $MODULE_ID = 'webdebug.utilities';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $Errors;

	function __construct() {
		$arModuleVersion = array();
		include(dirname(__FILE__).'/version.php');
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->PARTNER_NAME = GetMessage('WDU_PARTNER_NAME');
		$this->PARTNER_URI = GetMessage('WDU_PARTNER_URI');
		$this->MODULE_NAME = GetMessage('WDU_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('WDU_MODULE_DESCR');
	}

	function InstallDB($arParams = array()) {
		global $DBType, $APPLICATION, $DB, $USER;
		$this->Errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.ToLower($DBType).'/install.sql');
		if ($this->Errors !== false) {
			$APPLICATION->ThrowException(implode("", $this->Errors));
			return false;
		}
		// Install FastSQL
		$arFastSQL = array(
			"SHOW TABLES;",
			"SELECT * FROM `b_event` ORDER BY `ID` DESC;",
			"SELECT COUNT(`ID`) FROM `b_event` WHERE `SUCCESS_EXEC`='N' ORDER BY `ID` DESC;",
			"SELECT `ID`,`C_FIELDS`,`SUCCESS_EXEC` FROM `b_event` ORDER BY `ID` DESC;",
			"SELECT * FROM `b_option`;",
			"SELECT `ID`,`LOGIN`,`ACTIVE`,`NAME`,`LAST_NAME`,`SECOND_NAME`,`EMAIL` FROM `b_user`;",
			"SELECT * FROM `b_module_to_module`;",
			"SELECT table_name AS table_name, engine, ROUND(data_length/1024/1024,2) AS total_size_mb, table_rows FROM information_schema.tables WHERE table_schema=DATABASE() ORDER BY total_size_mb DESC;"
		);
		$Sort = 0;
		$UserID = $USER->GetID();
		foreach($arFastSQL as $FastSQL) {
			$Sort += 10;
			$FastSQL = $DB->ForSQL($FastSQL);
			$SQL = "INSERT INTO `wdu_fastsql` (`ACTIVE`,`SORT`,`QUERY`,`USER_ID`) VALUES('Y','{$Sort}','{$FastSQL}','{$UserID}');";
			$DB->Query($SQL);
		}
		return true;
	}

	function UnInstallDB() {
		global $DB, $DBType, $APPLICATION;
		$this->Errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/".ToLower($DBType)."/uninstall.sql");
		if ($this->Errors !== false) {
			$APPLICATION->ThrowException(implode("", $this->Errors));
			return false;
		}
		return true;
	}
	
	function InstallFiles() {
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/themes/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/js/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		return true;
	}
	
	function UnInstallFiles($SaveTemplate=true) {
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/.default/');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/js/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/components/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components');
		return true;
	}

	function DoInstall() {
		if (!check_bitrix_sessid()) return false;
		RegisterModule($this->MODULE_ID);
		$this->InstallDB();
		$this->InstallFiles();
		require_once(__DIR__.'/hotkeys_install.php');
		#
		$this->registerHandler('main', 'onPageStart');
		$this->registerHandler('main', 'onBeforeProlog');
		$this->registerHandler('main', 'onProlog');
		$this->registerHandler('main', 'onEpilog');
		$this->registerHandler('main', 'onAfterEpilog');
		$this->registerHandler('main', 'onBeforeEndBufferContent');
		$this->registerHandler('main', 'onEndBufferContent');
		$this->registerHandler('main', 'onAdminContextMenuShow');
		$this->registerHandler('main', 'onAdminListDisplay');
		$this->registerHandler('main', 'onAdminTabControlBegin');
		$this->registerHandler('main', 'onBuildGlobalMenu', 999999999);
		$this->registerHandler('main', 'onBeforeLocalRedirect');
		$this->registerHandler('main', 'onLocalRedirect');
		$this->registerHandler('main', 'onModuleUpdate');
		$this->registerHandler('main', 'onFileSave');
		$this->registerHandler('main', 'onAfterFileSave');
		$this->registerHandler('main', 'onGetFileSrc');
		$this->registerHandler('main', 'onFileDelete');
		$this->registerHandler('main', 'onBeforeEventAdd');
		$this->registerHandler('main', 'onBeforeEventSend');
		$this->registerHandler('main', 'onBeforeMailSend');
		$this->registerHandler('main', 'onPanelCreate');
		#
		return true;
	}

	function DoUninstall() {
		global $DB;
		if (!check_bitrix_sessid()) return false;
		COption::RemoveOption($this->MODULE_ID);
		#
		$this->unregisterHandler('main', 'onPageStart');
		$this->unregisterHandler('main', 'onBeforeProlog');
		$this->unregisterHandler('main', 'onProlog');
		$this->unregisterHandler('main', 'onEpilog');
		$this->unregisterHandler('main', 'onAfterEpilog');
		$this->unregisterHandler('main', 'onBeforeEndBufferContent');
		$this->unregisterHandler('main', 'onEndBufferContent');
		$this->unregisterHandler('main', 'onAdminContextMenuShow');
		$this->unregisterHandler('main', 'onAdminListDisplay');
		$this->unregisterHandler('main', 'onAdminTabControlBegin');
		$this->unregisterHandler('main', 'onBuildGlobalMenu');
		$this->unregisterHandler('main', 'onBeforeLocalRedirect');
		$this->unregisterHandler('main', 'onLocalRedirect');
		$this->unregisterHandler('main', 'onModuleUpdate');
		$this->unregisterHandler('main', 'onFileSave');
		$this->unregisterHandler('main', 'onAfterFileSave');
		$this->unregisterHandler('main', 'onGetFileSrc');
		$this->unregisterHandler('main', 'onFileDelete');
		$this->unregisterHandler('main', 'onBeforeEventAdd');
		$this->unregisterHandler('main', 'onBeforeEventSend');
		$this->unregisterHandler('main', 'onBeforeMailSend');
		$this->unregisterHandler('main', 'onPanelCreate');
		#
		require_once(__DIR__.'/hotkeys_uninstall.php');
		$this->UnInstallFiles();
		$this->UnInstallDB();
		UnRegisterModule($this->MODULE_ID);
		return true;
	}
	
	protected function registerHandler($strFromModuleId, $strEvent, $intSort=100){
		\Bitrix\Main\EventManager::getInstance()->registerEventHandler($strFromModuleId, $strEvent, $this->MODULE_ID, 
			'\WD\Utilities\EventHandler', $strEvent, $intSort);
	}
	
	protected function unregisterHandler($strFromModuleId, $strEvent){
		\Bitrix\Main\EventManager::getInstance()->unregisterEventHandler($strFromModuleId, $strEvent, $this->MODULE_ID, 
			'\WD\Utilities\EventHandler', $strEvent);
	}
	
}
?>