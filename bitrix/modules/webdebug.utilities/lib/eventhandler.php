<?
namespace WD\Utilities;

use
	\WD\Utilities\BxHelper,
	\WD\Utilities\Helper,
	\WD\Utilities\FastSql,
	\WD\Utilities\HttpHeader,
	\WD\Utilities\JsHelper,
	\WD\Utilities\IBlockHelper,
	\WD\Utilities\PageProp;

Helper::loadMessages();

/**
 * Class EventHandler
 * @package WD\Utilities
 */
class EventHandler {
	
	public static function onPageStart(){
		HttpHeader::processHeaders();
		if(Helper::getOption(WDU_MODULE,'js_debug_functions')=='Y') {
			JsHelper::addJsDebugFunctions();
		}
		if(Helper::getOption(WDU_MODULE,'global_main_functions')=='Y') {
			require __DIR__.'/../include/functions/global_functions.php';
		}
	}
	
	public static function onBeforeProlog(){
		
	}
	
	public static function onProlog(){
		if(defined('ADMIN_SECTION') && ADMIN_SECTION===true || $GLOBALS['USER']->isAdmin()) {
			if(Helper::getUserOption(WDU_MODULE, 'prevent_logout') == 'Y') {
				JsHelper::addJsPreventLogout();
			}
			if(!(defined('ADMIN_SECTION') && ADMIN_SECTION===true)) {
				$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/themes/.default/webdebug.utilities.css');
			}
			if(!defined('SITE_TEMPLATE_ID')){
				define('SITE_TEMPLATE_ID', '');
			}
			if($GLOBALS['APPLICATION']->getCurPage() == '/bitrix/admin/public_menu_edit.php'){
				print '<style type="text/css">
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item table {
						border-collapse:collapse!important;
						table-layout:fixed!important;
					}
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item table > tbody > tr > td {
						border:0!important;
						padding:4px 4px!important;
						vertical-align:middle!important;
						width:16px!important;
					}
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item table > tbody > tr > td > span.rowcontrol {
						margin:6px!important;
					}
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item table > tbody > tr > td:nth-child(2),
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item table > tbody > tr > td:nth-child(3) {
						width:30%!important;
					}
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item table > tbody > tr > td > div.edit-area > input {
						width:100%!important;
						height:31px!important;
						line-height:31px!important;
						box-sizing:border-box!important;
					}
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item div.edit-field.view-area {
						background-position:right 4px center!important;
						border:1px solid #fff!important;
						width:100%!important;
						box-sizing:border-box!important;
						margin:0!important;
					}
					#bx_menu_layout > div.bx-menu-placement > .bx-edit-menu-item div.edit-field.view-area:hover {
						border-color:gray!important;
					}
					</style>';
			}
			\CAjax::Init();
			GotoObject::addJs();
		}
	}
	
	public static function onEpilog(){
		global $APPLICATION;
		if(defined('ADMIN_SECTION') && ADMIN_SECTION===true) {
			if(Helper::getOption(WDU_MODULE, 'set_admin_favicon') == 'Y') {
				$AdminFavIcon = Helper::getOption(WDU_MODULE, 'admin_favicon');
				$APPLICATION->addHeadString('<link rel="icon" href="'.$AdminFavIcon.'" type="image/x-icon" />');
				$APPLICATION->addHeadString('<link rel="shortcut icon" href="'.$AdminFavIcon.'" type="image/x-icon" />');
			}
			#
			if(Helper::getOption(WDU_MODULE, 'use_select2_for_modules') == 'Y') {
				if($GLOBALS['APPLICATION']->getCurPage() == '/bitrix/admin/settings.php'){
					JsHelper::applySelect2toModules();
				}
			}
		}
		#
		if(Helper::getOption(WDU_MODULE, 'editor_show_template_path') == 'Y'){
			if($GLOBALS['APPLICATION']->getCurPage()=='/bitrix/admin/public_file_edit_src.php' && !empty($_GET['path'])) {
				if($_GET['new'] != 'Y'){
					$strPath = pathinfo($_GET['path'], PATHINFO_DIRNAME);
					?>
					<script>
					(function(){
						let
							popup = BX.WindowManager.Get(),
							head = popup.PARTS.HEAD,
							link = head.childNodes[0],
							div = document.getElementById('wdu_template_path');
						div.appendChild(link);
						link.title = link.text;
						head.appendChild(div);
						head.style.padding = '0';
						head.style.borderBottom = 'none';
					})();
					</script>
					<div style="display:none;">
						<style>
							#wdu_template_path {margin-right:10px; padding-right:26px; position:relative;}
							#wdu_template_path input {box-sizing:border-box; font:normal 12px "Courier New", "Courier", monospace; width:100%;}
							#wdu_template_path a {background:url("/bitrix/panel/main/images/popup_menu_sprite_2.png") no-repeat -10px -637px; font-size:0; height:16px; margin-top:-8px; outline:0; position:absolute; right:0; text-indent:-10000px; top:50%; width:16px;}
						</style>
						<div id="wdu_template_path">
							<input type="text" value="<?=htmlspecialcharsbx($strPath);?>" spellcheck="false" />
						</div>
					</div>
					<?
				}
			}
		}
	}
	
	public static function onAfterEpilog(){
		
	}
	
	public static function onBeforeEndBufferContent(){
		
	}
	
	public static function onEndBufferContent(&$strContent){
		if(in_array($GLOBALS['APPLICATION']->getCurPage(), array('/bitrix/admin/public_file_property.php', '/bitrix/admin/public_folder_edit.php'))) {
			if(Helper::getOption(WDU_MODULE, 'pageprops_enabled') == 'Y') {
				PageProp::OnEndBufferContent_Handler($strContent);
			}
		}
		#
		if(Helper::getOption(WDU_MODULE, 'php_no_confirm')=='Y'){
			if($GLOBALS['APPLICATION']->getCurPage() == '/bitrix/admin/php_command_line.php'){
				$strContent = preg_replace('#function\s?__FPHPSubmit\(\)\s*{\s*if\(confirm\(#', 'function __FPHPSubmit()'."\n".'{'."\n"."\t".'if(true||confirm(', $strContent);
				$strContent = str_replace('window.scrollTo(0, 500);', '', $strContent);
			}
		}
		if(Helper::getOption(WDU_MODULE, 'sql_no_confirm')=='Y'){
			if($GLOBALS['APPLICATION']->getCurPage() == '/bitrix/admin/sql.php'){
				$strContent = preg_replace('#function\s?__FSQLSubmit\(\)\s*{\s*if\(confirm\(#', 'function __FSQLSubmit()'."\n".'{'."\n"."\t".'if(true||confirm(', $strContent);
				$strContent = str_replace('window.scrollTo(0, 500);', '', $strContent);
			}
		}
	}
	
	public static function onAdminContextMenuShow(&$arMenuItems){
		IBlockHelper::addContextDetailLink($arMenuItems, Helper::getOption(WDU_MODULE, 'iblock_add_detail_link'));
	}
	
	public static function onAdminListDisplay(&$obList){
		
	}
	
	public static function onAdminTabControlBegin(&$obTabControl){
		if($GLOBALS['APPLICATION']->getCurPage() == '/bitrix/admin/sql.php') {
			$FastSQL_Enabled = Helper::getOption(WDU_MODULE, 'fastsql_enabled')=='Y';
			if($FastSQL_Enabled) {
				FastSql::OnAdminTabControlBegin_Handler($obTabControl);
			}
		}
		if(Helper::getOption(WDU_MODULE, 'iblock_show_element_id')=='Y') {
			IBlockHelper::displayElementIdInTabFoot($obTabControl);
		}
	}
	
	public static function onBuildGlobalMenu(&$arGlobalMenu, &$arMenuItems){
		\WD\Utilities\MenuManager::onBuildGlobalMenu($arGlobalMenu, $arMenuItems);
	}

	public static function onBeforeLocalRedirect(&$url, $skip_security_check, &$bExternal){
		
	}

	public static function onLocalRedirect(){
		
	}

	public static function onModuleUpdate($arModules){
		
	}

	public static function onFileSave(&$arFile, $strFileName, $strSavePath, $bForceMD5, $bSkipExt, $dirAdd){
		
	}

	public static function onAfterFileSave($arFields){
		
	}

	public static function onGetFileSrc($arFile){
		
	}

	public static function onFileDelete($res){
		
	}

	public static function onBeforeEventAdd(&$event, &$lid, &$arFields, &$message_id, &$files, &$languageId){
		
	}

	public static function onBeforeEventSend(&$arFields, &$eventMessage, $context){
		
	}

	public static function onBeforeMailSend(&$mailParams){
		
	}

	public static function onPanelCreate(){
		BxHelper::addPanelButtons();
	}
	
}
