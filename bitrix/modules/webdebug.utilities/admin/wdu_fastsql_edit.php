<?
use
	\WD\Utilities\FastSql,
	\WD\Utilities\Helper;

/********************************************
For module `security` it need to add rule:
/bitrix/admin/wdu_fastsql_edit*
********************************************/
#define("NOT_CHECK_PERMISSIONS", true);
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

if ($_GET['GetUserInfo']=='Y' && $_GET['UserID']>0) {
	$resUser = CUser::GetList($UserSortField='ID',$UserSortOrder='ASC',array('ID'=>$_GET['UserID']),array('FIELDS'=>array('LOGIN','NAME','LAST_NAME')));
	if ($arUser = $resUser->GetNext(false,false)) {
		$APPLICATION->RestartBuffer();
		print "({$arUser['LOGIN']}) {$arUser['NAME']} {$arUser['LAST_NAME']}";
	} else {
		print GetMessage('WDU_ERROR_USER_NOT_FOUND');
	}
	die();
}
$WD_FastSqlID = IntVal($_GET['ID']);
$Lang = LANGUAGE_ID;

$Mode = $WD_FastSqlID>0 ? 'edit' : 'add';
if ($Mode=='edit') {
	$APPLICATION->SetTitle(GetMessage('WDU_PAGE_TITLE_EDIT'));
} else {
	$APPLICATION->SetTitle(GetMessage('WDU_PAGE_TITLE_ADD'));
}

$arTabs = array(
	array("DIV"=>"wdu_fastsql_tab", "TAB"=>GetMessage('WDU_TAB1_NAME'), "TITLE"=>GetMessage('WDU_TAB1_DESC')),
);

$tabControl = new CAdminTabControl("WDU_FastSQL", $arTabs);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (isset($_POST["save"]) && trim($_POST["save"])!="" || isset($_POST["apply"]) && trim($_POST["apply"])!="") {
	$arErrors = array();
	$arSaveFields = $_POST["fields"];
	$arSaveFields['ACTIVE'] = $arSaveFields['ACTIVE']=='Y' ? 'Y' : 'N';
	if ($Mode=="edit") {
		$ID = $WD_FastSqlID;
		$bResult = FastSql::Update($ID, $arSaveFields);
	} else {
		$ID = FastSql::Add($arSaveFields);
		$bResult = $ID>0;
	}
	if (!$bResult) {
		$Delimiter = '<br/>';
		print 'Error!';
	}
	if ($bResult) {
		if (isset($_POST["save"]) && trim($_POST["save"])!="") {
			LocalRedirect("/bitrix/admin/wdu_fastsql_list.php?lang=".LANGUAGE_ID);
		} else {
			LocalRedirect("/bitrix/admin/wdu_fastsql_edit.php?ID={$ID}&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
		}
	}
}

// Get current interface data
if ($WD_FastSqlID>0) {
	$resSqlList = FastSql::GetByID($WD_FastSqlID);
	$arFields = $resSqlList->GetNext();
}

// Prepare fields
if (!is_array($arFields)) {
	$arFields = array(
		'ACTIVE' => 'Y',
		'SORT' => '100',
		'USER_ID' => $USER->GetID(),
	);
}
if ($arFields['USER_ID']==='0') {
	$arFields['USER_ID']='';
}

// If not exists
if ($Mode=='edit' && (!is_array($arFields) || empty($arFields))) {
	print(GetMessage('WDU_ERROR_NOT_FOUND'));
	die();
}

// Deleting
if ($_GET["action"]=="delete" && $WD_FastSqlID>0 && check_bitrix_sessid()) {
	if (FastSql::Delete($WD_FastSqlID)) {
		LocalRedirect('/bitrix/admin/wdu_fastsql_list.php?lang='.LANGUAGE_ID);
	}
}

// MenuItem: List
$aMenu[] = array(
	"TEXT"	=> GetMessage('WDU_CONTEXT_LIST'),
	"LINK"	=> "/bitrix/admin/wdu_fastsql_list.php?lang=".LANGUAGE_ID,
	"ICON"	=> "btn_list",
);
if ($Mode == "edit") {
	// MenuItem: Add
	$aMenu[] = array(
		"TEXT"	=> GetMessage('WDU_CONTEXT_ADD'),
		"LINK"	=> "/bitrix/admin/wdu_fastsql_edit.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_new",
	);
	// MenuItem: Delete
	$Confirm = GetMessage('WDU_CONTEXT_DELETE_CONFIRM');
	$aMenu[] = array(
		"TEXT"	=> GetMessage('WDU_CONTEXT_DELETE'),
		"LINK"	=> "javascript:if (confirm('{$Confirm}')) window.location='/bitrix/admin/wdu_fastsql_edit.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"	=> "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<form method="post" action="<?=POST_FORM_ACTION_URI;?>" enctype="multipart/form-data" name="post_form" id="wdг_fastsql_list">
	<?/*<input type="hidden" name="____SECFILTER_CONVERT_JS" value="Y" />*/?>
	<?$tabControl->Begin();?>
	<?$tabControl->BeginNextTab();?>
		<tr id="tr_active">
			<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_ACTIVE')?>:</td>
			<td class="field-data">
				<input type="checkbox" name="fields[ACTIVE]" value="Y"<?if($arFields["ACTIVE"]=='Y'):?> checked="checked"<?endif?> />
			</td>
		</tr>
		<tr id="tr_sort">
			<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_SORT')?>:</td>
			<td class="field-data">
				<input type="text" name="fields[SORT]" value="<?=$arFields["SORT"]?>" size="10" maxlength="255" />
			</td>
		</tr>
		<tr id="tr_query">
			<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_QUERY')?>:</td>
			<td class="field-data">
				<textarea name="fields[QUERY]" cols="50" rows="5" style="width:95%"><?=$arFields["QUERY"]?></textarea>
			</td>
		</tr>
		<tr id="tr_description">
			<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_DESCRIPTION')?>:</td>
			<td class="field-data">
				<textarea name="fields[DESCRIPTION]" cols="50" rows="2" style="width:95%"><?=$arFields["DESCRIPTION"]?></textarea>
			</td>
		</tr>
		<tr id="tr_user_id">
			<td class="field-name" width="40%"><?=GetMessage('WDU_FIELD_USER_ID');?>:</td>
			<td class="field-data">
				<script>
				function WDU_UpdateUserName() {
					var UserID = document.getElementById('wdu_fastsql_field_user_id').value;
					BX.ajax.load([{
						url:'<?=$_SERVER['PHP_SELF']?>?GetUserInfo=Y&UserID='+UserID,
						type:'html',
						callback:function(UserName){
							document.getElementById('wdu_fastsql_field_user_name').innerText = UserName;
						}
					}]);
				}
				</script>
				<input type="text" name="fields[USER_ID]" value="<?=$arFields["USER_ID"]?>" size="10" maxlength="255" id="wdu_fastsql_field_user_id" onchange="WDU_UpdateUserName();" />
				<input class="tablebodybutton" type="button" name="FindUserID" id="FindUserID" onclick="window.open('/bitrix/admin/user_search.php?lang=ru&FN=post_form&FC=fields[USER_ID]', '', 'scrollbars=yes,resizable=yes,width=900,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 900)/2-5));" value="..." />
				<span id="wdu_fastsql_field_user_name" style="margin-left:4px"></span>
				<script>
				if (document.getElementById('wdu_fastsql_field_user_id').value>0) {
					WDU_UpdateUserName();
				}
				</script>
			</td>
		</tr>
		<?$tabControl->Buttons(array("disabled"=>false,"back_url"=>"wd_reviews2_list.php?lang=".LANGUAGE_ID.'&interface='.$WD_Reviews2_InterfaceID));?>
	<?$tabControl->End();?>
</form>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>