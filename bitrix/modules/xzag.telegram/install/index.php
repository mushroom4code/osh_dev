<?php

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

/**
 * Class xzag_telegram
 */
class xzag_telegram extends CModule // phpcs:ignore
{
    public $MODULE_ID = 'xzag.telegram';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $MODULE_GROUP_RIGHTS = 'Y';
    public $errors = false;

    public $minimumRequiredPHPVersion = '7.0';
    public $requiredExtensions = array(
        'curl',
        'mbstring',
        'json',
        'pcre',
        'xml'
    );

    /**
     * xzag_telegram constructor.
     */
    public function __construct()
    {
        $this->MODULE_NAME        = Loc::getMessage('XZAG_TELEGRAM_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('XZAG_TELEGRAM_MODULE_DESCRIPTION');
        $this->PARTNER_NAME       = 'xzag';
        $this->PARTNER_URI        = 'https://xzag.ru/bitrix/';

        $arModuleVersion = array();

        $versionFile = __DIR__ . DIRECTORY_SEPARATOR . 'version.php';
        if (file_exists($versionFile) && is_readable($versionFile)) {
            include $versionFile;
        }

        $this->MODULE_VERSION = isset($arModuleVersion['VERSION']) ? $arModuleVersion['VERSION'] : null;
        $this->MODULE_VERSION_DATE = isset($arModuleVersion['VERSION_DATE']) ? $arModuleVersion['VERSION_DATE'] : null;
    }

    /**
     * @return bool
     */
    public function checkRequirements()
    {
        global $APPLICATION;
        $errors = array();

        if (phpversion() < $this->minimumRequiredPHPVersion) {
            $errors[] = Loc::getMessage('XZAG_TELEGRAM_PHP_VERSION_EXCEPTION', array(
                '{VERSION}' => phpversion(),
                '{REQUIRED}' => $this->minimumRequiredPHPVersion
            ));
        }

        foreach ($this->requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = Loc::getMessage('XZAG_TELEGRAM_MISSING_EXTENSION_EXCEPTION', array(
                    '{EXTENSION}' => $extension,
                ));
            }
        }

        $filenames = array(
            Loader::getDocumentRoot() . DIRECTORY_SEPARATOR . 'bitrix' . DIRECTORY_SEPARATOR . 'tools'
        );
        foreach ($filenames as $filename) {
            if (!is_writable($filename)) {
                $errors[] = Loc::getMessage('XZAG_TELEGRAM_FILE_PERMISSION_EXCEPTION', array(
                    '{FILENAME}' => $filename
                ));
            }
        }

        if ($errors) {
            $APPLICATION->ThrowException(implode('<br />', $errors));
            return false;
        }

        return true;
    }

    /**
     * @return bool|void
     */
    public function DoInstall() // phpcs:ignore
    {
        global $APPLICATION;

        if (!$this->checkRequirements()) {
            return false;
        }

        try {
            if (!$this->installFiles()) {
                throw new Exception(Loc::getMessage('XZAG_TELEGRAM_FILE_PERMISSION_GENERAL_EXCEPTION'));
            }

            $this->installDb();
            $this->installEvents();
        } catch (Exception $e) {
            $APPLICATION->ThrowException($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     */
    public function DoUninstall() // phpcs:ignore
    {
        global $APPLICATION;

        try {
            if (!$this->uninstallFiles()) {
                throw new Exception(Loc::getMessage('XZAG_TELEGRAM_FILE_PERMISSION_GENERAL_EXCEPTION'));
            }

            $this->uninstallEvents();
            $this->uninstallDb();
        } catch (Exception $e) {
            $APPLICATION->ThrowException($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @return bool|void
     */
    public function installDb()
    {
        ModuleManager::registerModule($this->MODULE_ID);
    }

    /**
     * @throws ArgumentNullException
     */
    public function uninstallDb()
    {
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * @return bool
     */
    public function installFiles()
    {
        return CopyDirFiles(
            __DIR__ . DIRECTORY_SEPARATOR . 'tools',
            Loader::getDocumentRoot() . DIRECTORY_SEPARATOR . 'bitrix' . DIRECTORY_SEPARATOR . 'tools',
            true,
            true
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function uninstallFiles()
    {
        $path = 'bitrix' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . $this->MODULE_ID;
        if (!is_writable(Loader::getDocumentRoot() . DIRECTORY_SEPARATOR . $path)) {
            throw new Exception(Loc::getMessage('XZAG_TELEGRAM_FILE_PERMISSION_EXCEPTION', array(
                '{FILENAME}' => Loader::getDocumentRoot() . DIRECTORY_SEPARATOR . $path
            )));
        }
        return DeleteDirFilesEx($path);
    }

    /**
     * @return array
     */
    private function getModuleEvents()
    {
        return array(
            'sale' => array(
                'Xzag\\Telegram\\Event\\SaleOrderCreatedEvent' => 'OnSaleOrderSaved',
                'Xzag\\Telegram\\Event\\SaleOrderPayedEvent' => 'OnSaleOrderSaved'
            ),
            'main' => array(
                'Xzag\\Telegram\\Event\\MainUserRegisteredEvent' => 'OnAfterUserRegister',
                'Xzag\\Telegram\\Event\\MainUserAddEvent' => 'OnAfterUserAdd'
            ),
            'form' => array(
                'Xzag\\Telegram\\Event\\FormResultCreatedEvent' => 'OnAfterResultAdd'
            )
        );
    }

    /**
     * @param bool $register
     */
    private function toggleEventHandler($register = true)
    {
        $eventManager = EventManager::getInstance();
        $moduleEvents = $this->getModuleEvents();
        foreach ($moduleEvents as $module => $events) {
            foreach ($events as $handler => $event) {
                if ($register) {
                    $eventManager->registerEventHandler(
                        $module,
                        $event,
                        $this->MODULE_ID,
                        $handler,
                        'handle'
                    );
                } else {
                    $eventManager->unRegisterEventHandler(
                        $module,
                        $event,
                        $this->MODULE_ID,
                        $handler,
                        'handle'
                    );
                }
            }
        }
    }

    /**
     *
     */
    public function installEvents()
    {
        $this->toggleEventHandler();
    }

    /**
     *
     */
    public function uninstallEvents()
    {
        $this->toggleEventHandler(false);
    }
}
