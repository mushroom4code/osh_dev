<?
namespace WD\Utilities;

use
	 \WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class MenuManager
 * @package WD\Utilities
 */
class MenuManager {
	
	protected static $bActive = true;
	
	/**
	 * @return string
	 */
	public static function onBuildGlobalMenu(&$arGlobalMenu, &$arMenuItems){
		if(static::$bActive){
			# Hide menus
			if(Helper::getOption(WDU_MODULE, 'hide_partners_menu') == 'Y'){
				$strExcludeMenu = Helper::getOption(WDU_MODULE, 'hide_partners_menu_exclude');
				$strExcludeMenu = trim($strExcludeMenu);
				if(strlen($strExcludeMenu)){
					$arExcludeMenu = Helper::splitCommaValues($strExcludeMenu);
					if(!empty($arExcludeMenu)){
						if(!in_array('settings', $arExcludeMenu)){
							$arExcludeMenu[] = 'settings';
						}
						$arCustomGlobalMenu = [];
						foreach($arGlobalMenu as $key => $arGlobalMenuItem){
							$intExcludeKey = array_search($arGlobalMenuItem['menu_id'], $arExcludeMenu);
							if(is_numeric($intExcludeKey)){
								$arGlobalMenu[$key]['sort'] = ($intExcludeKey + 1) * 100;
							}
							elseif($intExcludeKey === false){
								$arCustomGlobalMenu[$key] = $arGlobalMenuItem;
								unset($arGlobalMenu[$key]);
							}
						}
						#
						foreach($arMenuItems as $key => $arMenuItem){
							$strParentMenu = $arMenuItem['parent_menu'];
							unset($arMenuItem['parent_menu']);
							if(is_array($arCustomGlobalMenu[$strParentMenu])){
								$arCustomGlobalMenu[$strParentMenu]['items'][] = $arMenuItem;
								unset($arMenuItems[$key]);
							}
						}
						#
						foreach($arCustomGlobalMenu as $key => $arMenuItem){
							if(empty($arMenuItem['items'])){
								unset($arCustomGlobalMenu[$key]);
							}
						}
						if(!empty($arCustomGlobalMenu)){
							$arMenuItems[] = [
								'parent_menu' => 'global_menu_settings',
								'section' => 'wdu_custom_menus',
								'sort' => 1991,
								'text' => Helper::getMessage('WDU_MENUMANAGER_MENUITEM_NAME'),
								'icon' => 'wdu_icon_custom_menu',
								'items_id' => 'menu_wdu_custom_menus',
								'items' => $arCustomGlobalMenu,
							];
						}
					}
				}
			}
			# hide iblock for not-current site
			if(Helper::getOption(WDU_MODULE, 'iblock_just_this_site') == 'Y'){
				$strSiteId = BxHelper::getSiteByDomain();
				if(strlen($strSiteId)){
					$arIBlocksId = array_column(Helper::getIBlocks($bGroup=false, $bShowInactive=true, $strSiteId), 'ID');
					foreach($arMenuItems as $intKey1 => $arMenuItem){
						if($arMenuItem['module_id'] == 'iblock' && preg_match('#^menu_iblock_/#', $arMenuItem['items_id'])){
							if(is_array($arMenuItem['items']) && !empty($arMenuItem['items'])){
								foreach($arMenuItem['items'] as $intKey2 => $arSubMenuItem){
									if(preg_match('#^menu_iblock_/.*?/(\d+)$#', $arSubMenuItem['items_id'], $arMatch)){
										if(!in_array($arMatch[1], $arIBlocksId)){
											unset($arMenuItems[$intKey1]['items'][$intKey2]);
										}
									}
								}
							}
						}
					}
				}
			}
			# remove empty iblock types
			if(Helper::getOption(WDU_MODULE, 'iblock_hide_empty_types') == 'Y'){
				foreach($arMenuItems as $intKey1 => $arMenuItem){
					if($arMenuItem['module_id'] == 'iblock' && preg_match('#^menu_iblock_/#', $arMenuItem['items_id'])){
						if(!is_array($arMenuItem['items']) || empty($arMenuItem['items'])){
							unset($arMenuItems[$intKey1]);
						}
					}
				}
			}
			# bitrix.liveapi && bitrix.xscan icon
			foreach($arMenuItems as $key => $arMenuItem){
				if(in_array($arMenuItem['section'], ['bitrix.liveapi', 'bitrix.xscan'])){
					$arMenuItems[$key]['icon'] = 'wdu_bitrix_icon';
				}
			}
		}
	}

	/**
	 *	Stop handler 'onBuildGlobalMenu'
	 */
	public static function stopHandler(){
		static::$bActive = false;
	}

	/**
	 *	Start handler 'onBuildGlobalMenu'
	 */
	public static function startHandler(){
		static::$bActive = true;
	}

}
