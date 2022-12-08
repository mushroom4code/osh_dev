<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class BxHelper
 * @package WD\Utilities
 */
class BxHelper {
	
	/**
	 *	See https://www.webdebug.ru/blog/sbor-faylov-obnovleniya-moduley/
	 */
	public static function createUpdateArchive($strUpdaterDir){
		if(is_file($strUpdaterDir.'/install/version.php')){
			require $strUpdaterDir.'/install/version.php';
			$strModuleId = end(explode('/',$strUpdaterDir));
			$strDir = static::getUploadDir('updates/'.$strModuleId, true);
			if(!is_dir($strDir)){
				mkdir($strDir, BX_DIR_PERMISSIONS, true);
			}
			$strArcFileName = $strDir.'/'.$arModuleVersion['VERSION'].'.tar.gz';
			@unlink($strArcFileName);
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/tar_gz.php');
			$obArc = CBXArchive::GetArchive($strArcFileName, 'TAR.GZ');
			if($obArc instanceof IBXArchive) {
				$obArc->SetOptions([
					'COMPRESS' => true, 
					'ADD_PATH' => false, 
					'REMOVE_PATH' => $strUpdaterDir, 
					'CHECK_PERMISSIONS' => false 
				]);
				$arPackFiles = [$strUpdaterDir];
				$obArc->pack($arPackFiles, '');
			}
			unset($obArc);
		}
	}
	
	/**
	 *	Determine site by domain
	 */
	public static function getSiteByDomain($strDomain=null){
		static $arCurrentSiteId;
		if(!is_array($arCurrentSiteId)){
			$arCurrentSiteId = [];
		}
		if(!is_string($strDomain)){
			$strDomain = Helper::getCurrentDomain();
		}
		if(!isset($arCurrentSiteId[$strDomain])){
			$arSites = Helper::getSitesList($bActiveOnly=true);
			$strFoundSiteId = null;
			$arDefaultSite = null;
			foreach($arSites as $strSiteId => $arSite){
				if(is_null($arDefaultSite) || $arSite['DEF'] == 'Y'){
					$arDefaultSite = $arSite;
				}
				$arDomains = Helper::splitSpaceValues(toLower($arSite['DOMAINS']));
				foreach($arDomains as $strSiteDomain){
					if($strSiteDomain == $strDomain){ // domain
						$strFoundSiteId = $strSiteId;
						break 2;
					}
					elseif(substr($strDomain, -1 * strlen($strSiteDomain) - 1) == '.'.$strSiteDomain){// subdomain
						$strFoundSiteId = $strSiteId;
						break 2;
					}
				}
			}
			if(is_null($strFoundSiteId) && $arDefaultSite){
				$strFoundSiteId = $arDefaultSite['ID'];
			}
			if($strFoundSiteId){
				$arCurrentSiteId[$strDomain] = $strFoundSiteId;
			}
		}
		return $arCurrentSiteId[$strDomain];
	}
	
	/**
	 *	
	 */
	public static function addPanelButtons(){
		global $APPLICATION, $USER;
		if($APPLICATION->getGroupRight(WDU_MODULE) >= 'R' && $USER && $USER->getLogin() == 'webdebug') {
			# Main panel button
			$APPLICATION->addHeadString('<style>
			#bx-panel .icon-panel-wdu {
				background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAAAGXRFWHRTb2Z0d2FyZQ'
					.'BBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD'
					.'0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9Ik'
					.'Fkb2JlIFhNUCBDb3JlIDUuMC1jMDYxIDY0LjE0MDk0OSwgMjAxMC8xMi8wNy0xMDo1NzowMSAgICAgICAgIj4gPHJkZjpSREYgeG'
					.'1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZG'
					.'Y6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi'
					.'8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veG'
					.'FwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0ODZGMDlCMjJEQ0RFQTExQTgxNkFEQ0NBQjk1NUExNi'
					.'IgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpCNTBENTg4N0NEMkQxMUVBQUJDRUY4NDA5RUY2RDU5NiIgeG1wTU06SW5zdGFuY2'
					.'VJRD0ieG1wLmlpZDpCNTBENTg4NkNEMkQxMUVBQUJDRUY4NDA5RUY2RDU5NiIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3'
					.'Nob3AgQ1M1LjEgV2luZG93cyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjQ5NkYwOUIyMk'
					.'RDREVBMTFBODE2QURDQ0FCOTU1QTE2IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjQ4NkYwOUIyMkRDREVBMTFBODE2QURDQ0'
					.'FCOTU1QTE2Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+QY'
					.'aONwAAALxJREFUeNpiZGBg4ABiASgNAz+A+AOUJhqwgAw6cfHKc3QJC30dSSD1ghTDmNBchAw4GEgETAxUBFQ3DFcg/yDVMEZoTC'
					.'pAaRj4gBT4RMcyyDAJHLFpCBQ/T0os44tNAVJjeWjG5gdSY5kRT94EAQm0sPsIxC9FxcSYXr96xYIey4x4XI01lssL8907+yfuxB'
					.'bL+MIMa6x9/vSJD5f6wR2bDKTEGicnJ85YZiQQZuix/BNqCUiMHT02AQIMAFoSNTNznyDSAAAAAElFTkSuQmCC") 
						center center no-repeat !important;
			}
			</style>');
			$APPLICATION->addPanelButton([
				'HREF' => 'javascript:'.$APPLICATION->getPopupLink(
					[
						'URL' => sprintf('/bitrix/admin/wdu_dashboard.php?lang=%s&site=%s&public=Y&bxpublic=Y&str_URI=%s', 
							LANGUAGE_ID, SITE_ID, urlencode($APPLICATION->getCurPageParam('', ['clear_cache', 'sessid', 'login', 
							'logout', 'register', 'forgot_password', 'change_password', 'confirm_registration', 'confirm_code', 
							'confirm_user_id', 'bitrix_include_areas', 'show_page_exec_time', 'show_include_exec_time', 
							'show_sql_stat', 'show_link_stat']))),
						'PARAMS' => [
							'width' => 800,
							'height' => 500,
							'resizable' => true,
						]
					]
				),
				'ICON' => 'icon-panel-wdu',
				'TEXT' => Helper::getMessage('WDU_PANEL_BUTTON_TEXT'),
				'MAIN_SORT' => '1000',
				'SORT' => '1000',
				'RESORT_MENU' => true,
				'HINT' => [
					'TITLE' => Helper::getMessage('WDU_PANEL_BUTTON_HINT_TITLE'),
					'TEXT' => Helper::getMessage('WDU_PANEL_BUTTON_HINT_TEXT'),
				],
			]);
		}
	}
	
}
