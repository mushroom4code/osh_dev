<?
$strLang = 'WDU_FINDER_';
$strHint = $strLang.'HINT_';

$MESS[$strLang.'PAGE_TITLE'] = 'Текстовый поиск по файлам';

$MESS[$strLang.'TAB_GENERAL_NAME'] = 'Параметры поиска';
	$MESS[$strLang.'TAB_GENERAL_DESC'] = 'Параметры текстового поиска по файлам';

$MESS[$strLang.'FIELD_SEARCH_TEXT'] = 'Найти';
	$MESS[$strHint.'FIELD_SEARCH_TEXT'] = 'Укажите здесь текст для поиска.';
	$MESS[$strLang.'FIELD_SEARCH_TEXT_PLACEHOLDER'] = 'Введите текст для поиска';
	$MESS[$strLang.'FIELD_SEARCH_TEXT_EMPTY'] = 'Укажите текст для поиска';
$MESS[$strLang.'FIELD_SEARCH_FILTER'] = 'Фильтры';
	$MESS[$strHint.'FIELD_SEARCH_FILTER'] = 'Перечислите здесь типы файлов, среди которых необходимо провести поиск. Например:<br/>
	<code>*.php, *.txt; *.html; *.js, *.css</code><br/><br/>
	В качестве разделителей можно использовать как запятую, так и точку с запятой. Пробелы между элементами не имеют значения.';
	$MESS[$strLang.'FIELD_SEARCH_FILTER_DEFAULT'] = '*.php, *.txt, *.html, *.js, *.css';
	$MESS[$strLang.'FIELD_SEARCH_FILTER_PLACEHOLDER'] = 'Укажите здесь маски файлов ('.$MESS[$strLang.'FIELD_SEARCH_FILTER_DEFAULT'].')';
$MESS[$strLang.'FIELD_SEARCH_FOLDER'] = 'Директория';
	$MESS[$strHint.'FIELD_SEARCH_FOLDER'] = 'Укажите путь к директории (относительно корня сайта), в которой необходимо осуществить поиск. Например:<br/>
	<code>/bitrix/templates/</code>';
	$MESS[$strLang.'FIELD_SEARCH_FOLDER_DEFAULT'] = '/';
	$MESS[$strLang.'FIELD_SEARCH_FOLDER_PLACEHOLDER'] = 'Укажите начальную директорию';
$MESS[$strLang.'FIELD_SEARCH_FOLDER_EXCLUDE'] = 'Исключить директории';
	$MESS[$strHint.'FIELD_SEARCH_FOLDER_EXCLUDE'] = 'Укажите пути к папкам (относительно корня сайта), которые будут исключены из поиска. Например:<br/>
	<code>/bitrix/*cache/*; /upload/*</code><br/>
	В качестве разделителей можно использовать как запятую, так и точку с запятой. Пробелы между элементами не имеют значения.';
	$MESS[$strLang.'FIELD_SEARCH_FOLDER_EXCLUDE_DEFAULT'] = '/bitrix/*cache/*; /upload/*';
	$MESS[$strLang.'FIELD_SEARCH_FOLDE_EXCLUDER_PLACEHOLDER'] = 'Укажите директории для исключения из поиска';
$MESS[$strLang.'FIELD_SEARCH_REGEXP'] = 'Регулярное выражение';
	$MESS[$strHint.'FIELD_SEARCH_REGEXP'] = 'Отметьте опцию, если следует использовать поиск по регулярному выражению. При этом в поле «'.$MESS[$strLang.'FIELD_SEARCH_TEXT'].'» следует указывать само регулярное выражение. Например:<br/>
	<code>#\\\[\\\d+\\\]#</code>';
$MESS[$strLang.'FIELD_SEARCH_CASE'] = 'Учитывать регистр';
	$MESS[$strHint.'FIELD_SEARCH_CASE'] = 'Отметьте опцию, если необходимо учитывать регистр. В таком случае, если в файле имеется текст «Текст», то по слову «текст» он не будет найден.';
$MESS[$strLang.'FIELD_SEARCH_ENCODING'] = 'Во всех кодировках';
	$MESS[$strHint.'FIELD_SEARCH_ENCODING'] = 'Отметьте опцию, если необходимо искать текст как в кодировке UTF-8, так и в кодировке windows-1251. Это увеличивает время поиска, но позволяет снять привязку к определенной кодировке. Актуально только для поиска кириллических символов.<br/><br/>
	При этом, если текст для поиска одинаков в обеих кодировках, данная опция игнорируется.';
$MESS[$strLang.'FIELD_SEARCH_RESET'] = 'Сбросить все настройки';
	$MESS[$strLang.'FIELD_SEARCH_RESET_CONFIRM'] = 'Вы уверены что хотите сбросить текущие настройки формы?';
$MESS[$strLang.'FIELD_SEARCH_START'] = 'Запустить поиск';
$MESS[$strLang.'FIELD_SEARCH_STOP'] = 'Остановить поиск';
$MESS[$strLang.'FIELD_SEARCH_RESULTS'] = 'Результаты поиска';
$MESS[$strLang.'FIELD_SEARCH_RESULTS_EMPTY'] = '--- Результатов нет ---';

$MESS[$strLang.'NOTE'] = 'Максимальный размер файла для поиска - <b>#MAX_FILESIZE# мегабайт</b>.<br/>
Файлы, имеющие размер более указанного, в поиске не участвуют.<br/>
В результатах поиска показываются первые <b>#MAX_RESULTS# файлов</b>.';


?>