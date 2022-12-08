<?
use \WD\Antirutin\Helper;
$strPluginName = static::getName();
if(!Helper::isUtf()){
	$strPluginName = Helper::convertEncoding($strPluginName, 'CP1251', 'UTF-8');
}
$strMailTo = Helper::getMailtoLink([
	'TEXT' => 'напишите нам об этом',
	'SUBJECT' => 'Антирутин: заявка на добавление поддержки нового типа свойства в плагин «'.$strPluginName.'»',
	'ATTR' => 'class="wda-inline-link"',
]);
?>
<p>Плагин для удаления выбранных значений из множественных свойств элементов.</p>
<p>На данный момент поддерживаются только свойства типа «Список». Если вам требуется поддержка другого типа свойств, 
	пожалуйста, <?=$strMailTo;?> - мы добавим поддержку.</p>