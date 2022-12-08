<?
use
	\WD\Utilities\FastSql,
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
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$arNavParams = array(
	'nPageSize' => IntVal($_GET['SIZEN_1']),
	'iNumPage' => IntVal($_GET['PAGEN_1']),
);

$ModuleRights = $APPLICATION->GetGroupRight($ModuleID);
if($ModuleRights=="D") {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$sTableID = "WDU_FastSQL";
$oSort = new CAdminSorting($sTableID, "DATE_CREATED", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

// Filter
$FilterArr = Array(
	"find_id",
	"find_active",
	"find_sort",
	"find_query",
	"find_description",
	"find_user_id",
);
$lAdmin->InitFilter($FilterArr);
$arFilter = array();
if(!empty($find_id))
	$arFilter['ID'] = $find_id;
if(in_array($find_active,array('Y','N')))
	$arFilter['ACTIVE'] = $find_active;
if(!empty($find_sort))
	$arFilter['SORT'] = $find_sort;
if(!empty($find_query))
	$arFilter['%QUERY'] = $find_query;
if(!empty($find_description))
	$arFilter['%DESCRIPTION'] = $find_description;
if(!empty($find_user_id))
	$arFilter['USER_ID'] = $find_user_id;

// Processing with group actions
if(($arID = $lAdmin->GroupAction())) {
  if($_REQUEST['action_target']=='selected') {
    $rsData = FastSql::GetList(array($by=>$order), $arFilter, false, $arNavParams);
    while($arRes = $rsData->Fetch()) {
			$arID[] = $arRes['ID'];
		}
  }
  foreach($arID as $ID) {
    $ID = IntVal($ID);
    if(strlen($ID)<=0) continue;
		@set_time_limit(0);
		$DB->StartTransaction();
    switch($_REQUEST['action']) {
			case "delete":
				if(!FastSql::Delete($ID)) {
					$DB->Rollback();
					$lAdmin->AddGroupError('Error deleting', $ID);
				}
				break;
    }
		$DB->Commit();
  }
}

if (!is_array($arFilter)) {
	$arFilter = array();
}
	
// Get items list
$rsData = FastSql::GetList(array($by => $order), $arFilter, false, $arNavParams);
$rsData = new CAdminResult($rsData, $sTableID);
$lAdmin->NavText($rsData->GetNavPrint(''));

// Add headers
$arHeaders = array(
  array(
	  "id" => "ID",
    "content" => GetMessage('WDU_FIELD_ID'),
    "sort" => "ID",
    "align" => "right",
    "default" => true,
  ),
  array(
	  "id" =>"ACTIVE",
    "content" => GetMessage('WDU_FIELD_ACTIVE'),
    "sort" => "ACTIVE",
		"align" => "center",
    "default" => true,
  ),
  array(
	  "id" =>"SORT",
    "content" => GetMessage('WDU_FIELD_SORT'),
    "sort" => "SORT",
		"align" => "right",
    "default" => true,
  ),
  array(
	  "id" =>"QUERY",
    "content" => GetMessage('WDU_FIELD_QUERY'),
    "sort" => "QUERY",
		"align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"DESCRIPTION",
    "content" => GetMessage('WDU_FIELD_DESCRIPTION'),
    "sort" => "DESCRIPTION",
		"align" => "left",
    "default" => true,
  ),
  array(
	  "id" =>"USER_ID",
    "content" => GetMessage('WDU_FIELD_USER_ID'),
    "sort" => "USER_ID",
		"align" => "left",
    "default" => true,
  ),
);
$lAdmin->AddHeaders($arHeaders);

// Build items list
while ($arRes = $rsData->NavNext(true, "f_", false)) {
  $row = &$lAdmin->AddRow($f_ID, $arRes);
	// ID
	$row->AddViewField("ID", "<a href=\"wdu_fastsql_edit.php?ID={$f_ID}&lang=".LANGUAGE_ID."\">{$f_ID}</a>");
	// ACTIVE
	$row->AddViewField("ACTIVE", $f_ACTIVE=='Y'?GetMessage('MAIN_YES'):GetMessage('MAIN_NO'));
	// SORT
	$row->AddViewField("SORT", $f_SORT);
	// QUERY
	$f_QUERY = "<a href=\"wdu_fastsql_edit.php?ID={$f_ID}&lang=".LANGUAGE_ID."\">{$f_QUERY}</a>";
	$row->AddViewField("QUERY", $f_QUERY);
	// DESCRIPTION
	$row->AddViewField("DESCRIPTION", $f_DESCRIPTION);
	// USER_ID
	if ($f_USER_ID==='0') {
		$f_USER_ID = GetMessage('WDU_FIELD_USER_ID_ALL');
	} elseif ($f_USER_ID>0) {
		$resUser = CUser::GetList($UserSortField='ID', $UserSortOrder='ASC', array('ID'=>$f_USER_ID), array('FIELDS'=>array('ID','LOGIN','NAME','LAST_NAME')));
		if ($arUser = $resUser->GetNext(false,false)) {
			$f_USER_ID = '[<a href="/bitrix/admin/user_edit.php?lang=ru&ID='.$arUser['ID'].'">'.$arUser['ID'].'</a>] ('.$arUser['LOGIN'].') '.$arUser['NAME'].' '.$arUser['LAST_NAME'];
		}
	}
	$row->AddViewField("USER_ID", $f_USER_ID);
	
	// Build context menu
  $arActions = array();
  $arActions[] = array(
    "ICON" => "edit",
    "DEFAULT" => true,
    "TEXT" => GetMessage('WDU_CONTEXT_EDIT'),
    "ACTION"=>$lAdmin->ActionRedirect("wdu_fastsql_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID)
  );
  $arActions[] = array(
		"SEPARATOR" => true,
	);
	$arActions[] = array(
		"ICON" => "delete",
		"DEFAULT" => false,
		"TEXT" => GetMessage('WDU_CONTEXT_DELETE'),
		"ACTION" => "if(confirm('".GetMessage('WDU_CONTEXT_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, 'delete')
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

// Context menu
global $APPLICATION;
$aContext = array(
  array(
    "TEXT" => GetMessage('WDU_CONTEXT_ADD'),
    "LINK" => "wdu_fastsql_edit.php?lang=".LANGUAGE_ID,
    "ICON" => "btn_new",
  ),
);
$lAdmin->AddAdminContextMenu($aContext);

// Start output
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage('WDU_PAGE_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Output filter
$oFilter = new CAdminFilter(
  $sTableID.'_filter',
  array(
		'ACTIVE' => GetMessage('WDU_FILTER_ACTIVE'),
		'SORT' => GetMessage('WDU_FILTER_SORT'),
		'QUERY' => GetMessage('WDU_FILTER_QUERY'),
		'DESCRIPTION' => GetMessage('WDU_FILTER_DESCRIPTION'),
		'USER_ID' => GetMessage('WDU_FILTER_USER_ID'),
  )
);
?>

<form name="find_form" method="get" action="wdu_fastsql_list.php">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>" />
	<input type="hidden" name="filter" value="Y" />
	<?$oFilter->Begin();?>
	<tr>
		<td><b>ID:</b></td>
		<td><input type="text" size="25" name="find_id" value="<?=htmlspecialchars($find_id)?>" /></td>
	</tr>
	<tr>
		<td><?=GetMessage('WDU_FILTER_SORT');?>:</td>
		<td><input type="text" size="50" maxlength="255" name="find_sort" value="<?=htmlspecialchars($find_sort)?>" /></td>
	</tr>
	<tr>
		<td><?=GetMessage('WDU_FILTER_ACTIVE');?>:</td>
		<td>
			<?
			$arSelect = array(
				'reference' => array(
					GetMessage('MAIN_YES'),
					GetMessage('MAIN_NO'),
				),
				'reference_id' => array('Y','N')
			);
			print SelectBoxFromArray('find_active', $arSelect, $find_active, GetMessage('MAIN_ALL'), '');
			?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('WDU_FILTER_QUERY');?>:</td>
		<td><input type="text" size="50" maxlength="255" name="find_query" value="<?=htmlspecialchars($find_query)?>" /></td>
	</tr>
	<tr>
		<td><?=GetMessage('WDU_FILTER_DESCRIPTION');?>:</td>
		<td><input type="text" size="50" maxlength="255" name="find_description" value="<?=htmlspecialchars($find_description)?>" /></td>
	</tr>
	<tr>
		<td><?=GetMessage('WDU_FILTER_USER_ID');?>:</td>
		<td><?echo FindUserID("find_user_id", $find_user_id, "", "find_form", "5", "", " ... ", "", "");?></td>
	</tr>
	<?$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form" => "find_form"));?>
	<?$oFilter->End();?>
</form>

<?// Output ?>
<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>