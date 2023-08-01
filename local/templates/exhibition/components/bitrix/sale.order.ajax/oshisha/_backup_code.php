						elseif ($arProperties["TYPE"] == "TEXT")
						{
							?>
							<?if( $arProperties["CODE"] == 'DATE'):?>
							<?
						
							
								$dateStartDelivery = date('d.m.Y');
								//echo $dateStartDelivery;
							?>
							
								<select class="select2_date select_period" name="<?=$arProperties["FIELD_NAME"]?>" onchange="submitForm();" autocomplete="off">
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
								<?if($_POST["is_ajax_post"] != "Y"):?>
								<script>$('.select2_date').select2({minimumResultsForSearch: -1});</script>
								<?endif;?>
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