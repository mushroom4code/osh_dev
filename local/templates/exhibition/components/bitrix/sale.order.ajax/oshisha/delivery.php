<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $fieldsProp;
?>

<script type="text/javascript">
	function fShowStore(id, showImages, formWidth, siteId)
	{
		var strUrl = '<?=$templateFolder?>' + '/map.php';
		var strUrlPost = 'delivery=' + id + '&showImages=' + showImages + '&siteId=' + siteId;

		var storeForm = new BX.CDialog({
					'title': '<?=GetMessage('SOA_ORDER_GIVE')?>',
					head: '',
					'content_url': strUrl,
					'content_post': strUrlPost,
					'width': formWidth,
					'height':450,
					'resizable':false,
					'draggable':false
				});

		var button = [
				{
					title: '<?=GetMessage('SOA_POPUP_SAVE')?>',
					id: 'crmOk',
					'action': function ()
					{
						GetBuyerStore();
						BX.WindowManager.Get().Close();
					}
				},
				BX.CDialog.btnCancel
			];
		storeForm.ClearButtons();
		storeForm.SetButtons(button);
		storeForm.Show();
	}

	function GetBuyerStore()
	{
		BX('BUYER_STORE').value = BX('POPUP_STORE_ID').value;
		//BX('ORDER_DESCRIPTION').value = '<?=GetMessage("SOA_ORDER_GIVE_TITLE")?>: '+BX('POPUP_STORE_NAME').value;
		BX('store_desc').innerHTML = BX('POPUP_STORE_NAME').value;
		BX.show(BX('select_store'));
	}

	function showExtraParamsDialog(deliveryId)
	{
		var strUrl = '<?=$templateFolder?>' + '/delivery_extra_params.php';
		var formName = 'extra_params_form';
		var strUrlPost = 'deliveryId=' + deliveryId + '&formName=' + formName;

		if(window.BX.SaleDeliveryExtraParams)
		{
			for(var i in window.BX.SaleDeliveryExtraParams)
			{
				strUrlPost += '&'+encodeURI(i)+'='+encodeURI(window.BX.SaleDeliveryExtraParams[i]);
			}
		}

		var paramsDialog = new BX.CDialog({
			'title': '<?=GetMessage('SOA_ORDER_DELIVERY_EXTRA_PARAMS')?>',
			head: '',
			'content_url': strUrl,
			'content_post': strUrlPost,
			'width': 500,
			'height':200,
			'resizable':true,
			'draggable':false
		});

		var button = [
			{
				title: '<?=GetMessage('SOA_POPUP_SAVE')?>',
				id: 'saleDeliveryExtraParamsOk',
				'action': function ()
				{
					insertParamsToForm(deliveryId, formName);
					BX.WindowManager.Get().Close();
				}
			},
			BX.CDialog.btnCancel
		];

		paramsDialog.ClearButtons();
		paramsDialog.SetButtons(button);
		//paramsDialog.adjustSizeEx();
		paramsDialog.Show();
	}

	function insertParamsToForm(deliveryId, paramsFormName)
	{
		var orderForm = BX("ORDER_FORM"),
			paramsForm = BX(paramsFormName);
			wrapDivId = deliveryId + "_extra_params";

		var wrapDiv = BX(wrapDivId);
		window.BX.SaleDeliveryExtraParams = {};

		if(wrapDiv)
			wrapDiv.parentNode.removeChild(wrapDiv);

		wrapDiv = BX.create('div', {props: { id: wrapDivId}});

		for(var i = paramsForm.elements.length-1; i >= 0; i--)
		{
			var input = BX.create('input', {
				props: {
					type: 'hidden',
					name: 'DELIVERY_EXTRA['+deliveryId+']['+paramsForm.elements[i].name+']',
					value: paramsForm.elements[i].value
					}
				}
			);

			window.BX.SaleDeliveryExtraParams[paramsForm.elements[i].name] = paramsForm.elements[i].value;

			wrapDiv.appendChild(input);
		}

		orderForm.appendChild(wrapDiv);

		BX.onCustomEvent('onSaleDeliveryGetExtraParams',[window.BX.SaleDeliveryExtraParams]);
	}
</script>
<div class="bx-soa-section mb-4 bx-active bx-step-warning bx-selected" id="bx-soa-delivery">
<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?=$arResult["BUYER_STORE"]?>" />

	<?
	if(!empty($arResult["DELIVERY"]))
	{
		
		foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
			if ($delivery_id !== 0 && intval($delivery_id) <= 0)
			{
				foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
				{
					if( $arProfile["CHECKED"] == 'Y')
						$DELIVERY_ID = $profile_id;
				}
			}
			else
			{
					if( $arDelivery["CHECKED"] == 'Y')
						$DELIVERY_ID = $profile_id;				
			}
			
		
		
		?>
		<div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
			<div class="bx-soa-section-title" data-entity="section-title">Как Вы хотите получить заказ?</div>
		</div>
		<div class="box_with_delivery_type">
			<div class="bx-soa-section-content bx-wrap-delivery">
					
					<div class="delivery bx-soa-pp-company <?if(in_array($DELIVERY_ID,$arCourierID)):?>bx-selected<?endif;?>">
						<div class="bx-soa-pp-company-graf-container  box_with_delivery mb-3">
							<input
								type="radio"
								class="bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0"
								id="ID_DELIVERY_COURIER"
								name="CHECK_COURIER"
								value=1
								<?if($_POST['CHECK_COURIER'] == 1):?>checked<?endif;?>
								/>
								<div class="bx-soa-pp-company-curs"></div>
							<label class="bx-soa-pp-company-smalltitle color_black font_weight_600" for="ID_DELIVERY_COURIER">



								<div class="bx_description">

									<span>
										Доставка курьером
									</span>



									<p>
										Сегодня - если заказ сделан до 15:00; Ночная или на след. день - если заказ сделан до 20:00. В воскресенье доставка не работает.	
									</p>
								</div>

							</label>

						</div>
					</div>			
				<div class="block_courier" <?if($_POST['CHECK_COURIER'] == 1):?><?endif;?> >
						<div class="block_courier_left">
					
						<?PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_Y"], $arParams["TEMPLATE_LOCATION"], $arResult, array(6,12));?>
						<?=$fieldsProp['COURIER']?>
						</div>
						<div class="block_courier_right">
						
						</div>
				
				</div>			
			
		<?

		foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
		{
			if(in_array($delivery_id, $arCourierID)) continue;

			$arDelivery["PRICE_FORMATED"] = str_replace('руб', '₽', $arDelivery["PRICE_FORMATED"]);
			
			if ($delivery_id !== 0 && intval($delivery_id) <= 0)
			{
				foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
				{
					
					?>
					<div class="delivery bx-soa-pp-company <?if( $arProfile["CHECKED"] == 'Y'):?>bx-selected<?endif;?>">
						<div class="bx-soa-pp-company-graf-container  box_with_delivery mb-3">

							<input
								type="radio"
								class="bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0"
								id="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>"
								name="<?=htmlspecialcharsbx($arProfile["FIELD_NAME"])?>"
								value="<?=$delivery_id.":".$profile_id;?>"
								<?=$arProfile["CHECKED"] == "Y"  ? "checked=\"checked\"" : "";?>
								onclick="submitForm();"
								/>
								<div class="bx-soa-pp-company-curs"></div>
							<label class="bx-soa-pp-company-smalltitle color_black font_weight_600" for="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>">

<?

								if($arDelivery["ISNEEDEXTRAINFO"] == "Y")
									$extraParams = "showExtraParamsDialog('".$delivery_id.":".$profile_id."');";
								else
									$extraParams = "";

								?>


								<div class="bx_description">

									<span onclick="BX('ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>').checked=true;<?=$extraParams?>submitForm();">
										<?=htmlspecialcharsbx($arDelivery["TITLE"])." (".htmlspecialcharsbx($arProfile["TITLE"]).")";?>
									</span>



									<p onclick="BX('ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>').checked=true;submitForm();">
										<?if (strlen($arProfile["DESCRIPTION"]) > 0):?>
											<?=nl2br($arProfile["DESCRIPTION"])?>
										<?else:?>
											<?=nl2br($arDelivery["DESCRIPTION"])?>
										<?endif;?>
									</p>
								</div>

							</label>

						</div>
					</div>
					<?
				} // endforeach
			}
			else // stores and courier
			{
				if( $NEXT_CHECKED == 1)
				{
					$arDelivery["CHECKED"]="Y";
					$NEXT_CHECKED = 0;
				}
				if (count($arDelivery["STORE"]) > 0)
					$clickHandler = "onClick = \"fShowStore('".$arDelivery["ID"]."','".$arParams["SHOW_STORES_IMAGES"]."','".$width."','".SITE_ID."')\";";
				else
					$clickHandler = "onClick = \"BX('ID_DELIVERY_ID_".$arDelivery["ID"]."').checked=true;submitForm();\"";
				?>
					<div class="delivery bx-soa-pp-company <?if( $arDelivery["CHECKED"] == 'Y'):?>bx-selected<?endif;?>" <?=$clickHandler?>>

						<div class="bx-soa-pp-company-graf-container  box_with_delivery mb-3">

							<input type="radio"
								id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>"
								name="<?=htmlspecialcharsbx($arDelivery["FIELD_NAME"])?>"
								class="bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0"
								value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y") echo " checked";?>
								onclick="submitForm();"
								/>
								<div class="bx-soa-pp-company-curs"></div>
							<label class="bx-soa-pp-company-smalltitle color_black font_weight_600" for="ID_DELIVERY_ID_<?=$arDelivery["ID"]?>" >



								<div class="bx_description">
									<div class="bx-soa-pp-company-smalltitle color_black font_weight_600"><?= htmlspecialcharsbx($arDelivery["NAME"])?></div>

									<p>
										<?
										if (strlen($arDelivery["DESCRIPTION"])>0)
											echo $arDelivery["DESCRIPTION"]."";


									?>
									</p>
								</div>

							</label>

						<div class="clear"></div>
					</div>
				</div>
				<?
				if( $arTypeDelivery['PICKUP'] == $arDelivery["ID"] &&  $arDelivery["CHECKED"] == 'Y' ):
				?>
				<div class="block_pickup_address">
					<div class="block_pickup_address_left">
						<div class="block_pickup_name"><img src="/local/assets/images/icon_location.svg" class="icon_location"> Краснобогатырская, 2с2 </div>
						<div class="block_pickup_metro pickup-station-img ">м. ВДНХ</div>
						<div class="block_pickup_worktime pickup-time-img ">09:00 - 21:00</div>
						<div class="block_pickup_info pickup-info-img">Можно забрать завтра или в течение 14 дней</div>
					</div>
					<div class="block_pickup_address_right">
						<div class="block_pickup_address_map">
						<a href="/local/assets/images/schema.jpeg" class="fancybox"><img src="/local/assets/images/schema.jpeg" height=160></a>
						</div>
					</div>					
					
				</div>
				
				<h6>Данные для пропуска</h6>
				<div class="block_pickup">
					
					<?=$fieldsProp['PICKUP']?>
				</div>
				<?
				elseif( $arTypeDelivery['COURIER'] == $arDelivery["ID"] &&  $arDelivery["CHECKED"] == 'Y' ):
				?>

				<?
				endif;
			}
		}
		?>
			</div>
		</div>
		<?
	}
?>

</div>