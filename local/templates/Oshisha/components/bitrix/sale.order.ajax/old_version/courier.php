<?

?>

					<label class="<?if( $_POST["DELIVERY_RESULT"] != 1):?>hide_delivery<?endif;?> hided_delivery delivery bx-soa-pp-company <?if($_POST['DELIVERY_ID'] == $baseCourier || in_array($_POST['DELIVERY_ID'], $arCourierID) ):?>bx-selected<?endif;?>" for="ID_DELIVERY_COURIER">
						<div class="bx-soa-pp-company-graf-container  box_with_delivery mb-3">
							<input
								type="radio"
								class="bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0 DELIVERY_CHECK <?if(in_array($_POST['DELIVERY_ID'], $arCourierID)):?>checked<?endif;?>"
								id="ID_DELIVERY_COURIER"
								name="DELIVERY_ID"
								value="<?=$baseCourier?>"
								<?if($_POST['DELIVERY_ID'] == $baseCourier):?> checked<?endif;?>
								/>
								<div class="bx-soa-pp-company-curs"></div>
							<div class="bx-soa-pp-company-smalltitle color_black font_weight_600" >



								<div class="bx_description">

									<span>
										Доставка курьером
									</span>

									<div class="min_price">
									<?foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
									{
										if(!in_array($delivery_id, $arCourierID)) continue;
										$PRICE =  str_replace(array('руб', '₽'), '', $arDelivery["PRICE_FORMATED"]);
										if( $l == 0 )
											$PRICE_RES = intval($PRICE);
										else
											$PRICE = min(intval($PRICE),intval($PRICE_RES));
										$l++;
									}
									
										if( $PRICE > 0)
											echo 'от '.$PRICE.'₽';
										else
											echo 'Есть бесплатная доставка';
									?>
									</div>

									<p>
										Сегодня - если заказ сделан до 15:00; Ночная или на след. день - если заказ сделан до 20:00. В воскресенье доставка не работает.	
									</p>
								</div>

							</div>

						</div>
					</label>	
				<div class="block_courier_wrap" <?if($_POST['DELIVERY_ID'] == $baseCourier || in_array($_POST['DELIVERY_ID'], $arCourierID)):?>style="display:block;"<?endif;?>>
				<div class="block_courier"  >
						<div class="block_courier_left">
					
					
						
						
						<?=$fieldsProp['COURIER']?>
						</div>
						<div class="block_courier_right">
						
						</div>
						
						<div class="delivery_courier_block">
							<?foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery):?>
							<?if(!in_array($delivery_id, $arCourierID)) continue;

								$arDelivery["PRICE_FORMATED"] = str_replace('руб', '₽', $arDelivery["PRICE_FORMATED"]);?>							
							<label class="delivery_courier_item" <?if( $SELECTED_DAY == ''):?>style="display:none;"<?endif;?> for="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>" data-id="<?= $arDelivery["ID"] ?>">
							<input type="radio"
								id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>"
								name="DELIVERY_ID"
								class="bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0"
								value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y" && $_POST["is_ajax_post"] == 'Y') echo " checked";?>
								
								/><span><?=$arDelivery['NAME']?></span>
								<?if( $_POST["is_ajax_post"] == 'Y' && ($_POST['DISTANCE'] || $_POST['TYPE_DISTANCE']) ):?>
								<span class="price_delivery price_delivery-<?= $arDelivery["ID"] ?>"><?=$arDelivery["PRICE_FORMATED"]?></span>
								<?endif;?>
							</label>
							
							<?endforeach;?>
						</div>
						
						<div class="courier_alert"></div>
				
				</div>	
			</div>	 	