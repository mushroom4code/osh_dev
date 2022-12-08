<?
use Bitrix\Main\Localization\Loc;
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../../../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');
Loc::loadMessages(__FILE__);

if ( php_sapi_name() == 'cli' )
{
	//@set_time_limit(0);

	//lo($argv, '$argv');

	$arSendLogParams = array();
	if ( isset($argv[1]) && strlen($argv[1]) > 0 )
	{
		$dir = "/bitrix/tmp/askaron.pro1c/";
		$abs_path = $_SERVER["DOCUMENT_ROOT"].$dir.$argv[1];
		@include $abs_path;
	}

	//lo($arSendLogParams, '$arSendLogParams');

//	$arSendLogParams = array(
//		"TEST" => false,
//		"TIME" => ConvertTimeStamp(false, FULL),
//		"REQUEST_URI" => $_SERVER["REQUEST_URI"],
//		"CONTENT_CONVERTED" => $content_converted,
//	);

	if ( \CModule::IncludeModule("askaron.pro1c") )
	{
		if ( \COption::GetOptionString( "askaron.pro1c", "live_log") == "Y" )
		{
			if ( \CModule::IncludeModule('pull') )
			{
				if ( $arSendLogParams["TEST"] )
				{
					\CPullWatch::AddToStack('ASKARON_PRO1C_LIVE_LOG',
						Array(
							'module_id' => 'askaron.pro1c',
							'command' => 'live_log',
							'params' => Array(
								"TIME" => $arSendLogParams["TIME"],
								"URL" => 'test',
								"DATA" => 'Живой лог работает',
							)
						)
					);

					// bug after 17.5.1 update
					if ( is_callable(array( '\\Bitrix\\Pull\\Event', 'send' ) ) )
					{
						\Bitrix\Pull\Event::send();
					}
					//or  CMain::FinalActions(); die();
				}
				else
				{
					\CPullWatch::AddToStack('ASKARON_PRO1C_LIVE_LOG',
						Array(
							'module_id' => 'askaron.pro1c',
							'command' => 'live_log',
							'params' => Array(
								"TIME" => $arSendLogParams["TIME"],
								"URL" => $arSendLogParams["REQUEST_URI"],
								"DATA" => $arSendLogParams["CONTENT_CONVERTED"],

//							"TIME" => ConvertTimeStamp(false, FULL),
//							"URL" => $_SERVER["REQUEST_URI"],
//							"DATA" => $content_converted,
							)
						)
					);
				}



				// bug after 17.5.1 update
				if ( is_callable(array( '\\Bitrix\\Pull\\Event', 'send' ) ) )
				{
					\Bitrix\Pull\Event::send();
				}
				//or  CMain::FinalActions(); die();
			}
		}
	}
}
/*
else
{
	if ( $USER->IsAdmin() )
	{
		$timelimit = 300;
		$bStart = false;

		if ( $_REQUEST["submit"] == "Y" && check_bitrix_sessid() )
		{
			$bStart =  true;
			$timelimit = intval($_REQUEST["timelimit"]);

			@set_time_limit($timelimit);

			if ( \CModule::IncludeModule("askaron.sitemap") )
			{
				\Askaron\Sitemap\Work::goCreateSitemapCron( SITE_ID );
			}
		}
		?>
		<?if($bStart):?>
		<p>OK!</p>
	<?endif?>

		<h1>Генерация карты сайта напрямую скриптом</h1>
		<form action="" method="POST">
			<?=bitrix_sessid_post()?>
			Максимальное время выполнения скрипта: <input type="text" name="timelimit" value="<?=$timelimit?>" > секунд
			<br><br><input type="submit" name="submit" value="Сгенерировать sitemap, сайт <?=SITE_ID?>" >
			<input type="hidden" name="submit" value="Y">
		</form>
		<?
	}
}
*/
?>