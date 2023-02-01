<?php

use Bitrix\Catalog\Product\Basket;
use Bitrix\Main\Context;
use Bitrix\Sale\Fuser;
use Bitrix\Conversion\Internals\MobileDetect;

$mobile = new MobileDetect();

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @global array $arParams
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $cartId
 */
$compositeStub = (isset($arResult['COMPOSITE_STUB']) && $arResult['COMPOSITE_STUB'] == 'Y');

$USER_CHECK = $USER->GetId();
$FUser_id = Fuser::getId($USER_CHECK);
$arUserLike = DataBase_like::getLikeFavoriteAllProduct(array(), $FUser_id);

$cntBasketItems = CSaleBasket::GetList(
    array(),
    array(
        "FUSER_ID" => $FUser_id,
        "LID" => SITE_ID,
        "ORDER_ID" => "NULL"
    ),
    false, false,
    array('QUANTITY', 'ORDER_PRICE', 'SUM_PRICE')
);

$arBasket = [];
while ($arItems = $cntBasketItems->Fetch()) {
    $arBasket['QUANTITY'] = (int)round($arBasket['QUANTITY']) + (int)round($arItems['QUANTITY']);
    $arBasket['SUM_PRICE'] = (int)round($arBasket['SUM_PRICE']) + (int)round($arItems['SUM_PRICE']);
}


?>
<div class="box_with_loginBasket">
    <?php if (!$compositeStub && $arParams['SHOW_AUTHOR'] == 'Y'): ?>
        <div class="box_with_basket_login">
            <?php if ($USER->IsAuthorized()):
                $name = trim($USER->GetFirstName());
                $newName = explode(' ', $name);
                if (!$name)
                    $name = trim($USER->GetLogin());

                ?>

                <a class="link_header" href="<?= $arParams['PATH_TO_PROFILE'] ?>">
                    <div class="basket_icon_personal"></div>
                </a>

            <?php else:
            $arParamsToDelete = array(
                "login",
                "login_form",
                "logout",
                "register",
                "forgot_password",
                "change_password",
                "confirm_registration",
                "confirm_code",
                "confirm_user_id",
                "logout_butt",
                "auth_service_id",
                "clear_cache",
                "backurl",
            );

            $currentUrl = urlencode($APPLICATION->GetCurPageParam("", $arParamsToDelete));
            if ($arParams['AJAX'] == 'N') {
            ?>
                <script type="text/javascript"><?=$cartId?>.currentUrl = '<?=$currentUrl?>';</script><?php
            } else {
                $currentUrl = '#CURRENT_URL#';
            }

            $pathToAuthorize = $arParams['PATH_TO_AUTHORIZE'];
            $pathToAuthorize .= (mb_stripos($pathToAuthorize, '?') === false ? '?' : '&');
            $pathToAuthorize .= 'login=yes&backurl=' . $currentUrl;
            ?>
                <a class="link_header link_header_box" href="#">
                    <div class="basket_icon_personal"></div>
                </a>

                <?php $APPLICATION->IncludeComponent(
                    "ctweb:sms.authorize",
                    "top",
                    array(
                        "ALLOW_MULTIPLE_USERS" => "Y"
                    )
                ); ?>

            <?php endif ?>
        </div>
    <?php endif ?>
    <div class="box_with_basket_login mobile">
        <a href="/catalog/" class="link_header link_header_catalog">
            <div class="span_bar icon_header"></div>
        </a>
    </div>
    <div class="box_with_basket_login">
        <a href="/personal/subscribe/" id="personal_subscribe" class="link_header link_lk">
            <i class="fa fa-star-o icon_header" aria-hidden="true"></i>
            <? if ($arUserLike['USER']['NUM'] > 0): ?>
                <span class="spanLikeTop"><?= $arUserLike['USER']['NUM'] ?></span>
            <? endif; ?>
        </a>
    </div>
    <div class="box_with_basket_login">
        <a class="link_header" href="<?= $arParams['PATH_TO_BASKET'] ?>">
            <div class="basket_top d-flex flex-row align-items-end position-relative">
                <div class="basket_icon_basket mr-2"></div>
                <?php if (!empty($arBasket['QUANTITY']) && $arBasket['QUANTITY'] !== 0) { ?>
                    <span class="spanBasketTop"><?= $arBasket['QUANTITY'] ?></span>
                    <?php if (!$mobile->isMobile()) { ?>
                        <span class="font-12 font-weight-bold price_basket_top"><?= $arBasket['SUM_PRICE'] ?> ₽</span>
                    <?php }
                } ?>
            </div>
        </a>
        <?php
        if ($arParams['SHOW_PERSONAL_LINK'] == 'Y'):?>
            <div class="box_with_basket_login">
                <a href="<?= $arParams['PATH_TO_PERSONAL'] ?>" class="link_header">
                    <div class="basket_icon_basket"></div>
                </a>
            </div>
        <?php endif ?>

    </div>

</div>