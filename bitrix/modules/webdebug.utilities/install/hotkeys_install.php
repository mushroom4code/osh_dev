<?
IncludeModuleLangFile(__FILE__);
if(class_exists('CHotKeysCode')){
	$intUserID = $GLOBALS['USER']->getID();
	$arHotKeysCode = array(
		'CLASS_NAME' => 'Global',
		'CODE' => 'wduPopupGotoOpen();',
		'NAME' => GetMessage('WDU_HOTKEYS_NAME'),
		'COMMENTS' => '',
		'TITLE_OBJ' => '',
		'URL' => '',
	);
	$obHotKeysCode = new CHotKeysCode;
	$intCodeID = $obHotKeysCode->Add($arHotKeysCode);
	#
	if($intUserID && class_exists('CHotKeys') && is_numeric($intCodeID) && $intCodeID > 0){
		$strDefaultHotKey = 'Alt+71';
		$arHotKeys = array(
			'KEYS_STRING' => $strDefaultHotKey,
			'CODE_ID' => $intCodeID,
			'USER_ID' => $intUserID,
		);
		$obHotKey = CHotKeys::GetInstance();
		$obHotKey->Add($arHotKeys);
	}
}
?>