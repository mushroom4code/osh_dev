<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('sale');
use Enterego\ORM\EnteregoORMContragentsTable;

//ConvertTimeStamp(false, "FULL");
//strtotime();
date('');
try {
    $dateInsertUpdate = \Bitrix\Main\Type\DateTime::createFromUserTime(COption::GetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS'));
    $saveDB = EnteregoORMContragentsTable::update(60,
        [
            'DATE_UPDATE' => $dateInsertUpdate,
            'DATE_INSERT' => $dateInsertUpdate,
            'NAME_ORGANIZATION' => "VASIA3"
        ]
    );

  $queryResult =  EnteregoORMContragentsTable::getList(
        array(
            'select' => array('*'),
            'filter' => array('ID_CONTRAGENT' =>60),
        )
    )->fetch();
  print_r($queryResult);

} catch (Exception $e) {
    print_r($e);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>