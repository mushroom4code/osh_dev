<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "RELOCATION" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("RELOCATION"),
            "TYPE" => "STRING",
            "DEFAULT" => "/personal/",
        ),
    ),
);