<?
use
	\WD\Utilities\Helper,
	\WD\Utilities\IBlockHelper;

$strModuleId = 'webdebug.utilities';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$strModuleId.'/prolog.php');
if (!\Bitrix\Main\Loader::includeModule($strModuleId)) {
	die('Module is not found!');
}
$strModuleRights = $APPLICATION->getGroupRight($strModuleId);
if($strModuleRights < 'R') {
	$APPLICATION->authForm(Helper::getMessage('ACCESS_DENIED'));
}
$strLang = 'WDU_DASHBOARD_';
Helper::loadMessages();
CJSCore::init('jquery');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
$obJsPopup = new \CJSPopup();
$obJsPopup->showTitlebar(Helper::getMessage($strLang.'POPUP_TITLE'));
#
$arTabs = [];
$arTabs[] = [
	'DIV' => 'general',
	'TAB' => Helper::getMessage($strLang.'TAB_GENERAL_NAME'),
];
$arTabs[] = [
	'DIV' => 'general2',
	'TAB' => Helper::getMessage($strLang.'TAB_GENERAL_NAME'),
];

print Helper::showNote('Dashboard in not yet ready. Stay tuned.');


/*
<form method="post" action="<?=POST_FORM_ACTION_URI;?>" id="wdu_dashboard">
	<?$obTabControl = new \CAdminTabControl('WduDashboard', $arTabs);?>
	<?$obTabControl->begin();?>
	<?$obTabControl->beginNextTab();?>
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
	<?$obTabControl->beginNextTab();?>
	<tr>
		<td>
			111111
		</td>
	</tr>
	<?$obTabControl->buttons();?>
	<input type="submit" name="save" value="<?=getMessage('WD_PROPSORTER_BUTTON_SAVE');?>" class="adm-btn-save">
	<input type="button" value="<?=getMessage('WD_PROPSORTER_BUTTON_SAVE_TO_IBLOCK');?>" class="adm-btn-save-to-iblock" 
		style="float:right;">
	<?$obTabControl->end();?>
</form>
*/

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>