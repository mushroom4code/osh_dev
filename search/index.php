<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("Поиск");

$APPLICATION->IncludeComponent("bitrix:search.page", "oshisha_searсh.page", array(
	"RESTART" => "N",
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "Y",
	"DEFAULT_SORT" => "rank",
	"arrFILTER" => array(
		0 => "main",
		1 => "iblock_services",
		2 => "iblock_news",
		3 => "iblock_catalog",
	),
	"arrFILTER_main" => array(
	),
	"arrFILTER_iblock_services" => array(
		0 => "all",
	),
	"arrFILTER_iblock_news" => array(
		0 => "all",
	),
	"arrFILTER_iblock_catalog" => array(
		0 => "all",
	),
	"SHOW_WHERE" => "Y",
	"SHOW_WHEN" => "Y",
	"PAGE_RESULT_COUNT" => "25",
	"AJAX_MODE" => "Y",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "Y",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"DISPLAY_TOP_PAGER" => "Y",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "Результаты поиска",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "arrows",
	"USE_SUGGEST" => "N",
	"SHOW_ITEM_TAGS" => "Y",
	"SHOW_ITEM_DATE_CHANGE" => "N",
	"SHOW_ORDER_BY" => "Y",
	"SHOW_TAGS_CLOUD" => "N",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
