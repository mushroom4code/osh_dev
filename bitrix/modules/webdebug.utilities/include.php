<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

define('WDU_MODULE', 'webdebug.utilities');

$arAutoload = [
	'WD\Utilities\BxHelper' => 'lib/bxhelper.php',
	'WD\Utilities\Cli' => 'lib/cli.php',
	'WD\Utilities\DirSize' => 'lib/dirsize.php',
	'WD\Utilities\EventHandler' => 'lib/eventhandler.php',
	'WD\Utilities\FastSql' => 'lib/fastsql.php',
	'WD\Utilities\Finder' => 'lib/finder.php',
	'WD\Utilities\GotoObject' => 'lib/gotoobject.php',
	'WD\Utilities\Helper' => 'lib/helper.php',
	'WD\Utilities\HttpHeader' => 'lib/httpheader.php',
	'WD\Utilities\IBlockHelper' => 'lib/iblockhelper.php',
	'WD\Utilities\JsHelper' => 'lib/jshelper.php',
	'WD\Utilities\Json' => 'lib/json.php',
	'WD\Utilities\MenuManager' => 'lib/menumanager.php',
	'WD\Utilities\Option' => 'lib/option.php',
	'WD\Utilities\Options' => 'lib/options.php',
	'WD\Utilities\PageProp' => 'lib/pageprop.php',
	'WD\Utilities\PagePropBase' => 'lib/pagepropbase.php',
	'WD\Utilities\PropSorterTable' => 'lib/propsorter.php',
];
\Bitrix\Main\Loader::registerAutoLoadClasses(WDU_MODULE, $arAutoload);

Helper::loadMessages();

\CJSCore::registerExt('wdupopup', [
	'js' => '/bitrix/js/'.WDU_MODULE.'/wdu_popup.js', 
	'rel' => ['popup'],
]);
