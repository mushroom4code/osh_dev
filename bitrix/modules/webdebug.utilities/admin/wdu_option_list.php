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

$sTableID = "WDU_Options";
$oSort = new CAdminSorting($sTableID, "NAME", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arSites = array();
$resSite = CSite::GetList($SiteBy="sort",$SiteOrder="asc");
while ($arSite = $resSite->GetNext()) {
	$arSites[$arSite["ID"]] = $arSite;
}
$resLang = CLanguage::GetList($LangSortBy="lid", $LangSortOrder="asc", Array());
while($arLang = $resLang->GetNext()) {
	$arSites[$arLang["ID"]] = $arLang;
}
$arModules = Option::GetModulesList();

// Filter
function CheckFilter() {
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $f;
	return count($lAdmin->arFilterErrors)==0;
}
$FilterArr = Array(
	"find_module_id",
	"find_name",
	"find_value",
	"find_description",
	"find_site_id",
);
$lAdmin->InitFilter($FilterArr);
if (CheckFilter()) {
	$arFilter = Array(
		"MODULE_ID" => $find_module_id,
		"%NAME" => $find_name,
		"%VALUE" => $find_value,
		"%DESCRIPTION" => $find_description,
		"SITE_ID" => $find_site_id,
	);
}

if($lAdmin->EditAction()) {
	foreach($FIELDS as $ID=>$arFields) {
		if(!$lAdmin->IsUpdated($ID)) continue;
		$DB->StartTransaction();
		if($rsData = Option::GetList(array(),array("ID"=>$ID))) {
			if ($arData = $rsData->Fetch()) {
				foreach($arFields as $key=>$value) $arData[$key]=$value;
				if(!Option::Update($ID, $arData)) {
					$lAdmin->AddGroupError("ERROR1! {$ID}", $ID);
					$DB->Rollback();
				}
			} else {
				$lAdmin->AddGroupError("ERROR2! {$ID}", $ID);
				$DB->Rollback();
			}
		} else {
			$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}
if(($arID = $lAdmin->GroupAction())) {
  if($_REQUEST['action_target']=='selected') {
    $rsData = Option::GetList(array($by=>$order), $arFilter);
    while($arRes = $rsData->Fetch()) $arID[] = $arRes['ID'];
  }
  foreach($arID as $ID) {
    if(strlen($ID)<=0) continue;
    switch($_REQUEST['action']) {
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!Option::Delete($ID)) {
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("rub_del_err"), $ID);
				}
				$DB->Commit();
				break;
    }
  }
}

// Get items list
$rsData = Option::GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("rub_nav")));

// Add headers
$lAdmin->AddHeaders(array(
	/*
  array(
	  "id" => "ID",
    "content" => GetMessage("WEBDEBUG_OPTIONS_COLUMN_ID"),
    "sort" => "id",
    "align" => "left",
    "default" => false,
  ),
	*/
  array(
	  "id" => "MODULE_ID",
    "content" => GetMessage("WEBDEBUG_OPTIONS_COLUMN_MODULE_ID"),
    "sort" => "module_id",
    "align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"NAME",
    "content" => GetMessage("WEBDEBUG_OPTIONS_COLUMN_NAME"),
    "sort" => "name",
		"align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"VALUE",
    "content" => GetMessage("WEBDEBUG_OPTIONS_COLUMN_VALUE"),
    "sort" => "value",
		"align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"DESCRIPTION",
    "content" => GetMessage("WEBDEBUG_OPTIONS_COLUMN_DESCRIPTION"),
    "sort" => "description",
		"align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"SITE_ID",
    "content" => GetMessage("WEBDEBUG_OPTIONS_COLUMN_SITE_ID"),
    "sort" => "site_id",
		"align" => "left",
    "default" => true,
  ),
));

// Build items list
while ($arRes = $rsData->NavNext(true, "f_")) {
  $row = &$lAdmin->AddRow($f_ID, $arRes); 
	// ID
	$row->AddViewField("ID", $f_ID);
	// MODULE_ID
	$row->AddViewField("MODULE_ID", $f_MODULE_ID);
	$sHTML = "<select name=\"FIELDS[{$f_ID}][MODULE_ID]\">";
	foreach ($arModules as $ModuleID => $arModule) {
		$sHTML .= "<option value='{$ModuleID}'".($f_MODULE_ID==$ModuleID ? " selected='selected'" : "").">[{$ModuleID}] {$arModule["NAME"]}</option>";
	}
	$sHTML .= "</select>";
	$row->AddEditField("MODULE_ID", $sHTML);
  // NAME
  $row->AddViewField("NAME", $f_NAME);
	$row->AddInputField("NAME",array("SIZE" => "30"));
  // VALUE
  $row->AddViewField("VALUE", "<div style='max-width:500px; word-wrap:break-word;'>".$f_VALUE."</div>");
	$sHTML = '<textarea rows="3" cols="40" name="FIELDS['.$f_ID.'][VALUE]">'.htmlspecialchars($row->arRes["VALUE"]).'</textarea>';
	$row->AddEditField("VALUE", $sHTML);
	// DESCRIPTION
	$row->AddViewField("DESCRIPTION", $f_DESCRIPTION);
	$sHTML = '<textarea rows="3" cols="20" name="FIELDS['.$f_ID.'][DESCRIPTION]">'.htmlspecialchars($row->arRes["DESCRIPTION"]).'</textarea>';
	$row->AddEditField("DESCRIPTION", $sHTML);
	// SITE_ID
	$row->AddViewField("SITE_ID", $f_SITE_ID);
	$sHTML = "<select name=\"FIELDS[{$f_ID}][SITE_ID]\">";
	$sHTML .= "<option value=\"\">".GetMessage("WEBDEBUG_OPTIONS_FILTER_SITE_ID_ANY")."</option>";
	foreach ($arSites as $arSite) {
		$sHTML .= "<option value='{$arSite["ID"]}'".($f_SITE_ID==$arSite["ID"] ? " selected='selected'" : "").">[{$arSite["ID"]}] {$arSite["NAME"]}</option>";
	}
	$sHTML .= "</select>";
	$row->AddEditField("SITE_ID", $sHTML);
	
	// Build context menu
  $arActions = Array();
  $arActions[] = array(
    "ICON" => "edit",
    "DEFAULT"=>true,
    "TEXT" => GetMessage("WEBDEBUG_OPTIONS_CONTEXT_EDIT"),
    "ACTION"=>$lAdmin->ActionRedirect("wdu_option_edit.php?ID={$f_ID}&lang=".LANGUAGE_ID)
  );
  $arActions[] = array(
    "ICON" => "copy",
    "DEFAULT"=>false,
    "TEXT" => GetMessage("WEBDEBUG_OPTIONS_CONTEXT_COPY"),
    "ACTION"=>$lAdmin->ActionRedirect("wdu_option_edit.php?CopyID={$f_ID}&lang=".LANGUAGE_ID)
  );
	$arActions[] = array(
		"ICON" => "delete",
		"DEFAULT"=>false,
		"TEXT" => GetMessage("WEBDEBUG_OPTIONS_CONTEXT_DELETE"),
		"ACTION" => "if(confirm('".sprintf(GetMessage('WEBDEBUG_OPTIONS_CONTEXT_DELETE_CONFIRM'), $f_NAME)."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
	);
  $arActions[] = array("SEPARATOR"=>true);
  if(is_set($arActions[count($arActions)-1], "SEPARATOR")) {
    unset($arActions[count($arActions)-1]);
	}
  $row->AddActions($arActions);
}

// List Footer
$lAdmin->AddFooter(
  array(
    array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
    array("counter"=>true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
  )
);
$lAdmin->AddGroupActionTable(Array(
  "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

// Context menu
$aContext = array(
  array(
    "TEXT" => GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_ADD_NAME"),
    "LINK" => "wdu_option_edit.php?lang=".LANGUAGE_ID,
    "TITLE" => GetMessage("WEBDEBUG_OPTIONS_TOOLBAR_ADD_DESCR"),
    "ICON" => "btn_new",
  ),
);
$lAdmin->AddAdminContextMenu($aContext);

// Start output
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("WEBDEBUG_OPTIONS_APPLICATION_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Output filter
$oFilter = new CAdminFilter(
  $sTableID."_filter",
  array(
		GetMessage("WEBDEBUG_OPTIONS_FILTER_NAME"),
		GetMessage("WEBDEBUG_OPTIONS_FILTER_VALUE"),
		GetMessage("WEBDEBUG_OPTIONS_FILTER_DESCRIPTION"),
		GetMessage("WEBDEBUG_OPTIONS_FILTER_SITE_ID"),
  )
);
?>

<form name="find_form" method="get" action="<?=$APPLICATION->GetCurPage();?>">
	<?$oFilter->Begin();?>
	<tr>
		<td><b><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_MODULE_ID")?>:</b></td>
		<td>
			<select name="find_module_id" title="<?=GetMessage("WEBDEBUG_OPTIONS_FILTER_MODULE_ID_DESCR");?>">
				<option value=""><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_MODULE_ID_ANY");?></option>
				<?foreach ($arModules as $ModuleID => $arModule):?>
					<option value="<?=$ModuleID?>"<?if($find_module_id==$ModuleID):?> selected="selected"<?endif?>>[<?=$ModuleID?>] <?=$arModule["NAME"]?></option>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_NAME")?>:</td>
		<td><input type="text" size="50" maxlength="255" name="find_name" value="<?=htmlspecialchars($find_name)?>" title="<?=GetMessage("WEBDEBUG_OPTIONS_FILTER_NAME_DESCR");?>"/></td>
	</tr>
	<tr>
		<td><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_VALUE")?>:</td>
		<td><input type="text" size="50" maxlength="255" name="find_value" value="<?=htmlspecialchars($find_value)?>" title="<?=GetMessage("WEBDEBUG_OPTIONS_FILTER_VALUE_DESCR");?>"/></td>
	</tr>
	<tr>
		<td><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_DESCRIPTION")?>:</td>
		<td><input type="text" size="50" maxlength="255" name="find_description" value="<?=htmlspecialchars($find_description)?>" title="<?=GetMessage("WEBDEBUG_OPTIONS_FILTER_DESCRIPTION_DESCR");?>"/></td>
	</tr>
	<tr>
		<td><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_SITE_ID")?>:</td>
		<td>
			<select name="find_site_id" title="<?=GetMessage("WEBDEBUG_OPTIONS_FILTER_SITE_ID_DESCR");?>">
				<option value=""><?=GetMessage("WEBDEBUG_OPTIONS_FILTER_SITE_ID_ANY");?></option>
				<?foreach ($arSites as $arSite):?>
					<option value="<?=$arSite["ID"]?>"<?if($find_site_id==$arSite["ID"]):?> selected="selected"<?endif?>>[<?=$arSite["ID"]?>] <?=$arSite["NAME"]?></option>
				<?endforeach?>
			</select>
		</td>
	</tr>
	<?$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form" => "find_form"));?>
	<?$oFilter->End();?>
</form>

<?// Output ?>
<?$lAdmin->DisplayList();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>