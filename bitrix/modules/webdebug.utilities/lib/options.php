<?
/**
 *	Class to work with options.php
 */

namespace WD\Utilities;

use
	\WD\Utilities\Helper,
	\WD\Utilities\Json;

Helper::loadMessages();

class Options {
	
	const LANG = 'WDU_OPTION_';
	
	protected $strModuleId;
	protected $strModuleCode;
	protected $strOptionIdPrefix;
	protected $strOptionRowId;
	protected $arTabs;
	protected $arParams;
	protected $strBackUrl;
	
	protected $arGet;
	protected $arPost;

	protected $bPost;
	protected $bUpdate;
	protected $bApply;
	protected $bReset;
	
	protected $obTabControl;
	
	/**
	 *	Get lang params (it used in lang files)
	 */
	public static function getLang(){
		return [
			static::LANG,
			static::LANG.'NAME_',
			static::LANG.'HINT_',
		];
	}
	
	/**
	 *	Wrapper for Helper::getMessage
	 */
	public static function getMessage($strMessage, $arReplace=null){
		return Helper::getMessage(Options::LANG.$strMessage);
	}
	
	/**
	 *	Create object
	 */
	public function __construct($strModuleId, $arTabs, $arParams=null){
		global $APPLICATION;
		#
		$this->strModuleId = $strModuleId;
		$this->strModuleCode = str_replace('.', '_', $strModuleId);
		$this->strOptionIdPrefix = $this->strModuleCode.'_';
		$this->strOptionRowId = $this->strModuleCode.'_row_';
		$this->arTabs = &$arTabs;
		$this->arParams = is_array($arParams) ? $arParams : [];
		#
		list($this->arGet, $this->arPost) = Helper::getRequestQuery();
		#
		$this->bPost = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->isPost();
		$this->bUpdate = !!strlen($this->arPost['Update']);
		$this->bApply =  !!strlen($this->arPost['Apply']);
		$this->bReset =  !!strlen($this->arPost['RestoreDefaults']);
		#
		if(strlen($this->arGet['back_url_settings'])){
			$this->strBackUrl = $this->arGet['back_url_settings'];
		}
		#
		Helper::loadMessages(realpath(__DIR__.'/../../main/options.php'));
		Helper::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');
		#
		foreach($arTabs as $strTab => $arTab){
			if(!is_string($arTab['TAB']) || !strlen($arTab['TAB'])){
				$arTab['TAB'] = Helper::getMessage(static::LANG.'TAB_'.toUpper($arTab['DIV']).'_NAME');
			}
			if(!is_string($arTab['TITLE']) || !strlen($arTab['TITLE'])){
				$arTab['TITLE'] = Helper::getMessage(static::LANG.'TAB_'.toUpper($arTab['DIV']).'_DESC');
			}
			$arTabs[$strTab] = $arTab;
		}
		#
		$this->obTabControl = new \CAdminTabControl($this->getTabControlId(), $arTabs);
		#
		\CJSCore::init(['ajax', 'jquery3', 'wdupopup']);
		$APPLICATION->addHeadScript('/bitrix/js/'.WDU_MODULE.'/options.js');
		#
		foreach($arTabs as $strTab => $arTab){
			if(is_array($arTab['OPTIONS'])){
				$arTab['~OPTIONS'] = $arTab['OPTIONS'];
				$arTab['OPTIONS'] = $this->prepareOptions($arTab['OPTIONS']);
				foreach($arTab['OPTIONS'] as $strGroup => $arGroup){
					foreach($arGroup['OPTIONS'] as $strOption => $arOption){
						if(!is_string($arOption['TYPE']) || !strlen($arOption['TYPE'])){
							$arOption['TYPE'] = 'checkbox';
						}
						$strOptionId = $this->getOptionPrefix($strOption);
						if(is_callable($arOption['CALLBACK_HEAD_DATA'])){
							ob_start();
							call_user_func_array($arOption['CALLBACK_HEAD_DATA'], [$this, $arOption, $strOption, $strOptionId]);
							$APPLICATION->addHeadString(ob_get_clean());
						}
						$arTab['OPTIONS'][$strGroup]['OPTIONS'][$strOption] = $arOption;
					}
				}
			}
			$arTabs[$strTab] = $arTab;
		}
		#
		$this->handleAjaxOption();
		#
		if($this->isSaving()){
			if($this->save()){
				$this->redirect();
				print 'Saved!';
			}
			else {
				print 'Error!';
			}
		}
		$this->display();
	}
	
	/**
	 *	
	 */
	protected function handleAjaxOption(){
		$strAjaxOption = $this->arGet['wdu_ajax_option'];
		if(strlen($strAjaxOption)){
			foreach($this->arTabs as $arTab){
				if(is_array($arTab['OPTIONS'])){
					foreach($arTab['OPTIONS'] as $strGroup => $arGroup){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption){
							if($strOption == $strAjaxOption){
								$arJsonResult = Json::prepare();
								$arJsonResult['Title'] = '';
								$arJsonResult['Content'] = '';
								if(is_callable($arOption['CALLBACK_AJAX'])){
									call_user_func_array($arOption['CALLBACK_AJAX'], [&$arJsonResult, $this, $arOption, $strOption]);
								}
								Json::output($arJsonResult);
								die();
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 *	Get module id from this property
	 */
	public function getModuleId(){
		return $this->strModuleId;
	}
	
	/**
	 *	Get module code from this property
	 */
	public function getModuleCode(){
		return $this->strModuleCode;
	}
	
	/**
	 *	Get options prefix
	 */
	public function getOptionPrefix($strOptionName=null){
		return $this->strOptionIdPrefix.(is_string($strOptionName) && strlen($strOptionName) ? $strOptionName : '');
	}
	
	/**
	 *	Get options row id
	 */
	public function getOptionRowId($strOptionName){
		return $this->strOptionRowId.$strOptionName;
	}
	
	/**
	 *	Get options value
	 */
	public function getOptionValue($strOptionName){
		foreach($this->arTabs as $arTab){
			if(is_array($arTab['OPTIONS'])){
				foreach($arTab['OPTIONS'] as $strGroup => $arGroup){
					if(is_array($arGroup['OPTIONS'])){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption){
							if($strOption == $strOptionName) {
								if($arOption['USER']){
									return Helper::getUserOption($this->strModuleId, $strOptionName);
								}
								else{
									return Helper::getOption($this->strModuleId, $strOptionName);
								}
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 *	Set options value
	 */
	public function setOptionValue($strOptionName, $strValue){
		foreach($this->arTabs as $arTab){
			if(is_array($arTab['OPTIONS'])){
				foreach($arTab['OPTIONS'] as $strGroup => $arGroup){
					if(is_array($arGroup['OPTIONS'])){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption){
							if($strOption == $strOptionName) {
								if($arOption['USER']){
									return Helper::setUserOption($this->strModuleId, $strOptionName, $strValue);
								}
								else{
									return Helper::setOption($this->strModuleId, $strOptionName, $strValue);
								}
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 *	
	 */
	public function getTabControlId(){
		$strTabControlName = $this->arParams['TAB_CONTROL_NAME'];
		if(!is_string($strTabControlName) || !strlen($strTabControlName)){
			$strTabControlName = $this->strModuleCode;
		}
		$strTabControlName .= '_tabs';
		return $strTabControlName;
	}
	
	/**
	 *	Get $_GET
	 */
	public function getGet($strKey=false){
		return strlen($strKey) ? $this->arGet[$strKey] : $this->arGet;
	}
	
	/**
	 *	Get $_POST
	 */
	public function getPost($strKey=false){
		return strlen($strKey) ? $this->arPost[$strKey] : $this->arPost;
	}
	
	/**
	 *	Prepare options (include from file in /include/options/)
	 *	It all depends on start slash:
	 *	[ test/test.php]: /bitrix/modules/webdebug.utilities/include/options/
	 *	[/test/test.php]: /bitrix/modules/{$this->strModuleId}/include/options/
	 */
	public function prepareOptions($arOptions){
		$arResult = [];
		$strOptionsPath = realpath(__DIR__.'/../include/options/');
		$strOptionsPathModule = realpath(__DIR__.'/../../'.$this->strModuleId.'/include/options/');
		foreach($arOptions as $strFile){
			if(substr($strFile, 0, 1) == '/'){
				$strFilename = $strOptionsPathModule.'/'.substr($strFile, 1);
			}
			else{
				$strFilename = $strOptionsPath.'/'.$strFile;
			}
			if(is_file($strFilename)){
				$arNewOptions = require($strFilename);
				if(!isset($arNewOptions['NAME']) || !strlen($arNewOptions['NAME'])){
					$strGroupLangCode = toUpper($strFile);
					$strGroupLangCode = preg_replace('#\.php$#i', '', $strGroupLangCode);
					$strGroupLangCode = str_replace('/', '_', $strGroupLangCode);
					$arNewOptions['NAME'] = Helper::getMessage(static::LANG.'GROUP_'.$strGroupLangCode);
				}
				foreach($arNewOptions['OPTIONS'] as $strOption => &$arOption){
					$arOption = array_merge([
						'NAME' => Helper::getMessage(static::LANG.'NAME_'.toUpper($strOption)),
						'HINT' => Helper::getMessage(static::LANG.'HINT_'.toUpper($strOption)),
					], $arOption);
					if(!strlen($arOption['NAME'])){
						$arOption['NAME'] = $strOption;
					}
				}
				$arResult[] = array_merge($arResult, $arNewOptions);
			}
		}
		return $arResult;
	}
	
	/**
	 *	Check if saving in progress
	 */
	public function isSaving(){
		return $this->bPost && ($this->bUpdate || $this->bApply || $this->bReset);
	}
	
	/**
	 *	Do save options
	 */
	public function save(){
		global $APPLICATION;
		if($this->bUpdate || $this->bApply){
			// Fix old values
			$arOldValues = [];
			$arNewValues = [];
			foreach($this->arTabs as $strTab => $arTab){
				if(is_array($arTab['OPTIONS'])){
					foreach($arTab['OPTIONS'] as $arGroup){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption) {
							$arOldValues[$strOption] = $this->getOptionValue($this->strModuleId, $strOption);
						}
					}
				}
			}
			// Save new values
			foreach($this->arTabs as $strTab => $arTab){
				if(is_array($arTab['OPTIONS'])){
					foreach($arTab['OPTIONS'] as $arGroup){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption) {
							$strValue = $this->arPost[$strOption];
							$arNewValues[$strOption] = $strValue;
							$arOption['ORIGINAL_VALUE'] = $strValue;
							if(is_array($strValue)){
								$strValue = implode(',', $strValue);
							}
							if(is_callable($arOption['CALLBACK_BEFORE_SAVE'])){
								$strOptionId = $this->getOptionPrefix($strOption);
								call_user_func_array($arOption['CALLBACK_BEFORE_SAVE'], [$this, &$strValue, $arOption, $strOption, 
									$strOptionId]);
							}
							$this->setOptionValue($strOption, $strValue);
						}
					}
				}
			}
			// After all options saved
			foreach($this->arTabs as $strTab => $arTab){
				if(is_array($arTab['OPTIONS'])){
					foreach($arTab['OPTIONS'] as $arGroup){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption) {
							if(is_callable($arOption['CALLBACK_SAVE'])){
								$arOption['VALUE_OLD'] = $arOldValues[$strOption];
								$arOption['VALUE_NEW'] = $arNewValues[$strOption];
								$strOptionId = $this->getOptionPrefix($strOption);
								call_user_func_array($arOption['CALLBACK_SAVE'], [$this, $arOption, $strOption, $strOptionId]);
							}
						}
					}
				}
			}
		}
		elseif($this->bReset){
			Helper::deleteAllOptions($this->strModuleId);
		}
		#
		ob_start();
		$module_id = $this->strModuleId; // required for save rights
		$REQUEST_METHOD = $this->bPost ? 'POST' : 'GET'; // required for save rights
		$Update = $this->bUpdate || $this->bApply ? 'Y' : 'N'; // required for save rights
		$GROUPS = $GLOBALS['GROUPS']; // required for save rights
		$RIGHTS = $GLOBALS['RIGHTS']; // required for save rights
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');
		ob_end_clean();
		#
		if(is_callable($this->arParams['CALLBACK_SAVE'])){
			call_user_func_array($this->arParams['CALLBACK_SAVE'], [$this]);
		}
		#
		return true;
	}
	
	/**
	 *	Redirect after save
	 */
	public function redirect(){
		global $APPLICATION;
		$mUrl = null;
		if(strlen($this->arGet['back_url_settings']) && $this->bUpdate){
			$mUrl = $this->arGet['back_url_settings'];
		}
		elseif(strlen($this->arGet['back_url_settings']) && ($this->bApply || $bthis->Reset)){
			$mUrl = [
				'lang' => LANGUAGE_ID,
				'mid' => $this->strModuleId,
				'back_url_settings' => $arGet['back_url_settings'],
			];
		}
		else{
			$mUrl = [
				'lang' => LANGUAGE_ID,
				'mid' => $this->strModuleId,
			];
		}
		if(is_array($mUrl)){
			$mUrl = $APPLICATION->getCurPage(false).'?'.http_build_query($mUrl);
			if(is_object($this->obTabControl)){
				$mUrl .= '&'.$this->obTabControl->activeTabParam();
			}
		}
		LocalRedirect($mUrl, false, 302);
	}
	
	/**
	 *	Display all
	 */
	public function display(){
		$this->start();
		foreach($this->arTabs as $strTab => $arTab){
			if(is_array($arTab['OPTIONS'])){
				foreach($arTab['OPTIONS'] as $strGroup => $arGroup){
					if(is_array($arGroup['OPTIONS'])){
						foreach($arGroup['OPTIONS'] as $strOption => $arOption){
							#Helper::P($strOption);
							$strValue = $this->getOptionValue($strOption);
							$this->arTabs[$strTab]['OPTIONS'][$strGroup]['OPTIONS'][$strOption]['VALUE'] = $strValue;
						}
					}
				}
			}
		}
		foreach($this->arTabs as $strTab => $arTab){
			$this->next();
			if(is_callable($arTab['CALLBACK'])){
				call_user_func_array($arTab['CALLBACK'], [$this, $arTab]);
			}
			elseif(is_array($arTab['OPTIONS'])){
				$this->displayOptions($arTab['OPTIONS']);
			}
			elseif(isset($arTab['DATA'])){
				print '<tr>';
					print '<td>';
						print $arTab['DATA'];
					print '</td>';
				print '</tr>';
			}
			elseif($arTab['RIGHTS']){
				global $APPLICATION;
				$module_id = $this->strModuleId; // required for obtain rights
				require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');
			}
			else{
				print 'Empty!';
			}
		}
		$this->end();
	}
	
	/**
	 *	Display options block
	 */
	public function displayOptions($arOptions){
		print Helper::includeFile(WDU_MODULE, 'options', 'default', [
			'OPTIONS' => $arOptions,
			'THIS' => $this,
		]);
	}
	
	/**
	 *	Start tab
	 */
	public function start(){
		global $APPLICATION;
		$arQuery = [
			'lang' => LANGUAGE_ID,
			'mid' => $this->strModuleId,
		];
		if(strlen($this->arGet['back_url_settings'])){
			$arQuery['back_url_settings'] = $this->arGet['back_url_settings'];
		}
		?>
		<form method="post" action="<?=$APPLICATION->GetCurPage();?>?<?=http_build_query($arQuery);?>">
		<?
		print bitrix_sessid_post();
		$this->obTabControl->begin();
	}
	
	/**
	 *	Begin new tab
	 */
	public function next(){
		$this->obTabControl->beginNextTab();
	}
	
	/**
	 *	End tabs, show buttons
	 */
	public function end(){
		$bDisabled = !!$this->arParams['DISABLED'];
		$strBackUrl = $this->strBackUrl;
		?>
		</form>
		<?
		$this->obTabControl->buttons();
		?>
			<input<?if($bDisabled):?> disabled="disabled"<?endif?> class="adm-btn-save" type="submit" name="Update" 
				value="<?=Helper::getMessage('MAIN_SAVE')?>" title="<?=Helper::getMessage('MAIN_OPT_SAVE_TITLE')?>" />
			<input<?if($bDisabled):?> disabled="disabled"<?endif?> type="submit" name="Apply" 
				value="<?=Helper::getMessage('MAIN_OPT_APPLY')?>" title="<?=Helper::getMessage('MAIN_OPT_APPLY_TITLE')?>" />
			<?if(strlen($strBackUrl)):?>
				<input<?if($bDisabled):?> disabled="disabled"<?endif?> type="button" name="Cancel" 
					value="<?=Helper::getMessage('MAIN_OPT_CANCEL')?>" title="<?=Helper::getMessage('MAIN_OPT_CANCEL_TITLE')?>" 
					onclick="window.location='<?=htmlspecialcharsbx(\CUtil::addSlashes($strBackUrl))?>';" />
				<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($strBackUrl)?>" />
			<?endif?>
			<input<?if($bDisabled):?> disabled="disabled"<?endif?> type="submit" name="RestoreDefaults"
				value="<?=Helper::getMessage('MAIN_RESTORE_DEFAULTS')?>"
				title="<?=Helper::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>"
				onclick="return confirm('<?=addSlashes(Helper::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')">
		<?
		$this->obTabControl->end();
	}

}
