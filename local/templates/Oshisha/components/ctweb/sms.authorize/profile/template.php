<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */

use Bitrix\Main\Page\Asset;
use \Ctweb\SMSAuth\Manager;

CJSCore::Init(['masked_input']);

Asset::getInstance()->addJS("https://cdn.jsdelivr.net/npm/jquery.maskedinput@1.4.1/src/jquery.maskedinput.min.js");
Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js");

$mainID = $this->GetEditAreaId('');
$jsParams = array(
    'TEMPLATE' => array(
        'MAIN_ID' => $mainID,
        'MAIL_FORM' => $mainID . 'mail',
        'SAVE_SESSION' => $mainID . 'save_session',
        'CODE' => $mainID . 'code',
        'COMPONENT_ID_BUTTON_CODE' => $mainID . '_form',
        'TIMER' => $mainID . 'timer',
        'SUBMIT' => $mainID . 'submit',
        'BACK' => $mainID . 'back',
        'STATE' => $mainID . 'state',
        'ERROR_TITLE' => $mainID . 'error_title',
        'ERROR_TEXT' => $mainID . 'error_text',
        'RESEND' => $mainID . 'resend',
        'MSG_NOT_COME' => $mainID . 'msg_not_come',
        'CHANGE_PHONE' => $mainID . 'change_phone',
        'AUTH_EMAIL_LOGIN' => $mainID . 'auth_email_login',
        'LOGIN' => $mainID . 'login',
        'EMAIL' => $mainID . 'email',
        'PASSWORD' => $mainID . 'password',
        'ERROR_ALERT' => $mainID . 'error_alert',
        'REGISTRATION' => $mainID . 'registration',
        'AUTH_PHONE_LOGIN' => $mainID . 'auth_phone_login',
        'FORGOT_PASSWORD' => $mainID . 'forgot_pass'
    ),
    'DATA' => array(
        'TIME_LEFT' => $arResult['REUSE_TIME'],
    )
);

if ($arParams['PROFILE_AUTH'] == "Y"):?>
    <div style="display: block" class="ctweb-smsauth-menu-block profile mb-8 md:w-9/12 w-full"
         data-id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>">
        <div class="ctweb-smsauth-box profile">
            <div>
                <div class="row">
                    <form id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>"
                          class="ctweb-smsauth-menu-form profile"
                          action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                          method="POST" name="auth">
                        <?php echo bitrix_sessid_post(); ?>
                        <input type="hidden" name="FORM_ID"
                               value="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>">
                        <input type="hidden" name="PROFILE_AUTH" value="<?= $arParams['PROFILE_AUTH'] ?>">
                        <input type="hidden" name="recaptcha_token" value="">
                        <input id="<?= $jsParams['TEMPLATE']['STATE'] ?? Manager::STEP_PHONE_WAITING ?>" type="hidden"
                               name=""
                               value="<?= $arResult['STEP'] ?>">
                        <?php if ($arResult['AUTH_RESULT'] === 'SUCCESS') : ?>
                            <? ShowNote(GetMessage('SMS_PHONE_SAVED')); ?>
                        <?php elseif ($arResult['AUTH_RESULT'] === 'FAILED'): ?>
                            <? ShowError($arResult["strProfileError"]); ?>
                        <?php endif; ?>
                        <p class="md:text-2xl dark:text-textDarkLightGray text-textLight flex flex-row items-center
                        mb-5 mt-8 text-xl">
                            <span class="mr-4 md:font-semibold font-medium">Изменение номера телефона</span>
                            <svg width="21" height="20" viewBox="0 0 24 23" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path class="dark:fill-white fill-black"
                                      d="M0 3.19444C0 14.1327 9.01098 23 20.1266 23C20.628 23 21.1252 22.982 21.6176 22.9465C22.1826 22.9057 22.465 22.8854 22.7222 22.7397C22.9352 22.6191 23.1371 22.4052 23.2439 22.1873C23.3728 21.9244 23.3728 21.6176 23.3728 21.0041V17.4042C23.3728 16.8883 23.3728 16.6303 23.2864 16.4092C23.2104 16.2139 23.0865 16.0399 22.926 15.9027C22.7443 15.7473 22.4979 15.6592 22.0052 15.4828L17.8412 13.9928C17.2679 13.7877 16.9812 13.6851 16.7093 13.7025C16.4695 13.7178 16.2388 13.7985 16.0427 13.9352C15.8204 14.0902 15.6635 14.3475 15.3497 14.8623L14.2834 16.6111C10.8425 15.0777 8.0531 12.3292 6.49244 8.94444L8.26959 7.89516C8.79265 7.58633 9.05418 7.43191 9.21174 7.21313C9.35068 7.02021 9.43261 6.79314 9.44819 6.55717C9.46585 6.28957 9.36158 6.00751 9.15318 5.4434L7.63899 1.34577C7.45982 0.860915 7.37023 0.618483 7.21235 0.439683C7.07288 0.281737 6.89613 0.159914 6.69763 0.0849085C6.47291 1.52323e-07 6.21077 0 5.6865 0H2.02825C1.40481 0 1.09308 9.52019e-08 0.825813 0.12682C0.604446 0.231866 0.387131 0.430624 0.264502 0.640205C0.116449 0.893256 0.0957634 1.17124 0.0543936 1.72721C0.0183475 2.21165 0 2.70094 0 3.19444Z"/>
                            </svg>
                        </p>
                        <!--STEP PHONE WAITING-->
                        <div id="ctweb_form_step_1"
                             class="ctweb-smsauth-menu-step hidden lg:w-9/12 w-full">
                            <p class="form-group mb-6 text-xs font-normal mt-6 dark:text-iconGray text-textLight profile-asterisk">
                                * Будет выслан код подтверждения</p>
                            <div class="form-group mb-2 flex flex-col relative w-full">
                                <label class="main-profile-form-label mb-3 dark:text-textDarkLightGray text-textLight
                                 dark:font-normal font-semibold text-md" for="smsauth-phone">
                                    <?= GetMessage("SMS_AUTH_PHONE") ?></label>
                                <span id="flag"></span>
                                <div class="code relative">
                                    <input type="text" name="PHONE"
                                           data-input-type="phone"
                                           placeholder="+_ (___)-___-____"
                                           inputmode="text"
                                           value="<?= $arParams['USER_PHONE'] ?? '' ?>"
                                           class="w-full dark:bg-grayButton bg-white dark:border-none border-borderColor
                                           focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md
                                           input_lk profile auth-phone-profile"
                                           id="<?= $mainID . "phone" ?>"
                                           autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                        <div class="checkbox hidden">
                            <label>
                                <input type="checkbox" name="SAVE_SESSION" value="Y"
                                       id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                    <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?>
                            </label>
                        </div>
                </div>

                <!-- STEP CODE WAITING -->
                <div id="ctweb_form_step_3"
                     class="profile ctweb-smsauth-menu-step w-full
                     <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? 'flex' : 'hidden' ?> ">
                    <h3 class="text-xs mb-3 font-normal dark:text-iconGray text-textLight">
                        <?= GetMessage("SMS_AUTH_ENTER_CODE") ?>
                    </h3>
                    <div class="form-group flex flex-col mb-2">
                        <label class="ctweb-label text-xs mb-5 font-normal dark:text-iconGray text-textLight"
                               for="sms-auth-code"></label>
                        <div style="display: none">
                            <a class="ctweb-link"><?= GetMessage("SMS_AUTH_CHANGE_NUMBER_PHONE") ?></a>
                        </div>
                        <label style="display: none" class="ctweb-label"
                               for="sms-auth-code"><?= GetMessage("SMS_AUTH_CODE") ?></label>
                        <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                               class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                                   focus:border-borderColor shadow-none py-3 px-4 outline-none rounded-md w-full
                                    input_lk profile auth_code"
                               autocomplete="off">
                        <span id="result"></span>
                    </div>

                    <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                            id="<?= $jsParams['TEMPLATE']['TIMER'] ?>"
                            class="ctweb-timer font-light mb-4 text-textLight dark:text-textDarkLightGray text-xs "></div>
                    <input type="submit" id="submit_code" class="hidden">
                </div>

                <!-- ERROR STEP -->
                <div id="ctweb_form_step_error" class="ctweb-smsauth-menu-step hidden">
                    <h3 class="ctweb-title text-hover-red mb-3 text-md font-semibold"
                        id="<?= $jsParams['TEMPLATE']['ERROR_TITLE'] ?>"></h3>
                    <div class="form-group">
                        <label class="ctweb-label ctweb-label-error text-hover-red font-semibold mb-3 text-sm"
                               id="<?= $jsParams['TEMPLATE']['ERROR_TEXT'] ?>"></label>
                    </div>
                </div>

                <!--Навигация по форме авторизации-->
                <div class="ctweb-button-block profile mt-6">
                    <input class="btn btn dark:bg-dark-red rounded-md bg-light-red text-white xs:px-7 py-3
                             dark:shadow-md shadow-shadowDark font-light dark:hover:bg-hoverRedDark cursor-pointer
                              sm:w-72 px-5 w-56 get_code_button profile xs:text-md text-sm sm:font-normal mb-5"
                           id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>"
                           type="submit"
                           value="Сохранить"
                           onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">

                    <div style="display: none" class="ctweb-button-back">
                        <a class="ctweb-link cursor-pointer flex flex-row mb-5"
                           id="<?= $jsParams['TEMPLATE']['BACK'] ?>">
                            <svg width="21" height="22" viewBox="0 0 34 35" class="mr-3"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path class="fill-light-red dark:fill-white"
                                      d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"/>
                            </svg>
                            <?= GetMessage("SMS_AUTH_BACK") ?></a>
                    </div>

                    <div style="display: none" class="ctweb-button-send-code-again">
                        <a class="ctweb-link cursor-pointer flex flex-row mb-5"
                           id="<?= $jsParams['TEMPLATE']['RESEND'] ?>">
                            <svg width="21" height="23" viewBox="0 0 21 23" fill="none" class="mr-3"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.4434 0.727539V3.86709C4.83121 3.86709 0.257324 8.09636 0.257324 13.2857C0.257324 18.4751 4.83121 22.7044 10.4434 22.7044C16.0557 22.7044 20.6296 18.4751 20.6296 13.2857C20.6296 11.224 19.8998 9.31856 18.6777 7.76496L17.064 9.25706C17.8854 10.4119 18.366 11.7945 18.366 13.2857C18.366 17.344 14.8323 20.6114 10.4434 20.6114C6.05454 20.6114 2.52091 17.344 2.52091 13.2857C2.52091 9.22752 6.05454 5.96012 10.4434 5.96012V9.09968L16.1024 4.91361L10.4434 0.727539Z"
                                      class="fill-light-red dark:fill-white"/>
                            </svg>
                            <?= GetMessage("SMS_AUTH_SEND_CODE_AGAIN") ?>
                        </a>
                    </div>

                    <div class="ctweb-button-new-code" id="new_code_block">
                        <a class="ctweb-link glowing-text flex flex-row mb-5 cursor-pointer"
                           id="<?= $mainID . 'REUSE' ?>">
                            <svg width="21" height="23" viewBox="0 0 21 23" fill="none" class="mr-3"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.4434 0.727539V3.86709C4.83121 3.86709 0.257324 8.09636 0.257324 13.2857C0.257324 18.4751 4.83121 22.7044 10.4434 22.7044C16.0557 22.7044 20.6296 18.4751 20.6296 13.2857C20.6296 11.224 19.8998 9.31856 18.6777 7.76496L17.064 9.25706C17.8854 10.4119 18.366 11.7945 18.366 13.2857C18.366 17.344 14.8323 20.6114 10.4434 20.6114C6.05454 20.6114 2.52091 17.344 2.52091 13.2857C2.52091 9.22752 6.05454 5.96012 10.4434 5.96012V9.09968L16.1024 4.91361L10.4434 0.727539Z"
                                      class="fill-light-red dark:fill-white"/>
                            </svg>
                            <?= GetMessage('SMS_AUTH_REUSE_CODE') ?></a>
                    </div>

                    <div>
                        <a class="ctweb-link flex flex-row cursor-pointer"
                           id="<?= $jsParams['TEMPLATE']['CHANGE_PHONE'] ?>">
                            <svg width="20" height="20" viewBox="0 0 22 22" fill="none" class="mr-3"
                                 xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_2080_11931)">
                                    <path fill-rule="evenodd" clip-rule="evenodd" class="dark:fill-white fill-light-red"
                                          d="M0 17.4173V22H4.5827L18.0986 8.4841L13.5159 3.9014L0 17.4173ZM21.6425 4.94015C22.1192 4.46355 22.1192 3.69365 21.6425 3.21705L18.7829 0.35745C18.3063 -0.11915 17.5365 -0.11915 17.0599 0.35745L14.8235 2.59381L19.4062 7.1765L21.6425 4.94015Z"></path>
                                </g>
                            </svg>
                            <?= GetMessage("SMS_AUTH_CHANGE_PHONE") ?></a>
                    </div>


                    <div style="display:none">
                        <a class="ctweb-link hover:underline flex flex-row items-center text-sm dark:font-normal
                        font-medium" href="/about/FAQ/"
                           id="<?= $jsParams['TEMPLATE']['MSG_NOT_COME'] ?>">
                            <svg width="10" height="16" viewBox="0 0 10 16" fill="none"
                                 xmlns="http://www.w3.org/2000/svg"
                                 class="dark:stroke-white stroke-light-red">
                                <path d="M2.125 5.08925C2.125 1.15171 8.3125 1.15175 8.3125 5.08925C8.3125 7.90175 5.5 7.33913 5.5 10.7141"
                                      stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M5.5 14.2662L5.51458 14.25"
                                      stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?= GetMessage("SMS_AUTH_CODE_NOT_RESPONSE") ?></a>
                    </div>

                </div>
                </form>

            </div>
        </div>
    </div>
<?php elseif ($arParams['REGISTER'] == "Y") : ?>
    <div class="ctweb-smsauth-menu-block profile" data-id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>">
        <div class="ctweb-smsauth-box profile">
            <?php if ($arResult['AUTH_RESULT'] === 'SUCCESS') : ?>
                <?php if ($arResult['STEP'] === Manager::STEP_SUCCESS) : ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="error alert alert-success">
                            <?= GetMessage("SMS_SUCCESS_AUTH"); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="error alert alert-success">
                            <?= GetMessage("SMS_AUTH_ALREADY_AUTH"); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php else: ?>
                <form id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>" class="ctweb-smsauth-menu-form"
                      action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                      method="POST" name="auth">
                    <?php echo bitrix_sessid_post(); ?>
                    <input type="hidden" name="FORM_ID"
                           value="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?> ?>">
                    <input type="hidden" name="PROFILE_AUTH" value="<?= $arParams['PROFILE_AUTH'] ?>">
                    <input type="hidden" name="REGISTER" value="<?= $arParams['REGISTER'] ?>">
                    <input type="hidden" name="register" value="yes">
                    <input type="hidden" name="recaptcha_token" value="">
                    <input id="<?= $jsParams['TEMPLATE']['STATE'] ?? Manager::STEP_PHONE_WAITING ?>" type="hidden"
                           name=""
                           value="<?= $arResult['STEP'] ?>">
                    <div class="col-11 col-md-7 p-0 mb-2">
                        <!--STEP PNONE WAITING-->
                        <div id="ctweb_form_step_1" class="ctweb-smsauth-menu-step">
                            <p class="message_for_user_minzdrav font-14 mb-4">
                                Розничная дистанционная продажа (доставка) кальянов, табачной, никотинсодержащей
                                продукции
                                на
                                сайте не осуществляется. Сайт предназначен для потребителей старше 18 лет.</p>
                            <div class="d-flex flex-lg-row flex-md-row flex-column justify-content-between mb-3">
                                <p class="font-12 font-weight-bold">
                                    <span class="starrequired color-redLight">* </span>
                                    <?= GetMessage("AUTH_REQ") ?>
                                </p>
                                <p class="font-14 color-redLight font-weight-bold">
                                    <a href="<?= $arResult["AUTH_AUTH_URL"] ?>" rel="nofollow">
                                        <ins><?= GetMessage("AUTH_AUTH") ?></ins>
                                    </a>
                                </p>
                            </div>
                            <div class="d-flex flex-lg-row flex-md-row flex-column">
                                <div class="form-group mb-3 pl-lg-0 pl-md-0 pl-0 pr-lg-3 pr-md-3 pr-0">
                                    <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2 p-0 mb-2"
                                           for="main-profile-name"><?= GetMessage("AUTH_NAME") ?></label>
                                    <input type="text" name="NAME" maxlength="50"
                                           class="form-control dark:bg-grayButton input_lk bx-auth-input"
                                           value="<?= $arResult["NAME"] ?>"/>
                                </div>
                                <div class="form-group mb-3  p-0">
                                    <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                           for="main-profile-name"><?= GetMessage("AUTH_LAST_NAME") ?></label>
                                    <input type="text" name="LAST_NAME" maxlength="50"
                                           class="form-control dark:bg-grayButton input_lk bx-auth-input"
                                           value="<?= $arResult["LAST_NAME"] ?>"/>
                                </div>
                            </div>
                            <div class="d-flex flex-lg-row flex-md-row flex-column">
                                <div class="form-group mb-3 pl-lg-0 pl-md-0 pl-0 pr-lg-3 pr-md-3 pr-0">
                                    <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                           for="main-profile-name">
                                        <span class="starrequired color-redLight  color-redLight">* </span>
                                        <?= GetMessage("AUTH_EMAIL") ?>
                                    </label>
                                    <input type="text" name="EMAIL" maxlength="255"
                                           class="form-control dark:bg-grayButton input_lk bx-auth-input"
                                           required minlength="5"
                                           placeholder="_@_._"
                                           value="<?= $arResult["EMAIL"] ?>"/>
                                </div>
                                <div class="form-group mb-3 p-0">
                                    <div class="d-flex flex-row align-items-center mb-2 position-relative">
                                        <label class="col-form-label main-profile-form-label p-0"
                                               for="main-profile-name">
                                            <span class="starrequired color-redLight">* </span>
                                            <?= GetMessage("PERSONAL_BIRTHDAY") ?>
                                        </label>
                                        <i class="fa fa-question-circle-o font-20 color-redLight ml-2 block-icon-text"
                                           aria-hidden="true"></i>
                                        <div class="hidden block-text br-10 p-3">
                                            <p class="m-0">
                                                Возраст необходимо указать для открытия информации не доступной к
                                                просмотру
                                                лицам не достигшим 18 лет.</p>
                                        </div>
                                    </div>
                                    <input type="text" name="PERSONAL_BIRTHDAY" required
                                           class="form-control dark:bg-grayButton input_lk bx-auth-input user-birthday readonly"
                                           inputmode="none"
                                           id="main-profile-brd"
                                           autocomplete="Off"
                                           value=""
                                           minlength="8"
                                           placeholder="dd/mm/yyyy"/>
                                </div>
                            </div>
                            <div class="form-group mb-1">
                                <label class="col-form-label main-profile-form-label p-0 mb-2"
                                       for="main-profile-name">
                                    <span class="starrequired color-redLight">* </span>
                                    <?= GetMessage("AUTH_PASSWORD_REQ") ?>
                                </label>
                                <input type="password" name="PASSWORD" maxlength="255"
                                       minlength="6"
                                       class="form-control input_lk bx-auth-input js__show-pass" required
                                       value="<?= $arResult["PASSWORD"] ?>" autocomplete="off"/>
                                <?php if ($arResult["SECURE_AUTH"]): ?>
                                    <span class="bx-auth-secure" id="bx_auth_secure"
                                          title="<?php echo GetMessage("AUTH_SECURE_NOTE") ?>"
                                          style="display:none">
					        <div class="bx-auth-secure-icon"></div>
				            </span>
                                    <noscript>
                                <span class="bx-auth-secure" title="<?= GetMessage("AUTH_NONSECURE_NOTE") ?>">
                                    <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                                </span>
                                    </noscript>
                                    <script type="text/javascript">
                                        document.getElementById('bx_auth_secure').style.display = 'inline-block';
                                    </script>
                                <?php endif ?>
                            </div>
                            <p class="font-11 font-weight-bold color-redLight"><?= $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"]; ?></p>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                       for="main-profile-name">
                                    <span class="starrequired color-redLight  color-redLight">* </span>
                                    <?= GetMessage("AUTH_CONFIRM") ?>
                                </label>
                                <input type="password" name="CONFIRM_PASSWORD" maxlength="255"
                                       class="form-control input_lk bx-auth-input js__show-pass"
                                       minlength="6"
                                       value="<?= $arResult["CONFIRM_PASSWORD"] ?>"
                                       required
                                       autocomplete="off"/>
                            </div>
                            <div class="d-flex flex-lg-row flex-md-row flex-column">
                                <div class="col-md-8 col-lg-8 col-12 pl-0 flex-column">
                                    <div class="d-flex flex-row align-items-center font-14 mb-3 mt-3">
                                        <input type="checkbox" required class="check_input form-check-input mt-0 ml-0"
                                               id="soa-property-USER_RULES" checked name="USER_RULES"/>
                                        <label class="bx-soa-custom-label mb-0 ml-3">
                                            Я принимаю условия
                                            <a class="color-redLight text-decoration-underline"
                                               href="/about/users_rules/">
                                                Пользовательского соглашения
                                            </a>
                                        </label>
                                    </div>
                                    <div class="d-flex flex-row align-items-center font-14 mb-4">
                                        <input type="checkbox" required checked
                                               class="check_input  form-check-input mt-0 ml-0"
                                               name="USER_POLITICS"/>
                                        <label class="bx-soa-custom-label mb-0 ml-3">
                                            Я принимаю условия
                                            <a class="color-redLight text-decoration-underline" href="/about/politics/">
                                                Политики конфиденциальности
                                            </a>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mb-3 col-md-4 col-lg-4 col-12 p-0 align-self-end">
                                    <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                           for="main-profile-name">
                                        <span class="starrequired color-redLight">* </span> Номер телефона
                                    </label>
                                    <div class="form-group">
                                        <span id="flag"></span>
                                        <div class="code position-relative">
                                            <input type="text" name="PHONE"
                                                   placeholder="+_ (___)-___-____"
                                                   inputmode="text"
                                                   data-input-type="phone"
                                                   value="<?= $arResult['USER_VALUES']['PHONE'] ?? '' ?>"
                                                   class="form-control auth-phone dark:bg-grayButton input_lk bx-auth-input"
                                                   id="<?= $mainID . "phone" ?>"
                                                   autocomplete="off"/>
                                        </div>
                                    </div>
                                    <div class="checkbox hidden">
                                        <label>
                                            <input type="checkbox" name="SAVE_SESSION" value="Y"
                                                   id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                                <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                            <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- STEP CODE WAITING -->
                        <div id="ctweb_form_step_3"
                             class="ctweb-smsauth-menu-step col-md-6 col-lg-6 col-12 p-0
                             <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? 'flex' : 'hidden' ?> ">
                            <h3 class="ctweb-title text-center font-medium mb-4 text-textLight dark:text-textDarkLightGray
                            text-lg"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                            <div class="form-group mb-3 d-flex flex-column">
                                <label class="ctweb-label mb-3" for="sms-auth-code"></label>
                                <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                       class="form-control dark:bg-grayButton auth_code" autocomplete="off">
                                <span id="result"></span>
                            </div>

                            <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                                    id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer text-sm
                                               dark:font-normal font-light"></div>
                            <input type="submit" id="submit_code" class="hidden">
                        </div>
                        <!-- ERROR STEP -->
                        <div id="ctweb_form_step_error" class="ctweb-smsauth-menu-step hidden">
                            <h3 class="ctweb-title" id="<?= $jsParams['TEMPLATE']['ERROR_TITLE'] ?>"></h3>
                            <div class="form-group">
                                <label class="ctweb-label ctweb-label-error"
                                       id="<?= $jsParams['TEMPLATE']['ERROR_TEXT'] ?>"></label>
                            </div>
                        </div>
                        <!--Навигация по форме авторизации-->
                        <div class="ctweb-button-block col-md-6 col-lg-6 col-12 p-0">
                            <input class="btn link_menu_catalog get_code_button red_button_cart pl-3 pr-3 font-16"
                                   id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>"
                                   type="submit"
                                   value="Регистрация"
                                   name="registered"
                                   onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">

                            <div style="display: none" class="ctweb-button-back">
                                <a class="ctweb-link font-14 hover:underline flex flex-row items-center text-sm dark:font-normal
                        font-medium"
                                   id="<?= $jsParams['TEMPLATE']['BACK'] ?>"><?= GetMessage("SMS_AUTH_BACK") ?></a>
                            </div>

                            <div style="display: none" class="ctweb-button-send-code-again">
                                <a class="ctweb-link font-14 hover:underline flex flex-row items-center text-sm dark:font-normal
                        font-medium"
                                   id="<?= $jsParams['TEMPLATE']['RESEND'] ?>"><?= GetMessage("SMS_AUTH_SEND_CODE_AGAIN") ?></a>
                            </div>

                            <div class="ctweb-button-new-code" id="new_code_block">
                                <a class="ctweb-link glowing-text font-14 hover:underline flex flex-row items-center text-sm dark:font-normal
                        font-medium"
                                   id="<?= $mainID . 'REUSE' ?>"><?= GetMessage('SMS_AUTH_REUSE_CODE') ?></a>
                            </div>

                            <div>
                                <a class="ctweb-link font-14 hover:underline flex flex-row items-center text-sm dark:font-normal
                        font-medium"
                                   id="<?= $jsParams['TEMPLATE']['CHANGE_PHONE'] ?>"><?= GetMessage("SMS_AUTH_CHANGE_PHONE") ?></a>
                            </div>
                            <div>
                                <a class="ctweb-link hover:underline flex flex-row items-center text-sm dark:font-normal
                        font-medium" href="/about/FAQ/"
                                   id="<?= $jsParams['TEMPLATE']['MSG_NOT_COME'] ?>"><?= GetMessage("SMS_AUTH_CODE_NOT_RESPONSE") ?></a>
                            </div>
                        </div>
                    </div>
                </form>
                <script type="text/javascript">

                    let date_now = new Date();
                    let year_now = date_now.getFullYear();
                    let date_datipicker = date_now.setFullYear(year_now - 18);

                    $('input[name="PERSONAL_BIRTHDAY"]').datepicker({
                        dateFormat: 'dd/mm/yyyy',
                        maxDate: date_now,
                        autoClose: true,
                        toggleSelected: false,
                        placeholder: "dd/mm/yyyy"
                    });

                    $("input[name='EMAIL']").inputmask("email", {
                        removeMaskOnSubmit: true,
                        clearMaskOnLostFocus: true,
                    });

                    $(".readonly").keydown(function (e) {
                        e.preventDefault();
                    });

                    $('i.block-icon-text').on('click', function () {
                        $('.block-text').toggleClass('hidden');
                    });

                    $(document).on('click', 'input.form-check-input', function () {
                        if ($('input[name="USER_RULES"]').prop('checked') === true
                            && $('input[name="USER_POLITICS"]').prop('checked') === true) {
                            $('input[name="registered"]').removeAttr('style');
                        } else {
                            $('input[name="registered"]').attr('style', 'opacity: 0.65');
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
<?php else:
    if (strripos($_SERVER['REQUEST_URI'], '/?register=yes') === false) { ?>
        <div class="ctweb-smsauth-menu-block z-50 p-8 fixed md:top-1/4 top-0 left-0 right-0 m-auto md:max-w-md
         md:w-full w-screen md:h-auto h-screen hidden md:rounded-xl dark:bg-darkBox bg-white rounded-0"
             data-id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>">
            <div class="close_login_menu absolute top-1.5 right-1.5">
                <a class="close_header_box" href="">
                    <svg width="40" height="40" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.5"
                              d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z"
                              class="dark:fill-[#464646] fill-textDarkLightGray"/>
                        <path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z"
                              class="fill-darkBox dark:fill-white"/>
                    </svg>
                </a>
            </div>
            <div class="ctweb-smsauth-box h-full flex md:block items-center justify-center">
                <?php if ($arResult['AUTH_RESULT'] === 'SUCCESS') : ?>
                    <?php if ($arResult['STEP'] === Manager::STEP_SUCCESS) : ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="error alert alert-success">
                                    <?= GetMessage("SMS_SUCCESS_AUTH"); ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="error alert alert-success">
                                    <?= GetMessage("SMS_AUTH_ALREADY_AUTH"); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div>
                        <div class="row">
                            <form id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>"
                                  class="ctweb-smsauth-menu-form"
                                  action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                                  method="POST" name="auth">
                                <?php echo bitrix_sessid_post(); ?>
                                <input type="hidden" name="FORM_ID"
                                       value="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>">
                                <input type="hidden" name="REGISTER" value="<?= $arParams['REGISTER'] ?>">
                                <input type="hidden" name="PROFILE_AUTH" value="<?= $arParams['PROFILE_AUTH'] ?>">
                                <input type="hidden" name="recaptcha_token" value="">
                                <input id="<?= $jsParams['TEMPLATE']['STATE'] ?? Manager::STEP_PHONE_WAITING ?>"
                                       type="hidden"
                                       name=""
                                       value="<?= $arResult['STEP'] ?>">

                                <!--STEP PNONE WAITING-->
                                <div id="ctweb_form_step_1"
                                     class="ctweb-smsauth-menu-step mb-4 hidden justify-center">
                                    <div class="max-w-xs">
                                        <h3 class="text-center font-medium mb-4 text-textLight dark:text-textDarkLightGray text-lg">
                                            <?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                                        <div>
                                            <div class="form-group relative mb-4">
                                                <div>
                                                    <label class="text-xs font-normal mb-2 text-textLight dark:text-textDarkLightGray"
                                                           for="smsauth-phone"><?= GetMessage("SMS_AUTH_PHONE") ?></label>
                                                    <div class="code relative mt-2">
                                                        <span class="" id="flag"></span>
                                                        <input type="text" name="PHONE" placeholder="+_ (___)-___-____"
                                                               inputmode="text"
                                                               data-input-type="phone"
                                                               value="<?= $arResult['USER_VALUES']['PHONE'] ?? '' ?>"
                                                               class="bg-textDark p-3 dark:bg-grayButton cursor-pointer
                                                                w-full text-textLight rounded-md
                                                    dark:text-white border-0 text-xl auth-phone"
                                                               id="<?= $mainID . "phone" ?>"
                                                               autocomplete="off"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="checkbox">
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="SAVE_SESSION" value="Y"
                                                           class="p-4 dark:bg-grayButton checked:hover:bg-grayButton border-0
                                                   dark:text-white cursor-pointer text-textLight font-normal rounded-full bg-textDark
                                                   checked:focus:bg-grayButton mr-2"
                                                           id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                                        <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                                    <span class="text-xs dark:font-normal font-medium text-textLight dark:text-textDarkLightGray">  <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP CODE WAITING -->
                                <div id="ctweb_form_step_3"
                                     class="ctweb-smsauth-menu-step flex-col justify-center items-center mb-3
                                     <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? 'flex' : 'hidden' ?> ">
                                    <div class="max-w-xs flex flex-col items-center justify-center">
                                        <h3 class="ctweb-title text-center font-medium mb-4 text-textLight
                                    dark:text-textDarkLightGray text-lg"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                                        <div class="form-group flex flex-col">
                                            <label class="ctweb-label font-light mb-4 text-textLight
                                        dark:text-textDarkLightGray text-xs" for="sms-auth-code"></label>
                                            <div class="flex flex-row justify-between">
                                                <label class="ctweb-label font-normal mb-2 text-textLight
                                        dark:text-white text-md"
                                                       for="sms-auth-code"><?= GetMessage("SMS_AUTH_CODE") ?></label>
                                                <a class="ctweb-link font-medium mb-2 text-textLight
                                        dark:text-white underline text-xs" id="<?= $jsParams['TEMPLATE']['CHANGE_PHONE'] ?>">
                                                    <?= GetMessage("SMS_AUTH_CHANGE_NUMBER_PHONE") ?></a>
                                            </div>
                                            <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                                   class="form-control bg-textDark p-3 mb-2 dark:bg-grayButton cursor-pointer
                                                    w-full text-textLight rounded-md
                                                    dark:text-white border-0 text-xl auth_code" autocomplete="off">
                                            <span id="result"></span>
                                        </div>

                                        <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                                                id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer text-sm
                                               dark:font-normal font-light"></div>
                                        <input type="submit" id="submit_code" class="hidden">
                                    </div>
                                </div>

                                <!-- ERROR STEP -->
                                <div id="ctweb_form_step_error" class="ctweb-smsauth-menu-step hidden">
                                    <h3 class="ctweb-title" id="<?= $jsParams['TEMPLATE']['ERROR_TITLE'] ?>"></h3>
                                    <div class="form-group">
                                        <label class="ctweb-label ctweb-label-error"
                                               id="<?= $jsParams['TEMPLATE']['ERROR_TEXT'] ?>"></label>
                                    </div>
                                </div>

                                <!--Навигация по форме авторизации-->
                                <div class="ctweb-button-block flex items-center justify-center flex-col">
                                    <input class="btn link_menu_catalog get_code_button p-3 rounded-lg w-full max-w-xs text-white
                                    cursor-pointer font-normal bg-light-red  text-lg dark:bg-dark-red mb-4"
                                           id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>"
                                           type="submit"
                                           value="<?= GetMessage("SMS_AUTH_GET_CODE") ?>"
                                           onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                                    <div class="flex flex-col w-full">
                                        <div class="ctweb-button-back hidden mb-2">
                                            <a class="ctweb-link"
                                               id="<?= $jsParams['TEMPLATE']['BACK'] ?>"><?= GetMessage("SMS_AUTH_BACK") ?></a>
                                        </div>
                                        <div class="ctweb-button-send-code-again mb-2 hidden">
                                            <a class="ctweb-link flex flex-row items-center text-sm
                                               dark:font-normal font-medium"
                                               id="<?= $jsParams['TEMPLATE']['RESEND'] ?>">
                                                <span class="mr-2.5 p-2 dark:bg-grayButton border
                                                 border-textDarkLightGray dark:border-grayButton rounded-full">
                                                    <svg width="17" height="20" viewBox="0 0 19 22" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         class="dark:fill-white fill-light-red">
                                                        <path d="M9.49743 0.666504V3.63793C4.7569 3.63793 0.893433 7.64072 0.893433 12.5522C0.893433 17.4637 4.7569 21.4665 9.49743 21.4665C14.238 21.4665 18.1014 17.4637 18.1014 12.5522C18.1014 10.6009 17.485 8.79748 16.4527 7.32707L15.0897 8.73927C15.7835 9.83224 16.1894 11.1408 16.1894 12.5522C16.1894 16.3931 13.2046 19.4855 9.49743 19.4855C5.79022 19.4855 2.80543 16.3931 2.80543 12.5522C2.80543 8.71131 5.79022 5.61888 9.49743 5.61888V8.59031L14.2774 4.62841L9.49743 0.666504Z"
                                                        />
                                                    </svg>
                                                </span>
                                                <?= GetMessage("SMS_AUTH_SEND_CODE_AGAIN") ?></a>
                                        </div>
                                        <div class="ctweb-button-new-code mb-2 flex items-center justify-center flex-col"
                                             id="new_code_block">
                                            <a class="ctweb-link glowing-text
                                            p-3 rounded-lg w-full max-w-xs text-white text-center font-normal bg-light-red
                                             text-lg dark:bg-dark-red mb-4"
                                               id="<?= $mainID . 'REUSE' ?>"><?= GetMessage('SMS_AUTH_REUSE_CODE') ?></a>
                                        </div>
                                        <div class="mb-2">
                                            <a class="ctweb-link email-login flex flex-row items-center text-sm
                                               dark:font-normal font-medium hover:underline"
                                               id="<?= $jsParams['TEMPLATE']['AUTH_EMAIL_LOGIN'] ?>">
                                                 <span class="mr-2.5 p-2 dark:bg-grayButton border
                                                 border-textDarkLightGray dark:border-grayButton rounded-full">
                                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         class="dark:fill-white fill-light-red">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                              d="M24 5.35328V20.2225L18.198 12.9707L17.0955 13.853L22.954 21.1768H1.04605L6.90454 13.853L5.80201 12.9707L0 20.2225V5.35326L11.9993 15.7588L24 5.35328ZM23.9992 2.82495V3.48279L11.9999 13.8883L0.0005625 3.4828V2.82495H23.9992Z"
                                                        />
                                                    </svg>
                                                 </span>
                                                <?= GetMessage("SMS_AUTH_EMAIL_LOGIN") ?></a>
                                        </div>

                                        <div class="mb-2">
                                            <a class="ctweb-link hover:underline flex flex-row items-center
                                              text-sm dark:font-normal font-medium" href="<? SITE_DIR ?>about/FAQ/"
                                               id="<?= $jsParams['TEMPLATE']['MSG_NOT_COME'] ?>">
                                                <span class="mr-2.5 p-2 dark:bg-grayButton border
                                                 border-textDarkLightGray dark:border-grayButton rounded-full">
                                                <svg width="17" height="17" viewBox="0 0 10 16" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg"
                                                     class="dark:stroke-white stroke-light-red">
                                                    <path d="M2.125 5.08925C2.125 1.15171 8.3125 1.15175 8.3125 5.08925C8.3125 7.90175 5.5 7.33913 5.5 10.7141"
                                                          stroke-width="2.5" stroke-linecap="round"
                                                          stroke-linejoin="round"/>
                                                    <path d="M5.5 14.2662L5.51458 14.25"
                                                          stroke-width="2.5" stroke-linecap="round"
                                                          stroke-linejoin="round"/>
                                                </svg>
                                                </span>
                                                <?= GetMessage("SMS_AUTH_CODE_NOT_RESPONSE") ?></a>
                                        </div>
                                        <div class="mb-2">
                                            <a class="ctweb-link email-login hover:underline flex flex-row items-center
                                              text-sm dark:font-normal font-medium"
                                               href="/auth/?register=yes">
                                            <span class="mr-2.5 p-2 dark:bg-grayButton border
                                                 border-textDarkLightGray dark:border-grayButton rounded-full">
                                            <svg width="16" height="17" viewBox="0 0 21 22" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 class="dark:fill-white fill-light-red">
                                                <path d="M10.4997 10.451C13.3856 10.451 15.7252 8.11148 15.7252 5.22551C15.7252 2.33954 13.3856 0 10.4997 0C7.61371 0 5.27417 2.33954 5.27417 5.22551C5.27417 8.11148 7.61371 10.451 10.4997 10.451Z"
                                                />
                                                <path d="M10.5 13.0635C4.71286 13.0635 0 16.575 0 20.9017C0 21.1944 0.254125 21.4243 0.577556 21.4243H20.4224C20.7459 21.4243 21 21.1944 21 20.9017C21 16.575 16.2871 13.0635 10.5 13.0635Z"
                                                />
                                            </svg>
                                                </span>
                                                <?= GetMessage("EMAIL_AUTH_REGISTRATION") ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- STEP AUTH EMAIL LOGIN -->
                            <form id="<?= $jsParams['TEMPLATE']['MAIL_FORM'] ?>"
                                  action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                                  method="POST"
                                  class="ctweb-smsauth-menu-step hidden mb-4 flex justify-center flex-col">
                                <?= bitrix_sessid_post(); ?>
                                <div class="mb-4 flex justify-center">
                                    <div class="max-w-xs">
                                        <h3 class="text-center font-medium mb-4 text-textLight dark:text-textDarkLightGray text-lg">
                                            <?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                                        <div class="form-group flex flex-col">
                                            <input type="hidden" name="METHOD" placeholder="" value="EMAIL_AUTH"/>
                                            <label class="text-xs font-normal text-textLight mb-2 dark:text-textDarkLightGray">
                                                <?= GetMessage("SMS_AUTH_EMAIL") ?></label>
                                            <input type="text" name="EMAIL" placeholder=""
                                                   value="<?= $arResult['USER_VALUES']['EMAIL'] ?? '' ?>"
                                                   class="form-control bg-textDark p-3 dark:bg-grayButton cursor-pointer
                                                                w-full text-textLight rounded-md
                                                    dark:text-white border-0 text-xl auth-by-email mb-4"
                                                   id="<?= $mainID . "email" ?>"/>
                                            <div class="flex flex-row justify-between items-center">
                                                <label class="text-xs dark:font-normal font-normal text-textLight
                                             dark:text-textDarkLightGray"><?= GetMessage("SMS_AUTH_PASSWORD") ?></label>
                                                <span style="float: right">
                                        <a href="/auth/?forgot_password=yes"
                                           class="ctweb-link ctweb-link-fargot font-medium text-xs text-light-red
                                           hover:underline dark:text-textDarkLightGray"
                                           idk="<?= $jsParams['TEMPLATE']['FORGOT_PASSWORD'] ?>">Забыли пароль?</a>
                                    </span>
                                            </div>
                                            <div class="relative">
                                                <input type="password" name="PASSWORD" placeholder=""
                                                       value="<?= $arResult['USER_VALUES']['PASSWORD'] ?? '' ?>"
                                                       class="auth-by-email bg-textDark p-3 dark:bg-grayButton cursor-pointer
                                                                w-full text-textLight rounded-md
                                                    dark:text-white border-0 text-xl js__show-pass mb-4"
                                                       id="<?= $mainID . "password" ?>"/>
                                                <svg width="29" height="18" viewBox="0 0 29 18" class="absolute mr-4 right-0 top-0 mt-4" onclick="showHidePasswd(this)" data-type="password" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M14.5831 5.75639C17.7625 5.75639 20.3399 8.29345 20.3399 11.4231C20.3399 14.5527 17.7625 17.0897 14.5831 17.0897C11.4036 17.0897 8.82621 14.5527 8.82621 11.4231C8.82621 8.29345 11.4036 5.75639 14.5831 5.75639ZM14.5831 0.791504C21.2229 0.791504 26.9546 5.25401 28.545 11.5077C28.6898 12.077 28.3383 12.6539 27.76 12.7964C27.1817 12.9389 26.5956 12.593 26.4508 12.0237C25.0996 6.71028 20.2267 2.9165 14.5831 2.9165C8.93693 2.9165 4.06256 6.71355 2.71367 12.0301C2.56923 12.5995 1.98327 12.9457 1.40489 12.8035C0.826511 12.6614 0.474738 12.0846 0.619181 11.5153C2.20676 5.25786 7.94035 0.791504 14.5831 0.791504Z" class="fill-light-red dark:fill-white"></path>
                                                </svg>
                                            </div>
                                            <div class="ctweb-error-alert" style="display: none"
                                                 id="<?= $jsParams['TEMPLATE']['ERROR_ALERT'] ?>">
                                                <?= GetMessage("SMS_AUTH_ERROR_EMPTY_FIELD") ?>
                                            </div>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="SAVE_SESSION" value="Y"
                                                       class="p-4 dark:bg-grayButton checked:hover:bg-grayButton border-0
                                                   cursor-pointer text-xs dark:font-normal font-medium text-textLight
                                                   dark:text-textDarkLightGray rounded-full bg-textDark
                                                   checked:focus:bg-grayButton mr-2"
                                                       id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                                    <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                                <span class="text-xs dark:font-normal font-medium text-textLight dark:text-textDarkLightGray">
                                               <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?>
                                           </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="ctweb-button-block flex items-center justify-center flex-col">
                                    <input class="btn link_menu_catalog get_code_button p-3 rounded-lg w-full max-w-xs text-white
                                    cursor-pointer font-normal bg-light-red text-lg dark:bg-dark-red mb-4"
                                           id="<?= $jsParams['TEMPLATE']['LOGIN'] ?>"
                                           type="button"
                                           value="<?= GetMessage("SMS_AUTH_LOG_IN") ?>">
                                    <div class="flex flex-col w-full">
                                        <div class="my-3">
                                            <a class="ctweb-link flex flex-row items-center text-sm
                                            dark:font-normal font-medium hover:underline"
                                               id="<?= $jsParams['TEMPLATE']['AUTH_PHONE_LOGIN'] ?>">
                                                <span class="mr-2.5 p-2 dark:bg-grayButton border
                                                 border-textDarkLightGray dark:border-grayButton rounded-full">
                                                      <svg width="17" height="16" viewBox="0 0 24 23" fill="none"
                                                           xmlns="http://www.w3.org/2000/svg"
                                                           class="dark:fill-white fill-light-red">
                                                            <path d="M0 3.19444C0 14.1327 9.01098 23 20.1266 23C20.628 23 21.1252 22.982 21.6176 22.9465C22.1826 22.9057 22.465 22.8854 22.7222 22.7397C22.9352 22.6191 23.1371 22.4052 23.2439 22.1873C23.3728 21.9244 23.3728 21.6176 23.3728 21.0041V17.4042C23.3728 16.8883 23.3728 16.6303 23.2864 16.4092C23.2104 16.2139 23.0865 16.0399 22.926 15.9027C22.7443 15.7473 22.4979 15.6592 22.0052 15.4828L17.8412 13.9928C17.2679 13.7877 16.9812 13.6851 16.7093 13.7025C16.4695 13.7178 16.2388 13.7985 16.0427 13.9352C15.8204 14.0902 15.6635 14.3475 15.3497 14.8623L14.2834 16.6111C10.8425 15.0777 8.0531 12.3292 6.49244 8.94444L8.26959 7.89516C8.79265 7.58633 9.05418 7.43191 9.21174 7.21313C9.35068 7.02021 9.43261 6.79314 9.44819 6.55717C9.46585 6.28957 9.36158 6.00751 9.15318 5.4434L7.63899 1.34577C7.45982 0.860915 7.37023 0.618483 7.21235 0.439683C7.07288 0.281737 6.89613 0.159914 6.69763 0.0849085C6.47291 1.52323e-07 6.21077 0 5.6865 0H2.02825C1.40481 0 1.09308 9.52019e-08 0.825813 0.12682C0.604446 0.231866 0.387131 0.430624 0.264502 0.640205C0.116449 0.893256 0.0957634 1.17124 0.0543936 1.72721C0.0183475 2.21165 0 2.70094 0 3.19444Z"
                                                            />
                                                      </svg>
                                                </span>
                                                <?= GetMessage("AUTH_PHONE_LOGIN") ?></a>
                                        </div>
                                        <a class="ctweb-link email-login flex flex-row items-center text-sm
                                            dark:font-normal font-medium hover:underline"
                                           href="/auth/?register=yes">
                                            <span class="mr-2.5 p-2 dark:bg-grayButton border
                                                 border-textDarkLightGray dark:border-grayButton rounded-full">
                                                <svg width="16" height="17" viewBox="0 0 21 22" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg"
                                                     class="dark:fill-white fill-light-red">
                                                    <path d="M10.4997 10.451C13.3856 10.451 15.7252 8.11148 15.7252 5.22551C15.7252 2.33954 13.3856 0 10.4997 0C7.61371 0 5.27417 2.33954 5.27417 5.22551C5.27417 8.11148 7.61371 10.451 10.4997 10.451Z"/>
                                                    <path d="M10.5 13.0635C4.71286 13.0635 0 16.575 0 20.9017C0 21.1944 0.254125 21.4243 0.577556 21.4243H20.4224C20.7459 21.4243 21 21.1944 21 20.9017C21 16.575 16.2871 13.0635 10.5 13.0635Z"/>
                                                </svg>
                                            </span>
                                            <?= GetMessage("EMAIL_AUTH_REGISTRATION") ?>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    <?php } endif; ?>
<div class="fixed z-40 h-screen w-screen dark:bg-fancyboxDark top-0 left-0 bg-fancybox hidden overlay-box"></div>
<script>

    $('input.auth-phone').phonecode({
        preferCo: 'ru',
        default_prefix: '7'
    });

    $('input.auth-phone-profile').phonecode({
        phone_val: $('input.auth-phone-profile').val(),
    });


    $('input.auth-phone').inputmask("+7 (999)-999-9999", {
        minLength: 10,
        removeMaskOnSubmit: true,
        autoUnmask: true,
        clearMaskOnLostFocus: false,
        clearMaskOnLostHover: false,
        clearIncomplete: true,
        definitionSymbol: "*"
    });


    BX.message(<?= json_encode(array(
        'SMS_AUTH_TIME_LEFT' => GetMessage('SMS_AUTH_TIME_LEFT'),
        'SMS_AUTH_TIME_EXPIRED' => GetMessage('SMS_AUTH_TIME_OUT'),
        'SMS_AUTH_ERROR_CODE_NOT_CORRECT_TITLE' => GetMessage('SMS_AUTH_ERROR_CODE_NOT_CORRECT_TITLE'),
        'SMS_AUTH_ERROR_CODE_NOT_CORRECT_TEXT' => GetMessage('SMS_AUTH_ERROR_CODE_NOT_CORRECT_TEXT'),
        'SMS_AUTH_ERROR_PHONE_EXISTS_TITLE' => GetMessage('SMS_AUTH_ERROR_PHONE_EXISTS_TITLE'),
        'SMS_AUTH_ERROR_PHONE_EXISTS_TEXT' => GetMessage('SMS_AUTH_ERROR_PHONE_EXISTS_TEXT'),
        'SMS_AUTH_ERROR_TIME_EXPIRED_TITLE' => GetMessage('SMS_AUTH_ERROR_TIME_EXPIRED_TITLE'),
        'SMS_AUTH_ERROR_TIME_EXPIRED_TEXT' => GetMessage('SMS_AUTH_ERROR_TIME_EXPIRED_TEXT'),
        'SMS_AUTH_ERROR_CAPTCHA_WRONG' => GetMessage('SMS_AUTH_ERROR_CAPTCHA_WRONG'),
        'SMS_AUTH_ERROR_CAPTCHA_WRONG_TITLE' => GetMessage('SMS_AUTH_ERROR_CAPTCHA_WRONG_TITLE'),
        'SMS_AUTH_ERROR_CAPTCHA_WRONG_TEXT' => GetMessage('SMS_AUTH_ERROR_CAPTCHA_WRONG_TEXT'),
        'SMS_AUTH_CHANGE_PHONE' => GetMessage('SMS_AUTH_CHANGE_PHONE'),
        'ERROR_ALERT_NOT_CORRECT' => GetMessage("SMS_AUTH_ERROR_EMAIL_OR_PASS_NOT_CORRECT"),
        'ERROR_ALERT_EMPTY_FIELD' => GetMessage("SMS_AUTH_ERROR_EMPTY_FIELD"),
        'ERROR_NOT_CORRECT_PASSWORD' => GetMessage("ERROR_NOT_CORRECT_PASSWORD"),
    ))?>);

    BX(function () {
        new BX.Ctweb.SMSAuth.Controller(<?= json_encode($jsParams) ?>);
    });
</script>
