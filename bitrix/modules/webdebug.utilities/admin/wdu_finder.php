<?
use
	\WD\Utilities\Finder,
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
$strLang = 'WDU_FINDER_';
$strHint = $strLang.'HINT_';
\CJSCore::init(['jquery', 'wdupopup']);
$APPLICATION->addHeadScript('/bitrix/js/'.$strModuleId.'/wdu_popup.js');
$APPLICATION->addHeadScript('/bitrix/js/'.$strModuleId.'/helper.js');
$APPLICATION->addHeadScript('/bitrix/js/'.$strModuleId.'/finder.js');

$strModuleRights = $APPLICATION->getGroupRight($strModuleId);
if($strModuleRights == 'D') {
	$APPLICATION->authForm(Helper::getMessage("ACCESS_DENIED"));
}

list($arGet, $arPost) = Helper::getRequestQuery();

if(strlen($arGet['wdu_ajax_option'])){
	$arJsonResult = Json::prepare();
	#
	switch($arGet['wdu_ajax_option']){
		case 'ajax_search':
			$bContinue = false;
			$arState = &$_SESSION['WDU_FINDER_STATE'];
			$fStepTime = Helper::getOption(WDU_MODULE, 'finder_step_time');
			if($arPost['start']){
				$arParams = $arPost;
				if(!Helper::isUtf()){
					$arParams = Helper::convertEncoding($arParams, 'UTF-8', 'CP1251');
				}
				unset($arParams['start']);
				$strFilter = $arParams['search_filter'];
				if(!strlen($strFilter)){
					$strFilter = '*.php, *.txt, *.html';
				}
				$arState = [
					'START_PATH' => null,
					'PARAMS' => $arParams,
					'FILES_COUNT' => 0,
					'RESULTS' => [],
					'RESULTS_COUNT' => 0,
					'RESULTS_MAX' => Helper::getOption(WDU_MODULE, 'finder_max_results'),
					#
					'TEXT' => $arParams['search_text'],
					'FILTER' => array_filter(preg_split('#[,;]\s*#i', $arParams['search_filter'])),
					'FOLDER' => strlen($arParams['search_folder']) ? $arParams['search_folder'] : '',
					'FOLDER_EXCLUDE' => array_filter(preg_split('#[,;]\s*#i', $arParams['search_folder_exclude'])),
					'REGEXP' => $arParams['search_reqexp'],
					'CASE' => $arParams['search_case'],
					'ENCODING' => $arParams['search_encoding'],
					#
					'MAX_FILESIZE' => Helper::getOption(WDU_MODULE, 'finder_max_filesize'),
				];
				if(!strlen($arState['TEXT'])){
					$arJsonResult['ErrorMessage'] = Helper::getMessage($strLang.'FIELD_SEARCH_TEXT_EMPTY');
				}
				if(empty($arState['FILTER'])){
					$arState['FILTER'][] = '*';
				}
				if($arState['ENCODING'] == 'Y'){
					$obFinder = new Finder($fTmpStepTime=0, $arTmpState=[]);
					$strConvertedText = $obFinder->convertCharset($arParams['search_text']);
					unset($obFinder);
					if($strConvertedText == $arParams['search_text']){
						$arState['ENCODING'] = 'N';
					}
				}
				$fStepTime = 0.1;
				$arJsonResult['Start'] = true;
			}
			$obFinder = new Finder($fStepTime, $arState);
			if(isset($arState['START_PATH']) && !empty($arState['START_PATH'])) {
				$obFinder->startPath = $arState['START_PATH'];
			}
			$mResult = $obFinder->scan($_SERVER['DOCUMENT_ROOT']);
			if($mResult === Finder::RESULT_BREAK) {
				$arState['START_PATH'] = $obFinder->nextPath;
				$bContinue = true;
			}
			#
			$arJsonResult['Continue'] = $bContinue;
			$arJsonResult['Results'] = $arState['RESULTS'];
			$arJsonResult['ResultsCount'] = $arState['RESULTS_COUNT'];
			$arJsonResult['FilesCount'] = $arState['FILES_COUNT'];
			if($bContinue){
				$arJsonResult['Params'] = $arState['PARAMS'];
				$arJsonResult['NextPath'] = $arState['START_PATH'];
			}
			else{
				unset($arState);
			}
			break;
	}
	Json::output($arJsonResult);
	die();
}

$APPLICATION->setTitle(GetMessage($strLang.'PAGE_TITLE'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$arTabs[] = [
	'DIV' => 'general',
	'TAB' => Helper::getMessage($strLang.'TAB_GENERAL_NAME'),
	'TITLE' => Helper::getMessage($strLang.'TAB_GENERAL_DESC'),
];
?>

<form method="post" action="<?=POST_FORM_ACTION_URI;?>" id="wdu_finder_form">
	<?$obTabControl = new \CAdminTabControl('WDUFinder', $arTabs);?>
	<?$obTabControl->begin();?>
	<?$obTabControl->beginNextTab();?>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_TEXT'));?>
			<label for="wdu_search_text"><?=Helper::getMessage($strLang.'FIELD_SEARCH_TEXT');?>:</label>
		</td>
		<td width="60%">
			<?$strValue = Helper::convertEncodingFrom($arGet['search'], 'UTF-8');?>
			<input type="text" name="text" value="<?=htmlspecialcharsbx($strValue)?>" size="60"
				placeholder="<?=Helper::getMessage($strLang.'FIELD_SEARCH_TEXT_PLACEHOLDER');?>"
				data-role="wdu_search_text" id="wdu_search_text" data-key="search"
				data-empty="<?=Helper::getMessage($strLang.'FIELD_SEARCH_TEXT_EMPTY');?>" />
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_FILTER'));?>
			<label for="wdu_search_filter"><?=Helper::getMessage($strLang.'FIELD_SEARCH_FILTER');?>:</label>
		</td>
		<td width="60%">
			<?$strValue = strlen($arGet['filter']) ? Helper::convertEncodingFrom($arGet['filter'], 'UTF-8')
				: Helper::getMessage($strLang.'FIELD_SEARCH_FILTER_DEFAULT');?>
			<input type="text" name="filter" value="<?=htmlspecialcharsbx($strValue)?>" size="60"
				placeholder="<?=Helper::getMessage($strLang.'FIELD_SEARCH_FILTER_PLACEHOLDER');?>"
				data-role="wdu_search_filter" id="wdu_search_filter" data-key="filter"
				data-default="<?=Helper::getMessage($strLang.'FIELD_SEARCH_FILTER_DEFAULT');?>" />
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_FOLDER'));?>
			<label for="wdu_search_folder"><?=Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER');?>:</label>
		</td>
		<td width="60%">
			<?$strValue = strlen($arGet['folder']) ? Helper::convertEncodingFrom($arGet['folder'], 'UTF-8') 
				: Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_DEFAULT');?>
			<input type="text" name="folder" value="<?=htmlspecialcharsbx($strValue)?>" size="60"
				placeholder="<?=Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_PLACEHOLDER');?>"
				data-role="wdu_search_folder" id="wdu_search_folder" data-key="folder"
				data-default="<?=Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_DEFAULT');?>" />
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_FOLDER_EXCLUDE'));?>
			<label for="wdu_search_folder_exclude"><?=Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_EXCLUDE');?>:</label>
		</td>
		<td width="60%">
			<?$strValue = strlen($arGet['folder_exclude']) ? Helper::convertEncodingFrom($arGet['folder_exclude'], 'UTF-8') 
				: Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_EXCLUDE_DEFAULT');?>
			<input type="text" name="folder_exclude" value="<?=htmlspecialcharsbx($strValue)?>" size="60"
				placeholder="<?=Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_EXCLUDE_PLACEHOLDER');?>"
				data-role="wdu_search_folder_exclude" id="wdu_search_folder_exclude" data-key="folder_exclude"
				data-default="<?=Helper::getMessage($strLang.'FIELD_SEARCH_FOLDER_EXCLUDE_DEFAULT');?>" />
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_REGEXP'));?>
			<label for="wdu_search_regexp"><?=Helper::getMessage($strLang.'FIELD_SEARCH_REGEXP');?>:</label>
		</td>
		<td width="60%">
			<input type="checkbox" name="regexp" value="Y"<?if($arGet['regexp'] == 'Y'):?> checked="checked"<?endif?>
				data-role="wdu_search_regexp" id="wdu_search_regexp" data-key="regexp" data-default="N" />
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_CASE'));?>
			<label for="wdu_search_case"><?=Helper::getMessage($strLang.'FIELD_SEARCH_CASE');?>:</label>
		</td>
		<td width="60%">
			<input type="checkbox" name="case" value="Y"<?if($arGet['case'] == 'Y'):?> checked="checked"<?endif?>
				data-role="wdu_search_case" id="wdu_search_case" data-key="case" data-default="N" />
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?=Helper::showHint(Helper::getMessage($strHint.'FIELD_SEARCH_ENCODING'));?>
			<label for="wdu_search_encoding"><?=Helper::getMessage($strLang.'FIELD_SEARCH_ENCODING');?>:</label>
		</td>
		<td width="60%">
			<input type="checkbox" name="encoding" value="Y"<?if($arGet['encoding'] == 'Y'):?> checked="checked"<?endif?>
				data-role="wdu_search_encoding" id="wdu_search_encoding" data-key="encoding" data-default="N" />
		</td>
	</tr>
	<tr>
		<td width="40%"></td>
		<td width="60%">
			<input type="button" data-role="wdu_search_reset" class="adm-btn"
				value="<?=Helper::getMessage($strLang.'FIELD_SEARCH_RESET');?>"
				data-confirm="<?=Helper::getMessage($strLang.'FIELD_SEARCH_RESET_CONFIRM');?>" />
		</td>
	</tr>
	<tr>
		<td width="40%"></td>
		<td width="60%">
			<?=Helper::showNote(Helper::getMessage($strLang.'NOTE', [
				'#MAX_FILESIZE#' => round(Helper::getOption(WDU_MODULE, 'finder_max_filesize') / (1024 * 1024), 2),
				'#MAX_RESULTS#' => Helper::getOption(WDU_MODULE, 'finder_max_results'),
			]), true);?>
		</td>
	</tr>
	<tr>
		<td width="40%"></td>
		<td width="60%">
			<input type="submit" data-role="wdu_search_start" class="adm-btn-green"
				value="<?=Helper::getMessage($strLang.'FIELD_SEARCH_START');?>" />
			<input type="button" data-role="wdu_search_stop" class="adm-btn"
				value="<?=Helper::getMessage($strLang.'FIELD_SEARCH_STOP');?>" style="display:none;" />
			<?if(isset($arGet['start'])):?>
				<input type="hidden" data-role="wdu_search_start_now" value="Y" />
			<?endif?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br/>
			<div data-role="wdu_search_status">
				<div data-role="wdu_search_status_next_file"></div>
			</div>
			<?ob_start();?>
			<span data-role="wdu_search_results_count">0/0</span>
			<?=Helper::showHeading(Helper::getMessage($strLang.'FIELD_SEARCH_RESULTS').ob_get_clean());?>
			<div data-role="wdu_search_results">
				<span><?=Helper::getMessage($strLang.'FIELD_SEARCH_RESULTS_EMPTY');?></span>
			</div>
		</td>
	</tr>
	<?$obTabControl->buttons();?>
	<?$obTabControl->end();?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>