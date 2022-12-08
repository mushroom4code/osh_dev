<?
/**
 * Class to work with JSON
 */

namespace WD\Antirutin;

use
	\WD\Antirutin\Helper;

class Json extends \Bitrix\Main\Web\Json {
	
	/**
	 *	Set http header for JSON file
	 */
	public static function setHttpHeader(){
		header('Content-Type: application/json; charset='.(Helper::isUtf()?'utf-8':'windows-1251'));
	}
	
	/**
	 *	Set no display errors
	 *	Against 'Warning:  A non-numeric value encountered in /home/bitrix/www/bitrix/modules/perfmon/classes/general/keeper.php on line 321'
	 */
	public static function disableErrors(){
		ini_set('display_errors', 0);
		error_reporting(~E_ALL);
	}
	
	/**
	 *	Print JSON to page
	 */
	public static function printEncoded($arJson){
		print Json::encode($arJson);
	}
	
	/**
	 *	
	 */
	public static function prepare(){
		static::setHttpHeader();
		static::disableErrors();
		Helper::obRestart();
		return [];
	}
	
	/**
	 *	
	 */
	public static function output($arJsonResult){
		static::printEncoded($arJsonResult);
		static::disableErrors();
	}

}
?>