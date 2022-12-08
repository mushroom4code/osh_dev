<?
list($strLang, $strName, $strHint) = \WD\Utilities\Options::getLang();

$MESS[$strLang.'GROUP_GENERAL_HTTP_HEADERS'] = 'Управление заголовками ответа сервера';
#
$MESS[$strName.'SERVER_HEADERS_ADD'] = 'Добавить заголовки';
	$MESS[$strHint.'SERVER_HEADERS_ADD'] = 'Данная опция позволяет добавить произвольные заголовки ответа страницы.<br/><br/>Используется стандартная PHP-функция header().<br/><br/>Имейте ввиду, что стандартные заголовки (напр., Server, Date, Connection и др.) не могут быть переопределены.';
	$MESS[$strLang.'SERVER_HEADERS_ADD_PLACEHOLDER'] = 'Например, X-Powered-By: PHP/8.0.0';
#
$MESS[$strName.'SERVER_HEADERS_REMOVE'] = 'Удалить заголовки';
	$MESS[$strHint.'SERVER_HEADERS_REMOVE'] = 'Данная опция позволяет удалить некоторые заголовки из ответа страницы.<br/><br/>Имейте ввиду, что могут быть удалены только те заголовки, которые были выставлены PHP и (или) Битриксом. Заголовки, устанавливаемые веб-сервером, или nginx, удалены не могут быть, например: Server, Date, Connection и др.<br/><br/>Однако некоторые из них могут быть переопределены: Content-Type, Accept-Language, Accept-Encoding, Cache-Control и др.';
	$MESS[$strLang.'SERVER_HEADERS_REMOVE_PLACEHOLDER'] = 'Например, Expires';
#
$MESS[$strLang.'SERVER_HEADERS_BUTTON_ADD'] = 'Добавить';
$MESS[$strLang.'SERVER_HEADERS_BUTTON_DELETE'] = 'Удалить';

?>