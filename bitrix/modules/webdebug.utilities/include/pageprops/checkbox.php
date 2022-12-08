<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper,
	\WD\Utilities\PageProp,
	\WD\Utilities\PagePropBase;

Helper::loadMessages();

class PagePropBase_CheckBox extends PagePropBase {
	CONST CODE = 'CHECKBOX';
	CONST NAME = 'Флажок';
	public static function getName() {
		$Name = static::NAME;
		if (Helper::IsUtf()) {
			$Name = $GLOBALS['APPLICATION']->ConvertCharset($Name, 'CP1251', 'UTF-8');
		}
		return $Name;
	}
	public static function getCode() {
		return static::CODE;
	}
	public static function getIcon(){
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAkUlEQVR42mNgQAUWQLwAiBuIxDvQ9DM4QDGxoIEqBlggacyDYgc8mAPdgAVIko5QjEtzAZoLG7D6Aw9wGDADxIDYDZ8BxkAsgEfzJCDmxmeAIBBPw2IIsmaCXhBCMwRdM1FhADJkOhCrAvFkNM0g4ATE+UgGLcAWiCBDerFoxmaABaXRyEAVA3aQkH0XwJwNAwAg0izGRGSf2QAAAABJRU5ErkJggg==';
	}
	public static function getMessage($Item) {
		$arMess = array(
			'NO_SETTINGS' => 'Настройки не требуются.',
		);
		$strResult = $arMess[$Item];
		if (Helper::IsUtf()) {
			$strResult = $GLOBALS['APPLICATION']->ConvertCharset($strResult, 'CP1251', 'UTF-8');
		}
		return $strResult;
	}
	public static function showSettings($PropertyCode, $SiteID) {
		$arFilter = PageProp::GetFilter($PropertyCode, $SiteID);
		$resCurrentProp = PageProp::GetList(false,$arFilter);
		if ($arCurrentItem = $resCurrentProp->GetNext()) {
			$arCurrentItem = static::TransformItem($arCurrentItem);
		}
		if (!is_array($arCurrentItem['DATA'])) {
			$arCurrentItem['DATA'] = array();
		}
		$arCurrentItem['DATA'] = array_merge(array(false), $arCurrentItem['DATA']);
		ob_start();
		?>
			<div id="wd_pageprops_settings_selectbox">
				<?=static::GetMessage('NO_SETTINGS');?>
			</div>
		<?
		return ob_get_clean();
	}
	public static function saveSettings($PropertyCode, $SiteID, $arPost) {
		$arFields = array(
			'PROPERTY' => $PropertyCode,
			'SITE' => $SiteID,
			'TYPE' => static::GetCode(),
			'DATA' => '',
		);
		$arFilter = PageProp::GetFilter($PropertyCode, $SiteID);
		$resCurrentProp = PageProp::GetList(false,$arFilter);
		if ($arCurrentItem = $resCurrentProp->GetNext(false,false)) {
			if (PageProp::Update($arCurrentItem['ID'], $arFields)) {
				return true;
			}
		} else {
			if (PageProp::Add($arFields)) {
				return true;
			}
		}
		return false;
	}
	protected static function transformItem($arItem) {
		$arItem['DATA'] = @unserialize($arItem['~DATA']);
		if (!is_array($arItem['DATA'])) {
			$arItem['DATA'] = array();
		}
		foreach($arItem['DATA'] as $Key => $arOption) {
			if ($Key=='') {
				unset($arItem['DATA'][$Key]);
			}
		}
		return $arItem;
	}
	public static function showControls($arItem, $PropertyCode, $PropertyID, $PropertyValue, $SiteID) {
		$arItem = static::TransformItem($arItem);
		$UniqID = rand(100000000,999999999);
		ob_start();
		?>
			<input type="checkbox" name="PROPERTY[<?=$PropertyID;?>][VALUE]" value="Y" id="wd_pageprops_checkbox_<?=$UniqID;?>"<?if($PropertyValue=='Y'):?> checked="checked"<?endif?> />
			<script>
				BX.adminFormTools.modifyCheckbox(document.getElementById('wd_pageprops_checkbox_<?=$UniqID;?>'));
			</script>
		<?
		$strResult = ob_get_clean();
		return $strResult;
	}
}

?>