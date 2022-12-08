<?
namespace WD\Antirutin;

use \WD\Antirutin\Helper;

Helper::loadMessages(__FILE__);
$strLang = 'WDA_OPTIONS_';
$strHint = $strLang.'HINT_';

return [
	'NAME' => Helper::getMessage($strLang.'OPTION_MANUAL'),
	'OPTIONS' => [
		'show_results' => [
			'NAME' => Helper::getMessage($strLang.'OPTION_SHOW_RESULTS'),
			'HINT' => Helper::getMessage($strHint.'OPTION_SHOW_RESULTS'),
			'TYPE' => 'select',
			'VALUES' => \WD\Antirutin\Worker::getOptionsShowResults(),
		],
		'step_time' => [
			'NAME' => Helper::getMessage($strLang.'OPTION_STEP_TIME'),
			'HINT' => Helper::getMessage($strHint.'OPTION_STEP_TIME'),
			'TYPE' => 'text',
			'ATTR' => 'size="5" maxlength="5"',
		],
	],
];
?>