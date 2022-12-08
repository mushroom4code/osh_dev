<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class PageProp
 * @package WD\Utilities
 */
class PageProp {
	const TableName = 'wdu_pageprop';
	
	/**
	 *	Add
	 */
	function Add($arFields) {
		global $DB;
		if ($DB->Add(static::TableName, $arFields, array(), '', true)) {
			return true;
		}
		return false;
	}
	
	/*
	 *	Update
	 */
	function Update($ID, $arFields) {
		global $DB;
		$arSQL = array();
		foreach ($arFields as $Key => $Field) {
			$Key = $DB->ForSQL($Key);
			#$Field = $DB->ForSQL($Field);
			$arSQL[] = "`{$Key}`='{$Field}'";
		}
		$strSQL = implode(',',$arSQL);
		$TableName = static::TableName;
		$SQL = "UPDATE `{$TableName}` SET {$strSQL} WHERE `ID`='{$ID}' LIMIT 1;";
		if ($DB->Query($SQL, true)) {
			return true;
		}
		return false;
	}
	
	/**
	 *	Get list
	 */
	function GetList($arSort=false, $arFilter=false) {
		global $DB;
		if (!is_array($arSort)) {$arSort = array("PROPERTY"=>"ASC");}
		foreach ($arSort as $Key => $Value) {
			$Value = strtolower($Value);
			if ($Value!="asc" && $Value!="desc") {
				unset($arSort[$Key]);
			}
		}
		$TableName = static::TableName;
		$SQL = "SELECT * FROM `{$TableName}`";
		if (is_array($arFilter) && !empty($arFilter)) {
			foreach ($arFilter as $arFilterKey => $arFilterVal) {
				if (trim($arFilterVal)=="") {unset($arFilter[$arFilterKey]);}
			}
			$arWhere = array();
			foreach ($arFilter as $Key => $arFilterItem) {
				$SubStr2 = substr($Key, 0, 2);
				$SubStr1 = substr($Key, 0, 1);
				$Key = $DB->ForSQL($Key);
				$arFilterItem = $DB->ForSQL($arFilterItem);
				if ($SubStr2==">=" || $SubStr2=="<=") {
					$Val = substr($Key, 2);
					if ($SubStr2 == ">=") {$arWhere[] = "`{$Val}` >= '{$arFilterItem}'";}
					if ($SubStr2 == "<=") {$arWhere[] = "`{$Val}` <= '{$arFilterItem}'";}
				} elseif ($SubStr1==">" || $SubStr1=="<") {
					$Val = substr($Key, 1);
					if ($SubStr1 == ">") {$arWhere[] = "`{$Val}` > '{$arFilterItem}'";}
					if ($SubStr1 == "<") {$arWhere[] = "`{$Val}` < '{$arFilterItem}'";}
					if ($SubStr1 == "!") {$arWhere[] = "`{$Val}` <> '{$arFilterItem}'";}
				} elseif ($SubStr1=="%") {
					$Val = substr($Key, 1);
					$arWhere[] = "upper(`{$Val}`) like upper ('%{$arFilterItem}%') and `{$Val}` is not null";
				} else {
					$arWhere[] = "`{$Key}` = '{$arFilterItem}'";
				}
			}
			if (count($arWhere)>0) {
				$SQL .= " WHERE ".implode(" AND ", $arWhere);
			}
		}
		// Sort
		if (is_array($arSort) && !empty($arSort)) {
			$SQL .= " ORDER BY ";
			$arSortBy = array();
			foreach ($arSort as $arSortKey => $arSortItem) {
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
		return $DB->Query($SQL, true);
	}
	
	/**
	 *	Get by ID
	 */
	function GetByID($ID) {
		return static::GetList(false,array("ID"=>$ID));
	}
	
	/**
	 *	Delete
	 */
	function Delete($ID) {
		global $DB;
		$TableName = static::TableName;
		$SQL = "DELETE FROM `{$TableName}` WHERE `ID`='{$ID}';";
		if ($DB->Query($SQL, true)) {
			return true;
		}
		return false;
	}
	
	/**
	 *	Handler for OnEndBufferContent
	 */
	function OnEndBufferContent_Handler(&$strContent) {
		// Handling public page props edit
		if(in_array($GLOBALS['APPLICATION']->GetCurPage(), ['/bitrix/admin/public_file_property.php','/bitrix/admin/public_folder_edit.php'])) {
			static::GetTypes();
			$strContent = preg_replace_callback('#<tr style.*?>[\s]{0,}<td class="bx-popup-label bx-width30">(.*?):</td>[\s]{0,}<td>(.*?)</td>[\s]{0,}</tr>#is'.(Helper::IsUtf()?'u':''),create_function(
				'$matches',
				'return '.__CLASS__.'::modifyPagePropsDialogReplace($matches);'
			),$strContent);
		}
	}
	
	/**
	 *	Замены в окне редактирования значений свойств
	 */
	function modifyPagePropsDialogReplace($Matches){
		$strData = $Matches[2];
		$PropertyID = false;
		$PropertyCode = false;
		$PropertyValue = false;
		if (preg_match('#<input type="text" name="PROPERTY\[([\d]+)\]\[VALUE\]" value="(.*?)".*?>#',$strData,$M)) {
			$PropertyID = $M[1];
			$PropertyValue = $M[2];
		}
		if (preg_match('#<input.*?name="PROPERTY\[[\d]+\]\[CODE\]" value="(.*?)".*?>#',$strData,$M)) {
			$PropertyCode = $M[1];
		}
		if (preg_match('#<div.*?id="bx_view_property_([\d])+".*?>(.*?)</div>#',$strData,$M)) {
			// If inherited props
			$PropertyID = $M[1];
			$PropertyValue = $M[2];
		}
		$bPropFound = false;
		$NewHTML = static::ShowControls($PropertyCode, $PropertyID, $PropertyValue, $_GET['site']);
		if ($NewHTML===false) {
			return $Matches[0];
		} else {
			$NewHTML .= "<input type=\"hidden\" name=\"PROPERTY[{$PropertyID}][CODE]\" value=\"{$PropertyCode}\" data-value=\"{$PropertyValue}\" />";
		}
		$NewHTML = "<tr><td class=\"bx-popup-label bx-width30\">{$Matches[1]}:</td><td>{$NewHTML}</td></tr>";
		return $NewHTML;
	}
	
	/**
	 *	Получение списка классов для реализации типов свойств
	 */
	function GetTypes($Force=false) {
		$arResult = array();
		if (!$Force && isset($GLOBALS['WD_PAGEPROPS_TYPES']) && is_array($GLOBALS['WD_PAGEPROPS_TYPES'])) {
			return $GLOBALS['WD_PAGEPROPS_TYPES'];
		}
		$ProvidersPath = BX_ROOT.'/modules/'.WDU_MODULE.'/include/pageprops/';
		if (is_dir($_SERVER['DOCUMENT_ROOT'].$ProvidersPath)) {
			$Handle = opendir($_SERVER['DOCUMENT_ROOT'].$ProvidersPath);
			while (($File = readdir($Handle))!==false) {
				if ($File != '.' && $File != '..') {
					if (is_file($_SERVER['DOCUMENT_ROOT'].$ProvidersPath.$File)) {
						$arPathInfo = pathinfo($File);
						if (ToUpper($arPathInfo['extension'])=='PHP') {
							require_once($_SERVER['DOCUMENT_ROOT'].$ProvidersPath.$File);
						}
					}
				}
			}
			closedir($Handle);
		}
		foreach(GetModuleEvents(WDU_MODULE, 'OnGetTypes', true) as $arEvent) {
			ExecuteModuleEventEx($arEvent);
		}
		$arDeclaredClasses = get_declared_classes();
		foreach($arDeclaredClasses as $ClassName) {
			if(is_subclass_of($ClassName, __NAMESPACE__.'\PagePropBase')){
				$Code = $ClassName::GetCode();
				$Name = $ClassName::GetName();
				$Icon = $ClassName::GetIcon();
				$arResult[$Code] = array(
					'NAME' => $Name,
					'CODE' => $Code,
					'ICON' => $Icon,
					'CLASS' => $ClassName,
				);
			}
		}
		$GLOBALS['WD_PAGEPROPS_TYPES'] = $arResult;
		return $arResult;
	}
	
	/**
	 *	Показ настроек
	 */
	function ShowSettings($PropertyCode, $SiteId, $PropertyType=false) {
		$PropertyType = trim($PropertyType);
		$arTypes = static::GetTypes();
		if ($PropertyType!==false && is_array($arTypes[$PropertyType])) {
			$ClassName = $arTypes[$PropertyType]['CLASS'];
			if (class_exists($ClassName) && method_exists($ClassName, 'ShowSettings')) {}
			return $ClassName::ShowSettings($PropertyCode, $SiteId);
		}
	}
	
	/**
	 *	Показ настроек
	 */
	function SaveSettings($PropertyCode, $PropertySite, $PropertyType, $arPost) {
		$PropertyType = trim($PropertyType);
		$arTypes = static::GetTypes();
		if ($PropertyType!==false && is_array($arTypes[$PropertyType])) {
			$ClassName = $arTypes[$PropertyType]['CLASS'];
			if (class_exists($ClassName) && method_exists($ClassName, 'SaveSettings')) {
				return $ClassName::SaveSettings($PropertyCode, $PropertySite, $arPost);
			}
		}
		elseif ($PropertyType == 'DEFAULT' || $PropertyType == '') {
			$arFilter = static::GetFilter($PropertyCode, $PropertySite);
			$resCurrentProp = static::GetList(false,$arFilter);
			if ($arCurrentItem = $resCurrentProp->GetNext()) {
				static::Delete($arCurrentItem['ID']);
			}
			return true;
		}
		return false;
	}
	
	/**
	 *	Получение фильтр для поиска нужного элемента
	 */
	function GetFilter($PropertyCode, $SiteID=false) {
		$arResult = array();
		$arResult['PROPERTY'] = $PropertyCode;
		$bDifferentSet = Helper::getOption('fileman', 'different_set', 'Y')=='Y';
		if ($bDifferentSet && $SiteID!==false) {
			$arResult['SITE'] = $SiteID;
		}
		return $arResult;
	}
	
	/**
	 *	Замена полей в форме
	 */
	function ShowControls($PropertyCode, $PropertyID, $PropertyValue, $SiteID) {
		global $DB;
		$arFilter = static::GetFilter($PropertyCode, $SiteID);
		$resCurrentProp = static::GetList(false,$arFilter);
		if ($arCurrentItem = $resCurrentProp->GetNext()) {
			$PropertyType = trim($arCurrentItem['TYPE']);
			$arTypes = static::GetTypes();
			if ($PropertyType!==false && is_array($arTypes[$PropertyType])) {
				$ClassName = $arTypes[$PropertyType]['CLASS'];
				if (class_exists($ClassName) && method_exists($ClassName, 'ShowControls')) {
					return $ClassName::ShowControls($arCurrentItem, $PropertyCode, $PropertyID, $PropertyValue, $SiteID);
				}
			}
		}
		return false;
		
	}

}
