<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arStyles = array(
	"icons" => GetMessage("AG_STYLES_VALUE_ICON_NAME"),
	"counters" => GetMessage("AG_STYLES_VALUE_COUNTERS_NAME"),
	"iconsmenu" => GetMessage("AG_STYLES_VALUE_ICONMENU_NAME"),
	"small" => GetMessage("AG_STYLES_VALUE_SMALL_NAME"),
);

$arSocservises = array(
	"collections" => GetMessage("AG_SOC_VALUE_COLLECTIONS_NAME"),
	"vkontakte" => GetMessage("AG_SOC_VALUE_VKONTAKTE_NAME"),
	"facebook" => GetMessage("AG_SOC_VALUE_FACEBOOK_NAME"),
	"odnoklassniki" => GetMessage("AG_SOC_VALUE_ODNOKL_NAME"),
	"moimir" => GetMessage("AG_SOC_VALUE_MOIMIR_NAME"),
	"gplus" => GetMessage("AG_SOC_VALUE_GPLUS_NAME"),
	// "pinterest" => GetMessage("AG_SOC_VALUE_PIN_NAME"),
	"twitter" => GetMessage("AG_SOC_VALUE_TWITTER_NAME"),
	"blogger" => GetMessage("AG_SOC_VALUE_BLOGGER_NAME"),
	"delicious" => GetMessage("AG_SOC_VALUE_DELICIOUS_NAME"),
	"digg" => GetMessage("AG_SOC_VALUE_DIGG_NAME"),
	"reddit" => GetMessage("AG_SOC_VALUE_REDDIT_NAME"),
	"evernote" => GetMessage("AG_SOC_VALUE_EVERNOTE_NAME"),
	"linkedin" => GetMessage("AG_SOC_VALUE_LINKEDIN_NAME"),
	"lj" => GetMessage("AG_SOC_VALUE_LJ_NAME"),
	"pocket" => GetMessage("AG_SOC_VALUE_POCKET_NAME"),
	"qzone" => GetMessage("AG_SOC_VALUE_QZONE_NAME"),
	"renren" => GetMessage("AG_SOC_VALUE_RENREN_NAME"),
	"sinaWeibo" => GetMessage("AG_SOC_VALUE_SINA_NAME"),
	"surfingbird" => GetMessage("AG_SOC_VALUE_SURFINGBIRD_NAME"),
	"tencentWeibo" => GetMessage("AG_SOC_VALUE_TENCENT_NAME"),
	"tumblr" => GetMessage("AG_SOC_VALUE_TUMBLUR_NAME"),
	"viber" => GetMessage("AG_SOC_VALUE_VIBER_NAME"),
	"whatsapp" => GetMessage("AG_SOC_VALUE_WATSAPP_NAME"),
	"skype" => GetMessage("AG_SOC_VALUE_SKYPE_NAME"),
	"telegram" => GetMessage("AG_SOC_VALUE_TELEGRAM_NAME"),
);

$arAlign = array(
	"ar_al_left" => GetMessage("AG_ALIGN_LEFT_NAME"),
	"ar_al_center" => GetMessage("AG_ALIGN_CENTER_NAME"),
	"ar_al_right" => GetMessage("AG_ALIGN_RIGHT_NAME"),
);

$arComponentParameters = array(
	"GROUPS" => array(
		"MAIN_VALUES" => array(
			"NAME" => GetMessage("AG_MAIN_VALUES_NAME"),
		),
		"DOP_VALUES" => array(
			"NAME" => GetMessage("AG_DOP_VALUES_NAME"),
		),
	),
	"PARAMETERS" => array(
		"VISUAL_STYLE"=>array(
			"PARENT" => "MAIN_VALUES",
			"NAME" => GetMessage("AG_VISUAL_STYLE_NAME"),
			"TYPE" => "LIST",
			"VALUES" => $arStyles,
			"MULTIPLE" => "Т",
			"REFRESH" => "Y",
			"DEFAULT" => array(),	
		),
		"SERVISE_LIST"=>array(
			"PARENT" => "MAIN_VALUES",
			"NAME" => GetMessage("AG_SERVISE_LIST_NAME"),
			"TYPE" => "LIST",
			"VALUES" => $arSocservises,
			"MULTIPLE" => "Y",
			"DEFAULT" => array_keys($arSocservises),	
		),
		"TEXT_ALIGN"=>array(
			"PARENT" => "MAIN_VALUES",
			"NAME" => GetMessage("AG_TEXT_ALIGN_NAME"),
			"TYPE" => "LIST",
			"VALUES" => $arAlign,
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		"TEXT_BEFORE"=>array(
			"PARENT" => "MAIN_VALUES",
			"NAME" => GetMessage("AG_TEXT_BEFORE_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		"OLD_BROWSERS"=>array(
			"PARENT" => "DOP_VALUES",
			"NAME" => GetMessage("AG_DATA_OLD_BROWSERS_NAME"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		"DATA_TITLE"=>array(
			"PARENT" => "DOP_VALUES",
			"NAME" => GetMessage("AG_DATA_TITLE_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		"DATA_RESCRIPTION"=>array(
			"PARENT" => "DOP_VALUES",
			"NAME" => GetMessage("AG_DATA_RESCRIPTION_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		"DATA_IMAGE"=>array(
			"PARENT" => "DOP_VALUES",
			"NAME" => GetMessage("AG_DATA_IMAGE_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		"DATA_URL"=>array(
			"PARENT" => "DOP_VALUES",
			"NAME" => GetMessage("AG_DATA_URL_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
		),
		// "PINTEREST_PICT" => Array(
			// "NAME" => GetMessage("AG_SERVISE_PINT_PIC_NAME"),
			// "TYPE" => "STRING",
			// "MULTIPLE" => "N",
			// "DEFAULT" => "",
			// "COLS" => 25,
			// "PARENT" => "MAIN_VALUES",
		// ),
	),
);
// echo '<pre>'; print_r($arCurrentValues["VISUAL_STYLE"]); echo '</pre>';
if($arCurrentValues["VISUAL_STYLE"] == 'iconsmenu')
{
	$arComponentParameters["PARAMETERS"]["COUNT_FOR_SMALL"] = Array(
		"NAME" => GetMessage("AG_SERVISE_COUNT_FOR_SMALL_NAME"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "3",
		"COLS" => 25,
		"PARENT" => "MAIN_VALUES",
	);
}
?>