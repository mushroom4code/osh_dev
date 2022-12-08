<?
$root = realpath(dirname(__FILE__)."/../..");

if ( file_exists( $root."/local/modules/askaron.pro1c/tools/askaron_pro1c_send_log.php" ) )
{
	require_once($root."/local/modules/askaron.pro1c/tools/askaron_pro1c_send_log.php");
}
else
{
	require_once($root."/bitrix/modules/askaron.pro1c/tools/askaron_pro1c_send_log.php");
}
