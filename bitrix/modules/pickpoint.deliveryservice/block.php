<?php
IncludeModuleLangFile(__FILE__);
/** @global string $widgetParamsString */
/** @global string $orderPhone */

$inSession    = array_key_exists('PICKPOINT', $_SESSION) && is_array($_SESSION['PICKPOINT']);
$displayValue = ($inSession && strlen($_SESSION['PICKPOINT']['PP_NAME']) > 0) ? 'block' : 'none';
$smsPhone     = htmlspecialcharsbx(($inSession && array_key_exists('PP_SMS_PHONE', $_SESSION['PICKPOINT']) && $_SESSION['PICKPOINT']['PP_SMS_PHONE']) ? $_SESSION['PICKPOINT']['PP_SMS_PHONE'] : '');
?>
<div class="bx_result_price">
	<a class="btn btn-default" onclick="PickPoint.open(PickpointDeliveryservice.widgetHandler<?php if ($widgetParamsString):?><?=$widgetParamsString?><?php endif;?>); return false;" style="cursor:pointer;">
		<?=GetMessage('PP_CHOOSE')?>
	</a>
</div>
<table id="tPP" onclick="return false;" style="display:<?=$displayValue?>;">
	<tr>
		<td><?=GetMessage('PP_DELIVERY_IN_PLACE')?>:</td>
		<td>
			<span id="sPPDelivery"><?=(($inSession && $_SESSION['PICKPOINT']['PP_ADDRESS']) ? $_SESSION['PICKPOINT']['PP_ADDRESS']."<br/>".$_SESSION['PICKPOINT']['PP_NAME'] : GetMessage('PP_sNONE'))?></span>
		</td>
	</tr>
	<tr>
		<td></td>
	</tr>
    <?php if (!$orderPhone):?>
	<tr>
		<td><?=GetMessage('PP_SMS_PHONE')?>:</td>
		<td>
			<input type="text" name="PP_SMS_PHONE" value="<?=$smsPhone?>" id="pp_sms_phone" style="width: 100%;" />
			<br/><?=GetMessage('PP_EXAMPLE')?>: +79160000000
			<input type="hidden" id="pp_phone_in_prop" value="N">
		</td>
	</tr>
    <?php else:?>
	    <input type="hidden" id="pp_phone_in_prop" value="Y">
	    <input type="hidden" name="PP_SMS_PHONE" value="<?=$smsPhone?>" id="pp_sms_phone"/>
    <?php endif;?>
</table>