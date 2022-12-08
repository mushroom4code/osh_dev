<?
$obHotKeysCode = new CHotKeysCode;
$resItem = $obHotKeysCode->getList(array(),array('CODE' => 'wduPopupGotoOpen();'));
if($arItem = $resItem->getNext()){
	$obHotKeysCode->Delete($arItem['ID']);
}
?>