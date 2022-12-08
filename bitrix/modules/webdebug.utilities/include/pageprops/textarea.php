<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper,
	\WD\Utilities\PagePropBase;

Helper::loadMessages();

class PagePropBase_TextArea extends PagePropBase {
	CONST CODE = 'TEXTAREA';
	CONST NAME = 'Текстовая область';
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
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAATklEQVR42mNgQIAQIP4PxSEMZII1UEwSgNkagGQAsmsIYTDRA8RSSAZIQw3Bh3uQDQghwwshowYMRgNITcpYXUAKoK4BPUQkXbxJmWwMALnCVrLws0VPAAAAAElFTkSuQmCC';
	}
	public static function getMessage($Item) {
		$arMess = array(
			'OPTION_COLUMN_PARAM' => 'Параметр',
			'OPTION_COLUMN_VALUE' => 'Значение',
			'OPTION_COLS' => 'Кол-во столбцов',
			'OPTION_ROWS' => 'Кол-во строк',
			'OPTION_RESIZE_Y' => 'Ручное растягивание в высоту',
			'OPTION_RESIZE_X' => 'Ручное растягивание в ширину',
			'OPTION_RESIZE_FULL' => 'Автоматическое растягивание на всю ширину',
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
		ob_start();
		?>
			<style>
			#wd_pageprops_settings_textarea .adm-list-table-cell {padding-left:12px; padding-right:12px;}
			#wd_pageprops_settings_textarea input[type=text] {width:100%; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; box-sizing:border-box;}
			</style>
			<div id="wd_pageprops_settings_filesite">
				<table class="adm-list-table">
					<tbody>
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell" style="width:40%;">
								<div class="adm-list-table-cell-inner"><?=static::GetMessage('OPTION_COLUMN_PARAM');?></div>
							</td>
							<td class="adm-list-table-cell">
								<div class="adm-list-table-cell-inner"><?=static::GetMessage('OPTION_COLUMN_VALUE');?></div>
							</td>
						</tr>
						<tr class="adm-list-table-row">
							<td class="adm-list-table-cell align-right adm-detail-valign-middle">
								<label for="wd_propspage_textarea_cols"><?=static::GetMessage('OPTION_COLS');?>:</label>
							</td>
							<td class="adm-list-table-cell align-left">
								<input type="text" name="data[cols]" id="wd_propspage_textarea_cols" value="<?=htmlspecialcharsbx($arCurrentItem['DATA']['cols']);?>" size="50" />
							</td>
						</tr>
						<tr class="adm-list-table-row">
							<td class="adm-list-table-cell align-right adm-detail-valign-middle">
								<label for="wd_propspage_textarea_rows"><?=static::GetMessage('OPTION_ROWS');?>:</label>
							</td>
							<td class="adm-list-table-cell align-left">
								<input type="text" name="data[rows]" id="wd_propspage_textarea_rows" value="<?=htmlspecialcharsbx($arCurrentItem['DATA']['rows']);?>" size="50" />
							</td>
						</tr>
						<tr class="adm-list-table-row">
							<td class="adm-list-table-cell align-right adm-detail-valign-middle">
								<label for="wd_propspage_textarea_resize_y"><?=static::GetMessage('OPTION_RESIZE_Y');?>:</label>
							</td>
							<td class="adm-list-table-cell align-left">
								<input type="checkbox" name="data[resize_y]" id="wd_propspage_textarea_resize_y" value="Y"<?if($arCurrentItem['DATA']['resize_y']=='Y'):?> checked="checked"<?endif?> />
							</td>
						</tr>
						<tr class="adm-list-table-row">
							<td class="adm-list-table-cell align-right adm-detail-valign-middle">
								<label for="wd_propspage_textarea_resize_x"><?=static::GetMessage('OPTION_RESIZE_X');?>:</label>
							</td>
							<td class="adm-list-table-cell align-left">
								<input type="checkbox" name="data[resize_x]" id="wd_propspage_textarea_resize_x" value="Y"<?if($arCurrentItem['DATA']['resize_x']=='Y'):?> checked="checked"<?endif?> />
							</td>
						</tr>
						<tr class="adm-list-table-row">
							<td class="adm-list-table-cell align-right adm-detail-valign-middle">
								<label for="wd_propspage_textarea_resize_full"><?=static::GetMessage('OPTION_RESIZE_FULL');?>:</label>
							</td>
							<td class="adm-list-table-cell align-left">
								<input type="checkbox" name="data[resize_full]" id="wd_propspage_textarea_resize_full" value="Y"<?if($arCurrentItem['DATA']['resize_full']=='Y'):?> checked="checked"<?endif?> />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?
		return ob_get_clean();
	}
	public static function saveSettings($PropertyCode, $SiteID, $arPost) {
		$arData = $arPost['data'];
		$arFields = array(
			'PROPERTY' => $PropertyCode,
			'SITE' => $SiteID,
			'TYPE' => static::GetCode(),
			'DATA' => serialize($arData),
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
		if ($arItem['DATA']['resize_y']=='Y' && $arItem['DATA']['resize_x']!='Y') {
			$Resize = 'vertical';
		} elseif ($arItem['DATA']['resize_y']!='Y' && $arItem['DATA']['resize_x']=='Y') {
			$Resize = 'horizontal';
		} elseif ($arItem['DATA']['resize_y']=='Y' && $arItem['DATA']['resize_x']=='Y') {
			$Resize = 'both';
		} else {
			$Resize = 'none';
		}
		ob_start();
		?>
			<textarea name="PROPERTY[<?=$PropertyID;?>][VALUE]" cols="<?=$arItem['DATA']['cols'];?>" rows="<?=$arItem['DATA']['rows'];?>" data-resize="<?=$Resize?>" style="overflow:auto; resize:<?=$Resize;?>;<?if($arItem['DATA']['resize_full']=='Y'):?> width:90%;<?endif?>"><?=$PropertyValue;?></textarea>
		<?
		$strResult = ob_get_clean();
		return $strResult;
	}
}

?>