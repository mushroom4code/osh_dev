<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$module_id = "ipol.fivepost";
CModule::IncludeModule($module_id);

\Ipol\Fivepost\SubscribeHandler::getAjaxAction($_REQUEST[\Ipol\Fivepost\SubscribeHandler::getMODULELBL().'action']);
?>