<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Page\Asset;
use Bitrix\Sale\Fuser;
use DataBase_like;
use Enterego\EnteregoHelper;

if ($arParams['GUEST_MODE'] !== 'Y') {
	Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/script.js");
	Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/style.css");
}
CJSCore::Init(array('clipboard', 'fx'));
/** @var CUser|CAllUser $USER
 */

$orderIsNotActiveItemsPresent = false;
foreach ($arResult["BASKET"] as $orderBasketItem) {
	$product = CIBlockElement::GetByID($orderBasketItem['PRODUCT_ID'])->GetNext();
	if ($product['ACTIVE'] == 'N') {
		$orderIsNotActiveItemsPresent = true;
		break;
	}
}


$APPLICATION->SetTitle("");

if (!empty($arResult['ERRORS']['FATAL'])) {
	$component = $this->__component;
	foreach ($arResult['ERRORS']['FATAL'] as $code => $error) {
		if ($code !== $component::E_NOT_AUTHORIZED) {
			ShowError($error);
		}
	}


	if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED])) {
		$userName = $arResult["USER_NAME"];
		$paymentData[$payment['ACCOUNT_NUMBER']] = array(
			"payment" => $payment['ACCOUNT_NUMBER'],
			"order" => $arResult['ACCOUNT_NUMBER'],
			"allow_inner" => $arParams['ALLOW_INNER'],
			"only_inner_full" => $arParams['ONLY_INNER_FULL'],
			"refresh_prices" => $arParams['REFRESH_PRICES'],
			"path_to_payment" => $arParams['PATH_TO_PAYMENT']
		);
		$paymentSubTitle = Loc::getMessage('SPOD_TPL_BILL') . " " . Loc::getMessage('SPOD_NUM_SIGN') . $payment['ACCOUNT_NUMBER'];
		if (isset($payment['DATE_BILL'])) {
			$paymentSubTitle .= " " . Loc::getMessage('SPOD_FROM') . " " . $payment['DATE_BILL_FORMATED'];
		}
		$paymentSubTitle .= ",";
		?>
		<div class="row">
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<div class="alert alert-danger"><?= $arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED] ?></div>
			</div>
			<? $authListGetParams = array(); ?>
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<? $APPLICATION->AuthForm('', false, false, 'N', false); ?>
			</div>
		</div>
		<?
	}
} else {
	if (!empty($arResult['ERRORS']['NONFATAL'])) {
		foreach ($arResult['ERRORS']['NONFATAL'] as $error) {
			ShowError($error);
		}
	}
	?>
	<div class="row sale-order-detail ">
		<div class="col">

			<? if ($arParams['GUEST_MODE'] !== 'Y') {
				?>
				<div class="mb-3">
					<a class="link_home_orders" href="<?= htmlspecialcharsbx($arResult["URL_TO_LIST"]) ?>">К списку
						заказов</a>
				</div>
				<?
			}
			?>

			<div class="row mb-3 mx-0">
				<div class="col sale-order-detail-card">
					<div class="title_order_detail mb-4">
						<h4 class="mb-3"><b> Заказ № <?= $arResult["ACCOUNT_NUMBER"] ?></b></h4>
						<p class="sale-order-detail-props mb-3"><b>от <?= $arResult["DATE_INSERT_FORMATED"] ?></b></p>
						<div class="d-flex flex-row custom_item justify-content-between">
							<div class="d-flex flex-column custom_item pr-1">
                                <span class="mb-1"> <b
		                                class="mr-1">Товаров:</b>  <?= count($arResult['BASKET']); ?> </span>
								<span class="mb-1"> <b
										class="mr-1">Статус:</b>  <?= $arResult["STATUS"]["NAME"] ?> </span>
								<span class="mb-1"> <b
										class="mr-1">Сумма заказа:</b>  <?= $arResult["PRICE_FORMATED"] ?> </span>
								<span class="mb-1"> <b
										class="mr-1">Сумма доставки:</b>  <?php
									$deliveryPrice = 0;
									foreach ($arResult['SHIPMENT'] as $shipment) {
										$deliveryPrice += $shipment["PRICE_DELIVERY"];
									}
									echo htmlspecialcharsbx(CurrencyFormat($deliveryPrice, $arResult['CURRENCY'])); ?>
                                </span>
								<span class="mb-1" style="display:none;"> <b class="mr-1">Начислено балов:</b>
                                    <a href="#" class="link_repeat_orders">240</a>
                                </span>
							</div>
							<div class="d-flex flex-column custom_item">
                                <span class="mb-1"> <b
		                                class="mr-1">Способ доставки:</b>  <?php foreach ($arResult['SHIPMENT'] as $shipment) {
		                                echo htmlspecialcharsbx($shipment["DELIVERY_NAME"]);
	                                } ?> </span>

								<span class="mb-1"> <b class="mr-1">Способ оплаты: </b> <?
									foreach ($arResult['PAYMENT'] as $payment) {
										echo $payment['PAY_SYSTEM_NAME'];
									} ?> </span>
								<span class="mb-1"> <b class="mr-1">Получатель: </b>
                                        <?php
                                        if ($userName <> '') {
	                                        echo htmlspecialcharsbx($userName);
                                        } elseif (mb_strlen($arResult['FIO'])) {
	                                        echo htmlspecialcharsbx($arResult['FIO']);
                                        } else {
	                                        echo htmlspecialcharsbx($arResult["USER"]['LOGIN']);
                                        }
                                        ?>
                                </span>
								<span class="mb-1"> <b class="mr-1">Номер отслеживания:</b>
                                    <a href="#" class="link_repeat_orders"> <?= $arResult["TRACKING_NUMBER"] ?></a>
                                </span>
							</div>
							<div class="d-flex flex-column custom_item">
								<a href="<?= $arResult["URL_TO_COPY"] ?>"
								   class="link_repeat_orders sale-order-list-repeat-link mb-1 <?= empty($arResult['BASKET_ITEMS']) ? 'js--basket-empty' : 'js--basket-not-empty' ?> <?= $orderIsNotActiveItemsPresent === true ? 'js--not-active' : '' ?>">
									<?= Loc::getMessage('SPOD_ORDER_REPEAT') ?></a>
								<div id="popup_mess_order_copy"></div>
								<? if ($arResult["CAN_CANCEL"] === "Y") {
//                                    TODO отмена заказа
									?>
									<!--                                    <a href="--><?php //= $arResult["URL_TO_CANCEL"] ?><!--"-->
									<!--                                       class="link_repeat_orders sale-order-list-repeat-link mb-1">Отменить заказ</a>-->
									<?
								}
								?>
								<a style="display:none;" href="/personal/support/"
								   class="link_repeat_orders mb-1">Претензии к заказу</a>

							</div>
						</div>
					</div>

					<div class="row mt-5 mb-3">
						<div class="col p-0">
							<div class="row mx-0">
								<div class="col">
									<?php
									$id_USER = $USER->GetID();
									$FUser_id = Fuser::getId($id_USER);
									$item_id = [];

									foreach ($arResult['BASKET'] as $basketItem) {
										$item_id[] = $basketItem['ID'];
									}

									$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);

									foreach ($arResult['BASKET'] as $basketItem) {

                                        $db_props = CIBlockElement::GetProperty(IBLOCK_CATALOG, $basketItem['PRODUCT_ID'], array("sort" => "asc"), array("CODE" => PROPERTY_ACTIVE_UNIT));
                                        if ($ar_props = $db_props->Fetch()) {
                                            $activeUnitId = IntVal($ar_props["VALUE"]);
                                        } else {
                                            $activeUnitId = '';
                                        }

                                        if (!empty($activeUnitId)) {
                                            $basketItem[PROPERTY_ACTIVE_UNIT] = CCatalogMeasure::GetList(array(), array("CODE" => $activeUnitId))->fetch();
                                            if (!empty($basketItem[PROPERTY_ACTIVE_UNIT])) {

                                                $basketItem[PROPERTY_ACTIVE_UNIT] = $basketItem[PROPERTY_ACTIVE_UNIT]['SYMBOL_RUS'];
                                            } else {
                                                $basketItem[PROPERTY_ACTIVE_UNIT] = 'шт';
                                            }
                                        } else {
                                            $basketItem[PROPERTY_ACTIVE_UNIT] = 'шт';
                                        }

                                        $basketItem['MEASURE_RATIO'] = \Bitrix\Catalog\MeasureRatioTable::getList(array(
                                            'select' => array('RATIO'),
                                            'filter' => array('=PRODUCT_ID' => $basketItem['PRODUCT_ID'])
                                        ))->fetch()['RATIO'];

                                        $basketItem['QUANTITY_WITH_RATIO'] = $basketItem['QUANTITY'] / $basketItem['MEASURE_RATIO'];

                                        foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
											$basketItem['COUNT_LIKES'] = $count;
										}

										foreach ($count_likes['USER'] as $keyLike => $count) {
											if ($keyLike == $basketItem['ID']) {
												$basketItem['COUNT_LIKE'] = $count['Like'][0];
												$basketItem['COUNT_FAV'] = $count['Fav'][0];
											}
										}
										$areaId = $basketItem['AREA_ID'];

										$itemIds = array(
											'ID' => $areaId,
											'PICT' => $areaId . '_pict',
											'SECOND_PICT' => $areaId . '_secondpict',
											'PICT_SLIDER' => $areaId . '_pict_slider',
											'STICKER_ID' => $areaId . '_sticker',
											'SECOND_STICKER_ID' => $areaId . '_secondsticker',
											'QUANTITY' => $areaId . '_quantity',
											'QUANTITY_DOWN' => $areaId . '_quant_down',
											'QUANTITY_UP' => $areaId . '_quant_up',
											'QUANTITY_MEASURE' => $areaId . '_quant_measure',
											'QUANTITY_LIMIT' => $areaId . '_quant_limit',
											'BUY_LINK' => $areaId . '_buy_link',
											'BASKET_ACTIONS' => $areaId . '_basket_actions',
											'NOT_AVAILABLE_MESS' => $areaId . '_not_avail',
											'SUBSCRIBE_LINK' => $areaId . '_subscribe',
											'COMPARE_LINK' => $areaId . '_compare_link',
											'PRICE' => $areaId . '_price',
											'PRICE_OLD' => $areaId . '_price_old',
											'PRICE_TOTAL' => $areaId . '_price_total',
											'DSC_PERC' => $areaId . '_dsc_perc',
											'SECOND_DSC_PERC' => $areaId . '_second_dsc_perc',
											'PROP_DIV' => $areaId . '_sku_tree',
											'PROP' => $areaId . '_prop_',
											'DISPLAY_PROP_DIV' => $areaId . '_sku_prop',
											'BASKET_PROP_DIV' => $areaId . '_basket_prop',
										);
                                        $url = $basketItem['DETAIL_PAGE_URL'];
                                        if (!empty($basketItem['PARENT'])) {
                                            $url = '/catalog/product/' . CIBlockElement::GetByID($basketItem['PARENT']['ID'])->Fetch()['CODE'] . '/';
                                        } ?>
										<div class="d-flex flex-row justify-content-between mb-5">
											<div class="d-flex flex-row ">
												<div class="sale-order-detail-order-item-img-block mr-4 ">
													<a href="<?= $url ?>">
														<?php
														if ($basketItem['PICTURE']['SRC'] <> '') {
															$imageSrc = $basketItem['PICTURE']['SRC'];
														} else {
															$imageSrc = '/local/templates/Oshisha/images/no-photo.gif';
														}
														?>
														<img class="sale-order-detail-order-item-img-container"
														     src="<?= $imageSrc ?>"/>
													</a>
												</div>
												<div class="sale-order-detail-order-item-properties d-flex flex-column
                                               align-items-start justify-content-between mb-2"
												     style="min-width: 250px;">
													<div class="mb-2">
														<a class="sale-order-detail-order-item-title mb-3"
														   href="<?= $url ?>"><?= htmlspecialcharsbx($basketItem['NAME']) ?></a>
														<? if (isset($basketItem['PROP']) && is_array($basketItem['PROP'])) {
															foreach ($basketItem['PROP'] as $itemProps) { ?>
																<div
																	class="sale-order-detail-order-item-properties-type">
																	<?= htmlspecialcharsbx($itemProps) ?></div>
																<?
															}
														} ?>
													</div>
													<?php $res = EnteregoHelper::getItems($basketItem['PRODUCT_ID'],
														PROPERTY_KEY_VKUS);
													if (!empty($res)) {
														?>
														<div class="variation_taste mb-5"
														     id="<?= count($res[PROPERTY_KEY_VKUS]); ?>">
															<?php foreach ($res[PROPERTY_KEY_VKUS] as $key) { ?>
																<span class="taste"
																      data-background="#<?= $key['VALUE'] ?>"
																      id="<?= $key['ID'] ?>">
                                                                <?= $key['NAME'] ?>
                                                            </span>
																<?php
															} ?>
														</div>
													<?php } ?>
													<div>
														<div class="sale-order-detail-order-item-properties text-right">
															<strong class="bx-price"><?= $basketItem['FORMATED_SUM'] ?>
																x <?= $basketItem['QUANTITY_WITH_RATIO'] ?> <?= $basketItem[PROPERTY_ACTIVE_UNIT] ?>.</strong>
														</div>
													</div>
												</div>
											</div>
											<div class="d-flex flex-row">
												<div class="align-self-end d-flex">
													<div
														class="sale-order-detail-order-item-properties d-flex flex-row">
														<div class="product-item-amount-field-contain d-flex
                                                    flex-row align-items-center">
                                                        <span class="btn-minus  minus_icon no-select"
                                                              id="<?= $itemIds['QUANTITY_DOWN_ID'] ?>"><span
		                                                        class="minus_icon"></span></span>
															<div class="product-item-amount-field-block">
																<input class="product-item-amount card_element"
																       id="<?= $itemIds['QUANTITY_ID'] ?>"
																       type="number"
																       value="<?= $basketItem['QUANTITY'] ?>">
															</div>
															<a class="btn-plus plus_icon no-select add2basket"
															   id="<? echo $itemIds['BUY_LINK']; ?>"
															   href="javascript:void(0)"
															   data-url="<?= $basketItem['DETAIL_PAGE_URL'] ?>"
															   data-product_id="<?= $basketItem['PRODUCT_ID']; ?>"
															   title="Добавить в корзину"></a>
														</div>
														<!--                                                    <div class="product-item-amount-field-contain">-->
														<!--                                                        <span class="btn-minus no-select minus_icon "-->
														<!--                                                              id="-->
														<?//= $itemIds['QUANTITY_DOWN_ID'] ?><!--"></span>-->
														<!--                                                        <div class="product-item-amount-field-block">-->
														<!--                                                            <input class="product-item-amount"-->
														<!--                                                                   id="-->
														<?//= $itemIds['QUANTITY_ID'] ?><!--" type="number"-->
														<!--                                                                   value="-->
														<?//= $price['MIN_QUANTITY'] ?><!--">-->
														<!--                                                        </div>-->
														<!--                                                        <span class="btn-plus no-select plus_icon"-->
														<!--                                                              id="-->
														<?//= $itemIds['QUANTITY_UP_ID'] ?><!--"></span>-->
														<!--                                                    </div>-->
														<a id="<? echo $basketItem['BUY_LINK']; ?>"
														   href="javascript:void(0)"
														   rel="nofollow"
														   class="btn_basket add2basket basket_prod_detail"
														   data-url="<?= $url ?>"
														   data-product_id="<?= $basketItem['ID']; ?>"
														   title="Добавить в корзину">Забронировать</a>
													</div>
												</div>
												<div class="box_with_net ml-3">
													<?php
													/**
													 * @var CatalogSectionComponent $component
													 */
													$APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
														'templates',
														array(
															'ID_PROD' => $basketItem['ID'],
															'F_USER_ID' => $FUser_id,
															'LOOK_LIKE' => true,
															'LOOK_FAVORITE' => true,
															'COUNT_LIKE' => $basketItem['COUNT_LIKE'],
															'COUNT_FAV' => $basketItem['COUNT_FAV'],
															'COUNT_LIKES' => $basketItem['COUNT_LIKES'],
														),
														$component,
														array('HIDE_ICONS' => 'Y')
													);
													?>
												</div>
											</div>
										</div>
										<?
									}
									?>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div><!--sale-order-detail-general-->
		</div>
	</div>
	</div>
	<?
	$javascriptParams = array(
		"url" => CUtil::JSEscape($this->__component->GetPath() . '/ajax.php'),
		"templateFolder" => CUtil::JSEscape($templateFolder),
		"templateName" => $this->__component->GetTemplateName(),
		"paymentList" => $paymentData,
		"returnUrl" => $arResult['RETURN_URL'],
	);
	$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
	?>
	<script>
        BX.Sale.PersonalOrderComponent.PersonalOrderDetail.init(<?=$javascriptParams?>);
	</script>
	<?
}
?>

