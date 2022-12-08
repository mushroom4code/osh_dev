<?php
if (!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

/** @global CMain $APPLICATION */
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?php echo LANG?>">
    <?php
	$message = new CAdminMessage(['MESSAGE' => GetMessage("PP_DS_INSTALL"), 'TYPE' => 'OK']);
	echo $message->Show();
	echo GetMessage("PP_DS_INSTALL_TEXT");
	?>
	<input id='PICKPOINT_DELIVERYSERVICE_submitVal' style='display:none' type="submit" name="" value="OK">
</form>