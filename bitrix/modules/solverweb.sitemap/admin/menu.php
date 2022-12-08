<?php
IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight("solverweb.sitemap") > "D") {
	return $moduleMenu[] = array(
		"parent_menu"	=> "global_menu_services",
		"sort"			=> 10001,
		"text"			=> GetMessage('SW_SITEMAP_MENU_ITEM'),
		"title"			=> GetMessage('SW_SITEMAP_MENU_TITLE'),
		"url"			=> "solverweb_sitemap_list.php?lang=" . LANGUAGE_ID,
		"more_url"		=> array("solverweb_sitemap_edit.php"),
		"icon"			=> "solverweb_sitemap_menu_icon",
		"page_icon"		=> "solverweb_sitemap_page_icon",
	);
}
return false;