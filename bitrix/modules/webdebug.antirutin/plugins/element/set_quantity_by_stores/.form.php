<?
use
	WD\Antirutin\Helper,
	WD\Antirutin\IBlock;

?>

<div class="plugin-form__field">
	<div class="plugin-form__field-title">
		<?=$this->fieldName('MODE', true);?>
	</div>
	<div class="plugin-form__field-value">
		<div id="<?=$this->getId('mode');?>">
			<?=Helper::selectBox($this->getInputName('mode'), [
				'product' => static::getMessage('MODE_PRODUCT'),
				'offers' => static::getMessage('MODE_OFFERS'),
				'all' => static::getMessage('MODE_ALL'),
			], $this->get('mode'), null, 'data-role="mode"');?>
		</div>
	</div>
</div>

<div class="plugin-form__field">
	<div class="plugin-form__field-title">
		<?=$this->fieldName('STORES', true);?>
	</div>
	<div class="plugin-form__field-value">
		<div id="<?=$this->getId('stores');?>">
			<?=IBlock::showAvailableFields($this->getFields(), $this->strEntityType, $this->getInputName('stores', true),
				$this->get('stores'), 'multiple="multiple" size="8" data-role="stores"', true, false, false, false);?>
		</div>
	</div>
</div>

<input type="hidden" data-role="error_no_stores" value="<?=static::getMessage('ERROR_NO_STORES');?>" />
