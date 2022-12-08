<?
namespace WD\Antirutin;

use
	WD\Antirutin\Helper;

if(!isset($arParams)){
	#// If in demo-mode, 2nd argument is not '$arParams' :( - this looks like $_1565435424
	# So, we make hack in Helper::includeFile(): $GLOBALS['arParams'] = $arParams;
	global $arParams;
}

$strLang = 'WDA_POPUP_TASK_SETTINGS_';
$strHint = $strLang.'HINT_';
$arSettings = unserialize($arParams['PROFILE']['SETTINGS']);
$arSettings = is_array($arSettings) ? $arSettings : [];
?>
<div class="wda-task-settings-block">
	<table>
		<tbody>
			<tr>
				<td>
					<?=Helper::showHint(Helper::getMessage($strHint.'SHOW_RESULTS_POPUP'));?>
					<?=Helper::getMessage($strLang.'SHOW_RESULTS_POPUP');?>:
				</td>
				<td>
					<?
					$arShowResultsOptions = \WD\Antirutin\Worker::getOptionsShowResults();
					$arShowResultsOptions = array_merge(['D' => static::getMessage($strLang.'SHOW_RESULTS_POPUP_D')], 
						$arShowResultsOptions);
					?>
					<?=Helper::selectBox('settings[show_results_popup]', $arShowResultsOptions, 
						$arSettings['show_results_popup'], null, 'data-role="wda_settings_show_results_popup"');?>
				</td>
			</tr>
			<tr>
				<td>
					<?=Helper::showHint(Helper::getMessage($strHint.'STEP_TIME'));?>
					<?=Helper::getMessage($strLang.'STEP_TIME');?>:
				</td>
				<td>
					<input type="text" name="settings[step_time]" value="<?=$arSettings['step_time'];?>"
						data-role="wda_settings_step_time" size="5" maxlength="5" />
				</td>
			</tr>
		</tbody>
	</table>
</div>
