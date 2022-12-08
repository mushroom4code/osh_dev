<?
use
	\WD\Utilities\Helper,
	\WD\Utilities\GotoObject;

$ModuleID = 'webdebug.utilities';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
#
CModule::IncludeModule($ModuleID);
IncludeModuleLangFile(__FILE__);
#
$arTypes = GotoObject::getTypes();
$strCurrentType = key($arTypes);
#
$strType = htmlspecialcharsbx($_GET['type']);
$strVariant = htmlspecialcharsbx($_GET['variant']);
$strID = htmlspecialcharsbx($_GET['id']);
if(!defined('BX_UTF') || BX_UTF!==true){
	$strID = $GLOBALS['APPLICATION']->ConvertCharset($strID, 'UTF-8', 'CP1251');
}
#
$bSearch = $_GET['action'] == 'search' && strlen($strID);
#
if(strlen($strType)){
	CUserOptions::SetOption($ModuleID, 'goto_last_type', $strType);
	CUserOptions::SetOption($ModuleID, 'goto_last_variant_'.$strType, $strVariant);
}
else{
	$strType = CUserOptions::GetOption($ModuleID, 'goto_last_type');
}
#
if(strlen($strType)){
	$strCurrentType = $strType;
}
#
$arSearchResults = array();
if($bSearch){
	$arParams = array(
		'VARIANT' => $strVariant,
	);
	$arSearchResults = GotoObject::search($strType, $strID, $arParams);
}
#
function wduGotoDisplayElement($arElement){
	if(is_array($arElement['_INFO'])){
		foreach($arElement['_INFO'] as $key => $value){
			$arElement['_INFO'][$key] = '<b>'.$key.'</b>: '.$value;
		}
		$arElement['_INFO'] = implode(', ', $arElement['_INFO']);
	}
	ob_start();
	?>
	<li class="wdu-goto-item">
		<div class="wdu-goto-item-name"><a href="<?=$arElement['_URL'];?>"><?=$arElement['NAME'];?></a></div>
		<?if(strlen($arElement['_INFO'])):?>
			<div class="wdu-goto-item-info">
				<?=$arElement['_INFO'];?>
			</div>
		<?endif?>
	</li>
	<?
	return ob_get_clean();
}
?>

<form action="<?=$_SERVER['PHP_SELF'];?>" id="wdu-goto-form" method="post" class="form">
	<div>
		<div class="wdu-goto-links">
			<?foreach($arTypes as $strType => $arType):?>
				<input type="radio" name="TYPE" value="<?=$strType;?>" id="wdu-goto-type-<?=$strType;?>"
					class="wdu-goto-type" <?if($strType==$strCurrentType):?>checked="checked"<?endif?>/>
				<label for="wdu-goto-type-<?=$strType;?>">
					<span style="background-image:url('/bitrix/themes/.default/images/webdebug.utilities/popup_goto/<?=$arType['ICON']?>.png');"></span>
					<?=$arType['NAME'];?>
				</label>
			<?endforeach?>
		</div>
		<div>
			<input type="text" size="45" value="<?=$strID;?>" maxlength="255" placeholder="<?=GetMessage('WDU_POPUP_GOTO_PLACEHOLDER');?>" 
				class="wdu-goto-input" id="wdu-goto-input" />
			<?foreach($arTypes as $strType => $arType):?>
				<span class="wdu-goto-variants" id="wdu-goto-variants-<?=$strType;?>"<?if($strType!=$strCurrentType):?> style="display:none"<?endif?>>
					<?if(is_array($arType['VARIANTS'])):?>
						<?
						$strCurrentVariant = CUserOptions::GetOption($ModuleID, 'goto_last_variant_'.$strType);
						$strCurrentTypeVariant = strlen($strCurrentVariant) && array_key_exists($strCurrentVariant, $arType['VARIANTS']) ? $strCurrentVariant : $arType['DEFAULT_VARIANT'];
						?>
						<?foreach($arType['VARIANTS'] as $strVariantCode => $strVariantName):?>
							<label>
								<input type="radio" name="<?=$strType;?>.VARIANT" value="<?=$strVariantCode;?>" 
								<?if($strVariantCode == $strCurrentTypeVariant):?>checked="checked"<?endif?>/>
								<span><?=$strVariantName;?></span>
							</label>
						<?endforeach?>
					<?endif?>
				</span>
			<?endforeach?>
		</div>
		<br/>
		<?if($bSearch):?>
			<div class="wdu-form-results">
				<?if(is_array($arSearchResults) && !empty($arSearchResults)):?>
					<ul>
					<?
						$bSingle = count($arSearchResults) === 1;
						foreach($arSearchResults as $arSearchResult){
							print wduGotoDisplayElement($arSearchResult);
							if($bSingle){
								?><script>window.open('<?=$arSearchResult['_URL'];?>'); wduPopupGoTo.Close();</script><?
							}
						}
						?>
					</ul>
				<?else:?>
					<div class="wdu-goto-not-found"><?=GetMessage('WDU_GOTO_NOTHING_FOUND');?></div>
				<?endif?>
			</div>
		<?endif?>
	</div>
	<div style="display:none"><input type="submit" value="" /></div>
</form>
<script>
var wduGotoForm = BX('wdu-goto-form');
BX.bind(wduGotoForm, 'submit', BX.proxy(function(e){
	e.preventDefault();
	var inputsRadio = BX.findChild(BX('wdu-goto-form'), {'tagName': 'input'}, true, true);
	var type = null;
	var variant = null;
	for(var i in inputsRadio){
		if(inputsRadio[i].checked){
			type = inputsRadio[i].value;
			var inputsVariants = BX.findChild(BX('wdu-goto-variants-'+type), {'tagName': 'input'}, true, true);
			for(var i in inputsVariants){
				if(inputsVariants[i].checked){
					variant = inputsVariants[i].value;
				}
			}
			break;
		}
	}
	var id = encodeURIComponent(BX('wdu-goto-input').value);
	jsAjaxUtil.LoadData('/bitrix/admin/wdu_goto.php?action=search&type='+type+'&variant='+variant+'&id='+id+'&lang=' + phpVars.LANGUAGE_ID + '&' + Math.random(), wduPopupGotoCallback);
}));
setTimeout(function(){
	BX('wdu-goto-input').focus();
	BX('wdu-goto-input').select();
},10);
var wduGotoVariants = BX.findChild(wduGotoForm, {'class': 'wdu-goto-variants'}, true, true);
/*
for(var i in wduGotoVariants){
	wduGotoVariants[i].style.display = 'none';
}
*/
var wduGotoTypes = BX.findChild(wduGotoForm, {'class': 'wdu-goto-type'}, true, true);
for(var i in wduGotoTypes){
	BX.bind(wduGotoTypes[i], 'click', function(e){
		for(var i in wduGotoVariants){
			wduGotoVariants[i].style.display = 'none';
		}
		BX('wdu-goto-variants-' + this.value).style.display = 'inline-block';
	});
}
</script>