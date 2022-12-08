<?
list($strLang, $strName, $strHint) = \WD\Utilities\Options::getLang();

$MESS[$strLang.'GROUP_GENERAL_INTERFACE'] = 'Настройки интерфейса';
#
$MESS[$strName.'USE_SELECT2_FOR_MODULES'] = 'Использовать плагин jQuery select2 для списка модулей';
	$MESS[$strHint.'USE_SELECT2_FOR_MODULES'] = 'Опция позволяет для выпадающего списка модулей использовать jQuery-плагин select. Это дает возможность поиска (фильтра) по выпадающему списку.';
#
$MESS[$strName.'SET_ADMIN_FAVICON'] = 'Устанавливать в админке собственную favicon';
	$MESS[$strHint.'SET_ADMIN_FAVICON'] = 'Опция позволяет установить для админинстративного раздела собственную иконку сайта. Может быть полезно когда на сайте favicon.ico находится не в корне сайта. Или для более удобного ориентирования в большом количестве вкладок браузера.';
#
$MESS[$strName.'ADMIN_FAVICON'] = 'Административная favicon';
	$MESS[$strHint.'ADMIN_FAVICON'] = 'Здесь необходимо указать путь к файлу favicon.ico относительно корня сайта.';
#
$MESS[$strName.'HIDE_PARTNERS_MENU'] = 'Скрыть (перенести) лишние пункты меню';
	$MESS[$strHint.'HIDE_PARTNERS_MENU'] = 'Опция позволяет перенести пункты административного меню, добавляемые сторонними модулями, в отдельное подменю:<br/><b>«Настройки»</b> - <b>«Дополнительное меню»</b> (в самом низу).<br/><br/>При этом вы сами управляете тем, какие пункты оставить.';
#
$MESS[$strName.'HIDE_PARTNERS_MENU_EXCLUDE'] = 'Пункты главного меню, которые будут показаны';
	$MESS[$strHint.'HIDE_PARTNERS_MENU_EXCLUDE'] = 'Укажите здесь (через запятую) коды пунктов меню, которые не нужно переносить. Значение по умолчанию:<br/><code>desktop, content, landing, marketing, store, services, analytics, marketPlace, settings</code><br/><br/>
		<b>Обратите внмиание!</b> Группы меню будут выводиться в таком порядке, в котором Вы их здесь укажите - таким образом Вы можете отсортировать меню.<br/></br>
		Меню «Настройки» невозможно скрыть.<br/><br/>
		Если поле не заполнено, показываются все пункты меню.';
$MESS[$strLang.'HIDE_PARTNERS_MENU_EXCLUDE_POPUP_TITLE'] = 'Выберите, какие меню будут доступны';
$MESS[$strLang.'HIDE_PARTNERS_MENU_EXCLUDE_LOADING'] = 'Загрузка..';
$MESS[$strLang.'HIDE_PARTNERS_MENU_EXCLUDE_SAVE'] = 'Сохранить';

?>