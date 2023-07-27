<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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


	global $fieldsProp, $arTypeDelivery;
	
	$arTypeDelivery = array(
	'PICKUP' => 41,
	'COURIER' => 42,
	'REGION' => 43
	
	);
	$arCourierID = array(42,44,45);
	
//echo '<pre>'; print_r($arResult);
//echo '<pre>';
//print_r($arResult);
//print_r($arResult["BASKET_ITEMS"]);
//проверим есть ли в списке товаров доставка
foreach( $arResult["BASKET_ITEMS"] as $BASKET_ITEMS )
{
	if( stripos($BASKET_ITEMS['DETAIL_PAGE_URL'],'delivery_pay') !== false ) 
		$DELVERY_PAY = 1;
	$arID[] = $BASKET_ITEMS['PRODUCT_ID'];
	
	//print_r(CCatalogProduct::GetOptimalPrice($BASKET_ITEMS['PRODUCT_ID']));
	
}
//получаем данные по товарам
//SHOWROOM
	global $ADDRESS_NO_ACTIVE;
	$arFilter = array(
	'ID' => $arID, 
	'ACTIVE' => 'Y',
	);

	$resU = CIBlockElement::GetList(Array(), $arFilter, false, false);
	while($ob = $resU->GetNextElement())
	{	
		$arFields = $ob->GetFields();
		$arProps = $ob->GetProperties();

		$arResult['GABARITS'][$arFields['ID']] = array(
		'WEIGHT' => $arProps['WEIGHT']['VALUE'],
		);
		
	}

//echo $TYPE;

$APPLICATION->SetAdditionalCSS($templateFolder."/style.css");

CJSCore::Init(array('fx', 'popup', 'window', 'ajax'));
?>

<a name="order_form"></a>

<div id="order_form_div" class="order-checkout">
<NOSCRIPT>
	<div class="errortext"><?=GetMessage("SOA_NO_JS")?></div>
</NOSCRIPT>

<?
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
			function submitForm(val)
			{
				if( val == 'Y')
				{
					var email = '';
					if( $('#ORDER_PROP_13').length > 0 )
						var email = $('#ORDER_PROP_13');
					else if( $('#ORDER_PROP_2').length > 0 )
						var email = $('#ORDER_PROP_2');
					
					if( !validateEmail(email.val() ) )
					{
						email.addClass('error');
						return false;
					}				
				}
				if(val != 'Y')
					BX('confirmorder').value = 'N';

				var orderForm = BX('ORDER_FORM');
				
				BX.showWait();
				<?if(CSaleLocation::isLocationProEnabled()):?>
					BX.saleOrderAjax.cleanUp();
				<?endif?>	
console.log(orderForm);				
				BX.ajax.submit(orderForm, ajaxResult);

				return true;
			}

			function ajaxResult(res)
			{
				try
				{
					var json = JSON.parse(res);
					BX.closeWait();

					if (json.error)
					{
						return;
					}
					else if (json.redirect)
					{
						window.top.location.href = json.redirect;
					}
				}
				catch (e)
				{
					BX('order_form_content').innerHTML = res;
					
					$('.fancybox').fancybox();
					$('.select2_date').select2({minimumResultsForSearch: -1});
					$('.select_TIME').select2({minimumResultsForSearch: -1});
					//$(".phone_req").mask("+7(999)999-99-99");
					<?if(CSaleLocation::isLocationProEnabled()):?>
						BX.saleOrderAjax.initDeferredControl();
					<?endif?>				
				}

				BX.closeWait();
			}

			function SetContact(profileId)
			{
				BX("profile_change").value = "Y";
				submitForm();
			}

	
			</script>
			<?if($_POST["is_ajax_post"] != "Y")
			{
				?>
				    <form action="<?= POST_FORM_ACTION_URI ?>" method="POST" name="ORDER_FORM" class="bx-soa-wrapper mb-4<?= $themeClass ?>" id="ORDER_FORM" enctype="multipart/form-data">
				<?=bitrix_sessid_post()?>
				<input type="hidden" name="location_type" value="code">
				<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult['BUYER_STORE'] ?>">
				<div id="order_form_content" class="row " >
				
				<?
			}
			else
			{
				$APPLICATION->RestartBuffer();
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
            <div class="col-lg-8 col-md-7">
                <h5 class="mb-4"><b>Покупатель</b><i class="fa fa-pencil" aria-hidden="true"></i></h5>
				<div class="bx-soa">
<?				global $returnRecipient, $returnRecipientCheck, $flagCheckOther, $globalProps;
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/person_type.php");
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/props.php");
				
?>


<?			
			
			/*if ($arParams["DELIVERY_TO_PAYSYSTEM"] == "p2d")
			{
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/paysystem.php");
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/delivery.php");
			}
			else
			{*/
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/delivery.php");
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/related_props.php");
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/paysystem.php");
			//}

			
?>



			
                        <div class="new_block_with_sms bx-soa-section" id="new_block_with_sms">
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
                                    <div class="mr-5">
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
            <!--	SIDEBAR BLOCK	-->
            <div class="col-lg-4 col-md-5 ">
<?
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary.php");
			if(strlen($arResult["PREPAY_ADIT_FIELDS"]) > 0)
				echo $arResult["PREPAY_ADIT_FIELDS"];
			?>
			

                <div class="basket-checkout-container" data-entity="basket-checkout-aligner">
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


			<?if($_POST["is_ajax_post"] != "Y")
			{
				?>
				
				
				
				
				
					</div>
					<input type="hidden" name="confirmorder" id="confirmorder" value="Y">
					<input type="hidden" name="profile_change" id="profile_change" value="N">
					<input type="hidden" name="is_ajax_post" id="is_ajax_post" value="Y">
					<input type="hidden" name="json" value="Y">
					
					
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


<script>
$(document).ready(function() {
	
	//ПОЛУЧАТЕЛЬ
	$(document).on('click', "#ORDER_PROP_32", function()
	{
		if( $("#ORDER_PROP_32").is(':checked') )
		{
			$(".props_order_other_fields").removeClass("noactive");
			$(".text_props_order_other").removeClass("noactive");
		}
		else
		{
			$(".props_order_other_fields").addClass("noactive");
			$(".text_props_order_other").addClass("noactive");
			$("#ORDER_PROP_28").val("");
			$("#ORDER_PROP_30").val("");
			
			$("#ORDER_PROP_29").val("");
			$("#ORDER_PROP_31").val("");			
		}
	});
	$('body').on('click', "#ORDER_PROP_33", function()
	{
		if( $("#ORDER_PROP_33").is(':checked') )
		{
			$(".props_order_other_fields").removeClass("noactive");
			$(".text_props_order_other").removeClass("noactive");
		}
		else
		{
			$(".props_order_other_fields").addClass("noactive");
			$(".text_props_order_other").addClass("noactive");
			$("#ORDER_PROP_28").val("");
			$("#ORDER_PROP_30").val("");
			
			$("#ORDER_PROP_29").val("");
			$("#ORDER_PROP_31").val("");			
		}
	});
	
	$(document).on('click', ".bx_ordercart_second", function()
	{
		$('.bx_ordercart_second').toggleClass('active');
		$('.summary_items').toggleClass('active');
		$('.summary_items_hidden').toggleClass('active');
		
	});		
	//ПОДЪЕМ
	$(document).on('click', "#ORDER_PROP_48", function()
	{
		if( $("#ORDER_PROP_48").is(':checked') )
		{
			$(".block_ETAJ").removeClass("noactive");
			$(".block_LIFT").removeClass("noactive");
			$(".podiem_calc").removeClass("noactive");
		}
		else
		{
			$(".block_ETAJ").addClass("noactive");
			$(".block_LIFT").addClass("noactive");
			$(".podiem_calc").addClass("noactive");
			$("#ORDER_PROP_50").val("");
			$('#ORDER_PROP_52').prop('checked', false);
		
		}
	});
	$('body').on('click', "#ORDER_PROP_49", function()
	{
		if( $("#ORDER_PROP_49").is(':checked') )
		{
			$(".block_ETAJ").removeClass("noactive");
			$(".block_LIFT").removeClass("noactive");
			$(".podiem_calc").removeClass("noactive");
		}
		else
		{
			$(".block_ETAJ").addClass("noactive");
			$(".block_LIFT").addClass("noactive");
			$(".podiem_calc").addClass("noactive");
			$("#ORDER_PROP_51").val("");
			$('#ORDER_PROP_53').prop('checked', false);			
		}
	});	
	
	$('body').on('click', ".DELIVERY_TYPE", function()
	{
		if( $(".DELIVERY_TYPE_1").is(':checked') )
		{
			$(".addres_field").removeClass("noactiveAddress");
		}
		else
		{
			$(".addres_field").addClass("noactiveAddress");		
		}
	});		
	$('body').on('click', "#ORDER_PROP_54", function()
	{
		if( $("#ORDER_PROP_54").is(':checked') )
		{
			$(".ustanovka_info").removeClass("noactive");
		}
		else
		{
			$(".ustanovka_info").addClass("noactive");		
		}
	});		
	
	$('body').on('click', "#ORDER_PROP_55", function()
	{
		if( $("#ORDER_PROP_55").is(':checked') )
		{
			$(".ustanovka_info").removeClass("noactive");
		}
		else
		{
			$(".ustanovka_info").addClass("noactive");		
		}
	});		
});

  function validateEmail(email) {
    var re = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
    return re.test(String(email).toLowerCase());
  }
</script>
<span class="summary_items_hidden"></span>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js
"></script>

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