<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

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
$bAutocomplete = defined('BT_UT_AUTOCOMPLETE') && (BT_UT_AUTOCOMPLETE == 1);

/* copy element */
$bCopy = false;
$copyID = 0;
if (!$bAutocomplete)
	$bCopy = (isset($_REQUEST['action']) && $_REQUEST["action"] == "copy");
$copyID = (isset($_REQUEST['copyID']) ? (int)$_REQUEST['copyID'] : 0);

$id = (int)$_GET['id'];

if ($id > 0) {
	$arSitemap = SWSitemapList::getEntityById($id)->fetch();
} else if ($bCopy) {
	$arSitemap = SWSitemapList::getEntityById($copyID)->fetch();
}

$SITE_ID = ($_GET['site_id']!=''?$_GET['site_id']:$arSitemap['SITE_ID']);

if ((!is_array($arSitemap) && $id !== 0) || strlen($SITE_ID) === 0) {
	include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	SWSitemapList::id404();
}

if ($id > 0 && ($_REQUEST['action'] == 'delete' || $_REQUEST['action'] == 'generate') && check_bitrix_sessid()) {
	if ($CAT_R == "W") {
		if ($_REQUEST['action'] == 'delete') {
			SWSitemapList::deleteEntityById($id);
			LocalRedirect(BX_ROOT."/admin/solverweb_sitemap_list.php?lang=" . LANGUAGE_ID);
		} else if ($_REQUEST['action'] == 'generate') {
			SWSitemapGenerate::Generate($id);
			LocalRedirect(BX_ROOT."/admin/solverweb_sitemap_edit.php?id=" . $id . "&lang=" . LANGUAGE_ID);
		}
	} else {
		include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
		\CAdminMessage::ShowMessage(
			array(
				'TYPE' => 'ERROR',
				'MESSAGE' => GetMessage("SW_ACCESS_DENIED")
			)
		);
	}
}
if ($bCopy) {
	unset($arSitemap['ID']);
	unset($arSitemap['AGENT']);
	unset($arSitemap['LAST_RUN']);
}

if (is_array($arSitemap))
	$arSitemap['SETTINGS'] = unserialize($arSitemap['SETTINGS']);

if (empty($arSitemap['SETTINGS']["sitemap_filename"]))
	$arSitemap['SETTINGS']["sitemap_filename"] = 'sitemap';

if (
	(
		(isset($_REQUEST['save']) && $_REQUEST['save'] != '') ||
		(isset($_REQUEST['apply']) && $_REQUEST['apply'] != '')
	) &&
	check_bitrix_sessid()
) {
	if ($CAT_R == "W" && is_array($arSitemap)) {
		if ($arEntity = SWSitemapList::saveEntityByEntity($arSitemap))
			$arSitemap = $arEntity;
	} else {
		include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
		\CAdminMessage::ShowMessage(
			array(
				'TYPE' => 'ERROR',
				'MESSAGE' => GetMessage("SW_ACCESS_DENIED")
			)
		);
	}
}

$bLogical = 'Y' == ($arSitemap['SETTINGS']['log']?:$_REQUEST['log']);

// load directory structure
if(isset($_GET['dir']) && check_bitrix_sessid()) {
	$bLogical = $_GET['log'] == 'Y';
	$dir = $_GET['dir'];
	$depth = intval($_GET['depth']);
	$checked = $_GET['checked'] == 'Y';

	$APPLICATION->RestartBuffer();

	if(!is_array($arSitemap['SETTINGS']['dir']))
		$arSitemap['SETTINGS']['dir'] = array();
	if(!is_array($arSitemap['SETTINGS']['file']))
		$arSitemap['SETTINGS']['file'] = array();

	$arChecked = array_merge($arSitemap['SETTINGS']['dir'], $arSitemap['SETTINGS']['file']);

	echo SWSitemapEdit::getDirStructure($bLogical, $SITE_ID, $dir, $depth, $checked, $arChecked, $arSitemap['SETTINGS']);
	die();
}

$APPLICATION->SetTitle(GetMessage("SW_SITEMAP_TITLE"));

include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
global $error;
if ($error)
	echo CAdminMessage::ShowOldStyleError($error->GetErrorText());
// echo BeginNote(),GetMessage("SW_NOTICE"),EndNote();

$aMenu = array();
if ( !$bAutocomplete ) {
	$aMenu = array(
		array(
			"TEXT"	=> GetMessage('SW_LIST'),
			"LINK"	=> "solverweb_sitemap_list.php?lang=" . LANGUAGE_ID,
			"ICON"	=> "btn_list",
		)
	);
	if (!$bCopy && $CAT_R == "W") {
		$aMenu[] = array(
			"TEXT"	=> GetMessage("SW_SITEMAP_RUN"),
			"TITLE"	=> GetMessage("SW_SITEMAP_RUN_TITLE"),
			"LINK"	=> "solverweb_sitemap_edit.php?lang=" . LANGUAGE_ID . "&action=generate&id=" . $id . '&' . bitrix_sessid_get(),
			"ICON"	=> "btn_add",
		);
		$aMenu[] = array(
			"TEXT"	=> GetMessage("SW_COPY_ELEMENT"),
			"TITLE"	=> GetMessage("SW_COPY_ELEMENT_TITLE"),
			"LINK"	=> "solverweb_sitemap_edit.php?lang=" . LANGUAGE_ID . "&action=copy&copyID=" . $id . '&' . bitrix_sessid_get(),
			"ICON"	=> "btn_copy",
		);
	}
	if($id > 0 && !$bCopy && $CAT_R == "W") {
		$arSubMenu = array();
		$arSubMenu[] = array(
			"TEXT" => GetMessage("SW_ADD_SITEMAP"),
			"LINK" => 'solverweb_sitemap_edit.php?lang=' . LANGUAGE_ID,
			"ICON" => 'edit',
		);
		
		$urlDelete = "solverweb_sitemap_edit.php?lang=" . LANGUAGE_ID . '&action=delete';
		$urlDelete .= '&id=' . $id;
		$urlDelete .= '&' . bitrix_sessid_get();
		$arSubMenu[] = array(
			"TEXT"		=> GetMessage("SW_SITEMAP_DELETE"),
			"ACTION"	=> "if(confirm('" . GetMessageJS("SW_SITEMAP_DELETE_CONFIRM") . "'))window.location='" . CUtil::JSEscape($urlDelete) . "';",
			"ICON"		=> 'delete',
		);

		if (!empty($arSubMenu))
		{
			$aMenu[] = array("SEPARATOR"=>"Y");
			$aMenu[] = array(
				"TEXT"	=> GetMessage('SW_SITEMAP_ACTIONS'),
				"TITLE"	=> GetMessage('SW_SITEMAP_ACTIONS_TITLE'),
				"MENU"	=> $arSubMenu,
				"ICON"	=> 'btn_new'
			);
		}
	}
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}

$arSites = SWSitemapEdit::getSiteList(array('ID' => $SITE_ID));
?>
<form method="POST" action="<?=POST_FORM_ACTION_URI?>" name="sitemap_form" id="sitemap_form" enctype="multipart/form-data">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="id" value="<?=$id?>">
	<input type="hidden" name="LID" value="<?=$SITE_ID?>">
	<input type="hidden" name="site_dir" value="<?=$arSites[$SITE_ID]['DIR']?>">
	<input type="hidden" name="site_doc_root" value="<?=$arSites[$SITE_ID]['DOC_ROOT']?>">
<?
$tabControl = new CAdminTabControl("tabControl", SWSitemapEdit::getNumberOfTabs(5));
$tabControl->Begin();
$tabControl->BeginNextTab();
if ($arSitemap["ID"]) {
?>
<tr>
	<td width="40%" class="adm-detail-content-cell-l">ID:</td>
	<td width="60%"><?=htmlspecialcharsEx($arSitemap["ID"])?></td>
</tr>
<?}?>
<tr>
	<td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("SW_SITEMAP_ACTIVE")?>:</td>
	<td width="60%"><input type="checkbox" name="ACTIVE"<?=($arSitemap["ACTIVE"]=='Y'?' checked="checked"':'')?> value="Y"></td>
</tr>
<?if ($arSitemap["AGENT"]) {?>
<tr>
	<td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("SW_SITEMAP_AGENT")?>:</td>
	<td width="60%"><? if ($arSitemap['AGENT']) echo (SWSitemapList::getAgent(array('AGENT'=>$arSitemap['AGENT']))?'<a href="agent_edit.php?ID='.$arSitemap['AGENT'].'&amp;lang='.LANGUAGE_ID.'" title="'.GetMessage("SW_SITEMAP_EDIT_AGENT").'" target="_blank">'.htmlspecialcharsEx($arSitemap['AGENT']).'</a>':'');?></td>
</tr>
<?}?>
<tr class="adm-detail-required-field">
	<td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("SW_SITEMAP_NAME")?>:</td>
	<td width="60%"><input type="text" name="name" value="<?=htmlspecialcharsEx($arSitemap["NAME"])?>" style="width:70%"></td>
</tr>
<tr class="adm-detail-required-field">
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_LID')?>:</td>
	<td><div class="adm-list-item">
			<div class="adm-list-label"><?='<a href="site_edit.php?LID='.$SITE_ID.'&amp;lang='.LANGUAGE_ID.'" title="'.GetMessage("SW_SITEMAP_EDIT_SITE").'" target="_blank">'.'['.htmlspecialcharsex($SITE_ID).']&nbsp;'.htmlspecialcharsex($arSites[$SITE_ID]["NAME"]).'</a>'?></div>
		</div><?//=SWSitemapEdit::SelectBoxMultiRadio("LID", $SITE_ID);?></td>
</tr>
<tr class="adm-detail-required-field">
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_DOMEN')?>:</td>
	<td><?
	$domains = array();
	if (!is_array($arSitemap['SETTINGS']['domains'])) $arSitemap['SETTINGS']['domains'] = [];	  
	foreach ($arSites[$SITE_ID]['DOMAINS'] as $domain)
		if (strlen(trim($domain)) > 0)
			$domains[$domain] = trim($domain);
	$domains[$_SERVER['SERVER_NAME']] = $_SERVER['SERVER_NAME'];
	$https = isset($arSitemap['SETTINGS']['https'])?$arSitemap['SETTINGS']['https']:CMain::IsHTTPS();
	$site_dir = ($arSites[$SITE_ID]['DIR']?htmlspecialcharsEx($arSites[$SITE_ID]['DIR']):'/');
	$mapDomain = ($https?'https':'http').'://'.$arSitemap['SETTINGS']['domains'][0];
	$mapUri = $site_dir.htmlspecialcharsEx($arSitemap['SETTINGS']["sitemap_filename"]).'.xml'.($arSitemap["SETTINGS"]['gzip']=='Y'?'.gz':'');
	if ($arSitemap["SETTINGS"]['underscore'] == 'Y')
		$mapUri = str_replace('_','-',$mapUri);
	$mapUrl = $mapDomain.$mapUri;
	?><div>
	<select name="https" onchange="mapLinkChange(this)">
		<option value="0"<?=!$https? ' selected="selected"' : ''?>>http</option>
		<option value="1"<?=$https? ' selected="selected"' : ''?>>https</option>
	</select> <?
	?><b>://</b> <?
	?><select name="domains[]" onchange="mapLinkChange(this)"<?/*/?> multiple<?/*/?>><?
		foreach ($domains as $domain)
			echo '<option value="'.$domain.'"'.(in_array($domain,$arSitemap['SETTINGS']['domains'])?' selected="selected"':'').'>'.$domain.'</option>';
	?></select> <?
	?><b><?=$site_dir?></b> <?
	?><input type="text" name="sitemap_filename" value="<?=htmlspecialcharsEx($arSitemap['SETTINGS']["sitemap_filename"])?>" oninput="mapLinkChange(this)" onchange="mapLinkChange(this)" />.xml<span id="gzip_suffix">.gz</span></div><?
	?>
	</td>
</tr>
<tr>
	<td></td>
	<td><div style="margin:-15px 0"><?=BeginNote(),GetMessage("SW_SITEMAP_FILE_ATTENTION", array('#FILE_URL#' => $mapUrl)),EndNote();?></div></td>
</tr>
<tr>
	<td class="adm-detail-content-cell-l"><span id="convert_hint"></span> <?=GetMessage('SW_CONVERT')?>:<script>BX.ready(function(){BX.hint_replace(BX('convert_hint'), '<?=GetMessage('SW_CONVERT_HINT')?>');});</script></td>
	<td width="60%"><input type="checkbox" name="convert"<?=($arSitemap["SETTINGS"]['convert']=='Y'?' checked="checked"':'')?> value="Y"></td>
</tr>
<tr>
	<td class="adm-detail-content-cell-l"><span id="underscore_hint"></span> <?=GetMessage('SW_UNDERSCORE')?>:<script>BX.ready(function(){BX.hint_replace(BX('underscore_hint'), '<?=GetMessage('SW_UNDERSCORE_HINT')?>');});</script></td>
	<td width="60%"><input type="checkbox" name="underscore"<?=($arSitemap["SETTINGS"]['underscore']=='Y'?' checked="checked"':'')?> value="Y" onchange="mapLinkChange(this)"></td>
</tr>
<tr>
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_GZIP')?>:</td>
	<td width="60%">
		<script>
		<? if ($arSitemap["SETTINGS"]['gzip']!='Y') { ?>BX.ready(function(){ BX.toggle(BX('gzip_suffix')); });<?}?>
		function changeMapLink(input) {
			BX.toggle(BX('gzip_suffix'));
		}
		function mapLinkChange(input) {
			let p			= BX.findParent(input, {tagName:'TABLE'}),
				https		= BX.findChild(p, {tagName:'select', property:{name:'https'}}, true),
				domain		= BX.findChild(p, {tagName:'select', property:{name:'domains[]'}}, true),
				filename	= BX.findChild(p, {tagName:'INPUT', property:{name:'sitemap_filename'}}, true),
				gzip		= BX.findChild(p, {tagName:'INPUT', property:{name:'gzip'}}, true).checked,
				underscore	= BX.findChild(p, {tagName:'INPUT', property:{name:'underscore'}}, true).checked;
				
			let dom = (parseInt(https.value) ? 'https':'http') + '://' + domain.value;
			let uri = '<?=$site_dir?>' + filename.value + '.xml' + (gzip ? '.gz' : '');
			if (underscore)
				uri = uri.replace('_','-');

			BX('maplink').href = dom + uri;
		}
		</script>
		<input type="checkbox" name="gzip"<?=($arSitemap["SETTINGS"]['gzip']=='Y'?' checked="checked"':'')?> value="Y" onchange="mapLinkChange(this);changeMapLink(this)"></td>
</tr>
<tr>
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_SPLIT')?>:</td>
	<td width="60%">
		<script>
		function splitCheckInput(input) {
			var p = BX.findParent(input, {tagName:'TABLE'}),
				aspro = BX.findChild(p, {tagName:'INPUT', property:{name:'aspro_sm'}}, true),
				zverushki = BX.findChild(p, {tagName:'INPUT', property:{name:'zverushki_sm'}}, true),
				numinp = BX.findNextSibling(BX(input), {tag: 'input'});
			numinp.disabled = !input.checked;
			if (!!aspro && aspro.checked && !input.checked)
				BX.fireEvent(aspro, 'click');
			if (!!zverushki && zverushki.checked && !input.checked)
				BX.fireEvent(zverushki, 'click');
		}
		</script>
		<input type="checkbox" name="split_f" value="Y"<?=$arSitemap["SETTINGS"]['split_f']=='Y'?' checked="checked"':''?> onchange="splitCheckInput(this)">(<input type="number" name="split" value="<?=$arSitemap["SETTINGS"]['split']?:'50000'?>" min="1" max="50000"<?=$arSitemap["SETTINGS"]['split_f']=='Y'?'':' disabled="disabled"'?> style="text-align:right"> <?=GetMessage('SW_SPLIT_L')?>)</td>
</tr>
<?
if (class_exists('\Aspro\Max\Smartseo\Models\SmartseoSitemapTable')) {
	$aspro_filter = [
		'ACTIVE' => 'Y',
		'SITE_ID' => $SITE_ID
	];
	if ($arSitemap['SETTINGS']['domains'])
		$aspro_filter['DOMAIN'] = $arSitemap['SETTINGS']['domains'];
	$rsAsproSMRows = \Aspro\Max\Smartseo\Models\SmartseoSitemapTable::getList([
		  'select' => [
			  'ID',
			  'NAME'
		  ],
		  'filter' => $aspro_filter,
		  'cache' => [
			  'ttl' => \Aspro\Max\Smartseo\Models\SmartseoSitemapTable::getCacheTtl(),
		  ]
	]);
	$arAsproSMs = [];
	while ($arAsproSMRows = $rsAsproSMRows->fetch())
		$arAsproSMs[] = $arAsproSMRows;
	if (!empty($arAsproSMs)) {
	?>
<tr>
	<td class="adm-detail-content-cell-l"><span id="aspro_sm_hint"></span> <?=GetMessage('SW_ASPRO_SM')?>:<script>BX.ready(function(){BX.hint_replace(BX('aspro_sm_hint'), '<?=GetMessage('SW_ASPRO_SM_HINT')?>');});</script></td>
	<td width="60%">
		<script>
		function asproSMCheckInput(input) {
			var p = BX.findParent(input, {tagName:'TABLE'}),
				split = BX.findChild(p, {tagName:'INPUT', property:{name:'split_f'}}, true),
				valselect = BX.findNextSibling(BX(input), {tag: 'select'});
			valselect.disabled = !input.checked;
			if (input.checked && !split.checked)
				BX.fireEvent(split, 'click');
		}
		</script>
		<input type="checkbox" name="aspro_sm" value="Y"<?=$arSitemap["SETTINGS"]['aspro_sm']=='Y'?' checked="checked"':''?> onchange="asproSMCheckInput(this)">
		<select class="" name="aspro_sm_val" title="<?=GetMessage("SW_ASPRO_SM_VAL")?>"<?=$arSitemap["SETTINGS"]['aspro_sm']!='Y'?' disabled="disabled"':''?>>
		<? foreach ($arAsproSMs as $asproSM) { ?>
			<option value="<?=$asproSM['ID']?>"<?=($arSitemap["SETTINGS"]['aspro_sm_val']==$asproSM['ID']?' selected="selected"':'')?>><?=$asproSM['NAME'].' ('.$asproSM['ID'].')'?></option>
		<? } ?>
		</select>
	</td>
</tr>
	<?
	}
}

if (class_exists('\Sotbit\Seometa\SeometaUrlTable')) {
	?>
<tr>
	<td class="adm-detail-content-cell-l"><span id="sotbit_sm_hint"></span> <?=GetMessage('SW_SOTBIT_SM')?>:<script>BX.ready(function(){BX.hint_replace(BX('sotbit_sm_hint'), '<?=GetMessage('SW_SOTBIT_SM_HINT')?>');});</script></td>
	<td width="60%">
		<input type="checkbox" name="sotbit_sm" value="Y"<?=$arSitemap["SETTINGS"]['sotbit_sm']=='Y'?' checked="checked"':''?>>
		<?=SWSitemapEdit::showFreqSelect('sotbit[0][freq]', $arSitemap['SETTINGS']['sotbit'][0]['freq'], false, false); ?>
		<?=SWSitemapEdit::showModSelect('sotbit[0][mod]', $arSitemap['SETTINGS']['sotbit'][0]['mod'], false); ?>
		<?=SWSitemapEdit::staticAddCellPrior('sotbit[0][prior]', ($arSitemap['SETTINGS']['sotbit'][0]['prior']?:'0.5'), false); ?>
	</td>
</tr>
	<?

}

if (class_exists('\Zverushki\Seofilter\Agent')) {
	?>
<tr>
	<td class="adm-detail-content-cell-l"><span id="zverushki_sm_hint"></span> <?=GetMessage('SW_ZVERUSHKI_SM')?>:<script>BX.ready(function(){BX.hint_replace(BX('zverushki_sm_hint'), '<?=GetMessage('SW_ZVERUSHKI_SM_HINT')?>');});</script></td>
	<td width="60%">
		<script>
		function zverushkiSMCheckInput(input) {
			var p = BX.findParent(input, {tagName:'TABLE'}),
				split = BX.findChild(p, {tagName:'INPUT', property:{name:'split_f'}}, true);
			if (input.checked && !split.checked)
				BX.fireEvent(split, 'click');
		}
		</script>
		<input type="checkbox" name="zverushki_sm" value="Y"<?=$arSitemap["SETTINGS"]['zverushki_sm']=='Y'?' checked="checked"':''?> onchange="zverushkiSMCheckInput(this)">
	</td>
</tr>
	<?

}
?>


<? $tabControl->BeginNextTab(); ?>
<tr>
	<td>
<?
$startDir = htmlspecialcharsEx($arSites[$SITE_ID]['DIR']);
$bChecked = isset($arSitemap['SETTINGS']['dir']) && isset($arSitemap['SETTINGS']['dir'][$startDir]) && isset($arSitemap['SETTINGS']['dir'][$startDir]['active'])
	? $arSitemap['SETTINGS']['dir'][$startDir]['active'] == 'Y'
	: false;
?>
<tr>
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_MASS_ACTION')?> (<?=GetMessage('SW_TAB_2')?>):</td>
	<td>
		<?=SWSitemapEdit::showFreqSelect('file_mass[freq]', false, false); ?>
		<?=SWSitemapEdit::showModSelect('file_mass[mod]', false, false); ?>
		<?=SWSitemapEdit::staticAddCellPrior('file_mass[prior]', '0.5', false); ?>
		<input type="button" value="<?=GetMessage('SW_SET')?>" title="<?=GetMessage('SW_SET')?>" class="adm-btn-save" onclick="fileMypeChange(this)">
	</td>
</tr>
<tr>
	<td colspan="2"><hr></td>
</tr>
<tr>
	<td width="40%" valign="top"><?=GetMessage('SW_SITEMAP_STRUCT_TYPE')?>:</td>
	<td width="60%">
		<style>
		.sitemap-dir-item { position: relative }
		.sitemap-dir-item-text {
			display: inline-block;
			width: 80%;
			height: auto;
			min-height: 20px;
			position: relative;
			top: 2px;
			left: 20px;
		}
		.sitemap-dir-item-children {
			display: none;
			margin-left: 20px
		}
		.sitemap-tree-icon-iblock, .sitemap-tree-icon {
			background: url("/bitrix/panel/main/images/bx-admin-sprite.png") no-repeat scroll -7px -238px rgba(0, 0, 0, 0);
			display: inline-block;
			width: 20px;
			height: 20px;
			cursor: pointer;
		}
		.sitemap-tree-icon {
			position: absolute;
			top: 0;
			left: 0;
		}
		.sitemap-dir-item-text input[type="checkbox"]:checked ~ .item_props,
		.adm-cell-iblock input[type="checkbox"]:checked ~ .item_props {
			display: block;
			margin: 2px 0 3px;
		}
		.sitemap-opened { background-position: -7px -216px }
		.sitemap-logic-switch { cursor: pointer }
		.item_props { display: none }
		#log_Y,
		#log_N { margin-left: 0 }
		</style>
		<script>
		var loadedDirs = {};
		function loadDir(bLogical, sw, dir, div, depth, checked, chng) {
			div = 'subdirs_' + div;
			if(!!sw && BX.hasClass(sw, 'sitemap-opened')) {
				BX(div).style.display = 'none';
				BX.removeClass(sw, 'sitemap-opened')
			}
			else if (div != 'subdirs_<?=$startDir?>' && !!loadedDirs[div]) {
				if(sw)
				{
					BX.addClass(sw, 'sitemap-opened');
				}
				BX(div).style.display = 'block';
			} else {
				if (chng)
					BX.ajax.get('<?=$APPLICATION->GetCurPageParam('', array('dir', 'depth'))?>', {dir:dir,depth:depth,checked:checked?'Y':'N',log:bLogical?'Y':'N',sessid:BX.bitrix_sessid()}, function(res) {
						BX(div).innerHTML = res;
						BX(div).style.display = 'block';
						BX.addClass(sw, 'sitemap-opened');
						loadedDirs[div] = true;
						if (typeof BX.adminFormTools !== "undefined")
							BX.adminFormTools.modifyFormElements(BX(div));
					});
				else {
					BX(div).style.display = 'block';
					BX.addClass(sw, 'sitemap-opened');
					loadedDirs[div] = true;
					if (typeof BX.adminFormTools !== "undefined")
						BX.adminFormTools.modifyFormElements(BX(div));
				}
			}

			BX.onCustomEvent('onAdminTabsChange');
		}

		var bChanged = false;
		function switchLogic(l) {
			if(!bChanged || confirm('<?=CUtil::JSEscape(GetMessage('SW_SITEMAP_LOGIC_WARNING'))?>'))
			{
				loadDir(l, null, '<?=$startDir?>', '<?=$startDir?>', 0, /*BX('dir_<?=$startDir?>').checked*/ false, true);
				bChanged = false;
			}
			else
			{
				BX('log_' +(l ? 'N' : 'Y')).checked = true;
			}
		}

		function checkAll(div, v) {
			bChanged = true;
			_check_all(div, {tagName:'INPUT',property:{type:'checkbox'}}, v);
		}

		function _check_all(div, isElement, v) {
			var c = BX.findChildren(BX('subdirs_' + div), isElement, true);
			for(var i = 0; i < c.length; i++) {
				c[i].checked = v;
			}
		}
		function fileMypeChange(el) {
			var p = BX.findParent(el, {tagName:'TABLE'}),
				sfreq = BX.findPreviousSibling(el, {tagName:'SELECT',property:{name:'file_mass[freq]'}}).value,
				smod = BX.findPreviousSibling(el, {tagName:'SELECT',property:{name:'file_mass[mod]'}}).value,
				sprior = BX.findPreviousSibling(el, {tagName:'INPUT',property:{name:'file_mass[prior]'}}).value,
				freq = BX.findChildren(p, {tagName:'SELECT',className:'input_freq'}, true),
				mod = BX.findChildren(p, {tagName:'SELECT',className:'input_mod'}, true),
				prior = BX.findChildren(p, {tagName:'INPUT',className:'input_prior'}, true);
			for(var i = 0; i < freq.length; i++) {
				freq[i].value = sfreq;
			}
			for(var i = 0; i < mod.length; i++) {
				mod[i].value = smod;
			}
			for(var i = 0; i < prior.length; i++) {
				prior[i].value = sprior;
			}
		}
		</script>
		<input type="radio" name="log" id="log_Y" value="Y"<?=$bLogical ? ' checked="checked"' : ''?> onclick="switchLogic(true)" /><label for="log_Y"><?=GetMessage('SW_SITEMAP_STRUCT_TYPE_Y')?></label><br />
		<input type="radio" name="log" id="log_N" value="N"<?=$bLogical ? '' : ' checked="checked"'?> onclick="switchLogic(false)" /><label for="log_N"><?=GetMessage('SW_SITEMAP_STRUCT_TYPE_N')?></label>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?=GetMessage('SW_SITEMAP_STRUCTURE')?>: </td>
	<td width="60%">
		<input type="hidden" name="dir[<?=$startDir?>][active]" value="N" />
		<input type="checkbox" name="dir[<?=$startDir?>][active]" id="dir_<?=$startDir?>"<?=$bChecked ? ' checked="checked"' : ''?> value="Y" onclick="checkAll('<?=$startDir?>', this.checked);" />&nbsp;<label for="dir_<?=$startDir?>"><?=$startDir?></label>
		<div id="subdirs_<?=$startDir?>">
		<?
		if(is_array($arSitemap['SETTINGS']['file'])) {
			foreach($arSitemap['SETTINGS']['file'] as $dir => $value) {
		?>
			<input type="hidden" name="file[<?=htmlspecialcharsEx($dir);?>][active]" value="<?=$value=='N'?'N':'Y'?>" />
		<?
			}
		} else {
			$arSitemap['SETTINGS']['file'] = array();
		}
		if(is_array($arSitemap['SETTINGS']['dir'])) {
			foreach($arSitemap['SETTINGS']['dir'] as $dir => $value) {
				if($dir != $startDir) {
		?>
			<input type="hidden" name="dir[<?=htmlspecialcharsEx($dir);?>][active]" value="<?=$value=='N'?'N':'Y'?>" />
		<?
				}
			}
		} else {
			$arSitemap['SETTINGS']['dir'] = array();
		}
		$arChecked = array_merge($arSitemap['SETTINGS']['dir'], $arSitemap['SETTINGS']['file']);

		echo SWSitemapEdit::getDirStructure($bLogical, $SITE_ID, $startDir, 1, false, $arChecked, $arSitemap['SETTINGS']);
		?>
	</td>
</tr>

<? $tabControl->BeginNextTab(); ?>
<tr>
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_MASS_ACTION')?> (<?=GetMessage('SW_TAB_3')?>):</td>
	<td>
		<?=SWSitemapEdit::showFreqSelect('iblock_mass[freq]', false, false); ?>
		<?=SWSitemapEdit::showModSelect('iblock_mass[mod]', false, false, true); ?>
		<?=SWSitemapEdit::staticAddCellPrior('iblock_mass[prior]', '0.5', false); ?>
		<input type="button" value="<?=GetMessage('SW_SET')?>" title="<?=GetMessage('SW_SET')?>" class="adm-btn-save" onclick="iblockMypeChange(this)">
	</td>
</tr>
<tr>
	<td colspan="2"><hr></td>
</tr>
<tr>
	<td width="40%" valign="top"><?=GetMessage('SW_SITEMAP_STRUCT_TYPE')?>:</td>
	<td width="60%">
	<? $arIBlocks = SWSitemapList::getIblocksByType($SITE_ID);
	foreach ($arIBlocks as $type => $iblocks) {
		?><div class="adm-cell-iblock-type">
		<input type="checkbox" name="iblock_type[<?=$type?>][active]" id="iblock_<?=$type?>" value="Y" onchange="iblockTypeSelect(this)" />
		<label for="iblock_<?=$type?>" class="adm-submenu-item-name-link-text" style="cursor:default"><?=$type?></label>
		<a class="adm-submenu-item-name-link" href="iblock_admin.php?type=<?=$type?>&lang=<?=LANGUAGE_ID?>&admin=N" style="display:inline-block;padding:0 0 0 5px;vertical-align:middle;" target="_blank"><span class="adm-submenu-item-link-icon iblock_menu_icon_types"></span></a>
		<div class="adm-cell-iblocks"><?
		foreach ($iblocks as $iblock) {
			$check = $arSitemap['SETTINGS']['iblock'][$iblock['ID']]['active'] == 'Y';
			?>
			<div class="adm-cell-iblock">
				<input type="checkbox" name="iblock[<?=$iblock['ID']?>][active]" id="iblock_<?=$iblock['ID']?>" data-type="iblock_checkbox" value="Y"<?=$check? ' checked="checked"' : ''?> /><label for="iblock_<?=$iblock['ID']?>"><a style="padding-left:3px" href="iblock_list_admin.php?IBLOCK_ID=<?=$iblock['ID']?>&type=<?=$iblock['TYPE']?>&lang=<?=$iblock['TYPE']?>" target="_blank"><span><?='['.$iblock['ID'].']'?></span></a> <?=$iblock['NAME']?></label>
				<div class="item_props">
					<?=SWSitemapEdit::showFreqSelect('iblock['.$iblock['ID'].'][freq]', $arSitemap['SETTINGS']['iblock'][$iblock['ID']]['freq'], $check); ?>
					<?=SWSitemapEdit::showModSelect('iblock['.$iblock['ID'].'][mod]', $arSitemap['SETTINGS']['iblock'][$iblock['ID']]['mod'], $check, true); ?>
					<?=SWSitemapEdit::staticAddCellPrior('iblock['.$iblock['ID'].'][prior]', (is_numeric($arSitemap['SETTINGS']['iblock'][$iblock['ID']]['prior'])?$arSitemap['SETTINGS']['iblock'][$iblock['ID']]['prior']:'0.5'), $check); ?>
				</div>
			</div>
			<?
			
		}
		?></div>
		</div>
		<style>.adm-cell-iblock-type{padding-bottom:10px}</style>
		<script>
		function iblockMypeChange(el) {
			var p = BX.findParent(el, {tagName:'TABLE'}),
				sfreq = BX.findPreviousSibling(el, {tagName:'SELECT',property:{name:'iblock_mass[freq]'}}).value,
				smod = BX.findPreviousSibling(el, {tagName:'SELECT',property:{name:'iblock_mass[mod]'}}).value,
				sprior = BX.findPreviousSibling(el, {tagName:'INPUT',property:{name:'iblock_mass[prior]'}}).value,
				freq = BX.findChildren(p, {tagName:'SELECT',className:'input_freq'}, true),
				mod = BX.findChildren(p, {tagName:'SELECT',className:'input_mod'}, true),
				prior = BX.findChildren(p, {tagName:'INPUT',className:'input_prior'}, true);
			for(var i = 0; i < freq.length; i++) {
				freq[i].value = sfreq;
			}
			for(var i = 0; i < mod.length; i++) {
				mod[i].value = smod;
			}
			for(var i = 0; i < prior.length; i++) {
				prior[i].value = sprior;
			}
		}
		function iblockTypeSelect(el) {
			var p = BX.findNextSibling(el, {tagName:'div',className:'adm-cell-iblocks'}),
				c = BX.findChildren(p, {tagName:'INPUT',property:{type:'checkbox'}}, true),
				v = false;
			for(var i = 0; i < c.length; i++) {
				if (!c[i].checked)
					v = true;
			}
			for(var i = 0; i < c.length; i++) {
				c[i].checked = v;
			}
			el.checked = v;
		}
		</script><?
	}
	?>
	</td>
</tr>

<? $tabControl->BeginNextTab(); ?>
<?
?><tr>
	<td class="adm-detail-content-cell-l"><?=GetMessage('SW_MASS_ACTION')?> (<?=GetMessage('SW_TAB_4')?>):</td>
	<td>
		<?=SWSitemapEdit::showFreqSelect('static_mass[freq]', false, false); ?>
		<?=SWSitemapEdit::showModSelect('static_mass[mod]', false, false); ?>
		<?=SWSitemapEdit::staticAddCellPrior('static_mass[prior]', '0.5', false); ?>
		<input type="button" value="<?=GetMessage('SW_SET')?>" title="<?=GetMessage('SW_SET')?>" class="adm-btn-save" onclick="staticMypeChange(this)">
	</td>
</tr>
<tr>
	<td colspan="2"><hr></td>
</tr>
<tr>
	<td colspan="2" align="center">
	<table class="internal" id="static-tbl" style="margin: 0 auto;">
		<tr class="heading">
			<td><?=GetMessage("SW_STATIC_REVERSE")?></td>
			<td style="width: 80%"><?=GetMessage("SW_STATIC_NAME")?></td>
			<td><?=GetMessage("SW_STATIC_FREQ")?></td>
			<td><?=GetMessage("SW_STATIC_MOD")?></td>
			<td><?=GetMessage("SW_STATIC_PRIOR")?></td>
			<td><?=GetMessage("SW_STATIC_DEL")?></td>
		</tr>
		<?
		$static = (is_array($arSitemap['SETTINGS']['static']) ? array_values($arSitemap['SETTINGS']['static']) : array());
		for ($staticId = 0; $staticId <= count($static); $staticId++) {
			$arStatic = array(
				'reverse'	=> $static[$staticId]['reverse'],
				'name'		=> $static[$staticId]['name'],
				'freq'		=> $static[$staticId]['freq'],
				'mod'		=> $static[$staticId]['mod'],
				'prior'		=> $static[$staticId]['prior'],
			);
			echo SWSitemapEdit::staticAddRow($staticId, $arStatic);
		}
		?>
		</table>
		<div style="width: 100%; text-align: center; margin: 10px 0;">
			<input class="adm-btn-big" type="button" id="propedit_add_btn" name="propedit_add" value="<?=GetMessage("SW_LIST_MORE")?>">
		</div>
		<tr>
			<td class="adm-detail-content-cell-l"><?=GetMessage('SW_EXCLUDE_ROBOTS')?>:</td>
			<td width="60%">
			<input type="checkbox" name="check_robots"<?=($arSitemap["SETTINGS"]['check_robots']=='Y'?' checked="checked"':'')?> value="Y">
			<?=SWSitemapEdit::showUASelect('user_agent', $arSitemap["SETTINGS"]['user_agent']); ?>
			</td>
		</tr>
		<script>
		window.static = {
			tbl: BX("static-tbl"),
			count: <?=($staticId?:0)?>
		};

		function add_list_row() {
			var id = window.static.count++,
				newRow,
				oCell,
				strContent;
			
			newRow = window.static.tbl.insertRow(window.static.tbl.rows.length);

			oCell = newRow.insertCell(-1);
			strContent = '<?=CUtil::JSEscape(SWSitemapEdit::staticAddCellReverse('static[tmp_xxx][reverse]')); ?>';
			strContent = strContent.replace(/tmp_xxx/ig, id);
			oCell.innerHTML = strContent;
			oCell.setAttribute('align','center');
			
			oCell = newRow.insertCell(-1);
			strContent = '<?=CUtil::JSEscape(SWSitemapEdit::staticAddCellName('static[tmp_xxx][name]')); ?>';
			strContent = strContent.replace(/tmp_xxx/ig, id);
			oCell.innerHTML = strContent;
			oCell.setAttribute('class','bx-digit-cell');

			oCell = newRow.insertCell(-1);
			strContent = '<?=CUtil::JSEscape(SWSitemapEdit::staticAddCellFreq('static[tmp_xxx][freq]')); ?>';
			strContent = strContent.replace(/tmp_xxx/ig, id);
			oCell.innerHTML = strContent;

			oCell = newRow.insertCell(-1);
			strContent = '<?=CUtil::JSEscape(SWSitemapEdit::staticAddCellMod('static[tmp_xxx][mod]')); ?>';
			strContent = strContent.replace(/tmp_xxx/ig, id);
			oCell.innerHTML = strContent;

			oCell = newRow.insertCell(-1);
			strContent = '<?=CUtil::JSEscape(SWSitemapEdit::staticAddCellPrior('static[tmp_xxx][prior]')); ?>';
			strContent = strContent.replace(/tmp_xxx/ig, id);
			oCell.innerHTML = strContent;
			oCell.setAttribute('align','center');

			oCell = newRow.insertCell(-1);

			BX.style(oCell, 'textAlign', 'center');
			if (typeof BX.adminFormTools !== "undefined")
				BX.adminFormTools.modifyFormElements('sitemap_form');
		}

		var obListBtn = BX('propedit_add_btn');

		if (!!obListBtn && !!window.static)
			BX.bind(obListBtn, 'click', add_list_row);
		
		function staticMypeChange(el) {
			var p = BX.findParent(el, {tagName:'TABLE'}),
				sfreq = BX.findPreviousSibling(el, {tagName:'SELECT',property:{name:'static_mass[freq]'}}).value,
				smod = BX.findPreviousSibling(el, {tagName:'SELECT',property:{name:'static_mass[mod]'}}).value,
				sprior = BX.findPreviousSibling(el, {tagName:'INPUT',property:{name:'static_mass[prior]'}}).value,
				freq = BX.findChildren(p, {tagName:'SELECT',className:'input_freq'}, true),
				mod = BX.findChildren(p, {tagName:'SELECT',className:'input_mod'}, true),
				prior = BX.findChildren(p, {tagName:'INPUT',className:'input_prior'}, true);
			for(var i = 0; i < freq.length; i++) {
				freq[i].value = sfreq;
			}
			for(var i = 0; i < mod.length; i++) {
				mod[i].value = smod;
			}
			for(var i = 0; i < prior.length; i++) {
				prior[i].value = sprior;
			}
		}
		</script>
	</td>
</tr>

<? $tabControl->BeginNextTab(); ?>
<?
?>
<tr>
	<td colspan="2">
		<?=GetMessage('SW_INFO')?>
	</td>
</tr>
<? $tabControl->EndTab() ?>
<? if($CAT_R == "W") $tabControl->Buttons(array()); ?>
<? $tabControl->End() ?>
</form>
<? } ?>
<?SWSitemapList::getSupportButton();?>
<?require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");?>