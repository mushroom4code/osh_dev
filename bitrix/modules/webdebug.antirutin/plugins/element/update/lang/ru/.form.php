<?
\WD\Antirutin\Helper::getPluginLangPrefix(__FILE__, $strLang, $strHint);

$MESS[$strLang.$strField.'CATALOG_PRODUCT_UPDATE'] = 'Обновить товар торгового каталога [CCatalogProduct::Update]';
	$MESS[$strHint.$strField.'CATALOG_PRODUCT_UPDATE'] = 'Выполнить CcatalogProduct::Update, чтобы сработал обработчик <b><code>OnProductUpdate</code></b>.';
$MESS[$strLang.$strField.'IBLOCK_ELEMENT_UPDATE'] = 'Обновить элемент инфоблока [CIBlockElement::Update]';
	$MESS[$strHint.$strField.'IBLOCK_ELEMENT_UPDATE'] = 'Выполнить CIBlockElement::Update, чтобы сработали обработчики <b><code>OnBeforeIBlockElementUpdate</code></b>, <b><code>OnAfterIBlockElementUpdate</code></b>.';
$MESS[$strLang.$strField.'IBLOCK_ELEMENT_UPDATE_WITH_FIELDS'] = 'С полным набором данных (экспериментальная опция!)';
	$MESS[$strHint.$strField.'IBLOCK_ELEMENT_UPDATE_WITH_FIELDS'] = 'Отметьте галочку, если элементы нужно пересохранять со всеми имеющимися данными. В противном случае элементы сохраняются только с указанием даты обновления.<br/><br/>
<b>Внимание!</b> Это экспериментальная опция. В некоторых случаях может привести к частичной потере данных товара. Набор передаваемых данных может отличаться от набора данных, который передается при сохранении на странице редактирования товара.';

?>