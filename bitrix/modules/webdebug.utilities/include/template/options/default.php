<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

$arOptions = &$arParams['OPTIONS'];
$obOptions = &$arParams['THIS'];
$strOptionIdPrefix = $obOptions->getOptionPrefix();
?>
<?foreach($arOptions as $arGroup):?>
	<tr class="heading">
		<td colspan="2"><?=$arGroup['NAME'];?><?if($arGroup['HINT']):?> <?=Helper::showHint($arGroup['HINT']);?><?endif?></td>
	</tr>
	<?foreach($arGroup['OPTIONS'] as $strOption => $arOption):?>
		<?
		$strOptionId = $strOptionIdPrefix.$strOption;
		$arOption['CODE'] = $strOption;
		$arOption['VALUE'] = $strValue = $arOption['VALUE'];//$obOptions->getOptionValue($strOption);
		?>
		<tr id="<?=$strOptionIdPrefix;?>row_<?=$strOption;?>">
			<td width="40%"<?if($arOption['TOP'] == 'Y'):?> style="padding-top:10px; vertical-align:top;"<?endif?>>
				<?=Helper::showHint($arOption['HINT']);?>
				<label for="<?=$strOptionIdPrefix;?><?=$strOption;?>">
					<?if($arOption['REQUIRED']):?>
						<b><?=$arOption['NAME'];?></b>:
					<?else:?>
						<?=$arOption['NAME'];?>:
					<?endif?>
				</label>
			</td>
			<td width="40%">
				<?
				if(is_callable($arOption['CALLBACK_MAIN'])){
					call_user_func_array($arOption['CALLBACK_MAIN'], [$obOptions, $arOption, $strOption, $strOptionId]);
				}
				else{
					switch($arOption['TYPE']) {
						case 'text':
							?>
							<input type="text" name="<?=$strOption;?>" value="<?=$strValue;?>" <?=$arOption['ATTR'];?> 
								id="<?=$strOptionIdPrefix;?><?=$strOption;?>" />
							<?
							break;
						case 'password':
							?>
							<input type="password" name="<?=$strOption;?>" value="<?=$strValue;?>" <?=$arOption['ATTR'];?> 
								id="<?=$strOptionIdPrefix;?><?=$strOption;?>" />
							<?
							break;
						case 'textarea':
							?>
							<textarea name="<?=$strOption;?>" <?=$arOption['ATTR'];?>
								id="<?=$strOptionIdPrefix;?><?=$strOption;?>"><?=$strValue;?></textarea>
							<?
							break;
						case 'checkbox':
							if(stripos($arOption['ATTR'], 'disabled') !== false){
								$strValue = 'N';
							}
							?>
							<input type="hidden" name="<?=$strOption;?>" value="N" />
							<input type="checkbox" name="<?=$strOption;?>" value="Y" <?=$arOption['ATTR'];?>
								id="<?=$strOptionIdPrefix;?><?=$strOption;?>"
								<?if($strValue=='Y'):?> checked="checked"<?endif?> />
							<?
							break;
						case 'select':
							?>
							<select name="<?=$strOption;?>" id="<?=$strOptionIdPrefix;?><?=$strOption;?>" <?=$arOption['ATTR'];?>>
								<?if(is_array($arOption['VALUES'])):?>
									<?foreach($arOption['VALUES'] as $strOptionValue => $strOptionText):?>
										<option value="<?=$strOptionValue;?>"
											<?if($strOptionValue == $strValue):?> selected="selected"<?endif?>><?=$strOptionText;?></option>
									<?endforeach?>
								<?endif?>
							</select>
							<?
							break;
					}
				}
				if(is_callable($arOption['CALLBACK_MORE'])){
					print call_user_func_array($arOption['CALLBACK_MORE'], [$obOptions, $arOption, $strOption, $strOptionId]);
				}
				?>
			</td>
		</tr>
		<?
		if(is_callable($arOption['CALLBACK_BOTTOM'])){
			print call_user_func_array($arOption['CALLBACK_BOTTOM'], [$obOptions, $arOption, $strOption, $strOptionId]);
		}
		?>
	<?endforeach?>
<?endforeach?>