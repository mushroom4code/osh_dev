<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class JsHelper
 * @package WD\Utilities
 */
class JsHelper {
	
	public static function addJsDebugFunctions() {
		global $APPLICATION;
		$strFilename = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.WDU_MODULE.'/debug_functions.js';
		if(is_file($strFilename)) {
			$strJs = file_get_contents($strFilename);
			$GLOBALS['APPLICATION']->addHeadString('<script>'.$strJs.'</script>');
		}
	}
	
	public static function addJsPreventLogout(){
		$strFilename = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.WDU_MODULE.'/prevent_logout.js';
		if(is_file($strFilename)) {
			$strJs = file_get_contents($strFilename);
			$strJs = 'BX.message({"WD_UTILITIES_LOGOUT_CONFIRM":"'.Helper::getMessage('WDU_LOGOUT_CONFIRM').'"});'.$strJs;
			$GLOBALS['APPLICATION']->addHeadString('<script>'.$strJs.'</script>');
		}
	}
	
	public static function applySelect2toModules(){
		\CJSCore::init('jquery');
		Helper::addJsSelect2();
		ob_start();
		?>
		<script>
		$(document).ready(function(){
			wduSelect2($('form > select[name="mid"]'));
		});
		</script>
		<?
		$GLOBALS['APPLICATION']->addHeadString(ob_get_clean());
	}
	
}
