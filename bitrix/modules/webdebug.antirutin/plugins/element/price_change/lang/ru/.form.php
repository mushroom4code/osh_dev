<?
\WD\Antirutin\Helper::getPluginLangPrefix(__FILE__, $strLang, $strHint);

$MESS[$strLang.'SOURCE'] = 'Откуда взять цену?';
	$MESS[$strHint.'SOURCE'] = 'Выберите поле/свойство с ценой.';
$MESS[$strLang.'TARGET'] = 'Куда записать цену?';
	$MESS[$strHint.'TARGET'] = 'Выберите поле/свойство, в которое необходимо записать результат расчета.';
$MESS[$strLang.'CHANGE_CURRENCY'] = 'Привести к единой валюте';
	$MESS[$strHint.'CHANGE_CURRENCY'] = 'Опция позволяет сконвертировать цену в другую валюту.';
$MESS[$strLang.'CHANGE_PRICE'] = 'Изменить цену';
	$MESS[$strHint.'CHANGE_PRICE'] = 'Опция позволяет изменить значение цены. При этом в полях «от» и «до» указывается цена в базовой валюте.';
	$MESS[$strLang.'CHANGE_PRICE_NOTICE'] = 'Значения цен в полях «от» и «до» необходимо указывать в базовой валюте: <b>#CURRENCY#</b>, при проверке цены конвертируются в базовую валюту.';
	$MESS[$strLang.'CHANGE_PRICE_TEXT_1'] = 'при цене от';
	$MESS[$strLang.'CHANGE_PRICE_TEXT_2'] = 'до';
	$MESS[$strLang.'CHANGE_PRICE_ADD'] = 'Добавить';
	$MESS[$strLang.'CHANGE_PRICE_DELETE'] = 'Удалить';
$MESS[$strLang.'ROUND'] = 'Округлить цену';
	$MESS[$strHint.'ROUND'] = 'Отметьте галочку, если необходимо произвести округление цены.';
	$MESS[$strLang.'ROUND_PRECISION_-3'] = 'до тысяч (1000)';
	$MESS[$strLang.'ROUND_PRECISION_-2'] = 'до сотен (100)';
	$MESS[$strLang.'ROUND_PRECISION_-1'] = 'до десятков (10)';
	$MESS[$strLang.'ROUND_PRECISION_0'] = 'до целого (1)';
	$MESS[$strLang.'ROUND_PRECISION_1'] = 'до десятых (0,1)';
	$MESS[$strLang.'ROUND_PRECISION_2'] = 'до сотых (0,01)';
	$MESS[$strLang.'ROUND_PRECISION_3'] = 'до тысячных (0,001)';
	$MESS[$strLang.'ROUND_TYPE_ROUND'] = 'математически';
	$MESS[$strLang.'ROUND_TYPE_CEIL'] = 'округление вверх';
	$MESS[$strLang.'ROUND_TYPE_FLOOR'] = 'округление вниз';
$MESS[$strLang.'OFFERS'] = 'Применить также для ТП';
	$MESS[$strHint.'OFFERS'] = 'Отметьте галочку, если необходимо выполнить операции также для каждого торгового предложения товара.<br/><br/>Данная опция недоступна при копировании цены из свойства или в свойство, т.е. доступно только при копировании между ценами торгового каталога (в т.ч. закупочной ценой).';
$MESS[$strLang.'LIMIT'] = 'Ограничить минимальную цену относительно закупочной';
	$MESS[$strHint.'LIMIT'] = 'Опция позволяет снижать цены (применять скидки), ограничивая при этом ее нижнее значение - чтобы итоговая цена товаров не была ниже закупочной (плюс некоторый процент).';
	$MESS[$strLang.'LIMIT_PLACEHOLDER'] = 'Напр., +10%';
/*
$MESS[$strLang.'FORMAT'] = 'Форматирование цены (только для свойств)';
	$MESS[$strHint.'FORMAT'] = 'Отметьте галочку, если при записи цены в свойство инфоблока необходимо форматировать ее вывод.';
*/
?>