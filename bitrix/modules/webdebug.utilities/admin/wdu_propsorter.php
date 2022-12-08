<?
use
	\WD\Utilities\Helper,
	\WD\Utilities\IBlockHelper,
	\WD\Utilities\PropSorterTable as PropSorter;

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

$intIBlockId = IntVal($_GET['IBLOCK_ID']);
$arIBlockList = Helper::getIBlocks($bGroup=true, $bInactive=true);

if($_REQUEST['save_to_iblock'] == 'Y'){
	header('Content-Type: application/json; charset='.(defined('BX_UTF') && BX_UTF === true ? 'UTF-8' : 'windows-1251'));
	$arJsonResult = array(
		'Success' => false,
	);
	if(is_array($_POST['prop_id'])){
		$intIndex = 1;
		$intStep = 10;
		$obProperty = new \CIBlockProperty();
		foreach($_POST['prop_id'] as $strPropId){
			$obProperty->update($strPropId, ['SORT' => $intIndex * $intStep]);
			$intIndex++;
		}
		unset($obProperty);
		$arJsonResult['Success'] = true;
	}
	print \Bitrix\Main\Web\Json::encode($arJsonResult);
	die();
}

// Save data
$bSaving = isset($_POST['save']);
if($bSaving) {
	$arSaveProps = $_POST['prop_id'];
	// Delete exist items
	$arQuery = [
		'order' => [
			'SORT' => 'ASC',
		],
		'filter' => [
			'IBLOCK_ID' => $intIBlockId,
		],
	];
	$resItems = PropSorter::getList($arQuery);
	while($arItem = $resItems->fetch()){
		PropSorter::delete($arItem['ID']);
	}
	// Save new
	if (is_array($arSaveProps) && !empty($arSaveProps)) {
		$intSort = 0;
		foreach($arSaveProps as $strPropKey => $strPropValue) {
			if(preg_match('#^header_active_(.*?)$#', $strPropKey)){
				continue;
			}
			$arFields = [
				'IBLOCK_ID' => $intIBlockId,
				'PROPERTY_ID' => null,
				'GROUP_TITLE' => null,
				'SORT' => ++$intSort,
			];
			if(preg_match('#^prop_\d+$#i', $strPropKey, $arMatch)){
				$arFields['PROPERTY_ID'] = $strPropValue;
			}
			elseif(preg_match('#^header_(\d+)$#i', $strPropKey, $arMatch)){
				$strId = $arMatch[1];
				$arFields['GROUP_TITLE'] = $strPropValue;
				$arFields['GROUP_ACTIVE'] = $arSaveProps['header_active_'.$strId] == 'N' ? 'N' : 'Y';
			}
			PropSorter::add($arFields);
		}
		LocalRedirect($APPLICATION->GetCurPageParam('IBLOCK_ID='.$intIBlockId.'&lang='.LANGUAGE_ID, array('IBLOCK_ID', 'lang')));
	}
}

$APPLICATION->SetTitle(GetMessage('WD_PROPSORTER_PAGE_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arTabs[] = array("DIV"=>"general", "TAB"=>GetMessage("WD_PROPSORTER_TAB_GENERAL_NAME"), "TITLE"=>GetMessage("WD_PROPSORTER_TAB_GENERAL_DESC"));
CJSCore::Init(array('jquery'));
$APPLICATION->AddHeadScript('/bitrix/js/webdebug.utilities/jquery.ui.sortable.js');

// Load saved props
$arProps = PropSorter::loadIBlockData($intIBlockId);
?>

<style>
.wd_prop_item {
	padding:1px;
}
.wd_prop_item_outer.ui-sortable-placeholder {
	position:relative;
	visibility:visible!important;
}
.wd_prop_item_outer.ui-sortable-placeholder:before {
	content:'';
	border:2px dashed gray;
	height:100%;
	left:0;
	position:absolute;
	top:0;
	width:100%;
	-webkit-box-sizing:border-box;
	   -moz-box-sizing:border-box;
	        box-sizing:border-box;
}
.wd_prop_item_inner {
	background:#e5edef;
	border:1px solid #aab5b9;
	cursor:default;
	height:24px;
	line-height:24px;
	padding:0 5px 0 30px;
	position:relative;
	border-radius:2px;
}
.wd_prop_item_inner:hover {
	background-color:#ccd7db;
}
.wd_prop_item_inner:before {
	content:'';
	background:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAgklEQVR42mNgwA24gHg7FHMxkAhAGvYC8X8o3kuqITuA+A0QX4TiN1AxokEvEOsB8Roo1oOKkQxgBhDl5x1YbMFmQC9ULSd6gL2BOpWQAXpQtXtghmyHhvRFJA0w/ASK0cUvQvVsodSA7ZR4ASVtkBOIXFSNRqonJIqTMsWZiejsDABbQ0bDhprMrwAAAABJRU5ErkJggg==') 0 0 no-repeat;
	height:16px;
	left:4px;
	margin-top:-8px;
	position:absolute;
	top:50%;
	width:16px;
}
.wd_prop_item_group .wd_prop_item_inner {
	background:#afd2e0;
	height:36px;
	line-height:36px;
	padding-right:64px;
}
.wd_prop_item_group .wd_prop_item_inner_active {
	position:absolute;
	height:16px;
	line-height:16px;
	margin-top:-8px;
	right:40px;
	top:50%;
	width:16px;
}
.wd_prop_item_group .wd_prop_item_inner:hover {
	background:#afd2e0;
}
.wd_prop_item input[type=text] {
	-webkit-box-sizing:border-box;
	   -moz-box-sizing:border-box;
	        box-sizing:border-box;
	width:100%;
}
.wd_prop_item input[type=button]{
	font-size:15px;
	height:23px!important;
	margin-top:-11px;
	padding:0!important;
	position:absolute;
	right:4px;
	top:50%;
	width:24px;
}
</style>

<form method="post" action="<?=POST_FORM_ACTION_URI;?>" name="post_form" id="wd_propsorter_form">
	<?$TabControl = new CAdminTabControl("WDPropSorter", $arTabs);?>
	<?$TabControl->Begin();?>
	<?$TabControl->BeginNextTab();?>
	<tr>
		<td>
			<div id="wd_propsorter_iblock_list">
				<select name="iblock_id">
					<option value=""><?=GetMessage('WD_PROPSORTER_SELECT_IBLOCK');?></option>
					<?foreach($arIBlockList as $IBlockTypeKey => $arIBlockType):?>
						<?
						if(empty($arIBlockType['ITEMS'])){
							continue;
						}
						?>
						<optgroup label="<?=$arIBlockType['NAME'];?>">
							<?foreach($arIBlockType['ITEMS'] as $arIBlock):?>
								<option value="<?=$arIBlock['ID'];?>"<?if($intIBlockId==$arIBlock['ID']):?> selected="selected"<?endif?>>[<?=$arIBlock['ID'];?>] <?=$arIBlock['NAME'];?></option>
							<?endforeach?>
						</optgroup>
					<?endforeach?>
				</select>
			</div>
			<br/>
			<hr/>
			<br/>
			<div id="wd_iblock_data_wrapper">
				<?if($intIBlockId > 0):?>
					<div>
						<input type="text" value="" id="wd_propsorter_add_value" placeholder="<?=GetMessage('WD_PROPSORTER_ADD_GROUP_PLACEHOLDER');?>" size="50" maxlength="255" />
						<input type="button" value="<?=GetMessage('WD_PROPSORTER_ADD_GROUP_BUTTON');?>" id="wd_propsorter_add_button" />
					</div>
					<br/>
					<div class="wd_iblock_data" id="wd_iblock<?=$intIBlockId;?>_data">
						<div class="wd_prop_items">
							<?foreach($arProps as $arProperty):?>
								<?
								$bHeader = !$arProperty['PROPERTY_ID'];
								$arProp = &$arProperty['PROPERTY'];
								if(!$bHeader && !is_array($arProp)){
									continue;
								}
								?>
								<div class="wd_prop_item_outer">
									<div class="wd_prop_item<?if($bHeader):?> wd_prop_item_group<?endif?>">
										<div class="wd_prop_item_inner">
											<?if($bHeader):?>
												<?$strId = rand(100000000, 999999999);?>
												<input type="text" name="prop_id[header_<?=$strId;?>]" value="<?=htmlspecialcharsbx($arProperty['GROUP_TITLE']);?>" size="50" maxlength="255" />
												<span class="wd_prop_item_inner_active">
													<input type="hidden" name="prop_id[header_active_<?=$strId;?>]" value="N" />
													<input type="checkbox" name="prop_id[header_active_<?=$strId;?>]" value="Y"<?if($arProperty['GROUP_ACTIVE'] != 'N'):?> checked="checked"<?endif?> />
												</span>
												<input type="button" value="&times;" />
											<?else:?>
												<?=$arProp['NAME'];?> [<?=$arProp['ID'];?>, <?=$arProp['CODE'];?>, <?=$arProp['PROPERTY_TYPE'];?><?if(strlen($arProp['USER_TYPE'])):?>:<?=$arProp['USER_TYPE'];?><?endif?>]
												<input type="hidden" name="prop_id[prop_<?=$arProp['ID'];?>]" value="<?=$arProp['ID'];?>" />
											<?endif?>
										</div>
									</div>
								</div>
							<?endforeach?>
						</div>
					</div>
				<?else:?>
					<div id="wd_iblock_data_no"></div>
				<?endif?>
			</div>
		</td>
	</tr>
	<?$TabControl->Buttons();?>
	<input type="submit" name="save" value="<?=getMessage('WD_PROPSORTER_BUTTON_SAVE');?>" class="adm-btn-save">
	<input type="button" value="<?=getMessage('WD_PROPSORTER_BUTTON_SAVE_TO_IBLOCK');?>" class="adm-btn-save-to-iblock" 
		style="float:right;">
	<?$TabControl->End();?>
</form>

<script>
// Sortable
var WD_SortableHelper = function(Event, TR) {
	var $originals = TR.children();
	var $helper = TR.clone();
	$helper.children().each(function(index) {
		$(this).width($originals.eq(index).width());
	});
	return $helper;
};
var WD_SortableOnStop = function(Event, UI) {
	$('td.index', UI.item.parent()).each(function (i) {
		$(this).html(i + 1);
	});
};
var WD_SortableEscapeHtml = function(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) {
		return map[m];
	});
}
// Change current iblock handler
$('#wd_propsorter_iblock_list select').change(function(){
	location.href = '/bitrix/admin/wdu_propsorter.php?IBLOCK_ID='+$(this).val()+'&lang='+phpVars.LANGUAGE_ID;
});
$('#wd_propsorter_add_button').click(function(e){
	e.preventDefault();
	var newGroup = $('#wd_propsorter_add_value').val().trim();
	if(newGroup.length > 0){
		var id = Math.round(Math.random() * 100000000);
		var newGroupHtml = $('<div><div class="wd_prop_item_outer"><div class="wd_prop_item wd_prop_item_group">'
			+'<div class="wd_prop_item_inner">'
			+'<input type="text" name="prop_id[header_'+id+']" value="'+WD_SortableEscapeHtml(newGroup)+'"'
				+ ' size="50" maxlength="255" />'
					+ '<span class="wd_prop_item_inner_active">'
						+ '<input type="hidden" name="prop_id[header_active_'+id+']" value="N" />'
						+ '<input type="checkbox" name="prop_id[header_active_'+id+']" value="Y" checked="checked" />'
					+ '</span>'
				+ '<input type="button" value="&times;" />'
			+'</div></div></div></div>');
		$('input[type=checkbox]', newGroupHtml).each(function(){
			BX.adminFormTools.modifyCheckbox(this);
		});
		$('.wd_iblock_data:visible > .wd_prop_items').prepend(newGroupHtml.html()).sortable('refresh');
	}
	$('#wd_propsorter_add_value').val('');
});
$('#wd_propsorter_add_value').keydown(function(e){
	if (e.keyCode == 13) {
		e.preventDefault();
		$('#wd_propsorter_add_button').trigger('click');
	}
});
$(document).ready(function(){
	$('.wd_prop_items').sortable({
		connectWith: '.wd_prop_items',
		handle:'.wd_prop_item',
		helper: WD_SortableHelper,
		stop: WD_SortableOnStop,
		update: function(Event, UI) {}
	});
});
$(document).delegate('.wd_prop_item_group.wd_prop_item input[type=button]', 'click', function(e){
	e.preventDefault();
	if(confirm('<?=getMessage('WD_PROPSORTER_BUTTON_DELETE_CONFIRM');?>')){
		$(this).closest('.wd_prop_item_group').remove();
	}
});
$(document).delegate('input.adm-btn-save-to-iblock', 'click', function(e){
	e.preventDefault();
	if(confirm('<?=getMessage('WD_PROPSORTER_BUTTON_SAVE_TO_IBLOCK_CONFIRM');?>')){
		$.ajax({
			url: '<?=$APPLICATION->getCurPageParam('save_to_iblock=Y', ['save_to_iblock'])?>',
			type: 'POST',
			data: $('#wd_propsorter_form .wd_iblock_data .wd_prop_item_inner > input[type="hidden"]').serialize(),
			datatype: 'json',
			success: function(arJson) {
				if(arJson.Success){
					alert('<?=getMessage('WD_PROPSORTER_BUTTON_SAVE_TO_IBLOCK_SUCCESS');?>');
				}
				else{
					alert('<?=getMessage('WD_PROPSORTER_BUTTON_SAVE_TO_IBLOCK_ERROR');?>');
				}
			}
		});
	}
});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>