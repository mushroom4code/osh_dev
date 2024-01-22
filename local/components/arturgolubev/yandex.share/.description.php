<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("ARTURGOLUBEV_YANDEXSHARE_PANELQ_RASSARIVANIA"),
	"DESCRIPTION" => GetMessage("ARTURGOLUBEV_YANDEXSHARE_PANELQ_RASSARIVANIA"),
	"ICON" => "/images/icon.gif",
	"SORT" => 510,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "AG_DOP_SERVICES",
		"NAME" => GetMessage("ARTURGOLUBEV_YANDEXSHARE_MAIN_FOLDER"),
		"SORT" => 1930,
		"CHILD" => array(
			"ID" => "SOC_SERVICE",
			"NAME" => GetMessage("ARTURGOLUBEV_YANDEXSHARETITLE_FOLDER"),
			"SORT" => 150
		)
	),
	"COMPLEX" => "N",
);

?>