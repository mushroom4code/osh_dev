<?
namespace WD\Antirutin;

$bAllowMultiply = false;

$bWithDescription = $arField['DATA']['WITH_DESCRIPTION'] == 'Y' || in_array($strField, ['PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PICTURE']);

$strInputNameValue = $this->getInputName(static::INPUT_VALUE, true);
$strInputNameDescr = $this->getInputName(static::INPUT_DESCRIPTION, true);
$strFiledropTitle = \WD\Antirutin\Helper::getMessage('WDA_FILEDROP_TITLE');

$arFiles = [];
foreach($arValues as $intValueKey => $strValue){
	if(strlen(trim($strValue))){
		$arFile = [
			'name' => $strValue,
			'size' => filesize($_SERVER['DOCUMENT_ROOT'].$strValue),
			'type' => mime_content_type($_SERVER['DOCUMENT_ROOT'].$strValue),
			'file' => $_SERVER['DOCUMENT_ROOT'].$strValue,
		];
		if($bWithDescription){
			$arFile['description'] = $arDescriptions[$intValueKey];
		}
		$arFiles[] = $arFile;
	}
}
$strFilesJson = \Bitrix\Main\Web\Json::encode($arFiles);
?>
<style>
table[data-role="field_items"] {width:100%;}
</style>
<div data-role="wda_filedrop" data-field="<?=$strField;?>"></div>
<script>
$('#<?=$this->getId();?> [data-role="wda_filedrop"]').each(function(){
	var config = {
		ajaxUrl: location.href + (location.href.indexOf('?') == -1 ? '?' : '&') + 'ajax_action=file_upload',
		files: <?=$strFilesJson;?>,
		inputNameValue: '<?=$strInputNameValue;?>',
		inputNameDescr: '<?=$strInputNameDescr;?>',
		caption: '<?=$strFiledropTitle;?>',
		multiple: <?=($bMultiple ? 'true' : 'false');?>,
		withDescription: <?=($bWithDescription ? 'true' : 'false');?>
	};
	$(this).fileDrop(config);
});
</script>
<?=Helper::showNote(static::getMessage('TMP_FILE_NOTE'));?>
