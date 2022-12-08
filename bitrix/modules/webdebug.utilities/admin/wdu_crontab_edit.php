<?
use
	\WD\Utilities\Cli,
	\WD\Utilities\Helper;

$ModuleID = 'webdebug.utilities';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$ModuleID.'/prolog.php');
if (!CModule::IncludeModule($ModuleID)) {
	die('Module is not found!');
}
IncludeModuleLangFile(__FILE__);

$ModuleRights = $APPLICATION->GetGroupRight($ModuleID);
if($ModuleRights=="D") {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$strRawCommand = urldecode($_GET['ID']);
$arRawCommand = Cli::parseCronTask($strRawCommand);

$Mode = !empty($strRawCommand) ? 'edit' : 'add';
if ($Mode=='edit') {
	$APPLICATION->SetTitle(GetMessage('WDU_PAGE_TITLE_EDIT'));
} else {
	$APPLICATION->SetTitle(GetMessage('WDU_PAGE_TITLE_ADD'));
}

$arTabs = array(
	array("DIV"=>"wdu_tab", "TAB"=>GetMessage('WDU_TAB1_NAME'), "TITLE"=>GetMessage('WDU_TAB1_DESC')),
);

$obTabControl = new CAdminTabControl("WDU_CrontabEdit", $arTabs);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (isset($_POST["save"]) && trim($_POST["save"])!="" || isset($_POST["apply"]) && trim($_POST["apply"])!="") {
	$arErrors = array();
	if(empty($strRawCommand)) {
		$strRawCommand = $_POST['raw_command'];
	}
	//
	$strMinute = $_POST['fields']['MINUTE'];
	$strHour = $_POST['fields']['HOUR'];
	$strDay = $_POST['fields']['DAY'];
	$strMonth = $_POST['fields']['MONTH'];
	$strWeekday = $_POST['fields']['WEEKDAY'];
	$strCommand = $_POST['fields']['COMMAND'];
	//
	$strNewCommand = "{$strCommand}";
	$strNewSchedule = "{$strMinute} {$strHour} {$strDay} {$strMonth} {$strWeekday}";
	//
	$bResult = false;
	if ($Mode=="edit") {
		$arRawCommand = Cli::parseCronTask($strRawCommand);
		if(!empty($strRawCommand) && Cli::isCronTaskConfigured($arRawCommand['COMMAND'], $arRawCommand['SCHEDULE'])) {
			if(Cli::deleteCronTask($strRawCommand)) {
				$bResult = Cli::addCronTask($strNewCommand, $strNewSchedule);
			}
		}
	} else {
		$bResult = Cli::addCronTask($strNewCommand, $strNewSchedule);
	}
	if ($bResult) {
		if (isset($_POST["save"]) && trim($_POST["save"])!="") {
			LocalRedirect("/bitrix/admin/wdu_crontab_list.php?lang=".LANGUAGE_ID);
		} else {
			$strCommand = urlencode($strNewSchedule.' '.$strNewCommand);
			LocalRedirect("/bitrix/admin/wdu_crontab_edit.php?ID={$strCommand}&lang=".LANGUAGE_ID."&".$obTabControl->ActiveTabParam());
		}
	}
}

// Prepare fields
if (!is_array($arFields)) {
	$arFields = array();
}

// Deleting
if ($_GET["action"]=="delete" && !empty($strRawCommand) && check_bitrix_sessid()) {
	if(Cli::deleteCronTask($strRawCommand)) {
		LocalRedirect('/bitrix/admin/wdu_crontab_list.php?lang='.LANGUAGE_ID);
	}
}

// MenuItem: List
$aMenu[] = array(
	"TEXT"	=> GetMessage('WDU_CONTEXT_LIST'),
	"LINK"	=> "/bitrix/admin/wdu_crontab_list.php?lang=".LANGUAGE_ID,
	"ICON"	=> "btn_list",
);
if ($Mode == "edit") {
	// MenuItem: Add
	$aMenu[] = array(
		"TEXT"	=> GetMessage('WDU_CONTEXT_ADD'),
		"LINK"	=> "/bitrix/admin/wdu_crontab_edit.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_new",
	);
	// MenuItem: Delete
	$Confirm = GetMessage('WDU_CONTEXT_DELETE_CONFIRM');
	$aMenu[] = array(
		"TEXT"	=> GetMessage('WDU_CONTEXT_DELETE'),
		"LINK"	=> "javascript:if (confirm('{$Confirm}')) window.location='/bitrix/admin/wdu_crontab_edit.php?action=delete&ID=".urlencode($strRawCommand)."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"	=> "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

$arRawSchedule = array();
if(!empty($arRawCommand['SCHEDULE'])) {
	$arRawSchedule = explode(' ',$arRawCommand['SCHEDULE']);
}

if(!Cli::canAutoSet()) {
	Helper::showNote(Helper::getMessage('WDU_CLI_CANNOT_AUTOSET'));
}
else {
	if(Cli::isHostingTimeweb()){
		Helper::showNote(Helper::getMessage('WDU_CLI_IS_TIMEWEB'), true);
		print '<br/>';
	}
	?>
	<form method="post" action="<?=POST_FORM_ACTION_URI;?>" id="wdu_crontab_edit">
		<input type="hidden" name="raw_command" value="<?=htmlspecialcharsbx($strRawCommand);?>" />
		<?$obTabControl->Begin();?>
		<?$obTabControl->BeginNextTab();?>
			<?if($Mode=='edit'):?>
				<tr id="tr_raw">
					<td colspan="2"><div><?=GetMessage('WDU_EDITING_COMMAND');?></div><div style="background:#eee; border:1px dashed gray; margin:4px 0 20px; padding:4px 8px;"><code><?=htmlspecialcharsbx($strRawCommand);?></code></div></td>
				</tr>
			<?endif?>
			<tr id="tr_command">
				<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_COMMAND')?>:</td>
				<td class="field-data">
					<input type="text" size="60" name="fields[COMMAND]" style="width:95%" value="<?=htmlspecialcharsbx($arRawCommand['COMMAND'])?>" />
				</td>
			</tr>
			<tr id="tr_minute">
				<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_MINUTE')?>:</td>
				<td class="field-data">
					<input type="text" name="fields[MINUTE]" value="<?=htmlspecialcharsbx($arRawSchedule[0]);?>" size="10" />
				</td>
			</tr>
			<tr id="tr_hour">
				<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_HOUR')?>:</td>
				<td class="field-data">
					<input type="text" name="fields[HOUR]" value="<?=htmlspecialcharsbx($arRawSchedule[1]);?>" size="10" />
				</td>
			</tr>
			<tr id="tr_day">
				<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_DAY')?>:</td>
				<td class="field-data">
					<input type="text" name="fields[DAY]" value="<?=htmlspecialcharsbx($arRawSchedule[2]);?>" size="10" />
				</td>
			</tr>
			<tr id="tr_month">
				<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_MONTH')?>:</td>
				<td class="field-data">
					<input type="text" name="fields[MONTH]" value="<?=htmlspecialcharsbx($arRawSchedule[3]);?>" size="10" />
				</td>
			</tr>
			<tr id="tr_weekday">
				<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_WEEKDAY')?>:</td>
				<td class="field-data">
					<input type="text" name="fields[WEEKDAY]" value="<?=htmlspecialcharsbx($arRawSchedule[4]);?>" size="10" />
				</td>
			</tr>
			<?$obTabControl->Buttons(array("disabled"=>false,"back_url"=>"wdu_crontab_list.php?lang=".LANGUAGE_ID));?>
		<?$obTabControl->End();?>
	</form>
	<?
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>