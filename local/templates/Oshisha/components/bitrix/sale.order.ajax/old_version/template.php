<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!function_exists("getColumnName"))
{
	function getColumnName($arHeader)
	{
		return (strlen($arHeader["name"]) > 0) ? $arHeader["name"] : GetMessage("SALE_".$arHeader["id"]);
	}
}

if (!function_exists("cmpBySort"))
{
	function cmpBySort($array1, $array2)
	{
		if (!isset($array1["SORT"]) || !isset($array2["SORT"]))
			return -1;

		if ($array1["SORT"] > $array2["SORT"])
			return 1;

		if ($array1["SORT"] < $array2["SORT"])
			return -1;

		if ($array1["SORT"] == $array2["SORT"])
			return 0;
	}
}
if($USER->IsAuthorized() || $arParams["ALLOW_AUTO_REGISTER"] == "Y")
{
	if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y" || $arResult["NEED_REDIRECT"] == "Y")
	{
		if(strlen($arResult["REDIRECT_URL"]) > 0)
		{
			$APPLICATION->RestartBuffer();
			?>
			<script type="text/javascript">
				window.top.location.href='<?=CUtil::JSEscape($arResult["REDIRECT_URL"])?>';
			</script>
			<?
			die();
		}

	}
}
	if( count($arResult["GRID"]["ROWS"]) == 0 && !$_GET['ORDER_ID'] )
		LocalRedirect('/');


	global $fieldsProp, $arTypeDelivery, $SETTINGS;
	
	$arTypeDelivery = array(
	'PICKUP' => 41,
	'COURIER' => 42,
	'REGION' => 43
	
	);
	$baseCourier = 47;
	$deliveryDay = 42;
	$deliveryExpress = 44;
	$deliveryNight = 45;
	$arCourierID = array($deliveryDay,$deliveryExpress,$deliveryNight);
	$arAutoDelivery = array(
	48,49,54,55,52
	);

$APPLICATION->SetAdditionalCSS($templateFolder."/style.css");
?>


<div id="order_form_div" class="order-checkout">
<NOSCRIPT>
	<div class="errortext"><?=GetMessage("SOA_NO_JS")?></div>
</NOSCRIPT>
<script>BX.setCookie('DISTANCE', '', {expires: -1}); BX.setCookie('TYPE_DISTANCE', '', {expires: -1});</script>
<?

?>

<div class="bx_order_make">
	<?
	if(!$USER->IsAuthorized() && $arParams["ALLOW_AUTO_REGISTER"] == "N")
	{
		if(!empty($arResult["ERROR"]))
		{
			foreach($arResult["ERROR"] as $v)
				echo ShowError($v);
		}
		elseif(!empty($arResult["OK_MESSAGE"]))
		{
			foreach($arResult["OK_MESSAGE"] as $v)
				echo ShowNote($v);
		}

		include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/auth.php");
	}
	else
	{
		if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y" || $arResult["NEED_REDIRECT"] == "Y")
		{
			if(strlen($arResult["REDIRECT_URL"]) == 0)
			{
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/confirm.php");
			}
		}
		else
		{
			?>
			<script type="text/javascript">

			<?if(CSaleLocation::isLocationProEnabled()):?>

				<?
				// spike: for children of cities we place this prompt
				$city = \Bitrix\Sale\Location\TypeTable::getList(array('filter' => array('=CODE' => 'CITY'), 'select' => array('ID')))->fetch();
				?>

				BX.saleOrderAjax.init(<?=CUtil::PhpToJSObject(array(
					'source' => $this->__component->getPath().'/get.php',
					'cityTypeId' => intval($city['ID']),
					'messages' => array(
						'otherLocation' => '--- '.GetMessage('SOA_OTHER_LOCATION'),
						'moreInfoLocation' => '--- '.GetMessage('SOA_NOT_SELECTED_ALT'), // spike: for children of cities we place this prompt
						'notFoundPrompt' => '<div class="-bx-popup-special-prompt">'.GetMessage('SOA_LOCATION_NOT_FOUND').'.<br />'.GetMessage('SOA_LOCATION_NOT_FOUND_PROMPT', array(
							'#ANCHOR#' => '<a href="javascript:void(0)" class="-bx-popup-set-mode-add-loc">',
							'#ANCHOR_END#' => '</a>'
						)).'</div>'
					)
				))?>);

			<?endif?>

			var BXFormPosting = false;
			function submitForm(val)
			{
				if (BXFormPosting === true)
					return true;

				BXFormPosting = true;
				if(val != 'Y')
					BX('confirmorder').value = 'N';
				if(val == 'Y' && $('input[name="DELIVERY_ID"]').val() == 47 )
				{
					alert('Выберите вариант доставки!');
					return false;
				}
				var orderForm = BX('ORDER_FORM');
				console.log(orderForm);
				BX.showWait();

				<?if(CSaleLocation::isLocationProEnabled()):?>
					BX.saleOrderAjax.cleanUp();
				<?endif?>

				BX.ajax.submit(orderForm, ajaxResult);

				return true;
			}

			function ajaxResult(res)
			{
				var orderForm = BX('ORDER_FORM');
				try
				{
					// if json came, it obviously a successfull order submit

					var json = JSON.parse(res);
					BX.closeWait();

					if (json.error)
					{
						BXFormPosting = false;
						return;
					}
					else if (json.redirect)
					{
						window.top.location.href = json.redirect;
					}
				}
				catch (e)
				{
					// json parse failed, so it is a simple chunk of html

					BXFormPosting = false;
					BX('order_form_content').innerHTML = res;
					if( $('.block_code_ADDRESS').val() == '')
					{
						$('.hided_delivery').hide();
						$('.block_courier_wrap').hide();
						$('.block_auto_description').hide();
					}
					CheckDate(1);
					CheckViewDelivery(1);
					checkPickupType();
					$('.overlay_order').hide();
					$('.block_code_PHONE').inputmask("+7 (999)-999-9999", {clearMaskOnLostFocus: false});
					<?if(CSaleLocation::isLocationProEnabled()):?>
						BX.saleOrderAjax.initDeferredControl();
					<?endif?>
				}
				
				BX.closeWait();
				BX.onCustomEvent(orderForm, 'onAjaxSuccess');
			}

			function SetContact(profileId)
			{
				BX("profile_change").value = "Y";
				submitForm();
			}
			$(document).ready(function() {
		
			//submitForm();
			});

			</script>


			<?if($_POST["is_ajax_post"] != "Y")
			{
				?><form action="<?=$APPLICATION->GetCurPage();?>" method="POST" name="ORDER_FORM" class="bx-soa-wrapper mb-4<?= $themeClass ?>" id="ORDER_FORM" enctype="multipart/form-data">
				<?=bitrix_sessid_post()?>
				<div id="order_form_content" class="row ">
				<?
			}
			else
			{
				$APPLICATION->RestartBuffer();
			}

			if($_REQUEST['PERMANENT_MODE_STEPS'] == 1)
			{
				?>
				<input type="hidden" name="PERMANENT_MODE_STEPS" value="1" />
				<?
			}

			if(!empty($arResult["ERROR"]) && $arResult["USER_VALS"]["FINAL_STEP"] == "Y")
			{
				foreach($arResult["ERROR"] as $v)
					echo ShowError($v);
				?>
				<script type="text/javascript">
					top.BX.scrollToNode(top.BX('ORDER_FORM'));
				</script>
				<?
			}
?>
			<div id="col_left" class="col-lg-8 col-md-7">
			<h5 class="mb-4 bx-soa-section-title">Покупатель</h5>
			<div class="bx-soa">
<?
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/person_type.php");
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/props.php");

				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/delivery.php");

				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/paysystem.php");
			

			


			?>
                        <div class="new_block_with_sms bx-soa-section" id="new_block_with_sms" style="display:none">
                            <div id="new_block_with_sms_box" class="bx-selected">
                                <div class="mb-4 mt-2 bx-soa-section-title">Подтверждение заказа через:</div>
                                <div class="form-check d-flex flex-row">
                                    <div class="mr-5">
                                        <input type="radio" class="form-check-input  mr-1 check_custom"
                                               value="sms" id="sms" name="checked" checked/>
                                        <label for="sms">Смс</label>
                                    </div>
                                    <div class="mr-5">
                                        <input type="radio" id="telegram" class="form-check-input  mr-1 check_custom"
                                               value="telegram" name="checked"/>
                                        <label for="telegram">Телеграм</label>
                                    </div>
                                    <div class="mr-5" >
                                        <input type="radio" class="form-check-input mr-1  check_custom"
                                               value="telephone" name="checked" id="telephone"/>
                                        <label for="telephone">Звонок</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="new_block_with_comments" id="new_block_with_comments">
                            <div id="new_block_with_comment_box">
<div class="form-group bx-soa-customer-field"><label for="orderDescription" class="bx-soa-customer-label">Комментарии к заказу:</label>
<textarea id="orderDescription" cols="4" placeholder="Введите комментарий к заказу..." class="form-control bx-soa-customer-textarea bx-ios-fix" name="ORDER_DESCRIPTION"><?=$arResult["USER_VALS"]["ORDER_DESCRIPTION"]?></textarea></div>							
							</div>
                        </div>	
				</div>
			</div>
            <div id="col_right" class="col-lg-4 col-md-5 ">
				<Div class="stiky_right">
<?
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary.php");
			if(strlen($arResult["PREPAY_ADIT_FIELDS"]) > 0)
				echo $arResult["PREPAY_ADIT_FIELDS"];
?>

                <div class="basket-checkout-container" data-entity="basket-checkout-aligner" style="display:none;">
                    <div class="basket-checkout-section-inner">
                        <div class="basket-coupon-section">
                            <div class="basket-coupon-block-field">
                                <div class="basket-coupon-block-field-description mb-4">
                                    <span class="text_filter_basket">  <b>  Введите промокод или сертификат</b></span>
                                </div>
                                <div class="form">
                                    <div class="form-group" style="position: relative;">
                                        <input type="text" class="form-control mb-4 input_code" id=""
                                               placeholder="Введите код" data-entity="basket-coupon-input">
                                        <div class="basket-checkout-block  mb-4">
                                            <button class="btn_basket  basket-coupon-block-coupon-btn">
                                                Применить
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>



				</div>
		
			</div>
			<?if($_POST["is_ajax_post"] != "Y")
			{
				?>
					</div>
					<input type="hidden" name="DELIVERY_RESULT" id="DELIVERY_RESULT" value="">
					<input type="hidden" name="DAYS_DELIVERY" id="DAYS_DELIVERY" value="">
					<input type="hidden" name="TYPE_DISTANCE" id="TYPE_DISTANCE" value="">
					<input type="hidden" name="DISTANCE" id="DISTANCE" value="">
					<input type="hidden" name="confirmorder" id="confirmorder" value="Y">
					<input type="hidden" name="profile_change" id="profile_change" value="N">
					<input type="hidden" name="is_ajax_post" id="is_ajax_post" value="Y">
					<input type="hidden" name="json" value="Y">
				<!--	ORDER SAVE BLOCK	-->
				<div id="bx-soa-orderSave">
					<div class="checkbox">
						<?
						if ($arParams['USER_CONSENT'] === 'Y')
						{
							$APPLICATION->IncludeComponent(
								'bitrix:main.userconsent.request',
								'',
								array(
									'ID' => $arParams['USER_CONSENT_ID'],
									'IS_CHECKED' => $arParams['USER_CONSENT_IS_CHECKED'],
									'IS_LOADED' => $arParams['USER_CONSENT_IS_LOADED'],
									'AUTO_SAVE' => 'N',
									'SUBMIT_EVENT_NAME' => 'bx-soa-order-save',
									'REPLACE' => array(
										'button_caption' => isset($arParams['~MESS_ORDER']) ? $arParams['~MESS_ORDER'] : $arParams['MESS_ORDER'],
										'fields' => $arResult['USER_CONSENT_PROPERTY_DATA']
									)
								)
							);
						}
						?>
						</div>
					
	
				</div>
				</form>
				<?
				if($arParams["DELIVERY_NO_AJAX"] == "N")
				{
					?>
					<div style="display:none;"><?$APPLICATION->IncludeComponent("bitrix:sale.ajax.delivery.calculator", "", array(), null, array('HIDE_ICONS' => 'Y')); ?></div>
					<?
				}
			}
			else
			{
				?>
				<script type="text/javascript">
					top.BX('confirmorder').value = 'Y';
					top.BX('profile_change').value = 'N';
				</script>
				<?
				die();
			}
		}
	}
	?>
	</div>
</div>

<?if(CSaleLocation::isLocationProEnabled()):?>

	<div style="display: none">
		<?// we need to have all styles for sale.location.selector.steps, but RestartBuffer() cuts off document head with styles in it?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:sale.location.selector.steps",
			".default",
			array(
			),
			false
		);?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:sale.location.selector.search",
			".default",
			array(
			),
			false
		);?>
	</div>

<?endif?>

<script>
	$('.box_code_DATE').hide();
	function ChangeCity()
	{
		console.log('ChangeCity');
		$('.DATE_ORDER').val('');
		$('.DATE_ORDER').data('js-date', '');		
		$('.box_code_DATE').hide();
		$('.DELIVERY_ID').val('');
		$('.delivery_courier_item').hide();
		$('courier_alert').hide();	
		$('#TYPE_DISTANCE').val('');
		$('#DISTANCE').val('');
		$('#DAYS_DELIVERY').val('');
		
	}
	 
	function CheckCity( type_check )
	{
		let title = $(".bx-ui-sls-fake").attr('title');
		let inp = $('.box_code_LOCATION  input').val();
		
		
		if( inp == 166 || inp == 3130)
		{
			
			return true;
		}

		return false;
	}
	function CheckDate( type_check )
	{
		let TYPE_DISTANCE = $('#TYPE_DISTANCE').val();
		let DISTANCE = $('#DISTANCE').val();
		let DAYS_DELIVERY = $('#DAYS_DELIVERY').val();

		
		
		if( DAYS_DELIVERY != '' && ( DISTANCE > 5 && CheckCity(type_check) === false ) )
		{
			if( type_check != 1 )
			{
				$('.DATE_ORDER').val('');
				$('.DATE_ORDER').data('js-date', '');
				
			}
			$('.date_block').removeClass('selected').hide();
			console.log(DAYS_DELIVERY);
			if( DAYS_DELIVERY == 'VtPt' )
			{			
				$('.date_block[data-day="2"]').show();
				$('.date_block[data-day="5"]').show();
			}
			if( DAYS_DELIVERY == 'PnCht' )
			{
				$('.date_block[data-day="1"]').show();
				$('.date_block[data-day="4"]').show();
			}
			if( DAYS_DELIVERY == 'SrSb' )
			{
				$('.date_block[data-day="3"]').show();
				$('.date_block[data-day="6"]').show();
			}	
		}		
	}
	 
	function CheckViewDelivery(type_check)
	{
		/*if( !$('#ID_DELIVERY_COURIER').is(':checked') )
		{
			return;
		}*/
		let TYPE_DISTANCE = $('#TYPE_DISTANCE').val();
		let DISTANCE = $('#DISTANCE').val();
		let DAYS_DELIVERY = $('#DAYS_DELIVERY').val();
		let DATE_ORDER = $('#DATE_ORDER').attr("data-js-date");
		console.log('DATE_START:'+DATE_ORDER);
		$('.delivery_courier_item').hide();
		$('courier_alert').hide();
		
		if( DATE_ORDER == '')
			return;		
		var TimeCheck = <?=date('H')?>;
		
		var d = new Date();
		var nWeek = d.getDay(); 		
 		//if( nWeek == 0)
		//nWeek = 7;
		var dN = new Date(DATE_ORDER);
		var nWeekSelected = dN.getDay(); 
		

		console.log('DATES:'+DATE_ORDER+' - '+nWeekSelected+' - '+nWeek);
		
		var CheckDay = 0;

		if( TYPE_DISTANCE == 'INNER' && TimeCheck < 15 && nWeek == nWeekSelected && nWeekSelected != 6 && nWeekSelected != 7 ) //экспресс только до 15 в пн-пт
		{				
			$('.delivery_courier_item[data-id=<?=$deliveryExpress?>]').show();
			CheckDay = 1;
		}

		//дневная доставка	
		if( TYPE_DISTANCE == 'INNER' || DISTANCE < 200 )
		{ 		
			if( TimeCheck < 19 && nWeekSelected > nWeek ) //Если заказ оформляется до 19, то разрешаем получить его завтра и позднее
			{
				$('.delivery_courier_item[data-id=<?=$deliveryDay?>]').show();
				CheckDay = 1;
			}	
			else if( TimeCheck >= 19 && nWeekSelected > nWeek + 1 )
			{
				$('.delivery_courier_item[data-id=<?=$deliveryDay?>]').show();
				CheckDay = 1;
			}				
				
			
		}
		
		//ночная доставка
		console.log('PRE SELECT NIGHT DAY'+CheckCity(type_check));
		if( TYPE_DISTANCE == 'INNER' || DISTANCE <= 5 || CheckCity(type_check) )
		{	console.log('SELECT NIGHT DAY'+nWeekSelected);
			if( TimeCheck < 18 && nWeekSelected != 6 && nWeekSelected != 7 )
			{
				$('.delivery_courier_item[data-id=<?=$deliveryNight?>]').show();
				CheckDay = 1;
			}
			else if( TimeCheck >= 18 && nWeekSelected > nWeek && nWeekSelected != 6 && nWeekSelected != 7 )
			{
				$('.delivery_courier_item[data-id=<?=$deliveryNight?>]').show();
				CheckDay = 1;
			}
		}
			console.log(CheckDay);
		if( DISTANCE > 200)
		{
			$('.courier_alert').html('Курьерская доставка не доступна по вашему адресу. Пожалуйста выберите другие варианты доставки!').show();
		}
		else if( CheckDay == 0 && $('.block_code_ADDRESS input').val() != '' )
		{
			$('.courier_alert').html('Нет доступных вариантов доставки на этот день!').show();			
		}
		
		

			
		
	}

	function FindDistance()
	{
		var address = $('.block_code_ADDRESS').val();
		
			if( address != '' )
			{
				var url = '/local/templates/Oshisha/components/bitrix/sale.order.ajax/old_version/ajax_dist.php';
				var data_ct = {'query': address};
				$('.overlay_order').show();
				$.ajax({
					type:"POST",
					url: url,
					data: data_ct,
					success: function(msg){
						
						console.log(msg);
						if( msg == 'INNER' ) 
						{
							$('#TYPE_DISTANCE').val(msg);
							$('#DISTANCE').val(0);
							$('#DAYS_DELIVERY').val('');
							BX.setCookie('TYPE_DISTANCE', 'INNER', {expires: 86400});
							$('.box_code_DATE').show();
							$('#DELIVERY_RESULT').val(1);
							$('.hide_delivery').show();
						}
						else
						{
							var Params= JSON.parse(msg);
							if( Params.distance )
							{
								$('#TYPE_DISTANCE').val('');
								$('#DISTANCE').val(Params.distance);
								$('#DAYS_DELIVERY').val(Params.type);
								BX.setCookie('TYPE_DISTANCE', '', {expires: 86400});
								BX.setCookie('DISTANCE', Params.distance, {expires: 86400});
								$('.box_code_DATE').show();
								$('#DELIVERY_RESULT').val(1);
								$('.hide_delivery').show();
							}
							else
							{
								console.log('Ошибка расчета');
							}
							
							if( Params.distance > 200 )
							{
								$('.courier_alert').html('Курьерская доставка не доступна по вашему адресу. Пожалуйста выберите другие варианты доставки!').show();
							}
						}
						submitForm();
						CheckDate();
						
					
					}
					});	
				
					
					$('.delivery-info').hide();	
			}		
		
	}

	$(document).ready(function() {

		$(document).on('change', '.block_code_ADDRESS', function()
		{	

			

		});		
		$(document).on('change', '#ORDER_PROP_7', function()
		{	//alert(2);
			//ORDER_PROP_7checkCity();
			console.log($(this).text());
			console.log($('#ORDER_PROP_7').val());

		});
		

		$(document).on('click', '.bx-soa-pp-company-paysistem', function()
		{
			$('.bx-soa-pp-company-paysistem').removeClass('bx-selected');
			$(this).addClass('bx-selected');
			$('.PAY_SYSTEM_ID').val($(this).attr('data-pay-id'));
			
		});			
		$(document).on('click', '.address_item', function()
		{
			let address = $(this).attr('data-address');
			console.log(address);
			$('.block_code_ADDRESS').val(address);
			var suggestion;
			/*suggestion.data.city = 'Челябинск';
			suggestion.data.country = 'Россия';
			suggestion.data.region = 'Челябинская обл';
			dadataSuggestions.setLocation(suggestion);*/		
			//FindDistance();
		});	
		
		$(document).on('click', '.date_block', function()
		{
		
			$('.date_block').removeClass('selected');
			let date = $(this).data('date');
			let js_date = $(this).data('js-date');
			
			$('.DATE_ORDER').val(date);
			$('.DATE_ORDER').attr('data-js-date', js_date);
			$(this).addClass('selected');
			CheckViewDelivery();
			//setTimeout(submitForm,500);
			
		});			
		
		$(document).on('change', '.bx-ui-sls-fake', function()
		{
			ChangeCity();

		});			
		$(document).on('change', 'input[name="DELIVERY_ID"]', function(){
		
			setTimeout(submitForm,100);
				
		});

		$(document).on('click', '.type_pickup_btn', function(){
			
			$(this).addClass('active');
			let type = $(this).attr('data-type');
			$('.TYPE_PICKUP').val(type);
			
			checkPickupType();
				
		});
		

$('.block_code_PHONE').inputmask("+7 (999)-999-9999", {clearMaskOnLostFocus: false});		
	});
	
	function checkPickupType()
	{
		$('.type_pickup_btn').removeClass('active');
			let type = $('.TYPE_PICKUP').val();
			if( type == 'men' || type == '')
			{
				$('.box_code_CAR_NUMBER').hide();
				$('.box_code_CAR_MODEL').hide();
				$('.type_pickup_btn.t_men').addClass('active');	
				 
			}
			else
			{
				$('.box_code_CAR_NUMBER').show();
				$('.box_code_CAR_MODEL').show();
				$('.type_pickup_btn.t_auto').addClass('active');				
			}		
	}
</script>



<div class="overlay_order">
	<div class="wrap_wait">
	<div class="wrap_wait_text">Ожидайте, пожалуйста...<br> Подбираем варианты доставки!</div>
	<img src="/local/assets/images/loader.gif">
	</div>
</div>

