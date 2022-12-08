<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class Option
 * @package WD\Utilities
 */
class Option {

	/**
	 * Get options list
	 */
	function GetList($arSort=false, $arFilter=false) {
		global $DB;
		if (!is_array($arSort)) {$arSort = array();}
		if (!is_array($arFilter)) {
			$arFilter = array();
		} elseif (!empty($arFilter)) {
		}
		foreach ($arFilter as $arFilterKey => $arFilterVal) {
			if (trim($arFilterVal)=="") {unset($arFilter[$arFilterKey]);}
		}
		$arWhere = array();
		foreach ($arFilter as $Key => $arFilterItem) {
			$_Key = $Key;
			$SubStr2 = substr($Key, 0, 2);
			$SubStr1 = substr($Key, 0, 1);
			$Key = $DB->ForSQL($Key);
			$arFilterItem = $DB->ForSQL($arFilterItem);
			if ($_Key=="ID") {
				$arWhere[] = "concat(IFNULL(`MODULE_ID`,''),'_',IFNULL(`NAME`,''),'_',IFNULL(`SITE_ID`,'')) = '{$arFilterItem}'";
			} elseif ($SubStr2==">=" || $SubStr2=="<=") {
				$Val = substr($Key, 2);
				if (!self::FieldExists($Val)) continue;
				if ($SubStr2 == ">=") {$arWhere[] = "`{$Val}` >= '{$arFilterItem}'";}
				if ($SubStr2 == "<=") {$arWhere[] = "`{$Val}` <= '{$arFilterItem}'";}
			} elseif ($SubStr1==">" || $SubStr1=="<") {
				$Val = substr($Key, 1);
				if (!self::FieldExists($Val)) continue;
				if ($SubStr1 == ">") {$arWhere[] = "`{$Val}` > '{$arFilterItem}'";}
				if ($SubStr1 == "<") {$arWhere[] = "`{$Val}` < '{$arFilterItem}'";}
				if ($SubStr1 == "!") {$arWhere[] = "`{$Val}` <> '{$arFilterItem}'";}
			} elseif ($SubStr1=="%") {
				$Val = substr($Key, 1);
				if (!self::FieldExists($Val)) continue;
				$arWhere[] = "upper(`{$Val}`) like upper('%{$arFilterItem}%') and `{$Val}` is not null";
			} else {
				if (!self::FieldExists($Key)) continue;
				$arWhere[] = "`{$Key}` = '{$arFilterItem}'";
			}
		}
		$SQL = "SELECT *,concat(IFNULL(`MODULE_ID`,''),'_',IFNULL(`NAME`,''),'_',IFNULL(`SITE_ID`,'')) as `ID` FROM `b_option`";
		if (count($arWhere)>0) {
			$SQL .= " WHERE ".implode(" AND ", $arWhere);
		}
		if (is_array($arSort) && !empty($arSort)) {
			foreach ($arSort as $arSortKey => $arSortItem) {
				if (!self::FieldExists($arSortKey)) {
					unset($arSort[$arSortKey]);
				}
			}
		}
		if (is_array($arSort) && !empty($arSort)) {
			$SQL .= " ORDER BY ";
			$arSortBy = array();
			foreach ($arSort as $arSortKey => $arSortItem) {
				if (ToLower($arSortKey)=="sort") continue;
				$arSortKey = $DB->ForSQL($arSortKey);
				$arSortItem = $DB->ForSQL($arSortItem);
				if (trim($arSortKey)!="") {
					$SortBy = "`{$arSortKey}`";
					if (trim($arSortItem)!="") {
						$SortBy .= " {$arSortItem}";
					}
					$arSortBy[] = $SortBy;
				}
			}
			$SQL .= implode(", ", $arSortBy);
		}
		return $DB->Query($SQL, false, __LINE__);
	}
	
	/**
	 *	Check if field exists
	 */
	function FieldExists($Field) {
		return in_array(ToLower($Field),array("module_id","name","value","site_id"));
	}
	
	/**
	 *	Get one options
	 */
	function GetOne($ModuleID, $Name, $SiteID=false) {
		$SQL = "SELECT * FROM `b_option` WHERE `MODULE_ID`='{$ModuleID}' AND `NAME`='{$Name}'";
		if ($SiteID) {
			$SQL .= " AND `SITE_ID`='{$SiteID}'";
		}
		$SQL .= " LIMIT 0,1";
		return $DB->Query($SQL, false, __LINE__);
	}
	
	/**
	 *	Deleting option
	 */
	function Delete($ID) {
		global $DB;
		$SQL = "DELETE FROM `b_option` WHERE concat(IFNULL(`MODULE_ID`,''),'_',IFNULL(`NAME`,''),'_',IFNULL(`SITE_ID`,''))='{$ID}' LIMIT 1";
		$resResult = $DB->Query($SQL, false, __LINE__);
		static::cleanCache();
		return $resResult;
	}
	
	/**
	 *	Updating
	 */
	function Update($ID, $arFields) {
		global $DB;
		if (!$ID || !is_array($arFields) || empty($arFields)) {
			return false;
		}
		if (isset($arFields["ID"])) unset($arFields["ID"]);
		$SQL_SET = array();
		foreach ($arFields as $Key => $Field) {
			$Key = $DB->ForSQL($Key);
			$Field = $DB->ForSQL($Field);
			$SQL_SET[] = "`{$Key}`='{$Field}'";
		}
		$SQL_SET = implode(",",$SQL_SET);
		$SQL = "UPDATE `b_option` SET {$SQL_SET} WHERE concat(IFNULL(`MODULE_ID`,''),'_',IFNULL(`NAME`,''),'_',IFNULL(`SITE_ID`,''))='{$ID}' LIMIT 1";
		$Res = $DB->Query($SQL, true, __LINE__);
		static::cleanCache();
		if ($Res === false) {
			return false;
		}
		return $Res->AffectedRowsCount();
	}
	
	function GetModulesList() {
		global $DB;
		$arModules = array();
		// Modules from DB
		$SQL = "SELECT `MODULE_ID` FROM `b_option` GROUP BY `MODULE_ID` ORDER BY `MODULE_ID` ASC";
		$resModules = $DB->Query($SQL, true, __LINE__);
		while ($Module = $resModules->GetNext(false,false)) {
			$arModules[$Module["MODULE_ID"]] = self::GetModuleInfo($Module["MODULE_ID"]);
		}
		// Modules from /bitrix/modules/
		$Handle=@opendir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules");
		if($Handle) {
			while (false !== ($ModuleID = readdir($Handle))) {
				if(is_dir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$ModuleID) && $ModuleID!="." && $ModuleID!=".." && $ModuleID!="main" && strpos($ModuleID, ".") === false) {
					if (!isset($arModules[$ModuleID]) || !is_array($arModules[$ModuleID]) || empty($arModules[$ModuleID]) || !isset($arModules[$ModuleID]["NAME"]) || trim($arModules[$ModuleID]["NAME"])=="") {
						$arModules[$ModuleID] = self::GetModuleInfo($ModuleID);
					}
				}
			}
		}
		ksort($arModules);
		return $arModules;
	}
	
	function GetModuleInfo($ModuleID) {
		$arResult = array();
		if($info = \CModule::CreateModuleObject($ModuleID)) {
			$arResult["ID"] = $info->MODULE_ID;
			$arResult["NAME"] = $info->MODULE_NAME;
		}
		return $arResult;
	}
	
	public static function cleanCache(){
		\Bitrix\Main\Application::getInstance()->getManagedCache()->clean('b_option');
	}
	
}

?>