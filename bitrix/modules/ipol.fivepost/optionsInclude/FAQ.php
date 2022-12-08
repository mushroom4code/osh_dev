<?
use Ipol\Fivepost\Bitrix\Tools as Tools;
// блоки FAQ приведены для примера
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=Tools::getMessage('FAQ_HDR_MODULE')?></td></tr>
<tr><td colspan="2">
    <?Tools::placeFAQ('ABOUT')?>
    <?Tools::placeFAQ('HIW')?>
</td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=Tools::getMessage('FAQ_HDR_ABOUT')?></td></tr>
<tr><td colspan="2">
    <?Tools::placeFAQ('TURNINGON')?>
    <?Tools::placeFAQ('DELIVERYSETUPS')?>
    <?Tools::placeFAQ('SENDINGORDER')?>
    <?Tools::placeFAQ('SUNC')?>
</td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=Tools::getMessage('FAQ_HDR_ADDITIONAL')?></td></tr>
<tr><td colspan="2">
    <?Tools::placeFAQ('STICKERS')?>
    <?Tools::placeFAQ('MULTICITES')?>
    <?Tools::placeFAQ('DIFFERENTRATYTYPES')?>
    <?Tools::placeFAQ('EVENTHANDLERS')?>
</td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=Tools::getMessage('FAQ_HDR_HELP')?></td></tr>
<tr><td colspan="2">
    <?Tools::placeFAQ('TESTMODE')?>
    <?Tools::placeFAQ('DELIVERYSTUFF')?>
    <?Tools::placeFAQ('TROUBLES')?>
    <?Tools::placeFAQ('UPDATES')?>
</td></tr>