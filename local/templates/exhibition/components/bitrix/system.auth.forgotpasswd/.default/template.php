<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

ShowMessage($arParams["~AUTH_RESULT"]);

?>
<form name="bform" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?
if ($arResult["BACKURL"] <> '')
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="SEND_PWD">

    <div><?echo GetMessage("sys_forgot_pass_note_email")?></div>
	<div class="form-group row">
		<div><b><?=GetMessage("sys_forgot_pass_login1")?></b></div>
		<div class="col-8 col-md-4 сol-lg-2">
			<input type="text" class="form-control" name="USER_LOGIN" value="<?=$arResult["USER_LOGIN"]?>" />
			<input type="hidden" name="USER_EMAIL" />
		</div>
        <div class="mt-2">
            <button type="submit" class="btn red_button_cart pl-3 pr-3"><?=GetMessage("AUTH_SEND")?></button>
        </div>
	</div>

<?if($arResult["PHONE_REGISTRATION"] && false):?>

	<div style="margin-top: 16px">
		<div><b><?=GetMessage("sys_forgot_pass_phone")?></b></div>
		<div><input type="text" name="USER_PHONE_NUMBER" value="<?=$arResult["USER_PHONE_NUMBER"]?>" /></div>
		<div><?echo GetMessage("sys_forgot_pass_note_phone")?></div>
	</div>
<?endif;?>

<?if($arResult["USE_CAPTCHA"]):?>
	<div style="margin-top: 16px">
		<div>
			<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
		</div>
		<div><?echo GetMessage("system_auth_captcha")?></div>
		<div><input type="text" name="captcha_word" maxlength="50" value="" /></div>
	</div>
<?endif?>
</form>

<div class="mt-2">
	<p><a href="<?=$arResult["AUTH_AUTH_URL"]?>"><ins><?=GetMessage("AUTH_AUTH")?></ins></a></p>
</div>

<script type="text/javascript">
document.bform.onsubmit = function(){document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;};
document.bform.USER_LOGIN.focus();
</script>
