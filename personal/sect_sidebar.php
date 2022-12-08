<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="bx-sidebar-block">
	<?$APPLICATION->IncludeComponent(
	"bitrix:menu", 
	".default", 
	array(
		"ROOT_MENU_TYPE" => "personal",
		"MAX_LEVEL" => "3",
		"MENU_CACHE_TYPE" => "A",
		"CACHE_SELECTED_ITEMS" => "N",
		"MENU_CACHE_TIME" => "36000000",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_CACHE_GET_VARS" => array(
		),
		"COMPONENT_TEMPLATE" => ".default",
		"CHILD_MENU_TYPE" => "bottom",
		"USE_EXT" => "Y",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N",
		"MENU_THEME" => "site"
	),
	false
);?>
</div>
<div class="bx-sidebar-block">
	<?$APPLICATION->IncludeComponent(
		"bitrix:main.include",
		"",
		Array(
			"AREA_FILE_SHOW" => "file",
			"PATH" => SITE_DIR."include/about.php",
			"AREA_FILE_RECURSIVE" => "N",
			"EDIT_MODE" => "html",
		),
		false,
		Array('HIDE_ICONS' => 'N')
	);?>
</div>

