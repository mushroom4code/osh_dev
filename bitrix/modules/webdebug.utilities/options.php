<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper,
	\WD\Utilities\Options;

# Include module
$strModuleId = pathinfo(__DIR__, PATHINFO_BASENAME);
\Bitrix\Main\Loader::includeModule($strModuleId);
Helper::loadMessages();

# Check rights
$strRight = $APPLICATION->getGroupRight($strModuleId);
if($strRight < 'R'){
	return;
}

# Tabs
$arTabs = [
	[
		'DIV' => 'general',
		'OPTIONS' => [
			'general/general.php',
			'general/interface.php',
			'general/iblock.php',
			'general/http_headers.php',
		],
	], [
		'DIV' => 'developers',
		'OPTIONS' => [
			'developers/general.php',
			'developers/fastsql.php',
		],
	], [
		'DIV' => 'rights',
		'RIGHTS' => true,
	],
];

# Display all
$obOptions = new Options($strModuleId, $arTabs, [
	'TAB_CONTROL_NAME' => 'wdu',
	'DISABLED' => $strRight <= 'R',
]);

?>