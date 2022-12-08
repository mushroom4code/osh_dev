<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

global $DB, $APPLICATION;

$module_id	= "solverweb.sitemap";
$CAT_R		= $APPLICATION->GetGroupRight($module_id);

if ($CAT_R == "D") {
	$APPLICATION->AuthForm(GetMessage('SW_ACCESS_DENIED'));
	return;
} else if (!CModule::IncludeModule($module_id)) {
	include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	\CAdminMessage::ShowMessage(
		array(
			'TYPE' => 'ERROR',
			'MESSAGE' => GetMessage("SW_MODULE_NOT_REGISTERED")
		)
    );
	include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
} else {

	$arSites = SWSitemapEdit::getSiteList();
	$tableID = "tbl_sitemap_xml";
	$oSort = new CAdminSorting($tableID, "ID", "desc");
	$lAdmin = new CAdminList($tableID, $oSort);

	if(($arID = $lAdmin->GroupAction()) && $CAT_R == "W") {
		if($_REQUEST['action_target']=='selected') {
			$arID = array();
			$rsData = SWSitemapList::getEntityAll();
			while($arRes = $rsData->fetch()) {
				$arID[] = $arRes['ID'];
			}
		}

		foreach($arID as $ID) {
			$ID = intval($ID);
			if($ID <= 0)
				continue;

			switch($_REQUEST['action']) {
				case "activate":
				case "deactivate":
					SWSitemapList::activityEntityById($ID,($_REQUEST['action']=="activate"?"Y":"N"));
					break;
				case "delete":
					SWSitemapList::deleteEntityById($ID);
					break;
				case "generate":
					SWSitemapGenerate::Generate($ID);
					break;
			}
		}

		if(isset($return_url) && strlen($return_url)>0)
			LocalRedirect($return_url);
	}
	
	if($CAT_R == "W" && (isset($_REQUEST['save']) && $_REQUEST['save'] != '') && check_bitrix_sessid()) {
		foreach ($_REQUEST['FIELDS'] as $key => $arFields)
			SWSitemapList::setEntityField($key,$arFields);
	}

	$rsData = SWSitemapList::getEntityAll();
	$data = new CAdminResult($rsData, $tableID);
	$data->NavStart();

	$arHeaders = array(
		array("id"=>"ID", "content"=>GetMessage("SW_SITEMAP_ID"), "sort"=>"ID", "default"=>true),
		array("id"=>"ACTIVE", "content"=>GetMessage('SW_SITEMAP_ACTIVE'), "sort"=>"ACTIVE", "default"=>true),
		array("id"=>"AGENT", "content"=>GetMessage('SW_SITEMAP_AGENT'), "sort"=>"AGENT", "default"=>true),
		array("id"=>"NAME", "content"=>GetMessage('SW_SITEMAP_NAME'), "sort"=>"NAME", "default"=>true),
		array("id"=>"SITE_ID", "content"=>GetMessage('SW_SITEMAP_SITE_ID'), "sort"=>"SITE_ID", "default"=>true),
		array("id"=>"TIMESTAMP_X", "content"=>GetMessage('SW_SITEMAP_TIMESTAMP_X'), "sort"=>"TIMESTAMP_X", "default"=>true),
		array("id"=>"LAST_RUN", "content"=>GetMessage('SW_SITEMAP_LAST_RUN'), "sort"=>"LAST_RUN", "default"=>true),
		array("id"=>"RUN", "content"=>"", "default"=>true),
	);

	$lAdmin->AddHeaders($arHeaders);
	$lAdmin->NavText($data->GetNavPrint(GetMessage("PAGES")));

	while($sitemap = $data->NavNext())
	{
		$id = intval($sitemap['ID']);

		$row = &$lAdmin->AddRow($id, $sitemap, "solverweb_sitemap_edit.php?id=".$id."&lang=".LANGUAGE_ID, GetMessage("SW_SITEMAP_EDIT_TITLE"));

		$row->AddViewField("ID", $id);
		if ($CAT_R == "W")
			$row->AddCheckField("ACTIVE");
		if ($sitemap['AGENT'])
			$row->AddField("AGENT", (SWSitemapList::getAgent(array('AGENT'=>$sitemap['AGENT']))?'<a href="agent_edit.php?ID='.$sitemap['AGENT'].'&amp;lang='.LANGUAGE_ID.'" title="'.GetMessage("SW_SITEMAP_EDIT_AGENT").'" target="_blank">'.htmlspecialcharsEx($sitemap['AGENT']).'</a>':''));
		$row->AddField("NAME", '<a href="solverweb_sitemap_edit.php?id='.$id.'&amp;lang='.LANGUAGE_ID.'" title="'.GetMessage("SW_SITEMAP_EDIT_TITLE").'">'.htmlspecialcharsEx($sitemap['NAME']).'</a>');
		if ($CAT_R == "W")
			$row->AddInputField("NAME");
		$row->AddViewField('SITE_ID', '<a href="site_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.$sitemap['SITE_ID'].'">['.$sitemap['SITE_ID'].'] '.$arSites[$sitemap['SITE_ID']]['NAME'].'</a>');
		$row->AddViewField('TIMESTAMP_X', $sitemap['TIMESTAMP_X']);
		$row->AddViewField('LAST_RUN', $sitemap['LAST_RUN']);
		if ($CAT_R == "W")
			$row->AddField("RUN", '<input type="button" class="adm-btn-save" value="'.GetMessage("SW_SITEMAP_RUN").'" onclick="'.$lAdmin->ActionDoGroup($id, "generate").'" name="save" id="sitemap_run_button_'.$sitemap['ID'].'" />');
		
		$row->AddActions(array(
			array(
				"ICON" => "edit",
				"TEXT" => GetMessage("SW_SITEMAP_EDIT"),
				"ACTION" => $lAdmin->ActionRedirect("solverweb_sitemap_edit.php?id=".$id."&lang=".LANGUAGE_ID),
				"DEFAULT" => true,
			),
			array(
				"TEXT" => GetMessage("SW_SITEMAP_".($sitemap["ACTIVE"]=="Y"?'DE':'')."ACTIVATE"),
				"ACTION" => $lAdmin->ActionDoGroup($id, ($sitemap["ACTIVE"]=="Y"?'de':'')."activate"),
				"ONCLICK" => "",
				"DISABLED" => $CAT_R !== "W"
			),
			array('SEPARATOR' => 'Y'),
			array(
				"TEXT" => GetMessage("SW_SITEMAP_COPY"),
				"ACTION" => $lAdmin->ActionRedirect("solverweb_sitemap_edit.php?lang=" . LANGUAGE_ID . "&action=copy&copyID=".$id),
				"ONCLICK" => "",
				"DISABLED" => $CAT_R !== "W"
			),
			array('SEPARATOR' => 'Y'),
			array(
				"ICON"=>"delete",
				"TEXT" => GetMessage("SW_SITEMAP_DELETE"),
				"ACTION" => "if(confirm('".\CUtil::JSEscape(GetMessage('SW_SITEMAP_DELETE_CONFIRM'))."')) ".$lAdmin->ActionDoGroup($id, "delete"),
				"DISABLED" => $CAT_R !== "W"
			),
		));
	}
	if ($CAT_R == "W") {
		$arDDMenu = array();
		$arDDMenu[] = array(
			"TEXT" => "<b>".GetMessage("SW_ADD_SITEMAP_CHOOSE_SITE").":</b>",
			"HTML" => "<b>".GetMessage("SW_ADD_SITEMAP_CHOOSE_SITE").":</b>",
			"ACTION" => false
		);
		foreach($arSites as $arRes) {
			$arDDMenu[] = array(
				"TEXT" => "[".$arRes["LID"]."] ".$arRes["NAME"],
				"LINK" => "solverweb_sitemap_edit.php?id=0&lang=".LANGUAGE_ID."&site_id=".$arRes['LID']
			);
		}
		$aContext = array();
		$aContext[] = array(
			"TEXT"	=> GetMessage("SW_ADD_SITEMAP"),
			"TITLE"	=> GetMessage("SW_ADD_SITEMAP_TITLE"),
			"LINK" => 'solverweb_sitemap_edit.php?id=0&lang='.LANGUAGE_ID,
			"ICON"	=> "btn_new",
			"MENU" => $arDDMenu
		);
		$lAdmin->AddAdminContextMenu($aContext);
		$lAdmin->AddGroupActionTable(array(
			"delete"=>GetMessage("SW_SITEMAP_DELETE"),
			"activate"=>GetMessage("SW_SITEMAP_L_ACTIVATE"),
			"deactivate"=>GetMessage("SW_SITEMAP_L_DEACTIVATE")
		));
	}

	$lAdmin->CheckListMode();

	$APPLICATION->SetTitle(GetMessage("SW_SEO_SITEMAP_TITLE"));

	include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

	$lAdmin->DisplayList();

}
SWSitemapList::getSupportButton();
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");