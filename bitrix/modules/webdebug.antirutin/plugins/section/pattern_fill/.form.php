<?
use
	WD\Antirutin\Helper,
	WD\Antirutin\IBlock;

$arCodeConfig = Helper::getIBlockFields($this->intIBlockId, 'CODE', true);

$strObjectName = 'window.wdaInheritedPropertiesTemplates';
$strObjectFunc = 'insertIntoInheritedPropertiesTemplate';
$strTextareaId = 'pattern_'.$this->getId();

$arMacros = \CIBlockParameters::GetInheritedPropertyTemplateSectionMenuItems(2, $strObjectName.'.'.$strObjectFunc, '', $strTextareaId);
foreach($arMacros as $key1 => $arGroup){
	foreach($arGroup['MENU'] as $key2 => $arItem){
		break;
	}
	if(is_array($arItem)){
		$arItem['TEXT'] = 'ID';
		$arItem['ONCLICK'] = preg_replace('#({.*?})#', static::MACRO_ID, $arItem['ONCLICK'], 1);
		$arMacros[$key1]['MENU'] = array_merge([$arItem], $arGroup['MENU']);
		break;
	}
}

?>

<div class="plugin-form__field">
	<div class="plugin-form__field-title">
		<?=$this->fieldName('FIELD', true);?>
	</div>
	<div class="plugin-form__field-value">
		<div id="<?=$this->getId('field');?>">
			<?=IBlock::showAvailableFields($this->getFields(), $this->strEntityType, $this->getInputName('field'),
				$this->get('field'), 'data-role="field"', true);?>
		</div>
	</div>
</div>

<div class="plugin-form__field">
	<div class="plugin-form__field-title">
		<?=$this->fieldName('PATTERN', true);?>
	</div>
	<div class="plugin-form__field-value">
		<div id="<?=$this->getId('pattern');?>"data-role="pattern_wrapper">
			<textarea name="<?=$this->getInputName('pattern');?>" id="<?=$strTextareaId;?>" cols="80" rows="5"
				><?=htmlspecialcharsbx($this->get('pattern'));?></textarea>
		</div>
		<div id="<?=$this->getId('pattern_macro');?>" data-role="macro_wrapper">
			<span><?=static::getMessage('PATTERN_MACRO_TITLE');?></span>
			<select data-role="pattern">
				<option value="">---</option>
				<?foreach($arMacros as $arGroup):?>
					<?if(!empty($arGroup['MENU'])):?>
						<optgroup label="<?=htmlspecialcharsbx($arGroup['TEXT']);?>" data-code="<?=$strGroup;?>">
							<?foreach($arGroup['MENU'] as $arItem):?>
								<option value="<?=htmlspecialcharsbx($arItem['ONCLICK']);?>"><?=$arItem['TEXT'];?></option>
							<?endforeach?>
						</optgroup>
					<?endif?>
				<?endforeach?>
			</select>
			<?=$this->fieldHint('PATTERN_MACRO');?>
			<script>
			wdaSelect2($('#<?=$this->getId('pattern_macro');?> > select'), {
				dropdownParent: $('#<?=$this->getId('pattern_macro');?>'),
			});
			</script>
		</div>
	</div>
</div>

<input type="hidden" data-role="error_no_field" value="<?=static::getMessage('ERROR_NO_FIELD');?>" />
