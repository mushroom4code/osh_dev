<?
use
	WD\Antirutin\Helper,
	WD\Antirutin\IBlock;

?>

<div class="plugin-form__field">
	<div class="plugin-form__field-title">
		<?=$this->fieldName('ACTION', true);?>
	</div>
	<div class="plugin-form__field-value">
		<div id="<?=$this->getId('format');?>">
			<?=Helper::selectBox($this->getInputName('action'), [
				'set' => static::getMessage('ACTION_SET'),
				'change' => static::getMessage('ACTION_CHANGE'),
				'add' => static::getMessage('ACTION_ADD'),
				'delete' => static::getMessage('ACTION_DELETE'),
			], $this->get('action'), static::getMessage('ACTION_EMPTY'), 'data-role="action"');?>
		</div>
	</div>
</div>

<div class="plugin-form__field">
	<div class="plugin-form__field-title">
		<?=$this->fieldName('SECTION', true);?>
	</div>
	<div class="plugin-form__field-value">
		<div data-role="select_section">
			<div data-role="filter_wrapper">
				<input type="text" data-role="filter"
					placeholder="<?=static::getMessage('SECTION_FILTER_PLACEHOLDER')?>" />
			</div>
			<div data-role="select_wrapper">
				<select name="<?=$this->getInputName('section');?>" id="<?=$this->getId('section');?>" size="12"
					data-role="section">
					<?=Helper::includeFile('iblock_sections_select', [
						'ENTITY_TYPE' => Helper::TYPE_ELEMENT,
						'IBLOCK_ID' => $this->intIBlockId,
						'SECTIONS_ID' => IBlock::getIBlockSections($this->intIBlockId, $intMaxDepth=5),
						'SECTIONS_ID_SELECTED' => $this->get('section'),
						'PLACEHOLDER' => static::getMessage('SECTION_VALUE_EMPTY'),
					])?>
				</select>
			</div>
		</div>
	</div>
</div>

<input type="hidden" data-role="error_no_action" value="<?=static::getMessage('ERROR_NO_ACTION');?>" />
<input type="hidden" data-role="error_no_section" value="<?=static::getMessage('ERROR_NO_SECTION');?>" />
