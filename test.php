<?php

use Enterego\contragents\EnteregoTreatmentContrAgents;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;

$xml = new CDataXML();
$xml->Load($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/enterego_class/contragents/parseContarget.xml');
$contrs = $xml->GetArray()['КоммерческаяИнформация']['#']['Контрагенты'][0]['#']['Контрагент'];
$order= new CSaleOrderLoader();
foreach ($contrs as $item){
    $value = $item["#"];
    $writeContr = new EnteregoTreatmentContrAgents();
    $writeContr->import($value);
}

