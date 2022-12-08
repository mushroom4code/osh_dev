<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Loader::includeModule('ctweb.smsauth');

use Bitrix\Main\Config\Option as Option;

$arResult = array(
    "ERRORS" => array()
);
$arParams = (!empty($arParams)) ? $arParams : array();
$arParams["RELOCATION"] = isset($arParams["RELOCATION"]) ? $arParams["RELOCATION"] : $arParams["~RELOCATION"];

if (!$GLOBALS["USER"]->IsAuthorized()) {
    if (isset($_SESSION["SMS_AUTH"]["EXPIRE"]) && time() >= $_SESSION["SMS_AUTH"]["EXPIRE"]) {
        unset($_POST["PHONE"]);
        unset($_SESSION["SMS_AUTH"]);
        $arResult["ERRORS"][] = "CODE_EXPIRED";
        //LocalRedirect($APPLICATION->GetCurPageParam());
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid()) {
        if (isset($_POST["PHONE"]) && ($_POST["GET_CODE"] || $_POST["LOGIN"])) {
            if (strlen($_POST["PHONE"]) === 0) {
                $arResult["ERRORS"][] = "NO_PHONE_INPUT";
            } else {
                $_SESSION["SMS_AUTH"]["PHONE"] = $_POST["PHONE"];
                // Find user with phone
                $found_users = \Ctweb\SMSAuth\Manager::GetUserID($_SESSION["SMS_AUTH"]["PHONE"]);
                $found_user_id = false;
                if (empty($found_users)) {
                    $arResult["ERRORS"][] = "USER_NOT_FOUND";
                } else {
                    if (count($found_users) > 1) {
                        $arResult["USERS_FOUND"] = $found_users;
                        $arResult["ERRORS"][] = "MULTIPLE_USERS_FOUND";

                        if (isset($_POST["USER_SELECT"])) {
                            $found_user_id = $_POST["USER_SELECT"];
                            $auth = TryAuth($found_user_id, $arParams["RELOCATION"]);
                        }
                    } elseif (count($found_users) === 1) {
                        $arResult["USERS_FOUND"] = $found_users;
                        $found_user_id = $found_users[0]["ID"];
                    }

                    // Set code to session
                    if (!isset($_SESSION["SMS_AUTH"]["CODE"]))
                        $code = \Ctweb\SMSAuth\Manager::SetAuthCode($_SESSION["SMS_AUTH"]["PHONE"]);

                    if ($code === false)
                        $arResult["ERRORS"][] = "SMS_NOT_SENDED";
                    else
                        $arResult["ERRORS"][] = "SMS_SENDED";

                    if (Option::get("ctweb.smsauth", "CSR_DEBUG") == 1) {
                        $arResult["CODE_SENDED"] = $code;
                    } else {
                        if (isset($arResult["CODE_SENDED"]))
                            unset($arResult["CODE_SENDED"]);
                    }
                }

                // Authorize
                if ($_POST["LOGIN"]) {
                    $auth = TryAuth($found_user_id, $arParams["RELOCATION"]);

                    if ($auth === "CODE_NOT_CORRECT")
                        $arResult["ERRORS"][] = "CODE_NOT_CORRECT";
                    if ($auth === "CODE_EXPIRED")
                        $arResult["ERRORS"][] = "CODE_EXPIRED";
                }
            }

        }
    }
}

function TryAuth($uid, $redirect = false)
{
    global $USER, $APPLICATION;
    if ($uid && isset($_POST["AUTH_CODE"])) {
        if (md5(strtoupper($_POST["AUTH_CODE"])) === $_SESSION["SMS_AUTH"]["CODE"] && time() < $_SESSION["SMS_AUTH"]["EXPIRE"]) {
            $bSave = false;
            if (isset($_POST["SAVE"]) && $_POST["SAVE"] === "Y")
                $bSave = true;

            $result = $USER->Authorize($uid, $bSave);
            unset($_SESSION["SMS_AUTH"]);
            if ($result) {
                if (strlen($redirect) > 0) {
                    LocalRedirect($redirect);
                }
            }
            else
                LocalRedirect($APPLICATION->GetCurPageParam());
            exit();
        } elseif (time() >= $_SESSION["SMS_AUTH"]["EXPIRE"]) {
            unset($_SESSION["SMS_AUTH"]["CODE"]);
            return "CODE_EXPIRED";
        }
    }
    return "CODE_NOT_CORRECT";
}

$this->IncludeComponentTemplate();
?>