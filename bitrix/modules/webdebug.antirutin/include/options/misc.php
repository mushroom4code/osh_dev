<?
namespace WD\Antirutin;

use \WD\Antirutin\Helper;

Helper::loadMessages(__FILE__);
$strLang = 'WDA_OPTIONS_';
$strHint = $strLang.'HINT_';

return [
	'NAME' => Helper::getMessage($strLang.'OPTION_MISC'),
	'OPTIONS' => [
		'disable_old_module' => [
			'NAME' => Helper::getMessage($strLang.'OPTION_DISABLE_OLD_MODULE'),
			'HINT' => Helper::getMessage($strHint.'OPTION_DISABLE_OLD_MODULE'),
			'TYPE' => 'checkbox',
		],
		'show_carefully_notice' => [
			'NAME' => Helper::getMessage($strLang.'OPTION_SHOW_CAREFULLY_NOTICE'),
			'HINT' => Helper::getMessage($strHint.'OPTION_SHOW_CAREFULLY_NOTICE'),
			'TYPE' => 'checkbox',
		],
		'pin_plugins_to_end' => [
			'NAME' => Helper::getMessage($strLang.'OPTION_PIN_PLUGINS_TO_END'),
			'HINT' => Helper::getMessage($strHint.'OPTION_PIN_PLUGINS_TO_END'),
			'TYPE' => 'checkbox',
		],
	],
];
?>