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
    } ?>
    <div id="profile_people">
        <h5 class="text-2xl dark:text-textDarkLightGray text-textLight dark:font-normal font-semibold mb-5">
            Личный кабинет
        </h5>
        <div class="md:mb-16 mb-10">
            <div class="bg-white shadow-lg dark:bg-darkBox rounded-xl py-10 px-8 xl:w-1/2 w-full bg-no-repeat
            bg-boxUser dark:bg-boxUserDark  bg-right-bottom bg-contain">
                <div class="box_user">
                    <p class="mb-5 text-xl dark:text-textDarkLightGray text-textLight dark:font-normal font-semibold">
                        <?= $text_name ?>
                    </p>
                    <?php if ($email_bool) { ?>
                        <p class="mb-3 text-sm dark:text-textDarkLightGray text-textLight font-normal">Почта:
                            <?= $Email ?>
                        </p>
                    <?php } ?>
                    <p class="mb-6 text-sm dark:text-textDarkLightGray text-textLight font-normal">
                        Телефон: <?= $number_phone['PERSONAL_PHONE']; ?></p>
                    <a class="dark:text-white text-sm font-normal dark:font-light flex flex-row text-textLight
                    dark:hover:text-white hover:text-light-red items-center w-fit"
                       href="/?logout=yes&<?= bitrix_sessid_get() ?>" title="Выйти">
                        <svg width="22" height="24" viewBox="0 0 24 26" fill="none"
                             class="mr-3 stroke-black dark:stroke-white" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.1914 13.0831H22.4988M22.4988 13.0831L18.9385 16.7776M22.4988 13.0831L18.9385 9.38867"
                                  stroke-width="2.17" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M18.6147 5.69444V4.46296C18.6147 3.10271 17.5521 2 16.2412 2H4.37353C3.06267 2 2 3.10271 2 4.46296V21.7037C2 23.064 3.06267 24.1667 4.37353 24.1667H16.2412C17.5521 24.1667 18.6147 23.064 18.6147 21.7037V20.4722"
                                  stroke-width="2.17" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        Выйти</a>
                </div>
            </div>
        </div>
        <div id="createContragent" class="w-full"></div>
    </div>
<?php }

