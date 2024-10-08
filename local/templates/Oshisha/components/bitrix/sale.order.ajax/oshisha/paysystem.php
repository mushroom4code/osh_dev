<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

	<script type="text/javascript">
		function changePaySystem(param) {
			if (BX("account_only") && BX("account_only").value == 'Y') // PAY_CURRENT_ACCOUNT checkbox should act as radio
			{
				if (param == 'account') {
					if (BX("PAY_CURRENT_ACCOUNT")) {
						BX("PAY_CURRENT_ACCOUNT").checked = true;
						BX("PAY_CURRENT_ACCOUNT").setAttribute("checked", "checked");
						BX.addClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');

						// deselect all other
						var el = document.getElementsByName("PAY_SYSTEM_ID");
						for (var i = 0; i < el.length; i++)
							el[i].checked = false;
					}
				} else {
					BX("PAY_CURRENT_ACCOUNT").checked = false;
					BX("PAY_CURRENT_ACCOUNT").removeAttribute("checked");
					BX.removeClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');
				}
			} else if (BX("account_only") && BX("account_only").value == 'N') {
				if (param == 'account') {
					if (BX("PAY_CURRENT_ACCOUNT")) {
						BX("PAY_CURRENT_ACCOUNT").checked = !BX("PAY_CURRENT_ACCOUNT").checked;

						if (BX("PAY_CURRENT_ACCOUNT").checked) {
							BX("PAY_CURRENT_ACCOUNT").setAttribute("checked", "checked");
							BX.addClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');
						} else {
							BX("PAY_CURRENT_ACCOUNT").removeAttribute("checked");
							BX.removeClass(BX("PAY_CURRENT_ACCOUNT_LABEL"), 'selected');
						}
					}
				}
			}

			submitForm();
		}
	</script>
	<div id="bx-soa-paysystem" class="bx-soa-section mb-4 bx-active bx-selected">
		<div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
			<div class="bx-soa-section-title" data-entity="section-title">Как Вы хотите оплатить заказ? </div>
		</div>
		<div class="bx-soa-section-content">
			<div class="bx-soa-pp row">
				<div class="x-soa-item-container bx-wrap-payment">
					<div class="row">
		
						
			<?
			if ($arResult["PAY_FROM_ACCOUNT"] == "Y") {
				$accountOnly = ($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y") ? "Y" : "N";
			?>
				<input type="hidden" id="account_only" value="<?= $accountOnly ?>" />
				<div class="bx-soa-pp-company col-6">
					<div class="bx-soa-pp-company-graf-container pay_system height_100">
						<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="N">
						<label class="bx-soa-pp-company-title" for="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT_LABEL" onclick="changePaySystem('account');" class="<? if ($arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"] == "Y") echo "selected" ?>">
							<input type="checkbox" name="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT" value="Y" <? if ($arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"] == "Y") echo " checked=\"checked\""; ?>>
							<div class="bx_logotype">
								<span style="background-image:url(<?= $templateFolder ?>/images/logo-default-ps.gif);"></span>
							</div>
							<div class="bx_description">
								<?= GetMessage("SOA_TEMPL_PAY_ACCOUNT") ?>
								<p>
								<div><?= GetMessage("SOA_TEMPL_PAY_ACCOUNT1") . " <b>" . $arResult["CURRENT_BUDGET_FORMATED"] ?></b></div>
								<? if ($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y") : ?>
									<div><?= GetMessage("SOA_TEMPL_PAY_ACCOUNT3") ?></div>
								<? else : ?>
									<div><?= GetMessage("SOA_TEMPL_PAY_ACCOUNT2") ?></div>
								<? endif; ?>
								</p>
							</div>
						</label>
						<div class="clear"></div>
					</div>
				</div>
				<?
			}

			uasort($arResult["PAY_SYSTEM"], "cmpBySort"); // resort arrays according to SORT value

			foreach ($arResult["PAY_SYSTEM"] as $arPaySystem) {
				//BBRAIN FIX оплата доставки



				if (strlen(trim(str_replace("<br />", "", $arPaySystem["DESCRIPTION"]))) > 0 || intval($arPaySystem["PRICE"]) > 0) {
					if (count($arResult["PAY_SYSTEM"]) == 1) {
				?>
						<div class="bx-soa-pp-company col-6 <?if( $arPaySystem["CHECKED"] == 'Y'):?>bx-selected<?endif;?>">
							<div class="bx-soa-pp-company-graf-container pay_system height_100">
								<input type="hidden" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>">
								<input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>" <? if ($arPaySystem["CHECKED"] == "Y" && !($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"] == "Y")) echo " checked=\"checked\""; ?> onclick="changePaySystem();" />
								<label  class="bx-soa-pp-company-title" for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" onclick="BX('ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>').checked=true;changePaySystem();">
									<?
									if (count($arPaySystem["PSA_LOGOTIP"]) > 0) :
										$imgUrl = $arPaySystem["PSA_LOGOTIP"]["SRC"];
									else :
										$imgUrl = $templateFolder . "/images/logo-default-ps.gif";
									endif;
									?>

									<div class="bx-soa-pp-company-title">
										<? if ($arParams["SHOW_PAYMENT_SERVICES_NAMES"] != "N") : ?>
											<?= $arPaySystem["PSA_NAME"]; ?>
										<? endif; ?>
									</div>	
										<p class="bx-soa-pp-company-smalltitle">
											<?
											if (intval($arPaySystem["PRICE"]) > 0)
												echo str_replace("#PAYSYSTEM_PRICE#", SaleFormatCurrency(roundEx($arPaySystem["PRICE"], SALE_VALUE_PRECISION), $arResult["BASE_LANG_CURRENCY"]), GetMessage("SOA_TEMPL_PAYSYSTEM_PRICE"));
											else
												echo $arPaySystem["DESCRIPTION"];
											?>
										</p>
									
								</label>
							
							</div>
						</div>
					<?
					} else // more than one
					{
					?>
						<div class="bx-soa-pp-company col-6 <?if( $arPaySystem["CHECKED"] == 'Y'):?>bx-selected<?endif;?>">
							<div class="bx-soa-pp-company-graf-container pay_system height_100">
								<input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>" <? if ($arPaySystem["CHECKED"] == "Y" && !($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"] == "Y")) echo " checked=\"checked\""; ?> onclick="changePaySystem();" />
								<label  class="bx-soa-pp-company-title" for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" onclick="BX('ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>').checked=true;changePaySystem();">
									<?
									if (count($arPaySystem["PSA_LOGOTIP"]) > 0) :
										$imgUrl = $arPaySystem["PSA_LOGOTIP"]["SRC"];
									else :
										$imgUrl = $templateFolder . "/images/logo-default-ps.gif";
									endif;
									?>

									<div class="bx-soa-pp-company-title">
										<? if ($arParams["SHOW_PAYMENT_SERVICES_NAMES"] != "N") : ?>
											<?= $arPaySystem["PSA_NAME"]; ?>
										<? endif; ?>
									</div>	
										<p class="bx-soa-pp-company-smalltitle">
											<?
											if (intval($arPaySystem["PRICE"]) > 0)
												echo str_replace("#PAYSYSTEM_PRICE#", SaleFormatCurrency(roundEx($arPaySystem["PRICE"], SALE_VALUE_PRECISION), $arResult["BASE_LANG_CURRENCY"]), GetMessage("SOA_TEMPL_PAYSYSTEM_PRICE"));
											else
												echo $arPaySystem["DESCRIPTION"];
											?>
										</p>
									
								</label>
							
							</div>
						</div>
					<?
					}
				}

				if (strlen(trim(str_replace("<br />", "", $arPaySystem["DESCRIPTION"]))) == 0 && intval($arPaySystem["PRICE"]) == 0) {
					if (count($arResult["PAY_SYSTEM"]) == 1) {
					?>
						<div class="bx-soa-pp-company col-6 <?if( $arPaySystem["CHECKED"] == 'Y'):?>bx-selected<?endif;?>">
							<div class="bx-soa-pp-company-graf-container pay_system height_100">
								<input type="hidden" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>">
								<input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>" <? if ($arPaySystem["CHECKED"] == "Y" && !($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"] == "Y")) echo " checked=\"checked\""; ?> onclick="changePaySystem();" />
								<label class="bx-soa-pp-company-title" for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" onclick="BX('ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>').checked=true;changePaySystem();">

									<? if ($arParams["SHOW_PAYMENT_SERVICES_NAMES"] != "N") : ?>
										<div class="bx-soa-pp-company-title">
										
											<?= $arPaySystem["PSA_NAME"]; ?>
										</div>
									<? endif; ?>
							</div>
						</div>
					<?
					} else // more than one
					{
					?>
						<div class="bx-soa-pp-company col-6 <?if( $arPaySystem["CHECKED"] == 'Y'):?>bx-selected<?endif;?>">
							<div class="bx-soa-pp-company-graf-container pay_system height_100">

								<input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>" <? if ($arPaySystem["CHECKED"] == "Y" && !($arParams["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y" && $arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"] == "Y")) echo " checked=\"checked\""; ?> onclick="changePaySystem();" />

								<label class="bx-soa-pp-company-title" for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" onclick="BX('ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>').checked=true;changePaySystem();">

									<? if ($arParams["SHOW_PAYMENT_SERVICES_NAMES"] != "N") : ?>
										<div class="bx-soa-pp-company-title">
										

											<? if ($arParams["SHOW_PAYMENT_SERVICES_NAMES"] != "N") : ?>
												<?= $arPaySystem["PSA_NAME"]; ?>
											<? else : ?>
												<?= "&nbsp;" ?>
											<? endif; ?>

										</div>
									<? endif; ?>

								</label>
							</div>
						</div>
			<?
					}
				}
			}
			?>
				</div>
			</div>
		</div>
		</div>
	</div>
