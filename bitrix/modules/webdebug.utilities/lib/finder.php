<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/backup.php');

/**
 * Class Cli
 * @package WD\Utilities
 */

class Finder extends \CDirScan {
	const STEP_TIME = 5;
	const DELAY_TIME = 0;
	const RESULT_BREAK = 'BREAK';
	
	var $fStartTime = null;
	var $fStepTime = null;
	var $arState = null;
	
	public function __construct($fStepTime, &$arState){
		$this->fStartTime = microtime(true);
		$this->fStepTime = $fStepTime;
		$this->arState = &$arState;
	}
	
	public function processFile($strFile) {
		global $DB;
		if($this->haveTime()) {
			$this->searchInFile($strFile);
			return true;
		}
		return static::RESULT_BREAK;
	}
	
	protected function searchInFile($strFile){
		$arState = &$this->arState;
		$bSuccess = false;
		$strFileContent = file_get_contents($strFile);
		if($arState['ENCODING']){
			$strFileContent .= $this->convertCharset($strFileContent);
		}
		if($arState['REGEXP'] == 'Y'){
			if(preg_match($arState['TEXT'], $strFileContent)){
				$bSuccess = true;
			}
		}
		else{
			$strStrposFunc = $arState['CASE'] == 'Y' ? 'strpos' : 'stripos';
			$bSuccess = call_user_func($strStrposFunc, $strFileContent, $arState['TEXT']) !== false;
		}
		unset($strFileContent);
		$this->arState['FILES_COUNT']++;
		if($bSuccess){
			if($arState['RESULTS_COUNT'] < $arState['RESULTS_MAX']){
				$arState['RESULTS'][] = [
					'Hash' => md5($strFile),
					'File' => substr($strFile, strlen($_SERVER['DOCUMENT_ROOT'])),
					'Size' => \CFile::formatSize(filesize($strFile)),
				];
			}
			$arState['RESULTS_COUNT']++;
		}
	}
	
	public function skip($strFile) {
		$arState = &$this->arState;
		$bSkip = false;
		if(!$bSkip && $this->startPath) {
			if(strpos($this->startPath.'/', $strFile.'/') === 0) {
				if($this->startPath == $strFile) {
					unset($this->startPath);
				}
				$bSkip = false;
			}
			else {
				$bSkip = true;
			}
		}
		if(!$bSkip && is_file($strFile) && strlen($arState['FOLDER']) && $arState['FOLDER'] != '/'){
			if(strpos($arState['FOLDER'], '*') !== false || strpos($arState['FOLDER'], '?') !== false){
				$bSkip = true;
				$strCurDirReal = pathinfo($strFile, PATHINFO_DIRNAME);
				$strCurDirTest = $_SERVER['DOCUMENT_ROOT'].(strlen($arState['FOLDER']) ? $arState['FOLDER'] : '/');
				if(fnmatch($strCurDirTest, $strCurDirReal)){
					$bSkip = false;
				}
			}
			else{
				if(strpos($strFile, $_SERVER['DOCUMENT_ROOT'].(strlen($arState['FOLDER']) ? $arState['FOLDER'] : '/')) !== 0){
					$bSkip = true;
				}
			}
		}
		if(!$bSkip && is_file($strFile) && !empty($arState['FOLDER_EXCLUDE'])){
			foreach($arState['FOLDER_EXCLUDE'] as $strFolderExclude){
				$strCurDirReal = pathinfo($strFile, PATHINFO_DIRNAME);
				$strCurDirTest = $_SERVER['DOCUMENT_ROOT'].$strFolderExclude;
				if(fnmatch($strCurDirTest, $strCurDirReal)){
					$bSkip = true;
					break;
				}
			}
		}
		if(!$bSkip && is_file($strFile)){
			$bSkip = true;
			foreach($arState['FILTER'] as $strFilter){
				if($strFilter == '*' || fnmatch($strFilter, $strFile)){
					$bSkip = false;
					break;
				}
			}
		}
		if(!$bSkip && is_file($strFile)){
			$bSkip = filesize($strFile) > $arState['MAX_FILESIZE'];
		}
		return $bSkip;
	}
	
	public function haveTime(){
		return microtime(true) - $this->fStartTime < $this->fStepTime;
	}
	
	public function convertCharset($strContent){
		if(defined('BX_UTF') && BX_UTF === true){
			$strContent = $GLOBALS['APPLICATION']->convertCharset($strContent, 'CP1251', 'UTF-8');
		}
		else{
			$strContent = $GLOBALS['APPLICATION']->convertCharset($strContent, 'UTF-8', 'CP1251');
		}
		return $strContent;
	}
	
}
?>