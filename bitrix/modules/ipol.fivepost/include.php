<?
namespace Ipol\Fivepost;

use \Bitrix\Main\Loader;

define('IPOL_FIVEPOST', 'ipol.fivepost'); // Use this if module code needed
define('IPOL_FIVEPOST_LBL', 'IPOL_FIVEPOST_');

IncludeModuleLangFile(__FILE__);

Loader::includeModule('sale'); // Because no delivery without sales

// Module classes autoloader
spl_autoload_register(function($className){
    if (strpos($className, __NAMESPACE__) === 0)
    {
        $classPath = implode(DIRECTORY_SEPARATOR, explode('\\', substr($className,14)));

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "classes".DIRECTORY_SEPARATOR."lib" . DIRECTORY_SEPARATOR . $classPath . ".php";

        if (is_readable($filename) && file_exists($filename))
            require_once $filename;
    }
});

// Main module classes
Loader::registerAutoLoadClasses(IPOL_FIVEPOST, array(
    // General
    '\Ipol\Fivepost\AbstractGeneral'   => '/classes/general/AbstractGeneral.php',
    '\Ipol\Fivepost\BarcodeHandler'    => '/classes/general/BarcodeHandler.php',
    '\Ipol\Fivepost\SubscribeHandler'  => '/classes/general/SubscribeHandler.php',
    '\Ipol\Fivepost\Option'            => '/classes/general/Option.php',
    '\Ipol\Fivepost\OptionsHandler'    => '/classes/general/OptionsHandler.php',
    '\Ipol\Fivepost\OrderHandler'      => '/classes/general/OrderHandler.php',
    '\Ipol\Fivepost\OrderPropsHandler' => '/classes/general/OrderPropsHandler.php',
    '\Ipol\Fivepost\AgentHandler'      => '/classes/general/AgentHandler.php',
    '\Ipol\Fivepost\AuthHandler'       => '/classes/general/AuthHandler.php',
    '\Ipol\Fivepost\Warhouses'         => '/classes/general/Warhouses.php',
    '\Ipol\Fivepost\StatusHandler'     => '/classes/general/StatusHandler.php',

    '\Ipol\Fivepost\DeliveryHandler'   => '/classes/general/DeliveryHandler.php',
    '\Ipol\Fivepost\ProfileHandler'    => '/classes/general/ProfileHandler.php',
    '\Ipol\Fivepost\PvzWidgetHandler'  => '/classes/general/PvzWidgetHandler.php',

    '\Ipol\Fivepost\PointsHandler'     => '/classes/general/PointsHandler.php',
    '\Ipol\Fivepost\LocationsHandler'  => '/classes/general/LocationsHandler.php',
    '\Ipol\Fivepost\SyncHandler'       => '/classes/general/SyncHandler.php',

    // DB
    '\Ipol\Fivepost\OrdersTable'       => '/classes/db/OrdersTable.php',
    '\Ipol\Fivepost\PointsTable'       => '/classes/db/PointsTable.php',
    '\Ipol\Fivepost\RatesTable'        => '/classes/db/RatesTable.php',
    '\Ipol\Fivepost\LocationsTable'    => '/classes/db/LocationsTable.php',
));