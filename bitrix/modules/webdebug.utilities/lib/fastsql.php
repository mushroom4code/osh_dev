<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class FastSql
 * @package WD\Utilities
 */
class FastSql {
	const TableName = 'wdu_fastsql';
	
	/**
	 *	Add
	 */
	function Add($arFields) {
		global $DB, $USER;
		$arFields = static::RemoveWrongFields($arFields);
		$ID = $DB->Add(static::TableName, $arFields, array(), '', false);
		if ($ID>0) {
			return $ID;
		}
		return false;
	}
	
	// Update
	function Update($ID, $arFields) {
		global $DB;
		$arFields = static::RemoveWrongFields($arFields);
		$arSQL = array();
		foreach ($arFields as $Key => $Field) {
			$Key = $DB->ForSQL($Key);
			$Field = $DB->ForSQL($Field);
			$arSQL[] = "`{$Key}`='{$Field}'";
		}
		$strSQL = implode(',',$arSQL);
		$TableName = static::TableName;
		$SQL = "UPDATE `{$TableName}` SET {$strSQL} WHERE `ID`='{$ID}' LIMIT 1;";
		if ($DB->Query($SQL, false)) {
			return true;
		}
		return false;
	}
	
	/**
	 *	Delete
	 */
	function Delete($ID) {
		global $DB;
		$TableName = static::TableName;
		$SQL = "DELETE FROM `{$TableName}` WHERE `ID`='{$ID}';";
		if ($DB->Query($SQL, false)) {
			return true;
		}
		return false;
	}
	
	/**
	 *	Get list
	 */
	function GetList($arSort=false, $arFilter=false, $arGroupBy=false, $arNavParams=false) {
		global $DB;
		$arSort = static::RemoveWrongFields($arSort);
		$arFilter = static::RemoveWrongFields($arFilter);
		$TableName = static::TableName;
		$SQL = "SELECT * FROM `{$TableName}`";
		$arWhere = array();
		// WHERE
		if (is_array($arFilter) && !empty($arFilter)) {
			foreach ($arFilter as $Key => $FilterItem) {
				$Key = trim($Key," \r\n\t");
				$FilterKey = trim($Key,"<>=%!");
				$Operation = substr($Key,0,strlen($Key)-strlen($FilterKey));
				$FilterKey = $DB->ForSQL($FilterKey);
				switch($Operation) {
					case '>=':
					case '<=':
					case '<':
					case '>':
						$FilterItem = $DB->ForSQL($FilterItem);
						$arWhere[] = "`{$FilterKey}` {$Operation} '{$FilterItem}'";
						break;
						break;
					case '%':
						if (is_array($FilterItem)) {
							$arSubWhere = array();
							foreach($FilterItem as $Value) {
								$Value = $DB->ForSQL($Value);
								$arSubWhere[] = "(UPPER(`{$FilterKey}`) LIKE UPPER ('%{$Value}%') AND `{$FilterKey}` IS NOT NULL)";
							}
							$strSubWhere = implode(' OR ', $arSubWhere);
							$arWhere[] = "({$strSubWhere})";
						} else {
							$FilterItem = $DB->ForSQL($FilterItem);
							$arWhere[] = "(UPPER(`{$FilterKey}`) LIKE UPPER ('%{$FilterItem}%') AND `{$FilterKey}` IS NOT NULL)";
						}
						break;
					case '<>':
					case '!':
						if ($FilterItem===false || $FilterItem===null) {
							$arWhere[] = "`{$FilterKey}` is not null";
						} elseif(is_array($FilterItem)) {
							$arSubWhere = array();
							foreach($FilterItem as $Value) {
								$Value = $DB->ForSQL($Value);
								$arSubWhere[] = "`{$FilterKey}` {$Operation} '{$Value}'";
							}
							$strSubWhere = implode(' OR ', $arSubWhere);
							$arWhere[] = "({$strSubWhere})";
						}  else {
							$FilterItem = $DB->ForSQL($FilterItem);
							$arWhere[] = "`{$FilterKey}` <> '{$FilterItem}'";
						}
						break;
					case '=':
					default:
						if (is_array($FilterItem)) {
							$arSubWhere = array();
							foreach($FilterItem as $Value) {
								$Value = $DB->ForSQL($Value);
								$arSubWhere[] = "`{$FilterKey}` = '{$Value}'";
							}
							$strSubWhere = implode(' OR ', $arSubWhere);
							$arWhere[] = "({$strSubWhere})";
						} else {
							$FilterItem = $DB->ForSQL($FilterItem);
							$arWhere[] = "`{$FilterKey}` = '{$FilterItem}'";
						}
						break;
				}
			}
			if (count($arWhere)>0) {
				$SQL .= " WHERE ".implode(" AND ", $arWhere);
			}
		}
		// SORT
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
		
		$SQL_Count = "SELECT COUNT(`ID`) as `CNT` FROM `{$TableName}`";
		if (count($arWhere)>0) {
			$SQL_Count .= " WHERE ".implode(" AND ", $arWhere);
		}
		if (!is_array($arNavParams)) {
			$arNavParams = array();
		}
		$resCount = $DB->Query($SQL_Count);
		$arCount = $resCount->GetNext(false,false);
		$res = new \CDBResult();
		$res->NavQuery($SQL, $arCount['CNT'], $arNavParams, false);
		return $res;

	}
	
	/**
	 *	Get by ID
	 */
	function GetByID($ID) {
		return static::GetList(false,array('ID'=>$ID));
	}
	
	/**
	 *	Get fields in table
	 */
	function GetTableFields() {
		global $DB;
		$arResult = array();
		$Table = static::TableName;
		$SQL = "SHOW COLUMNS FROM `{$Table}`";
		$resColumns = $DB->Query($SQL);
		while ($arColumn = $resColumns->GetNext(false,false)) {
			$arResult[] = $arColumn['Field'];
		}
		return $arResult;
	}
	
	/**
	 *	Remove not existance fields
	 */
	function RemoveWrongFields($arFields) {
		$arResult = array();
		if (!is_array($arFields)) {
			$arFields = array();
		}
		$arExistsFields = static::GetTableFields();
		foreach($arFields as $Key => $Value) {
			$KeyName = trim($Key,"\r\n\t<>=%!");
			if (in_array($KeyName,$arExistsFields)) {
				$arResult[$Key] = $Value;
			}
		}
		return $arResult;
	}
	
	function OnAdminTabControlBegin_Handler(&$obTabControl) {
		global $USER, $DB;
		\CJSCore::Init('jquery');
		$jQuerySrc = \CJSCore::getExtInfo('jquery');
		$jQuerySrc = $jQuerySrc['js'];
		$UseCodeEditor = Helper::getOption('fileman','use_code_editor')!='N';
		$AutoExec = Helper::getOption(WDU_MODULE,'fastsql_auto_exec');
		$TableName = static::TableName;
		$UserID = $USER->GetID();
		$SQL = "SELECT `QUERY` FROM `{$TableName}` WHERE (`ACTIVE`='Y') AND (`USER_ID`='{$UserID}' OR `USER_ID`='') ORDER BY SORT ASC, ID ASC;";
		$arQueries = array();
		$resQueries = $DB->Query($SQL);
		while ($arQuery = $resQueries->GetNext()) {
			$arQueries[] = $arQuery['~QUERY'];
		}
		if (empty($arQueries)) {
			return false;
		}
		?>
			<script type="text/javascript" src="<?=$jQuerySrc;?>"></script>
			<div id="wd_sql_b_event">
				<ul>
					<?foreach($arQueries as $arQuery):?>
						<li><a href="#sql"><?=$arQuery;?></a></li>
					<?endforeach?>
				</ul>
			</div>
			<script>
			$(document).ready(function(){
				$('#wd_sql_b_event').prependTo($('.bxce,#sql').eq(0).parent()).find('a').click(function(Event){
					var SyntaxSwitcher = $('.bxce-mode-link');
					var bUseSyntaxHighlight = false;
					Event.preventDefault();
					var Text = $(this).text();
					<?if($UseCodeEditor):?>
						if (SyntaxSwitcher.hasClass('bxce-mode-link-on')) {
							bUseSyntaxHighlight = true;
							SyntaxSwitcher.click();
						}
						$('.bxce .bxce-lines-cnt > div').filter(function(){
							return !$(this).attr('class');
						}).html('<div>'+Text+'</div>');
					<?endif?>
					$('#sql').val(Text);
					if (bUseSyntaxHighlight) {
						SyntaxSwitcher.click();
					}
					<?if($AutoExec=='Y'):?>
						__FSQLSubmit();
					<?elseif($AutoExec=='X'):?>
						$('#query').val($('#sql').val());
						$('html,body').animate({
							scrollTop:$('#form_tbl_sql').offset().top
						},400);
						$('#form_tbl_sql').attr('action','/bitrix/admin/sql.php?mode=frame&lang=<?=LANGUAGE_ID;?>&del_query=Y').submit();
					<?endif?>
				});
			});
			</script>
		<?
	}
	
}

?>