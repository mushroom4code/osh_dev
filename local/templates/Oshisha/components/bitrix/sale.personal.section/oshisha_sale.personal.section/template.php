<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 */

if ($arParams["MAIN_CHAIN_NAME"] <> '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$daDataToken = Bitrix\Main\Config\Option::get('osh.shipping', "osh_da_data_token");
$jsDaDataParam = "
                    <script type=\"text/javascript\">
                        BX.ready(function(){
                            window.daDataParam = {token: '{$daDataToken}'};
                        });
                    </script>
                ";
$cAsset = Bitrix\Main\Page\Asset::getInstance()->addString($jsDaDataParam);

$this->addExternalJs('/bitrix/js/osh.shipping/jquery.suggestions.min.js');
$this->addExternalCss('/bitrix/modules/osh.shipping/install/css/suggestions.css');
$theme = Bitrix\Main\Config\Option::get("main", "wizard_eshop_bootstrap_theme_id", "blue", SITE_ID);

use Bitrix\Main\Localization\Loc;

global $USER;
if ($USER->IsAuthorized()) {

    if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y' && !$USER->IsAuthorized()) {
        LocalRedirect($arResult['PATH_TO_AUTH_PAGE']);
    }

    ShowError($arResult["strProfileError"]);


    if ($arParams["MAIN_CHAIN_NAME"] <> '') {
        $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
    }
    $APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PRIVATE"));

    $APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ACCOUNT"));
    $text_name = trim($USER->GetFullName());
    $Email = trim($USER->GetEmail());
     /*$text_name = '';
   if (!$name) {
        $name = trim($USER->GetLogin());
        if (strpos($name, 'user_') !== false) {
            $text_name = 'Имя Пользователя';
        } else {
            $text_name = $name;
        }
    }*/
    $user_id = $USER->GetId();
    $phone = CUser::GetByID($user_id);
    $number_phone = $phone->fetch();
    $email_bool = true;
    if (strpos($Email, 'noemail')) {
        $email_bool = false;
    }

//    $user_object =  new EnteregoUserExchange();
//    $user_object->USER_ID = $user_id;
//    $user_object->GetCompanyForUser();
//    $user_object->GetContragentsUser();
//    \Enterego\contragents\EnteregoExchange::GetInfoForXML();
    ?>
    <div class="hides" id="profile_people">
        <h5 class="mb-4"><b>Личный кабинет</b></h5>
        <div class="d-flex row_section mb-5 justify-content-between">
            <div class="cart_box">
                <div class="box_photos_cart"></div>
                <div class="box_user">
                    <p class="cart_text_name"><b><?= $text_name ?></b>
                    </p>
                    <?php if ($email_bool) { ?> <p class="cart_text">Почта: <?= $Email ?></p><?php } ?>
                    <p class="cart_text">Телефон: <?= $number_phone['PERSONAL_PHONE']; ?></p>
                    <a class="cart_text_link" href="/?logout=yes&<?= bitrix_sessid_get() ?>" title="Выйти">Выйти</a>
                </div>
            </div>
            <div class="cart_box_bonus" style="display:none;">
                <div class="progress-bar-wrapper">
                    <div class="center-circle">300</div>
                    <div class="sector"></div>
                </div>
                <div class="box_width">
                    <p class="lk">Баланс карты</p>
                    <p class="lk_light_cart">Номер карты:<br> 39458404544646</p>
                    <a href="#" class="lk_light_cart_small">Подробнее</a>
                </div>
            </div>
        </div>
    </div>
<?php }

