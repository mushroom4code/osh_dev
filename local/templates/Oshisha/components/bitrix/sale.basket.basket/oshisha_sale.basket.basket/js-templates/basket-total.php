<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 */
?>
<script id="basket-total-template" type="text/html">
    <div class="basket-checkout-container p-8 rounded-xl bg-textDark dark:bg-darkBox mb-7"
         data-entity="basket-checkout-aligner">
        <div class="basket-checkout-section">
            <div class="basket-checkout-section-inner">
                <div class="border-b border-borderColor dark:border-gray-slider-arrow mb-5">
                    <div class="basket-checkout-block mb-5 flex justify-between items-center flex-row"
                         data-entity="basket-items-list-header">
                    <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray font-normal dark:font-light"
                          data-filter="all"
                          data-entity="basket-items-count">Товары   ( <span data-count="{{{BASKET_ITEMS_COUNT}}}">
                            {{{BASKET_ITEMS_COUNT}}} </span>) </span>
                        <div class="basket-coupon-block-total-price" data-entity="basket-total-price">
                       <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray font-normal dark:font-light">
                        {{{PRICE_FORMATED}}}
                        </span>
                        </div>
                    </div>
                    <?php if (USE_CUSTOM_SALE_PRICE) { ?>
                        <div class="basket-checkout-block mb-5 flex justify-between items-center flex-row">
                            <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray font-normal dark:font-light">Скидка</span>
                            <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray font-normal dark:font-light">500₽</span>
                        </div>
                    <?php } ?>
                    <div class="basket-checkout-block pb-4 flex justify-between items-center flex-row">
                        {{#WEIGHT_FORMATED}}
                        <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray font-normal dark:font-light">
                        <?= Loc::getMessage('SBB_WEIGHT') ?></span>
                        <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray font-normal dark:font-light">
                        {{{WEIGHT_FORMATED}}}</span>
                        {{/WEIGHT_FORMATED}}
                    </div>
                </div>
                <div class="basket-checkout-block mb-5 flex justify-between flex-row items-center">
                    <div class="flex flex-col">
                        <span class="text_filter_basket mb-1 text-base text-textLight dark:text-white font-semibold dark:font-medium">
                            Общая стоимость</span>
                        <span class="text_filter_basket link_bonus" style="display:none;"><a
                                    href="#">Бесплатная</a> доставка по г. Москва.</span>
                    </div>
                    <div>
                        <span class="text_filter_basket text-base text-textLight dark:text-white font-semibold dark:font-medium"
                              data-entity="basket-total-price"> {{{PRICE_FORMATED}}}</span>
                    </div>
                </div>
                <div class="basket-checkout-block basket-checkout-block-btn">
                    <?php if ($USER->IsAuthorized()) {
                        $canOrder = empty($arResult['ITEMS']['nAnCanBuy']); ?>
                        <button class="btn_basket basket-btn-checkout shadow-md text-white w-full font-normal dark:font-light text-base
                        dark:bg-dark-red bg-light-red py-3 px-4 rounded-5
                        {{#DISABLE_CHECKOUT}} opacity-50 {{/DISABLE_CHECKOUT}}"
                                {{#DISABLE_CHECKOUT}} disabled {{/DISABLE_CHECKOUT}}
                        data-entity="basket-checkout-button">
                        <?= Loc::getMessage('SBB_ORDER') ?>
                        </button>
                        {{#DISABLE_CHECKOUT}}
                        <div id="basket_bnt_checkout_errors"
                             class="text-center mt-4 text-xs text-hover-red font-medium">
                            Удалите или замените отсутствующие товары корзины.
                        </div>
                        {{/DISABLE_CHECKOUT}}
                    <?php } else { ?>
                        <div class="mt-3 text-xs text-hover-red text-center font-normal dark:font-medium">
                            Для оформления заказа необходимо авторизоваться
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="basket-checkout-container p-8 rounded-xl bg-textDark dark:bg-darkBox mb-7"
         data-entity="basket-checkout-aligner">
        <div class="basket-checkout-section-inner">
            <?php if ($arParams['HIDE_COUPON'] !== 'Y') { ?>
                <div class="basket-coupon-section">
                    <div class="basket-coupon-block-field">
                        <div class="basket-coupon-block-field-description mb-4">
                            <span class="text_filter_basket text-base text-textLight dark:text-textDarkLightGray
                            font-semibold dark:font-light"> Введите промокод или сертификат</span>
                        </div>
                        <div class="form">
                            <div class="form-group" style="position: relative;">
                                <input type="text" class="dark:bg-grayButton max-w-2xl w-full bg-white border-none
                                outline-none dark:text-grayIconLights py-3 px-4 rounded-7 text-textLight text-base mb-4
                                input_code" id="" placeholder="Введите код"
                                       data-entity="basket-coupon-input">
                                <div class="basket-checkout-block">
                                    <button class="btn_basket shadow-md text-white w-full font-normal dark:font-light
                                    text-base dark:bg-dark-red bg-light-red py-3 px-4 rounded-5 basket-coupon-block-coupon-btn">
                                        Применить
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="basket-coupon-alert-section">
                            <div class="basket-coupon-alert-inner">
                                {{#COUPON_LIST}}
                                <div class="basket-coupon-alert flex flex-row justify-between mt-4 text-{{CLASS}}">
                                    <span class="basket-coupon-text text-sm text-textLight dark:text-textDarkLightGray font-normal dark:font-light">
                                        <span class="{{^ACTIVE}} text-hover-red {{/ACTIVE}} dark:font-medium font-semibold
                                        {{#ACTIVE}} text-greenButton {{/ACTIVE}}">{{COUPON}}</span> -
                                        <span class="text-xs"> <?= Loc::getMessage('SBB_COUPON') ?> {{JS_CHECK_CODE}}
                                        {{#DISCOUNT_NAME}}({{DISCOUNT_NAME}}){{/DISCOUNT_NAME}}</span>
                                    </span>
                                    <span class="close-link cursor-pointer text-sm text-textLight dark:text-textDarkLightGray
                                          ml-3 dark:font-medium font-semibold"
                                          data-entity="basket-coupon-delete" data-coupon="{{COUPON}}">
							            <?= Loc::getMessage('SBB_DELETE') ?>
						            </span>
                                </div>
                                {{/COUPON_LIST}}
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <a class="link_basket_after flex flex-row items-center justify-center" href="/catalog/">
        <svg width="30" height="30" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" class="mr-2">
            <path d="M21.5859 11.205C21.5859 9.06999 20.9529 6.98289 19.767 5.20766C18.581 3.43243 16.8954 2.04881 14.9232 1.23176C12.9511 0.414711 10.781 0.200935 8.68736 0.617462C6.59373 1.03399 4.67061 2.06211 3.16119 3.57182C1.65177 5.08153 0.623837 7.00501 0.207389 9.09904C-0.20906 11.1931 0.00467642 13.3636 0.82157 15.3361C1.63846 17.3086 3.02182 18.9946 4.79672 20.1807C6.57161 21.3669 8.65832 22 10.793 22C13.6554 22 16.4007 20.8627 18.4247 18.8383C20.4488 16.8138 21.5859 14.068 21.5859 11.205ZM8.78547 15.1884L5.69869 11.9499C5.65301 11.9031 5.61641 11.8482 5.59076 11.788C5.54492 11.7372 5.50839 11.6787 5.48283 11.6152C5.42573 11.486 5.39623 11.3463 5.39623 11.205C5.39623 11.0638 5.42573 10.924 5.48283 10.7948C5.53419 10.6623 5.61122 10.5413 5.70948 10.4386L8.94737 7.20009C9.1506 6.99682 9.42625 6.88262 9.71367 6.88262C10.0011 6.88262 10.2767 6.99682 10.48 7.20009C10.6832 7.40337 10.7974 7.67907 10.7974 7.96654C10.7974 8.25401 10.6832 8.52971 10.48 8.73298L9.07688 10.1255H15.1101C15.3964 10.1255 15.6709 10.2393 15.8733 10.4417C16.0757 10.6442 16.1894 10.9187 16.1894 11.205C16.1894 11.4913 16.0757 11.7659 15.8733 11.9684C15.6709 12.1708 15.3964 12.2845 15.1101 12.2845H9.00133L10.3505 13.6987C10.548 13.9063 10.6549 14.1838 10.6479 14.4703C10.6408 14.7567 10.5202 15.0286 10.3127 15.2262C10.1051 15.4237 9.82766 15.5307 9.54125 15.5236C9.25484 15.5166 8.98298 15.396 8.78547 15.1884Z"
                  class="fill-lightGrayBg dark:fill-white"/>
        </svg>
        <span class="font-medium dark:font-light text-lg dark:text-textDarkLightGray text-lightGrayBg">Продолжить покупки</span>
    </a>
</script>
