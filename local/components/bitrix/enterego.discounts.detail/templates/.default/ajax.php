<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$arParams = json_decode(base64_decode($_REQUEST['paramsstr']),1);
	
$arParams['AJAX'] = 'Y';
$arParams['numPage'] = $_REQUEST['numPage'];
/*$APPLICATION->IncludeComponent(
	"bbrain:page",
	"",
	$arParams
);*/?>