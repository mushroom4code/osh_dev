<?
use
	\WD\Utilities\Cli,
	\WD\Utilities\Helper;

$ModuleID = 'webdebug.utilities';
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$ModuleID.'/prolog.php');
if (!CModule::IncludeModule($ModuleID)) {
	die('Module is not found!');
}
IncludeModuleLangFile(__FILE__);

$ModuleRights = $APPLICATION->GetGroupRight($ModuleID);
if($ModuleRights=="D") {
	$APPLICATION->AuthForm(Helper::getMessage("ACCESS_DENIED"));
}

$ModuleRights = $APPLICATION->GetGroupRight($ModuleID);
if($ModuleRights=="D") {
	$APPLICATION->AuthForm(Helper::getMessage("ACCESS_DENIED"));
}

$sTableID = "WDU_Crontab";
$lAdmin = new CAdminList($sTableID);

// Processing with group actions
if(($arID = $lAdmin->GroupAction())) {
	/*
  if($_REQUEST['action_target']=='selected') {
    $rsData = CWDU_FastSQL::GetList(array($by=>$order), $arFilter, false, $arNavParams);
    while($arRes = $rsData->Fetch()) {
			$arID[] = $arRes['ID'];
		}
  }
	*/
  foreach($arID as $ID) {
		@set_time_limit(0);
    switch($_REQUEST['action']) {
			case "delete":
				$bDeleted = false;
				$arFullCommand = Cli::parseCronTask($ID);
				if(is_array($arFullCommand)) {
					$strSchedule = $arFullCommand['SCHEDULE'];
					$strCommand = $arFullCommand['COMMAND'];
					if(!empty($strSchedule) && !empty($strCommand)) {
						$bDeleted = Cli::deleteCronTask($strCommand, $strSchedule);
					}
				}
				if(!$bDeleted) {
					$lAdmin->AddGroupError('Delete error: '.$ID);
				}
				break;
    }
  }
}

if (!is_array($arFilter)) {
	$arFilter = array();
}

// Get items list
$arCrontab = array();
$arCrontab = Cli::getCronTasks();
$rsData = new CDBResult();
$rsData->InitFromArray($arCrontab);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(''));

// Add headers
$arHeaders = array(
  array(
	  "id" => "MINUTE",
    "content" => Helper::getMessage('WDU_FIELD_MINUTE'),
    "sort" => "MINUTE",
    "align" => "left",
    "default" => true,
  ),
  array(
	  "id" => "HOUR",
    "content" => Helper::getMessage('WDU_FIELD_HOUR'),
    "sort" => "HOUR",
    "align" => "left",
    "default" => true,
  ),
  array(
	  "id" => "DAY",
    "content" => Helper::getMessage('WDU_FIELD_DAY'),
    "sort" => "DAY",
    "align" => "left",
    "default" => true,
  ),
  array(
	  "id" => "MONTH",
    "content" => Helper::getMessage('WDU_FIELD_MONTH'),
    "sort" => "MONTH",
    "align" => "left",
    "default" => true,
  ),
  array(
	  "id" => "WEEKDAY",
    "content" => Helper::getMessage('WDU_FIELD_WEEKDAY'),
    "sort" => "WEEKDAY",
    "align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"COMMAND",
    "content" => Helper::getMessage('WDU_FIELD_COMMAND'),
    "sort" => "COMMAND",
		"align" => "left",
    "default" => true,
  ),
);
$lAdmin->AddHeaders($arHeaders);

// Build items list
while ($arRes = $rsData->NavNext(true, "f_", false)) {
  $obRow = &$lAdmin->AddRow(htmlspecialcharsbx(urldecode($f_COMMAND_FULL)), $arRes);
	$obRow->AddViewField('MINUTE', htmlspecialcharsbx($f_MINUTE));
	$obRow->AddViewField('HOUR', htmlspecialcharsbx($f_HOUR));
	$obRow->AddViewField('DAY', htmlspecialcharsbx($f_DAY));
	$obRow->AddViewField('MONTH', htmlspecialcharsbx($f_MONTH));
	$obRow->AddViewField('WEEKDAY', htmlspecialcharsbx($f_WEEKDAY));
	$obRow->AddViewField('COMMAND', '<a href="/bitrix/admin/wdu_crontab_edit.php?ID='.urlencode($f_COMMAND_FULL).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsbx($f_COMMAND).'</a>');
	
	// Build context menu
  $arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => Helper::getMessage('WDU_CONTEXT_EDIT'),
		"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/wdu_crontab_edit.php?ID=".urlencode($f_COMMAND_FULL)."&lang=".LANGUAGE_ID)
	);
	$arActions[] = array(
		"ICON" => "delete",
		"DEFAULT" => false,
		"TEXT" => Helper::getMessage('WDU_CONTEXT_DELETE'),
		"ACTION" => "if(confirm('".Helper::getMessage('WDU_CONTEXT_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup(urlencode($f_COMMAND_FULL), 'delete')
	);
  $obRow->AddActions($arActions);
}

// List Footer
$lAdmin->AddFooter(
  array(
    array("title" => Helper::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
    array("counter"=>true, "title" => Helper::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
  )
);
$lAdmin->AddGroupActionTable(Array(
  "delete" => Helper::getMessage("MAIN_ADMIN_LIST_DELETE"),
));

// Context menu
global $APPLICATION;
$aContext = array(
  array(
    "TEXT" => Helper::getMessage('WDU_CONTEXT_ADD'),
    "LINK" => "wdu_crontab_edit.php?lang=".LANGUAGE_ID,
    "ICON" => "btn_new",
  ),
);
$lAdmin->AddAdminContextMenu($aContext);

// Start output
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Helper::getMessage('WDU_PAGE_TITLE'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

if(!Cli::canAutoSet()) {
	Helper::showNote(Helper::getMessage('WDU_CLI_CANNOT_AUTOSET'));
}
else {
	if(Cli::isHostingTimeweb()){
		Helper::showNote(Helper::getMessage('WDU_CLI_IS_TIMEWEB'), true);
		print '<br/>';
	}
	$lAdmin->DisplayList();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>