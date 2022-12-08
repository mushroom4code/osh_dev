<?
namespace WD\Utilities;

use
	 \WD\Utilities\Helper,
	 \WD\Utilities\JsHelper;

Helper::loadMessages();

/**
 * Class HttpHeader
 * @package WD\Utilities
 */
class HttpHeader {
	
	/**
	 *	Remove headers, add headers
	 */
	public static function processHeaders(){
		static::removeHeaders();
		static::addHeaders();
	}
	
	/**
	 *	Remove headers
	 */
	public static function removeHeaders(){
		$arHeaders = Helper::getOption(WDU_MODULE, 'server_headers_remove');
		if(strlen($arHeaders)){
			$arHeaders = unserialize($arHeaders);
		}
		if(is_array($arHeaders)){
			foreach($arHeaders as $strHeader){
				if(strlen($strHeader)){
					header_remove($strHeader);
				}
			}
		}
	}
	
	/**
	 *	Add headers
	 */
	public static function addHeaders(){
		$arHeaders = Helper::getOption(WDU_MODULE, 'server_headers_add');
		if(strlen($arHeaders)){
			$arHeaders = unserialize($arHeaders);
		}
		if(is_array($arHeaders)){
			foreach($arHeaders as $strHeader){
				if(strlen($strHeader)){
					header($strHeader);
				}
			}
		}
	}
	
}
