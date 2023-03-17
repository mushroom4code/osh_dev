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
        <h5 class="mb-3"><?= GetMessage("AUTH_REGISTER") ?></h5>

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