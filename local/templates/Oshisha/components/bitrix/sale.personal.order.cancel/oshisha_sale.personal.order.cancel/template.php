<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Enterego\EnteregoHelper;

/** @var $USER  CAllUser */

$itemsBasket = EnteregoHelper::getItems($arResult["ID"], 'CSaleBasket');
$arItem = EnteregoHelper::getItems($USER->getId(), 'Order');
$price['MIN_QUANTITY'] = '1';
?>
<!--<h4 class="mb-4"><b>Отмена или возврат заказа</b></h4>-->
<!--<div class="row">-->
<!--    <div class="bx-order-cancel col-5 d-flex flex-column">-->
<!--        --><?php //if ($arResult["ERROR_MESSAGE"] == ''): ?>
<!--            <form method="post" action="--><?php //= POST_FORM_ACTION_URI ?><!--">-->
<!--                <input type="hidden" name="CANCEL" value="Y">-->
<!--                --><?php //= bitrix_sessid_post() ?>
<!--                <input type="hidden" name="ID" value="--><?php //= $arResult["ID"] ?><!--">-->
<!---->
<!--                <div class="form-group mb-3">-->
<!--                    <label for="orderCancel" class="orderCancel"><b>Заказ</b></label>-->
<!--                    <select class="select_orders" id="select_orders">-->
<!--                        <option value="--><?php //= $arResult["ID"] ?><!--" id="--><?php //= $arResult["ID"] ?><!--">-->
<!--                            № --><?php //= $arResult["ID"] ?><!--</option>-->
<!--                        --><?php //foreach ($arItem['ORDERS_ID'] as $key => $itemId) {
//                            if ($itemId !== $arResult["ID"]) {
//                                ?>
<!--                                <option value="--><?php //= $itemId ?><!--" id="--><?php //= $itemId ?><!--">№ --><?php //= $itemId ?><!--</option>-->
<!--                            --><?php //}
//                        } ?>
<!--                    </select>-->
<!--                </div>-->
<!--                <div class="form-group mb-3">-->
<!--                    <label for="orderCanceled" class="orderCancel"><b>Причина возврата</b></label>-->
<!--                    <select class="select_orders" id="select_comments">-->
<!--                        <option value="1" id=""></option>-->
<!--                        <option value="2" id=""></option>-->
<!--                        <option value="3" id=""></option>-->
<!--                    </select>-->
<!--                </div>-->
<!--                <div class="form-group mb-3">-->
<!--                    <label class="orderCancel mb-3"><b>Был ли товар в пользовании?</b></label>-->
<!--                    <div class="form-check d-flex flex-row">-->
<!--                        <div class="mr-5">-->
<!--                            <input type="radio" class="form-check-input  mr-1 check_custom" value="yes" id="yes"-->
<!--                                   name="checked" checked>-->
<!--                            <label class="orderCancel" for="yes">Да</label>-->
<!--                        </div>-->
<!--                        <div class="mr-5">-->
<!--                            <input type="radio" id="no" class="form-check-input  mr-1 check_custom" value="no"-->
<!--                                   name="checked">-->
<!--                            <label class="orderCancel" for="no">Нет</label>-->
<!--                        </div>-->
<!--                    </div>-->
<!---->
<!--                </div>-->
<!--                <div class="form-group mb-3">-->
<!--                    <label for="orderCancel" class="orderCancel"><b>Комментарий</b></label>-->
<!--                    <textarea name="REASON_CANCELED" class="form-control order_cancel_comment" id="orderCancel"-->
<!--                              rows="3"></textarea>-->
<!--                </div>-->
<!--                <div class="example-1 mb-5">-->
<!--                    <div class="form-group">-->
<!--                        <label class="label">-->
<!--                            <i class="fa fa-paperclip" aria-hidden="true"></i>-->
<!--                            <span class="title">Прикрепите фото</span>-->
<!--                            <input type="file">-->
<!--                        </label>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <input type="submit" name="action" class="btn btn_cancel"-->
<!--                       value="Отправить на рассмотрение">-->
<!--            </form>-->
<!--        --><?php //else: ?>
<!--            --><?php //= ShowError($arResult["ERROR_MESSAGE"]); ?>
<!--        --><?php //endif; ?>
<!--    </div>-->
<!--    <div class="col-7 box_with_products">-->
<!--        <div class="box_with_warning d-flex flex-row mb-2 align-items-center"><span class="span_circle mr-3"></span>-->
<!--            <span>Табачная продукция возврату не подлежит</span>-->
<!--        </div>-->
<!--        <div class="box_with_products_variation">-->
<!--            <div class="d-flex flex-column">-->
<!--                <div class="form-check d-flex flex-row justify-content-center">-->
<!--                    <div class="mr-5">-->
<!--                        <input type="radio" class="form-check-input  mr-1 check_custom check_input_edit" value="all" id="allProducts"-->
<!--                               name="checked" checked >-->
<!--                        <label class="text_preview" for="allProducts">Полностью</label>-->
<!--                    </div>-->
<!--                    <div class="mr-5">-->
<!--                        <input type="radio" id="small" class="form-check-input  mr-1 check_custom check_input_edit" value="small"-->
<!--                               name="checked">-->
<!--                        <label class="text_preview" for="small">Частично</label>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="box_product d-flex flex-column">-->
<!--                    --><?php //foreach ($itemsBasket['ITEM'] as $arItem) {
//                        $newPrice = explode('.',$arItem['PRICE']);
//                        ?>
<!--                        <div class="border_prod d-flex flex-row bx_catalog_items">-->
<!--                            <div class="d-flex flex-row align-items-center mr-3">-->
<!--                                <div class="form-group form-check mr-3 box_check_product" >-->
<!--                                    <input type="checkbox" value="Y" name="" id="--><?php //= $arItem['PRODUCT_ID']?><!--"-->
<!--                                           class="check_input form-check-input input_check_product">-->
<!--                                </div>-->
<!--                                <a class="d-flex flex-row" href="--><?php //= $arItem['DETAIL_PAGE_URL'] ?><!--">-->
<!--                                    <div class="basket-item-block-image">-->
<!--                                        <img class="basket-item-image"-->
<!--                                             src="--><?php //EnteregoHelper::getItems($arItem['PRODUCT_ID'], 'file'); ?><!--"/>-->
<!--                                    </div>-->
<!--                                </a>-->
<!--                            </div>-->
<!--                            <div class="d-flex flex-column justify-content-between align-content-between width_box">-->
<!--                                <span class="text_preview">--><?php //= $arItem['NAME'] ?><!--</span>-->
<!--                                <div class="d-flex flex-row justify-content-between">-->
<!--                                    <div class="product-item-amount-field-contain d-flex flex-row align-items-center">-->
<!--                                                        <span class="btn-minus  minus_icon no-select"-->
<!--                                                              id="--><?php //= $arItemIDs['QUANTITY_DOWN_ID'] ?><!--"><span-->
<!--                                                                    class="minus_icon"></span></span>-->
<!--                                        <div class="product-item-amount-field-block">-->
<!--                                            <input class="product-item-amount card_element"-->
<!--                                                   id="--><?php //= $arItemIDs['QUANTITY_ID'] ?><!--" type="number"-->
<!--                                                   value="--><?php //= $arItem['QUANTITY'] ?><!--">-->
<!--                                        </div>-->
<!--                                        <a class="btn-plus plus_icon no-select add2basket"-->
<!--                                           id="--><?php //= $arItemIDs['BUY_LINK']; ?><!--"-->
<!--                                           href="javascript:void(0)" data-url="--><?php //= $arItem['DETAIL_PAGE_URL'] ?><!--"-->
<!--                                           data-product_id="--><?php //= $arItem['PRODUCT_ID']; ?><!--"-->
<!--                                           title="Добавить в корзину"></a>-->
<!--                                    </div>-->
<!--                                    <span class="text_price">--><?php //= $newPrice[0] ?><!--₽</span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div id="result_box" ></div>-->
<!--                        </div>-->
<!--                    --><?php //} ?>
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
