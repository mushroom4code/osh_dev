<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Ctweb\SMSAuth\Module;
use Bitrix\Main\Loader;

if (!Loader::includeModule("ctweb.smsauth")) {
    return;
}

$arRegisterFields = array_intersect_key(Module::getUserRegisterFields(), array_flip(Module::getOptions()['REGISTER_FIELDS']));

$arComponentParameters = array(
    "PARAMETERS" => array(
        "REQUIRE_FIELDS" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("SMS_AUTH_REQUIRE_FIELDS"),
            "TYPE" => "LIST",
            'MULTIPLE' => 'Y',
            "VALUES" => $arRegisterFields
        ),
    ),
);