<?
if(!check_bitrix_sessid()) return;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?echo LANG?>">

    <?=GetMessage("IPOL_FIVEPOST_INSTALL_TEXT")?><br>

    <input style='display:none' type="submit" name="" value="OK">
</form>