<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!function_exists("showFilePropertyField"))
{
	function showFilePropertyField($name, $property_fields, $values, $max_file_size_show=50000)
	{
		$res = "";

		if (!is_array($values) || empty($values))
			$values = array(
				"n0" => 0,
			);

		if ($property_fields["MULTIPLE"] == "N")
		{
			$res = "<label for=\"\"><input type=\"file\" size=\"".$max_file_size_show."\" value=\"".$property_fields["VALUE"]."\" name=\"".$name."[0]\" id=\"".$name."[0]\"></label>";
		}
		else
		{
			$res = '
			<script type="text/javascript">
				function addControl(item)
				{
					var current_name = item.id.split("[")[0],
						current_id = item.id.split("[")[1].replace("[", "").replace("]", ""),
						next_id = parseInt(current_id) + 1;

					var newInput = document.createElement("input");
					newInput.type = "file";
					newInput.name = current_name + "[" + next_id + "]";
					newInput.id = current_name + "[" + next_id + "]";
					newInput.onchange = function() { addControl(this); };

					var br = document.createElement("br");
					var br2 = document.createElement("br");

					BX(item.id).parentNode.appendChild(br);
					BX(item.id).parentNode.appendChild(br2);
					BX(item.id).parentNode.appendChild(newInput);
				}
			</script>
			';

			$res .= "<label for=\"\"><input type=\"file\" size=\"".$max_file_size_show."\" value=\"".$property_fields["VALUE"]."\" name=\"".$name."[0]\" id=\"".$name."[0]\"></label>";
			$res .= "<br/><br/>";
			$res .= "<label for=\"\"><input type=\"file\" size=\"".$max_file_size_show."\" value=\"".$property_fields["VALUE"]."\" name=\"".$name."[1]\" id=\"".$name."[1]\" onChange=\"javascript:addControl(this);\"></label>";
		}

		return $res;
	}
}

if (!function_exists("PrintPropsForm"))
{
	function PrintPropsForm($arSource = array(), $locationTemplate = ".default", $arResult, $PROP_CHECK=array(), $PROP_iskl = array() )
	{
		global $fieldsProp;


		
		if (!empty($arSource))
		{
			?>
			
				<?
//print_r($arProperties);
				foreach ($arSource as $arProperties)
				{
					if( count($PROP_CHECK) > 0 && !in_array($arProperties["ID"], $PROP_CHECK))
						continue;					
					if( count($PROP_iskl) > 0 && in_array($arProperties["ID"], $PROP_iskl))
						continue;
					
						if( in_array($arProperties["CODE"], array('ADDRESS', 'CAR_MODEL', 'CAR_NUMBER', 'FIO_POLUCHATELYA', 'TIME', 'CALL_ME', 'DATE')) )
							ob_start();					
					
					
						$dop_class = '';
						
						if( in_array($arProperties["CODE"], array('EMAIL', 'PHONE')) )
						{
							$dop_class = 'custom_field';
						}
						$k++;
					?>
					<div  data-property-id-row="<?=intval(intval($arProperties["ID"]))?>" class="box_code_<?=$arProperties["CODE"]?> form-group box<?=intval(intval($arProperties["ID"]))?> bx-soa-customer-field p-0 <?=$dop_class?>">

						<label class="bx-soa-custom-label" for="<?=$arProperties["FIELD_NAME"]?>">
							<?=$arProperties["NAME"]?>
							<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
								<span class="bx_sof_req">*</span>
							<?endif?>
						</label>

						<?
						if ($arProperties["TYPE"] == "CHECKBOX")
						{
							?>
							<div class="checkbox_block">
								<input type="hidden" name="<?=$arProperties["FIELD_NAME"]?>" value="">
								<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" class="form-check-input check_checkbox_delivery" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if ($arProperties["CHECKED"]=="Y") echo " checked";?>>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						elseif ($arProperties["TYPE"] == "TEXT")
						{
							?>
							<?if( $arProperties["CODE"] == 'DATE'):?>
							<?
						
							
								$dateStartDelivery = date('d.m.Y');
								//echo $dateStartDelivery;
								$i = 0;
							?>
								<?for($k=0;$k<14;$k++):?>
									<?if( FormatDate('D', strtotime($dateStartDelivery) + $k * 24 * 3600) == 'Вс') continue;
										$i++;
										
										$SELECTED = '';
										if( $arProperties["VALUE"] == '' && $k == 0 )
										{											
											$SELECTED='selected';
											$DATE_DELIVERY = FormatDate('d.m, D', strtotime($dateStartDelivery) + ($k * 24 * 3600));
										}
										elseif( $arProperties["VALUE"] != '' && $arProperties["VALUE"] == date('d.m.Y', strtotime($dateStartDelivery) + ($k * 24 * 3600)) )
										{
											$SELECTED='selected';
											$DATE_DELIVERY = FormatDate('d.m, D', strtotime($dateStartDelivery) + ($k * 24 * 3600));
										}									
									
									?>
									
								
									<div class="date_block <?=$SELECTED?>" data-day="<?=date('N', strtotime($dateStartDelivery) + ($k * 24 * 3600));?>" data-date="<?=date('d.m.Y', strtotime($dateStartDelivery) + ($k * 24 * 3600));?>">
										<?=FormatDate('d.m, D', strtotime($dateStartDelivery) + ($k * 24 * 3600));?>
									</div>
									<?if( $i == 5) break;?>
								<?endfor;?>
								<? if( $_POST["is_ajax_post"] != 'Y') $arProperties["VALUE"] = ''; ?>
								<input type=hidden name="<?=$arProperties["FIELD_NAME"]?>" class="ORDER_PROP_DATE" value="<?=$arProperties["VALUE"]?>">
								
							<?else:?>							
							
							
							<div class="soa-property-container">
								<input type="text" size="<?=$arProperties["SIZE1"]?>" value="<?=$arProperties["VALUE"]?>" class="form-control bx-soa-customer-input bx-ios-fix" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" />
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?endif;?>
							<?
						}
						elseif ($arProperties["TYPE"] == "SELECT")
						{
							?>
							<div class="soa-property-container">
								<select class="select_<?=$arProperties["CODE"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
									<?foreach($arProperties["VARIANTS"] as $arVariants):?>
										<option value="<?=$arVariants["VALUE"]?>"<?=$arVariants["SELECTED"] == "Y" ? " selected" : ''?>><?=$arVariants["NAME"]?></option>
									<?endforeach?>
								</select>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?if($_POST["is_ajax_post"] != "Y"):?>
								<script>$('#<?=$arProperties["FIELD_NAME"]?>').select2({minimumResultsForSearch: -1});</script>
							<?endif;?>
							<?
						}
						elseif ($arProperties["TYPE"] == "MULTISELECT")
						{
							?>
							<div class="soa-property-container">
								<select multiple name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
									<?foreach($arProperties["VARIANTS"] as $arVariants):?>
										<option value="<?=$arVariants["VALUE"]?>"<?=$arVariants["SELECTED"] == "Y" ? " selected" : ''?>><?=$arVariants["NAME"]?></option>
									<?endforeach?>
								</select>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						elseif ($arProperties["TYPE"] == "TEXTAREA")
						{
							$rows = ($arProperties["SIZE2"] > 10) ? 4 : $arProperties["SIZE2"];
							?>
							<div class="soa-property-container">
								<textarea rows="<?=$rows?>" cols="<?=$arProperties["SIZE1"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>"><?=$arProperties["VALUE"]?></textarea>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						elseif ($arProperties["TYPE"] == "LOCATION")
						{
							?>
							<div class="soa-property-container">
								<?
								$value = 0;
								if (is_array($arProperties["VARIANTS"]) && count($arProperties["VARIANTS"]) > 0)
								{
									foreach ($arProperties["VARIANTS"] as $arVariant)
									{
										if ($arVariant["SELECTED"] == "Y")
										{
											$value = $arVariant["ID"];
											break;
										}
									}
								}
echo $value.'+';
								// here we can get '' or 'popup'
								// map them, if needed
								if(CSaleLocation::isLocationProMigrated())
								{
									$locationTemplateP = $locationTemplate == 'popup' ? 'search' : 'steps';
									$locationTemplateP = $_REQUEST['PERMANENT_MODE_STEPS'] == 1 ? 'steps' : $locationTemplateP; // force to "steps"
								}
								
								?>

								<?if($locationTemplateP == 'steps'):?>
									<input type="hidden" id="LOCATION_ALT_PROP_DISPLAY_MANUAL[<?=intval($arProperties["ID"])?>]" name="LOCATION_ALT_PROP_DISPLAY_MANUAL[<?=intval($arProperties["ID"])?>]" value="<?=($_REQUEST['LOCATION_ALT_PROP_DISPLAY_MANUAL'][intval($arProperties["ID"])] ? '1' : '0')?>" />
								<?endif?>

								<?CSaleLocation::proxySaleAjaxLocationsComponent(array(
									"AJAX_CALL" => "N",
									"COUNTRY_INPUT_NAME" => "COUNTRY",
									"REGION_INPUT_NAME" => "REGION",
									"CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
									"CITY_OUT_LOCATION" => "Y",
									"LOCATION_VALUE" => $value,
									"ORDER_PROPS_ID" => $arProperties["ID"],
									"ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "" : "",
									"SIZE1" => $arProperties["SIZE1"],
								),
									array(
										"ID" => $value,
										"CODE" => "",
										"SHOW_DEFAULT_LOCATIONS" => "Y",

										// function called on each location change caused by user or by program
										// it may be replaced with global component dispatch mechanism coming soon
										"JS_CALLBACK" => "submitFormProxy",

										// function window.BX.locationsDeferred['X'] will be created and lately called on each form re-draw.
										// it may be removed when sale.order.ajax will use real ajax form posting with BX.ProcessHTML() and other stuff instead of just simple iframe transfer
										"JS_CONTROL_DEFERRED_INIT" => intval($arProperties["ID"]),

										// an instance of this control will be placed to window.BX.locationSelectors['X'] and lately will be available from everywhere
										// it may be replaced with global component dispatch mechanism coming soon
										"JS_CONTROL_GLOBAL_ID" => intval($arProperties["ID"]),

										"DISABLE_KEYBOARD_INPUT" => "Y",
										"PRECACHE_LAST_LEVEL" => "Y",
										"PRESELECT_TREE_TRUNK" => "Y",
										"SUPPRESS_ERRORS" => "Y"
									),
									$locationTemplateP,
									true,
									'location-block-wrapper'
								)?>

								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						elseif ($arProperties["TYPE"] == "RADIO")
						{
							?>
							<div class="soa-property-container">
								<?
								if (is_array($arProperties["VARIANTS"]))
								{
									foreach($arProperties["VARIANTS"] as $arVariants):
									?>
										<input
											type="radio"
											name="<?=$arProperties["FIELD_NAME"]?>"
											id="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"
											value="<?=$arVariants["VALUE"]?>" <?if($arVariants["CHECKED"] == "Y") echo " checked";?> />

										<label for="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"><?=$arVariants["NAME"]?></label></br>
									<?
									endforeach;
								}
								?>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						elseif ($arProperties["TYPE"] == "FILE")
						{
							?>
							<div class="soa-property-container">
								<?=showFilePropertyField("ORDER_PROP_".$arProperties["ID"], $arProperties, $arProperties["VALUE"], $arProperties["SIZE1"])?>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						elseif ($arProperties["TYPE"] == "DATE")
						{
							?>
							<div>
								<?
								global $APPLICATION;

								$APPLICATION->IncludeComponent('bitrix:main.calendar', '', array(
									'SHOW_INPUT' => 'Y',
									'INPUT_NAME' => "ORDER_PROP_".$arProperties["ID"],
									'INPUT_VALUE' => $arProperties["VALUE"],
									'SHOW_TIME' => 'N'
								), null, array('HIDE_ICONS' => 'N'));
								?>
								<?if (strlen(trim($arProperties["DESCRIPTION"])) > 0):?>
									<div class="bx_description"><?=$arProperties["DESCRIPTION"]?></div>
								<?endif?>
							</div>
							<?
						}
						?>
					</div>

					<?if(CSaleLocation::isLocationProEnabled()):?>

					<?
					$propertyAttributes = array(
						'type' => $arProperties["TYPE"],
						'valueSource' => $arProperties['SOURCE'] == 'DEFAULT' ? 'default' : 'form' // value taken from property DEFAULT_VALUE or it`s a user-typed value?
					);

					if(intval($arProperties['IS_ALTERNATE_LOCATION_FOR']))
						$propertyAttributes['isAltLocationFor'] = intval($arProperties['IS_ALTERNATE_LOCATION_FOR']);

					if(intval($arProperties['CAN_HAVE_ALTERNATE_LOCATION']))
						$propertyAttributes['altLocationPropId'] = intval($arProperties['CAN_HAVE_ALTERNATE_LOCATION']);

					if($arProperties['IS_ZIP'] == 'Y')
						$propertyAttributes['isZip'] = true;
					?>

						<script>

							<?// add property info to have client-side control on it?>
							(window.top.BX || BX).saleOrderAjax.addPropertyDesc(<?=CUtil::PhpToJSObject(array(
									'id' => intval($arProperties["ID"]),
									'attributes' => $propertyAttributes
								))?>);

						</script>
					<?endif?>

				
				
					<?
						/*if( in_array($arProperties["CODE"], array('ADDRESS')) )
							$fieldsProp['ADDRESS'] .= ob_get_clean();	*/				

						if( in_array($arProperties["CODE"], array('CAR_MODEL', 'CAR_NUMBER', 'FIO_POLUCHATELYA')) )
							$fieldsProp['PICKUP'] .= ob_get_clean();
						if( in_array($arProperties["CODE"], array('ADDRESS','TIME', 'CALL_ME', 'DATE')) )
							$fieldsProp['COURIER'] .= ob_get_clean();						
						
						

						
				}
				?>
			
			<?
		}
	}
}
?>