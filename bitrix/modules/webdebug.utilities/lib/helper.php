<?
namespace WD\Utilities;

use
	\WD\Utilities\Cli,
	\WD\Utilities\Filter,
	\WD\Utilities\IBlock,
	\WD\Utilities\Json,
	\WD\Utilities\HttpRequest;

Helper::loadMessages();

final class Helper {
	
	# Constants
	const ARRAY_INSERT_BEGIN = '_ARRAY_INSERT_BEGIN_';
	const ARRAY_INSERT_AFTER = '_ARRAY_INSERT_AFTER_';
	const ARRAY_INSERT_BEFORE = '_ARRAY_INSERT_BEFORE_';
	const RAND_ID_PREFIX = 'rand_id_';
	
	# Caching
	static $arCache = [];
	const CACHE_CATALOG_ARRAY = 'CATALOG_ARRAY';
	const CACHE_PLUGIN_PATHS = 'PLUGIN_PATHS';
	const CACHE_PLUGINS = 'PLUGINS';
	const CACHE_CURRENCIES = 'CURRENCIES';
	const CACHE_CURRENCY_CONVERT_FACTOR = 'CURRENCY_CONVERT_FACTOR';
	const CACHE_MEASURES = 'MEASURES';
	const CACHE_VAT = 'VAT';
	const CACHE_VAT_VALUE = 'VAT_VALUES';
	const CACHE_QUERY = 'REQUEST_QUERY';
	const CACHE_IBLOCK_FIELDS = 'IBLOCK_FIELDS';
	const CACHE_PRICES = 'PRICES';
	const CACHE_STORES = 'STORES';
	
	# Other options
	static $fMem = 0;
	
	/**
	 *	Debug
	 */
	public static function P($arData) {
		if($bJust && is_object($GLOBALS['APPLICATION'])){
			static::obRestart();
		}
		$strId = 'pre_'.static::randString();
		$strResult = '<style>pre#'.$strId.'{background:none repeat scroll 0 0 #FAFAFA; border-color:#AAB4BE #AAB4BE #AAB4BE #B4B4B4; border-style:dotted dotted dotted solid; border-width:1px 1px 1px 20px; font:normal 11px \"Courier New\",\"Courier\",monospace; margin:10px 0; padding:5px 0 5px 10px; position:relative; text-align:left; white-space:pre-wrap; word-break: break-all; -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;}</style>';
		if(is_array($arData) && empty($arData))
			$arData = '--- Array is empty ---';
		if($arData === false)
			$arData = '[false]';
		elseif ($arData === true)
			$arData = '[true]';
		elseif ($arData === null)
			$arData = '[null]';
		$strResult .= '<pre id="'.$strId.'">'.print_r($arData, true).'</pre>';
		print $strResult;
	}
	
	/**
	 *	Log
	 */
	function L($mMessage, $strFilename=false){
		if(is_array($mMessage)) {
			$mMessage = print_r($mMessage,true);
		}
		$intTime = microtime(true);
		$strMicroTime = sprintf('%06d',($intTime - floor($intTime)) * 1000000);
		$obDate = new \DateTime(date('d.m.Y H:i:s.'.$strMicroTime, $intTime));
		$strTime = $obDate->format('d.m.Y H:i:s.u');
		if(!is_string($strFilename)) {
			if(defined('LOG_FILENAME') && strlen(LOG_FILENAME)) {
				$strFilename = LOG_FILENAME;
			}
			else {
				$strDir = static::getUploadDir('log', true);
				$strFilename = $strDir.static::getOption('main', 'server_uniq_id').'.txt';
			}
		}
		$resHandle = fopen($strFilename, 'a+');
		@flock($resHandle, LOCK_EX);
		fwrite($resHandle, '['.$strTime.'] '.$mMessage.PHP_EOL);
		@flock($resHandle, LOCK_UN);
		fclose($resHandle);
		unset($obDate, $resHandle, $intTime, $strMicroTime, $strTime);
	}
	
	/**
	 *	Restart buffering
	 */
	public static function obRestart(){
		$GLOBALS['APPLICATION']->restartBuffer();
	}
	
	/**
	 *	Stop buffering
	 */
	public static function obStop(){
		while(ob_get_level()){
			ob_clean();
		}
	}
	
	/**
	 *	Get document root
	 */
	public static function root(){
		return \Bitrix\Main\Loader::getDocumentRoot();
	}
	
	/**
	 *	Is site works on UTF-8?
	 */
	public static function isUtf() {
		return defined('BX_UTF') && BX_UTF === true;
	}
	
	/**
	 *	Check if site works via HTTPS
	 */
	public static function isHttps() {
		return \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps();
	}
	
	/**
	 *	Is value empty?
	 */
	public static function isEmpty($mValue) {
		if(empty($mValue)){
			return true;
		}
		return false;
	}
	
	/**
	 *	SQL-query
	 */
	public static function query($strSql){
		return \Bitrix\Main\Application::getConnection()->query($strSql);
	}
	
	/**
	 *	Prepare string for use in SQL
	 */
	public static function forSql($strValue){
		return $GLOBALS['DB']->forSql($strValue);
	}
	
	/**
	 *	Wrapper for Loc::loadMessages()
	 *	ToDo: сделать $strFile необязательным
	 */
	public static function loadMessages($strFile=false){
		if(!is_string($strFile) || !strlen($strFile)){
			$arDebug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
			$strFile = $arDebug[0]['file'];
		}
		\Bitrix\Main\Localization\Loc::loadMessages($strFile);
	}
	
	/**
	 *	Analog for Loc::getMessage()
	 */
	public static function getMessage($strMessage, $strPrefix=null, $arReplace=null, $bDebug=false){
		if(is_array($strPrefix) && !empty($strPrefix)){ // If there ewre passed only two arguments
			$arReplace = $strPrefix;
		}
		if(is_string($strPrefix) && strlen($strPrefix)){
			$strMessage = $strPrefix.'_'.$strMessage;
		}
		if($bDebug){
			static::P($strMessage);
		}
		return \Bitrix\Main\Localization\Loc::getMessage($strMessage, $arReplace);
	}
	
	/**
	 * Convert charset (CP1251->UTF-8 || UTF-8->CP1251)
	 */
	public static function convertEncoding($mText, $strFrom='UTF-8', $strTo='CP1251') {
		$error = '';
		if(is_array($mText)) {
			foreach($mText as $key => $value){
				$mText[$key] = static::convertEncoding($value, $strFrom, $strTo);
			}
		}
		else {
			$mText = \Bitrix\Main\Text\Encoding::convertEncoding($mText, $strFrom, $strTo, $error);
		}
		return $mText;
	}
	
	/**
	 * Convert charset from site charset to specified charset
	 */
	public static function convertEncodingTo($mText, $strTo) {
		if(strlen($strTo)){
			$strFrom = static::isUtf() ? 'UTF-8' : 'CP1251';
			$strTo = ToLower($strTo) == 'windows-1251' ? 'CP1251' : $strTo;
			if($strTo != $strFrom){
				$mText = static::convertEncoding($mText, $strFrom, $strTo);
			}
		}
		return $mText;
	}
	
	/**
	 * Convert charset from specified charset to site charset
	 */
	public static function convertEncodingFrom($mText, $strFrom) {
		if(strlen($strFrom)){
			$strFrom = ToLower($strFrom) == 'windows-1251' ? 'CP1251' : $strFrom;
			$strTo = static::isUtf() ? 'UTF-8' : 'CP1251';
			if($strFrom != $strTo){
				$mText = static::convertEncoding($mText, $strFrom, $strTo);
			}
		}
		return $mText;
	}
	
	/**
	 *	Show note
	 */
	public static function showNote($strNote, $bCompact=false, $bCenter=false, $bReturn=false) {
		if($bReturn){
			ob_start();
		}
		$arClass = [];
		if($bCompact){
			$arClass[] = 'wdu_note_compact';
		}
		if($bCenter){
			$arClass[] = 'wdu_note_center';
		}
		print '<div class="'.implode(' ', $arClass).'">';
		print BeginNote();
		print $strNote;
		print EndNote();
		print '</div>';
		if($bReturn){
			return ob_get_clean();
		}
	}
	
	/**
	 *	Show success
	 */
	public static function showSuccess($strMessage=null, $strDetails=null) {
		ob_start();
		\CAdminMessage::showMessage([
			'MESSAGE' => $strMessage,
			'DETAILS' => $strDetails,
			'HTML' => true,
			'TYPE' => 'OK',
		]);
		return ob_get_clean();
	}
	
	/**
	 *	Show error
	 */
	public static function showError($strMessage=null, $strDetails=null) {
		ob_start();
		\CAdminMessage::showMessage([
			'MESSAGE' => $strMessage,
			'DETAILS' => $strDetails,
			'HTML' => true,
		]);
		return ob_get_clean();
	}
	
	/**
	 *	Show error
	 */
	public static function showHeading($strMessage, $bNoMargin=false){
		$strResult = '';
		$strClass = $bNoMargin ? ' class="wdu_table_nomargin"' : '';
		$strResult .= '<table style="width:100%"'.$strClass.'><tbody><tr class="heading"><td>'
			.$strMessage.'</td></tr></tbody></table>';
		return $strResult;
	}
	
	/**
	 *	Show hint
	 */
	public static function showHint($strText) {
		$strCode = toLower(static::randString());
		$strText = str_replace('"', '\"', $strText);
		$strText = str_replace("\r", '', $strText);
		$strText = str_replace("\n", ' ', $strText);
		$strResult = '<span id="hint_'.$strCode.'"><span></span></span>'
			.'<script>BX.hint_replace(BX("hint_'.$strCode.'").childNodes[0], "'.$strText.'");</script>';
		return $strResult;
	}
	
	/**
	 *	Word form for russian (1 tevelizor, 2 tevelizora, 5 tevelizorov)
	 */
	public static function wordForm($intValue, $arWords) {
		$strLastSymbol = substr($intValue, -1);
		$strSubLastSymbol = substr($intValue, -2, 1);
		if (strlen($intValue) >= 2 && $strSubLastSymbol == '1') {
			return $arWords['5'];
		}
		else {
			if ($strLastSymbol == '1')
				return $arWords['1'];
			elseif ($strLastSymbol >= 2 && $strLastSymbol <= 4)
				return $arWords['2'];
			else
				return $arWords['5'];
		}
	}
	
	/**
	 *	Insert new key into array (in a selected place)
	 */
	public static function arrayInsert(array &$arData, $strKey, $mItem, $strAfter=null, $strBefore=null){
		$bSuccess = false;
		if($strAfter === static::ARRAY_INSERT_BEGIN) {
			$bSuccess = true;
			$arData = array_merge(array($strKey => $mItem), $arData);
		}
		elseif(!is_null($strAfter)) {
			$intIndex = 0;
			foreach($arData as $key => $value){
				$intIndex++;
				if($key === $strAfter){
					$bSuccess = true;
					$arBefore = array_slice($arData, 0, $intIndex, true);
					$arAfter = array_slice($arData, $intIndex, null, true);
					$arData = array_merge($arBefore, [$strKey => $mItem], $arAfter);
					unset($arBefore, $arAfter);
					break;
				}
			}
		}
		elseif(!is_null($strBefore)) {
			$intIndex = 0;
			foreach($arData as $key => $value){
				if($key === $strBefore){
					$bSuccess = true;
					$arBefore = array_slice($arData, 0, $intIndex, true);
					$arAfter = array_slice($arData, $intIndex, null, true);
					$arData = array_merge($arBefore, [$strKey => $mItem], $arAfter);
					unset($arBefore, $arAfter);
					break;
				}
				$intIndex++;
			}
		}
		if(!$bSuccess){
			$arData[$strKey] = $mItem;
		}
	}
	
	/**
	 *	Remove empty values from array (check by strlen(trim()))
	 */
	public static function arrayRemoveEmptyValues(&$arValues, $bTrim=true) {
    foreach($arValues as $key => $value){
			if($bTrim && !strlen(trim($value)) || !$bTrim && !strlen($value)){
				unset($arValues[$key]);
			}
		}
	}
	
	/**
	 *	Remove empty values from array (check by strlen(trim()))
	 */
	public static function arrayRemoveEmptyValuesRecursive(&$arValues) {
    foreach($arValues as $key => $value){
			if(is_array($value)){
				static::arrayRemoveEmptyValuesRecursive($arValues[$key]);
			}
			else{
				if(!strlen(trim($value))){
					unset($arValues[$key]);
				}
			}
		}
	}
	
	/**
	 *	Exec custom action for each element of array (or single if it is not array)
	 */
	public static function execAction($arData, $callbackFunction, $arParams=false){
		if(is_array($arData)) {
			foreach($arData as $Key => $arItem){
				$arData[$Key] = $callbackFunction($arItem, $arParams);
			}
		}
		else {
			$arData = $callbackFunction($arData, $arParams);
		}
		return $arData;
	}
	
	/**
	 *	Is managed cache on ?
	 */
	public static function isManagedCacheOn(){
		return static::getOption('main', 'component_managed_cache_on', 'N') != 'N' || defined('BX_COMP_MANAGED_CACHE');
	}
	
	/**
	 *	Format size (kilobytes, megabytes, ...)
	 */
	public static function formatSize($intSize){
		$strResult = \CFile::FormatSize($intSize);
		// replace '2 Mb' to '2.00 Mb'
		$strResult = preg_replace('#^([\d]+)[\s]#', '$1.00 ', $strResult);
		// replace '2.1 Mb' to '2.10 Mb'
		$strResult = preg_replace('#^([\d]+)\.([\d]{1})[\s]#', '${1}.${2}0 ', $strResult);
		return $strResult;
	}
	
	/**
	 *	Round numeric value
	 */
	public static function roundEx($fValue, $intPrecision=0, $strFunc=null) {
		$intPow = pow(10, $intPrecision);
		$strFunc = in_array($strFunc, ['round', 'floor', 'ceil']) ? $strFunc : 'round';
		return call_user_func($strFunc, $fValue * $intPow) / $intPow;
	}
	
	/**
	 *	Get option value
	 */
	public static function getOption($strModuleId, $strOption, $mDefaultValue=null){
		return \Bitrix\Main\Config\Option::get($strModuleId, $strOption, $mDefaultValue);
	}
	
	/**
	 *	Set option value
	 */
	public static function setOption($strModuleId, $strOption, $mValue){
		return \Bitrix\Main\Config\Option::set($strModuleId, $strOption, $mValue);
	}
	
	/**
	 *	Remove single option
	 */
	public static function removeOption($strModuleId, $strOption){
		return \Bitrix\Main\Config\Option::delete($strModuleId, ['name' => $strOption]);
	}
	
	/**
	 *	Remove all options
	 */
	public static function removeAllOptions($strModuleId){
		return \Bitrix\Main\Config\Option::delete($strModuleId);
	}
	
	/**
	 *	Get user option value
	 */
	public static function getUserOption($strModuleId, $strOption, $mDefaultValue=null){
		return \CUserOptions::getOption($strModuleId, $strOption, $mDefaultValue);
	}
	
	/**
	 *	Set user option value
	 */
	public static function setUserOption($strModuleId, $strOption, $mValue){
		return \CUserOptions::setOption($strModuleId, $strOption, $mValue);
	}
	
	/**
	 *	Format elapsed time from 121 to 2:01
	 */
	public static function formatElapsedTime($intSeconds){
		$strResult = '';
		if(is_numeric($intSeconds)){
			$intHours = floor($intSeconds / (60*60));
			$intSeconds -= $intHours * 60 * 60;
			$intMinutes = floor($intSeconds / 60);
			$intMinutes = sprintf('%02d', $intMinutes);
			if($intMinutes > 0) {
				$intSeconds = $intSeconds - $intMinutes * 60;
			}
			$intSeconds = sprintf('%02d', $intSeconds);
			$strResult = ($intHours ? $intHours.':' : '').$intMinutes.':'.$intSeconds;
		}
		return $strResult;
	}
	
	/**
	 *	Whereis class defined?
	 */
	public static function getClassFilename($strClass){
		$obReflectionClass = new \ReflectionClass($strClass);
		$strFileClass = $obReflectionClass->getFileName();
		unset($obReflectionClass);
		return $strFileClass;
	}
	
	/**
	 *	Add notify
	 */
	public static function addNotify($strModuleId, $strMesage, $strTag, $bClose=true){
		$arParams = [
			'MODULE_ID' => $strModuleId,
			'MESSAGE' => $strMesage,
			'TAG' => $strTag,
			'ENABLE_CLOSE' => $bClose ? 'Y' : 'N',
		];
		static::deleteNotify($strTag);
		return \CAdminNotify::add($arParams);
	}
	
	/**
	 *	Delete notify
	 */
	public static function deleteNotify($strTag){
		return \CAdminNotify::deleteByTag($strTag);
	}
	
	/**
	 *	Get notify list
	 */
	public static function getNotifyList($strModuleId){
		$arResult = [];
		$arSort = [
			'ID' => 'ASC',
		];
		$arFilter = [
			'MODULE_ID' => $strModuleId,
		];
		$resItems = \CAdminNotify::getList($arSort, $arFilter);
		while($arItem = $resItems->getNext()){
			$arResult[] = $arItem;
		}
		return $arResult;
	}
	
	/**
	 *	Replace \ to /
	 */
	public static function path($strPath){
		return str_replace('\\', '/', $strPath);
	}
	
	/**
	 *	Remove slashes at the end of text
	 */
	public static function removeTrailingBackslash($strText){
		return preg_replace('#[/]*$#', '', $strText);
	}
	
	/**
	 *	Scan directory [can be recursively]
	 *	Params:
	 *		CALLBACK($strFileName, $arParams), default null
	 *		RECURSIVELY [true|false], default true
	 *		FILES [true|false], default true
	 *		DIRS [true|false], default false
	 */
	public static function scandir($strDir, $arParams=[]) {
		$arResult = [];
		$strDir = static::path($strDir);
		$strDir = static::removeTrailingBackslash($strDir);
		if(!is_array($arParams)){
			$arParams = [];
		}
		if($arParams['RECURSIVELY'] !== false){
			$arParams['RECURSIVELY'] = true;
		}
		if($arParams['FILES'] !== false){
			$arParams['FILES'] = true;
		}
		if(strlen($strDir) && is_dir($strDir)){
			$resHandle = opendir($strDir);
			while(($strItem = readdir($resHandle)) !== false)  {
				if(!in_array($strItem, ['.', '..'])) {
					if(is_file($strDir.'/'.$strItem)) {
						if($arParams['FILES']){
							if(isset($arParams['EXT'])){
								$strExt = toUpper(pathinfo($strItem, PATHINFO_EXTENSION));
								$bAppropriate = (is_string($arParams['EXT']) && toUpper($arParams['EXT']) == $strExt) 
									|| is_array($arParams['EXT']) && in_array($strExt, array_map(function($strItem){
										return toUpper($strItem);
									}, $arParams['EXT']));
								if(!$bAppropriate){
									continue;
								}
							}
							$mCallbackResult = null;
							if(is_callable($arParams['CALLBACK'])){
								$mCallbackResult = call_user_func_array($arParams['CALLBACK'], [$strDir.'/'.$strItem, $arParams]);
							}
							if($mCallbackResult === false){
								continue;
							}
							$arResult[] = $strDir.'/'.$strItem;
						}
					} elseif(is_dir($strDir.'/'.$strItem)) {
						if($arParams['DIRS']){
							$arResult[] = $strDir.'/'.$strItem;
						}
						if($arParams['RECURSIVELY']){
							$arResult = array_merge($arResult, static::scandir($strDir.'/'.$strItem, $arParams));
						}
					}
				}
			}
			closedir($resHandle);
		}
		sort($arResult);
		return $arResult;
	}
	
	/**
	 *	Get all sites
	 */
	public static function getSitesList($bActiveOnly=false, $bSimple=false, $strField=null, $strOrder=null, $bIcons=false) {
		$arResult = [];
		$arFilter = [];
		if($bActiveOnly) {
			$arFilter['ACTIVE'] = 'Y';
		}
		$strField = strlen($strField) ? $strField : 'SORT';
		$strOrder = strlen($strOrder) ? $strOrder : 'ASC';
		$resSites = \CSite::getList($strField, $strOrder, $arFilter);
		while($arSite = $resSites->getNext(false, false)) {
			$arSite['TEXT'] = static::formatSite($arSite);
			if(!$bSimple && $bIcons && strlen($arSite['SERVER_NAME'])){
				$arSite['ICON'] = static::getSiteIcon($arSite);
			}
			$arResult[$arSite['ID']] = $bSimple ? sprintf('[%s] %s', $arSite['ID'], $arSite['NAME']) : $arSite;
		}
		return $arResult;
	}
	
	/**
	 *	
	 */
	public static function getSiteIcon($arSite){
		$strDomain = $arSite['SERVER_NAME'];
		$strFolder = strlen($arSite['DOC_ROOT']) ? $arSite['DOC_ROOT'] : 
			(strlen($arSite['ABS_DOC_ROOT']) ? $arSite['ABS_DOC_ROOT'] : '');
		$strContent = null;
		$strMime = 'image/x-icon';
		$strFile = 'favicon.ico';
		if(strlen($strFolder) && is_dir($strFolder) && is_file($strFolder.'/'.$strFile)){
			$strContent = file_get_contents($strFolder.'/'.$strFile);
		}
		if(!strlen($strContent)){
			$strUrl = sprintf('https://%s/%s', $arSite['SERVER_NAME'], $strFile);
			$obHttpClient = new \Bitrix\Main\Web\HttpClient;
			$obHttpClient->setTimeout(1);
			$strContent = $obHttpClient->get($strUrl);
			if($strContent === false){
				$strUrl = sprintf('http://%s/%s', $arSite['SERVER_NAME'], $strFile);
				$strContent = $obHttpClient->get($strUrl);
			}
		}
		if(strlen($strContent)){
			$strContent = base64_encode($strContent);
			/*
			if(in_array($obHttpClient->getHeaders()->getContentType(), ['image/png'])){
				$strMime = 'image/png';
			}
			elseif(in_array($obHttpClient->getHeaders()->getContentType(), ['image/gif'])){
				$strMime = 'image/gif';
			}
			*/
		}
		else{
			//
		}
		return strlen($strContent) ? sprintf('data:%s;base64,%s', $strMime, $strContent) : '';
	}
	
	/**
	 *	Format currency
	 */
	public static function formatSite($arSite){
		return sprintf('[%s] %s (%s)', $arSite['ID'], $arSite['SITE_NAME'], $arSite['SERVER_NAME']);
	}
	
	/**
	 *	Get list of iblocks
	 */
	public static function getIBlocks($bGroupByType=true, $bShowInactive=false, $strSiteId=null) {
		$arResult = [];
		if(\Bitrix\Main\Loader::includeModule('iblock')) {
			$arSort = [
				'SORT' => 'ASC',
			];
			$arFilter = [
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'W',
			];
			if($bGroupByType) {
				$resIBlockTypes = \CIBlockType::GetList([], $arFilter);
				while($arIBlockType = $resIBlockTypes->GetNext(false, false)) {
					$arIBlockTypeLang = \CIBlockType::GetByIDLang($arIBlockType['ID'], LANGUAGE_ID, false);
					$arResult[$arIBlockType['ID']] = [
						'NAME' => $arIBlockTypeLang['NAME'],
						'ITEMS' => [],
					];
				}
			}
			if(!$bShowInactive){
				$arFilter['ACTIVE'] = 'Y';
			}
			if(strlen($strSiteId)){
				$arFilter['SITE_ID'] = $strSiteId;
			}
			$resIBlock = \CIBlock::GetList($arSort, $arFilter);
			while($arIBlock = $resIBlock->GetNext(false, false)) {
				if($bGroupByType) {
					$arResult[$arIBlock['IBLOCK_TYPE_ID']]['ITEMS'][] = $arIBlock;
				}
				else {
					$arResult[] = $arIBlock;
				}
			}
		}
		return $arResult;
	}
	
	/**
	 *	Is catalog based on new filter? 
	 *	https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=12183#iblock_18_6_200
	 */
	public static function isCatalogNewFilter(){
		return checkVersion(SM_VERSION, '18.6.200');
	}
	
	/**
	 *	Is stores available?
	 */
	public static function isCatalogBarcodeAvailable(){
		return \Bitrix\Main\Loader::includeModule('catalog') && class_exists('\CCatalogStoreBarCode');
	}
	
	/**
	 *	Is stores available?
	 */
	public static function isCatalogStoresAvailable(){
		return \Bitrix\Main\Loader::includeModule('catalog') && class_exists('\CCatalogStoreProduct');
	}
	
	/**
	 *	Get module absolute dir
	 */
	public static function getModuleDir($strModuleId, $strRelativeDir=null){
		$strResult = realpath(__DIR__.'/../..').'/'.$strModuleId;
		if(is_string($strRelativeDir) && strlen($strRelativeDir)){
			$strResult .= '/'.$strRelativeDir;
			$strResult = str_replace('//', '/', $strResult);
		}
		return $strResult;
	}
	
	/**
	 *	Get current module version
	 */
	public static function getModuleVersion($strModuleId){
		include static::root().'/bitrix/modules/'.$strModuleId.'/install/version.php';
		return $arModuleVersion['VERSION'];
	}
	
	/**
	 *	Show file file /include/
	 */
	public static function includeFile($strModuleId, $strFile, $strTemplate=null, $arParams=[]){
		$strTemplate = is_string($strTemplate) && strlen($strTemplate) ? $strTemplate : 'default';
		$strDir = static::getModuleDir($strModuleId, '/include/template/'.$strFile);
		$strFile = $strDir.'/'.$strTemplate.'.php';
		ob_start();
		if(is_file($strFile)){
			$arParams = is_array($arParams) ? $arParams : [];
			$GLOBALS['arParams'] = $arParams; // If in demo-mode, 2nd argument is not '$arParams' :( - this looks like $_1565435424
			$strModuleCode = str_replace('.', '_', $strModuleId);
			static::loadMessages($strFile);
			include($strFile);
		}
		return ob_get_clean();
	}
	
	/**
	 *	Get event handlers all
	 */
	public static function getEventHandlers($strModuleId, $strEvent){
		return \Bitrix\Main\EventManager::getInstance()->findEventHandlers($strModuleId, $strEvent);
	}
	
	/**
	 *	Clear file name from special chars (allowed just A-z, 0-9, _, -)
	 */
	public static function clearFilename($strFilename){
		return preg_replace('#[^A-z0-9_-]#', '', $strFilename);
	}
	
	/**
	 *	Get rand string (32 chars)
	 */
	public static function randString($bWithPrefix=false){
		return ($bWithPrefix === true ? static::RAND_ID_PREFIX : '').'a'.substr(md5(randString(16).microtime(true)), 1);
	}
	
	/**
	 *	Show popup by id
	 */
	public static function getPopupContent($strModuleId, $strPopupId, $arParams=[]){
		ob_start();
		$strDir = realpath(__DIR__.'/../../'.$strModuleId.'/include/popup');
		$strPopupId = static::clearFilename($strPopupId);
		if(strlen($strPopupId)){
			$strPopupFile = $strDir.'/'.$strPopupId.'.php';
			if(is_file($strPopupFile)){
				static::loadMessages($strPopupFile);
				$GLOBALS['arParams'] = $arParams; // If in demo-mode, 2nd argument is not '$arParams' :( - this looks like $_1565435424
				require($strPopupFile);
			}
		}
		return ob_get_clean();
	}
	
	/**
	 *	Get $_GET and $_POST
	 */
	public static function getRequestQuery(){
		$arQuery = &static::$arCache[static::CACHE_QUERY];
		if(!is_array($arQuery)){
			$arGet = \Bitrix\Main\Context::getCurrent()->getRequest()->getQueryList()->toArray();
			$arPost = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();
			$arQuery = [$arGet, $arPost];
		}
		return $arQuery;
	}
	
	/**
	 *	Get first non-empty value
	 */
	public static function getFirstValue($arValues, $bInteger=false){
		foreach($arValues as $mValue){
			if($bInteger){
				$mValue = intVal($mValue);
				if($mValue > 0){
					return $mValue;
				}
			}
			elseif(strlen($mValue)){
				return $mValue;
			}
		}
		return false;
	}
	
	/**
	 *	Create directories path for file
	 */
	public static function createDirectoriesForFile($strFileName, $bAutoChangeOwner=false){
		$strDirname = static::getFileDir($strFileName);
		if(!is_dir($strDirname)){
			@mkdir($strDirname, BX_DIR_PERMISSIONS, true);
		}
		if($bAutoChangeOwner){
			$strPath = substr(pathinfo($strFileName, PATHINFO_DIRNAME), strlen(static::root()));
			$strPath = trim(static::path($strPath), '/');
			$arPath = explode('/', $strPath);
			for($i=1; $i <= count($arPath); $i++){
				$strPath = implode('/', array_slice($arPath, 0, $i));
				if(strlen($strPath)){
					$strPath = '/'.$strPath;
					if(is_dir(static::root().$strPath)){
						static::changeFileOwner(static::root().$strPath);
					}
				}
			}
		}
		return is_dir($strDirname);
	}
	
	/**
	 *	Get file directory
	 */
	public static function getFileDir($strFileName){
		return pathinfo($strFileName, PATHINFO_DIRNAME);
	}
	
	/**
	 *	Change log-file owner
	 */
	public static function changeFileOwner($strFilename){
		if(Cli::isCli() && Cli::isRoot() && function_exists('fileowner')){
			if(is_file($strFilename) || is_dir($strFilename)){
				$intBitrixUser = Cli::getBitrixUser();
				if(is_numeric($intBitrixUser)){
					$intOwner = @fileowner($strFilename);
					if($intOwner === 0){
						if(function_exists('chown')){
							if(chown($strFilename, $intBitrixUser)){
								if(function_exists('chgrp')){
									if(chgrp($strFilename, $intBitrixUser)){
										return true;
									}
								}
							}
						}
					}
					elseif($intOwner === $intBitrixUser){
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 *	
	 */
	public static function memSet(){
		static::$fMem = memory_get_usage();
	}
	
	/**
	 *	
	 */
	public static function memGet(){
		if(is_null(static::$fMem)){
			static::$fMem = 0;
		}
		return \CFile::formatSize(memory_get_usage() - static::$fMem);
	}
	
	/**
	 *	jQuery select2
	 */
	public static function addJsSelect2(){
		$strLangFile = Helper::isUtf() ? 'ru_utf8.js' : 'ru_cp1251.js';
		\Bitrix\Main\Page\Asset::getInstance()->AddJs('/bitrix/js/'.WDU_MODULE.'/jquery.select2/dist/js/select2.min.js');
		\Bitrix\Main\Page\Asset::getInstance()->AddJs('/bitrix/js/'.WDU_MODULE.'/jquery.select2/'.$strLangFile);
		\Bitrix\Main\Page\Asset::getInstance()->AddJs('/bitrix/js/'.WDU_MODULE.'/jquery.select2/select2.wdu.js');
		$GLOBALS['APPLICATION']->setAdditionalCss('/bitrix/js/'.WDU_MODULE.'/jquery.select2/dist/css/select2.css');
		$GLOBALS['APPLICATION']->setAdditionalCss('/bitrix/js/'.WDU_MODULE.'/jquery.select2/select2.wdu.css');
	}
	
	/**
	 *	Wrapper for SelectBoxFromArray()
	 */
	public static function selectBox($strName, $arValues, $strSelected=null, $strDefault=null, $strAttr=null, $strInputId=null, $bSelect2=true, $bSelect2Icons=false, $arSelect2Config=null){
		$strId = static::randString(true);
		$arValues = [
			'REFERENCE' => array_values($arValues),
			'REFERENCE_ID' => array_keys($arValues),
		];
		if(is_null($strSelected)){
			$strSelected = reset($arValues['REFERENCE_ID']);
		}
		if(strlen($strInputId)){
			$strAttr .= sprintf(' id="%s"', $strInputId);
		}
		$strHtml = static::selectBoxFromArray($strName, $arValues, $strSelected, $strDefault, $strAttr, $bSelect2Icons);
		$strHtml = sprintf('<div id="%s">%s</div>', $strId, $strHtml);
		if($bSelect2){
			static::addJsSelect2();
			$arSelect2ConfigResult = [];
			if($bSelect2Icons){
				$arSelect2ConfigResult['withIcons'] = true;
			}
			if(is_array($arSelect2Config)){
				$arSelect2ConfigResult['extConfig'] = $arSelect2Config;
			}
			$strHtml .= sprintf('<script>wduSelect2($(\'#%s > select\'), %s);</script>', $strId,
				\Bitrix\Main\Web\Json::encode($arSelect2ConfigResult));
		}
		return $strHtml;
	}
	
	/**
	 *	
	 */
	public static function selectBoxEx($strName, $arValues, $arParams=[]){
		$strSelected = isset($arParams['SELECTED']) ? $arParams['SELECTED'] : null; // $strSelected=null
		$strDefault = isset($arParams['DEFAULT']) ? $arParams['DEFAULT'] : null; // $strDefault = null
		$strAttr = isset($arParams['ATTR']) ? $arParams['ATTR'] : null; // $strAttr=null
		$strInputId = isset($arParams['INPUT_ID']) ? $arParams['INPUT_ID'] : null; // $strInputId=null
		$bSelect2 = $arParams['SELECT2'] === false ? false : true; // $bSelect2=true
		$bIcons = $arParams['WITH_ICONS'] === true ? true : false; // $bSelect2icons=false
		$arSelect2Config = $arParams['SELECT2_CONFIG'] ? $arParams['SELECT2_CONFIG'] : null; // $arSelect2Config=null
		return static::selectBox($strName, $arValues, $strSelected, $strDefault, $strAttr, $strInputId, $bSelect2, $bIcons,
			$arSelect2Config);
	}
	
	/**
	 *	
	 */
	public static function selectBoxFromArray($strBoxName, $db_array, $mSelectedVal='', $strDetText='', $field1='', $bSelect2Icons=null){
		$boxName = htmlspecialcharsbx($strBoxName);
		$strReturnBox = '<select '.$field1.' name="'.$boxName.'">';
		if(isset($db_array["reference"]) && is_array($db_array["reference"]))
			$ref = $db_array["reference"];
		elseif(isset($db_array["REFERENCE"]) && is_array($db_array["REFERENCE"]))
			$ref = $db_array["REFERENCE"];
		else
			$ref = [];
		if(isset($db_array["reference_id"]) && is_array($db_array["reference_id"]))
			$ref_id = $db_array["reference_id"];
		elseif(isset($db_array["REFERENCE_ID"]) && is_array($db_array["REFERENCE_ID"]))
			$ref_id = $db_array["REFERENCE_ID"];
		else
			$ref_id = [];
		if($strDetText <> '')
			$strReturnBox .= '<option value="">'.$strDetText.'</option>';
		foreach($ref as $i => $val){
			$val = is_array($val) ? $val : ['TEXT' => $val];
			$icon = $bSelect2Icons && strlen($val['ICON']) ? $val['ICON'] : '';
			$strReturnBox .= '<option';
			if(is_array($mSelectedVal)){
				if(in_array($ref_id[$i], $mSelectedVal)){
					$strReturnBox .= ' selected';
				}
			}
			elseif(strcasecmp($ref_id[$i], $mSelectedVal) == 0) {
				$strReturnBox .= ' selected';
			}
			if(strlen($icon)){
				$strReturnBox .= ' data-icon="'.$icon.'"';
			}
			$strReturnBox .= ' value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($val['TEXT']).'</option>';
		}
		return $strReturnBox.'</select>';
	}
	
	/**
	 *	Get current domain (without port)
	 */
	public static function getCurrentDomain(){
		return preg_replace('#:(\d+)$#', '', toLower(\Bitrix\Main\Context::getCurrent()->getServer()->getHttpHost()));
	}
	
	/**
	 *	
	 */
	public static function getUserTitle($intUserId, $intMode=1){
		$intUserId = intVal($intUserId);
		$intMode = $intMode >= 1 && $intMode <= 4 ? $intMode : 1;
		if($intUserId > 0){
			$resUser = \CUser::getByID($intUserId);
			if($arUser = $resUser->fetch()){
				$strTitle = \CUser::FormatName(\CSite::getNameFormat(), $arUser, true, false);
				$strLink = '<a title="'.static::getMessage('MAIN_EDIT_USER_PROFILE').'" href="/bitrix/admin/user_edit.php?ID='.$arUser['ID'].'&lang='.LANGUAGE_ID.'" target="_blank">'.$arUser['ID'].'</a>';
				switch($intMode){
					case 1:
						$strTitle = sprintf('%s',$strTitle);
						break;
					case 2:
						$strTitle = sprintf('(%s) %s', $arUser['LOGIN'], $strTitle);
						break;
					case 3:
						$strTitle = sprintf('[%s] (%s) %s', $arUser['ID'], $arUser['LOGIN'], $strTitle);
						break;
					case 4:
						$strTitle = sprintf('[%s] (%s) %s', $strLink, $arUser['LOGIN'], $strTitle);
						break;
				}
				return $strTitle;
			}
		}
		return false;
	}
	
	/**
	 *	Translate ru -> en [by Yandex]
	 *	Need the key in main module's settings
	 */
	public static function translate($strText){
		$strResult = $strText;
		$strKey = static::getOption('translate_key_yandex', null, 'main');
		$strLang = 'ru-en';
		$strClientId = 'bitrix';
		if(!static::isUtf()){
			$strText = static::convertEncoding($strText, 'CP1251', 'UTF-8');
		}
		$strText = urlencode($strText);
		$strUrl = sprintf('https://translate.yandex.net/api/v1.5/tr.json/translate?key=%s&lang=%s&clientId=%s&text=%s',
			$strKey, $strLang, $strClientId, $strText);
		$strResponse = HttpRequest::getHttpContent($strUrl);
		if(strlen($strResponse)){
			print $strResponse;
			$arResponse = \Bitrix\Main\Web\Json::decode($strResponse);
			if(is_array($arResponse)){
				$strResult = reset($arResponse['text']);
			}
		}
		return $strResult;
	}
	
	/**
	 *	Convert myTestValue to my_test_value
	 */
	public static function toUnderlineCase($strText, $bUpper=true){
		$strText = preg_replace('#([a-z]{1})([A-Z]{1})#', '$1_$2', $strText);
		if($bUpper){
			$strText = toUpper($strText);
		}
		else{
			$strText = toLower($strText);
		}
		return $strText;
	}
	
	/**
	 *	Convert my_test_value to myTestValue
	 */
	public static function toCamelCase($strText){
		return preg_replace_callback('#_([A-z])#', function($arMatch){
			return toUpper($arMatch[1]);
		}, $strText);
	}
	
	/**
	 *	Split 1, 2, 3 => [1, 2, 3]
	 */
	public static function splitCommaValues($strValues){
		return preg_split('#,[\s]*#', $strValues);
	}
	
	/**
	 *	Split 1 2 3 => [1, 2, 3]
	 */
	public static function splitSpaceValues($strValues){
		return preg_split('#\s+#s', trim($strValues));
	}
	
	/**
	 *	Get upload dir from config
	 */
	public static function getUploadDir($strSubdir=null, $bAbsolute=false){
		$strResult = '/'.static::getOption(WDU_MODULE, 'upload');
		if(is_string($strSubdir) && $strSubdir){
			$strResult .= '/'.WDU_MODULE.'/'.$strSubdir;
		}
		$strAbsolute = static::root().$strResult;
		if($bAbsolute){
			$strResult = $strAbsolute;
		}
		if(!is_dir($strAbsolute)){
			mkdir($strAbsolute, BX_DIR_PERMISSIONS, true);
		}
		return $strResult;
	}
	
	/* --------- Universal cached GetList:  ---------------------*/
	/* $arTestResult = CacheExec('CIBlockElement::GetList',array(array('SORT'=>'ASC'),array('IBLOCK_ID'=>4),false,false,array('ID','NAME')),60,false,false,false,array('iblock')); */
	public static function cacheExec($FuncName, $Arguments, $CacheTime=false, $CacheID=false, $CacheDir=false, $CacheTags=false, $Modules=false){
		$arResult = [];
		$PHPCache = new CPHPCache;
		$CacheTime = $CacheTime>0 ? $CacheTime : 60*60;
		$CacheID = strlen($CacheID) ? $CacheID : serialize(array($FuncName,$Arguments,$Modules,$CacheTags));
		$CacheDir = strlen($CacheDir) ? $CacheDir : '/'.str_replace('::','_',$FuncName);
		if($PHPCache->InitCache($CacheTime, $CacheID, $CacheDir)) {
			$arResult = $PHPCache->GetVars();
		} elseif($PHPCache->StartDataCache()) {
			if(is_array($Modules)) {
				foreach($Modules as $Module) {
					CModule::IncludeModule($Module);
				}
			}
			$resItems = call_user_func_array($FuncName,$Arguments);
			while($arItem = $resItems->GetNext()){
				if(is_numeric($arItem['ID']) && $arItem['ID']>0) {
					$arResult[$arItem['ID']] = $arItem;
				} else {
					$arResult[] = $arItem;
				}
			}
			if($CacheTags!==false) {
				$GLOBALS['CACHE_MANAGER']->StartTagCache($CacheDir);
				foreach($CacheTags as $CacheTag){
					$GLOBALS['CACHE_MANAGER']->RegisterTag($CacheTag);
				}
				$GLOBALS['CACHE_MANAGER']->EndTagCache();
			}
			$PHPCache->EndDataCache($arResult);
		}
		unset($PHPCache,$Module,$resItems,$arItem);
		return $arResult;
	}
	
	/**
	 *	Форматирование интервала времени для акций (пример: «с 27 июня по 28 июня 2020 года»)
	 */
	function formatDateInterval($strActiveFrom, $strActiveTo=false){
		static $arMonth;
		if(is_null($arMonth)){
			$strMonth = 'января, февраля, марта, апреля, мая, июня, июля, августа, сентября, октября, ноября, декабря';
			$arMonth = explode(', ', $strMonth);
			array_unshift($arMonth, true);
			unset($arMonth[0]);
		}
		#
		$obDateFrom = new \Bitrix\Main\Type\DateTime($strActiveFrom);
		$intDateFromD = $obDateFrom->format('j');
		$intDateFromM = $obDateFrom->format('n');
		$intDateFromY = $obDateFrom->format('Y');
		$strDateFromMonth = $arMonth[$intDateFromM];
		unset($obDateFrom);
		#
		$bDateTo = !!strlen($strActiveTo);
		if($bDateTo){
			$obDateTo = new \Bitrix\Main\Type\DateTime($strActiveTo);
			$intDateToD = $obDateTo->format('j');
			$intDateToM = $obDateTo->format('n');
			$intDateToY = $obDateTo->format('Y');
			$strDateToMonth = $arMonth[$intDateToM];
			unset($obDateTo);
		}
		#
		$strResult = 'с '.$intDateFromD;
		if(!$bDateTo || $intDateFromM != $intDateToM){
			$strResult .= ' '.$strDateFromMonth;
		}
		if(!$bDateTo || $intDateFromY != $intDateToY){
			$strResult .= ' '.$intDateFromY.' года';
		}
		if($bDateTo){
			$strResult .= ' по';
			$strResult .= ' '.$intDateToD;
			$strResult .= ' '.$strDateToMonth;
			$strResult .= ' '.$intDateToY.' года';
		}
		return $strResult;
	}
	
}
