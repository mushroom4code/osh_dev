<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 */

use Bitrix\Main\Localization\Loc;

$daDataToken = Bitrix\Main\Config\Option::get('gorillas.dadata', "apikey", SITE_ID);

if (stripos($arResult["arUser"]["EMAIL"], 'noemail.sms') !== false)
    $arResult["arUser"]["EMAIL"] = '';
$jsDaDataParam = '<script type="text/javascript">BX.ready(function(){window.daDataParam = {token: \'' . $daDataToken . '\'};});</script>';
$cAsset = Bitrix\Main\Page\Asset::getInstance()->addString($jsDaDataParam);

$this->addExternalJs('https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/js/jquery.suggestions.min.js');
$this->addExternalCss('/bitrix/modules/osh.shipping/install/css/suggestions.css');

?>

<div class="bx_profile w-full">
    <?

    ShowError($arResult["strProfileError"]);

    if ($arResult['DATA_SAVED'] == 'Y') {
        ShowNote(Loc::getMessage('PROFILE_DATA_SAVED'));
    }
    ?>
    <form method="post" class="mb-20 w-full" name="form1" action="<?= POST_FORM_ACTION_URI ?>"
          enctype="multipart/form-data"
          role="form">
        <?= $arResult["BX_SESSION_CHECK"] ?>
        <input type="hidden" name="lang" value="<?= LANG ?>"/>
        <input type="hidden" name="ID" value="<?= $arResult["ID"] ?>"/>
        <input type="hidden" name="LOGIN" value="<?= $arResult["arUser"]["LOGIN"] ?>"/>
        <div class="main-profile-block-shown" id="user_div_reg">
            <div class="md:w-9/12 w-full">
                <p class="md:text-2xl text-xl dark:text-textDarkLightGray text-lightGrayBg flex flex-row items-center mb-8">
                    <span class="mr-4 md:font-semibold font-medium">Изменить профиль</span>
                    <svg width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_2080_11931)">
                            <path fill-rule="evenodd" clip-rule="evenodd" class="dark:fill-white fill-black"
                                  d="M0 17.4173V22H4.5827L18.0986 8.4841L13.5159 3.9014L0 17.4173ZM21.6425 4.94015C22.1192 4.46355 22.1192 3.69365 21.6425 3.21705L18.7829 0.35745C18.3063 -0.11915 17.5365 -0.11915 17.0599 0.35745L14.8235 2.59381L19.4062 7.1765L21.6425 4.94015Z"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_2080_11931">
                                <rect width="22" height="22" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </p>
                <?php if (!in_array(LANGUAGE_ID, array('ru', 'ua'))) { ?>
                    <div class="row">
                        <div class="col align-items-center">
                            <div class="form-group">
                                <label for="main-profile-title"><?= Loc::getMessage('main_profile_title') ?></label>
                                <input class="w-full dark:bg-grayButton mt-3 bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="text" name="TITLE" maxlength="50"
                                       id="main-profile-title" value="<?= $arResult["arUser"]["TITLE"] ?>"/>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="form-group mb-4">
                    <label for="main-profile-name"
                           class="dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold">ФИО</label>
                    <div class="w-full mt-3">
                        <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md input_lk w-full"
                               type="text" name="NAME"
                               minlength="3" maxlength="50"
                               id="main-profile-name" value="<?= $arResult["arUser"]["NAME"] ?>"/>
                    </div>
                </div>
                <div class="flex flex-row mb-12">
                    <div class="form-group xs:w-2/5 w-1/2 mb-2 mr-3">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold"
                               for="main-profile-day">Дата рождения</label>
                        <div class="mt-3 w-full">
                            <?php if (strtotime(date('m/d/Y')) < strtotime($arResult['arUser']['UF_DATE_CHANGE_BH'])): ?>
                                <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor w-full
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md input_lk" type="text"
                                       name="PERSONAL_BIRTHDAY_INACTIVE" maxlength="50"
                                       id="main-profile-day2" disabled
                                       value="<?= date('d/m/Y', strtotime($arResult['arUser']['PERSONAL_BIRTHDAY'])) ?>"/>
                                <?php $dateChange = '<br>
                                    <b class="dark:font-medium font-semibold">Изменить дату рождения можно будет ' .
                                    date('d/m/Y', strtotime($arResult["arUser"]["UF_DATE_CHANGE_BH"])) . '</b>'; ?>
                            <?php else: ?>
                                <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md input_lk user-birthday
                          w-full xs:text-md text-sm" readonly type="text"
                                       name="PERSONAL_BIRTHDAY"
                                       maxlength="50"
                                       id="main-profile-day2"
                                       value="<?= strtotime($arResult['arUser']['PERSONAL_BIRTHDAY']) !== false ?
                                           date('d/m/Y', strtotime($arResult['arUser']['PERSONAL_BIRTHDAY']))
                                           : $arResult['arUser']['PERSONAL_BIRTHDAY'] ?>"/>
                            <?php endif; ?>
                        </div>
                        <div class="info-date text-xs dark:font-light font-normal dark:text-iconGray text-textLight mt-3">
                            В день рождения вам будут доступны персональные скидки<br>
                            Смена дня рождения доступна не чаще одного раза в год
                            <?= $dateChange ?>
                        </div>
                    </div>
                    <script>//$('input[name="PERSONAL_BIRTHDAY"]').inputmask("99/99/9999",{ "placeholder": "dd/mm/yyyy" });;
                        Inputmask("datetime", {
                            inputFormat: "dd/mm/yyyy",
                            placeholder: "_",
                            leapday: "-02-29",
                            alias: "tt/mm/jjjj"
                        }).mask("input[name='PERSONAL_BIRTHDAY']");
                    </script>
                    <div class="form-group xs:w-3/5 w-1/2 mb-2">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold"
                               for="main-profile-email">Почта</label>
                        <div class="mt-3">
                            <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md w-full"
                                   type="text"
                                   name="EMAIL"
                                   inputmode="email"
                                   maxlength="50"
                                   id="main-profile-email" value="<?= $arResult["arUser"]["EMAIL"] ?>"
                                   pattern="[^@\s]+@[^@\s]+\.[^@\s]+"/>
                        </div>
                    </div>
                </div>
                <?php if ($arResult['CAN_EDIT_PASSWORD']) { ?>
                    <div class="mb-12">
                        <div class="form-group w-full mb-4">
                            <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold"
                                   for="main-profile-password">Новый пароль</label>
                            <div class="mt-3 relative">
                                <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md w-full input_lk
                         bx-auth-input main-profile-password"
                                       type="password"
                                       name="NEW_PASSWORD" minlength="6" maxlength="50" id="main-profile-password"
                                       value=""
                                       autocomplete="new-password"/>
                                <svg width="29" height="18" viewBox="0 0 29 18" class="absolute mr-4 right-0 top-0 mt-4"
                                     onclick="showHidePasswd(this)" data-type="password"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14.5831 5.75639C17.7625 5.75639 20.3399 8.29345 20.3399 11.4231C20.3399 14.5527 17.7625 17.0897 14.5831 17.0897C11.4036 17.0897 8.82621 14.5527 8.82621 11.4231C8.82621 8.29345 11.4036 5.75639 14.5831 5.75639ZM14.5831 0.791504C21.2229 0.791504 26.9546 5.25401 28.545 11.5077C28.6898 12.077 28.3383 12.6539 27.76 12.7964C27.1817 12.9389 26.5956 12.593 26.4508 12.0237C25.0996 6.71028 20.2267 2.9165 14.5831 2.9165C8.93693 2.9165 4.06256 6.71355 2.71367 12.0301C2.56923 12.5995 1.98327 12.9457 1.40489 12.8035C0.826511 12.6614 0.474738 12.0846 0.619181 11.5153C2.20676 5.25786 7.94035 0.791504 14.5831 0.791504Z"
                                          class="fill-light-red dark:fill-white"/>
                                </svg>
                                <svg width="28" height="18" viewBox="0 0 28 18" fill="none" data-type="text"
                                     class="absolute mr-4 right-0 top-0 mt-4 hidden"
                                     xmlns="http://www.w3.org/2000/svg" onclick="showHidePasswd(this)">
                                    <path d="M14 5.75654C17.1296 5.75654 19.6667 8.2936 19.6667 11.4232C19.6667 14.5528 17.1296 17.0899 14 17.0899C10.8704 17.0899 8.33333 14.5528 8.33333 11.4232C8.33333 8.2936 10.8704 5.75654 14 5.75654ZM14 7.88154C12.044 7.88154 10.4583 9.4672 10.4583 11.4232C10.4583 13.3792 12.044 14.9649 14 14.9649C15.956 14.9649 17.5417 13.3792 17.5417 11.4232C17.5417 9.4672 15.956 7.88154 14 7.88154ZM14 0.791656C20.5358 0.791656 26.1778 5.25417 27.7432 11.5079C27.8857 12.0771 27.5398 12.6541 26.9706 12.7966C26.4013 12.9391 25.8243 12.5931 25.6819 12.0239C24.3518 6.71043 19.5553 2.91666 14 2.91666C8.44232 2.91666 3.64431 6.7137 2.31655 12.0303C2.17437 12.5996 1.59759 12.9459 1.02827 12.8037C0.458955 12.6615 0.112692 12.0847 0.254873 11.5154C1.81758 5.25801 7.46135 0.791656 14 0.791656Z"
                                          class="fill-light-red dark:fill-white"/>
                                </svg>
                            </div>
                        </div>
                        <div class="form-group w-full mb-2">
                            <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold"
                                   for="main-profile-password-confirm">
                                Подтвердите пароль
                            </label>
                            <div class="mt-3 relative">
                                <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md w-full input_lk"
                                       type="password" name="NEW_PASSWORD_CONFIRM"
                                       minlength="6"
                                       maxlength="50" value="" id="main-profile-password-confirm"
                                       autocomplete="new-password"/>
                                <svg width="29" height="18" viewBox="0 0 29 18" class="absolute mr-4 right-0 top-0 mt-4"
                                     onclick="showHidePasswd(this)" data-type="password"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14.5831 5.75639C17.7625 5.75639 20.3399 8.29345 20.3399 11.4231C20.3399 14.5527 17.7625 17.0897 14.5831 17.0897C11.4036 17.0897 8.82621 14.5527 8.82621 11.4231C8.82621 8.29345 11.4036 5.75639 14.5831 5.75639ZM14.5831 0.791504C21.2229 0.791504 26.9546 5.25401 28.545 11.5077C28.6898 12.077 28.3383 12.6539 27.76 12.7964C27.1817 12.9389 26.5956 12.593 26.4508 12.0237C25.0996 6.71028 20.2267 2.9165 14.5831 2.9165C8.93693 2.9165 4.06256 6.71355 2.71367 12.0301C2.56923 12.5995 1.98327 12.9457 1.40489 12.8035C0.826511 12.6614 0.474738 12.0846 0.619181 11.5153C2.20676 5.25786 7.94035 0.791504 14.5831 0.791504Z"
                                          class="fill-light-red dark:fill-white"/>
                                </svg>
                                <svg width="28" height="18" viewBox="0 0 28 18" fill="none"
                                     data-type="text"
                                     class="absolute mr-4 right-0 top-0 mt-4 hidden"
                                     xmlns="http://www.w3.org/2000/svg"
                                     onclick="showHidePasswd(this)">
                                    <path d="M14 5.75654C17.1296 5.75654 19.6667 8.2936 19.6667 11.4232C19.6667 14.5528 17.1296 17.0899 14 17.0899C10.8704 17.0899 8.33333 14.5528 8.33333 11.4232C8.33333 8.2936 10.8704 5.75654 14 5.75654ZM14 7.88154C12.044 7.88154 10.4583 9.4672 10.4583 11.4232C10.4583 13.3792 12.044 14.9649 14 14.9649C15.956 14.9649 17.5417 13.3792 17.5417 11.4232C17.5417 9.4672 15.956 7.88154 14 7.88154ZM14 0.791656C20.5358 0.791656 26.1778 5.25417 27.7432 11.5079C27.8857 12.0771 27.5398 12.6541 26.9706 12.7966C26.4013 12.9391 25.8243 12.5931 25.6819 12.0239C24.3518 6.71043 19.5553 2.91666 14 2.91666C8.44232 2.91666 3.64431 6.7137 2.31655 12.0303C2.17437 12.5996 1.59759 12.9459 1.02827 12.8037C0.458955 12.6615 0.112692 12.0847 0.254873 11.5154C1.81758 5.25801 7.46135 0.791656 14 0.791656Z"
                                          class="fill-light-red dark:fill-white"/>
                                </svg>
                                <small id="emailHelp"
                                       class="text-xs font-normal mt-2 dark:text-iconGray text-textLight">
                                    <?php echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"]; ?>
                                </small>
                            </div>
                        </div>
                        <label class="col-sm-12 col-md-12 link_input_address d-none" id="password-notification-error">
                        </label>
                    </div>
                <?php }
                $address = [];
                if (!empty($arResult["arUser"]["PERSONAL_STATE"])) {
                    $address[] = $arResult["arUser"]["PERSONAL_STATE"];
                }
                if (!empty($arResult["arUser"]["PERSONAL_CITY"])) {
                    $address[] = $arResult["arUser"]["PERSONAL_CITY"];
                }
                if (!empty($arResult["arUser"]["PERSONAL_STREET"])) {
                    $address[] = $arResult["arUser"]["PERSONAL_STREET"];
                }
                $strAddress = implode(', ', $address);
                $strAddress = str_replace('г Москва, Москва', 'г Москва', $strAddress);
                $strAddress = str_replace('г Санкт-Петербург, Санкт-Петербург', 'г Санкт-Петербург', $strAddress);
                ?>
                <div class="form-group mb-6">
                    <label class="dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold"
                           for="main-profile-country">Сохраненый адрес доставки:</label>
                    <div class="w-full mt-3 dark:text-iconGray text-textLight font-light md:text-md text-sm">
                        <?= $strAddress ?>
                    </div>
                </div>
                <div class="form-group mb-8">
                    <label class="dark:text-textDarkLightGray text-textLight text-md dark:font-normal font-semibold"
                           for="main-profile-address">Изменить адрес</label>
                    <div class="w-full relative mt-3">
                        <input class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md w-full input_lk"
                               type="text" name="" maxlength="100"
                               id="main-profile-address" value=" <?= $strAddress ?>"/>
                    </div>
                    <a style="display:none;" href="javascript:void(0);"
                       class="col-sm-12 col-md-12 link_input_address" id="edit_address">Ввести
                        новый
                        адрес</a>
                </div>
                <div class="form-group mb-3 hidden" id="edit_addressBox">
                    <div class="form-group  mb-2">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                               for="main-profile-state">Область/край</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="text" name="PERSONAL_STATE" maxlength="50"
                                   id="main-profile-state" value="<?= $arResult["arUser"]["PERSONAL_STATE"] ?>"/>
                        </div>
                    </div>
                    <div class="form-group  mb-2">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                               for="main-profile-city">Город</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="text" name="PERSONAL_CITY" maxlength="50"
                                   id="main-profile-city" value="<?= $arResult["arUser"]["PERSONAL_CITY"] ?>"/>
                        </div>
                    </div>

                    <div class="form-group  mb-2">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                               for="main-profile-street">Улица</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="text" name="PERSONAL_STREET" maxlength="50"
                                   id="main-profile-street" value="<?= $arResult["arUser"]["PERSONAL_STREET"] ?>"/>
                        </div>
                    </div>
                    <a href="javascript:void(0);" class="w-72 link_input_address mb-3">Сохранить</a>
                </div>
                <div class="form-group notification_box mb-2">
                    <input class="check_input xs:p-5 p-4 dark:bg-grayButton checked:hover:bg-grayButton border-iconGray
                    dark:text-white cursor-pointer font-normal rounded-full text-light-red
                  checked:focus:bg-grayButton mr-2 input_lk_notification" type="checkbox"
                           name="notification" checked
                           id="notification"/>
                    <label class="main-profile-form-label_notification dark:text-textDarkLightGray dark:font-light
                    font-medium md:text-md text-sm"
                           for="notification">
                        Согласие на обработку персональных данных</label>
                </div>
                <label class="link_input_address hidden" id="notification-error">
                    Необходимо согласие на обработку персольнальных данных
                </label>
                <div class="mt-10">
                    <input type="submit" class="btn dark:bg-dark-red rounded-md bg-light-red text-white xs:px-7 py-3
                             dark:shadow-md shadow-shadowDark font-light dark:hover:bg-hoverRedDark cursor-pointer
                              sm:w-72 px-5 w-56 get_code_button profile xs:text-md text-sm sm:font-normal
                               link_red_button main-profile-submit"
                           id="main-profile-submit"
                           name="save"
                           value="<?= (($arResult["ID"] > 0) ?
                               Loc::getMessage("MAIN_SAVE") : Loc::getMessage("MAIN_ADD")) ?>">
                </div>
            </div>
        </div>
        <input type=hidden name="CHANGE_FORM" class="CHANGE_FORM" value="">
    </form>
    <?php $APPLICATION->IncludeComponent(
        "ctweb:sms.authorize",
        "profile",
        array(
            "ALLOW_MULTIPLE_USERS" => "Y",
            "PROFILE_AUTH" => "Y",
            "USER_PHONE" => $arResult['arUser']['PHONE_NUMBER']
        )
    ); ?>

    <div class="clearfix"></div>
    <script>
        let date_now = new Date();
        let year_now = date_now.getFullYear();
        let date_datipicker = date_now.setFullYear(year_now - 18);

        function showHidePasswd(item) {
            const parentBox = item.closest('div');
            const inputBox = parentBox.querySelector('input');
            inputBox.type === 'password' ? inputBox.type = 'text' : inputBox.type = 'password'
            parentBox.querySelector('[data-type="text"]').classList.toggle('hidden')
            parentBox.querySelector('[data-type="password"]').classList.toggle('hidden')
        }

        $('#main-profile-email').inputmask('email');
        $('input[name="PERSONAL_BIRTHDAY"]').datepicker({
            dateFormat: 'dd/mm/yyyy',
            maxDate: date_now,
            autoClose: true,
            toggleSelected: false,
        });
    </script>
    <script>
        BX.Sale.PrivateProfileComponent.init();
    </script>
</div>
