<?php
namespace Askaron\Pro1c;
//Loc::loadMessages(__FILE__);

class Tools
{
	public static function IsCatalog_17_6_0()
	{
		static $cache = null;
		$result = true;

		if ( $cache !== null )
		{
			$result = $cache;
		}
		else
		{
			if ($info = \CModule::CreateModuleObject('catalog'))
			{
				$testVersion = '17.6.0';
				if (CheckVersion($info->MODULE_VERSION, $testVersion))
				{
					$result = true;
				}
				else
				{
					$result = false;
				}
			}

			$cache = $result;
		}

		return $result;
	}

	public static function SendLog( $arSendLogParams )
	{
		$delayed_actions = \COption::GetOptionString("askaron.pro1c", "delayed_actions");
		if ( $delayed_actions != "NO" )
		{
			$file = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/askaron_pro1c_send_log.php";

			$file_name = "sendlog_" . time() . "_rand" . mt_rand(100000, 999999) . ".php";
			$dir = "/bitrix/tmp/askaron.pro1c/";
			$abs_path = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tmp/askaron.pro1c/" . $file_name;

			$text = "<?php\n\$arSendLogParams = " . var_export($arSendLogParams, true) . ";\n?>";

			CheckDirPath($_SERVER["DOCUMENT_ROOT"] . $dir );
			file_put_contents($abs_path, $text);

			if ($delayed_actions == "FILE")
			{
				$delayed_actions_bash = \COption::GetOptionString("askaron.pro1c", "delayed_actions_bash");
				$command_line = $delayed_actions_bash . ' ' . escapeshellarg($file) . ' ' . escapeshellarg($file_name) . ';';
				try
				{
					self::ExcecuteCommand($command_line, false);
				}
				catch (\Exception $e)
				{
					echo "Exception:";
					echo $e->getMessage();

					\CAskaronPro1C::log($e->getMessage(), "Bash error message");
					\CAskaronPro1C::log($e->getTraceAsString(), "Bash error trace");
				}
			}
			@unlink($abs_path);
		}
	}

	public static function ExcecuteCommand($command, $show = true)
	{
		$result = false;
		$str_err = "";

		if ($show)
		{
			echo $command . "\n\n";
		}

		$descriptors = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);

		$process = proc_open($command, $descriptors, $pipes, null, null, array('bypass_shell' => true));

		if (is_resource($process))
		{
			$result = stream_get_contents($pipes[1]);
			$str_err = stream_get_contents($pipes[2]);

			if ( $show )
			{
				echo $result . "\n\n";
				if (strlen($str_err) > 0)
				{
					echo "Error:\n";
					echo $str_err . "\n\n";
				}
			}

			fclose($pipes[1]);
			fclose($pipes[2]);

			$return_value = proc_close($process);
			if ($return_value != 0)
			{
				// 0 ne vsegda oshibka.
				// Na nekotorih hostingah bivaet -1. Ne razbiralis pochemu
				throw new \Exception("Could not run command $command:\n$str_err");
			}
			else
			{
				//$result = $str_output;
			}
		}
		else
		{
			throw new \Exception("Could not run command $command");
			//$this->error = "Could not run command $command";
		}

		return $result;
	}
}