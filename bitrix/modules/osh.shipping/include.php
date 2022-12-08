<?
use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

//if (!function_exists('boolval')) {
//    function boolval($val) {
//        return (bool) $val;
//    }
//}

$moduleId = "osh.shipping";
//define("OSH_DELIVERY_YMAPS_URL","//api-maps.yandex.ru/2.1/?lang=ru_RU");
//define("OSH_DELIVERY_YMAPS_URL","");
//define("OSH_DELIVERY_FA_URL","https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css");

Loader::includeModule("sale");

Loader::registerAutoLoadClasses($moduleId, array(
//    '\Osh\Delivery\COshAPI' => "lib/COshAPI.php",
//    '\Osh\Delivery\YApi' => "lib/YApi.php",
//    '\Osh\Delivery\YApiPoint' => "lib/YApiPoint.php",
//    '\Osh\Delivery\YApiPolygon' => "lib/YApiPolygon.php",
//    '\Osh\Delivery\OshService' => 'lib/OshService.php',
    '\Osh\Delivery\OshHandler' => 'lib/OshHandler.php',
//    '\Osh\Delivery\Helpers\Calc' => 'lib/helpers/calc.php',
    '\Osh\Delivery\ProfileHandler' => 'lib/ProfileHandler.php',
//    '\Osh\Delivery\Restrictions\ExcludeLocation' => 'lib/restrictions/ExcludeLocation.php',
//    '\Osh\Delivery\Services\DateDelivery' => 'lib/services/DateDelivery.php',
//    '\Osh\Delivery\Services\TimeDelivery' => 'lib/services/TimeDelivery.php',
    '\Osh\Delivery\Options\Config' => 'lib/options/Config.php',
    '\Osh\Delivery\Options\Helper' => 'lib/options/Helper.php',
    '\Osh\Delivery\Cache\Cache' => 'lib/cache/Cache.php',
//    '\Osh\Delivery\Cache\Module' => 'lib/cache/Module.php',
//    '\Osh\Delivery\Logger' => 'lib/Logger.php',
//    '\Osh\Delivery\Agents' => 'lib/include/agents.php',
    '\Osh\Delivery\Helpers\Order' => 'lib/helpers/order.php',
//    'COshDeliveryHandler' => 'lib/include/handler.php',
//    'COshDeliveryHelper' => 'lib/include/helper.php'
));

include_once ($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/osh.shipping/lib/include/handler.php');
include_once ($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/osh.shipping/lib/include/helper.php');

if(!\CJSCore::IsExtRegistered('osh_pickup')){
    \CJSCore::RegisterExt(
        "osh_pickup",
        array(
            "js" => "/bitrix/js/{$moduleId}/pickup.js",
            "css" => "/bitrix/css/{$moduleId}/styles.css",
            "lang" => "/bitrix/modules/{$moduleId}/lang/".LANGUAGE_ID."/js/pickup.php",
            "rel" => Array("ajax","popup"),
            "skip_core" => false,
        )
    );
}
//function wfDump($var){
//    COshDeliveryHelper::shDump($var);
//}
//function wfDumpHid($var){
//    COshDeliveryHelper::shDump($var,true);
//}