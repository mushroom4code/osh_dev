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
	function PrintPropsForm($arSource = array(), $locationTemplate = ".default", $arResult = array())
	{
		global $returnRecipient, $returnRecipientCheck, $flagCheckOther, $gorod, $globalProps, $SHOWROM, $APPLICATION, $ETAJ, $PODIEM, $USTANOVKA, $DATE_DELIVERY, $ADDRESS_NO_ACTIVE;
		
		if (!empty($arSource))
		{
			?>
				<div class="wrap_props_order">
					<?
					foreach ($arSource as $arProperties)
					{
						if( $arProperties["CODE"] == 'OTHER_MEN' && $arProperties["CHECKED"]=="Y" )
							$flagCheckOther = 1;
						
						if( in_array($arProperties["CODE"], array('OTHER_MEN', 'PHONE_RECIPIENT', 'NAME_RECIPIENT', 'LOCATION')) )
							ob_start();
						
						/*if( $arProperties["CODE"] == 'COMMENT_DELIVERY' )
							ob_start();						
	
						if( $arProperties["CODE"] == 'DATE' )
							ob_start();	*/
						$dop_class = '';
						if( in_array($arProperties["CODE"], array('ADDRESS', 'DOM', 'PODIEZD', 'KV')) )
						{
							$dop_class='addres_field';
						}	
						if( in_array($arProperties["CODE"], array('PHONE_RECIPIENT', 'NAME_RECIPIENT')) )
						{
							$arProperties["REQUIED_FORMATED"]='Y';
						}
						
						$noactive = '';
						
						if( in_array($arProperties["CODE"], array('ETAJ', 'LIFT')) &&  $PODIEM != 1  )
						{
							$noactive = 'noactive';
						}
					
						
?>
						<div class="bx_block_prop_item block_<?=$arProperties["CODE"]?> <?=$noactive?> <?=$dop_class?> <?if($ADDRESS_NO_ACTIVE==1):?>noactiveAddress<?endif;?>">
						<?	
						//После врапа т.к. сам чекбокс надо показать
						if( $arProperties["CODE"] == 'USTANOVKA' && $arProperties["CHECKED"] != "Y"  )
						{
							$noactive = 'noactive';
						}
						elseif( $arProperties["CODE"] == 'USTANOVKA' && $arProperties["CHECKED"] == "Y" )
						{
							$USTANOVKA = 1;
						}						
						?>
<?						
						if ($arProperties["TYPE"] == "CHECKBOX")
						{
							//Сохраним данные по чекбоксу подъема
							if( $arProperties["CODE"] == 'PODIEM' &&  $arProperties["CHECKED"]=="Y" )
							{
								$PODIEM = 1;
							}

							?>
							<?if( $arProperties["CODE"] == 'LIFT' ):?>
							<?
								if( !$_POST )
								$arProperties["CHECKED"] = 'Y';
							
							?>
								<div class="bx_block_prop">
								<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" autocomplete="off" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if ($arProperties["CHECKED"]=="Y") echo " checked";?> <?if( $arProperties["CODE"] == 'LIFT' || $arProperties["CODE"] == 'PODIEM' || $arProperties["CODE"] == 'USTANOVKA' ):?>onclick="submitForm();"<?endif;?>>
								<label for="ORDER_PROP_52">Есть лифт</label>
								</div>							
							<?else:?>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop">
								<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" autocomplete="off" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if ($arProperties["CHECKED"]=="Y") echo " checked";?> <?if( $arProperties["CODE"] == 'LIFT' || $arProperties["CODE"] == 'PODIEM' || $arProperties["CODE"] == 'USTANOVKA' ):?>onclick="submitForm();"<?endif;?>>

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>

							<?endif;?>
							<?
						}
						elseif ($arProperties["TYPE"] == "TEXT")
						{
							if( $arProperties["CODE"] == 'DATE')
							{
								foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
								{
								//print_r($arDelivery);
									if( $arDelivery["CHECKED"] == 'Y' && $delivery_id == 2)
										$arProperties["NAME"] = 'Дата самовывоза';
									if( $arDelivery["CHECKED"] == 'Y' && $delivery_id == 25)
										$arProperties["NAME"] = 'Дата отгрузки в транспортную компанию';									
								}	
								
							}
							if(  $arProperties["CODE"] == 'DELIVERY_TK_TYPE' )
							{
								if( $arProperties["VALUE"] == "Доставка до терминала транспортной компании" )
									$ADDRESS_NO_ACTIVE = 1;
								elseif( $arProperties["VALUE"] == '' )
								{
									$ADDRESS_NO_ACTIVE = 0;
									$arProperties["VALUE"] = 'Доставка до адреса';
									
								}
?>
							<div class="bx_block_prop">
								<div class="label left">
									<input type=radio id="DELIVERY_TYPE_1" class="DELIVERY_TYPE DELIVERY_TYPE_1" name="<?=$arProperties["FIELD_NAME"]?>" value="Доставка до адреса" 
									<?if( $arProperties["VALUE"] == "Доставка до адреса" ):?>checked<?endif;?>>
									<label for="DELIVERY_TYPE_1">Доставка до адреса</label>
								</div>
								<div class="label left">
									<input type=radio id="DELIVERY_TYPE_2" class="DELIVERY_TYPE DELIVERY_TYPE_2" name="<?=$arProperties["FIELD_NAME"]?>" value="Доставка до терминала транспортной компании"
									<?if( $arProperties["VALUE"] == "Доставка до терминала транспортной компании" ):?>checked<?endif;?>
									>
									<label for="DELIVERY_TYPE_2">Доставка до терминал транспортной компании</label>								
								</div>
							</div>
							</div>
<?								continue;	
							}
							else
							{
							?>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<?}?>
							<div class="bx_block_prop r3x1">
							<?if( $arProperties["CODE"] == 'DATE'):?>
							<?
						
							
								$dateStartDelivery = CheckDateText($SHOWROM);
								//echo $dateStartDelivery;
							?>
							
								<select name="<?=$arProperties["FIELD_NAME"]?>" onchange="submitForm();" autocomplete="off">
									<?for($k=0;$k<14;$k++):?>
									<?if( FormatDate('D', strtotime($dateStartDelivery) + $k * 24 * 3600) == 'Вс') continue;?>
										<?
										$SELECTED = '';
										if( $arProperties["VALUE"] == '' && $k == 0 )
										{											
											$SELECTED='selected';
											$DATE_DELIVERY = FormatDate('d F, D', strtotime($dateStartDelivery) + ($k * 24 * 3600));
										}
										elseif( $arProperties["VALUE"] != '' && $arProperties["VALUE"] == date('d.m.Y', strtotime($dateStartDelivery) + ($k * 24 * 3600)) )
										{
											$SELECTED='selected';
											$DATE_DELIVERY = FormatDate('d F, D', strtotime($dateStartDelivery) + ($k * 24 * 3600));
										}
										?>
										
										<option <?=$SELECTED?> value="<?=date('d.m.Y', strtotime($dateStartDelivery) + ($k * 24 * 3600));?>"><?=FormatDate('d F, D', strtotime($dateStartDelivery) + ($k * 24 * 3600));?></option>
									<?endfor;?>
								</select>
								
							<?else:?>
							
					<input type="text" maxlength="250" size="<?=$arProperties["SIZE1"]?>" value="<?=$arProperties["VALUE"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" <?if( $arProperties["CODE"] == 'ETAJ' ):?>onchange="submitForm();"<?endif;?> class="<?if( $arProperties["CODE"] == 'PHONE' || $arProperties["CODE"] == 'PHONE_RECIPIENT'):?>phone_req<?endif;?> ">

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							
							<?endif;?>
							</div>
							
						
							<?
						}
						elseif ($arProperties["TYPE"] == "SELECT")
						{
							?>
							<br/>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop r3x1">
								<select name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
									<?
									foreach($arProperties["VARIANTS"] as $arVariants):
									?>
										<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
									<?
									endforeach;
									?>
								</select>

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>
						
							<?
						}
						elseif ($arProperties["TYPE"] == "MULTISELECT")
						{
							?>
							<br/>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop r3x1">
								<select multiple name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
									<?
									foreach($arProperties["VARIANTS"] as $arVariants):
									?>
										<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
									<?
									endforeach;
									?>
								</select>

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>
					
							<?
						}
						elseif ($arProperties["TYPE"] == "TEXTAREA")
						{
							$rows = ($arProperties["SIZE2"] > 10) ? 4 : $arProperties["SIZE2"];
							?>
							<br/>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop r3x1">
								<textarea rows="<?=$rows?>" cols="<?=$arProperties["SIZE1"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>"><?=$arProperties["VALUE"]?></textarea>

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>
					
							<?
						}
						elseif ($arProperties["TYPE"] == "LOCATION")
						{
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
							?>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop ">
								<?
								$GLOBALS["APPLICATION"]->IncludeComponent(
									"bitrix:sale.ajax.locations",
									$locationTemplate,
									array(
										"AJAX_CALL" => "N",
										"COUNTRY_INPUT_NAME" => "COUNTRY",
										"REGION_INPUT_NAME" => "REGION",
										"CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
										"CITY_OUT_LOCATION" => "Y",
										"LOCATION_VALUE" => $value,
										"ORDER_PROPS_ID" => $arProperties["ID"],
										"ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitForm()" : "",
										"SIZE1" => $arProperties["SIZE1"],
									),
									null,
									array('HIDE_ICONS' => 'Y')
								);
								?>

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>
						
							<?
						}
						elseif ($arProperties["TYPE"] == "RADIO")
						{
							?>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop r3x1">
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

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>
						
							<?
						}
						elseif ($arProperties["TYPE"] == "FILE")
						{
							?>
							<br/>
							<div class="bx_block_prop">
								<?=$arProperties["NAME"]?>
								<?if ($arProperties["REQUIED_FORMATED"]=="Y"):?>
									<span class="bx_sof_req">*</span>
								<?endif;?>
							</div>

							<div class="bx_block_prop r3x1">
								<?=showFilePropertyField("ORDER_PROP_".$arProperties["ID"], $arProperties, $arProperties["VALUE"], $arProperties["SIZE1"])?>

								<?
								if (strlen(trim($arProperties["DESCRIPTION"])) > 0):
								?>
								<div class="bx_description">
									<?=$arProperties["DESCRIPTION"]?>
								</div>
								<?
								endif;
								?>
							</div>

							
							<?
						}
						?>
						</div>
						
						<?
						if( in_array($arProperties["CODE"], array('OTHER_MEN')) )
							$returnRecipientCheck .= ob_get_clean();
						if( in_array($arProperties["CODE"], array('PHONE_RECIPIENT', 'NAME_RECIPIENT')) )
							$returnRecipient .= ob_get_clean();
						if( in_array($arProperties["CODE"], array('LOCATION')) )
							$gorod .= ob_get_clean();

						/*if( $arProperties["CODE"] == 'COMMENT_DELIVERY' )
							$globalProps['COMMENT_DELIVERY'] .= ob_get_clean();*/						
	
						/*if( $arProperties["CODE"] == 'DATE' )
							$globalProps['CODE'] .= ob_get_clean();*/	

						//Если поле Лифт - подрубаем блок  подъема
						if( $arProperties["CODE"] == 'ETAJ')
						{
							$ETAJ = intval($arProperties["VALUE"]);
						}
						if( $arProperties["CODE"] == 'LIFT')
						{
						?>
						<div class="podiem_calc <?=$noactive?>">
							<?=calcPodiem($arResult, $arProperties["CHECKED"], $ETAJ );?>
							<div class="podiem_calc_info">
								<?$APPLICATION->IncludeFile(SITE_DIR."include/order/text_props_order_podiem.php", Array(), Array("MODE" => "html",  "NAME" => 'Текст о получателе'));?>					
							
							</div>
						</div>
						
						
						
						<?	
							
						}
						
						if( $arProperties["CODE"] == 'USTANOVKA')
						{
?>
							<div class="ustanovka_info <?=$noactive?>">
								<?$APPLICATION->IncludeFile(SITE_DIR."include/order/text_props_order_install_pre.php", Array(), Array("MODE" => "html",  "NAME" => 'Текст о получателе'));?>					
							
							</div>
<?						
						}
					}
					?>
				</div>
			<?
		}
	}
	function CheckDateText($SHOWROM)
	{
		if( $SHOWROM == 1 )
			$dayDelivery = 1;
		else
			$dayDelivery = 3;
		
		$dayofweek = FormatDate('D', strtotime('NOW') + $dayDelivery * 24 * 3600);
		$dayoDeliveryCheck = date('d.m', strtotime('NOW') + $dayDelivery * 24 * 3600);

		if ($dayoDeliveryCheck == '23.02' || $dayoDeliveryCheck == '08.03' || $dayoDeliveryCheck == '12.06' || $dayoDeliveryCheck == '04.11') {
			$dayofweek = FormatDate('D', strtotime('NOW') + ($dayDelivery + 1) * 24 * 3600);
			$dayDelivery = $dayDelivery + 1;
		}

		if ($dayofweek == 'Сб') {
			$dayRes = date('d.m.Y', strtotime('NOW') + ($dayDelivery + 2) * 24 * 3600);
		} elseif ($dayofweek == 'Вс') {
			$dayRes = date('d.m.Y', strtotime('NOW') + ($dayDelivery + 1) * 24 * 3600);
		} else {
			$dayRes =  date('d.m.Y', strtotime('NOW') + $dayDelivery * 24 * 3600);
		}
		return $dayRes;
	}

	function calcPodiem($arResult, $LIFT = '', $ETAJ = 1 )
	{
		global $PODIEM_PRICE;
		//цены подъема
$resU = CIBlockElement::GetList(array('SORT' => 'DESC'), array('IBLOCK_ID' => 28, 'ACTIVE' => 'Y'), false, false, array('IBLOCK_ID', 'ID', 'PROPERTY_OT', 'PROPERTY_DO', 'PROPERTY_LIFT', 'PROPERTY_NO_LIFT'));
while ($row = $resU->Fetch()) {
	/*if ($row['PROPERTY_OT_VALUE'] <= $arResult['PROPERTIES']['WEIGHT']['VALUE'] && $row['PROPERTY_DO_VALUE'] >= $arResult['PROPERTIES']['WEIGHT']['VALUE']) {
		$PODIEM = $row['PROPERTY_LIFT_VALUE'];
	}*/
	
	$arPodiem[] = array(
	'OT' => $row['PROPERTY_OT_VALUE'],
	'DO' => $row['PROPERTY_DO_VALUE'],
	'LIFT' => $row['PROPERTY_LIFT_VALUE'],
	'ETAJ' => $row['PROPERTY_NO_LIFT_VALUE'],
	);
}		
		
		if( intval($ETAJ) == 0 )
			$ETAJ = 1;
		$PRICE_ALL = 0;
		foreach ($arResult["GRID"]["ROWS"] as $k => $arData)
		{
			$strVES = '';
			$VES = '';
			if( $arResult['GABARITS'][$arData['data']['PRODUCT_ID']]['WEIGHT'] != '' )
			{
				$strVES = ', вес '.$arResult['GABARITS'][$arData['data']['PRODUCT_ID']]['WEIGHT'].' кг.';
				$VES = $arResult['GABARITS'][$arData['data']['PRODUCT_ID']]['WEIGHT'];
			}
			elseif( $arData['data']['WEIGHT'] != '' )
			{
				$strVES = ', вес '.$arData['data']['WEIGHT'].' кг.';
				$VES = $arData['data']['WEIGHT'];
			}			
			$PRICE = 'обсуждается отдельно';
			
			if( $VES != '' )
				foreach( $arPodiem as $_podiem )
				{
					if( $_podiem['OT'] <= $VES && $_podiem['DO'] >= $VES )
					{
						if( $LIFT == 'Y' )
						{
							$PRICE = $_podiem['LIFT'].' ₽';
							$PRICE_ALL = $PRICE_ALL + $PRICE;
						}
						else
						{
							$PRICE = $ETAJ * $_podiem['ETAJ'].' ₽';
							$PRICE_ALL = $PRICE_ALL + $PRICE;
						}
					}
				}
			
			
			$return .='
			<div class="summary_podiem_item">
					<div class="summary_podiem_item_left">
						<div class="name">'.$arData['data']['NAME'].'</div>
						<div class="quantity_row">'.$arData['data']['QUANTITY'].' шт. '.$strVES.'</div>					
					</div>
					<div class="summary_podiem_item_right">
					'.$PRICE.'
					</div>
			</div>		
					';			
		}
		if( $PRICE_ALL > 0 )
		{
			global $PERSON_TYPE;
			if( $PERSON_TYPE == 1 )
			{
				$ALL_PODIEM_CODE = 'ORDER_PROP_58';
			}
			else
				$ALL_PODIEM_CODE = 'ORDER_PROP_59';
			$return .='<div class="summary_podiem_itogo">
		Итого:<span>'.$PRICE_ALL.'</span>
		</div><input type=hidden name="'.$ALL_PODIEM_CODE.'" class="ALL_PODIEM" value="'.$PRICE_ALL.'">';
			 $PODIEM_PRICE = $PRICE_ALL;
		}
		return $return;
	}	
}
?>