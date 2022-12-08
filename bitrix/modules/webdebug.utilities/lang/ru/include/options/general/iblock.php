<?
list($strLang, $strName, $strHint) = \WD\Utilities\Options::getLang();

$MESS[$strLang.'GROUP_GENERAL_IBLOCK'] = 'Полезности для инфоблоков';
#
$MESS[$strName.'IBLOCK_ADD_DETAIL_LINK'] = 'Кнопка просмотра товара в форме редактирования';
	$MESS[$strHint.'IBLOCK_ADD_DETAIL_LINK'] = 'Опция добавляет на страницу редактирования товара кнопку «Просмотр на сайте» (в зависимости от текущей настройки, либо в меню «Действия», либо непосредственно на панель кнопок), которая ведет на страницу сайта с карточкой товара.<br/><br/>
	Домен определяется на основе сайта, к которому привязан инфоблок.';
	$MESS[$strLang.'IBLOCK_ADD_DETAIL_LINK_NO'] = 'Не выводить';
	$MESS[$strLang.'IBLOCK_ADD_DETAIL_LINK_SUBMENU'] = 'В подменю';
	$MESS[$strLang.'IBLOCK_ADD_DETAIL_LINK_SEPARATE'] = 'Отдельно';
#
$MESS[$strName.'IBLOCK_SHOW_ELEMENT_ID'] = 'Показывать ID элемента в форме редактирования';
	$MESS[$strHint.'IBLOCK_SHOW_ELEMENT_ID'] = 'Опция позволяет вывести ID товара в форме редактирования (на панель с кнопками «Сохранить», «Применить», «Отменить»).<br/><br/>
		Работает в т.ч. в popup-окне редактирования товара.';
#
$MESS[$strName.'IBLOCK_JUST_THIS_SITE'] = 'Показывать в меню инфоблоки только текущего сайта';
	$MESS[$strHint.'IBLOCK_JUST_THIS_SITE'] = 'При включенной опции модуль определяет текущий сайт по домену, и скрывает из меню все инфоблоки, не привязанные к текущему сайту.';
#
$MESS[$strName.'IBLOCK_HIDE_EMPTY_TYPES'] = 'Не показывать в меню пустые типы инфоблоков';
	$MESS[$strHint.'IBLOCK_HIDE_EMPTY_TYPES'] = 'Опция удаляет из меню все пустые типы инфоблоков (т.е. такие, в которых нет ни одного инфоблока).';


?>