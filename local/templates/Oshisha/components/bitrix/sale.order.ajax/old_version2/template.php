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
	global $SHOWROM, $ADDRESS_NO_ACTIVE;
	$arFilter = array(
	'ID' => $arID, 
	'ACTIVE' => 'Y',
	);
	$SHOWROM = 1;
	$resU = CIBlockElement::GetList(Array(), $arFilter, false, false);
	while($ob = $resU->GetNextElement())
	{	
		$arFields = $ob->GetFields();
		$arProps = $ob->GetProperties();
		if( $arProps["SHOWROOM"]["VALUE"] != 'да')
		{
			$SHOWROM = 0;
		}
		$arResult['GABARITS'][$arFields['ID']] = array(
		'WEIGHT' => $arProps['WEIGHT']['VALUE'],
		);
		
	}

//echo $TYPE;
$APPLICATION->SetAdditionalCSS($templateFolder."/style_cart.css");
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
				else{
					ym(19011055,'reachGoal','button_order_make'); 
				}
				var orderForm = BX('ORDER_FORM');
				BX.showWait();
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
					
					if( $('.summary_items_hidden').hasClass('active') )
					{
						$('.summary_items').toggleClass('active');
					}						
					OnUpdatePodiem();
					$(".phone_req").mask("+7(999)999-99-99");
				}

				BX.closeWait();
			}

			function SetContact(profileId)
			{
				BX("profile_change").value = "Y";
				submitForm();
			}
			function OnUpdatePodiem()
			{
				
			}
			$(".phone_req").mask("+7(999)999-99-99");
			</script>
			<?if($_POST["is_ajax_post"] != "Y")
			{
				?><form action="<?=$APPLICATION->GetCurPage();?>" method="POST" name="ORDER_FORM" id="ORDER_FORM" enctype="multipart/form-data">
				<?=bitrix_sessid_post()?>
				<div id="order_form_content">
				
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
<div class="makeorder_content_wrap">
<div class="makeorder_content_left">
<div class="makeorder_content_left_personal">
<?			global $returnRecipient, $returnRecipientCheck, $flagCheckOther, $globalProps;
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/person_type.php");
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/props.php");
?>
			<div class="props_order_other">
				<div class="wrap_props_order_other">
					<div class="props_order_other_check">
					<?echo $returnRecipientCheck;?>
					</div>
					<div class="props_order_other_fields <?if($flagCheckOther != 1):?>noactive<?endif;?>" >
					<?echo $returnRecipient;?>
					</div>
				</div>
				<div class="text_props_order_other <?if($flagCheckOther != 1):?>noactive<?endif;?>">
				<?$APPLICATION->IncludeFile(SITE_DIR."include/order/text_props_order_other.php", Array(), Array("MODE" => "html",  "NAME" => 'Текст о получателе'));?>
				</div>
			</div>

</div>
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
</div>
<div class="makeorder_content_right">
<?
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary.php");
			if(strlen($arResult["PREPAY_ADIT_FIELDS"]) > 0)
				echo $arResult["PREPAY_ADIT_FIELDS"];
			?>
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