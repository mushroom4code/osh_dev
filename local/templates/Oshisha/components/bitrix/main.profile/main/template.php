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
    <form method="post" class="mb-5 w-full" name="form1" action="<?= POST_FORM_ACTION_URI ?>"
          enctype="multipart/form-data"
          role="form">
        <?= $arResult["BX_SESSION_CHECK"] ?>
        <input type="hidden" name="lang" value="<?= LANG ?>"/>
        <input type="hidden" name="ID" value="<?= $arResult["ID"] ?>"/>
        <input type="hidden" name="LOGIN" value="<?= $arResult["arUser"]["LOGIN"] ?>"/>
        <div class="main-profile-block-shown" id="user_div_reg">
            <div class="lg:w-9/12 w-full">
                <p class="text-2xl dark:text-textDarkLightGray text-textLight flex flex-row items-center mb-5">
                    <span class="mr-4">Изменить профиль</span>
                    <svg width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_2080_11931)">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M0 17.4173V22H4.5827L18.0986 8.4841L13.5159 3.9014L0 17.4173ZM21.6425 4.94015C22.1192 4.46355 22.1192 3.69365 21.6425 3.21705L18.7829 0.35745C18.3063 -0.11915 17.5365 -0.11915 17.0599 0.35745L14.8235 2.59381L19.4062 7.1765L21.6425 4.94015Z"
                                  fill="#E8E8E8"/>
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
                    <label for="main-profile-name">ФИО</label>
                    <div class="w-full mt-3">
                        <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 outline-none rounded-md
                             input_lk w-full" type="text" name="NAME"
                               minlength="3" maxlength="50"
                               id="main-profile-name" value="<?= $arResult["arUser"]["NAME"] ?>"/>
                    </div>
                </div>
                <div class="flex flex-row xs:flex-col mb-8">
                    <div class="form-group xs:w-full w-2/5 mb-2 mr-3">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                               for="main-profile-day">Дата рождения</label>
                        <div class="mt-3 w-full">
                            <?php if (strtotime(date('m/d/Y')) < strtotime($arResult['arUser']['UF_DATE_CHANGE_BH'])): ?>
                                <input class="dark:bg-grayButton w-full bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="text" name="PERSONAL_BIRTHDAY_INACTIVE" maxlength="50"
                                       id="main-profile-day2" disabled
                                       value="<?= date('d/m/Y', strtotime($arResult['arUser']['PERSONAL_BIRTHDAY'])) ?>"/>
                                <?php $dateChange = '<br><b>Изменить дату рождения можно будет ' .
                                    date('d/m/Y', strtotime($arResult["arUser"]["UF_DATE_CHANGE_BH"])) . '</b>'; ?>
                            <?php else: ?>
                                <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk user-birthday w-full" readonly type="text"
                                       name="PERSONAL_BIRTHDAY"
                                       maxlength="50"
                                       id="main-profile-day2"
                                       value="<?= strtotime($arResult['arUser']['PERSONAL_BIRTHDAY']) !== false ?
                                           date('d/m/Y', strtotime($arResult['arUser']['PERSONAL_BIRTHDAY']))
                                           : $arResult['arUser']['PERSONAL_BIRTHDAY'] ?>"/>
                            <?php endif; ?>
                        </div>
                        <div class="info-date text-xs font-light dark:text-iconGray text-textLight mt-3">
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
                    <div class="form-group xs:w-full w-3/5 mb-2">
                        <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                               for="main-profile-email">Почта</label>
                        <div class="mt-3">
                            <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 w-full outline-none
                                    rounded-md input_lk" type="email" name="EMAIL" maxlength="50"
                                   id="main-profile-email" value="<?= $arResult["arUser"]["EMAIL"] ?>"
                                   pattern="[^@\s]+@[^@\s]+\.[^@\s]+"/>
                        </div>
                    </div>
                </div>
                <?php if ($arResult['CAN_EDIT_PASSWORD']) { ?>
                    <div class="mb-8">
                        <div class="form-group w-full mb-2">
                            <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                                   for="main-profile-password">Новый пароль</label>
                            <div class="mt-3">
                                <input class="dark:bg-grayButton bg-textDark w-full border-none py-3 px-4 outline-none
                                    rounded-md input_lk bx-auth-input main-profile-password"
                                       type="password"
                                       name="NEW_PASSWORD" minlength="6" maxlength="50" id="main-profile-password"
                                       value=""
                                       autocomplete="new-password"/>
                            </div>
                        </div>
                        <div class="form-group w-full mb-2">
                            <label class="mb-3 dark:text-textDarkLightGray text-textLight text-md"
                                   for="main-profile-password-confirm">
                                Подтвердите пароль
                            </label>
                            <div class="mt-3">
                                <input class="dark:bg-grayButton bg-textDark w-full border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="password" name="NEW_PASSWORD_CONFIRM"
                                       minlength="6"
                                       maxlength="50" value="" id="main-profile-password-confirm"
                                       autocomplete="new-password"/>
                                <small id="emailHelp" class="text_small">
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
                <div class="form-group mb-5">
                    <label class="dark:text-textDarkLightGray text-textLight text-md"
                           for="main-profile-country">Сохраненый адрес доставки:</label>
                    <div class="w-full mt-2 dark:text-iconGray text-textLight font-light">
                        <?= $strAddress ?>
                    </div>
                </div>
                <div class="form-group mb-6">
                    <label class="dark:text-textDarkLightGray text-textLight text-md"
                           for="main-profile-address">Изменить адрес</label>
                    <div class="col-sm-12 col-md-12 mt-3">
                        <input class="dark:bg-grayButton bg-textDark border-none py-3 px-4 outline-none
                                    rounded-md input_lk" type="text" name="" maxlength="100"
                               id="main-profile-address" value=" <?= $strAddress ?>"/>
                    </div>
                    <a style="display:none;" href="javascript:void(0);"
                       class="col-sm-12 col-md-12 link_input_address" id="edit_address">Ввести
                        новый
                        адрес</a>
                </div>
                <div class="form-group mb-3 hidden"  id="edit_addressBox">
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
                    <a href="javascript:void(0);" class="col-sm-12 col-md-12 link_input_address mb-3">Сохранить</a>
                </div>

                <div class="form-group notification_box mb-2">
                    <input class="check_input p-4 dark:bg-grayButton checked:hover:bg-grayButton border-0
                    dark:text-white cursor-pointer text-textLight font-normal rounded-full bg-textDark
                     checked:focus:bg-grayButton mr-2 input_lk_notification" type="checkbox"
                           name="notification"
                           id="notification"/>
                    <label class="main-profile-form-label_notification" for="notification">
                        Согласие на обработку персональных данных</label>
                </div>
                <label class="col-sm-12 col-md-12 link_input_address hidden" id="notification-error">
                    Необходимо согласие на обработку персольнальных данных
                </label>
                <div class="col">
                    <input type="submit" class="btn dark:bg-dark-red rounded-md bg-light-red text-white px-7 py-3 w-fit
                             dark:shadow-md shadow-shadowDark dark:hover:bg-hoverRedDark cursor-pointer link_red_button main-profile-submit" id="main-profile-submit"
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
