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

$cntBasketItems = CSaleBasket::GetList(
    array(),
    array(
        "FUSER_ID" => $FUser_id,
        "LID" => SITE_ID,
        "ORDER_ID" => "NULL"
    ),
    false, false,
    array('QUANTITY', 'SUM_PRICE')
);

$arBasket = [];
while ($arItems = $cntBasketItems->Fetch()) {
    $arBasket['QUANTITY'] = (int)round($arBasket['QUANTITY']) + (int)round($arItems['QUANTITY']);
    $arBasket['SUM_PRICE'] = (int)round($arBasket['SUM_PRICE']) + (int)round($arItems['SUM_PRICE']);
}


?>
<div class="flex flex-row justify-between items-center">
    <?php if (!$compositeStub && $arParams['SHOW_AUTHOR'] == 'Y'): ?>
        <div class="box_with_basket_login mr-5 w-max">
            <?php if ($USER->IsAuthorized()):
                $name = $USER->GetFirstName();
                $newName = $name;
                if (empty($name)) {
                    $newName = $USER->GetLogin();
                }
                $class_width = '';
                if ($newName == '') {
                    $newName = '';
                    $class_width = 'style="min-width:98px"';
                } ?>
                <a class="link_header flex-col flex items-center" <?= $class_width ?> href="<?= $arParams['PATH_TO_PROFILE'] ?>">
                    <div>
                        <svg width="24" height="24" viewBox="0 0 24 24" class="stroke-black stroke-2 dark:stroke-white"
                             fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.04 23C22.04 19.625 17.5539 16.8889 12.02 16.8889C6.4861 16.8889 2 19.625 2 23M12.02 13.2222C8.56132 13.2222 5.7575 10.4862 5.7575 7.11111C5.7575 3.73604 8.56132 1 12.02 1C15.4787 1 18.2825 3.73604 18.2825 7.11111C18.2825 10.4862 15.4787 13.2222 12.02 13.2222Z"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs mt-1"><?= htmlspecialcharsbx($newName) ?></span>
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
                <a class="link_header link_header_box flex-col flex items-center" href="#">
                    <div>
                        <svg width="24" height="24" viewBox="0 0 24 24" class="stroke-black stroke-2 dark:stroke-white"
                             fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.04 23C22.04 19.625 17.5539 16.8889 12.02 16.8889C6.4861 16.8889 2 19.625 2 23M12.02 13.2222C8.56132 13.2222 5.7575 10.4862 5.7575 7.11111C5.7575 3.73604 8.56132 1 12.02 1C15.4787 1 18.2825 3.73604 18.2825 7.11111C18.2825 10.4862 15.4787 13.2222 12.02 13.2222Z"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs mt-1">Профиль</span>
                </a>

                <?php $APPLICATION->IncludeComponent(
                    "ctweb:sms.authorize",
                    "profile",
                    array(
                        "ALLOW_MULTIPLE_USERS" => "Y",
                        "PROFILE_AUTH" => "N"
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
    <div class="box_with_basket_login mr-5 w-max">
        <a href="/personal/subscribe/" id="personal_subscribe" class="link_lk flex-col flex items-center">
            <svg width="26" height="25" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg"
                 class="stroke-black stroke-2 dark:stroke-white">
                <path d="M13.1765 19.9412L5.05882 24L7.08823 15.8824L1 9.11765L9.79412 8.44118L13.1765 1M13.1765 1L16.5588 8.44118L25.3529 9.11765L19.2647 15.8824L21.2941 24L13.1765 19.9412"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs mt-1">Избранное</span>
        </a>
    </div>
    <div class="box_with_basket_login w-max">
        <a class="link_header" href="<?= $arParams['PATH_TO_BASKET'] ?>">
            <div class="basket_top position-relative flex-col flex items-center">
                <div >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         class="stroke-light-red dark:stroke-white stroke-2"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.6768 7.45443H18.2401C19.6423 7.45443 20.8104 8.52925 20.9269 9.92661L21.8256 20.7109C21.9566 22.2827 20.7162 23.6309 19.1388 23.6309H3.43039C1.85308 23.6309 0.612641 22.2827 0.74363 20.7109L1.64233 9.92661C1.75877 8.52925 2.92689 7.45443 4.32909 7.45443H5.89245M16.6768 7.45443H5.89245M16.6768 7.45443V6.10639C16.6768 4.6763 16.1087 3.30479 15.0974 2.29356C14.0862 1.28234 12.7147 0.714233 11.2846 0.714233C9.85447 0.714233 8.48301 1.28234 7.47177 2.29356C6.46056 3.30479 5.89245 4.6763 5.89245 6.10639V7.45443M16.6768 7.45443V12.8466M5.89245 7.45443V12.8466"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

<!--                    --><?php //if (!empty($arBasket['QUANTITY']) && $arBasket['QUANTITY'] !== 0) { ?>
<!--                    <span class="spanBasketTop p-1 bg-light-red text-xs rounded-full" style="">-->
<!--                        --><?php //= $arBasket['QUANTITY'] ?>
<!--                    </span>-->
<!--                    --><?php //} ?>
                </div>
                <?php if (!empty($arBasket['QUANTITY']) && $arBasket['QUANTITY'] !== 0) { ?>

                    <span class="text-textLight dark:text-textDarkLightGray font-semibold text-xs mt-1">
                            <?= $arBasket['SUM_PRICE'] ?> ₽</span>
                    <?php } else { ?>
                    <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs mt-1">Корзина</span>
                <?php } ?>
            </div>
        </a>
        <?php
        if ($arParams['SHOW_PERSONAL_LINK'] == 'Y'):?>
            <div class="box_with_basket_login">
                <a href="<?= $arParams['PATH_TO_PERSONAL'] ?>" class="link_header">
                </a>
            </div>
        <?php endif ?>
    </div>
</div>