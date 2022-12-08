<?
namespace WD\Antirutin;

use
	\WD\Antirutin\Helper;

$strModuleId = 'webdebug.antirutin';
\Bitrix\Main\Loader::includeModule($strModuleId);

if(class_exists('\WD\Antirutin\Helper') && $APPLICATION->getGroupRight($strModuleId) >= 'R') {
	Helper::loadMessages(__FILE__);
	$arMenu = [
		'parent_menu' => 'global_menu_content',
		'section' => 'webdebug_antirutin',
		'sort' => 990,
		'text' => Helper::getMessage('WD_ANTIRUTIN_MAIN'),
		'icon' => 'wda_icon_main',
		'items_id' => 'wd_antirutin',
		'items' => [
			[
				'text' => Helper::getMessage('WD_ANTIRUTIN_NEW_ELEMENT'),
				'url' => '/bitrix/admin/wda_new.php?lang='.LANGUAGE_ID,
				'more_url' => [],
			],
			[
				'text' => Helper::getMessage('WD_ANTIRUTIN_OLD'),
				'url' => '/bitrix/admin/wda.php?lang='.LANGUAGE_ID,
				'more_url' => ['/bitrix/admin/wda_profiles.php?lang='.LANGUAGE_ID],
			],
		],
	];
	if(Helper::getOption('disable_old_module') == 'Y'){
		$arNewModuleMenu = reset($arMenu['items']);
		$arMenu['url'] = $arNewModuleMenu['url'];
		$arMenu['more_url'] = $arNewModuleMenu['more_url'];
		unset($arMenu['items']);
	}
	return $arMenu;
}

?>