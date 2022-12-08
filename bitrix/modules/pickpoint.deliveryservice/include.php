<?php
use \PickPoint\DeliveryService\Autoload;
use \PickPoint\DeliveryService\Option;

define('PICKPOINT_DELIVERYSERVICE', 'pickpoint.deliveryservice');
define('PICKPOINT_DELIVERYSERVICE_LBL', 'PICKPOINT_DELIVERYSERVICE_');

IncludeModuleLangFile(__FILE__);
global $APPLICATION;

if (!function_exists('CheckPickpointLicense')) {
    function CheckPickpointLicense($sIKN)
    {
        if (preg_match('#[0-9]{10}#', $sIKN)) {
            return true;
        }

        return false;
    }
}

global $DBType;
global $arOptions;

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/pickpoint.deliveryservice/lib/Helper.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/pickpoint.deliveryservice/lib/Sql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/pickpoint.deliveryservice/lib/Invoices.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/pickpoint.deliveryservice/lib/Request.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/pickpoint.deliveryservice/lib/OrderOptions.php';
define('PP_CSV_URL', 'http://www.pickpoint.ru/citys/cities.csv');
define('PP_ZONES_COUNT', 11);

$MODULE_ID = PICKPOINT_DELIVERYSERVICE;
if (!CModule::IncludeModule('sale')) {
    //	trigger_error("Currency is not installed");
    return false;
}
CModule::AddAutoloadClasses(
    $MODULE_ID,
    array(
        'CAllPickpoint' => 'mysql/pickpoint.php',
        'CPickpoint' => 'general/pickpoint.php',
    )
);

// New classes structure
\Bitrix\Main\Loader::registerAutoLoadClasses(PICKPOINT_DELIVERYSERVICE, array(		
	// Autoloader for new lib
	'\\PickPoint\\DeliveryService\\Autoload'         => '/classes/general/Autoload.php',	
	// General
	'\\PickPoint\\DeliveryService\\AbstractGeneral'  => '/classes/general/AbstractGeneral.php',	
	'\\PickPoint\\DeliveryService\\Option'           => '/classes/general/Option.php',	
	'\\PickPoint\\DeliveryService\\AgentHandler'     => '/classes/general/AgentHandler.php',	
	'\\PickPoint\\DeliveryService\\CourierHandler'   => '/classes/general/CourierHandler.php',	
	'\\PickPoint\\DeliveryService\\PrintHandler'     => '/classes/general/PrintHandler.php',	
	'\\PickPoint\\DeliveryService\\RegistryHandler'  => '/classes/general/RegistryHandler.php',	
	'\\PickPoint\\DeliveryService\\StatusHandler'    => '/classes/general/StatusHandler.php',	
	'\\PickPoint\\DeliveryService\\SubscribeHandler' => '/classes/general/SubscribeHandler.php',	
	// DB
	'\\PickPoint\\DeliveryService\\OrderTable'       => '/classes/db/OrderTable.php',
	'\\PickPoint\\DeliveryService\\CourierTable'     => '/classes/db/CourierTable.php',
	'\\PickPoint\\DeliveryService\\RegistryTable'    => '/classes/db/RegistryTable.php',
));

// Register autoloader
Autoload::register();

// If module included after OnPageStart
if (!isset($_SESSION['PICKPOINT'])) {
    CPickpoint::CheckRequest();
}

// Cities file update
$iTimestamp = COption::GetOptionInt($MODULE_ID, 'pp_city_download_timestamp', 0);
if (time() > $iTimestamp || !file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/cities.csv')) {
    CPickpoint::GetCitiesCSV();
}

// Legacy 
if (!Option::get('pp_service_types_all'))
	Option::set('pp_service_types_all', Option::getVariants('pp_service_types_all'), true);

// Available options 
$arOptions = array();

// Useful
$arOptions['OPTIONS']['pp_ikn_number'] = COption::GetOptionString($MODULE_ID, 'pp_ikn_number', '');
$arOptions['OPTIONS']['pp_api_login'] = COption::GetOptionString($MODULE_ID, 'pp_api_login');
$arOptions['OPTIONS']['pp_api_password'] = COption::GetOptionString($MODULE_ID, 'pp_api_password');
$arOptions['OPTIONS']['pp_enclosure'] = COption::GetOptionString($MODULE_ID, 'pp_enclosure', '');
$arOptions['OPTIONS']['pp_test_mode'] = COption::GetOptionString($MODULE_ID, 'pp_test_mode');

$arOptions['OPTIONS']['pp_service_types_all'] = COption::GetOptionString($MODULE_ID, 'pp_service_types_all');
$arOptions['OPTIONS']['pp_service_types_selected'] = COption::GetOptionString($MODULE_ID, 'pp_service_types_selected');

$arOptions['OPTIONS']['pp_term_inc'] = Option::get('pp_term_inc');
$arOptions['OPTIONS']['pp_postamat_picker'] = Option::get('pp_postamat_picker');

$arOptions['OPTIONS']['pp_add_info'] = COption::GetOptionString($MODULE_ID, 'pp_add_info', '1');
$arOptions['OPTIONS']['pp_order_phone'] = COption::GetOptionString($MODULE_ID, 'pp_order_phone', '0');
$arOptions['OPTIONS']['pp_city_location'] = COption::GetOptionString($MODULE_ID, 'pp_city_location', '0');
$arOptions['OPTIONS']['pp_order_city_status'] = COption::GetOptionString($MODULE_ID, 'pp_order_city_status', '0');

$arOptions['OPTIONS']['pp_from_city'] = COption::GetOptionString($MODULE_ID, 'pp_from_city');

$arOptions['OPTIONS']['pp_store_region'] = COption::GetOptionString($MODULE_ID, 'pp_store_region');
$arOptions['OPTIONS']['pp_store_city'] = COption::GetOptionString($MODULE_ID, 'pp_store_city');
$arOptions['OPTIONS']['pp_store_address'] = COption::GetOptionString($MODULE_ID, 'pp_store_address');
$arOptions['OPTIONS']['pp_store_phone'] = COption::GetOptionString($MODULE_ID, 'pp_store_phone');
$arOptions['OPTIONS']['pp_store_fio'] = COption::GetOptionString($MODULE_ID, 'pp_store_fio');
$arOptions['OPTIONS']['pp_store_post'] = COption::GetOptionString($MODULE_ID, 'pp_store_post');
$arOptions['OPTIONS']['pp_store_organisation'] = COption::GetOptionString($MODULE_ID, 'pp_store_organisation');
$arOptions['OPTIONS']['pp_store_comment'] = COption::GetOptionString($MODULE_ID, 'pp_store_comment');

$arOptions['OPTIONS']['pp_dimension_width'] = COption::GetOptionString($MODULE_ID, 'pp_dimension_width');
$arOptions['OPTIONS']['pp_dimension_height'] = COption::GetOptionString($MODULE_ID, 'pp_dimension_height');
$arOptions['OPTIONS']['pp_dimension_depth'] = COption::GetOptionString($MODULE_ID, 'pp_dimension_depth');

$arOptions['OPTIONS']['pp_use_coeff'] = COption::GetOptionString($MODULE_ID, 'pp_use_coeff');
$arOptions['OPTIONS']['pp_custom_coeff'] = COption::GetOptionString($MODULE_ID, 'pp_custom_coeff');

// Legacy ?
$arOptions['OPTIONS']['pp_zone_count'] = COption::GetOptionString($MODULE_ID, 'pp_zone_count');
$arOptions['OPTIONS']['pp_free_delivery_price'] = COption::GetOptionString($MODULE_ID, 'pp_free_delivery_price');
$arOptions['OPTIONS']['show_elements_count'] = COption::GetOptionString($MODULE_ID, 'show_elements_count', '50');