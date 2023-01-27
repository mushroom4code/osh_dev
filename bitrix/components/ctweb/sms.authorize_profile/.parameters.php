<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "ALLOW_MULTIPLE_USERS" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("SMS_AUTH_ALLOW_MULTIPLE_USERS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
    ),
);