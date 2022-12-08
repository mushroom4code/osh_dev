<?php

use Bitrix\Main\Loader;

Loader::includeModule('sale');

CModule::AddAutoloadClasses(
    "osh.userprice",
    array(
        "Enterego\UserPrice\CmlUserPrice"                => "lib/CmlUserPrice.php",
    )
);

require_once(__DIR__ . '/lib/PluginStatic.php');
require_once(__DIR__ . '/lib/UserPriceHeperOsh.php');
require_once(__DIR__ . '/lib/Special.php');
require_once(__DIR__ . '/lib/EntUserPriceGlobal.php');

AddEventHandler('main', 'OnEventLogGetAuditTypes', ['EntUserPriceGlobal', 'OnEventLogGetAuditTypes']);
AddEventHandler("catalog", "OnSuccessCatalogImport1C", ['EntUserPriceGlobal', "EraseCaches"]);