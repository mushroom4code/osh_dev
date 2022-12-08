<?
list($strLang, $strName, $strHint) = \WD\Utilities\Options::getLang();

$MESS[$strLang.'GROUP_DEVELOPERS_GENERAL'] = 'Общие настройки';
#
$MESS[$strName.'GLOBAL_MAIN_FUNCTIONS'] = 'Добавлять простые функции отладки PHP: P() и L()';
	$MESS[$strHint.'GLOBAL_MAIN_FUNCTIONS'] = 'Опция активирует дополнительные специальные функции PHP: P (отладочный вывод), L (логирование).<br/><br/>
		<b>Отладочный вывод</b><br/>
		<code>P($myVar);</code><br/><br/>
		<b>Логирование</b><br/>
		<code>L($myVar, $_SERVER[\'DOCUMENT_ROOT\'].\'/log.txt\');</code><br/>
		Второй аргумент - имя файла (абсолютное). Если имя файла не задано, то имя файла берется из константы LOG_FILENAME. Если же эта константа не задана, то логирование будет в папку /upload/log/.';	
$MESS[$strLang.'GLOBAL_MAIN_FUNCTIONS_MORE'] = 'Будут доступны функции: <code>P()</code> и <code>L()</code>';
#
$MESS[$strName.'JS_DEBUG_FUNCTIONS'] = 'Добавлять простые функции отладки JS';
	$MESS[$strHint.'JS_DEBUG_FUNCTIONS'] = 'Опция активирует дополнительные специальные функции для JavaScript, которые помогают анализировать содержимое объектов и простых переменных.<br/><br/>
		<code>
		&lt;script&gt;<br/>
		log(var_dump_ex(location));<br/>
		&lt;/script&gt;
		</code><br/><br/>
		Данный функционал был реализовано достаточно давно, и с учетом современных возможностей отладки в браузерах, может быть полезным только в редких случаях.';
#
$MESS[$strName.'EDITOR_SHOW_TEMPLATE_PATH'] = 'Показывать путь к шаблону компонента при редактировании';
	$MESS[$strHint.'EDITOR_SHOW_TEMPLATE_PATH'] = 'Опция добавляет во всплывающем окне редактирования шаблона компонента поле, в котором указан путь к шаблону, чтобы этот путь было легко скопировать.<br/><br/>
	Это позволяет быстрее и проще переходить в папку шаблона в различных FTP-клиентах (вставить путь к шаблону и нажать Enter гораздо быстрее чем просто перемещение по папкам).';
#
$MESS[$strName.'PHP_NO_CONFIRM'] = 'Выполнять PHP-код без подтверждения';
	$MESS[$strHint.'PHP_NO_CONFIRM'] = 'Опция позволяет повысить удобство благодаря отключению подтверждения выполнения <a href="/bitrix/admin/php_command_line.php?lang='.LANGUAGE_ID.'" target="_blank">PHP-кода</a>.<br/><br/>
		<b>Внимание!</b> Эта опция повышает удобство, но повышает риск ошибки. Используйте с осторожностью.';
#
$MESS[$strName.'SQL_NO_CONFIRM'] = 'Выполнять SQL-запрос без подтверждения';
	$MESS[$strHint.'SQL_NO_CONFIRM'] = 'Опция позволяет повысить удобство благодаря отключению подтверждения выполнения <a href="/bitrix/admin/sql.php?lang='.LANGUAGE_ID.'" target="_blank">SQL-запроса</a>.<br/><br/>
		<b>Внимание!</b> Эта опция повышает удобство, но повышает риск ошибки. Используйте с осторожностью.';
		
		
?>