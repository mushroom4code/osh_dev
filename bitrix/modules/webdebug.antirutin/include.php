<?
namespace WD\Antirutin;

use
	\Bitrix\Main\Config\Option,
	\WD\Antirutin\Cli,
	\WD\Antirutin\Filter,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\Json,
	\WD\Antirutin\HttpRequest,
	\WD\Antirutin\ProfileTable as Profile;

global $DB, $DBType;
define('WDA_MODULE', 'webdebug.antirutin');

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::registerAutoLoadClasses(WDA_MODULE, [
		# Old
		'CWDA' => 'classes/general/CWDA.php',
		'CWDA_Plugin' => 'classes/general/CWDA_Plugin.php',
		'CWDA_Profile' => 'classes/'.$DBType.'/CWDA_Profile.php',
		# New
		'WD\Antirutin\Cli' => 'lib/cli.php',
		'WD\Antirutin\Filter' => 'lib/filter.php',
		'WD\Antirutin\IBlock' => 'lib/iblock.php',
		'WD\Antirutin\HttpRequest' => 'lib/httprequest.php',
		'WD\Antirutin\Json' => 'lib/json.php',
		'WD\Antirutin\Log' => 'lib/log.php',
		'WD\Antirutin\Options' => 'lib/options.php',
		'WD\Antirutin\Plugin' => 'lib/plugin.php',
		'WD\Antirutin\PluginElement' => 'lib/pluginelement.php',
		'WD\Antirutin\PluginSection' => 'lib/pluginsection.php',
		'WD\Antirutin\ProfileTable' => 'lib/profile.php',
		'WD\Antirutin\ProfileActionTable' => 'lib/profileaction.php',
		'WD\Antirutin\Support' => 'lib/support.php',
		'WD\Antirutin\Thread' => 'lib/thread.php',
		'WD\Antirutin\Uploader' => 'lib/uploader.php',
		'WD\Antirutin\ValueItem' => 'lib/valueitem.php',
		'WD\Antirutin\Worker' => 'lib/worker.php',
]);

final class Helper {
	
	const ARRAY_INSERT_BEGIN = '_ARRAY_INSERT_BEGIN_';
	const ARRAY_INSERT_AFTER = '_ARRAY_INSERT_AFTER_';
	const ARRAY_INSERT_BEFORE = '_ARRAY_INSERT_BEFORE_';
	
	const RAND_ID_PREFIX = 'rand_id_';
	
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
	
	# Plugin types
	const TYPE_ELEMENT = 'ELEMENT';
	const TYPE_SECTION = 'SECTION';
	
	# Plugin types
	const TYPE_NATIVE = 'NATIVE';
	const TYPE_CUSTOM = 'CUSTOM';
	
	#
	static $fMem;
	
	/**
	 *	Debug
	 */
	public static function p($arData, $bJust=false) {
		if($bJust && is_object($GLOBALS['APPLICATION'])){
			static::obRestart();
		}
		$strID = 'pre_'.static::randString();
		$strResult = '<style type="text/css">pre#'.$strID.'{background:none repeat scroll 0 0 #FAFAFA; border-color:#AAB4BE #AAB4BE #AAB4BE #B4B4B4; border-style:dotted dotted dotted solid; border-width:1px 1px 1px 20px; font:normal 11px "Courier New","Courier",monospace; margin:10px 0; padding:5px 0 5px 10px; position:relative; text-align:left; white-space:pre-wrap;}</style>';
		if(is_array($arData) && empty($arData))
			$arData = '--- Array is empty ---';
		if($arData === false)
			$arData = '[false]';
		elseif ($arData === true)
			$arData = '[true]';
		elseif ($arData === null)
			$arData = '[null]';
		#$arData = debug_backtrace(2);
		$strResult .= '<pre id="'.$strID.'">'.print_r($arData, true).'</pre>';
		print $strResult;
		if($bJust){
			die();
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
	 */
	public static function loadMessages($strFile){
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
			$arClass[] = 'wda-note-compact';
		}
		if($bCenter){
			$arClass[] = 'wda-note-center';
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
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => $strMessage,
			'DETAILS' => $strDetails,
			'HTML' => true,
			'TYPE' => 'OK',
		));
		return ob_get_clean();
	}
	
	/**
	 *	Show error
	 */
	public static function showError($strMessage=null, $strDetails=null) {
		ob_start();
		\CAdminMessage::ShowMessage(array(
			'MESSAGE' => $strMessage,
			'DETAILS' => $strDetails,
			'HTML' => true,
		));
		return ob_get_clean();
	}
	
	/**
	 *	Show error
	 */
	public static function showHeading($strMessage, $bNoMargin=false){
		$strResult = '';
		$strClass = $bNoMargin ? ' class="wda-table-nomargin"' : '';
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
	 *	Get all sites
	 */
	public static function getSitesList($bActiveOnly=false, $bSimple=false) {
		$arResult = [];
		$arFilter = [];
		if($bActiveOnly) {
			$arFilter['ACTIVE'] = 'Y';
		}
		$resSites = \CSite::GetList($strBy='SORT', $strOrder='ASC', $arFilter);
		while($arSite = $resSites->GetNext(false, false)) {
			$arResult[$arSite['ID']] = $bSimple ? sprintf('[%s] %s', $arSite['ID'], $arSite['NAME']) : $arSite;
		}
		return $arResult;
	}
	
	/**
	 *	Check if site works via HTTPS
	 */
	public static function isHttps() {
		return \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps();
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
					$arData = array_merge($arBefore, array($strKey => $mItem), $arAfter);
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
					$arData = array_merge($arBefore, array($strKey => $mItem), $arAfter);
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
		} else {
			$arData = $callbackFunction($arData, $arParams);
		}
		return $arData;
	}
	
	/**
	 *	Is managed cache on ?
	 */
	public static function isManagedCacheOn(){
		return (Option::get('main', 'component_managed_cache_on', 'N') != 'N' || defined('BX_COMP_MANAGED_CACHE'));
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
	 *	Get option value
	 */
	public static function getOption($strOption, $mDefaultValue=null, $strModuleId=null){
		$strModuleId = !is_null($strModuleId) ? $strModuleId : WDA_MODULE;
		return Option::get($strModuleId, $strOption, $mDefaultValue);
	}
	
	/**
	 *	Set option value
	 */
	public static function setOption($strOption, $mValue){
		return Option::set(WDA_MODULE, $strOption, $mValue);
	}
	
	/**
	 *	Remove
	 */
	public static function removeOption($strOption){
		return Option::delete(WDA_MODULE, ['name' => $strOption]);
	}
	
	/**
	 *	Remove all
	 */
	public static function removeAllOptions(){
		return Option::delete(WDA_MODULE);
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
	 *	CCatalog::GetByID with cache
	 */
	public static function getCatalogArray($intIBlockID) {
		$intIBlockID = IntVal($intIBlockID);
		$arCachedValue = &static::$arCache[static::CACHE_CATALOG_ARRAY][$intIBlockID];
		if(!is_array($arCachedValue)){
			$arCachedValue = [];
		}
		if($intIBlockID > 0) {
			if(!empty($arCachedValue)){
				return $arCachedValue;
			}
			elseif(\Bitrix\Main\Loader::includeModule('catalog')) {
				$arCatalog = \CCatalog::GetByID($intIBlockID);
				if(is_array($arCatalog) && !empty($arCatalog)) {
					$arCachedValue = $arCatalog;
					return $arCachedValue;
				}
				else { // Каталог теперь может не быть торговым каталогом, но может иметь торговые предложения
					$resCatalogs = \CCatalog::GetList([], array('PRODUCT_IBLOCK_ID' => $intIBlockID));
					if($arCatalog = $resCatalogs->getNext(false, false)){
						if (\Bitrix\Main\Loader::includeModule('iblock')) {
							$resIBlock = \CIBlock::GetList([], array('ID' => $intIBlockID));
							if($arIBlock = $resIBlock->GetNext(false, false)) {
								$arResult = array(
									'IBLOCK_ID' => $intIBlockID,
									'YANDEX_EXPORT' => 'N',
									'SUBSCRIPTION' => 'N',
									'VAT_ID' => 0,
									'PRODUCT_IBLOCK_ID' => 0,
									'SKU_PROPERTY_ID' => 0,
									'ID' => $intIBlockID,
									'IBLOCK_TYPE_ID' => $arIBlock['IBLOCK_TYPE_ID'],
									'LID' => $arIBlock['LID'],
									'NAME' => $arIBlock['NAME'],
									'OFFERS_IBLOCK_ID' => $arCatalog['IBLOCK_ID'],
									'OFFERS_PROPERTY_ID' => $arCatalog['SKU_PROPERTY_ID'],
									'OFFERS' => 'N',
								);
								return $arResult;
							}
						}
					}
				}
			}
		}
		return false;
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
	public static function addNotify($strMesage, $strTag, $bClose=true){
		$arParams = [
			'MODULE_ID' => WDA_MODULE,
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
	public static function getNotifyList(){
		$arResult = [];
		$arSort = [
			'ID' => 'ASC',
		];
		$arFilter = [
			'MODULE_ID' => WDA_MODULE,
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
	function scandir($strDir, $arParams=[]) {
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
	 *	Get list of iblocks
	 */
	static function getIBlocks($bGroupByType=true, $bShowInactive=false) {
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
		foreach(getModuleEvents(WDA_MODULE, 'OnGetIBlockList', true) as $arEvent) {
			executeModuleEventEx($arEvent, array(&$arResult, $bGroupByType, $bShowInactive));
		}
		return $arResult;
	}
	
	/**
	 *	Get settings for iblock
	 */
	public static function getIBlockFields($intIBlockId, $strField=null, $bDefaultValue=null){
		$arIBlockFields = &static::$arCache[static::CACHE_IBLOCK_FIELDS][$intIBlockId];
		if(!is_array($arIBlockFields) || empty($arIBlockFields)) {
			$arIBlockFields = [];
			if(\Bitrix\Main\Loader::includeModule('iblock')){
				$arIBlockFields = \CIBlock::getFields($intIBlockId);
			}
		}
		if(!is_null($strField)){
			if($bDefaultValue){
				return $arIBlockFields[$strField]['DEFAULT_VALUE'];
			}
			return $arIBlockFields[$strField];
		}
		return $arIBlockFields;
	}
	
	/**
	 *	Get all avaiable currencies
	 */
	public static function getCurrencyList($bSimple=false, $bBaseFirst=true){
		$arResult = &static::$arCache[static::CACHE_CURRENCIES];
		if(!is_array($arResult) || empty($arResult)) {
			$arResult = [];
			if(\Bitrix\Main\Loader::includeModule('currency')) {
				$resCurrency = \CCurrency::GetList($by='SORT', $order='ASC', LANGUAGE_ID);
				while($arCurrency = $resCurrency->GetNext(false, false)) {
					$arCurrency['IS_BASE'] = FloatVal($arCurrency['AMOUNT']) == 1 ? true: false;
					if (isset($arCurrency['DEAULT']) && !isset($arCurrency['DEFAULT'])) {
						$arCurrency['DEFAULT'] = $arCurrency['DEAULT'];
						unset($arCurrency['DEAULT']);
					}
					$arResult[ToUpper($arCurrency['CURRENCY'])] = $arCurrency;
				}
				# Sort, BASE first
				if($bBaseFirst){
					uasort($arResult, function($a, $b){
						$a1 = $a['BASE'] == 'Y';
						$b1 = $b['BASE'] == 'Y';
						if(!$a1 && $b1){
							return 1;
						}
						elseif($a1 && !$b1){
							return -1;
						}
						else{
							return $a['SORT'] == $b['SORT'] ? 0 : ($a['SORT'] > $b['SORT'] ? 1 : -1);
						}
					});
				}
			}
			# Simple mode (e.g, for SelectBox)
			if($bSimple){
				foreach($arResult as $strCurrency => $arCurrency){
					$arResult[$strCurrency] = sprintf('[%s] %s', $arCurrency['CURRENCY'], $arCurrency['FULL_NAME']);
				}
			}
		}
		return $arResult;
	}
	
	/**
	 *	Get site base currency
	 */
	public static function getBaseCurrency(){
		foreach(static::getCurrencyList() as $arCurrency){
			if($arCurrency['BASE'] == 'Y'){
				return $arCurrency['CURRENCY'];
			}
		}
		return false;
	}
	
	/**
	 *	Get convert currency factor
	 */
	public static function getConvertCurrencyFactor($strCurrencyFrom, $strCurrencyTo){
		$strKey = sprintf('%s_%s', $strCurrencyFrom, $strCurrencyTo);
		$fRate = &static::$arCache[static::CACHE_CURRENCY_CONVERT_FACTOR][$strKey];
		if(!isset($fRate)) {
			$fRate = \CCurrencyRates::getConvertFactorEx($strCurrencyFrom, $strCurrencyTo);
		}
		return $fRate;
	}
	
	/**
	 *	Convert currency
	 */
	public static function convertCurrency($fPrice, $strCurrencyFrom, $strCurrencyTo=null, $bFormat=false, $bRound=false){
		if(\Bitrix\Main\Loader::includeModule('currency')){
			$arCurrencies = static::getCurrencyList();
			if(is_null($strCurrencyTo)){
				$strCurrencyTo = key($arCurrencies);
			}
			$fRate = static::getConvertCurrencyFactor($strCurrencyFrom, $strCurrencyTo);
			if($bFormat){
				return static::currencyFormat($fPrice, $strCurrencyTo);
			}
			elseif($bRound){
				return number_format($fPrice * $fRate, 2, '.', '');
			}
			else{
				return $fPrice * $fRate;
			}
		}
		return false;
	}
	
	/**
	 *	Format currency
	 */
	public static function currencyFormat($fPrice, $strCurrency){
		if(\Bitrix\Main\Loader::includeModule('currency')){
			return \CCurrencyLang::currencyFormat($fPrice, $strCurrency, true);
		}
		return sprintf('%s %s', $fPrice, $strCurrency);
	}
	
	/**
	 *	Convert currency
	 */
	public static function convertCurrencyArray($arPrice, $strCurrencyTo=null, $bFormat=false){
		return static::convertCurrency($arPrice['PRICE'], $arPrice['CURRENCY'], $strCurrencyTo, $bFormat);
	}
	
	/**
	 *	Get all avaiable prices
	 */
	public static function getPriceList($arSort=false) {
		$arResult = [];
		if(\Bitrix\Main\Loader::includeModule('catalog')) {
			if($arSort == false){
				$arSort = array('SORT' => 'ASC', 'ID' => 'ASC');
			}
			$resPrices = \CCatalogGroup::GetList($arSort);
			while ($arPrice = $resPrices->getNext(false, false)) {
				$arResult[$arPrice['ID']] = $arPrice;
			}
		}
		return $arResult;
	}
	
	/**
	 *	Get optimal price for element
	 */
	public static function getOptimalPrice($intElementId, $strSiteId, $arPrices=[], $strCurrency=null){
		if(\Bitrix\Main\Loader::includeModule('catalog')){
			$arPrices = is_array($arPrices) ? $arPrices : [];
			if(strlen($strCurrency)){
				\CCatalogProduct::setUsedCurrency($strCurrency);
			}
			$arOptimalPrice = \CCatalogProduct::getOptimalPrice($intElementId, 1, [], 'N', $arPrices, $strSiteId);
			return array_merge([
				'ID' => $arOptimalPrice['PRICE']['ID'],
				'VAT_RATE' => $arOptimalPrice['PRICE']['VAT_RATE'],
				'VAT_INCLUDED' => $arOptimalPrice['PRICE']['VAT_INCLUDED'],
				'IBLOCK_ID' => $arOptimalPrice['PRICE']['ELEMENT_IBLOCK_ID'],
				'PRODUCT_ID' => $arOptimalPrice['PRODUCT_ID'],
				'DISCOUNT_LIST' => $arOptimalPrice['DISCOUNT_LIST'],
			], $arOptimalPrice['RESULT_PRICE']);
		}
		return false;
	}
	
	/**
	 *	Format price type
	 */
	public static function formatPriceType($arPrice){
		return sprintf('[%d] [%s] %s', $arPrice['ID'], $arPrice['NAME'], $arPrice['NAME_LANG']);
	}
	
	/**
	 *	Format currency
	 */
	public static function formatCurrency($arCurrency){
		return sprintf('%s (%s)', $arCurrency['CURRENCY'], $arCurrency['FULL_NAME']);
	}
	
	/**
	 *	Format currency
	 */
	public static function formatSite($arSite){
		return sprintf('[%s] %s (%s)', $arSite['ID'], $arSite['SITE_NAME'], $arSite['SERVER_NAME']);
	}
	
	/**
	 *	Get stores list
	 */
	public static function getStoresList() {
		$arResult = &static::$arCache[static::CACHE_STORES];
		if(!is_array($arResult)){
			$arResult = [];
		}
		if(empty($arResult) && \Bitrix\Main\Loader::includeModule('catalog') && class_exists('\CCatalogStore')){
			$resStores = \CCatalogStore::getList(['SORT' => 'ASC']);
			while($arStore = $resStores->getNext(false, false)) {
				$arResult[$arStore['ID']] = $arStore;
			}
			unset($resStores, $arStore);
		}
		return $arResult;
	}
	
	/**
	 *	Get stores list
	 */
	public static function getMeasuresList($bSimple=false){
		$arResult = &static::$arCache[static::CACHE_MEASURES];
		if(!is_array($arResult)){
			$arResult = [];
		}
		if(empty($arResult) && \Bitrix\Main\Loader::includeModule('catalog')){
			$resMeasure = \CCatalogMeasure::getList([], []);
			while($arMeasure = $resMeasure->getNext(false, false)) {
				$arResult[$arMeasure['ID']] = $bSimple ? $arMeasure['MEASURE_TITLE'] : $arMeasure;
			}
			unset($resMeasure, $arMeasure);
		}
		return $arResult;
	}
	
	/**
	 *	Get stores list
	 */
	public static function getPrice($intPriceId, $strKey=null){
		$arResult = &static::$arCache[static::CACHE_PRICES][$intPriceId];
		if(!is_array($arResult)){
			$arResult = [];
		}
		if(empty($arResult) && \Bitrix\Main\Loader::includeModule('catalog')){
			$arResult = \CCatalogGroup::getById($intPriceId);
			$arResult['X'] = $intPriceId.' => '.rand(100, 999);
		}
		if(strlen($strKey)){
			return $arResult[$strKey];
		}
		return $arResult;
	}
	
	/**
	 *	Get VAT list
	 */
	public static function getVatList($bSimple=false){
		$arVatValues = &static::$arCache[static::CACHE_VAT];
		if(!is_array($arVatValues)){
			$arVatValues = [];
		}
		if(empty($arVatValues) && \Bitrix\Main\Loader::includeModule('catalog')){
			$resVat = \CCatalogVat::getList(['RATE' => 'ASC']);
			while($arVat = $resVat->getNext()) {
				$arVatValues[$arVat['ID']] = $bSimple ? $arVat['NAME'] : $arVat;
			}
		}
		return $arVatValues;
	}
	
	/**
	 *	Get VAT by ID
	 */
	public static function getVatRateById($intVatId){
		$arVat = static::getVatList();
		if($intVatId){
			$arVat = $arVat[$intVatId];
			if(is_array($arVat)) {
				if(stripos($arVat['NAME'], static::getMessage('WDA_NO_VAT_PRETEXT')) === false){
					return $arVat['RATE'];
				}
			}
		}
		return ''; // No vat
	}
	
	/**
	 *	Get VAT by ID
	 */
	public static function getVatNameById($intVatId){
		$arVat = static::getVatList();
		if($intVatId){
			$arVat = $arVat[$intVatId];
			if(is_array($arVat)) {
				return $arVat['NAME'];
			}
		}
		return ''; // No vat
	}
	
	/**
	 *	Set price for product
	 */
	public static function setProductPrice($intProductId, $intPriceId, $fPrice, $strCurrency) {
		$bResult = false;
		if(\Bitrix\Main\Loader::includeModule('catalog')){
			$arProductFields = [
				'ID' => $intProductId,
			];
			\CCatalogProduct::add($arProductFields);
			$arPriceFields = [
				'PRODUCT_ID' => $intProductId,
				'CATALOG_GROUP_ID' => $intPriceId,
				'PRICE' => floatVal($fPrice),
				'CURRENCY' => $strCurrency,
			];
			$resPrice = \CPrice::getList([], ['PRODUCT_ID' => $intProductId, 'CATALOG_GROUP_ID' => $intPriceId]);
			if($arPrice = $resPrice->getNext(false, false)) {
				$bResult = !!\CPrice::update($arPrice['ID'], $arPriceFields);
			}
			else {
				$bResult = !!\CPrice::add($arPriceFields);
			}
		}
		return $bResult;
	}
	
	/**
	 *	Set quantity at selected store
	 */
	public static function setProductStoreAmount($intProductId, $intStoreId, $fAmount) {
		$bResult = false;
		if(\Bitrix\Main\Loader::includeModule('catalog') && static::isCatalogStoresAvailable()){
			$arStoreFields = [
				'PRODUCT_ID' => $intProductId,
				'AMOUNT' => floatVal($fAmount),
				'STORE_ID' => $intStoreId,
			];
			$resItem = \CCatalogStoreProduct::getList([], ['STORE_ID' => $intStoreId, 'PRODUCT_ID' => $intProductId], false, 
				false, ['ID']);
			if($arItem = $resItem->GetNext(false, false)) {
				$bResult = \CCatalogStoreProduct::update($arItem['ID'], $arStoreFields) > 0;
			}
			else {
				$bResult = \CCatalogStoreProduct::add($arStoreFields) > 0;
			}
		}
		return $bResult;
	}
	
	/**
	 *	Set product measure ratio
	 */
	public static function setProductMeasureRatio($intElementId, $fValue){
		$bResult = false;
		if(\Bitrix\Main\Loader::includeModule('catalog')){
			$resRatio = \CCatalogMeasureRatio::getList([], ['PRODUCT_ID' => $intElementId]);
			if($arRatio = $resRatio->getNext(false, false)) {
				if(\CCatalogMeasureRatio::update($arRatio['ID'], ['RATIO' => $fValue])) {
					$bResult = true;
				}
			}
			else {
				if (\CCatalogMeasureRatio::add(['PRODUCT_ID' => $intElementId, 'RATIO' => $fValue])) {
					$bResult = true;
				}
			}
		}
		return $bResult;
	}
	
	/**
	 *	Set product field
	 */
	public static function setProductField($intProductId, $mCatalogField, $mValue=null) {
		$bResult = false;
		if(\Bitrix\Main\Loader::includeModule('catalog')){
			if(!is_array($mCatalogField)){
				$mCatalogField = [$mCatalogField => $mValue];
			}
			$arProductFields = array_merge([
				'ID' => $intProductId,
			], $mCatalogField);
			$bResult = !!\CCatalogProduct::add($arProductFields);
		}
		return $bResult;
	}
	
	/**
	 *	Set barcodes
	 */
	public static function setProductBarcodes($intProductId, $mValue, $bSaveCurrent=false){
		if(\Bitrix\Main\Loader::includeModule('catalog') && static::isCatalogBarcodeAvailable()){
			$arCatalogProduct = \CCatalogProduct::getById($intProductId);
			if(is_array($arCatalogProduct) && $arCatalogProduct['BARCODE_MULTI'] == 'Y'){
				return false;
			}
			else{
				$bCanAdd = is_array($arCatalogProduct);
				if(!$bCanAdd){
					$bCanAdd = \CCatalogProduct::add(['ID' => $intProductId]);
				}
				if($bCanAdd){
					$mValue = is_array($mValue) ? $mValue : [$mValue];
					foreach($mValue as $key => $strBarcode){
						if(!strlen($strBarcode)){
							unset($mValue[$key]);
						}
					}
					$arCurrent = [];
					$arFilter = ['PRODUCT_ID' => $intProductId];
					$arSelect = ['ID', 'BARCODE'];
					$resBarcodes = \CCatalogStoreBarCode::getList([], $arFilter, false, false, $arSelect);
					while($arBarcode = $resBarcodes->getNext()){
						$arCurrent[$arBarcode['ID']] = $arBarcode['BARCODE'];
					}
					$arDelete = [];
					$arAdd = [];
					foreach($mValue as $strBarcode){
						if(!in_array($strBarcode, $arCurrent)){
							$arAdd[] = $strBarcode;
						}
					}
					if(!$bSaveCurrent){
						foreach($arCurrent as $intBarcodeId => $strBarcode){
							if(!in_array($strBarcode, $mValue)){
								$arDelete[$intBarcodeId] = $strBarcode;
							}
						}
					}
					foreach($arDelete as $intBarcodeId => $strBarcode){
						\CCatalogStoreBarCode::delete($intBarcodeId);
					}
					foreach($arAdd as $strBarcode){
						\CCatalogStoreBarCode::add([
							'PRODUCT_ID' => $intProductId,
							'BARCODE' => $strBarcode,
						]);
					}
					return true;
				}
			}
		}
		return false;
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
	 *	Show file file /include/
	 */
	public static function includeFile($strFile, $arParams=[]){
		$strFile = __DIR__.'/include/template/'.$strFile.'.php';
		ob_start();
		if(is_file($strFile)){
			static::loadMessages($strFile);
			$GLOBALS['arParams'] = $arParams; // If in demo-mode, 2nd argument is not '$arParams' :( - this looks like $_1565435424
			include($strFile);
		}
		return ob_get_clean();
	}
	
	/**
	 *	
	 */
	public static function findPlugins($strEntityType, $bGroup=true){
		$strDocumentRoot = static::root();
		$arPlugins = &static::$arCache[static::CACHE_PLUGINS][$strEntityType];
		if(!is_array($arPlugins) || empty($arPlugins)) {
			$arPlugins = array();
			$strPluginsDir = static::getPluginsDir($strEntityType);
			// Search plugins
			try {
				$resHandle = opendir($strDocumentRoot.$strPluginsDir);
				while ($strPluginDir = readdir($resHandle)) {
					if($strPluginDir != '.' && $strPluginDir != '..') {
						$strPluginFullDir = $strDocumentRoot.$strPluginsDir.'/'.$strPluginDir;
						if(is_dir($strPluginFullDir) && is_file($strPluginFullDir.'/class.php')) {
							static::loadMessages($strPluginFullDir.'/class.php');
							static::loadMessages($strPluginFullDir.'/.form.php');
							require_once($strPluginFullDir.'/class.php');
						}
					}
				}
				closedir($resHandle);
			}
			catch(\SystemException $obException) {
				//
			}
			// You can add your custom plugin
			foreach (static::getEventHandlers('OnFindPlugins') as $arHandler) {
				ExecuteModuleEventEx($arHandler, [$strEntityType, $strPluginsDir]);
			}
			//
			$strPluginClass = '\Plugin'.toUpper(substr($strEntityType, 0, 1)).toLower(substr($strEntityType, 1));
			// Search children of Plugin class - it will be our plugins
			$arCachePaths = &static::$arCache[static::CACHE_PLUGIN_PATHS];
			$arCachePaths = [];
			foreach(get_declared_classes() as $strClass) {
				if(is_subclass_of($strClass, __NAMESPACE__.$strPluginClass)) {
					$strPluginCode = $strClass::getCode();
					$strClassFilename = static::getClassFilename($strClass);
					$strClassFilename = static::path($strClassFilename);
					$arCachePaths[$strPluginCode] = $strClassFilename;
					if(stripos($strClassFilename, $strDocumentRoot.$strPluginsDir) === false){
						static::loadMessages($strClassFilename);
					}
				}
			}
			// 
			foreach(get_declared_classes() as $strClass) {
				if(is_subclass_of($strClass, __NAMESPACE__.$strPluginClass)) {
					if(!$strClass::isDisabled()){
						$strPluginCode = $strClass::getCode();
						$arPlugins[$strPluginCode] = [
							'CLASS' => $strClass,
							'GROUP' => $strClass::getGroup(),
							'CODE' => $strPluginCode,
							'NAME' => $strClass::getName(),
							'HINT' => $strClass::getHint(),
						];
					}
				}
			}
			// You can modify plugins list
			foreach (static::getEventHandlers('OnAfterFindPlugins') as $arHandler) {
				ExecuteModuleEventEx($arHandler, [&$arPlugins]);
			}
			$arPlugins = is_array($arPlugins) ? $arPlugins : [];
			// Remove wrong or corrupted plugins
			foreach($arPlugins as $strPlugin => $arPlugin) {
				$bCorruptedPlugin = !is_array($arPlugin) || !strlen($arPlugin['CODE']) || !strlen($arPlugin['NAME'])
					|| $strPlugin != $arPlugin['CODE'] || is_numeric($strPlugin)
					|| !strlen($arPlugin['CLASS']) || !class_exists($arPlugin['CLASS'])
					|| !is_subclass_of($arPlugin['CLASS'], __NAMESPACE__.$strPluginClass);
				if($bCorruptedPlugin) {
					unset($arPlugins[$strPlugin]);
					//
				}
			}
			// Determine type of plugin - native or custom, and directory
			foreach($arPlugins as $strPlugin => $arPlugin) {
				$arPlugins[$strPlugin]['TYPE'] = static::TYPE_NATIVE;
				$strFileClass = $arCachePaths[$arPlugin['CODE']];
				if(stripos($strFileClass, $strDocumentRoot) !== 0){
					/*
					 *	Fix for some cases, e.g.:
					 *	static::root() is /home/bitrix/ext_www/mysite.ru
					 *	but Reflection determines path of plugins as
					 *	/home/bitrix/ext_www/core/bitrix/modules/webdebug.antirutin/plugins/yandex.market/class.php
					 *	In fact, that is not within the document root
					 */
					$intPos = stripos($strFileClass, '/bitrix/modules/');
					if($intPos !== false){
						$strFileClass = $strDocumentRoot.substr($strFileClass, $intPos);
					}
				}
				if(strlen($strFileClass)) {
					$strFileClass = substr($strFileClass, strlen($strDocumentRoot));
					$arPlugins[$strPlugin]['DIRECTORY'] = Helper::path(pathinfo($strFileClass, PATHINFO_DIRNAME));
					if(stripos($strFileClass,$strPluginsDir) === 0) {
						$arPlugins[$strPlugin]['TYPE'] = static::TYPE_NATIVE;
					}
				}
			}
			// Get icon
			foreach($arPlugins as $strPlugin => $arPlugin) {
				$arPlugins[$strPlugin]['ICON'] = false;
				$arPlugins[$strPlugin]['ICON_BASE64'] = false;
				$strFilename = $arPlugin['DIRECTORY'].'/icon.png';
				$arPlugins[$strPlugin]['ICON_FILE'] = $strFilename;
				#
				if(is_file($strDocumentRoot.$strFilename)){
					$arPlugins[$strPlugin]['ICON'] = $strFilename;
					$arPlugins[$strPlugin]['ICON_BASE64'] = 'data:image/png;base64,'
						.base64_encode(file_get_contents($strDocumentRoot.$strFilename));
				}
			}
			// Sort
			uasort($arPlugins, function($a, $b){
				$strNameA = toLower($a['NAME']);
				$strNameB = toLower($b['NAME']);
				$bCustomA = strpos($strNameA, '[') !== false;
				$bCustomB = strpos($strNameB, '[') !== false;
				if($bCustomA && !$bCustomB){
					return 1;
				}
				elseif(!$bCustomA && $bCustomB){
					return -1;
				}
				else{
					return strcmp($strNameA, $strNameB);
				}
			});
		}
		// Group subplugins
		if($bGroup) {
			$arGroups = static::getPluginGroups();
			foreach($arPlugins as $key => $arPlugin){
				if(is_array($arPlugin['GROUP'])){
					# Create custom group
					if(!is_array($arGroups[$arPlugin['GROUP']['CODE']])){
						$arPlugin['GROUP']['SORT'] = is_numeric($arPlugin['GROUP']) && $arPlugin['GROUP'] > 0 ? $arPlugin['GROUP'] : 500;
						$arGroups[$arPlugin['GROUP']['CODE']] = $arPlugin['GROUP'];
					}
					$arPlugins[$key]['GROUP'] = $arPlugin['GROUP']['CODE'];
				}
			}
			foreach($arGroups as $key => $arGroup){
				$arGroups[$key]['ITEMS'] = [];
			}
			uasort($arGroups, function($arGroupA, $arGroupB){
				return strcmp($arGroupA['SORT'], $arGroupB['SORT']);
			});
			foreach($arPlugins as $arPlugin){
				$arGroups[$arPlugin['GROUP']]['ITEMS'][$arPlugin['CODE']] = $arPlugin;
			}
			foreach($arGroups as $key => $arGroup){
				if(empty($arGroup['ITEMS'])){
					unset($arGroups[$key]);
				}
			}
			return $arGroups;
		}
		return $arPlugins;
	}
	
	/**
	 *	Get directory with plugins (relative)
	 */
	protected static function getPluginsDir($strEntityType){
		$strDir = substr(__DIR__, strlen(static::root()));
		# Correct path (example: document root is '/home/a/abcdef/public_html', but __DIR__ is '/home/a/abcdef/new/public_html/bitrix/modules/webdebug.antirutin')
		$intPos = stripos(__DIR__, '/bitrix/modules/');
		if($intPos !== false){
			$strDir = substr(__DIR__, $intPos);
		}
		switch($strEntityType){
			case static::TYPE_ELEMENT:
				$strDir .= '/plugins/element';
				break;
			case static::TYPE_SECTION:
				$strDir .= '/plugins/section';
				break;
		}
		return $strDir;
	}
	
	/**
	 *	Get all groups
	 */
	public static function getPluginGroups(){
		$arGroups = [
			'GENERAL' => ['SORT' => 100],
			'CATALOG' => ['SORT' => 200],
			'IMAGES' => ['SORT' => 300],
			'CUSTOM' => ['SORT' => 900],
		];
		foreach($arGroups as $key => $name){
			$arGroups[$key]['NAME'] = static::getMessage('WDA_PLUGINGROUP_'.$key);
		}
		return $arGroups;
	}
	
	/**
	 *	Get lang phrase prefixes (use in lang/ru/class.php in plugins)
	 */
	public static function getPluginLangPrefix($strFile, &$strLang, &$strHint){
		$strLang = '';
		$strHint = '';
		$strField = '';
		$arCachePaths = &static::$arCache[static::CACHE_PLUGIN_PATHS];
		if(is_array($arCachePaths)){
			$strPath = static::path(pathinfo($strFile, PATHINFO_DIRNAME));
			$strPath = preg_replace('#^(.*?)/lang/[a-z]{2}(.*?)$#i', '$1', $strPath);
			if(strlen($strPath) && is_dir($strPath)){
				$strPath .= '/class.php';
			}
			$strCode = array_search($strPath, $arCachePaths);
			if(strlen($strCode)){
				$strLang = Plugin::LANG_PREFIX.'_'.$strCode.'_';
				$strHint = $strLang.'HINT_';
			}
		}
	}
	
	/**
	 *	Get event handlers all
	 */
	public static function getEventHandlers($strEvent){
		return \Bitrix\Main\EventManager::getInstance()->findEventHandlers(WDA_MODULE, $strEvent);
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
	 *	Add js lang phrases
	 */
	public static function addJsLangPhrases($arPhrases, $strPrefix=null){
		$strJs = static::includeFile('lang_phrases', [
			'PREFIX' => $strPrefix,
			'PHRASES' => $arPhrases,
		]);
		\Bitrix\Main\Page\Asset::getInstance()->addString($strJs, true, \Bitrix\Main\Page\AssetLocation::AFTER_CSS);
	}
	
	/**
	 *	Show popup by id
	 */
	public static function getPopupContent($strPopupId, $arParams=[]){
		ob_start();
		$strDir = realpath(__DIR__.'/include/popup');
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
			if(isset($arGet['ajax_action']) && !static::isUtf()){
				$arPost = static::convertEncoding($arPost, 'UTF-8', 'CP1251');
			}
			$arQuery = [$arGet, $arPost];
		}
		return $arQuery;
	}
	
	/**
	 *	Get first non-empty value
	 */
	public function getFirstValue($arValues, $bInteger=false){
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
	 *	Get profile array
	 */
	public function getProfileArray($intProfileId){
		$arProfile = [];
		if(is_numeric($intProfileId) && $intProfileId > 0){
			$arProfile = Profile::getList(['filter' => ['ID' => $intProfileId]])->fetch();
			if(!is_array($arProfile)){
				$arProfile = [];
			}
		}
		return $arProfile;
	}
	/**
	 *	Create directories path for file
	 */
	public static function createDirectoriesForFile($strFileName, $bAutoChangeOwner=false){
		$strDirname = static::getDirectoryForFile($strFileName);
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
	public static function getDirectoryForFile($strFileName){
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
	 *	Wrapper for SelectBoxFromArray()
	 */
	public static function selectBox($strName, $arValues, $strSelected=null, $strDefault=null, $strAttr=null, $strInputId=null, $bSelect2=true){
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
		$strHtml = static::selectBoxFromArray($strName, $arValues, $strSelected, $strDefault, $strAttr);
		$strHtml = sprintf('<div id="%s">%s</div>', $strId, $strHtml);
		if($bSelect2){
			$strHtml .= sprintf('<script>wdaSelect2($(\'#%s > select\'));</script>', $strId);
		}
		return $strHtml;
	}
	
	/**
	 *	
	 */
	public static function selectBoxFromArray($strBoxName, $db_array, $mSelectedVal = "", $strDetText = "", $field1=""){
		$boxName = htmlspecialcharsbx($strBoxName);
		$strReturnBox = '<select '.$field1.' name="'.$boxName.'">';
		if(isset($db_array["reference"]) && is_array($db_array["reference"]))
			$ref = $db_array["reference"];
		elseif(isset($db_array["REFERENCE"]) && is_array($db_array["REFERENCE"]))
			$ref = $db_array["REFERENCE"];
		else
			$ref = array();
		if(isset($db_array["reference_id"]) && is_array($db_array["reference_id"]))
			$ref_id = $db_array["reference_id"];
		elseif(isset($db_array["REFERENCE_ID"]) && is_array($db_array["REFERENCE_ID"]))
			$ref_id = $db_array["REFERENCE_ID"];
		else
			$ref_id = array();
		if($strDetText <> '')
			$strReturnBox .= '<option value="">'.$strDetText.'</option>';
		foreach($ref as $i => $val){
			$strReturnBox .= '<option';
			if(is_array($mSelectedVal)){
				if(in_array($ref_id[$i], $mSelectedVal)){
					$strReturnBox .= ' selected';
				}
			}
			elseif(strcasecmp($ref_id[$i], $mSelectedVal) == 0) {
				$strReturnBox .= ' selected';
			}
			$strReturnBox .= ' value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($val).'</option>';
		}
		return $strReturnBox.'</select>';
	}
	
	/**
	 *	Replace values in input with rand_id_*** (in action array)
	 */
	public static function replaceActionRandInputValues($arAction){
		$arResult = [];
		foreach($arAction as $key => $value){
			$key = preg_replace('#_'.Helper::RAND_ID_PREFIX.'[A-z0-9]{32}#i', '', $key);
			if(isset($arResult[$key])){
				if(!is_array($arResult[$key])){
					$arResult[$key] = [$arResult[$key]];
				}
				$arResult[$key][] = $value;
			}
			else{
				$arResult[$key] = $value;
			}
		}
		return $arResult;
	}
	
	/**
	 *	Get current module version
	 */
	public static function getModuleVersion($strModuleId){
		include static::root().'/bitrix/modules/'.$strModuleId.'/install/version.php';
		return $arModuleVersion['VERSION'];
	}
	
	/**
	 *	Get current domain (without port)
	 */
	public static function getCurrentDomain(){
		return preg_replace('#:(\d+)$#', '', \Bitrix\Main\Context::getCurrent()->getServer()->getHttpHost());
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
	 *	Analog for bitrix calendar()
	 */
	public static function calendar($strInputId, $bTime=false){
		return static::includeFile('calendar', [
			'INPUT_ID' => $strInputId,
			'WITH_TIME' => $bTime,
		]);
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
	 *	
	 */
	public static function getMailtoLink(array $arData, $bJustHref=false){
		$arData['EMAIL'] = strlen($arData['EMAIL']) ? $arData['EMAIL'] : 'info@webdebug.ru';
		$arData['SUBJECT'] = strlen($arData['SUBJECT']) ? sprintf('subject=%s', $arData['SUBJECT']) : '';
		$arData['BODY'] = strlen($arData['BODY']) ? sprintf('body=%s', $arData['BODY']) : '';
		$arData['ATTR'] = strlen($arData['ATTR']) ? ' '.$arData['ATTR'] : '';
		$arQuery = [$arData['SUBJECT'], $arData['BODY']];
		$arQuery = array_filter($arQuery);
		$strQuery = implode('&', $arQuery);
		$strUrl = $arData['EMAIL'].(strlen($strQuery) ? '?'.$strQuery : '');
		if($bJustHref){
			return sprintf('mailto:%s', $strUrl);
		}
		return sprintf('<a href="mailto:%s"%s>%s</a>', $strUrl, $arData['ATTR'], $arData['TEXT']);
	}
	
	/**
	 *	Change price value
	 */
	public static function interpretPrice($fPrice, $strExpression, $fMinPrice=0) {
		if(strlen($strExpression)){
			if(is_string($fPrice)){
				$fPrice = str_replace(',', '.', $fPrice);
			}
			if(preg_match('#^\+([\d.]+)%$#', $strExpression, $arMatch)) { // +20%
				$fPrice += round($fPrice * $arMatch[1] / 100, 2);
			}
			elseif(preg_match('#^\-([\d.]+)%$#', $strExpression, $arMatch)) { // -50%
				$fPrice -= round($fPrice * $arMatch[1] / 100, 2);
			}
			elseif(preg_match('#^([\d.]+)%$#', $strExpression, $arMatch)){ // 150%
				$fPrice = round($fPrice * $arMatch[1] / 100, 2);
			}
			elseif(preg_match('#^\+([\d.]+)$#', $strExpression, $arMatch)){ // +180
				$fPrice += $arMatch[1];
			}
			elseif(preg_match('#^\-([\d.]+)$#', $strExpression, $arMatch)){ // -30
				$fPrice -= $arMatch[1];
			}
			if($fPrice < $fMinPrice){
				$fPrice = $fMinPrice;
			}
		}
		return $fPrice;
	}
	
	/**
	 *	Round numeric value
	 */
	public static function roundEx($fValue, $intPrecision=0, $strFunc=null) {
		$intPow = pow(10, $intPrecision);
		$strFunc = in_array($strFunc, array('round', 'floor', 'ceil')) ? $strFunc : 'round';
		return call_user_func($strFunc, $fValue * $intPow) / $intPow;
	}
	
}

?>