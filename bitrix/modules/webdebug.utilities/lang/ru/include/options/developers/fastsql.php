<?
list($strLang, $strName, $strHint) = \WD\Utilities\Options::getLang();

$MESS[$strLang.'GROUP_DEVELOPERS_FASTSQL'] = 'Быстрые SQL-запросы';
#
$MESS[$strName.'FASTSQL_ENABLED'] = 'Включить быстрые запросы';
	$MESS[$strHint.'FASTSQL_ENABLED'] = 'Данная опция позволяет для <a href="/bitrix/admin/sql.php?lang='.LANGUAGE_ID.'" target="_blank">страницы выполнения SQL-запросов</a> создавать быстрые запросы, выполняемые одним кликом.<br/><br/>Управление быстрыми запросами (добавление, редактирование, удаление) производится <a href="/bitrix/admin/wdu_fastsql_list.php?lang='.LANGUAGE_ID.'" target="_blank">здесь</a>.';
#
$MESS[$strName.'FASTSQL_AUTO_EXEC'] = 'Авто-выполнение запроса';
	$MESS[$strHint.'FASTSQL_AUTO_EXEC'] = 'Данная опция позволяет указать, как будет выполняться SQL-запрос при клике по нему.';
		$MESS[$strLang.'FASTSQL_AUTO_EXEC_N'] = 'нет';
		$MESS[$strLang.'FASTSQL_AUTO_EXEC_Y'] = 'да, с подтверждением';
		$MESS[$strLang.'FASTSQL_AUTO_EXEC_X'] = 'да, без подтверждения';
#

?>