<?php
/** @global CMain $APPLICATION */
if ($ex = $APPLICATION->GetException()) {
    echo CAdminMessage::ShowMessage(GetMessage('CURRENCY_INSTALL_UNPOSSIBLE').'<br />'.$ex->GetString()); ?>
    <form action="<?php echo $APPLICATION->GetCurPage()?>">
    <p>
        <input type="hidden" name="lang" value="<?= LANG?>">
        <input type="submit" name="" value="<?= GetMessage('MOD_BACK')?>">
    </p>
    <form>
<?php } else { ?>
    <form action="<?= $APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?= LANG?>">
        <input type="hidden" name="id" value="pickpoint.deliveryservice">
        <input type="hidden" name="uninstall" value="Y">
        <input type="hidden" name="step" value="2">
        <?php CAdminMessage::ShowMessage(GetMessage('MOD_UNINST_WARN'))?>
        <p><?= GetMessage('MOD_UNINST_SAVE')?></p>
        <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= GetMessage('MOD_UNINST_SAVE_TABLES')?></label></p>
        <input type="submit" name="inst" value="<?= GetMessage('MOD_UNINST_DEL')?>">
    </form>
    <?php
}
