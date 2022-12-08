<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper,
	\WD\Utilities\PagePropBase;

Helper::loadMessages();

class PagePropBase_SelectBox extends PagePropBase {
	CONST CODE = 'SELECTBOX';
	CONST NAME = 'Выпадающий список';
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
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAv0lEQVR42sWSSwrCMBCGcwM7mekp3KioGwuuVayPg0hx7YOqN/VVPYX/YCpZNog18C1CMl8yf2LMt8NauyCiR0R0j5inwQItbMRxC5IBBGcn7RPzyXF0HDxyBfuyt0CkDUFSCkSkg8WNx9ax89iDlWHmGSQFuJHI2NQ+fhIi2upWCRE1axUUnuCiAp1XDDHT0+afFkQmfwlxCZ7aCm6TBgv0+qCpvw+SqwuxFxSipo7+h+UrhIaY6slaDOOo9gxfPOJSkHY0ydoAAAAASUVORK5CYII=';
	}
	public static function getMessage($Item) {
		$arMess = array(
			'OPTION_CODE' => 'Значение',
			'OPTION_NAME' => 'Описание значения',
			'SORT' => 'Сортировка',
			'DELETING' => 'Удаление',
			'DELETE' => 'Удалить',
			'SELECT_OPTION_EMPTY' => '--- не задано ---',
			'ADD_ROW' => 'Добавить значение',
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
			<style>
			#wd_pageprops_settings_selectbox .adm-list-table-cell {padding-left:12px; padding-right:12px;}
			#wd_pageprops_settings_selectbox input[type=text] {width:100%; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; box-sizing:border-box;}
			</style>
			<div id="wd_pageprops_settings_selectbox">
				<table class="adm-list-table">
					<tbody>
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell" style="width:200px;">
								<div class="adm-list-table-cell-inner"><?=static::GetMessage('OPTION_CODE');?></div>
							</td>
							<td class="adm-list-table-cell">
								<div class="adm-list-table-cell-inner"><?=static::GetMessage('OPTION_NAME');?></div>
							</td>
							<td class="adm-list-table-cell" style="width:80px;">
								<div class="adm-list-table-cell-inner"><?=static::GetMessage('SORT');?></div>
							</td>
							<td class="adm-list-table-cell" style="width:80px;">
								<div class="adm-list-table-cell-inner"><?=static::GetMessage('DELETING');?></div>
							</td>
						</tr>
						<?$MainRow=true?>
						<?foreach($arCurrentItem['DATA'] as $Key => $arOption):?>
							<tr class="adm-list-table-row"<?if($MainRow):?> data-main="Y" style="display:none"<?endif?>>
								<td class="adm-list-table-cell">
									<input type="text" name="data[code][]" value="<?=($MainRow?'':$Key)?>" />
								</td>
								<td class="adm-list-table-cell">
									<input type="text" name="data[name][]" value="<?=($MainRow?'':htmlspecialcharsbx($arOption['NAME']))?>" />
								</td>
								<td class="adm-list-table-cell">
									<input type="text" name="data[sort][]" value="<?=($MainRow?'100':htmlspecialcharsbx($arOption['SORT']))?>" class="sort" />
								</td>
								<td class="adm-list-table-cell align-center">
									<input type="button" value="<?=static::GetMessage('DELETE');?>" onclick="WD_Pageprops_Selectbox_DeleteRow(this);" />
								</td>
							</tr>
							<?$MainRow=false;?>
						<?endforeach?>
					</tbody>
				</table>
				<script>
				// Adding row
				function WD_Pageprops_Selectbox_AddRow() {
					var NewRow = $('#wd_pageprops_settings_selectbox tr.adm-list-table-row[data-main=Y]').clone().removeAttr('data-main').css('display','');
					NewRow.appendTo($('#wd_pageprops_settings_selectbox tbody')).find('input[type=text]').not('.sort').val('');
				}
				// Delete row
				function WD_Pageprops_Selectbox_DeleteRow(Sender) {
					var Row = $(Sender).parents('tr').eq(0);
					if (Row.attr('data-main')!='Y') {
						Row.remove();
					}
				}
				</script>
				<hr/>
				<div>
					<input type="button" value="<?=static::GetMessage('ADD_ROW');?>" onclick="WD_Pageprops_Selectbox_AddRow();" />
				</div>
				<hr/>
			</div>
		<?
		return ob_get_clean();
	}
	public static function saveSettings($PropertyCode, $SiteID, $arPost) {
		$arData = $arPost['data'];
		unset($arData['code']['0'],$arData['name']['0'],$arData['sort']['0']);
		$arValues = array();
		if (is_array($arData['code'])) {
			foreach($arData['code'] as $Key => $Code) {
				$Sort = $arData['sort'][$Key];
				$Name = $arData['name'][$Key];
				$arValues[$Code] = array(
					'NAME' => $Name,
					'SORT' => IntVal($Sort),
				);
			}
		}
		uasort($arValues, create_function('$a,$b','if ($a["SORT"] == $b["SORT"]) return 0; return ($a["SORT"] < $b["SORT"]) ? -1 : 1;'));
		$arFields = array(
			'PROPERTY' => $PropertyCode,
			'SITE' => $SiteID,
			'TYPE' => static::GetCode(),
			'DATA' => serialize($arValues),
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
		ob_start();
		?>
			<select name="PROPERTY[<?=$PropertyID;?>][VALUE]">
				<option value=""><?=static::GetMessage('SELECT_OPTION_EMPTY');?></option>
				<?foreach($arItem['DATA'] as $Key => $arOption):?>
					<option value="<?=$Key;?>"<?if($Key==$PropertyValue):?> selected="selected"<?endif?>><?=htmlspecialcharsbx($arOption['NAME']);?></option>
				<?endforeach?>
			</select>
		<?
		$strResult = ob_get_clean();
		return $strResult;
	}
}

?>