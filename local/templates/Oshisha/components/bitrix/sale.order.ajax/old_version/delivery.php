<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $fieldsProp;

global $SELECTED_DAY; //задается в props.php филд DATE
/*echo '<pre>';
print_r($arResult["DELIVERY"]);*/
?>


<div class="bx-soa-section mb-4 bx-active bx-step-warning bx-selected" id="bx-soa-delivery">
<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?=$arResult["BUYER_STORE"]?>" />

	<?
	//print_r($arResult["DELIVERY"]);
	if(!empty($arResult["DELIVERY"]))
	{
		
		foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
		{
			if ($delivery_id !== 0 && intval($delivery_id) <= 0)
			{
				foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
				{
					if( $arProfile["CHECKED"] == 'Y' && $_POST["is_ajax_post"] == 'Y')
						$DELIVERY_ID = $profile_id;
				
				}
			}
			else
			{
			
				if ($arDelivery["CHECKED"]=="Y" && $_POST["is_ajax_post"] == 'Y')
				{
					if( $_POST['DELIVERY_ID'] == $baseCourier )
					{
						if(in_array($delivery_id, $arCourierID) && $SELECTED_DAY != '')
							$DELIVERY_ID = $delivery_id;
					}
					else
					{
						$DELIVERY_ID = $delivery_id;
					}
				}
						

			
			}
		}
		
	
		?>
		<input type="hidden" name="DELIVERY_ID2" class="DELIVERY_ID DELIVERY_ID_CHECKS" value="<?=$DELIVERY_ID?>">				
		<div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
			<div class="bx-soa-section-title" data-entity="section-title">Как Вы хотите получить заказ?</div>
		</div>
		<div class="box_with_delivery_type">
			<div class="bx-soa-section-content bx-wrap-delivery">
					
			
			
		<?

		foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
		{
			if(in_array($delivery_id, $arCourierID) || $baseCourier == $delivery_id ) continue;

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
								name="DELIVERY_ID"
								value="<?=$delivery_id.":".$profile_id;?>"
								<?=$arProfile["CHECKED"] == "Y"  ? "checked=\"checked\"" : "";?>
							
								/>
								<div class="bx-soa-pp-company-curs"></div>
							<label class="bx-soa-pp-company-smalltitle color_black font_weight_600" for="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>">


								<div class="bx_description">

									<span >
										<?=htmlspecialcharsbx($arDelivery["TITLE"])." (".htmlspecialcharsbx($arProfile["TITLE"]).")";?>
									</span>



									<p >
										<?if (strlen($arProfile["DESCRIPTION"]) > 0):?>
											<?=nl2br($arProfile["DESCRIPTION"])?>
										<?else:?>
											<?=nl2br($arDelivery["DESCRIPTION"])?>
										<?endif;?>
										
									</p>
								</div>

							</label>
				<?if( in_array($profile_id, $arAutoDelivery) &&  $arDelivery["ID"] == $profile_id ): //Автоматизированные?>
				<div class="block_auto_description">
					<?echo $arDelivery["DESCRIPTION"]."";?>
					<div class="btn-punkts" id="btn-punkts"></div>
				</div>
				<?endif;?>

						</div>
					</div>
					<?
				} // endforeach
			}
			else // stores and courier
			{  
				if(  $_POST["DELIVERY_RESULT"] != 1 && $arTypeDelivery['PICKUP'] != $arDelivery["ID"] )
					$hide_delivery = 'hide_delivery';
				if(   $arTypeDelivery['PICKUP'] != $arDelivery["ID"] )
					$hided_delivery = 'hided_delivery';
				?>
					<label  class="<?=$hide_delivery?>  <?=$hided_delivery?> delivery bx-soa-pp-company bx-soa-delivery-<?=$arDelivery["ID"]?> <?if( $arDelivery["ID"] == $DELIVERY_ID):?>bx-selected<?endif;?>" <?=$clickHandler?>  for="ID_DELIVERY_ID_<?=$arDelivery["ID"]?>" >

						<div class="bx-soa-pp-company-graf-container  box_with_delivery mb-3">

							<input type="radio"
								id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>"
								name="DELIVERY_ID"
								class="bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0"
								value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["ID"] == $DELIVERY_ID) echo " checked";?>
							
								/>
								<div class="bx-soa-pp-company-curs"></div>
							<div class="bx-soa-pp-company-smalltitle color_black font_weight_600">



								<div class="bx_description">
									<div class="bx-soa-pp-company-smalltitle color_black font_weight_600"><?= htmlspecialcharsbx($arDelivery["NAME"])?></div>
									<?if( $arTypeDelivery['PICKUP'] != $arDelivery["ID"]):?>
										<?if( $arDelivery["ID"] == 52 || $arDelivery["ID"] == 51):?>
										Расчитывается менджером. Оплачивается при получении.
										<?elseif( $arDelivery["ID"] == 43):?>
										<?else:?>
										<div class="min_price"><?=str_replace(array('руб', '₽'), '', $arDelivery["PRICE_FORMATED"]);?></div>
										<?endif;?>
									<?endif;?>
									
									
									<p>
										<? 
										if( $SETTINGS['ORDER_PICKUP_TITLE'] && $arTypeDelivery['PICKUP'] == $arDelivery["ID"] )
											echo $SETTINGS['ORDER_PICKUP_TITLE']; 
										elseif (strlen($arDelivery["DESCRIPTION"])>0 && !in_array($arDelivery["ID"], $arAutoDelivery))
											echo $arDelivery["DESCRIPTION"]."";


									?>
									</p>
								</div>

							</div>
				<?
				if( $arTypeDelivery['PICKUP'] == $arDelivery["ID"] ):?>
					
				<div class="type_pickup">
					<div class="type_pickup_btn active t_men" data-type="men">Пешком</div>
					<div class="type_pickup_btn t_auto"  data-type="auto">На машине</div>
					<input type=hidden name="TYPE_PICKUP" class="TYPE_PICKUP" value="<?=$_POST['TYPE_PICKUP']?>">
				</div>				
				<?endif;?>
					
					</div>
				</label>
				<?if( in_array($arDelivery["ID"], $arAutoDelivery) &&  $arDelivery["ID"] == $DELIVERY_ID ): //Автоматизированные?>
				<div class="block_auto_description">
					<?echo $arDelivery["DESCRIPTION"]."";?>
					<?if( $arDelivery["ID"] == 52): //СДЭК самовывоз?>
					<div class="btn-punkts" id="btn-punkts"></div>
					<?elseif($arDelivery["ID"] == 55):?>
					
					<div class="btn-punkts" id="btn-punkts-5post"></div>
					<?endif;?>
				</div>
				<?endif;?>				
				
				<?
				
				//Если выбран самовывоз
				if( $arTypeDelivery['PICKUP'] == $arDelivery["ID"] &&  $arDelivery["ID"] == $DELIVERY_ID ):?>

				<div class="block_pickup_address">
					<div class="block_pickup_address_left">
						<div class="block_pickup_name"><img src="/local/assets/images/icon_location.svg" class="icon_location"> 
						<?=$SETTINGS['ORDER_PICKUP_ADDRESS']?></div>
						<div class="block_pickup_metro pickup-station-img ">м. ВДНХ</div>
						
						<div class="block_pickup_info pickup-info-img"> 
						<?=$SETTINGS['ORDER_PICKUP_TEXT']?>
					

</div>
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
				
				<?endif;?>
				
				
				<?
				//Подключаем блок с курьером
				if( $arTypeDelivery['PICKUP'] == $arDelivery["ID"] )
				{
					echo '<div class="address_block_max">
					<h6>Получить заказ курьером или в пунктах самовывоза CDEK, Pickpoint, Почта РФ:</h6>
					';
					PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_Y"], $arParams["TEMPLATE_LOCATION"], $arResult, array(26,12));
					
					echo $fieldsProp['ADDRESS'];
					echo '<div class="delivery-info">Укажите ваш адрес, чтобы мы подобрали вам варианты доставки</div>';
					echo '<span style="display:none;">Выбрать адрес</span>';
					echo '
					<div class="block_addresses" style="display:none;">
						<div class="address_item" data-address="г Челябинск, ул Кировоградская, д 5, кв 10">
						Челябинская обл, Челябинск, Кировоградская, д 5, кв 10 
						</div>
					</div>
					';
					echo '</div>';
					
					if( intval($_POST['DISTANCE']) <= 200 )
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/courier.php");
				}
					
				?>
				
<?
			}
		}
		?>
			</div>
		</div>
		<?
	}
?>

</div>