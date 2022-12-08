<?
use
	WD\Antirutin\Helper,
	WD\Antirutin\IBlock;

$intMacrosIBlockId = $this->isOfferField($this->get('field')) ? $this->getOffersIBlockId() : $this->intIBlockId;

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
			<textarea name="<?=$this->getInputName('pattern');?>" id="<?=$this->getTextareaId();?>" cols="80" rows="5"
				><?=htmlspecialcharsbx($this->get('pattern'));?></textarea>
		</div>
		<div id="<?=$this->getId('pattern_macro');?>" data-role="macro_wrapper">
			<span><?=static::getMessage('PATTERN_MACRO_TITLE');?></span>
			<select data-role="pattern">
				<?=$this->getMacrosForSelect($intMacrosIBlockId);?>
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
