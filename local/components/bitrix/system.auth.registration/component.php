<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @var CUser|CAllUser $USER
 */

use B01110011ReCaptcha\BitrixCaptcha;
use Bitrix\Main\UserPhoneAuthTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $USER_FIELD_MANAGER;

if (!is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"] <> '') {
    $arParams["~AUTH_RESULT"] = array("MESSAGE" => $arParams["~AUTH_RESULT"], "TYPE" => "ERROR");
}

$arResult["PHONE_REGISTRATION"] = (COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y");
$arResult["PHONE_REQUIRED"] = ($arResult["PHONE_REGISTRATION"] && COption::GetOptionString("main", "new_user_phone_required", "N") == "Y");
$arResult["EMAIL_REGISTRATION"] = (COption::GetOptionString("main", "new_user_email_auth", "Y") <> "N");
$arResult["EMAIL_REQUIRED"] = ($arResult["EMAIL_REGISTRATION"] && COption::GetOptionString("main", "new_user_email_required", "Y") <> "N");
$arResult["USE_EMAIL_CONFIRMATION"] = (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" && $arResult["EMAIL_REQUIRED"] ? "Y" : "N");
$arResult["PHONE_CODE_RESEND_INTERVAL"] = CUser::PHONE_CODE_RESEND_INTERVAL;

$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
if ($def_group != "") {
    $arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
} else {
    $arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
}

$arParamsToDelete = array(
    "login",
    "logout",
    "register",
    "forgot_password",
    "change_password",
    "confirm_registration",
    "confirm_code",
    "confirm_user_id",
);

if (defined("AUTH_404")) {
    $arResult["AUTH_URL"] = POST_FORM_ACTION_URI;
} else {
    $arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("register=yes", $arParamsToDelete);
}

$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", $arParamsToDelete);

foreach ($arResult as $key => $value) {
    if (!is_array($value)) $arResult[$key] = htmlspecialcharsbx($value);
}

/*** ENTEREGO  доп параметры
 *
 * USER_PERSONAL_BIRTHDAY,
 * __phone_prefix
 */
$arRequestParams = array(
    "USER_NAME",
    "USER_LAST_NAME",
    "USER_LOGIN",
    "USER_PASSWORD",
    "USER_CONFIRM_PASSWORD",
    "USER_PHONE_NUMBER",
    "USER_PERSONAL_BIRTHDAY",
    'recaptcha_token',
    '__phone_prefix'
);

/*** ENTEREGO captcha 3*/
$_REQUEST['USER_LOGIN'] = $_REQUEST['USER_EMAIL'];

foreach ($arRequestParams as $param) {
    $arResult[$param] = $_REQUEST[$param] <> '' ? $_REQUEST[$param] : "";
    $arResult[$param] = htmlspecialcharsbx($arResult[$param]);
}

$arResult["USER_EMAIL"] = htmlspecialcharsbx($_REQUEST["sf_EMAIL"] <> '' ? $_REQUEST["sf_EMAIL"] : $_REQUEST["USER_EMAIL"]);

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
$arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", 0, LANGUAGE_ID);
if (is_array($arUserFields) && count($arUserFields) > 0) {
    foreach ($arUserFields as $FIELD_NAME => $arUserField) {
        if ($arUserField["MANDATORY"] != "Y")
            continue;
        $arUserField["EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
        $arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
        $arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
        $arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
    }
}
if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
    $arResult["USER_PROPERTIES"]["SHOW"] = "Y";
// ******************** /User properties ***************************************************

$arResult["SHOW_SMS_FIELD"] = false;
$arResult["SHOW_EMAIL_SENT_CONFIRMATION"] = false;
$arResult["bVarsFromForm"] = false;

if (is_array($arParams["AUTH_RESULT"])) {
    if (isset($arParams["~AUTH_RESULT"]["SIGNED_DATA"])) {
        //special key "SIGNED_DATA" was added after the SMS was sent in CUser::Register()
//        $arResult["SHOW_SMS_FIELD"] = true;
//        $arResult["SIGNED_DATA"] = $arParams["~AUTH_RESULT"]["SIGNED_DATA"];
    } elseif ($arParams['AUTH_RESULT']["TYPE"] == "ERROR") {
        $arResult["bVarsFromForm"] = true;
    }
    if ($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && $arParams["AUTH_RESULT"]["TYPE"] === "OK") {
        $arResult["SHOW_EMAIL_SENT_CONFIRMATION"] = true;
    }
} elseif ($arParams["AUTH_RESULT"] <> '') {
    $arResult["bVarsFromForm"] = true;
}

/*** ENTEREGO captcha 3*/
if (class_exists('B01110011ReCaptcha\BitrixCaptcha')) {
    $res = BitrixCaptcha::checkSpam();
    if ($res === false) {
        $arResult["CAPTCHA_CODE"] = 'Ошибок нет';
    }
}
/*** ENTEREGO captcha 3*/
$arResult["AGREEMENT_ORIGINATOR_ID"] = "main/reg";
$arResult["AGREEMENT_ORIGIN_ID"] = "register";
$arResult["AGREEMENT_INPUT_NAME"] = "USER_AGREEMENT";

$arResult["SECURE_AUTH"] = false;
if (!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y') {
    $sec = new CRsaSecurity();
    if (($arKeys = $sec->LoadKeys())) {
        $sec->SetKeys($arKeys);
        $sec->AddToForm('bform', array('USER_PASSWORD', 'USER_CONFIRM_PASSWORD'));
        $arResult["SECURE_AUTH"] = true;
    }
}

// verify phone code
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$USER->IsAuthorized() && isset($_REQUEST['Register'])) {
    if (!empty($arResult['USER_PHONE_NUMBER'])) {
        if (true) {
//            if ($arResult["PHONE_REQUIRED"]) {
            //the user was added as inactive, now phone number is confirmed, activate them
//            $user = new CUser();
//            $phone='+' . $arResult['__phone_prefix'] . $arResult['USER_PHONE_NUMBER'];
//            $user->Add([
//                'LOGIN' => $arResult['USER_EMAIL'],
//                'ACTIVE' => 'Y',
//                'EMAIL' => $arResult['USER_EMAIL'],
//                'NAME' => $arResult['USER_NAME'] ?? '',
//                'LAST_NAME' => $arResult['USER_LAST_NAME'] ?? '',
//                'PASSWORD' => $arResult['USER_PASSWORD'],
//                'CONFIRM_PASSWORD' => $arResult['USER_CONFIRM_PASSWORD'],
//                'PERSONAL_BIRTHDAY' => $arResult['USER_PERSONAL_BIRTHDAY'],
//                'PERSONAL_PHONE' => $phone,
//                'PHONE_NUMBER' => $phone,
//            ]);

//            }
        } else {
            $arParams["~AUTH_RESULT"] = array(
                "MESSAGE" => GetMessage("main_register_sms_error"),
                "TYPE" => "ERROR",
            );
            $arResult["SHOW_SMS_FIELD"] = true;
            $arResult["CODE"] = $_REQUEST["CODE"];
            $arResult["SIGNED_DATA"] = $_REQUEST["SIGNED_DATA"];
        }
    }
}

$this->IncludeComponentTemplate();
