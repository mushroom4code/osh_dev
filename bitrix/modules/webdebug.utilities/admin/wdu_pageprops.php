<?
use
	\WD\Utilities\Helper,
	\WD\Utilities\Json,
	\WD\Utilities\PageProp;

$strModuleId = 'webdebug.utilities';
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$strModuleId.'/prolog.php');
\Bitrix\Main\Loader::includeModule('fileman');
if(!\Bitrix\Main\Loader::includeModule($strModuleId)) {
	die('Module is not found!');
}
Helper::loadMessages();
\CJSCore::init(['jquery', 'wdupopup']);
$APPLICATION->addHeadScript('/bitrix/js/'.$strModuleId.'/pageprops_fileman.js');

$strModuleRights = $APPLICATION->getGroupRight($strModuleId);
if($strModuleRights == 'D') {
	$APPLICATION->authForm(Helper::getMessage("ACCESS_DENIED"));
}

list($arGet, $arPost) = Helper::getRequestQuery();

$arSites = Helper::getSitesList($bActiveOnly=false, $bSimple=false, $strField=null, $strOrder=null, $bIcons=true);
$arSitesFull = array_merge(['-' => Helper::getMessage('WDU_PAGEPROPS_SITE_ALL')], $arSites);
$strCurrentSiteId = $arGet['site'];
if(!isset($arSites[$strCurrentSiteId])){
	$strCurrentSiteId = '-';
}

$arFilemanProps = [];
foreach($arSitesFull as $strSiteId => $strSiteName){
	$arFilemanProps[$strSiteId] = \CFileMan::getPropstypes(strlen($strSiteId) ? $strSiteId : null);
}
$arPropTypes = PageProp::getTypes();

$arCurrentData = [];
$resCurrentData = PageProp::getList();
while($arCurrentDataItem = $resCurrentData->fetch()){
	$arCurrentData[$arCurrentDataItem['SITE']][$arCurrentDataItem['PROPERTY']] = $arCurrentDataItem;
}

if(strlen($arGet['wdu_ajax_option'])){
	$strProp = $arPost['prop'];
	$strSite = $arPost['site'];
	$strType = $arPost['type'];
	$strName = $arFilemanProps[$strSite][$strProp];
	#
	$arJsonResult = Json::prepare();
	#
	if($strSite && $strSite != '-' && $strSite[$strSite]){
		$arJsonResult['Title'] = sprintf(Helper::getMessage('WDU_PAGEPROPS_POPUP_SITE'), $strName, $strProp, $strSite);
	}
	else{
		$arJsonResult['Title'] = sprintf(Helper::getMessage('WDU_PAGEPROPS_POPUP_ALL'), $strName, $strProp);
	}
	#
	switch($arGet['wdu_ajax_option']){
		case 'ajax_load_prop':
			# Get current property data
			$arFilter = PageProp::getFilter($strProp, $strSite);
			$resCurrentProp = PageProp::getList(false, $arFilter);
			if($arCurrentProp = $resCurrentProp->getNext()){
				$strType = $arCurrentProp['TYPE'];
			}
			else{
				$arCurrentProp = ['TYPE' => false];
			}
			ob_start();
			?>
			<form action="<?=POST_FORM_ACTION_URI;?>" id="wdu_pageprops_prop_form" method="post">
				<?foreach(array_merge($arGet, $arPost) as $key => $value):?>
					<input type="hidden" name="<?=$key;?>" value="<?=$value?>" />
				<?endforeach?>
				<div>
					<div class="wd_pageprops_select_type">
						<div><?=Helper::getMessage('WDU_PAGEPROPS_POPUP_PROP_LABEL');?></div>
						<div>
							<?
							$arValues = array_combine(array_keys($arPropTypes), array_map(function($arItem){
								return ['TEXT' => $arItem['NAME'], 'ICON' => $arItem['ICON']];
							}, $arPropTypes));
							
							print Helper::selectBoxEx('type', $arValues, [
								'SELECTED' => $arCurrentProp['TYPE'],
								'DEFAULT' => Helper::getMessage('WDU_PAGEPROPS_POPUP_PROP_DEFAULT'),
								'ATTR' => 'data-role="wdu_pageprop_type"',
								'SELECT2' => true,
								'WITH_ICONS' => true,
							]);
							?>
						</div>
					</div>
					<br/>
					<div class="wd_pageprops_select_type_settings" data-role="wdu_pageprop_prop_type_settings">
						<?=PageProp::showSettings($strProp, $strSite, $strType);?>
					</div>
				</div>
			</form>
			<?
			$arJsonResult['Content'] = ob_get_clean();
			break;
		case 'ajax_load_prop_type':
			$arJsonResult['Content'] = PageProp::showSettings($strProp, $strSite, $strType);
			break;
		case 'ajax_load_prop_save':
			if(!Helper::isUtf()){
				$arPost = Helper::convertEncoding($arPost, 'UTF-8', 'CP1251');
			}
			$arJsonResult['Success'] = PageProp::saveSettings($strProp, $strSite, $strType, $arPost);
			$arJsonResult['Content'] = PageProp::showSettings($strProp, $strSite, $strType);
			$arJsonResult['Prop'] = [
				'Code' => $strProp,
				'Site' => $strSite,
				'Type' => $strType,
				'Exists' => false,
				'Icon' => $arPropTypes[$strType]['ICON'],
			];
			$arFilter = PageProp::getFilter($strProp, $strSite);
			$resCurrentProp = PageProp::getList(false, $arFilter);
			if($arCurrentProp = $resCurrentProp->fetch()){
				$arJsonResult['Prop']['Exists'] = true;
				$arJsonResult['Prop']['Type'] = $arCurrentProp['TYPE'];
			}
	}
	Json::output($arJsonResult);
	die();
}
if($arGet['wdu_ajax_option'] == 'ajax_load_prop'){

}

$APPLICATION->setTitle(GetMessage('WDU_PAGEPROPS_PAGE_TITLE'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$arTabs[] = [
	'DIV' => 'general',
	'TAB' => Helper::getMessage('WDU_PAGEPROPS_TAB_GENERAL_NAME'),
	'TITLE' => Helper::getMessage('WDU_PAGEPROPS_TAB_GENERAL_DESC'),
];
?>

<form method="post" action="<?=POST_FORM_ACTION_URI;?>" id="wdu_pageprops_form">
	<?$obTabControl = new \CAdminTabControl('WDUPageProps', $arTabs);?>
	<?$obTabControl->begin();?>
	<?$obTabControl->beginNextTab();?>
	<?if(Helper::getOption(WDU_MODULE, 'pageprops_enabled') != 'Y'):?>
		<tr>
			<td colspan="2" style="padding-bottom:15px;">
				<?=Helper::showNote(Helper::getMessage('WDU_PAGEPROPS_NOTE_OFF'), true);?>
			</td>
		</tr>
	<?endif?>
	<tr>
		<td colspan="2" style="padding-bottom:15px;">
			<?=Helper::showNote(Helper::getMessage('WDU_PAGEPROPS_NOTE_FILEMAN'), true);?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label for="wdu_different_site_settings" style="display:inline-block; padding-bottom:4px;">
				<?=Helper::getMessage('WDU_PAGEPROPS_SITE_ID');?>
			</label><br/>
			<?=Helper::selectBoxEx(false, $arSitesFull, [
				'SELECTED' => $strCurrentSiteId,
				'ATTR' => 'data-role="wdu_pageprop_site_id"',
				'WITH_ICONS' => true,
			]);?>
			<br/>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?foreach($arSitesFull as $strSiteId => $strSiteName):?>
				<table class="wdu_pageprop_table <?if($strSiteId != $strCurrentSiteId):?> wdu_pageprop_table_hidden<?endif?>"
					data-role="wdu_pageprop_table" data-site="<?=$strSiteId;?>">
					<tbody>
						<tr class="heading">
							<td><?=Helper::getMessage('WDU_PAGEPROPS_PROP_TYPE');?></td>
							<td><?=Helper::getMessage('WDU_PAGEPROPS_PROP_CODE');?></td>
							<td><?=Helper::getMessage('WDU_PAGEPROPS_PROP_NAME');?></td>
							<td></td>
						</tr>
						<?foreach($arFilemanProps[$strSiteId] as $strPropCode => $strPropName):?>
							<?
							$bCheck = !!$arCurrentData[$strSiteId][$strPropCode];
							$strType = false;
							$strIcon = false;
							if($bCheck){
								$strType = $arCurrentData[$strSiteId][$strPropCode]['TYPE'];
								if($strType && $arPropTypes[$strType]){
									$strIcon = $arPropTypes[$strType]['ICON'];
								}
							}
							?>
							<tr data-role="wdu_pageprop_prop" data-prop="<?=$strPropCode;?>" data-site="<?=$strSiteId;?>">
								<td>
									<span class="wdu_pageprop_prop_icon" data-role="wdu_pageprop_prop_icon"
										<?if($bCheck):?>style="background-image:url('<?=$strIcon;?>');"<?endif?>
									></span>
								</td>
								<td>
									<input type="text" value="<?=htmlspecialcharsbx($strPropCode);?>" readonly >
								</td>
								<td>
									<input type="text" value="<?=htmlspecialcharsbx($strPropName);?>" readonly >
								</td>
								<td>
									<input type="button" value="<?=Helper::getMessage('WDU_PAGEPROPS_PROP_SETUP');?>" />
								</td>
							</tr>
						<?endforeach?>
					</tbody>
				</table>
			<?endforeach?>
		</td>
	</tr>
	<?$obTabControl->buttons();?>
	<?$obTabControl->end();?>
</form>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>