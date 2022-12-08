<?php
use \PickPoint\DeliveryService\Option;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/include.php';
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';

/** @global CMain $APPLICATION */
/** @global array $arServiceTypesCodes - from constants */
/** @global array $arOptionDefaults - from constants */
/** @global array $statusTable - from constants */

$iModuleID = 'pickpoint.deliveryservice';
$ST_RIGHT = $APPLICATION->GetGroupRight($iModuleID);

if ($ST_RIGHT == 'D') {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
IncludeModuleLangFile(__FILE__);
$message = null;

if ($_GET['ORDER_ID']) {
    try {
        $ppRequest = new PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);

        $editOrderData = $ppRequest->getOrderDataToMakeUpdateForm($_GET['ORDER_ID'], $statusTable);
		
		$settings = unserialize($editOrderData['DATA']['SETTINGS']);
		if (!is_array($settings))
			$settings = array();
		
		// Assessed cost
        $assessedCost = 0;
		if (array_key_exists('ASSESSED_COST', $settings)) {
            $assessedCost = $settings['ASSESSED_COST'];
		} else {
			if (Option::get('set_assessed_cost') == 'Y')
                $assessedCost = round(floatval($editOrderData['ORDER_PRICE']), 2);
			else
                $assessedCost = 0;
		}	

        if (!empty($_REQUEST['EDIT'])) {
            $ppRequest->changeInvoice($_GET['ORDER_ID'], $_REQUEST['EDIT'], $_REQUEST['EDIT']['PP_INVOICE_NUMBER']);
            LocalRedirect('/bitrix/admin/pickpoint_edit.php?mess=ok&ORDER_ID='.$_GET['ORDER_ID']);
        }

    } catch (Bitrix\Main\SystemException $exception) {
        echo $exception->getMessage();
    }
} else {
    LocalRedirect('/bitrix/admin/pickpoint_export.php');
}
$arTabs = array();
$arTabs[] = array(
    'DIV' => 'export',
    'TAB' => GetMessage('PP_EDIT'),
    'TITLE' => GetMessage('PP_EDIT'),
);
$tabControl = new CAdminTabControl('tabControl', $arTabs);

if (array_key_exists('mess', $_REQUEST) && $_REQUEST['mess'] == 'ok') {
    CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_NEW_INVOICE'), 'TYPE' => 'OK'));
}
?>
<style>

    .edit_form input{
        width: 50%;
        padding-left: 20px;
    }

    .edit_form span{
        padding-right: 50px;
    }

</style>

<form method="post" class="edit_form" action="" name="find_form">
<?php
if ($ex = $APPLICATION->GetException()) {
    CAdminMessage::ShowOldStyleError($ex->GetString());
}
$tabControl->Begin();
$tabControl->BeginNextTab();
if ($_GET['ORDER_ID']) {
?>
    <input type="hidden" name="EDIT[PP_INVOICE_NUMBER]" value="<?= $editOrderData['DATA']['PP_INVOICE_ID']?>">
    <div><?= getMessage('PP_PHONE'); ?></div> <input type="text" name="EDIT[PP_PHONE]" value="<?= $editOrderData['DATA']['SMS_PHONE'] ?>" <?= (array_key_exists('FIELDS', $editOrderData) && $editOrderData['FIELDS']['MobilePhone']) || $editOrderData['FIELDS_ALL'] ? '' : 'readonly'; ?>><br><br>
    <div><?= getMessage('PP_NAME'); ?></div> <input type="text" name="EDIT[PP_NAME]" value="<?= $editOrderData['DATA']['NAME'] ?>" <?= (array_key_exists('FIELDS', $editOrderData) && $editOrderData['FIELDS']['RecipientName']) || $editOrderData['FIELDS_ALL'] ? '' : 'readonly'; ?>><br><br>
    <div><?= getMessage('PP_EMAIL'); ?></div> <input type="email" name="EDIT[PP_EMAIL]" value="<?= $editOrderData['DATA']['EMAIL'] ?>" <?= (array_key_exists('FIELDS', $editOrderData) && $editOrderData['FIELDS']['Email']) || $editOrderData['FIELDS_ALL'] ? '' : 'readonly'; ?>><br><br>
    <div><?= getMessage('PP_POSTOMAT'); ?></div> <input type="text" name="EDIT[PP_POSTAMAT_ID]" value="<?= $editOrderData['DATA']['POSTAMAT_ID'] ?>" <?= (array_key_exists('FIELDS', $editOrderData) && $editOrderData['FIELDS']['PostamatNumber']) || $editOrderData['FIELDS_ALL'] ? '' : 'readonly'; ?>><br><br>
	
	<div><?=getMessage('PICKPOINT_DELIVERYSERVICE_EDIT_ACCESSED_COST');?></div> <input type="text" name="EDIT[ASSESSED_COST]" value="<?=$assessedCost?>" <?= !$editOrderData['DATA']['PP_INVOICE_ID'] ? '' : 'readonly'; ?>><br><br>
	
    <br><br>
    <div><?= getMessage('PP_WIDTH'); ?></div> <input type="text" name="EDIT[PP_WIDTH]" value="<?= $editOrderData['DIMENSION']['WIDTH'] ?>" <?= $editOrderData['INVOICE_SEND'] ? 'readonly' : ''; ?>><br><br>
    <div><?= getMessage('PP_HEIGHT'); ?></div> <input type="text" name="EDIT[PP_HEIGHT]" value="<?= $editOrderData['DIMENSION']['HEIGHT'] ?>" <?= $editOrderData['INVOICE_SEND'] ? 'readonly' : ''; ?>><br><br>
    <div><?= getMessage('PP_DEPTH'); ?></div> <input type="text" name="EDIT[PP_DEPTH]" value="<?= $editOrderData['DIMENSION']['DEPTH'] ?>" <?= $editOrderData['INVOICE_SEND'] ? 'readonly' : ''; ?>><br><br>


    <input type="submit" class="adm-btn-save" name="export" value="<?php echo GetMessage('PP_EXPORT_BUTTON') ?>">
<?php } else { ?>

<?php } ?>
<?php $tabControl->Buttons(); ?>
<?php
$tabControl->End();
$tabControl->ShowWarnings('find_form', $message);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';