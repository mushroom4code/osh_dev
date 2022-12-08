<div class="skyweb24_delete_step">
	<?\CAdminMessage::ShowNote(\Bitrix\Main\Localization\Loc::getMessage("skyweb24.loyaltyprogram_DELETE_TABLES"));?>
	<a class="adm-btn" href="/bitrix/admin/partner_modules.php?id=skyweb24.loyaltyprogram&lang=<?=LANGUAGE_ID?>&uninstall=Y&SAVE=N&sessid=<?=$_REQUEST["sessid"]?>">
		<?=\Bitrix\Main\Localization\Loc::getMessage("skyweb24.loyaltyprogram_DELETE_TABLES_YES")?>
	</a>
	<a class="adm-btn adm-btn-save" href="/bitrix/admin/partner_modules.php?id=skyweb24.loyaltyprogram&lang=<?=LANGUAGE_ID?>&uninstall=Y&SAVE=Y&sessid=<?=$_REQUEST["sessid"]?>">
		<?=\Bitrix\Main\Localization\Loc::getMessage("skyweb24.loyaltyprogram_DELETE_TABLES_NO")?>
	</a>
</div>
