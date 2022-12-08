<?
namespace WD\Antirutin;

use
	\WD\Antirutin\Cli,
	\WD\Antirutin\Helper,
	\WD\Antirutin\Filter,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\ProfileTable as Profile,
	\WD\Antirutin\Thread;

Helper::loadMessages(__FILE__);
	
class Worker {
	
	const TYPE_START = 'start';
	const TYPE_CONTINUE = 'continue';
	
	const RESULT_SUCCESS = 'SUCCESS';
	const RESULT_BREAKED = 'BREAKED';
	const RESULT_ERROR = 'ERROR';
	
	const SESSION_KEY = 'WD_ANTIRUTIN_SESSION';
	
	const LANG = 'WDA_WORKER_';
	
	protected $bCron = false;
	protected $intTimeStart = null;
	protected $intTimeLimit = null;
	protected $strExecuteType = null;
	protected $strEntityType = null;
	protected $intIBlockId = null;
	protected $arSectionsId = [];
	protected $bIncludeSubsections = false;
	protected $strFilter = null;
	protected $arActions = [];
	protected $arSession = [];
	protected $arSessionCopy = []; // Using after close session on success 
	protected $intLastId = null;
	protected $arPlugins = [];
	protected $arDebugMessage = [];
	protected $intProfileId = null;
	protected $strError = null;
	protected $arSettings = [];
	protected $bBreaked = false;
	
	/**
	 *	Create worker
	 */
	public function __construct($bLikeCron=false){
		$this->bCron = $bLikeCron === true || php_sapi_name == 'cli' || defined('WDA_CRON');
		if(!$this->bCron){
			$this->intTimeStart = time();
			$this->setTimeLimit($this->getTimeLimit());
		}
	}
	
	/**
	 *	Get time limit per step from options
	 */
	public function getTimeLimit($intTime=null){
		$intTime = intVal($intTime);
		$intTime = $intTime > 0 ? $intTime : Helper::getOption('step_time');
		$intTime = intVal(Helper::getOption('step_time'));
		if($intTime <= 0){
			$intTime = 10;
		}
		return $intTime;
	}
	
	/**
	 *	Set time limit per step (for http run in admin section)
	 */
	public function setTimeLimit($intTimeLimit){
		$this->intTimeLimit = $intTimeLimit;
	}
	
	/**
	 *	Set execute type
	 */
	public function setExecuteType($strExecuteType){
		$this->strExecuteType = $strExecuteType;
	}
	
	/**
	 *	Set worker type (element | section)
	 */
	public function setEntityType($strEntityType){
		$this->strEntityType = $strEntityType;
	}
	
	/**
	 *	
	 */
	public function isElement(){
		return $this->strEntityType == Helper::TYPE_ELEMENT;
	}
	
	/**
	 *	
	 */
	public function isSection(){
		return $this->strEntityType == Helper::TYPE_SECTION;
	}
	
	/**
	 *	Set iblock id
	 */
	public function setIBlockId($intIBlockId){
		$this->intIBlockId = $intIBlockId;
	}
	
	/**
	 *	Set iblock sections id
	 */
	public function setIBlockSectionsId($arSectionsId, $bIncludeSubsections){
		$this->arSectionsId = $arSectionsId;
		$this->bIncludeSubsections = $bIncludeSubsections;
	}
	
	/**
	 *	Set filter json
	 */
	public function setFilter($strFilterJson){
		$this->strFilter = $strFilterJson;
	}
	
	/**
	 *	Set settings
	 */
	public function setSettings($arSettings){
		if(is_array($arSettings)){
			$this->arSettings = $arSettings;
		}
	}
	
	/**
	 *	Set actions
	 */
	public function setActions($arActions){
		$this->arActions = $arActions;
	}
	
	/**
	 *	Set last id
	 */
	public function setLastId($intLastId){
		$this->intLastId = $intLastId;
	}
	
	/**
	 *	Build filter from string
	 */
	protected function getFilter($bLastId=false){
		$obFilter = new Filter($this->intIBlockId, $this->strEntityType);
		$obFilter->setSectionsId($this->arSectionsId);
		$obFilter->setIncludeSubsections($this->bIncludeSubsections);
		if(strlen($this->strFilter)){
			$obFilter->setJson($this->strFilter);
		}
		$arFilter = $obFilter->buildFilter();
		if($bLastId && $this->arSession['LAST_ID'] > 0){
			$arFilter['>ID'] = $this->arSession['LAST_ID'];
		}
		$obFilter = null;
		return $arFilter;
	}
	
	/**
	 *	Open session
	 */
	protected function openSession(){
		$bResult = false;
		$this->arSessionCopy = null;
		if($this->bCron){
			$this->arSession = &$GlOBALS[static::SESSION_KEY];
		}
		else{
			$this->arSession = &$_SESSION[static::SESSION_KEY];
		}
		switch($this->strExecuteType){
			case static::TYPE_START:
				$this->arSession = [
					'SESSION_ID' => Helper::randString(),
					'FILTER' => $this->strFilter,
					'ACTIONS' => $this->arActions,
					'TIME_START' => microtime(true),
					'TIME_END' => null,
					'COUNT' => $this->getRealCount(),
					'INDEX' => 0,
					'COUNT_SUCCESS' => 0,
					'COUNT_ERROR' => 0,
					'LAST_ID' => 0,
					'USER_DATA' => [],
					'SETTINGS' => $this->arSettings,
				];
				$bResult = true;
				break;
			case static::TYPE_CONTINUE:
				if(is_array($this->arSession)){
					$this->setFilter($this->arSession['FILTER']);
					$this->setActions($this->arSession['ACTIONS']);
					$bResult = true;
				}
				break;
		}
		if(!$this->bCron && $this->arSession['SETTINGS']['step_time'] > 0){
			$this->setTimeLimit($this->arSession['SETTINGS']['step_time']);
		}
		return $bResult;
	}
	
	/**
	 *	Get session id
	 */
	public function getSessionId(){
		return $this->arSession['SESSION_ID'];
	}
	
	/**
	 *	Close session
	 */
	protected function closeSession(){
		$this->arSessionCopy = $this->arSession;
		if($this->bCron){
			$GlOBALS[static::SESSION_KEY] = null;
			unset($GlOBALS[static::SESSION_KEY]);
		}
		else{
			$_SESSION[static::SESSION_KEY] = null;
			unset($_SESSION[static::SESSION_KEY]);
		}
		$this->arSession = null;
	}
	
	/**
	 *	Get real elements count by filtering
	 */
	protected function getRealCount(){
		$arFilter = $this->getFilter(false);
		if($this->isElement()){
			return IBlock::getElementCount($arFilter);
		}
		elseif($this->isSection()){
			return IBlock::getSectionCount($arFilter);
		}
		return 0;
	}
	
	/**
	 *	Time is over
	 */
	protected function timeIsOver(){
		if($this->bCron) {
			return false;
		}
		return (time() - $this->intTimeStart >= $this->intTimeLimit);
	}
	
	/**
	 *	Create plugin objects
	 *	This method must be called after openSession()
	 */
	protected function createPlugins(){
		$this->arPlugins = [];
		$arPluginsAll = Helper::findPlugins($this->strEntityType, $bGroup=false);
		foreach($this->arActions as $strHash => $arAction){
			$strPluginCode = $arAction['_plugin'];
			$arPluginData = $arPluginsAll[$strPluginCode];
			if(is_array($arPluginData)){
				$this->arPlugins[$arPluginData['CODE']][$strHash] = new $arPluginData['CLASS'];
				$this->arPlugins[$arPluginData['CODE']][$strHash]->setId($strHash);
				$this->arPlugins[$arPluginData['CODE']][$strHash]->setEntityType($this->strEntityType);
				$this->arPlugins[$arPluginData['CODE']][$strHash]->setIBlockId($this->intIBlockId);
				$this->arPlugins[$arPluginData['CODE']][$strHash]->setSavedData($arAction);
				$this->arPlugins[$arPluginData['CODE']][$strHash]->setSections($this->arSectionsId, $this->bIncludeSubsections);
				$this->arPlugins[$arPluginData['CODE']][$strHash]->setWorker($this);
			}
		}
	}
	
	/**
	 *	Execute plugin handlers: onStart, onContinue, onFinish
	 */
	protected function executePluginHandlers($strHandler){
		$mResult = null;
		foreach($this->arPlugins as $strPluginCode => $arPlugins){
			foreach($arPlugins as $strHash => $obPlugin){
				$mHandlerResult = call_user_func([$obPlugin, $strHandler]);
				if(!is_null($mHandlerResult)){
					$mResult = $mHandlerResult;
				}
			}
		}
		return $mResult;
	}
	
	/**
	 *	Is process single-time?
	 */
	protected function isSingleTime(){
		$bResult = false;
		foreach($this->arPlugins as $strPluginCode => $arPlugins){
			foreach($arPlugins as $strHash => $obPlugin){
				if($obPlugin->isSingleTime()){
					$bResult = true;
					break 2;
				}
			}
		}
		return $bResult;
	}
	
	/**
	 *	Get is breaked
	 */
	public function getBreaked(){
		return $this->bBreaked;
	}
	
	/**
	 *	Set breaked
	 */
	public function setBreaked($bBreaked=true){
		$this->bBreaked = $bBreaked;
	}
	
	/**
	 *	Execute process
	 *	@return array for json
	 */
	public function execute(){
		if(!is_numeric($this->intIBlockId) || $this->intIBlockId <= 0){ 
			return $this->error('Empty iblock!');
		}
		if(!\Bitrix\Main\Loader::includeModule('iblock')){
			return $this->error('No iblock module');
		}
		# Start session
		if(!$this->openSession()){
			return $this->error('Error opening session');
		}
		# Create plugins for current step
		$this->createPlugins($this->strEntityType);
		# Prepare
		if(!$this->arSession['COUNT']){
			$this->addDebugMessage(Helper::getMessage(static::LANG.'NOTHING_FOUND_'.$this->strEntityType));
		}
		elseif($this->isSingleTime()){
			$this->arSession['COUNT'] = 1;
		}
		# onStart event
		if($this->strExecuteType == static::TYPE_START) {
			$strHandlerError = $this->executePluginHandlers('onStart');
		}
		elseif($this->strExecuteType == static::TYPE_CONTINUE){
			$strHandlerError = $this->executePluginHandlers('onContinue');
		}
		if(!is_null($strHandlerError)){
			$this->closeSession();
			return $this->error($strHandlerError);
		}
		# Execute!
		if($this->isElement()){
			$strResult = $this->executeElements();
		}
		elseif($this->isSection()){
			$strResult = $this->executeSections();
		}
		# onFinish event
		if($strResult == static::RESULT_SUCCESS){
			$this->executePluginHandlers('onFinish');
			$this->closeSession();
		}
		return $this->returnResult($strResult);
	}
	
	/**
	 *	Execute iblock elements
	 */
	protected function executeElements(){
		$arSort = ['ID' => 'ASC'];
		$arFilter = $this->getFilter(true);
		$arSelect = ['ID'];
		$intLimit = false;
		if($this->isSingleTime()){
			$intLimit = 1;
		}
		$resItems = IBlock::getElementList($arSort, $arFilter, $arSelect, $intLimit);
		while($arItem = $resItems->fetch()){
			$this->arSession['INDEX']++;
			$intElementId = intVal($arItem['ID']);
			if($this->processElement($intElementId)){
				$this->arSession['COUNT_SUCCESS']++;
			}
			else{
				$this->arSession['COUNT_ERROR']++;
				if(!isset($this->arSession['FIRST_ERROR'])){
					$this->arSession['FIRST_ERROR'] = $this->strError;
				}
			}
			$this->arSession['LAST_ID'] = $intElementId;
			if($this->getBreaked()){
				$this->arSession['TIME_END'] = microtime(true);
				return static::RESULT_SUCCESS;
			}
			if($this->timeIsOver()){
				return static::RESULT_BREAKED;
			}
		}
		$this->arSession['TIME_END'] = microtime(true);
		return static::RESULT_SUCCESS;
	}
	
	/**
	 *	Process single element
	 */
	protected function processElement($intElementId){
		$bResult = true;
		foreach($this->arPlugins as $strPluginCode => $arPlugins){
			foreach($arPlugins as $strHash => $obPlugin){
				$arLogData = [
					'#ELEMENT_ID#' => $intElementId,
					'#PLUGIN_NAME#' => $obPlugin->getName(),
				];
				$obPlugin->log(Helper::getMessage(static::LANG.'LOG_ELEMENT_START', $arLogData), true);
				$mElementResult = $obPlugin->processElement($intElementId);
				if($mElementResult === true) {
					$obPlugin->log(Helper::getMessage(static::LANG.'LOG_ELEMENT_END', $arLogData), true);
				}
				else{
					$arLogData['#ERROR#'] = $obPlugin->getError();
					if(!strlen($arLogData['#ERROR#'])){
						$arLogData['#ERROR#'] = Helper::getMessage(static::LANG.'LOG_ELEMENT_ERROR_UNKNOWN');
					}
					$this->strError = Helper::getMessage(static::LANG.'LOG_ELEMENT_ERROR', $arLogData);
					$obPlugin->log($this->strError);
					$bResult = false;
					break 2;
				}
			}
		}
		return $bResult;
	}
	
	/**
	 *	Execute iblock sections
	 */
	protected function executeSections(){
		$arSort = ['ID' => 'ASC'];
		$arFilter = $this->getFilter(true);
		$arSelect = ['ID'];
		$intLimit = false;
		if($this->isSingleTime()){
			$intLimit = 1;
		}
		$resSections = IBlock::getSectionList($arSort, $arFilter, $arSelect, $intLimit);
		while($arSection = $resSections->fetch()){
			$this->arSession['INDEX']++;
			$intSectionId = intVal($arSection['ID']);
			if($this->processSection($intSectionId)){
				$this->arSession['COUNT_SUCCESS']++;
			}
			else{
				$this->arSession['COUNT_ERROR']++;
				if(!isset($this->arSession['FIRST_ERROR'])){
					$this->arSession['FIRST_ERROR'] = $this->strError;
				}
			}
			$this->arSession['LAST_ID'] = $intSectionId;
			if($this->getBreaked()){
				$this->arSession['TIME_END'] = microtime(true);
				return static::RESULT_SUCCESS;
			}
			if($this->timeIsOver()){
				return static::RESULT_BREAKED;
			}
		}
		$this->arSession['TIME_END'] = microtime(true);
		return static::RESULT_SUCCESS;
	}
	
	/**
	 *	Process single section
	 */
	protected function processSection($intSectionId){
		$bResult = true;
		foreach($this->arPlugins as $strPluginCode => $arPlugins){
			foreach($arPlugins as $strHash => $obPlugin){
				$arLogData = [
					'#SECTION_ID#' => $intSectionId,
					'#PLUGIN_NAME#' => $obPlugin->getName(),
				];
				$obPlugin->log(Helper::getMessage(static::LANG.'LOG_SECTION_START', $arLogData), true);
				$mSectionResult = $obPlugin->processSection($intSectionId);
				if($mSectionResult){
					$obPlugin->log(Helper::getMessage(static::LANG.'LOG_SECTION_END', $arLogData), true);
				}
				else{
					$arLogData['#ERROR#'] = $obPlugin->getError();
					if(!strlen($arLogData['#ERROR#'])){
						$arLogData['#ERROR#'] = Helper::getMessage(static::LANG.'LOG_SECTION_ERROR_UNKNOWN');
					}
					$this->strError = Helper::getMessage(static::LANG.'LOG_SECTION_ERROR', $arLogData);
					$obPlugin->log($this->strError);
					$bResult = false;
					break 2;
				}
			}
		}
		return $bResult;
	}
	
	/**
	 *	
	 */
	protected function error($strMessage){
		return [
			'ErrorText' => $strMessage,
		];
	}
	
	/**
	 *	Set whole debug message
	 */
	public function setDebugMessage($mMessage){
		$this->arDebugMessage = $mMessage;
	}
	
	/**
	 *	Add item to debug message
	 */
	public function addDebugMessage($mMessage){
		$this->arDebugMessage[] = $mMessage;
	}
	
	/**
	 *	Get return array for execute();
	 */
	protected function returnResult($strResultCode){
		$arSession = is_array($this->arSession) ? $this->arSession : $this->arSessionCopy;
		$strType = $this->isSection() ? 'SECTION' : 'ELEMENT';
		if($arSession['COUNT'] > 0 && $arSession['INDEX'] > $arSession['COUNT']){ # If new items have been added during the process
			$arSession['COUNT'] = $arSession['INDEX'];
		}
		$arResult = [
			'Success' => $strResultCode == static::RESULT_SUCCESS,
			'Continue' => $strResultCode == static::RESULT_BREAKED,
			'Error' => $strResultCode == static::RESULT_ERROR,
			'Count' => $arSession['COUNT'],
			'CountSuccess' => $arSession['COUNT_SUCCESS'],
			'CountError' => $arSession['COUNT_ERROR'],
			'FirstError' => $arSession['FIRST_ERROR'],
			'Index' => $arSession['INDEX'],
			'Percent' => $arSession['COUNT'] > 0 ? round($arSession['INDEX'] * 100 / $arSession['COUNT'], 2) : -1,
		];
		if(is_array($this->arDebugMessage) && !empty($this->arDebugMessage)){
			$arResult['DebugMessage'] = implode(PHP_EOL, $this->arDebugMessage);
		}
		elseif(is_string($this->arDebugMessage) && strlen($this->arDebugMessage)){
			$arResult['DebugMessage'] = $this->arDebugMessage;
		}
		if($this->isShowResultsPopup()){
			$arResult['ResultsHtml'] = Helper::includeFile('results_html', ['WORKER' => $this, 'SESSION' => $arSession, 
				'TYPE' => $strType]);
		}
		return $arResult;
	}
	
	/**
	 *	Whether to show results popup
	 */
	protected function isShowResultsPopup(){
		$arSession = is_array($this->arSession) ? $this->arSession : $this->arSessionCopy;
		$strValueModule = Helper::getOption('show_results');
		$strValueCustom = $arSession['SETTINGS']['show_results_popup'];
		if($strValueCustom == 'D' && array_key_exists($strValueModule, static::getOptionsShowResults())){
			$strValueCustom = $strValueModule;
		}
		if($strValueCustom == 'Y'){
			return true;
		}
		elseif($strValueCustom == 'N'){
			return false;
		}
		elseif($strValueCustom == 'E'){
			if(strlen($arSession['FIRST_ERROR'])){
				return true;
			}
			else{
				return false;
			}
		}
		return true;
	}
	
	/**
	 *	Get variants for 'show results'
	 */
	public static function getOptionsShowResults(){
		return [
			'Y' => Helper::getMessage(static::LANG.'SHOW_RESULTS_Y'),
			'E' => Helper::getMessage(static::LANG.'SHOW_RESULTS_E'),
			'N' => Helper::getMessage(static::LANG.'SHOW_RESULTS_N'),
		];
	}
	
	/**
	 *	Execute from cli
	 */
	public static function executeCron($strProfileId){
		$intProfileId = intVal($strProfileId);
		if($intProfileId <= 0) {
			print sprintf('Wrong profile id: \'%s\'.', $strProfileId);
		}
		return static::executeProfile($intProfileId);
	}
	
	/**
	 *	
	 */
	public function setProfileId($intProfileId){
		$this->intProfileId = $intProfileId;
	}
	
	/**
	 *	
	 */
	public function getProfileId(){
		return $this->intProfileId;
	}
	
	/**
	 *	Execute single profile
	 */
	public static function executeProfile($intProfileId){
		$arProfile = Helper::getProfileArray($intProfileId);
		if(!is_array($arProfile) || empty($arProfile)){
			print sprintf('Profile [%d] is not found.', $intProfileId);
			return false;
		}
		$arProfile['ACTIONS'] = Profile::getProfileActions($intProfileId);
		#
		$intIBlockId = intVal($arProfile['IBLOCK_ID']);
		$bIncludeSubsections = $arProfile['INCLUDE_SUBSECTIONS'] == 'Y';
		$arSectionsId = strlen($arProfile['SECTIONS_ID']) && $arProfile['SECTIONS_ID'] != '-' 
			? explode(',', $arProfile['SECTIONS_ID']) : [];
		$strFilter = $arProfile['FILTER'];
		#
		$arActions = [];
		if(is_array($arProfile['ACTIONS'])){
			foreach($arProfile['ACTIONS'] as $arAction){
				$arActions[$arAction['HASH']] = $arAction['PARAMS'];
			}
		}
		#
		$obWorker = new static(true);
		$obWorker->setExecuteType(static::TYPE_START);
		$obWorker->setProfileId($intProfileId);
		$obWorker->setEntityType($arProfile['ENTITY_TYPE']);
		$obWorker->setIBlockId($intIBlockId);
		$obWorker->setIBlockSectionsId($arSectionsId, $bIncludeSubsections);
		if(strlen($strFilter)){
			$obWorker->setFilter($strFilter);
		}
		$obWorker->setActions($arActions);
		#
		return $obWorker->execute();
	}
	
	/**
	 *	
	 */
	public function getSession(){
		return $this->arSession;
	}
	
	/**
	 *	
	 */
	public function getSessionKey($key){
		return $this->arSession[$key];
	}
	
	/**
	 *	
	 */
	public static function run($intProfileId){
		if(is_numeric($intProfileId) && $intProfileId > 0){
			$arCommand = Cli::getFullCommand(null, $intProfileId);
			if(is_array($arCommand) && strlen($arCommand['COMMAND'])){
				if(Cli::isProcOpen()){
					$arArguments = [];
					if(is_object($GLOBALS['USER']) && $GLOBALS['USER']->isAuthorized()){
						$arArguments['user'] = $GLOBALS['USER']->getId();
					}
					$obThread = new Thread($arCommand['COMMAND'], $arArguments);
					$intPid = $obThread->getPid();
					unset($obThread);
					if(is_numeric($intPid) && $intPid > 0){
						return $intPid;
					}
				}
			}
		}
		return false;
		/*
		$bRunInBackground = false;
		$arDebug = debug_backtrace(2);
		if(is_array($arDebug) && !empty($arDebug)){
			$strHaystack = Helper::path($arDebug[0]['file']);
			$strNeedle = '/bitrix/modules/acrit.core/admin/export/profile_edit.php';
			if(stripos($strHaystack, $strNeedle) !== false){
				$bRunInBackground = true;
			}
		}
		// We allow to execute Exporter::run just by click on button 'Run in background' (besides of exportproplus)
		if(end(\Acrit\Core\Export\Exporter::getInstance($strModuleId)->getExportModules(true)) != $strModuleId){
			if(!$bRunInBackground){
				return false;
			}
		}
		if(Cli::isProcOpen()) {
			$arProfilesID = is_array($mProfileID) ? $mProfileID : array($mProfileID);
			$arCli = Cli::getFullCommand($strModuleId, 'export.php', $mProfileID, 
				is_numeric($intProfileID) ? Log::getInstance($strModuleId)->getLogFilename($intProfileID) : null);
			#
			foreach($arProfilesID as $intProfileID){
				Log::getInstance($strModuleId)->add(Loc::getMessage('ACRIT_EXP_LOG_CUSTOM_RUN', array(
					'#COMMAND#' => $arCli['COMMAND'],
				)), $intProfileID, $bRunInBackground);
			}
			#
			$arArguments = [];
			if(is_object($GLOBALS['USER']) && $GLOBALS['USER']->isAuthorized()){
				$arArguments['user'] = $GLOBALS['USER']->getId();
			}
			$obThread = new Thread($arCli['COMMAND'], $arArguments);
			$intPid = $obThread->getPid();
			unset($obThread);
			if(is_numeric($intPid) && $intPid > 0){
				usleep(50000);
				return $intPid;
			}
		}
		return false;
		*/
	}
	
}
?>