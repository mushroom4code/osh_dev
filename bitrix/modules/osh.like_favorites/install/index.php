<?php

class osh_like_favorites extends CModule
{

    var $MODULE_ID = "osh.like_favorites";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_PATH = 'osh.like_favorites';


    function __construct()
    {
        $ModuleVersion = array();

        include(__DIR__ . '/version.php');

        $this->MODULE_VERSION = $ModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $ModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = "Лайки-Избранное – модуль с компонентом";
        $this->MODULE_DESCRIPTION = "После установки вы сможете пользоваться компонентом Лайки-Избранное";
    }

    function InstallFiles(): bool
    {
        mkdir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/$this->MODULE_PATH/", 0777);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/$this->MODULE_PATH/install/components/$this->MODULE_PATH/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/bitrix/$this->MODULE_PATH/", true, true, false);
        mkdir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/Oshisha/components/bitrix/$this->MODULE_PATH/", 0777);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/$this->MODULE_PATH/install/components/$this->MODULE_PATH/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/Oshisha/components/bitrix/$this->MODULE_PATH/", true,
            true, false);
//        TODO написать добавление строки в файл  - автозагрузка класса в инит файл
//        $data = 'CModule::AddAutoloadClasses("",
//        array("\Ent_like\DataBase_like" => "/bitrix/modules/Like/lib/DataBase_like.php"));';
//        file_put_contents('bitrix/php_interface/init.php', $data, FILE_APPEND);
        return true;
    }

    function UnInstallFiles(): bool
    {
        DeleteDirFilesEx("/bitrix/templates/Oshisha/components/bitrix/$this->MODULE_PATH/");
        DeleteDirFilesEx("/bitrix/components/bitrix/$this->MODULE_PATH/");
        return true;
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();
        RegisterModule("$this->MODULE_PATH");
        $APPLICATION->IncludeAdminFile("Установка модуля Лайки-Избранное",
            $DOCUMENT_ROOT . "/modules/$this->MODULE_PATH/install/step.php");
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        UnRegisterModule("$this->MODULE_PATH");
        $this->UnInstallEvents();
        $this->UnInstallDB();
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля Лайки-Избранное",
            $DOCUMENT_ROOT . "/modules/$this->MODULE_PATH/install/unstep.php");

    }

    function InstallDB()
    {
        global $DB;

        $sql = "CREATE TABLE IF NOT EXISTS ent_like_favorite(
                F_USER_ID      INT(255) NOT NULL,
                I_BLOCK_ID INT(255)  NULL,
                LIKE_USER INT(1) DEFAULT 0,
                FAVORITE INT(1) DEFAULT 0
              )";
        $indexes = "CREATE INDEX ent_like_favorite_I_BLOCK_ID_LIKE_FAVORITE_F_USER_ID_index
	ON ent_like_favorite (I_BLOCK_ID, LIKE_USER, FAVORITE, F_USER_ID);";
        $DB->Query($sql);
        $DB->Query($indexes);
    }

    function UnInstallDB()
    {
        global $DB;

        $sql = "DROP TABLE IF EXISTS ent_like_favorite";
        $DB->Query($sql);
    }
}


