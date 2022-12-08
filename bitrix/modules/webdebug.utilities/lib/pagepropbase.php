<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class PagePropBase
 * @package WD\Utilities
 */
abstract class PagePropBase {
	abstract public static function getName();
	abstract public static function getCode();
	abstract public static function getIcon();
	abstract public static function showSettings($PropertyType, $SiteID);
	abstract public static function saveSettings($PropertyCode, $SiteID, $arPost);
	abstract public static function showControls($arItem, $PropertyCode, $PropertyID, $PropertyValue, $SiteID);
}
