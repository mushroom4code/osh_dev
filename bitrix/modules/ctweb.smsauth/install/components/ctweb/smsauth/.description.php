<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
        "NAME" => GetMessage("CT_SMSAUTH_COMPONENT_NAME"),
        "DESCRIPTION" => GetMessage("CT_SMSAUTH_COMPONENT_DESCRIPTION"),
        "CACHE_PATH" => "Y",
        "SORT" => 40,
        "PATH" => array(
                "ID" => "ctweb",
                "NAME" => GetMessage("CTWEB"),
        ),
);
?>
