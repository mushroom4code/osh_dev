<?php

CModule::AddAutoloadClasses("", array(
    '\CommonPVZ\DeliveryHelper' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DeliveryHelper.php',
    '\CommonPVZ\CommonPVZ' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/CommonPVZ.php',
    '\CommonPVZ\CommonDoorDeliveryHandler' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/handlerDelivery.php',
    '\CommonPVZ\OshishaDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/OshishaDelivery.php',
    '\CommonPVZ\DoorDeliveryProfile' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DoorDeliveryProfile.php',
    '\CommonPVZ\PVZDeliveryProfile' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/PVZDeliveryProfile.php',
    '\CommonPVZ\SDEKDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/SDEKDelivery.php',
    '\CommonPVZ\RussianPostDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/RussianPostDelivery.php',
    '\CommonPVZ\RussianPostPointsTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/tables/RussianPostPointsTable.php',
    '\CommonPVZ\DellinDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/DellinDelivery.php',
    '\CommonPVZ\DellindeliveryApicore' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/DellindeliveryApicore.php',
    '\CommonPVZ\DellinPointsTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/tables/DellinPointsTable.php',
    '\CommonPVZ\PEKDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/PEKDelivery.php',
    '\CommonPVZ\FivePostDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/FivePostDelivery.php',
    '\CommonPVZ\FivePostPointsTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/tables/FivePostPointsTable.php',
    '\CommonPVZ\PickPointDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/deliveries/PickPointDelivery.php',
    '\CommonPVZ\PickPointPointsTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/tables/PickPointPointsTable.php',
    '\CommonPVZ\SavedDeliveryProfiles' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/SavedDeliveryProfiles.php',
    '\CommonPVZ\ProfilesAddressesTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ProfilesAddressesTable.php',
    '\CommonPVZ\ProfilesPropertiesTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ProfilesPropertiesTable.php',
    '\Enterego\EnteregoDBDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/EnteregoDBDelivery.php',
    '\Enterego\EnteregoDeliveries' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/EnteregoDeliveries.php',
    '\PecomKabinet' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/pecom_kabinet.php',
));

if ( file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php') )
{
    require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
}

if(!\CJSCore::IsExtRegistered('osh_pickup')){
    \CJSCore::RegisterExt(
        "osh_pickup",
        array(
            "js" => "/bitrix/js/".\CommonPVZ\DeliveryHelper::$MODULE_ID."/pickup.js",
            "css" => "/bitrix/css/".\CommonPVZ\DeliveryHelper::$MODULE_ID."/styles.css",
            "lang" => "/bitrix/modules/".\CommonPVZ\DeliveryHelper::$MODULE_ID."/lang/".LANGUAGE_ID."/js/pickup.php",
            "rel" => Array("ajax","popup"),
            "skip_core" => false,
        )
    );
}