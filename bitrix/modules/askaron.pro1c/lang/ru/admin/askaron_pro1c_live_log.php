<?

$MESS ['askaron_pro1c_live_log_title'] = "Живой лог";
$MESS ['askaron_pro1c_live_log_pull_not_installed'] = 'Модуль Push and Pull не установлен!';
$MESS ['askaron_pro1c_live_log_alert_message'] = 'Задержка получения ответа составляет 5 секунд. Пожалуйста, дождитесь ответа и не закрывайте эту страницу.';
$MESS ['askaron_pro1c_live_log_alert_message_nginx'] = 'Запрос отправлен на «Push server». Скоро должен быть ответ.';
$MESS ['askaron_pro1c_live_log_alert_message_nginx_error'] = 'Ответ от «Push server» не пришел в течение 5 секунд. Попробуйте обновить страницу и проверить еще раз. Если не работает, скорее всего на хостинге не настроен «Push server». Ничего страшного, отключите опцию модуля Push and Pull  «Использовать "Push server"» и пользутесь модулем в режиме без «Push server».';

$MESS ['askaron_pro1c_live_log_message'] = 'Живой лог позволяет в реальном времени наблюдать за выполненнием страниц.';

$MESS ['askaron_pro1c_live_log_warning'] = '
	<strong>Внимание:</strong> задержка получения ответа от  сервера составляет 5 секунд.
	<br /><br />Опция модуля Push and Pull «Использовать "Push server"» выключена.
	<br /><br />Чтобы Живой лог работал без задержек, рекомендуется в 
	<a href="settings.php?lang=#LANG#&amp;mid=pull&amp;mid_menu=1"> настойках модуля Push and Pull</a> 
	включить опцию <em>«Использовать "Push server"»</em>.
	<br /><br />Важно: настройка «Push server» или nginx на вашем сервере в обязаннности разработчика модуля не входит. 
	«Push server» уже настроен на Виртуальной машине Битрикса 4.2 и выше.';

$MESS ['askaron_pro1c_live_log_warning_nginx'] = '
	Опция модуля Push and Pull <em>«Использовать "Push server"»</em> <strong>включена</strong>.
	<br /><br />Если живой лог не работает, то скорее всего на вашем сервере не настроен «Push server».
	<br /><br />Отключите опцию <em>«Использовать "Push server"»</em> в 
	<a href="settings.php?lang=#LANG#&amp;mid=pull&amp;mid_menu=1"> настойках модуля Push and Pull</a> и пользуйтесь модулем в режиме без «Push server».
	<br /><br />Важно: настройка  «Push server» или nginx на вашем сервере в обязаннности разработчика модуля не входит. 
	«Push server» уже настроен на Виртуальной машине Битрикса 4.2 и выше.';


$MESS ['askaron_pro1c_live_log_test'] = 'Проверить работу Живого лога';
$MESS ['askaron_pro1c_live_log_off'] = 'Живой лог выключен';
$MESS ['askaron_pro1c_live_log_off_details'] = 'Включите Живой лог в <a href="settings.php?mid=askaron.pro1c&amp;lang=#LANG#&amp;mid_menu=2">настройках модуля</a> (раздел Отладка обмена).';

$MESS ['askaron_pro1c_delayed_actions'] = 'Выполнение отложенных действий выключено';
$MESS ['askaron_pro1c_delayed_actions_details'] = 'Включите «Вызывать отложенные действия отдельным скриптом» в <a href="settings.php?mid=askaron.pro1c&amp;lang=#LANG#&amp;mid_menu=2">настройках модуля</a> (раздел Отладка обмена).';

$MESS ['askaron_pro1c_live_log_clean'] = 'Очистить область';
$MESS ['askaron_pro1c_live_log_counter'] = 'Счетчик: ';
$MESS ['askaron_pro1c_live_log_pull_install'] = '<a href="module_admin.php?lang=#LANG#">Установить модуль</a> Push and Pull';

$MESS ['askaron_pro1c_live_log_settings'] = 'Настройки модуля «<a href="settings.php?mid=askaron.pro1c&amp;lang=#LANG#&amp;mid_menu=2">Продвинутый обмен с 1С</a>».';


$MESS ['askaron_pro1c_live_log_set_forbidden'] = "Запретить выполнение скрипта";
$MESS ['askaron_pro1c_live_log_set_forbidden_success'] = "Опция «Запретить выполнение скрипта» записана успешно";

$MESS ['askaron_pro1c_live_log_set_log'] = "Записывать все шаги в обычный лог-файл";
$MESS ['askaron_pro1c_live_log_set_log_success'] = "Опция «Записывать все шаги в обычный лог-файл» записана успешно";

$MESS ['askaron_pro1c_live_log_help_in_settings'] = "Подробное описание опции в настройках модуля";

?>