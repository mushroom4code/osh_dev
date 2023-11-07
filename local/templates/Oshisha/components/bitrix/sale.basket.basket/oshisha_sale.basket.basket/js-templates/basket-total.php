<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 */
?>
<script id="basket-total-template" type="text/html">
    <div class="basket-checkout-container" data-entity="basket-checkout-aligner">
        <div class="basket-checkout-section">
            <div class="basket-checkout-section-inner">
                <div class="basket-checkout-block mb-4 d-flex justify-content-between  align-items-center flex-row"
                     data-entity="basket-items-list-header">
                    <span class="text_filter_basket" data-filter="all"
                          data-entity="basket-items-count">Товары   ( <span data-count="{{{BASKET_ITEMS_COUNT}}}">
                            {{{BASKET_ITEMS_COUNT}}} </span>) </span>
                    <div class="basket-coupon-block-total-price" data-entity="basket-total-price">
                       <span class="text_filter_basket">
                        {{{PRICE_FORMATED}}}
                        </span>
                    </div>
                </div>
                <?php if (USE_CUSTOM_SALE_PRICE) { ?>
                    <div class="basket-checkout-block mb-4  d-flex justify-content-between  align-items-center flex-row">
                        <span class="text_filter_basket">Скидка</span>
                        <span class="text_filter_basket">500₽</span>
                    </div>
                <?php } ?>
                <div class="basket-checkout-block pb-3 mb-4 d-flex justify-content-between  align-items-center flex-row
                 border_color">
                    {{#WEIGHT_FORMATED}}
                    <span class="text_filter_basket">  <?= Loc::getMessage('SBB_WEIGHT') ?></span>
                    <span class="text_filter_basket"> {{{WEIGHT_FORMATED}}}</span>
                    {{/WEIGHT_FORMATED}}
                </div>
                <!--               Бонусная система -->
                <!--                <div>-->
                <!--                    <div class="basket-checkout-block mb-4 d-flex justify-content-between flex-row align-items-center">-->
                <!--                        <span class="text_filter_basket"><b>У вас 500 баллов</b></span>-->
                <!--                        <span class="text_filter_basket link_bonus">(Вам начислится <a href="#">230</a> баллов) </span>-->
                <!--                    </div>-->
                <!--                    <div class="basket-checkout-block mb-4 d-flex pb-3  align-items-center justify-content-between-->
                <!--                 flex-row border_color">-->
                <!--                        <input type="text" class="input-form-control input_basket" placeholder="Введите кол-во баллов"/>-->
                <!--                        <button class="btn_basket_filter" type="button">Списать</button>-->
                <!--                    </div>-->
                <!--                </div>-->
                <div class="basket-checkout-block mb-4 d-flex justify-content-between flex-row align-items-center">
                    <div class="d-flex flex-column">
                        <span class="text_filter_basket mb-1"><b>Общая стоимость</b></span>
                        <span class="text_filter_basket link_bonus" style="display:none;"><a
                                    href="#">Бесплатная</a> доставка по г. Москва.</span>
                    </div>
                    <div>
                        <span class="text_filter_basket"
                              data-entity="basket-total-price"><b> {{{PRICE_FORMATED}}}</b></span>
                    </div>
                </div>
                <div class="basket-checkout-block mb-4 basket-checkout-block-btn text-left">
                    <?php
                    if ($USER->IsAuthorized()) {
                        $canOrder = empty($arResult['ITEMS']['nAnCanBuy']);
                        ?>
                        <button class="btn_basket  basket-btn-checkout"
                                data-entity="basket-checkout-button">
                            <?= Loc::getMessage('SBB_ORDER') ?>
                        </button>
                        {{#DISABLE_CHECKOUT}}
                            <div id="basket_bnt_checkout_errors" class="text-center  mt-3 font-13 text-danger">
                                Удалите или замените отсутствующие товары корзины.
                            </div>
                        {{/DISABLE_CHECKOUT}}
                        <?php
                    }  else {
                        ?>
                            <span class="btn-primary-color">Для оформления заказа необходимо авторизоваться</span>
                        <?php
                    }?>
                </div>
            </div>
        </div>
    </div>
    <div class="basket-checkout-container" data-entity="basket-checkout-aligner">
        <div class="basket-checkout-section-inner">
            <?
            if ($arParams['HIDE_COUPON'] !== 'Y') {
                ?>
                <div class="basket-coupon-section">
                    <div class="basket-coupon-block-field">
                        <div class="basket-coupon-block-field-description mb-4">
                            <span class="text_filter_basket">  <b>  Введите промокод или сертификат</b></span>
                        </div>
                        <div class="form">
                            <div class="form-group" style="position: relative;">
                                <input type="text" class="form-control mb-4 input_code" id="" placeholder="Введите код"
                                       data-entity="basket-coupon-input">
                                <div class="basket-checkout-block  mb-4">
                                    <button class="btn_basket  basket-coupon-block-coupon-btn">
                                        Применить
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="basket-coupon-alert-section">
                            <div class="basket-coupon-alert-inner">
                                {{#COUPON_LIST}}
                                <div class="basket-coupon-alert text-{{CLASS}}">
						<span class="basket-coupon-text">
							<strong>{{COUPON}}</strong> - <?= Loc::getMessage('SBB_COUPON') ?> {{JS_CHECK_CODE}}
							{{#DISCOUNT_NAME}}({{DISCOUNT_NAME}}){{/DISCOUNT_NAME}}
						</span>
                                    <span class="close-link" data-entity="basket-coupon-delete"
                                          data-coupon="{{COUPON}}">
							<?= Loc::getMessage('SBB_DELETE') ?>
						</span>
                                </div>
                                {{/COUPON_LIST}}
                            </div>
                        </div>

                    </div>
                </div>
                <?
            }
            ?></div>

    </div>
    <a class="link_basket_after" href="/catalog/">Вернуться к покупкам</a>
</script>
