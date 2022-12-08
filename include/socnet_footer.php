<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent(
	"bitrix:eshop.socnet.links", 
	"bootstrap_v4", 
	array(
		"FACEBOOK" => "https://www.facebook.com/1CBitrix",
		"VKONTAKTE" => "https://vk.com/bitrix_1c",
		"TWITTER" => "https://twitter.com/1c_bitrix",
		"GOOGLE" => "https://plus.google.com/111119180387208976312/",
		"INSTAGRAM" => "https://instagram.com/1CBitrix/",
		"COMPONENT_TEMPLATE" => "bootstrap_v4"
	),
	false,
	array(
		"HIDE_ICONS" => "N"
	)
);?>