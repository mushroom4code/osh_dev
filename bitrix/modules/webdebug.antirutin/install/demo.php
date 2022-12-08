<?
$ModuleID = 'webdebug.antirutin';
//-----------------------------------------------------------------------

global $MESS, $APPLICATION, $USER;

$MESS['WDA_MODULE_DEMO_NOTICE'] = '<div style="color:red"><b>Модуль «<a href="http://marketplace.1c-bitrix.ru/solutions/'.$ModuleID.'/" target="_blank" style="color:red">#MODULE_NAME#</a>» работает в демонстрационном режиме.</b></div><div>До завершения демо-режима осталось <b>#DAYS#</b>.</div><div>После завершения демо-режима модуль перестанет функционировать.</div><div>Для снятия ограничений необходимо <a href="http://marketplace.1c-bitrix.ru/tobasket.php?ID='.$ModuleID.'" target="_blank">приобрести лицензию</a>.</div>';
$MESS['WDA_MODULE_EXPIRED'] = '<div style="color:red"><b>Демонстрационный срок работы модуля «<a href="http://marketplace.1c-bitrix.ru/solutions/'.$ModuleID.'/" target="_blank" style="color:red">#MODULE_NAME#</a>» завершен.</b></div><div>Для продолжения работы модуля необходимо <a href="http://marketplace.1c-bitrix.ru/tobasket.php?ID='.$ModuleID.'" target="_blank">приобрести лицензию</a>.</div>';
$MESS['WDA_MODULE_DAYS'] = ',день,дня,,,дней';
if(!(defined('BX_UTF')&&BX_UTF===true)) {
	$MESS['WDA_MODULE_DEMO_NOTICE'] = $APPLICATION->ConvertCharset($MESS['WDA_MODULE_DEMO_NOTICE'],'UTF-8','CP1251');
	$MESS['WDA_MODULE_EXPIRED'] = $APPLICATION->ConvertCharset($MESS['WDA_MODULE_EXPIRED'],'UTF-8','CP1251');
	$MESS['WDA_MODULE_DAYS'] = $APPLICATION->ConvertCharset($MESS['WDA_MODULE_DAYS'],'UTF-8','CP1251');
}

$ModuleID_ = str_replace('.','_',$ModuleID);
$ModuleName = $ModuleID;
require_once(__DIR__.'/index.php');
$obModule = new $ModuleID_();
$ModuleName = $obModule->MODULE_NAME;
unset($obModule);
$MESS['WDA_MODULE_DEMO_NOTICE'] = str_replace('#MODULE_NAME#', $ModuleName, $MESS['WDA_MODULE_DEMO_NOTICE']);
$MESS['WDA_MODULE_EXPIRED'] = str_replace('#MODULE_NAME#', $ModuleName, $MESS['WDA_MODULE_EXPIRED']);

$bExpired = false;

$ModuleMode = CModule::IncludeModuleEx($ModuleID);

$APPLICATION->addHeadString('<style>#'.$ModuleID_.'_demo_note > .adm-info-message {margin:0!important;}</style>');

if(!function_exists('WdaModuleWordForm')) {
	function WdaModuleWordForm($Value, $arWord) {
		$Value = trim($Value);
		$LastSymbol = substr($Value,-1);
		$SubLastSymbol = substr($Value,-2,1);
		if (strlen($Value)>=2 && $SubLastSymbol == '1')
			return $arWord['5'];
		elseif ($LastSymbol=='1')
			return $arWord['1'];
		elseif ($LastSymbol >= 2 && $LastSymbol <= 4)
			return $arWord['2'];
		else
			return $arWord['5'];
	}
}

if ($ModuleMode==MODULE_DEMO) {
	$Now = time();
	if(defined($ModuleID_.'_OLDSITEEXPIREDATE') && $Now<constant($ModuleID_.'_OLDSITEEXPIREDATE')) {
		$arExpire = getdate(constant($ModuleID_.'_OLDSITEEXPIREDATE'));
		$arNow = getdate($Now);
		$intExpireDate = gmmktime($arExpire['hours'],$arExpire['minutes'],$arExpire['seconds'],$arExpire['mon'],$arExpire['mday'],$arExpire['year']);
		$intNowDate = gmmktime($arExpire['hours'],$arExpire['minutes'],$arExpire['seconds'],$arNow['mon'],$arNow['mday'],$arNow['year']);
		$intDays = ($intExpireDate-$intNowDate)/86400;
		if($bSkipIncludeProlog!==true) {
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		}
		print BeginNote('id="'.$ModuleID_.'_demo_note"');
		print GetMessage('WDA_MODULE_DEMO_NOTICE',array('#DAYS#'=>$intDays.' '.WdaModuleWordForm($intDays,explode(',',GetMessage('WDA_MODULE_DAYS')))));
		print EndNote();
		print '<br/>';
	} else {
		$bExpired = true;
	}
}

if ($ModuleMode==MODULE_DEMO_EXPIRED || $bExpired) {
	if($bSkipIncludeProlog!==true) {
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	}
	print BeginNote('id="'.$ModuleID_.'_demo_note"');
	print GetMessage('WDA_MODULE_EXPIRED');
	print EndNote();
}

?>