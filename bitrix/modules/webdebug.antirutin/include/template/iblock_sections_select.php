<?
namespace WD\Antirutin;

use
	\WD\Antirutin\Helper;

if(!isset($arParams)){
	#// If in demo-mode, 2nd argument is not '$arParams' :( - this looks like $_1565435424
	# So, we make hack in Helper::includeFile(): $GLOBALS['arParams'] = $arParams;
	global $arParams;
}

$arValues = is_array($arParams['SECTIONS_ID_SELECTED']) ? $arParams['SECTIONS_ID_SELECTED'] : 
	(is_numeric($arParams['SECTIONS_ID_SELECTED']) ? [$arParams['SECTIONS_ID_SELECTED']] : '');
?>
<?if(isset($arParams['PLACEHOLDER'])):?>
	<option value=""<?if(empty($arValues)):?> selected="selected"<?endif?>><?
		print $arParams['PLACEHOLDER'];
	?></option>
<?endif?>1
<?if(is_array($arParams['SECTIONS_ID'])):?>2
	<?foreach($arParams['SECTIONS_ID'] as $arSection):?>3
		<?$bSelected = in_array($arSection['ID'], $arValues);?>
		<option value="<?=$arSection['ID'];?>"<?if($bSelected):?> selected="selected"<?endif?>><?
			print str_repeat('&nbsp;', ($arSection['DEPTH_LEVEL'] - 1) * 4);
			print $arSection['NAME'].' ['.$arSection['ID'].']';
		?></option>
	<?endforeach?>
<?endif?>