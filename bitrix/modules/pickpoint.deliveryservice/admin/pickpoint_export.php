<?php
use \PickPoint\DeliveryService\Option;
use \PickPoint\DeliveryService\StatusHandler;
use \PickPoint\DeliveryService\Pickpoint\Statuses;
use \PickPoint\DeliveryService\Bitrix\Tools;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/include.php';
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';

/** @global CMain $APPLICATION */
/** @global array $arOptions */
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

CJSCore::Init(array("jquery"));

$APPLICATION->SetTitle(GetMessage('PICKPOINT_DELIVERYSERVICE_ADMIN_EXPORT_TITLE'));

// Check required options
$isRequiredOptionsSet = Option::isRequiredOptionsSet();
if (!$isRequiredOptionsSet) {
	$optionsNotSetMessage = new CAdminMessage([
		'MESSAGE' => GetMessage("PP_MODULE_OPTIONS_NOT_SET"), 
		'TYPE' => 'ERROR', 
		'DETAILS' => GetMessage("PP_MODULE_OPTIONS_NOT_SET_TEXT"), 
		'HTML' => true
		]);
	echo $optionsNotSetMessage->Show();
}	

// Working with orders
if (!empty($_REQUEST['updateStatus']) || !empty($_REQUEST['CANCEL']) || !empty($_REQUEST['EXPORT']) || !empty($_REQUEST['save']) || !empty($_REQUEST['show']) || !empty($_REQUEST['ARCHIVE']) || !empty($_REQUEST['FROMARCHIVE'])) {
    try {
        if (!empty($_REQUEST['show'])) {
            \PickPoint\Helper::setPageElementsCount($_REQUEST['show']);
            LocalRedirect('/bitrix/admin/pickpoint_export.php?lang='.LANG);
        }

        $ppRequest = new \PickPoint\Request($arServiceTypesCodes, $arOptionDefaults);
        $status = true;

        //  Update states
        if (!empty($_REQUEST['updateStatus'])) {
            if ($_REQUEST['updateStatus'] == 'Y') {
				// Deprecated method
                // $ppRequest->updateAllInvoicesStatus();
				
				StatusHandler::refreshOrderStates();
                LocalRedirect('/bitrix/admin/pickpoint_export.php?lang='.LANG.'&mess=update');
            }
        }

        //  Move to archive
        if (!empty($_REQUEST['ARCHIVE'])) {
            foreach ($_REQUEST['ARCHIVE'] as $orderId => $ppNumber) {
                $ppRequest->changeArchiveStatus($orderId);
            }

            LocalRedirect('/bitrix/admin/pickpoint_export.php?lang='.LANG.'&mess=archive');
        }
		
		// Back from achive
		if (!empty($_REQUEST['FROMARCHIVE'])) {
            foreach ($_REQUEST['FROMARCHIVE'] as $orderId => $ppNumber) {
                $ppRequest->changeArchiveStatus($orderId);
            }

            LocalRedirect('/bitrix/admin/pickpoint_export.php?lang='.LANG.'&mess=fromarchive');
        }		

        //  Cancel order
        if (!empty($_REQUEST['CANCEL'])) {
            foreach ($_REQUEST['CANCEL'] as $orderId => $ppNumber) {
                $answer = $ppRequest->cancelInvoice(key($ppNumber), $orderId);
                if  (!$answer['STATUS']){
                    CAdminMessage::ShowMessage(array('MESSAGE' => $orderId . ' -> ' . $answer['TEXT'], 'TYPE' => 'ERROR'));
                    $status = false;
                }
            }

            if ($status) {
                LocalRedirect('/bitrix/admin/pickpoint_export.php?lang='.LANG.'&mess=delete');
            }
        }

        // Export orders
        if (!empty($_REQUEST['EXPORT']) && ($_REQUEST['export'])) {
            $bError = false;
            $arExportIDs = array();

            foreach ($_REQUEST['EXPORT'] as $iOrderID => $arFields) {
                if ($arFields['EXPORT']) {
                    $obOrder = CSaleOrder::GetList(
                        array(),
                        array('ID' => $iOrderID),
                        false,
                        false,
                        array('ID', 'PRICE', 'PAY_SYSTEM_ID', 'PERSON_TYPE_ID')
                    );
                    if ($arOrder = $obOrder->Fetch()) {
                        $arExportIDs[] = $arOrder['ID'];
                    } else {
                        $APPLICATION->ThrowException(GetMessage('NO_ORDER', array('#ORDER_ID#' => $iOrderID)));
                        break;
                    }
                }
            }

            $answer = $ppRequest->sendInvoice($arExportIDs); // Send orders to PP
			
			// Refresh order states after export 
			StatusHandler::refreshOrderStates();

            foreach ($answer as $orderIdAnswer => $orderItemAnswer) {
                if  (!$orderItemAnswer['STATUS']) {
                    $bError = true;
                    foreach ($orderItemAnswer['ERRORS'] as $error) {
                        CAdminMessage::ShowMessage([
                            'MESSAGE' => GetMessage('PP_ERROR_IN_ORDER') . $orderIdAnswer . ' : ' . $error,
                            'TYPE' => 'ERROR'
                        ]);
                    }
                } else {
                    CAdminMessage::ShowMessage([
                        'MESSAGE' => GetMessage('PP_ORDER_WITH_NUMBER') . $orderIdAnswer . GetMessage('PP_ORDER_WITH_NUMBER_IS_SUCCESS'),
                        'TYPE' => 'OK'
                    ]);
                }
            }

            if (!$bError) {
                LocalRedirect('/bitrix/admin/pickpoint_export.php?lang=' . LANG . '&mess=ok');
            }
        } elseif ($_REQUEST['save']) {
            foreach ($_REQUEST['EXPORT'] as $iOrderID => $arFields) {
                CPickpoint::SaveOrderOptions($iOrderID);
            }
            LocalRedirect('/bitrix/admin/pickpoint_export.php?lang='.LANG.'&mess=save');
        }

    } catch (Bitrix\Main\SystemException $exception){
        CAdminMessage::ShowMessage(array('MESSAGE' => $exception->getMessage(), 'TYPE' => 'ERROR'));
    }
}

// Get orders data
try {
    $invoices = new \PickPoint\Invoices();
    $arItems = $invoices->getInvoicesArray();
	// Make actual statuses info
	$map = Statuses::getStatusMap();
	
	foreach ($arItems as $g => $group) {
		foreach ($group as $i => $item) {
			if (isset($item['STATUS_CODE']) && array_key_exists($item['STATUS_CODE'], $map))
				$arItems[$g][$i]['STATUS_TEXT'] = $map[$item['STATUS_CODE']]['VISUALSTATE'];				
			else if (isset($item['STATUS']))
				// Legacy statuses info - table from constants.php
				$arItems[$g][$i]['STATUS_TEXT'] = !empty($item['STATUS']) ? $statusTable[$item['STATUS']]['TEXT'] : '';
		}
	}	
} catch (Bitrix\Main\SystemException $exception) {
    echo $exception->getMessage();
}

$arTabs = [];

$arTabs[] = [
    'DIV' => 'export',
    'TAB' => GetMessage('PP_EXPORT_NEW'),
    'TITLE' => GetMessage('PP_EXPORT_NEW'),
];
$arTabs[] = [
    'DIV' => 'forwarded',
    'TAB' => GetMessage('PP_EXPORT_FORWARDED'),
    'TITLE' => GetMessage('PP_EXPORT_FORWARDED'),
];
$arTabs[] = [
    'DIV' => 'revert',
    'TAB' => GetMessage('PP_EXPORT_REVERT'),
    'TITLE' => GetMessage('PP_EXPORT_REVERT'),
];
$arTabs[] = [
    'DIV' => 'ready',
    'TAB' => GetMessage('PP_EXPORT_READY'),
    'TITLE' => GetMessage('PP_EXPORT_READY'),
];
$arTabs[] = [
    'DIV' => 'canceled',
    'TAB' => GetMessage('PP_EXPORT_CANCELED'),
    'TITLE' => GetMessage('PP_EXPORT_CANCELED'),
];
$arTabs[] = [
    'DIV' => 'archive',
    'TAB' => GetMessage('PP_EXPORT_ARCHIVE'),
    'TITLE' => GetMessage('PP_EXPORT_ARCHIVE'),
];

$tabControl = new CAdminTabControl('tabControl', $arTabs);

Tools::getOptionsCss();
?>
<script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
<script type="text/javascript">
    var <?=PICKPOINT_DELIVERYSERVICE_LBL?>export = new pickpoint_deliveryservice_adminInterface({
        'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
        'label'    : '<?=PICKPOINT_DELIVERYSERVICE?>',
        'logging'  : true
    });
    
    <?=PICKPOINT_DELIVERYSERVICE_LBL?>export.addPage('orders',{
        init: function(){            
        },               
        
        getBarcodes: function(invoice){
            <?=PICKPOINT_DELIVERYSERVICE_LBL?>export.ajax({
                data: {<?=PICKPOINT_DELIVERYSERVICE_LBL?>action: 'getBarcodesRequest', invoices: invoice},
                dataType: 'json',
                success: function(data){
                    if(data.success){
                        window.open(data.url);
                    }else{
                        alert('<?=GetMessage("PICKPOINT_DELIVERYSERVICE_GETBARCODES_ERROR")?>');
                    }
                }
            });
        },
    });
	
	$(document).ready(<?=PICKPOINT_DELIVERYSERVICE_LBL?>export.init);
</script>
<style>
	a.knopka {
		color: #fff; 
		text-decoration: none; 
		user-select: none; 
		background: rgb(212,75,56); 
		padding: .6em 1.5em; 
		outline: none; 
	}
	a.knopka:hover { background: rgb(232,95,76); } 
	a.knopka:active { background: rgb(152,15,0); } 

	span.knopka {
		color: #000000; 
		padding: .3em 1em; 
		border: 1px solid #0b011d;
		cursor: pointer;
		margin-top: 10px;
	}

	.show_div {
		float: left;
		font-size: 14px;
	}
	.show_div a{
		text-decoration: none;
	}
	.show_div span{
		font-weight: bold;
	}
	
	#table_export {
		border-spacing: 0 6px;
	}
	
	#table_export td {
		border-bottom: 1px dashed black;
		padding-bottom: 6px;
	}
	
	#table_export .gettingType {
		width: 125px;
	}
</style>
<script>
	function SelectAll(cSelectAll) {
		bVal = (cSelectAll.checked);
		Table = document.getElementById("table_export");
		arInputs = Table.getElementsByClassName("cToExport");
		for (i = 0; i < arInputs.length; i++) {
			if (!arInputs[i].hasAttribute("disabled"))
				arInputs[i].checked = bVal;
		}

	}
	
	function CheckFields(cSelectAll) {			
		var check = ['cToExport', 'cToCancel', 'cToArchive'];
		
		var tab = $('[name=find_form]').find('div.adm-detail-content:visible').attr('id');			
		if (tab == 'undefined')
			return false;
		
		var Table = $('#'+tab).find('#'+tab+"_edit_table");  					
		
		for (c = 0; c < check.length; c++)
		{
			var inputs = $(Table).find('input.'+check[c]);										
			if (inputs.length)
			{
				for (i = 0; i < inputs.length; i++) {						
					if ($(inputs[i]).prop('checked')) 
						return true;
				}					
			}
		}		
		
		return false;
	}
	function CheckServiceType(select, orderId) {
		price = document.getElementById("export_price_" + orderId);
		payedprice = document.getElementById("export_payed_price_" + orderId);
		if (select.value == 1 || select.value == 3) {
			payedprice.innerHTML = '<input type = "text" size = "8" name="EXPORT[' + orderId + '][PAYED]" value="' + price.value + '"/>';
		}
		else {
			payedprice.innerHTML = '<?=GetMessage('PP_NO')?>';
		}
	}
	function showAll(elements) {
		$('.id_' + elements).show();
		$('.button_' + elements).hide();
	}		
</script>

<?php
if (array_key_exists('mess', $_REQUEST)) {
    switch ($_REQUEST['mess']) {
        case 'ok':
            CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_NEW_INVOICE'), 'TYPE' => 'OK'));
            break;
        case 'save':
            CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_SAVE_SETTINGS'), 'TYPE' => 'OK'));
            break;
        case 'update':
            CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_UPDATED'), 'TYPE' => 'OK'));
            break;
        case 'delete':
            CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_DELETE'), 'TYPE' => 'OK'));
            break;
        case 'archive':
            CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_ARCHIVE'), 'TYPE' => 'OK'));
            break;
        case 'fromarchive':
            CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('PP_FROMARCHIVE'), 'TYPE' => 'OK'));
            break;
    }
}

// Status last sync datetime
$statusLastSync = Option::get('status_last_sync');
$statusLastSync = ($statusLastSync > 0) ? date("d.m.Y H:i:s", $statusLastSync) : '-';

$isStatusSync = (Option::get('status_sync_enabled') == 'Y');

$isAssessedCost = (Option::get('set_assessed_cost') == 'Y');

// Getting types
$allGettingTypes = Option::getVariants('getting_type');
$allowedGettingTypes = array_intersect(array_keys($allGettingTypes), Option::get('getting_type'));
$defaultGettingType = reset($allowedGettingTypes);
?>
    <form method="post" action="<?= $APPLICATION->GetCurPage() ?>" name="find_form">
        <?php
        if ($ex = $APPLICATION->GetException()) {
            CAdminMessage::ShowOldStyleError($ex->GetString());
        }
        if (array_key_exists('message', $_REQUEST) && strlen($_REQUEST['message']) > 0) {
            CAdminMessage::ShowNote($_REQUEST['message']);
        }
        $arServiceTypes = (unserialize(COption::GetOptionString($iModuleID, 'pp_service_types_all')));
        $arAllowedServiceTypes = unserialize(COption::GetOptionString($iModuleID, 'pp_service_types_selected'));

        $tabControl->Begin();
        $tabControl->BeginNextTab();?>

        <div style="text-align: center; height: 30px">
            <div class="show_div">
                <span><?php echo GetMessage('PP_SHOW_ORDER') ?></span>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 20 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=20">20</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 50 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=50">50</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 100 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=100">100</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 99999999 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=99999999"><?php echo GetMessage('PP_ALL') ?></a>
            </div>
            <?php if ($isRequiredOptionsSet):?>
				<?php if ($isStatusSync)
					echo '<p>'.GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT').' '.$statusLastSync.' '.
						'<a class="knopka" href="'.$APPLICATION->GetCurPage().'?updateStatus=Y">'.GetMessage('PP_UPDATE_BUTTON').'</a></p>';
				else
					echo GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED');
				?>				
			<?php endif;?>
        </div>
        <!--        tab1        -->
        <tr>
            <td>
                <table width="100%" class="edit-table" id="table_export">
                    <tr class="heading">
                        <td><input type="checkbox" id="cSelectAll" onclick="SelectAll(this)"/></td>
                        <td><?= GetMessage('PP_ORDER_NUMBER') ?></td>
                        <td><?= GetMessage('PP_SUMM') ?></td>
                        <td><?= GetMessage('PP_PAYED_BY_PP') ?></td>
						<td>
							<a href='#' class='<?=PICKPOINT_DELIVERYSERVICE_LBL?>PropHint' onclick='return <?=PICKPOINT_DELIVERYSERVICE_LBL?>export.popup("pop-export_accessedCost", this);'></a>
							<span id='export_accessedCost'><?=GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_ACCESSED_COST')?></span> 
							<?php Tools::placeHint('export_accessedCost');?>
						</td>
                        <td><?= GetMessage('PP_ADDRESS') ?></td>
                        <td><?= GetMessage('PP_SERVICE_TYPE') ?></td>
                        <td>
							<a href='#' class='<?=PICKPOINT_DELIVERYSERVICE_LBL?>PropHint' onclick='return <?=PICKPOINT_DELIVERYSERVICE_LBL?>export.popup("pop-export_gettingType", this);'></a>
							<span id='export_gettingType'><?=GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE')?></span> 
							<?php Tools::placeHint('export_gettingType');?>
						</td>
                        <td><?= GetMessage('PP_WIDTH') ?></td>
                        <td><?= GetMessage('PP_HEIGHT') ?></td>
                        <td><?= GetMessage('PP_DEPTH') ?></td>
                        <td><?= GetMessage('PP_ACTION_EDIT') ?></td>
                        <td><?= GetMessage('PP_ARCHIVE_ADD') ?></td>
                    </tr>
					<?php if (is_array($arItems['NEW']) && count($arItems['NEW'])):?>
						<?php foreach ($arItems['NEW'] as $key => $arItem) : ?>
							<?php
                                $settings = $arItem['SETTINGS'];
								if (!is_array($settings))
									$settings = array();
							?>						
							<?php $arRequestItem = array_key_exists('EXPORT', $_REQUEST) ? $_REQUEST['EXPORT'][$arItem['ORDER_ID']] : [];
							$bActive = $arItem['INVOICE_ID'] ? false : true;

							$arItem['PAYED'] = $arItem['PRICE'];
							$arItem['PAYED'] = number_format($arItem['PAYED'], 2, '.', '');

							if (in_array($arItem['SETTINGS']['SERVICE_TYPE'], array(1, 3))) {
								$arItem['PAYED_PP_SET'] = 1;
							}
							?>
							<tr class="id_new" <?= $key > $arOptions['OPTIONS']['show_elements_count'] ? 'style="display:none"' : ''; ?>>
								<td><input <?php if (!$bActive) : ?> disabled="disabled"<?php endif; ?>
										type="checkbox" <?= ($arRequestItem['EXPORT']) ? 'checked' : '' ?> class="cToExport"
										name="EXPORT[<?= $arItem['ORDER_ID'] ?>][EXPORT]" autocomplete="off"/></td>
								<td align="center"><?php //= GetMessage('PP_N') ?><?= $arItem['ORDER_NUMBER'] ?> <?= GetMessage('PP_FROM') ?>
									<br/><?= $arItem['ORDER_DATE'] ?></td>
								<td align="center"><?= CurrencyFormat($arItem['PRICE'], 'RUB') ?>
									<?php if ($bActive) : ?><input type="hidden" id="export_price_<?= $arItem['ORDER_ID'] ?>"
																   value="<?= $arItem['PRICE'] ?>" /><?php endif; ?></td>
								<td align="center" id="export_payed_price_<?= $arItem['ORDER_ID'] ?>"><?php
                                    if ($arItem['PAYED_BY_PP'] || $arItem['PAYED_PP_SET'])
										echo $arItem['PAYED'];
									else
										echo GetMessage('PP_NO');
								?></td>
								<td align="center"><?php
                                    $assessedCost = 0;
									if (array_key_exists('ASSESSED_COST', $settings))
										$assessedCost = $settings['ASSESSED_COST'];									
									else if ($isAssessedCost)
										$assessedCost = $arItem['PRICE'];																				
									
									echo CurrencyFormat($assessedCost, 'RUB');									
								?></td>
								<td align="center"><?= $arItem['PP_ADDRESS'] ?></td>
								<td align="center"><?php
                                    if ($arItem['PAYED_BY_PP'])
										echo $arServiceTypes[1];
									else
										echo $arServiceTypes[0];
								?></td>
								<td align="center">
									<?php $itemGettingType = (intval($arItem['GETTING_TYPE']) > 0) ? $arItem['GETTING_TYPE'] : $defaultGettingType;?>
									<select class='gettingType' <?=(!$bActive) ? 'disabled="disabled"' : ''?> name="EXPORT[<?=$arItem['ORDER_ID']?>][GETTING_TYPE]"/>
									<?php foreach ($allGettingTypes as $type => $text) {
										?><option value="<?=$type?>" <?=($itemGettingType == $type) ? 'selected' : ''?> <?=(!in_array($type, $allowedGettingTypes)) ? 'disabled="disabled"' : ''?>><?=$text?></option><?php
                                    }?>
									</select>									
								</td>
								<td align="center"><?= number_format(floatval($arItem['WIDTH']) , 2); ?></td>
								<td align="center"><?= number_format(floatval($arItem['HEIGHT']) , 2); ?></td>
								<td align="center"><?= number_format(floatval($arItem['DEPTH']) , 2); ?></td>
								<td style="text-align: center">
									<?php if (!$arItem['CANCELED']) : ?>
										<a target="_blank" style="text-decoration: none" href="/bitrix/admin/pickpoint_edit.php?ORDER_ID=<?= $arItem['ORDER_ID'] ?>"><?= GetMessage('PP_EDIT_LINK') ?></a>
									<?php endif; ?>
								</td>
								<td align="center">
									<input type="checkbox" class="cToArchive"
										   name="ARCHIVE[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
										   autocomplete="off"/>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
                </table>
                <?php if (is_array($arItems['NEW']) && (count($arItems['NEW']) > $arOptions['OPTIONS']['show_elements_count'])): ?>
                    <div style="text-align: center; height: 30px">
                        <span class="knopka button_new" onclick="showAll('new')">
                            <?= GetMessage('PP_SHOW_MORE') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
            <?php $tabControl->BeginNextTab(); ?>
            <div style="text-align: center; height: 30px">
                <div class="show_div">
                    <span><?php echo GetMessage('PP_SHOW_ORDER') ?></span>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 20 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=20">20</a>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 50 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=50">50</a>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 100 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=100">100</a>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 99999999 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=99999999"><?php echo GetMessage('PP_ALL') ?></a>
                </div>
				<?php if ($isRequiredOptionsSet):?>
					<?php if ($isStatusSync)
						echo '<p>'.GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT').' '.$statusLastSync.' '.
							'<a class="knopka" href="'.$APPLICATION->GetCurPage().'?updateStatus=Y">'.GetMessage('PP_UPDATE_BUTTON').'</a></p>';
					else
						echo GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED');
					?>	
				<?php endif;?>
            </div>            <!--        tab2        -->
            <tr>
                <td>
                    <table width="100%" class="edit-table" id="table_export">
                        <tr class="heading">
                            <td><?= GetMessage('PP_ORDER_NUMBER') ?></td>
                            <td><?= GetMessage('PP_INVOICE_ID') ?></td>
                            <td><?= GetMessage('PP_STATUS') ?></td>
                            <td><?= GetMessage('PP_SUMM') ?></td>
                            <td><?= GetMessage('PP_PAYED_BY_PP') ?></td>
                            <td><?= GetMessage('PP_ADDRESS') ?></td>
                            <td><?= GetMessage('PP_SERVICE_TYPE') ?></td>
                            <td><?= GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE') ?></td>
                            <td><?= GetMessage('PP_WIDTH') ?></td>
                            <td><?= GetMessage('PP_HEIGHT') ?></td>
                            <td><?= GetMessage('PP_DEPTH') ?></td>
                            <td>
								<a href='#' class='<?=PICKPOINT_DELIVERYSERVICE_LBL?>PropHint' onclick='return <?=PICKPOINT_DELIVERYSERVICE_LBL?>export.popup("pop-export_actionGetBarcodes", this);'></a>
								<span id='export_actionGetBarcodes'><?=GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_ACTION_GETBARCODES')?></span> 
								<?php Tools::placeHint('export_actionGetBarcodes');?>
							</td>
                            <td><?= GetMessage('PP_ACTION_CANCEL') ?></td>
                            <td><?= GetMessage('PP_ACTION_EDIT') ?></td>
                            <td><?= GetMessage('PP_ARCHIVE_ADD') ?></td>
                        </tr>
						<?php if (is_array($arItems['FORWARDED']) && count($arItems['FORWARDED'])):?>
							<?php foreach ($arItems['FORWARDED'] as $key => $arItem) : ?>
								<?php $arRequestItem = $_REQUEST['EXPORT'][$arItem['ORDER_ID']];
								$bActive = $arItem['INVOICE_ID'] ? false : true;

								$arItem['PAYED'] = $arItem['PRICE'];
								$arItem['PAYED'] = number_format($arItem['PAYED'], 2, '.', '');

								if (in_array($arItem['SETTINGS']['SERVICE_TYPE'], array(1, 3))) {
									$arItem['PAYED_PP_SET'] = 1;
								}
								?>
								<tr class="id_forward" <?= $key > $arOptions['OPTIONS']['show_elements_count'] ? 'style="display:none"' : ''; ?>>
									<td align="center"><?php //= GetMessage('PP_N') ?><?= $arItem['ORDER_NUMBER'] ?> <?= GetMessage('PP_FROM') ?>
										<br/><?= $arItem['ORDER_DATE'] ?></td>
									<td align="center"><?= $arItem['INVOICE_ID'] ? $arItem['INVOICE_ID'] : '' ?></td>
									<td align="center" style="font-weight: bold" title="<?=$arItem['STATUS_TEXT'];?>"><?= $arItem['STATUS']; ?></td>
									<td align="center"><?= CurrencyFormat($arItem['PRICE'], 'RUB') ?>
										<?php if ($bActive) : ?><input type="hidden" id="export_price_<?= $arItem['ORDER_ID'] ?>"
																	   value="<?= $arItem['PRICE'] ?>" /><?php endif; ?></td>
									<td align="center" id="export_payed_price_<?= $arItem['ORDER_ID'] ?>">
										<?php if ($arItem['PAYED_BY_PP'] || $arItem['PAYED_PP_SET']): ?>
											<?= $arItem['PAYED'] ?>
										<?php else : ?>
											<?= GetMessage('PP_NO') ?>
										<?php endif; ?>
									</td>
									<td align="center"><?= $arItem['PP_ADDRESS'] ?></td>
									<td align="center">
										<?php if ($arItem['PAYED_BY_PP']) : ?>
											<?= $arServiceTypes[1] ?>
										<?php else : ?>
											<?= $arServiceTypes[0] ?>
										<?php endif ?>
									</td>
									<td align="center"><?=(intval($arItem['GETTING_TYPE']) > 0) ? $allGettingTypes[$arItem['GETTING_TYPE']] : '-'?></td>
									<td align="center"><?= number_format(floatval($arItem['WIDTH']) , 2); ?></td>
									<td align="center"><?= number_format(floatval($arItem['HEIGHT']) , 2); ?></td>
									<td align="center"><?= number_format(floatval($arItem['DEPTH']) , 2); ?></td>
									<td align="center">
										<?php if ($arItem['INVOICE_ID'] && ((isset($arItem['STATUS_CODE']) && Statuses::canGetBarcodeCheckByVisualState($arItem['STATUS_CODE'])) ||
											Statuses::canGetBarcodeCheckByState($arItem['STATUS']))
											):?>
												<a style="text-decoration:none;" href="javascript:void(0);" onclick="<?=PICKPOINT_DELIVERYSERVICE_LBL?>export.getPage('orders').getBarcodes('<?=$arItem['INVOICE_ID']?>');"><?=GetMessage('PICKPOINT_DELIVERYSERVICE_GETBARCODES_LINK')?></a>
										<?php endif;?>
									</td>
									<td style="text-align: center">
										<?php if ($arItem['CANCELED'] && $arItem['INVOICE_ID']) : ?>
											<span style="color: red;"><?= GetMessage('PP_CANCEL_SUCCESS') ?></span>
										<?php elseif($arItem['INVOICE_ID'] && !$arItem['CANCELED']): ?>
											<input type="checkbox" class="cToCancel"
												   name="CANCEL[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
												   autocomplete="off"/>
										<?php else: ?>
											<span style="color: green;"><?= GetMessage('PP_CANCEL_NOT_EXPORT') ?></span>
										<?php endif; ?>
									</td>
									<td style="text-align: center">
										<?php if (!$arItem['CANCELED']) : ?>
											<a target="_blank" style="text-decoration: none" href="/bitrix/admin/pickpoint_edit.php?ORDER_ID=<?= $arItem['ORDER_ID'] ?>"><?= GetMessage('PP_EDIT_LINK') ?></a>
										<?php endif; ?>
									</td>
									<td align="center">
										<input type="checkbox" class="cToArchive"
											   name="ARCHIVE[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
											   autocomplete="off"/>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
                    </table>
                    <?php if (is_array($arItems['FORWARDED']) && (count($arItems['FORWARDED']) > $arOptions['OPTIONS']['show_elements_count'])): ?>
                        <div style="text-align: center; height: 30px">
                            <span class="knopka button_forward" onclick="showAll('forward')">
                                <?= GetMessage('PP_SHOW_MORE') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php $tabControl->BeginNextTab(); ?>
            <div style="text-align: center; height: 30px">
                <div class="show_div">
                    <span><?php echo GetMessage('PP_SHOW_ORDER') ?></span>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 20 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=20">20</a>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 50 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=50">50</a>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 100 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=100">100</a>
                    <a <?= $arOptions['OPTIONS']['show_elements_count'] == 99999999 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=99999999"><?php echo GetMessage('PP_ALL') ?></a>
                </div>
				<?php if ($isRequiredOptionsSet):?>
					<?php if ($isStatusSync)
						echo '<p>'.GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT').' '.$statusLastSync.' '.
							'<a class="knopka" href="'.$APPLICATION->GetCurPage().'?updateStatus=Y">'.GetMessage('PP_UPDATE_BUTTON').'</a></p>';
					else
						echo GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED');
					?>	
				<?php endif;?>
            </div>            <!--        tab3        -->
            <tr>
                <td>
                    <table width="100%" class="edit-table" id="table_export">
                        <tr class="heading">
                            <td><?= GetMessage('PP_ORDER_NUMBER') ?></td>
                            <td><?= GetMessage('PP_INVOICE_ID') ?></td>
                            <td><?= GetMessage('PP_STATUS') ?></td>
                            <td><?= GetMessage('PP_SUMM') ?></td>
                            <td><?= GetMessage('PP_PAYED_BY_PP') ?></td>
                            <td><?= GetMessage('PP_ADDRESS') ?></td>
                            <td><?= GetMessage('PP_SERVICE_TYPE') ?></td>
                            <td><?= GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE') ?></td>
                            <td><?= GetMessage('PP_WIDTH') ?></td>
                            <td><?= GetMessage('PP_HEIGHT') ?></td>
                            <td><?= GetMessage('PP_DEPTH') ?></td>
                            <td><?= GetMessage('PP_ACTION_CANCEL') ?></td>
                            <td><?= GetMessage('PP_ACTION_EDIT') ?></td>
                            <td><?= GetMessage('PP_ARCHIVE_ADD') ?></td>
                        </tr>
						<?php if (is_array($arItems['REVERTED']) && count($arItems['REVERTED'])):?>
							<?php foreach ($arItems['REVERTED'] as $key => $arItem) : ?>
								<?php $arRequestItem = $_REQUEST['EXPORT'][$arItem['ORDER_ID']];
								$bActive = $arItem['INVOICE_ID'] ? false : true;

								$arItem['PAYED'] = $arItem['PRICE'];
								$arItem['PAYED'] = number_format($arItem['PAYED'], 2, '.', '');

								if (in_array($arItem['SETTINGS']['SERVICE_TYPE'], array(1, 3))) {
									$arItem['PAYED_PP_SET'] = 1;
								}
								?>
								<tr class="id_revert" <?= $key > $arOptions['OPTIONS']['show_elements_count'] ? 'style="display:none"' : ''; ?>>
									<td align="center"><?php //= GetMessage('PP_N') ?><?= $arItem['ORDER_NUMBER'] ?> <?= GetMessage('PP_FROM') ?>
										<br/><?= $arItem['ORDER_DATE'] ?></td>
									<td align="center"><?= $arItem['INVOICE_ID'] ? $arItem['INVOICE_ID'] : '' ?></td>
									<td align="center" style="font-weight: bold" title="<?=$arItem['STATUS_TEXT'];?>"><?= $arItem['STATUS']; ?></td>
									<td align="center"><?= CurrencyFormat($arItem['PRICE'], 'RUB') ?>
										<?php if ($bActive) : ?><input type="hidden" id="export_price_<?= $arItem['ORDER_ID'] ?>"
																	   value="<?= $arItem['PRICE'] ?>" /><?php endif; ?></td>
									<td align="center" id="export_payed_price_<?= $arItem['ORDER_ID'] ?>">
										<?php if ($arItem['PAYED_BY_PP'] || $arItem['PAYED_PP_SET']): ?>
											<?= $arItem['PAYED'] ?>
										<?php else : ?>
											<?= GetMessage('PP_NO') ?>
										<?php endif; ?>
									</td>
									<td align="center"><?= $arItem['PP_ADDRESS'] ?></td>
									<td align="center">
										<?php if ($arItem['PAYED_BY_PP']) : ?>
											<?= $arServiceTypes[1] ?>
										<?php else : ?>
											<?= $arServiceTypes[0] ?>
										<?php endif ?>
									</td>
									<td align="center"><?=(intval($arItem['GETTING_TYPE']) > 0) ? $allGettingTypes[$arItem['GETTING_TYPE']] : '-'?></td>
									<td align="center"><?= number_format(floatval($arItem['WIDTH']) , 2); ?></td>
									<td align="center"><?= number_format(floatval($arItem['HEIGHT']) , 2); ?></td>
									<td align="center"><?= number_format(floatval($arItem['DEPTH']) , 2); ?></td>
									<td style="text-align: center">
										<?php if ($arItem['CANCELED'] && $arItem['INVOICE_ID']) : ?>
											<span style="color: red;"><?= GetMessage('PP_CANCEL_SUCCESS') ?></span>
										<?php elseif($arItem['INVOICE_ID'] && !$arItem['CANCELED']): ?>
											<input type="checkbox" class="cToCancel"
												   name="CANCEL[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
												   autocomplete="off"/>
										<?php else: ?>
											<span style="color: green;"><?= GetMessage('PP_CANCEL_NOT_EXPORT') ?></span>
										<?php endif; ?>
									</td>
									<td style="text-align: center">
										<?php if (!$arItem['CANCELED']) : ?>
											<a target="_blank" style="text-decoration: none" href="/bitrix/admin/pickpoint_edit.php?ORDER_ID=<?= $arItem['ORDER_ID'] ?>"><?= GetMessage('PP_EDIT_LINK') ?></a>
										<?php endif; ?>
									</td>
									<td align="center">
										<input type="checkbox" class="cToArchive"
											   name="ARCHIVE[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
											   autocomplete="off"/>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
                    </table>
                    <?php if (is_array($arItems['REVERTED']) && (count($arItems['REVERTED']) > $arOptions['OPTIONS']['show_elements_count'])): ?>
                        <div style="text-align: center; height: 30px">
                            <span class="knopka button_revert" onclick="showAll('revert')">
                                <?= GetMessage('PP_SHOW_MORE') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php $tabControl->BeginNextTab(); ?>
        <div style="text-align: center; height: 30px">
            <div class="show_div">
                <span><?php echo GetMessage('PP_SHOW_ORDER') ?></span>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 20 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=20">20</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 50 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=50">50</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 100 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=100">100</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 99999999 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=99999999"><?php echo GetMessage('PP_ALL') ?></a>
            </div>
			<?php if ($isRequiredOptionsSet):?>
				<?php if ($isStatusSync)
					echo '<p>'.GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT').' '.$statusLastSync.' '.
						'<a class="knopka" href="'.$APPLICATION->GetCurPage().'?updateStatus=Y">'.GetMessage('PP_UPDATE_BUTTON').'</a></p>';
				else
					echo GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED');
				?>	
			<?php endif;?>
        </div>        <!--        tab3        -->
        <tr>
            <td>
                <table width="100%" class="edit-table" id="table_export">
                    <tr class="heading">
                        <td><?= GetMessage('PP_ORDER_NUMBER') ?></td>
                        <td><?= GetMessage('PP_INVOICE_ID') ?></td>
                        <td><?= GetMessage('PP_STATUS') ?></td>
                        <td><?= GetMessage('PP_SUMM') ?></td>
                        <td><?= GetMessage('PP_PAYED_BY_PP') ?></td>
                        <td><?= GetMessage('PP_ADDRESS') ?></td>
                        <td><?= GetMessage('PP_SERVICE_TYPE') ?></td>
                        <td><?= GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE') ?></td>
                        <td><?= GetMessage('PP_WIDTH') ?></td>
                        <td><?= GetMessage('PP_HEIGHT') ?></td>
                        <td><?= GetMessage('PP_DEPTH') ?></td>
                        <td><?= GetMessage('PP_ARCHIVE_ADD') ?></td>
                    </tr>
					<?php if (is_array($arItems['READY']) && count($arItems['READY'])):?>
						<?php foreach ($arItems['READY'] as $key => $arItem) : ?>
							<?php $arRequestItem = $_REQUEST['EXPORT'][$arItem['ORDER_ID']];
							$bActive = $arItem['INVOICE_ID'] ? false : true;

							$arItem['PAYED'] = $arItem['PRICE'];
							$arItem['PAYED'] = number_format($arItem['PAYED'], 2, '.', '');

							if (in_array($arItem['SETTINGS']['SERVICE_TYPE'], array(1, 3))) {
								$arItem['PAYED_PP_SET'] = 1;
							}
							?>
							<tr class="id_ready" <?= $key > $arOptions['OPTIONS']['show_elements_count'] ? 'style="display:none"' : ''; ?>>
								<td align="center"><?php //= GetMessage('PP_N') ?><?= $arItem['ORDER_NUMBER'] ?> <?= GetMessage('PP_FROM') ?>
									<br/><?= $arItem['ORDER_DATE'] ?></td>
								<td align="center"><?= $arItem['INVOICE_ID'] ? $arItem['INVOICE_ID'] : '' ?></td>
								<td align="center" style="font-weight: bold" title="<?=$arItem['STATUS_TEXT'];?>"><?= $arItem['STATUS']; ?></td>
								<td align="center"><?= CurrencyFormat($arItem['PRICE'], 'RUB') ?>
									<?php if ($bActive) : ?><input type="hidden" id="export_price_<?= $arItem['ORDER_ID'] ?>"
																   value="<?= $arItem['PRICE'] ?>" /><?php endif; ?></td>
								<td align="center" id="export_payed_price_<?= $arItem['ORDER_ID'] ?>">
									<?php if ($arItem['PAYED_BY_PP'] || $arItem['PAYED_PP_SET']): ?>
										<?= $arItem['PAYED'] ?>
									<?php else : ?>
										<?= GetMessage('PP_NO') ?>
									<?php endif; ?>
								</td>
								<td align="center"><?= $arItem['PP_ADDRESS'] ?></td>
								<td align="center">
									<?php if ($arItem['PAYED_BY_PP']) : ?>
										<?= $arServiceTypes[1] ?>
									<?php else : ?>
										<?= $arServiceTypes[0] ?>
									<?php endif ?>
								</td>
								<td align="center"><?=(intval($arItem['GETTING_TYPE']) > 0) ? $allGettingTypes[$arItem['GETTING_TYPE']] : '-'?></td>
								<td align="center"><?= number_format(floatval($arItem['WIDTH']) , 2); ?></td>
								<td align="center"><?= number_format(floatval($arItem['HEIGHT']) , 2); ?></td>
								<td align="center"><?= number_format(floatval($arItem['DEPTH']) , 2); ?></td>
								<td align="center">
									<input type="checkbox" class="cToArchive"
										   name="ARCHIVE[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
										   autocomplete="off"/>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
                </table>
                <?php if (is_array($arItems['READY']) && (count($arItems['READY']) > $arOptions['OPTIONS']['show_elements_count'])): ?>
                    <div style="text-align: center; height: 30px">
                        <span class="knopka button_ready" onclick="showAll('ready')">
                            <?= GetMessage('PP_SHOW_MORE') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php $tabControl->BeginNextTab(); ?>
        <div style="text-align: center; height: 30px">
            <div class="show_div">
                <span><?php echo GetMessage('PP_SHOW_ORDER') ?></span>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 20 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=20">20</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 50 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=50">50</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 100 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=100">100</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 99999999 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=99999999"><?php echo GetMessage('PP_ALL') ?></a>
            </div>
			<?php if ($isRequiredOptionsSet):?>
				<?php if ($isStatusSync)
					echo '<p>'.GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT').' '.$statusLastSync.' '.
						'<a class="knopka" href="'.$APPLICATION->GetCurPage().'?updateStatus=Y">'.GetMessage('PP_UPDATE_BUTTON').'</a></p>';
				else
					echo GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED');
				?>	
			<?php endif;?>
        </div>        <!--        tab3        -->
        <tr>
            <td>
                <table width="100%" class="edit-table" id="table_export">
                    <tr class="heading">
                        <td><?= GetMessage('PP_ORDER_NUMBER') ?></td>
                        <td><?= GetMessage('PP_INVOICE_ID') ?></td>
                        <td><?= GetMessage('PP_STATUS') ?></td>
                        <td><?= GetMessage('PP_SUMM') ?></td>
                        <td><?= GetMessage('PP_PAYED_BY_PP') ?></td>
                        <td><?= GetMessage('PP_ADDRESS') ?></td>
                        <td><?= GetMessage('PP_SERVICE_TYPE') ?></td>
                        <td><?= GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE') ?></td>
                        <td><?= GetMessage('PP_WIDTH') ?></td>
                        <td><?= GetMessage('PP_HEIGHT') ?></td>
                        <td><?= GetMessage('PP_DEPTH') ?></td>
                        <td><?= GetMessage('PP_ARCHIVE_ADD') ?></td>
                    </tr>
					<?php if (is_array($arItems['CANCELED']) && count($arItems['CANCELED'])):?>
						<?php foreach ($arItems['CANCELED'] as $key => $arItem) : ?>
							<?php $arRequestItem = $_REQUEST['EXPORT'][$arItem['ORDER_ID']];
							$bActive = $arItem['INVOICE_ID'] ? false : true;

							$arItem['PAYED'] = $arItem['PRICE'];
							$arItem['PAYED'] = number_format($arItem['PAYED'], 2, '.', '');

							if (in_array($arItem['SETTINGS']['SERVICE_TYPE'], array(1, 3))) {
								$arItem['PAYED_PP_SET'] = 1;
							}
							?>
							<tr class="id_cancel" <?= $key > $arOptions['OPTIONS']['show_elements_count'] ? 'style="display:none"' : ''; ?>>
								<td align="center"><?php //= GetMessage('PP_N') ?><?= $arItem['ORDER_NUMBER'] ?> <?= GetMessage('PP_FROM') ?>
									<br/><?= $arItem['ORDER_DATE'] ?></td>
								<td align="center"><?= $arItem['INVOICE_ID'] ? $arItem['INVOICE_ID'] : '' ?></td>
								<td align="center" style="font-weight: bold" title="<?=$arItem['STATUS_TEXT'];?>"><?= $arItem['STATUS']; ?></td>
								<td align="center"><?= CurrencyFormat($arItem['PRICE'], 'RUB') ?>
									<?php if ($bActive) : ?><input type="hidden" id="export_price_<?= $arItem['ORDER_ID'] ?>"
																   value="<?= $arItem['PRICE'] ?>" /><?php endif; ?></td>
								<td align="center" id="export_payed_price_<?= $arItem['ORDER_ID'] ?>">
									<?php if ($arItem['PAYED_BY_PP'] || $arItem['PAYED_PP_SET']): ?>
										<?= $arItem['PAYED'] ?>
									<?php else : ?>
										<?= GetMessage('PP_NO') ?>
									<?php endif; ?>
								</td>
								<td align="center"><?= $arItem['PP_ADDRESS'] ?></td>
								<td align="center">
									<?php if ($arItem['PAYED_BY_PP']) : ?>
										<?= $arServiceTypes[1] ?>
									<?php else : ?>
										<?= $arServiceTypes[0] ?>
									<?php endif ?>
								</td>
								<td align="center"><?=(intval($arItem['GETTING_TYPE']) > 0) ? $allGettingTypes[$arItem['GETTING_TYPE']] : '-'?></td>
								<td align="center"><?= number_format(floatval($arItem['WIDTH']) , 2); ?></td>
								<td align="center"><?= number_format(floatval($arItem['HEIGHT']) , 2); ?></td>
								<td align="center"><?= number_format(floatval($arItem['DEPTH']) , 2); ?></td>
								<td align="center">
									<input type="checkbox" class="cToArchive"
										   name="ARCHIVE[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
										   autocomplete="off"/>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
                </table>
                <?php if (is_array($arItems['CANCELED']) && (count($arItems['CANCELED']) > $arOptions['OPTIONS']['show_elements_count'])): ?>
                    <div style="text-align: center; height: 30px">
                        <span class="knopka button_cancel" onclick="showAll('cancel')">
                            <?= GetMessage('PP_SHOW_MORE') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php $tabControl->BeginNextTab(); ?>
        <div style="text-align: center; height: 30px">
            <div class="show_div">
                <span><?php echo GetMessage('PP_SHOW_ORDER') ?></span>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 20 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=20">20</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 50 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=50">50</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 100 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=100">100</a>
                <a <?= $arOptions['OPTIONS']['show_elements_count'] == 99999999 ? 'style="color:black;font-weight: bold;"' : ''; ?> href="<?= $APPLICATION->GetCurPage() ?>?show=99999999"><?php echo GetMessage('PP_ALL') ?></a>
            </div>
			<?php if ($isRequiredOptionsSet):?>
				<?php if ($isStatusSync)
					echo '<p>'.GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT').' '.$statusLastSync.' '.
						'<a class="knopka" href="'.$APPLICATION->GetCurPage().'?updateStatus=Y">'.GetMessage('PP_UPDATE_BUTTON').'</a></p>';
				else
					echo GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED');
				?>	
			<?php endif;?>
        </div>        <!--        tab3        -->
        <tr>
            <td>
                <table width="100%" class="edit-table" id="table_export">
                    <tr class="heading">
                        <td><?= GetMessage('PP_ORDER_NUMBER') ?></td>
                        <td><?= GetMessage('PP_INVOICE_ID') ?></td>
                        <td><?= GetMessage('PP_STATUS') ?></td>
                        <td><?= GetMessage('PP_SUMM') ?></td>
                        <td><?= GetMessage('PP_PAYED_BY_PP') ?></td>
                        <td><?= GetMessage('PP_ADDRESS') ?></td>
                        <td><?= GetMessage('PP_SERVICE_TYPE') ?></td>
                        <td><?= GetMessage('PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE') ?></td>
                        <td><?= GetMessage('PP_ARCHIVE_DELETE') ?></td>
                    </tr>
					<?php if (is_array($arItems['ARCHIVE']) && count($arItems['ARCHIVE'])):?>
						<?php foreach ($arItems['ARCHIVE'] as $key => $arItem) : ?>
							<?php $arRequestItem = $_REQUEST['EXPORT'][$arItem['ORDER_ID']];
							$bActive = $arItem['INVOICE_ID'] ? false : true;

							$arItem['PAYED'] = $arItem['PRICE'];
							$arItem['PAYED'] = number_format($arItem['PAYED'], 2, '.', '');

							if (in_array($arItem['SETTINGS']['SERVICE_TYPE'], array(1, 3))) {
								$arItem['PAYED_PP_SET'] = 1;
							}
							?>
							<tr class="id_archive" <?= $key > $arOptions['OPTIONS']['show_elements_count'] ? 'style="display:none"' : ''; ?>>
								<td align="center"><?php //= GetMessage('PP_N') ?><?= $arItem['ORDER_NUMBER'] ?> <?= GetMessage('PP_FROM') ?>
									<br/><?= $arItem['ORDER_DATE'] ?></td>
								<td align="center"><?= $arItem['INVOICE_ID'] ? $arItem['INVOICE_ID'] : '' ?></td>
								<td align="center" style="font-weight: bold" title="<?=$arItem['STATUS_TEXT'];?>"><?= $arItem['STATUS']; ?></td>
								<td align="center"><?= CurrencyFormat($arItem['PRICE'], 'RUB') ?>
									<?php if ($bActive) : ?><input type="hidden" id="export_price_<?= $arItem['ORDER_ID'] ?>"
																   value="<?= $arItem['PRICE'] ?>" /><?php endif; ?></td>
								<td align="center" id="export_payed_price_<?= $arItem['ORDER_ID'] ?>">
									<?php if ($arItem['PAYED_BY_PP'] || $arItem['PAYED_PP_SET']): ?>
										<?= $arItem['PAYED'] ?>
									<?php else : ?>
										<?= GetMessage('PP_NO') ?>
									<?php endif; ?>
								</td>
								<td align="center"><?= $arItem['PP_ADDRESS'] ?></td>
								<td align="center">
									<?php if ($arItem['PAYED_BY_PP']) : ?>
										<?= $arServiceTypes[1] ?>
									<?php else : ?>
										<?= $arServiceTypes[0] ?>
									<?php endif ?>
								</td>
								<td align="center"><?=(intval($arItem['GETTING_TYPE']) > 0) ? $allGettingTypes[$arItem['GETTING_TYPE']] : '-'?></td>
								<td align="center">
									<input type="checkbox" class="cToArchive"
										   name="FROMARCHIVE[<?= $arItem['ORDER_ID'] ?>][<?= $arItem['INVOICE_ID'] ?>]"
										   autocomplete="off"/>
								</td>
							 </tr>
						<?php endforeach; ?>
					<?php endif; ?>
                </table>
                <?php if (is_array($arItems['ARCHIVE']) && (count($arItems['ARCHIVE']) > $arOptions['OPTIONS']['show_elements_count'])): ?>
                    <div style="text-align: center; height: 30px">
                        <span class="knopka button_archive" onclick="showAll('archive')">
                            <?= GetMessage('PP_SHOW_MORE') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php $tabControl->Buttons(); ?>
		<?php if ($isRequiredOptionsSet):?>
			<input type="submit" class="adm-btn-save" name="export" onclick="return CheckFields();" value="<?php echo GetMessage('PP_EXPORT_BUTTON') ?>">		   
			<?php if ($isStatusSync):?>
				<a class="knopka" href="<?= $APPLICATION->GetCurPage() ?>?updateStatus=Y"><?php echo GetMessage('PP_UPDATE_BUTTON') ?></a>
			<?php endif;?>
		<?php endif;?>
        <?php
        $tabControl->End();
        $tabControl->ShowWarnings('find_form', $message);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
