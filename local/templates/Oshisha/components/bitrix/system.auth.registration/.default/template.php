<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>
<div class="bx-auth mb-5">
    <?php ShowMessage($arParams["~AUTH_RESULT"]);
    if ($arResult["SHOW_EMAIL_SENT_CONFIRMATION"]): ?>
        <p><?= GetMessage("AUTH_EMAIL_SENT") ?></p>
    <?php endif;
    if (!$arResult["SHOW_EMAIL_SENT_CONFIRMATION"] && $arResult["USE_EMAIL_CONFIRMATION"] === "Y"): ?>
        <p><?= GetMessage("AUTH_EMAIL_WILL_BE_SENT") ?></p>
    <?php endif ?>
    <noindex>
        <?php echo bitrix_sessid_post(); ?>
        <input type="hidden" name="AUTH_FORM" value="Y"/>
        <input type="hidden" name="TYPE" value="REGISTRATION"/>
        <h4 class="flex flex-row items-center justify-between mt-5 text-3xl font-medium dark:font-normal text-textLight
            dark:text-textDarkLightGray mb-5">
            <span><?= GetMessage("AUTH_REGISTER") ?></span>
                <a href="<?= $arResult["AUTH_AUTH_URL"] ?>" rel="nofollow" class="flex flex-row">
                    <svg width="32" height="31" class="mr-2" viewBox="0 0 32 31" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M32 15.5C32 12.4344 31.0616 9.43763 29.3035 6.88867C27.5454 4.33971 25.0466 2.35303 22.1229 1.17987C19.1993 0.0067159 15.9823 -0.300236 12.8786 0.297835C9.77486 0.895906 6.92394 2.37214 4.6863 4.53985C2.44866 6.70756 0.924806 9.4694 0.307443 12.4761C-0.309921 15.4828 0.00693254 18.5993 1.21793 21.4316C2.42894 24.2639 4.4797 26.6846 7.11088 28.3878C9.74207 30.0909 12.8355 31 16 31C20.2435 31 24.3131 29.367 27.3137 26.4602C30.3143 23.5533 32 19.6109 32 15.5ZM13.024 21.2195L8.44801 16.5695C8.38029 16.5023 8.32604 16.4234 8.288 16.337C8.22006 16.2641 8.1659 16.1801 8.12801 16.089C8.04336 15.9035 7.99963 15.7029 7.99963 15.5C7.99963 15.2972 8.04336 15.0965 8.12801 14.911C8.20416 14.7207 8.31834 14.5469 8.46401 14.3995L13.264 9.7495C13.5653 9.45763 13.9739 9.29366 14.4 9.29366C14.8261 9.29366 15.2347 9.45763 15.536 9.7495C15.8373 10.0414 16.0066 10.4372 16.0066 10.85C16.0066 11.2628 15.8373 11.6586 15.536 11.9505L13.456 13.95H22.4C22.8243 13.95 23.2313 14.1133 23.5314 14.404C23.8314 14.6947 24 15.0889 24 15.5C24 15.9111 23.8314 16.3053 23.5314 16.596C23.2313 16.8867 22.8243 17.05 22.4 17.05H13.344L15.344 19.0805C15.6368 19.3785 15.7954 19.7771 15.7849 20.1884C15.7744 20.5997 15.5957 20.9901 15.288 21.2738C14.9804 21.5574 14.569 21.711 14.1444 21.7009C13.7198 21.6907 13.3168 21.5175 13.024 21.2195Z"
                              class="fill-light-red dark:fill-white"/>
                    </svg>
                    <span class="font-semibold text-textLight dark:text-white text-lg dark:font-light">Авторизация</span>
                </a>
        </h4>
        <?php $APPLICATION->IncludeComponent(
            "ctweb:sms.authorize",
            "profile",
            array(
                "ALLOW_MULTIPLE_USERS" => "Y",
                "PROFILE_AUTH" => "N",
                "REGISTER" => "Y",
                "USER_PHONE" => $arResult['USER_PHONE_NUMBER']
            )
        );
        $APPLICATION->IncludeComponent("bitrix:main.userconsent.request", "",
            array(
                "ID" => COption::getOptionString("main", "new_user_agreement", ""),
                "IS_CHECKED" => "Y",
                "AUTO_SAVE" => "N",
                "IS_LOADED" => "Y",
                "ORIGINATOR_ID" => $arResult["AGREEMENT_ORIGINATOR_ID"],
                "ORIGIN_ID" => $arResult["AGREEMENT_ORIGIN_ID"],
                "INPUT_NAME" => $arResult["AGREEMENT_INPUT_NAME"],
                "REPLACE" => array(
                    "button_caption" => GetMessage("AUTH_REGISTER"),
                    "fields" => array(
                        rtrim(GetMessage("AUTH_NAME"), ":"),
                        rtrim(GetMessage("AUTH_LAST_NAME"), ":"),
                        rtrim(GetMessage("AUTH_LOGIN_MIN"), ":"),
                        rtrim(GetMessage("AUTH_PASSWORD_REQ"), ":"),
                        rtrim(GetMessage("AUTH_EMAIL"), ":"),
                    )
                ),
            )
        ); ?>
    </noindex>
</div>