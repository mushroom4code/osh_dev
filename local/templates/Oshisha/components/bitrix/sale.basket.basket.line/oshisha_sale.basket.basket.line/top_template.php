<?php

use Bitrix\Catalog\Product\Basket;
use Bitrix\Main\Context;
use Bitrix\Sale\Fuser;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @global array $arParams
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $cartId
 */
$compositeStub = (isset($arResult['COMPOSITE_STUB']) && $arResult['COMPOSITE_STUB'] == 'Y');

$cntBasketItems = CSaleBasket::GetList(
    array(),
    array(
        "FUSER_ID" => CSaleBasket::GetBasketUserID(),
        "LID" => SITE_ID,
        "ORDER_ID" => "NULL"
    ),
    array()
);
global $USER;
$USER_CHECK = $USER->GetId();
$FUser_id = Fuser::getId($USER_CHECK);
$arUserLike = DataBase_like::getLikeFavoriteAllProduct(array(), $FUser_id);
//print_r($arUserLike);
?>
<div class="box_with_loginBasket">
    <?php if (!$compositeStub && $arParams['SHOW_AUTHOR'] == 'Y'): ?>
        <div class="box_with_basket_login">
            <?php if ($USER->IsAuthorized()):
                $name = trim($USER->GetFirstName());
                $newName = explode(' ', $name);
                if (!$name)
                    $name = trim($USER->GetLogin());
                $class_width = '';
                if (!$name || $newName[0] == '') {
                    $newName[0] = 'Личный кабинет';
                    $class_width = 'style="min-width:98px"';
                }
                ?>

                <a class="link_header" <?= $class_width ?> href="<?= $arParams['PATH_TO_PROFILE'] ?>">
                    <div class="basket_icon_personal"></div>
                    <span><?= htmlspecialcharsbx($newName[0]) ?></span>
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
                    <span>
                     <?= GetMessage('TSB1_LOGIN') ?>
                    </span>
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
            <div class="span_bar icon_header">
            </div>
            <span>Каталог</span>
        </a>
    </div>
    <div class="box_with_basket_login">
        <a href="/personal/subscribe/" id="personal_subscribe" class="link_header link_lk">
            <i class="fa fa-star-o icon_header" aria-hidden="true"></i>
            <span>Избранное</span>
			<?if( $arUserLike['USER']['NUM'] > 0):?>
			<span class="spanLikeTop"><?=$arUserLike['USER']['NUM']?></span> 
			<?endif;?>
        </a>
    </div>
    <div class="box_with_basket_login">
            <a class="link_header" href="<?= $arParams['PATH_TO_BASKET'] ?>">
                <div class="basket_top">
                    <?php if (!empty($cntBasketItems) && $cntBasketItems !== null && $cntBasketItems !== 0) { ?>
                        <span class="spanBasketTop"><?= $cntBasketItems ?></span>
                    <?php } ?>
                    <div class="basket_icon_basket"></div>
                </div>

                <span>
                   <?= GetMessage('TSB1_CART') ?>
               </span>
            </a>
            <?php
        if ($arParams['SHOW_PERSONAL_LINK'] == 'Y'):?>
            <div class="box_with_basket_login">
                <a href="<?= $arParams['PATH_TO_PERSONAL'] ?>" class="link_header">
                    <div class="basket_icon_basket"></div>
                    <span>
                       <?= GetMessage('TSB1_PERSONAL') ?>
                   </span>
                </a>
            </div>
        <?php endif ?>

    </div>

</div>