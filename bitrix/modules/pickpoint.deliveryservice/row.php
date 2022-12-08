<?php
IncludeModuleLangFile(__FILE__);
$arTypes = array('ANOTHER', 'USER', 'ORDER', 'PROPERTY');
/** @global array $arRow */
/** @global array $val */
/** @global array $arFields */
?>
<tr>
    <td><?=$arRow['NAME']?></td>
    <td>
        <?php $iNum = 0; ?>
        <?php foreach ($arRow['SELECTED'] as $iSelectedKey => $arSelectedValue) :?>
            <?php $iNum++?>
            <?php if ($iNum > 1):?><div class = "dAdded"><?php endif; ?>
                <table cellspacing="2" cellpadding="0" border="0" class = "tType">
                    <tr>
                        <td><?=GetMessage('PP_TYPE')?></td>
                        <td>
                            <select onchange="PropertyTypeChange(this,<?=$val['ID']?>)" id="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][TYPE]" name="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][TYPE]">
                                <?php foreach ($arTypes as $sType):?>
                                    <option value="<?=$sType?>" <?=($arSelectedValue['TYPE'] == $sType) ? 'selected' : ''?>><?=GetMessage("PP_{$sType}")?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?=GetMessage('PP_VALUE')?></td>
                        <td>
                            <?php if ($arSelectedValue['TYPE'] == 'ANOTHER'):?>
                                <select style="display: none;" id="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE]" name="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE]"></select>
                                <input style = "display:block;" id = "OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE_ANOTHER]" name = "OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE_ANOTHER]" type = "text" value = "<?=$arSelectedValue['VALUE']?>"/>
                            <?php elseif ($arSelectedValue['TYPE'] == 'PROPERTY'):?>
                                <select style="display: block;" id="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE]" name="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE]">
                                    <?php foreach ($arFields[$arSelectedValue['TYPE']][$val['ID']] as $sKey => $sValue):?>
                                        <option value = "<?=$sKey?>" <?=($arSelectedValue['VALUE'] == $sKey) ? 'selected' : ''?>><?=$sValue?> (id = <?=$sKey?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <input style = "display:none;" type = "text" id = "OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE_ANOTHER]" name = "OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE_ANOTHER]" value = ""/>
                            <?php else:?>
                                <select style="display: block;" id="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE]" name="OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE]">
                                    <?php foreach ($arFields[$arSelectedValue['TYPE']] as $sKey => $sValue):?>
                                        <option value = "<?=$sKey?>" <?=($arSelectedValue['VALUE'] == $sKey) ? 'selected' : ''?>><?=$sValue?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input style = "display:none;" type = "text" id = "OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE_ANOTHER]" name = "OPTIONS[<?=$val['ID']?>][<?=$arRow['CODE']?>][<?=$iSelectedKey?>][VALUE_ANOTHER]" value = ""/>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($iNum > 1):?><tr><td colspan="2"><a onclick = 'DeleteTable(this)' class = 'aDelete'><?=GetMessage('PP_DELETE')?></a></div></td></tr><?php endif; ?>
                </table>
        <?php endforeach; ?>
        <?php if ($arRow['CODE'] == 'ADDITIONAL_PHONES'):?>
            <p align = "right">
                <input type = "button" value = "<?=GetMessage('PP_MORE')?>" onclick = "AddTable('<?=$arRow['CODE']?>',<?=$val['ID']?>,this)"/>
            </p>
        <?php endif; ?>
    </td>
</tr>
