<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CMain $APPLICATION */
/** @var $arResult array */
?>
<div id="basket-empty" class="bx-sbb-empty-cart-container <?= $arResult['EMPTY_BASKET'] ? '' : 'd-none'; ?>">
    <div class="mb-lg-5 mb-md-5 mb-2"><h4 class="font-m-21"><b>В вашей корзине нет товаров</b></h4></div>
    <div class="bx-sbb-empty-cart-image">
        <div class="banners_box">
            <div class="banners_container">
                <div class="d-flex flex-column mt-3">
                    <h2 class="mb-lg-5 mb-md-5 mb-2 font-m-14 font-w-m-600">Начните покупки прямой сейчас!</h2>
                    <p class="mb-5"><b class="font-16 font-w-m-300">Можете воспользоваться поиском, рекомендуемыми<br>
                            товарами или вернуться в каталог</b>
                    </p>
                    <div class="box-image-empty-basket-mobile"></div>
                    <a href="/catalog/" class="bx-advertisingbanner-btn btn font-w-m-400">
                        Вернуться к покупкам</a>
                </div>

                <div class="banner_small_basket">
                    <?php
                    $APPLICATION->IncludeComponent(
                        "bitrix:advertising.banner",
                        "oshisha_banners",
                        array(
                            "BS_ARROW_NAV" => "N",
                            "BS_BULLET_NAV" => "N",
                            "BS_CYCLING" => "N",
                            "BS_EFFECT" => "fade",
                            "BS_HIDE_FOR_PHONES" => "Y",
                            "BS_HIDE_FOR_TABLETS" => "N",
                            "BS_KEYBOARD" => "Y",
                            "BS_WRAP" => "Y",
                            "CACHE_TIME" => "0",
                            "CACHE_TYPE" => "A",
                            "COMPONENT_TEMPLATE" => "oshisha_banners",
                            "DEFAULT_TEMPLATE" => "bootstrap_v4",
                            "NOINDEX" => "N",
                            "QUANTITY" => "1",
                            "TYPE" => "BANNER_BASKET_SMALL"
                        )
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

