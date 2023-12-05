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
<div class="flex flex-row justify-between items-center w-full">
    <?php if (!$compositeStub && $arParams['SHOW_AUTHOR'] == 'Y'): ?>
        <div class="box_with_basket_login md:mr-5 mr-0 md:w-max w-1/5">
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
                <a class="link_header flex-col flex items-center" <?= $class_width ?>
                   href="<?= $arParams['PATH_TO_PROFILE'] ?>">
                    <div>
                        <svg width="22" height="22" viewBox="0 0 24 24" class="stroke-lightGrayBg stroke-2 dark:stroke-textDarkLightGray"
                             fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.04 23C22.04 19.625 17.5539 16.8889 12.02 16.8889C6.4861 16.8889 2 19.625 2 23M12.02 13.2222C8.56132 13.2222 5.7575 10.4862 5.7575 7.11111C5.7575 3.73604 8.56132 1 12.02 1C15.4787 1 18.2825 3.73604 18.2825 7.11111C18.2825 10.4862 15.4787 13.2222 12.02 13.2222Z"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs md:mt-1 mt-0"><?= htmlspecialcharsbx($newName) ?></span>
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
                        <svg width="22" height="22" viewBox="0 0 24 24" class="stroke-lightGrayBg stroke-2 dark:stroke-textDarkLightGray"
                             fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.04 23C22.04 19.625 17.5539 16.8889 12.02 16.8889C6.4861 16.8889 2 19.625 2 23M12.02 13.2222C8.56132 13.2222 5.7575 10.4862 5.7575 7.11111C5.7575 3.73604 8.56132 1 12.02 1C15.4787 1 18.2825 3.73604 18.2825 7.11111C18.2825 10.4862 15.4787 13.2222 12.02 13.2222Z"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs md:mt-1 mt-0 md:block hidden">Профиль</span>
                </a>
            <?php endif ?>
        </div>
    <?php endif ?>
    <div class="box_with_basket_login md:mr-5 mr-0 md:w-max w-1/5">
        <a href="/personal/subscribe/" id="personal_subscribe" class="link_lk flex-col flex items-center justify-center">
            <svg width="25" height="24" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg"
                 class="stroke-lightGrayBg dark:stroke-textDarkLightGray">
                <path d="M13.1765 19.9412L5.05882 24L7.08823 15.8824L1 9.11765L9.79412 8.44118L13.1765 1M13.1765 1L16.5588 8.44118L25.3529 9.11765L19.2647 15.8824L21.2941 24L13.1765 19.9412"
                      stroke-linecap="round" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
            <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs mt-1 md:block hidden">Избранное</span>
        </a>
    </div>
    <div class="box_with_basket_login md:hidden block mr-0 md:w-max w-1/5">
        <a href="/catalog/" class="link_header link_header_catalog flex justify-center MenuHeader" data-open="false">
            <div class="span_bar icon_header">
                <svg width="28" height="18" viewBox="0 0 48 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="stroke-light-red"
                          d="M2.68994 34.4987H21.2093M2.68994 2.75122H45.0199H2.68994ZM2.68994 18.625H45.0199H2.68994Z"
                          stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </a>
    </div>
    <div class="md:hidden block mr-0 md:w-max w-1/5">
        <a href="/diskont/" class="flex justify-center">
            <svg width="25" height="25" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.75 6.25L6.25 23.75" class="stroke-lightGrayBg dark:stroke-textDarkLightGray"
                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.75 12.5C10.8211 12.5 12.5 10.8211 12.5 8.75C12.5 6.67894 10.8211 5 8.75 5C6.67894 5 5 6.67894 5 8.75C5 10.8211 6.67894 12.5 8.75 12.5Z"
                      class="stroke-lightGrayBg dark:stroke-textDarkLightGray" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path d="M21.25 25C23.3211 25 25 23.3211 25 21.25C25 19.1789 23.3211 17.5 21.25 17.5C19.1789 17.5 17.5 19.1789 17.5 21.25C17.5 23.3211 19.1789 25 21.25 25Z"
                      class="stroke-lightGrayBg dark:stroke-textDarkLightGray" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>

        </a>
    </div>
    <div class="box_with_basket_login md:w-max w-1/5">
        <a class="link_header" href="<?= $arParams['PATH_TO_BASKET'] ?>">
            <div class="basket_top relative flex-col flex items-center">
                <div>
                    <svg width="22" height="23" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg"
                         class="stroke-light-red dark:stroke-white">
                        <path d="M12.3402 6.06905H13.4033C14.3568 6.06905 15.1511 6.79993 15.2303 7.75013L15.8414 15.0835C15.9305 16.1523 15.087 17.0691 14.0144 17.0691H3.33267C2.26009 17.0691 1.41659 16.1523 1.50567 15.0835L2.11678 7.75013C2.19596 6.79993 2.99028 6.06905 3.94378 6.06905H5.00686M12.3402 6.06905H5.00686M12.3402 6.06905V5.15238C12.3402 4.17992 11.9539 3.24729 11.2662 2.55966C10.5786 1.87203 9.64602 1.48572 8.67353 1.48572C7.70104 1.48572 6.76844 1.87203 6.0808 2.55966C5.39318 3.24729 5.00686 4.17992 5.00686 5.15238V6.06905M12.3402 6.06905V9.73572M5.00686 6.06905V9.73572"
                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php if (!empty($arBasket['QUANTITY']) && $arBasket['QUANTITY'] !== 0) { ?>
                        <span class="spanBasketTop absolute text-white top-0 right-0 rounded-full px-1 py-0.5
                            bg-light-red text-10 font-medium" style="">
                            <?= $arBasket['QUANTITY'] ?>
                        </span>
                    <?php } ?>
                </div>
                <?php if (!empty($arBasket['QUANTITY']) && $arBasket['QUANTITY'] !== 0) { ?>
                    <span class="text-textLight dark:text-textDarkLightGray font-medium text-xs mt-1 price_basket_top">
                            <?= $arBasket['SUM_PRICE'] ?> ₽</span>
                <?php } else { ?>
                    <span class="text-textLight dark:text-textDarkLightGray font-normal text-xs mt-1 md:block hidden">Корзина</span>
                <?php } ?>
            </div>
        </a>
        <?php if ($arParams['SHOW_PERSONAL_LINK'] == 'Y'): ?>
            <div class="box_with_basket_login">
                <a href="<?= $arParams['PATH_TO_PERSONAL'] ?>" class="link_header">
                </a>
            </div>
        <?php endif ?>
    </div>
</div>