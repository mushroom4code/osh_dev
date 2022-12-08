<?php

global $MESS;

IncludeModuleLangFile(__FILE__);

if (class_exists('pickpoint_deliveryservice')) {
    return;
}

class pickpoint_deliveryservice extends CModule
{
    var $MODULE_ID = "pickpoint.deliveryservice";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $arModuleVersion = array();

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include $path.'/version.php';

        $this->PARTNER_NAME = 'PickPoint';
        $this->PARTNER_URI = 'http://pickpoint.ru/';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        } else {
            $this->MODULE_VERSION = ST_VERSION;
            $this->MODULE_VERSION_DATE = ST_VERSION_DATE;
        }

        $this->MODULE_NAME = GetMessage('PP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('PP_MODULE_DESCRIPTION');
    }

    public function DoInstall()
    {
		global $APPLICATION;
		
        $this->InstallFiles();
        $this->InstallDB();

        RegisterModuleDependences(
            'sale',
            'OnSaleComponentOrderOneStepComplete',
            $this->MODULE_ID,
            'CPickpoint',
            'OnOrderAddV15'
        );
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler(
            'sale',
            'OnSaleOrderSaved',
            $this->MODULE_ID,
            'CPickpoint',
            'OnSaleOrderSaved'
        )
        ;
        RegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, 'CPickpoint', 'CheckRequest');
        RegisterModuleDependences(
            'sale',
            'OnSaleComponentOrderOneStepDelivery',
            $this->MODULE_ID,
            'CPickpoint',
            'OnSCOrderOneStepDeliveryHandler'
        );
        RegisterModuleDependences(
            'sale',
            'OnSaleComponentOrderOneStepPersonType',
            $this->MODULE_ID,
            'CPickpoint',
            'addPickpointJs'
        );

        RegisterModule('pickpoint.deliveryservice');

        $GLOBALS['errors'] = $this->errors;
		
		$APPLICATION->IncludeAdminFile(GetMessage("PP_DS_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;
        $step = intval($step);

        if ($step < 2) {
            //COption::RemoveOption($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(
                GetMessage('ST_INSTALL_TITLE'),
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/unstep1.php'
            );
        } elseif ($step == 2) {
            $this->UnInstallDB(array('savedata' => $_REQUEST['savedata']));
            $this->UnInstallFiles();
            $GLOBALS['errors'] = $this->errors;

            UnRegisterModuleDependences('sale', 'OnOrderAdd', $this->MODULE_ID, 'CPickpoint', 'OnOrderAdd');
            UnRegisterModuleDependences(
                'sale',
                'OnSaleComponentOrderOneStepComplete',
                $this->MODULE_ID,
                'CPickpoint',
                'OnOrderAddV15'
            );
            \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
                'sale',
                'OnSaleOrderSaved',
                $this->MODULE_ID,
                'CPickpoint',
                'OnSaleOrderSaved'
            )
            ;
            UnRegisterModuleDependences('main', 'OnPageStart', $this->MODULE_ID, 'CPickpoint', 'CheckRequest');
            UnRegisterModuleDependences(
                'sale',
                'OnSaleComponentOrderOneStepDelivery',
                $this->MODULE_ID,
                'CPickpoint',
                'OnSCOrderOneStepDeliveryHandler'
            );
            UnRegisterModuleDependences(
                'sale',
                'OnSaleComponentOrderOneStepPersonType',
                $this->MODULE_ID,
                'CPickpoint',
                'addPickpointJs'
            );

            UnRegisterModule('pickpoint.deliveryservice');

            $APPLICATION->IncludeAdminFile(
                GetMessage('ST_INSTALL_TITLE'),
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/unstep2.php'
            );
        }

        $GLOBALS['errors'] = $this->errors;
    }

    public function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/db/'.$DBType.'/install.sql');
		
		if ($this->errors === false)
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/db/'.$DBType.'/courier.sql');
		
		if ($this->errors === false)
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/db/'.$DBType.'/registry.sql');			
		
        if ($this->errors !== false) {
            $APPLICATION->ThrowException(implode('', $this->errors));
            return false;
        }
		
		CAgent::AddAgent('\PickPoint\DeliveryService\AgentHandler::refreshStatuses();', $this->MODULE_ID, "N", 900);
		CAgent::AddAgent('\PickPoint\DeliveryService\AgentHandler::deleteOldFiles();', $this->MODULE_ID, "N", 86400);
		CAgent::AddAgent('\PickPoint\DeliveryService\AgentHandler::deleteOldData();', $this->MODULE_ID, "N", 86400);

        return true;
    }

    public function UnInstallDB($arParams = array())
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        if (array_key_exists('savedata', $arParams) && $arParams['savedata'] != 'Y') {
            $this->errors = $DB->RunSQLBatch(
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/db/'.$DBType.'/uninstall.sql'
            );
            if ($this->errors !== false) {
                $APPLICATION->ThrowException(implode('', $this->errors));

                return false;
            }
        }
		
		CAgent::RemoveModuleAgents($this->MODULE_ID);

        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin',
            true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/images/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/pickpoint.deliveryservice/',
            true,
            true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/themes/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/',
            true,
            true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/js/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/',
            true,
            true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/delivery/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_delivery/',
            true,
            true
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/payment/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/pickpoint.deliveryservice/',
            true,
            true
        );
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/admin',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
        );
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/themes/.default/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/.default'
        );
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/install/delivery/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_delivery'
        );
        DeleteDirFilesEx('/bitrix/php_interface/include/sale_payment/pickpoint.deliveryservice/');
        DeleteDirFilesEx('/bitrix/themes/.default/icons/pickpoint.deliveryservice/');
        DeleteDirFilesEx('/bitrix/images/pickpoint.deliveryservice/');
        DeleteDirFilesEx('/bitrix/js/pickpoint.deliveryservice');
    }
}
