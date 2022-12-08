<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

	if(typeof submitForm === 'function')
		BX.addCustomEvent('onDeliveryExtraServiceValueChange', function(){ submitForm(); });

</script>

<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?=$arResult["BUYER_STORE"]?>" />
<div class="bx_section">
	<?
	if(!empty($arResult["DELIVERY"]))
	{
		$width = ($arParams["SHOW_STORES_IMAGES"] == "Y") ? 850 : 700;
		?>
		<h4>
Доставка </h4>

		<?foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery){
		 if( in_array($arDelivery["ID"], array(7,15,47,49)))
        	$find = 1;
        }

        if( $find == 1){
        $TEXT_DELIVERY = COption::GetOptionString("main", "TEXT_DELIVERY", "");
        	?>

<div class=delivery_atent>
<?=$TEXT_DELIVERY?>
</div>
		<?}?>
		<?
         global $arIDsBasket;
         /*$arChemical = array(1047,3390,3391,3392,3393,3394,3395,3396,3397);
         foreach( $arIDsBasket['SECTION_ID'] as $SECTION_ID)
         {
         	if( in_array($SECTION_ID,$arChemical) )
         		$STOP_CDEK = 1;
         }*/
         //print_r($arResult["DELIVERY"]);
		foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
		{
            if( !$arDelivery['NAME']) continue;
			if($_POST["is_ajax_post"] != "Y")
				$arDelivery["CHECKED"] = '';

			if( ($delivery_id == 61 || $delivery_id == 62) && $STOP_CDEK == 1 )
				continue;

			if($arDelivery["ISNEEDEXTRAINFO"] == "Y")
				$extraParams = "showExtraParamsDialog('".$delivery_id."');";
			else
				$extraParams = "";

			if (count($arDelivery["STORE"]) > 0)
				$clickHandler = "onClick = \"fShowStore('".$arDelivery["ID"]."','".$arParams["SHOW_STORES_IMAGES"]."','".$width."','".SITE_ID."')\";";
			else
				$clickHandler = "onClick = \"BX('ID_DELIVERY_ID_".$arDelivery["ID"]."').checked=true;".$extraParams."submitForm();\"";

			?>
			<div class="bx_block w100 vertical">

				<div class="bx_element delivery_order">

					<input type="radio"
						id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>"
						name="<?=htmlspecialcharsbx($arDelivery["FIELD_NAME"])?>"
						value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y") echo " checked";?>
						onclick="submitForm();"
						/>

					<label for="ID_DELIVERY_ID_<?=$arDelivery["ID"]?>">

						<?
						if (count($arDelivery["LOGOTIP"]) > 0):

							$arFileTmp = CFile::ResizeImageGet(
								$arDelivery["LOGOTIP"]["ID"],
								array("width" => "95", "height" =>"55"),
								BX_RESIZE_IMAGE_PROPORTIONAL,
								true
							);

							$deliveryImgURL = $arFileTmp["src"];
						else:
							$deliveryImgURL = $templateFolder."/images/logo-default-d.gif";
						endif;


						if( in_array($arDelivery["ID"], array(1,2,3,4,5,6, 13)))
						{
							$margin = '20px';
						}
						else
						{
							$margin = '20px';
						}
						?>

						<div class="bx_logotype" style="margin-bottom:<?=$margin?>;"><span style='background-image:url(<?=$deliveryImgURL?>);' <?=$clickHandler?>></span></div>
							<strong <?=$clickHandler?>>
								<div class=name_delivery ><strong><?= htmlspecialcharsbx($arDelivery["NAME"])?></strong></div>
							</strong>
						<div class="bx_description">



							<span class="bx_result_price">
								<?if(isset($arDelivery["PRICE"]))
								{
                                    if( stripos($arDelivery["NAME"], 'транспортной компании') !== false )
                                    {
                                    	$text_dop = ' до ТК';
                                    }
                                    else
                                    {
                                    	$text_dop = ' ';
                                    }

									echo GetMessage("SALE_DELIV_PRICE")."".$text_dop.": <b>";
									if (isset($arDelivery['DELIVERY_DISCOUNT_PRICE'])
										&& round($arDelivery['DELIVERY_DISCOUNT_PRICE'], 4) != round($arDelivery["PRICE"], 4))
									{
										echo (strlen($arDelivery["DELIVERY_DISCOUNT_PRICE_FORMATED"]) > 0 ? $arDelivery["DELIVERY_DISCOUNT_PRICE_FORMATED"] : number_format($arDelivery["DELIVERY_DISCOUNT_PRICE"], 2, ',', ' '));
										echo "</b><br/><span style='text-decoration:line-through;color:#828282;'>".(strlen($arDelivery["PRICE_FORMATED"]) > 0 ? $arDelivery["PRICE_FORMATED"] : number_format($arDelivery["PRICE"], 2, ',', ' '))."</span>";
									}
									else
									{
										echo (strlen($arDelivery["PRICE_FORMATED"]) > 0 ? $arDelivery["PRICE_FORMATED"] : number_format($arDelivery["PRICE"], 2, ',', ' '))."</b>";
									}
									echo "<br />";

									if (strlen($arDelivery["PERIOD_TEXT"])>0)
									{
										echo GetMessage('SALE_SADC_TRANSIT')."".$text_dop.": <b>".$arDelivery["PERIOD_TEXT"]."</b>";
										echo '<br />';
									}
									if ($arDelivery["PACKS_COUNT"] > 1)
									{
										echo '<br />';
										echo GetMessage('SALE_SADC_PACKS').': <b>'.$arDelivery["PACKS_COUNT"].'</b>';
									}
								}
								elseif(isset($arDelivery["CALCULATE_ERRORS"]))
								{
									ShowError($arDelivery["CALCULATE_ERRORS"]);
								}
								else
								{
									$APPLICATION->IncludeComponent('bitrix:sale.ajax.delivery.calculator', '.default', array(
										"NO_AJAX" => $arParams["DELIVERY_NO_AJAX"],
										"DELIVERY_ID" => $delivery_id,
										"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
										"ORDER_PRICE" => $arResult["ORDER_PRICE"],
										"LOCATION_TO" => $arResult["USER_VALS"]["DELIVERY_LOCATION"],
										"LOCATION_ZIP" => $arResult["USER_VALS"]["DELIVERY_LOCATION_ZIP"],
										"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
										"ITEMS" => $arResult["BASKET_ITEMS"],
										"EXTRA_PARAMS_CALLBACK" => $extraParams,
										"ORDER_DATA" => $arResult['ORDER_DATA']
									), null, array('HIDE_ICONS' => 'Y'));

								}?>

							</span>
							<div <?=$clickHandler?>>
								<?
								if (strlen($arDelivery["DESCRIPTION"])>0)
									echo $arDelivery["DESCRIPTION"]."";
									if( $arDelivery["CALCULATE_DESCRIPTION"] ):
										echo '<div style="color:#3a3a3a; line-height:15px; margin-bottom:5px;" id="ajax_delivery_'.$arDelivery["ID"].'">'.$arDelivery["CALCULATE_DESCRIPTION"].'</div>';
									else:
										echo '<div style="color:#3a3a3a; line-height:15px; margin-bottom:5px;" id="ajax_delivery_'.$arDelivery["ID"].'"></div>';
									endif;
									echo "";
                                if( stripos($arDelivery["NAME"], 'транспортной компании') !== false )
                                {
                                	if( $arDelivery["CALCULATE_DESCRIPTION"] )
                                		echo '<div style="">Итоговая стоимость доставки определяется транспортной компанией.</div>';
                                	else
                                		echo '<div id="ajax_delivery_descr_'.$arDelivery["ID"].'">ВНИМАНИЕ! Указана стоимость доставки до транспортной компании.<br> Итоговая стоимость доставки определяется транспортной компанией.</div>';
                                }
								if (count($arDelivery["STORE"]) > 0 && $arDelivery['CHECKED'] == 'Y'):
								?>
									<span id="select_store"<?if(strlen($arResult["STORE_LIST"][$arResult["BUYER_STORE"]]["TITLE"]) <= 0) echo " style=\"display:none;\"";?>>
										<span class="select_store"><?=GetMessage('SOA_ORDER_GIVE_TITLE');?>: </span>
										<span class="ora-store" id="store_desc"><?=htmlspecialcharsbx($arResult["STORE_LIST"][$arResult["BUYER_STORE"]]["TITLE"])?></span>
									</span>
								<?
							endif;
							?>
							</div>
						</div>
					</label>
					<?if ($arDelivery['CHECKED'] == 'Y'):?>
						<table class="delivery_extra_services">
							<?foreach ($arDelivery['EXTRA_SERVICES'] as $extraServiceId => $extraService):?>
								<?if(!$extraService->canUserEditValue()) continue;?>
								<tr>
									<td >
										<?=$extraService->getName()?>
									</td>
									<td class="control">
										<?=$extraService->getEditControl('DELIVERY_EXTRA_SERVICES['.$arDelivery['ID'].']['.$extraServiceId.']')	?>
									</td>
									<td rowspan="2" class="price">
										<?

										if ($price = $extraService->getPrice())
										{
											echo GetMessage('SOA_TEMPL_SUM_PRICE').': ';
											echo '<strong>'.SaleFormatCurrency($price, $arResult['BASE_LANG_CURRENCY']).'</strong>';
										}

										?>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="description">
										<?=$extraService->getDescription()?>
									</td>
								</tr>
							<?endforeach?>
						</table>
					<?endif?>

					<div class="clear"></div>
				</div>
			</div>
			<?
		}
	}
?>
<div class="clear"></div>
</div>
<script>
BX.ready(function(){
InitDeliveryAjaxCustom();

	function InitDeliveryAjaxCustom(  )
	{
		var res = SetDeliveryAjaxCustom( 55 );


		var res = SetDeliveryAjaxCustom( 56 );


		var res = SetDeliveryAjaxCustom( 57 );

	}
	function SetDeliveryAjaxCustom( delivery_id )
	{

		form = BX('ORDER_FORM');
				if (form)
					var location_id = form.querySelector('input[type=text][name=ORDER_PROP_6]').value;

        //alert(form.querySelector('.bx_result_price').value);
     	BX.ajax({
              url: '<?=SITE_TEMPLATE_PATH?>/components/bitrix/sale.order.ajax/old_version/ajax_tk.php?delivery_id='+delivery_id+'&location_id='+location_id+'&RND='+Math.random(),
              method: 'POST',
              dataType: 'html',
              timeout: 30,
              async: true,
              processData: true,
              scriptsRunFirst: true,
              emulateOnload: true,
              start: true,
              cache: false,
              onsuccess: function(data){
                  console.log(data);

                  //$(data).appendTo("#ajax_delivery_"+delivery_id);
                  BX('ajax_delivery_'+delivery_id).innerHTML = data;
                  //BX('ajax_delivery_descr_'+delivery_id).replace("!ВНИМАНИЕ! Указана стоимость доставки до транспортной компании.", "");
                  BX('ajax_delivery_descr_'+delivery_id).innerHTML = 'Итоговая стоимость доставки определяется транспортной компанией.';

              },
              onfailure: function(){

              }
          });

    }
});
</script>