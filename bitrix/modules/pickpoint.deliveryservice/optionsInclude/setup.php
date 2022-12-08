<?php
use \PickPoint\DeliveryService\Bitrix\Tools;
use \Bitrix\Main\Localization\Loc;

// Main setup option tab

/** @global array $arOptions */
/** @global array $arAllOptions */
/** @global array $arSelectedST */
/** @global array $arSelectedST */
/** @global array $arServiceTypes - from constants */
/** @global array $arOptionDefaults - from constants */
/** @global array $arFields - from fields */
/** @global array $arFieldsName - from fields */

Loc::loadMessages(__FILE__);
?>
<tr>
	<td valign="top" width="50%">
		<span class="required">*</span><?=Loc::getMessage('PP_IKN_NUMBER');?>
	</td>
	<td valign="top" width="50%">
		<input type="text" size="30" maxlength="255" value="<?=htmlspecialcharsbx($arOptions['OPTIONS']['pp_ikn_number'])?>" name="pp_ikn_number" />
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<span class="required">*</span><?=Loc::getMessage('PP_API_LOGIN');?>
	</td>
	<td valign="top" width="50%">
		<input type="text" size="30" maxlength="255" value="<?=htmlspecialcharsbx($arOptions['OPTIONS']['pp_api_login'])?>" name="pp_api_login" />
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<span class="required">*</span><?=Loc::getMessage('PP_API_PASSWORD');?>
	</td>
	<td valign="top" width="50%">
		<input type="text" size="30" maxlength="255"  value="<?=htmlspecialcharsbx($arOptions['OPTIONS']['pp_api_password'])?>" name="pp_api_password" />
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<span class="required">*</span><?=Loc::getMessage('PP_ENCLOSURE');?>
	</td>
	<td valign="top" width="50%">
		<input type="text" size="30" maxlength="255" value="<?=htmlspecialcharsbx($arOptions['OPTIONS']['pp_enclosure'])?>" name="pp_enclosure" />
	</td>
</tr>
<tr>
	<td valign="top">
		<input type="checkbox" id="pp_test_mode" <?=$arOptions['OPTIONS']['pp_test_mode'] ? 'checked' : ''?> name="pp_test_mode" />
	</td>
	<td valign="top">
		<label for="pp_test_mode"><?=Loc::getMessage('PP_TEST_MODE');?></label>
	</td>
</tr>
<tr>
	<td valign="top">
		<span class="required">*</span><?=Loc::getMessage('PP_SERVICE_TYPES_SELECTED');?>:<br>
		<img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
	</td>
	<td valign="top">
		<select name="pp_service_types_selected[]" size="5" multiple width="30">
            <?php foreach ($arServiceTypes as $iKey => $sValue) {?>
				<option value="<?=$iKey?>" <?=in_array($iKey, $arSelectedST) ? 'selected' : ''?>><?=$sValue?></option>
            <?php }?>
		</select>
	</td>
</tr>
<?php // Cool new Common?>
<?php ShowParamsHTMLByArray($arAllOptions["common"]);?>
<?php Tools::placeHint('getting_type');?>
<?php // --?>
<tr>
	<td valign="top" width="50%">
		<?=Loc::getMessage('PP_TERM_INC');?>
	</td>
	<td valign="top" width="50%">
		<input type="text" size="30" maxlength="255" value="<?=htmlspecialcharsbx($arOptions['OPTIONS']['pp_term_inc']);?>" name="pp_term_inc" />
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<?=Loc::getMessage('PP_POSTAMAT_PICKER');?>
	</td>
	<td valign="top" width="50%">
		<input type="text" size="30" maxlength="255" value="<?=htmlspecialcharsbx($arOptions['OPTIONS']['pp_postamat_picker']);?>" name="pp_postamat_picker" />
	</td>
</tr>
<tr>
	<td valign="top">
		<input type="checkbox" id="pp_add_info" <?=$arOptions['OPTIONS']['pp_add_info'] ? 'checked' : ''?> name="pp_add_info" />
	</td>
	<td valign="top">
		<label for="pp_add_info"><?=Loc::getMessage('PP_ADD_INFO');?></label>
	</td>
</tr>
<tr>
	<td valign="top">
		<input type="checkbox" id="pp_order_phone" <?=$arOptions['OPTIONS']['pp_order_phone'] ? 'checked' : ''?> name="pp_order_phone" />
	</td>
	<td valign="top">
		<label for="pp_order_phone"><?=Loc::getMessage('PP_PHONE_FROM_PROPERTY_TITLE');?></label>
	</td>
</tr>
<tr>
	<td valign="top">
		<input type="checkbox" id="pp_city_location" <?=$arOptions['OPTIONS']['pp_city_location'] ? 'checked' : ''?> name="pp_city_location" />
	</td>
	<td valign="top">
		<label for="pp_city_location"><?=Loc::getMessage('PP_CITY_FROM_LOCATION');?></label>
	</td>
</tr>
<tr>
	<td valign="top">
		<input type="checkbox" id="pp_order_city_status" <?=$arOptions['OPTIONS']['pp_order_city_status'] ? 'checked' : ''?> name="pp_order_city_status" />
	</td>
	<td valign="top">
		<label for="pp_order_city_status"><?=Loc::getMessage('PP_CITY_FROM_ORDER_PROP_STATUS');?></label>
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
        <?php // arFields and arFieldsName defined in ./fields.php?>
		<script>
			var arFields = <?=\Bitrix\Main\Web\Json::encode($arFields)?>;
			var arFieldsName = <?=\Bitrix\Main\Web\Json::encode($arFieldsName)?>;

			function DeleteTable(a) {
				while (a = a.parentNode) {
					if (a.className == "dAdded") break;
				}
				a.parentNode.removeChild(a);
			}
			function AddTable(key, pt, button) {
				Table = "<table cellspacing='2' cellpadding='0' border='0' class = 'tType'><tr><td><?=Loc::getMessage(
					'PP_TYPE'
				)?></td><td><select name='OPTIONS[#PT#][#KEY#][#NUMBER#][TYPE]' id='OPTIONS[#PT#][#KEY#][#NUMBER#][TYPE]' onchange='PropertyTypeChange(this,#PT#)'>";
				for (sKey in arFieldsName) {
					Table += "<option value = '" + sKey + "'>" + arFieldsName[sKey] + "</option>";
				}
				Table += "</select></td></tr><tr><td><?=Loc::getMessage(
					'PP_VALUE'
				)?></td><td><select name='OPTIONS[#PT#][#KEY#][#NUMBER#][VALUE]' id='OPTIONS[#PT#][#KEY#][#NUMBER#][VALUE]' style='display: none;'></select><input type='text' value='' name='OPTIONS[#PT#][#KEY#][#NUMBER#][VALUE_ANOTHER]' id='OPTIONS[#PT#][#KEY#][#NUMBER#][VALUE_ANOTHER]' style=''></td></tr><tr><td colspan='2' align='right'><a onclick = 'DeleteTable(this)' class='aDelete'><?=Loc::getMessage(
					'PP_DELETE'
				)?></a></td></tr></table>";
				Td = button.parentNode.parentNode;
				Number = Td.children.length - 1;
				Table = Table.split("#KEY#").join(key);
				Table = Table.split("#PT#").join(pt);
				Table = Table.split("#NUMBER#").join(Number);

				var Div = document.createElement('div');
				Div.setAttribute('class', "dAdded");
				Div.innerHTML = Table;
				Td.insertBefore(Div, button.parentNode);
			}

			function CleanSelect(Select) {
				count = Select.options.length;
				for (i = 0; i < count; i++) Select.removeChild(Select.options[0]);
			}

			function PropertyTypeChange(Select, iPersonType) {

				ID = Select.id;
				ChildSelect = document.getElementById(ID.replace("[TYPE]", "[VALUE]"));
				InputAnother = document.getElementById(ID.replace("[TYPE]", "[VALUE_ANOTHER]"));
				Options = Select.options;
				for (i = 0; i < Options.length; i++) {
					if (Options[i].selected) {
						SelectedOption = Options[i];
						break;
					}
				}
				CleanSelect(ChildSelect);
				InputAnother.value = "";
				Type = SelectedOption.value;
				switch (Type) {
					case "ANOTHER":
						ChildSelect.style.display = "none";
						InputAnother.style.display = "";
						break;
					case "PROPERTY":
						for (sKey in arFields[Type][iPersonType]) {
							NewOption = new Option;
							NewOption.value = sKey;
							NewOption.text = arFields[Type][iPersonType][sKey] + " (id = "+ sKey +")";
							ChildSelect.appendChild(NewOption);
						}
						ChildSelect.style.display = "";
						InputAnother.style.display = "none";
						break;
					case "USER":
					case "ORDER":
						for (sKey in arFields[Type]) {
							NewOption = new Option;
							NewOption.value = sKey;
							NewOption.text = arFields[Type][sKey];
							ChildSelect.appendChild(NewOption);
						}
						ChildSelect.style.display = "";
						InputAnother.style.display = "none";
						break;
				}
			}
		</script>
        <?php
		$aTabs1 = array();
		$personType = array();
		$dbPersonType = CSalePersonType::GetList(array('ID' => 'ASC'), array('ACTIVE' => 'Y'));
		while ($arPersonType = $dbPersonType->GetNext()) {
			$aTabs1[] = array(
				'DIV' => 'oedit'.$arPersonType['ID'],
				'TAB' => $arPersonType['NAME'],
				'TITLE' => $arPersonType['NAME'],
			);
			$personType[$arPersonType['ID']] = $arPersonType;
		}
		$tabControl1 = new CAdminViewTabControl('tabControl1', $aTabs1);
		$tabControl1->Begin();
				
		foreach ($personType as $val) {
			$tabControl1->BeginNextTab(); ?>
			<table class="internal" width="80%">
				<tr class="heading">
					<td align="center"><?=Loc::getMessage('PP_VALUE_NAME');?></td>
					<td align="center"><?=Loc::getMessage('PP_VALUE');?></td>
				</tr>
                <?php $arSelected = (!empty($arTableOptions[$val['ID']]['ORDER_LOCATION']) && count($arTableOptions[$val['ID']]['ORDER_LOCATION']))
					? $arTableOptions[$val['ID']]['ORDER_LOCATION']
					: array($arOptionDefaults['ORDER_LOCATION']);
				$arRow = array(
					'NAME' => Loc::getMessage('PP_CITY_FROM_ORDER_PROP'),
					'CODE' => 'ORDER_LOCATION',
					'SELECTED' => $arSelected,
				);
				require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/pickpoint.deliveryservice/row.php';?>

                <?php $arSelected = (!empty($arTableOptions[$val['ID']]['ORDER_PHONE']) && count($arTableOptions[$val['ID']]['ORDER_PHONE']))
					? $arTableOptions[$val['ID']]['ORDER_PHONE']
					: array($arOptionDefaults['ORDER_PHONE']);
				$arRow = array(
					'NAME' => Loc::getMessage('PP_PHONE_FROM_PROPERTY_SELECT'),
					'CODE' => 'ORDER_PHONE',
					'SELECTED' => $arSelected,
				);
				require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/pickpoint.deliveryservice/row.php';?>

                <?php $arSelected = (!empty($arTableOptions[$val['ID']]['FIO']) && count($arTableOptions[$val['ID']]['FIO']))
					? $arTableOptions[$val['ID']]['FIO']
					: array($arOptionDefaults['FIO']);
				$arRow = array(
					'NAME' => Loc::getMessage('PP_FIO'),
					'CODE' => 'FIO',
					'SELECTED' => $arSelected,
				);
				require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/pickpoint.deliveryservice/row.php';?>

                <?php $arSelected = (!empty($arTableOptions[$val['ID']]['EMAIL']) && count($arTableOptions[$val['ID']]['EMAIL']))
					? $arTableOptions[$val['ID']]['EMAIL'] : array($arOptionDefaults['EMAIL']);
				$arRow = array(
					'NAME' => Loc::getMessage('PP_EMAIL'),
					'CODE' => 'EMAIL',
					'SELECTED' => $arSelected,
				);
				require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/pickpoint.deliveryservice/row.php';?>
			</table>
            <?php
		}
		$tabControl1->End();
		?>
	</td>
</tr>