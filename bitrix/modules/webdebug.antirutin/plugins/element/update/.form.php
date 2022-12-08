<?
use
	WD\Antirutin\Helper,
	WD\Antirutin\IBlock;

?>

<div class="plugin-form__field">
	<span>
		<input type="hidden" name="<?=$this->getInputName('catalog_product_update');?>" value="N" />
		<input type="checkbox" name="<?=$this->getInputName('catalog_product_update');?>" value="Y" 
			<?if($this->get('catalog_product_update') == 'Y'):?>checked="checked"<?endif?> 
			id="<?=$this->getId('catalog_product_update');?>" data-role="catalog_product_update" />
	</span>
	<label for="<?=$this->getId('catalog_product_update');?>">
		<?=$this->fieldName('CATALOG_PRODUCT_UPDATE', true, false);?>
	</label>
</div>

<div class="plugin-form__field">
	<span>
		<input type="hidden" name="<?=$this->getInputName('iblock_element_update');?>" value="N" />
		<input type="checkbox" name="<?=$this->getInputName('iblock_element_update');?>" value="Y" 
			<?if($this->get('iblock_element_update') == 'Y'):?>checked="checked"<?endif?> 
			id="<?=$this->getId('iblock_element_update');?>" data-role="iblock_element_update" />
	</span>
	<label for="<?=$this->getId('iblock_element_update');?>">
		<?=$this->fieldName('IBLOCK_ELEMENT_UPDATE', true, false);?>
	</label>
	<div class="plugin-form__group">
		<div class="plugin-form__field">
			<span>
				<input type="hidden" name="<?=$this->getInputName('iblock_element_update_with_fields');?>" value="N" />
				<input type="checkbox" name="<?=$this->getInputName('iblock_element_update_with_fields');?>" value="Y" 
					<?if($this->get('iblock_element_update_with_fields') == 'Y'):?>checked="checked"<?endif?> 
					id="<?=$this->getId('iblock_element_update_with_fields');?>" data-role="iblock_element_update_with_fields" />
			</span>
			<label for="<?=$this->getId('iblock_element_update_with_fields');?>">
				<?=$this->fieldName('IBLOCK_ELEMENT_UPDATE_WITH_FIELDS', true, false);?>
			</label>
		</div>
	</div>
</div>

<input type="hidden" data-role="error_nothing_selected" value="<?=static::getMessage('ERROR_NOTHING_SELECTED');?>" />
