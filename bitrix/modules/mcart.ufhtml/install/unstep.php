<?
if (!check_bitrix_sessid())
	return;

if ($ex = $APPLICATION->GetException())
	echo CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MODULE_UNINST_ERROR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else 
	echo CAdminMessage::ShowNote(GetMessage("MODULE_UNINST_OK"));
?>
<form action="<?echo $APPLICATION->GetCurPage(); ?>">
	<input type="hidden" name="lang" value="<?echo LANG ?>">
	<input type="submit" name="" value="<?echo GetMessage("MODULE_BACK"); ?>">
<form>
