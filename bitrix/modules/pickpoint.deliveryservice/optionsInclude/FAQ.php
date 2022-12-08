<?php
use \PickPoint\DeliveryService\Bitrix\Tools;
use \Bitrix\Main\Localization\Loc;

// FAQ option tab
?>
<tr class="heading">
	<td colspan="2" valign="top" align="center"><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_FAQ_HDR_MODULE');?></td>
</tr>
<tr>
	<td colspan="2">
        <?php Tools::placeFAQ('ABOUT');?>
	</td>
</tr>
<tr class="heading">
	<td colspan="2" valign="top" align="center"><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_FAQ_HDR_SETUP');?></td>
</tr>
<tr>
	<td colspan="2">
        <?php Tools::placeFAQ('INTRO');?>
        <?php Tools::placeFAQ('ACCOUNT');?>
        <?php Tools::placeFAQ('ORDERPROPS');?>
        <?php Tools::placeFAQ('SENDERREVERT');?>
        <?php Tools::placeFAQ('ZONES');?>
        <?php Tools::placeFAQ('DELIVERY');?>
        <?php Tools::placeFAQ('PAYSYSTEM');?>
	</td>
</tr>
<tr class="heading">
	<td colspan="2" valign="top" align="center"><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_FAQ_HDR_WORK');?></td>
</tr>
<tr>
	<td colspan="2">
        <?php Tools::placeFAQ('SEND');?>
	</td>
</tr>
<tr class="heading">
	<td colspan="2" valign="top" align="center"><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_FAQ_HDR_INFO');?></td>
</tr>
<tr>
	<td colspan="2">
        <?php Tools::placeFAQ('EVENTHANDLERS');?>
        <?php Tools::placeFAQ('UPDATE');?>
        <?php Tools::placeFAQ('HELP');?>
	</td>
</tr>