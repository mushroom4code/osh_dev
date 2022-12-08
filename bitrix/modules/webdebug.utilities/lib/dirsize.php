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

class DirSize extends \CDirScan {
	const STEP_TIME = 5;
	const DELAY_TIME = 0;
	
	var $fStartTime = false;
	var $fStepTime = false;
	var $arState = false;
	
	function __construct($StepTime, &$arState){
		$this->fStartTime = microtime(true);
		$this->fStepTime = $StepTime;
		$this->arState = &$arState;
	}
	
	function ProcessFile($f) {
		global $DB;
		if($this->HaveTime()) {
			$this->arState['FILES_FOUND_COUNT']++;
			$intFilesize = IntVal(filesize($f));
			$this->arState['FILES_FOUND_SIZE'] += $intFilesize;
			$strRelPath = $DB->ForSQL(substr($f, strlen($_SERVER['DOCUMENT_ROOT'])));
			$strFileHash = MD5($strRelPath);
			$strDirname = pathinfo($strRelPath,PATHINFO_DIRNAME);
			$intFileDepth = substr_count($strRelPath,'/');
			if($strDirname!='/') {
				$arDirectory = explode('/',$strDirname);
				foreach($arDirectory as $Key => $Value){
					if(strlen($Value)===0) {
						unset($arDirectory[$Key]);
					}
				}
				$strPath = '';
				$intDepth = 0;
				foreach($arDirectory as $strDirectory){
					$intDepth++;
					$strPath .= '/'.$strDirectory;
					$strPathHash = MD5($strPath);
					$DB->Query("UPDATE wdu_dirsize SET SIZE = SIZE+{$intFilesize}, COUNT = COUNT+1 WHERE TYPE='D' AND PATH_HASH='{$strPathHash}';");
				}
			}
			$SQL = "SELECT PATH_HASH FROM wdu_dirsize WHERE TYPE='F' AND PATH_HASH='{$strFileHash}';";
			$resPath = $DB->Query($SQL);
			if(!$resPath->GetNext(false,false)) {
				$SQL = "INSERT INTO wdu_dirsize (TYPE,PATH,PATH_HASH,SIZE,DEPTH) VALUES ('F','{$strRelPath}','{$strFileHash}',{$intFilesize},{$intFileDepth});";
				$DB->Query($SQL);
			}
			return true;
		}
		return 'BREAK';
	}
	
	function ProcessDirBefore($f) {
		global $DB;
		if($f!=$_SERVER['DOCUMENT_ROOT']) {
			$strPath = $DB->ForSQL(substr($f, strlen($_SERVER['DOCUMENT_ROOT'])));
			$intDepth = substr_count($strPath,'/');
			$strPathHash = MD5($strPath);
			$SQL = "SELECT PATH_HASH FROM wdu_dirsize WHERE TYPE='D' AND PATH_HASH='{$strPathHash}';";
			$resPath = $DB->Query($SQL);
			if(!$resPath->GetNext(false,false)) {
				$SQL = "INSERT INTO wdu_dirsize (TYPE,PATH,PATH_HASH,SIZE,DEPTH) VALUES ('D','{$strPath}','{$strPathHash}',0,{$intDepth});";
				$DB->Query($SQL);
			}
		}
		return true;
	}
	
	function ProcessDirAfter($f) {
		return true;
	}
	
	function Skip($f) {
		$res = false;
		if ($this->startPath) {
			if (strpos($this->startPath.'/', $f.'/') === 0) {
				if ($this->startPath == $f) {
					unset($this->startPath);
				}
				return false;
			} else {
				return true;
			}
		}
		return $res;
	}
	
	function HaveTime(){
		return microtime(true) - $this->fStartTime < $this->fStepTime;
	}
	
	function RemoveLastTime(){
		\COption::RemoveOption(WDU_MODULE,'dirsize_date_last');
	}
	
	function SetLastTime(){
		\COption::SetOptionString(WDU_MODULE,'dirsize_date_last',time());
	}
	
	function GetLastTime(){
		$strLastTime = \COption::GetOptionString(WDU_MODULE,'dirsize_date_last');
		return !empty($strLastTime) ? $strLastTime : false;
	}
	
	function Truncate(){
		global $DB;
		$DB->Query("TRUNCATE wdu_dirsize;");
	}
	
	function GetPathItems($Path){
		global $DB;
		if(empty($Path)){
			$Path = '/';
		}
		$Path = str_replace('//','/',$Path.'/');
		$Depth = substr_count($Path,'/');
		$SQL = "SELECT * FROM wdu_dirsize WHERE DEPTH={$Depth} AND PATH LIKE '{$Path}%' ORDER BY SIZE DESC;";
		$resItems = $DB->Query($SQL);
		$arItems = array();
		while($arItem = $resItems->GetNext(false,false)) {
			$arItems[] = $arItem;
		}
		return $arItems;
	}
	
}
?>