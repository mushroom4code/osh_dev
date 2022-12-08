<?
use
	\WD\Utilities\Helper,
	\WD\Utilities\Option;

$ModuleID = 'webdebug.utilities';
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$ModuleID.'/prolog.php');
if (!CModule::IncludeModule($ModuleID)) {
	die('Module is not found!');
}
IncludeModuleLangFile(__FILE__);

$ModuleRights = $APPLICATION->GetGroupRight($ModuleID);
if($ModuleRights=="D") {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(GetMessage("WEBDEBUG_OPTIONS_APPLICATION_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

global $DB;
global $APPLICATION;

$Mode = "add";
$ID = trim($_GET["ID"]);
if (trim($ID)!="") {
	$Mode = "edit";
}

$arOption = null;

// Copying
if (trim($_GET["CopyID"])!="" && empty($_POST)) {
	$Res = Option::GetList(false, array("ID"=>$_GET["CopyID"]));
	$arOption = $Res->GetNext(false,false);
}

if ($Mode=="edit" && $ID!="") {
	$Res = Option::GetList(false, array("ID"=>$ID));
	$arOption = $Res->GetNext(false,false);
}

if (isset($_POST["save"]) && trim($_POST["save"])!="" || isset($_POST["apply"]) && trim($_POST["apply"])!="") {
	$arSaveFields = array(
		"NAME" => trim($_POST["name"]),
		"MODULE_ID" => trim($_POST["module_id"]),
		"VALUE" => $_POST["value"] ? $_POST["value"] : false,
		"DESCRIPTION" => trim($_POST["description"]) ? trim($_POST["description"]) : false,
		"SITE_ID" => $_POST["site_id"] ? $_POST["site_id"] : "",
	);
	$ID = $arSaveFields["MODULE_ID"]."_".$arSaveFields["NAME"]."_".$arSaveFields["SITE_ID"];
	if($arOption){
		COption::RemoveOption($arOption["MODULE_ID"], $arOption["NAME"], $arOption["SITE_ID"]);
	}
	if (!CheckVersion(SM_VERSION,'14.0.0')) {
		$arSaveFields["DESCRIPTION"] = $arSaveFields["SITE_ID"];
		$arSaveFields["SITE_ID"] = null;
	}
	$bSetResult = COption::SetOptionString($arSaveFields["MODULE_ID"],$arSaveFields["NAME"],$arSaveFields["VALUE"],$arSaveFields["DESCRIPTION"],$arSaveFields["SITE_ID"]);
	if ($bSetResult) {
		Option::Update($ID, array('DESCRIPTION'=>$arSaveFields["DESCRIPTION"]));
	}
	if (isset($_POST["save"]) && trim($_POST["save"])!="") {
		LocalRedirect("/bitrix/admin/wdu_option_list.php?lang=".LANGUAGE_ID);
	} else {
		LocalRedirect("/bitrix/admin/wdu_option_edit.php?ID={$ID}&lang=".LANGUAGE_ID."&WebdebugOptionsTabControl_active_tab=".$_REQUEST["WebdebugOptionsTabControl_active_tab"]);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////

// Deleting Profile
if ($_GET["action"]=="delete" && $ID!="" && check_bitrix_sessid()) {
	Option::Delete($ID);
	LocalRedirect("wdu_option_list.php?lang=".LANGUAGE_ID);
}

// MenuItem: Profiles
$aMenu[] = array(
	"TEXT"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_LIST_NAME"),
	"LINK"	=> "/bitrix/admin/wdu_option_list.php?lang=".LANGUAGE_ID,
	"ICON"	=> "btn_list",
	"TITLE"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_LIST_DESCR"),
);
if ($Mode == "edit") {
	// MenuItem: Add
	$aMenu[] = array(
		"TEXT"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_ADD_NAME"),
		"LINK"	=> "/bitrix/admin/wdu_option_edit.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_new",
		"TITLE"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_ADD_DESCR"),
	);
	// MenuItem: Copy
	$aMenu[] = array(
		"TEXT"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_COPY_NAME"),
		"LINK"	=> "/bitrix/admin/wdu_option_edit.php?CopyID=".$ID."&lang=".LANGUAGE_ID,
		"ICON"	=> "btn_copy",
		"TITLE"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_COPY_DESCR"),
	);
}
if ($Mode == "edit" && $ID!=1) {
	// MenuItem: Delete
	$aMenu[] = array(
		"TEXT"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_DELETE_NAME"),
		"LINK"	=> "javascript:if (confirm('".sprintf(GetMessage("WEBDEBUG_OPTION_TOOLBAR_DELETE_CONFIRM"),$arOption["NAME"])."')) window.location='/bitrix/admin/wdu_option_edit.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"	=> "btn_delete",
		"TITLE"	=> GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_DELETE_DESCR"),
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
$arTabs = array(
	array("DIV"=>"general", "TAB"=>GetMessage("WEBDEBUG_OPTIONS_TAB_GENERAL_NAME"), "ICON"=>"webdebug-options-tabs-general", "TITLE"=>GetMessage("WEBDEBUG_OPTIONS_TAB_GENERAL_DESCR")),
);
?>
<form method="post" action="" enctype="multipart/form-data" name="post_form" id="webdebug-option-parameters">
	<?$tabControl = new CAdminTabControl("WebdebugOptionsTabControl", $arTabs);?>
	<?$tabControl->Begin();?>
	<?$tabControl->BeginNextTab();?>
		<tr>
			<td class="l"><?=GetMessage("WEBDEBUG_OPTION_MODULE_ID")?>:</td>
			<td class="r">
				<?$arModules = Option::GetModulesList();?>
				<select name="module_id">
					<?foreach ($arModules as $ModuleID => $arModule):?>
						<option value="<?=$ModuleID?>"<?if($arOption["MODULE_ID"]==$ModuleID):?> selected="selected"<?endif?>>[<?=$ModuleID?>] <?=$arModule["NAME"]?></option>
					<?endforeach?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="l"><?=GetMessage("WEBDEBUG_OPTION_OPTION_ID")?>:</td>
			<td class="r"><input type="text" size="50" maxlength="50" class="text" value="<?=$arOption["NAME"]?>" name="name" /></td>
		</tr>
		<tr>
			<td class="l"><?=GetMessage("WEBDEBUG_OPTION_VALUE")?>:</td>
			<td class="r"><textarea class="text" rows="3" cols="50" name="value" maxlength="2000"><?=$arOption["VALUE"]?></textarea></td>
		</tr>
		<tr>
			<td class="l"><?=GetMessage("WEBDEBUG_OPTION_SITE_ID")?>:</td>
			<td class="r">
				<?
					$arSites = array();
					$resSite = CSite::GetList($SiteBy="sort",$SiteOrder="asc");
					while ($arSite = $resSite->GetNext()) {
						$arSites[$arSite["ID"]] = $arSite;
					}
					$resLang = CLanguage::GetList($by="lid", $order="asc", Array());
					while($arLang = $resLang->GetNext()) {
						$arSites[$arLang["ID"]] = $arLang;
					}
				?>
				<select name="site_id">
					<option value=""><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_SITE_ID_ANY");?></option>
					<?foreach ($arSites as $arSite):?>
						<option value="<?=$arSite["ID"]?>"<?if($arOption["SITE_ID"]==$arSite["ID"]):?> selected="selected"<?endif?>>[<?=$arSite["ID"]?>] <?=$arSite["NAME"]?></option>
					<?endforeach?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="l"><?=GetMessage("WEBDEBUG_OPTION_DESCRIPTION");?>:</td>
			<td class="r"><textarea class="text" rows="3" cols="50" name="description"><?=$arOption["DESCRIPTION"]?></textarea></td>
		</tr>
	<?$tabControl->Buttons(array("disabled"=>false,"back_url"=>"wdu_option_list.php?lang=".LANG));?>
	<?$tabControl->End();?>
</form>

<?
/////////////////////////////////////////////////////////////////////////////////////////////
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>