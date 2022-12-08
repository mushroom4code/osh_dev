<?
#################################################
#        Company developer: IPOL
#        Developer: Nikta Egorov
#        Site: http://www.ipol.com
#        E-mail: om-sv2@mail.ru
#        Copyright (c) 2006-2017 IPOL
#################################################
?>
<?
IncludeModuleLangFile(__FILE__);

if(class_exists("ipol_fivepost"))
    return;

Class ipol_fivepost extends CModule{
    var $MODULE_ID = "ipol.fivepost";
    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "N";
    var $errors;

    function __construct()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = GetMessage("IPOL_FIVEPOST_INSTALL_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("IPOL_FIVEPOST_INSTALL_DESCRIPTION");

        $this->PARTNER_NAME = "Ipol";
        $this->PARTNER_URI = "http://www.ipol.ru";
    }

    /**
     * @return array
     * список таблиц для установки вида название таблицы -> sql+код (имя файла - sql+код.php)
     */
    protected function getDB(){
        return array(
                'ipol_fivepost_orders'    => 'Orders',
                'ipol_fivepost_points'    => 'Points',
                'ipol_fivepost_rates'     => 'Rates',
                'ipol_fivepost_locations' => 'Locations'
        );
    }

    function InstallDB(){
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;

        $arDB = $this->getDB();

        foreach($arDB as $name => $path)
            if(!$DB->Query("SELECT 'x' FROM ".$name, true)){
                $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/install".$path.".sql");
                if($this->errors !== false){
                    $APPLICATION->ThrowException(implode("", $this->errors));
                    return false;
                }
            }

        return true;
    }

    function UnInstallDB(){
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;

        $arDB = $this->getDB();

        foreach($arDB as $name => $path){
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/uninstall".$path.".sql");
            if(!empty($this->errors)){
                $APPLICATION->ThrowException(implode("", $this->errors));
                return false;
            }
        }

        return true;
    }

    function InstallFiles(){
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".$this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);

        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.$this->MODULE_ID) && file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/tools')){
            $existFiles  = scandir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/" . $this->MODULE_ID);
            $copiedFiles = scandir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/".$this->MODULE_ID.'/install/tools');
            foreach ($copiedFiles as $file){
                if(!in_array($file,$existFiles) && strlen($file) > 3){
                    copy($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID.'/install/tools/'.$file,$_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/".$this->MODULE_ID.'/'.$file);
                }
            }
        } else {
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/".$this->MODULE_ID, true, true);
        }

        //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
        return true;
    }
    function UnInstallFiles(){
        DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID);
//        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.$this->MODULE_ID))
//            DeleteDirFilesEx("/bitrix/tools/".$this->MODULE_ID);
        DeleteDirFilesEx("/bitrix/images/".$this->MODULE_ID);

        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/")) {
            $adminFiles = scandir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/");
            foreach ($adminFiles as $file){
                if(strlen($file) > 2 && strpos($file,'.')){
                    unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".$file);
                }
            }
        }
        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/")) {
            $adminFiles = scandir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/.default/");
            foreach ($adminFiles as $file){
                if(strlen($file) > 2 && strpos($file,'.')){
                     unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default/".$file);
                }
            }
        }

        DeleteDirFilesEx("/upload/".$this->MODULE_ID);
        return true;
    }

    function DoInstall(){
        global $DB, $APPLICATION, $step;
        $this->errors = false;

        // если надо не давать устанавливать модуль - вот тут этим делом занимаемся
        if(!CheckVersion($this->getSaleVersion(),'16.0.0'))
        {
            $GLOBALS['IPOL_FIVEPOST_LBL_INSTALL_ERROR'] = GetMessage('IPOL_FIVEPOST_BADSALEVERSION')." ".$this->getSaleVersion().".";
        }
        if(!function_exists('curl_init'))
        {
            $GLOBALS['IPOL_FIVEPOST_LBL_INSTALL_ERROR'] = GetMessage('IPOL_FIVEPOST_NOCURL')." ".$this->getSaleVersion().".";
        }

        if($GLOBALS['IPOL_FIVEPOST_LBL_INSTALL_ERROR'])
        {
            $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage('IPOL_FIVEPOST_INSTALL_ERROR_TITLE'), __DIR__ .'/error.php');

            return;
        }

        $this->InstallDB();
        $this->InstallFiles();

        RegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(GetMessage("IPOL_FIVEPOST_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
    }

    function DoUninstall(){
        global $DB, $APPLICATION, $step;
        $this->errors = false;

        if($_REQUEST['step'] < 2){
            $this->ShowDataSaveForm();
        }else {
            \cmodule::includemodule($this->MODULE_ID);

            // если надо сохранить таблицы
            if(!array_key_exists('IPOL_FIVEPOST_savedata',$_REQUEST) || $_REQUEST['IPOL_FIVEPOST_savedata'] != 'Y') {
                \COption::setOptionString($this->MODULE_ID,'sync_data_completed','N');
                $this->UnInstallDB();
            }

            $this->UnInstallFiles();

            \Ipol\Fivepost\AuthHandler::delogin();

            UnRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(GetMessage("IPOL_FIVEPOST_DEL"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep1.php");
        }
    }

    /**
     * сохранение таблиц модуля
     */
    private function ShowDataSaveForm() {
        $keys = array_keys($GLOBALS);
        for ($i = 0; $i < count($keys); $i++) {
            if ($keys[$i] != 'i' && $keys[$i] != 'GLOBALS' && $keys[$i] != 'strTitle' && $keys[$i] != 'filepath') {
                global ${$keys[$i]};
            }
        }

        $APPLICATION->SetTitle(GetMessage('IPOL_FIVEPOST_DEL'));
        include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
        ?>
        <form action="<?= $APPLICATION->GetCurPage() ?>" method="get">
            <?= bitrix_sessid_post();?>
            <input type="hidden" name="lang" value="<?= LANG ?>" />
            <input type="hidden" name="id" value="<?= $this->MODULE_ID ?>" />
            <input type="hidden" name="uninstall" value="Y" />
            <input type="hidden" name="step" value="2" />
            <? \CAdminMessage::ShowMessage(GetMessage('IPOL_FIVEPOST_PRESERVE_TABLES')) ?>
            <p><?echo GetMessage('MOD_UNINST_SAVE')?></p>
            <p><input type="checkbox" name="IPOL_FIVEPOST_savedata" id="IPOL_FIVEPOST_savedata" value="Y" checked="checked" /><label for="savedata"><?echo GetMessage('MOD_UNINST_SAVE_TABLES')?></label><br></p>
            <input type="submit" name="inst" value="<?echo GetMessage('MOD_UNINST_DEL');?>" />
        </form>
        <?
        include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
        die();
    }

    private function getSaleVersion()
    {
        include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/install/version.php');
        return $arModuleVersion['VERSION'];
    }
}
?>
