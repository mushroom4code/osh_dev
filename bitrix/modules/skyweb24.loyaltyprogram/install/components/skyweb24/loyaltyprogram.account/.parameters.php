<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = [
	"PARAMETERS" => [
		"CACHE_TIME"  => [],
		'CHAIN_BONUSES' => [
			"NAME" => GetMessage("SW24_LOYALTYPROGRAM_CHAIN_BONUSES"),
		],
		'TITLE_BONUSES' => [
			"NAME" => GetMessage("SW24_LOYALTYPROGRAM_TITLE_BONUSES"),
		],
		"DISPLAY_PAGER" => [
			"NAME" => GetMessage("SW24_LOYALTYPROGRAM__DISPLAY_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"PAGER_TEMPLATE" => [
			"PARENT" => "DETAIL_PAGER_SETTINGS",
			"NAME" => GetMessage("SW24_LOYALTYPROGRAM_PAGER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
		"PAGER_NAME" => [
			"NAME" => GetMessage("SW24_LOYALTYPROGRAM_PAGER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
		"PAGER_COUNT" => [
			"NAME" => GetMessage("SW24_LOYALTYPROGRAM_PAGER_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		],
	]
];
?>
