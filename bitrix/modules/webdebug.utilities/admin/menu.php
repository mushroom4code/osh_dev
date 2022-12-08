<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

$strModuleId = 'webdebug.utilities';

if(\Bitrix\Main\Loader::includeModule($strModuleId) && $APPLICATION->getGroupRight($strModuleId)>='R') {
	
	Helper::loadMessages();

	$arSubmenu = [];
	
	// PropSorter
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_PROPSORTER'),
		'more_url' => [],
		'url' => 'wdu_propsorter.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_propsorter',
	];
	
	// Dir size
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_DIRSIZE'),
		'more_url' => [],
		'url' => 'wdu_dirsize.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_dirsize',
	];
	
	// Finder
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_FINDER'),
		'more_url' => [],
		'url' => 'wdu_finder.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_finder',
	];
	
	// Crontab
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_CRONTAB'),
		'more_url' => ['wdu_crontab_edit.php'],
		'url' => 'wdu_crontab_list.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_crontab',
	];
	
	// Page props
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_PAGEPROPS'),
		'more_url' => [],
		'url' => 'wdu_pageprops.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_pageprops',
	];
	
	// Fast SQL
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_FASTSQL'),
		'more_url' => ['wdu_fastsql_edit.php'],
		'url' => 'wdu_fastsql_list.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_fastsql',
	];
	
	// Options
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_OPTIONS'),
		'more_url' => ['wdu_option_edit.php'],
		'url' => 'wdu_option_list.php?lang='.LANGUAGE_ID,
		'icon' => 'wdu_icon_options',
	];
	
	// Settings
	$arSubmenu[] = [
		'text' => Helper::getMessage('WDU_MENU_SETTINGS'),
		'more_url' => [],
		'url' => '/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid='.$strModuleId,
		'icon' => 'sys_menu_icon',
	];

	$arMenu = [
		'parent_menu' => 'global_menu_settings',
		'section' => 'webdebug_utilities',
		'sort' => 1990,
		'text' => Helper::getMessage('WDU_MENU_MAIN'),
		'icon' => 'wdu_icon_main',
		'items_id' => 'wd_utils_submenu',
		'items' => $arSubmenu,
	];
	
	return $arMenu;
}

return false;
