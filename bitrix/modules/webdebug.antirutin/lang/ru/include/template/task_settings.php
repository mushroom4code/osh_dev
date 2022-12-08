<?
$strLang = 'WDA_POPUP_TASK_SETTINGS_';
$strHint = $strLang.'HINT_';

$MESS[$strLang.'SHOW_RESULTS_POPUP'] = 'Выводить окно после завершения процесса';
	$MESS[$strHint.'SHOW_RESULTS_POPUP'] = 'Выберите режим отображения ошибок.';
	$MESS[$strLang.'SHOW_RESULTS_POPUP_D'] = '--- Использовать настройки модуля ---';
$MESS[$strLang.'STEP_TIME'] = 'Время одного шага выполнения';
	$MESS[$strHint.'STEP_TIME'] = 'Укажите время выполнения одного шага обработки (в секундах). Указывать значение более 25 секунд не рекомендуется и в некоторых случаях это приведет к ошибке при выполнении.<br/><br/>
Если значение пусто, используются <a href="/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid='.WDA_MODULE.'" target="_blank">настройки модуля</a>.';


?>