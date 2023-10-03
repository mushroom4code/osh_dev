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
    <div style="display: block" class="ctweb-smsauth-menu-block profile mb-8"
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
                            <span class="mr-4 font-semibold">Изменение номера телефона</span>
                            <svg width="21" height="20" viewBox="0 0 24 23" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 3.19444C0 14.1327 9.01098 23 20.1266 23C20.628 23 21.1252 22.982 21.6176 22.9465C22.1826 22.9057 22.465 22.8854 22.7222 22.7397C22.9352 22.6191 23.1371 22.4052 23.2439 22.1873C23.3728 21.9244 23.3728 21.6176 23.3728 21.0041V17.4042C23.3728 16.8883 23.3728 16.6303 23.2864 16.4092C23.2104 16.2139 23.0865 16.0399 22.926 15.9027C22.7443 15.7473 22.4979 15.6592 22.0052 15.4828L17.8412 13.9928C17.2679 13.7877 16.9812 13.6851 16.7093 13.7025C16.4695 13.7178 16.2388 13.7985 16.0427 13.9352C15.8204 14.0902 15.6635 14.3475 15.3497 14.8623L14.2834 16.6111C10.8425 15.0777 8.0531 12.3292 6.49244 8.94444L8.26959 7.89516C8.79265 7.58633 9.05418 7.43191 9.21174 7.21313C9.35068 7.02021 9.43261 6.79314 9.44819 6.55717C9.46585 6.28957 9.36158 6.00751 9.15318 5.4434L7.63899 1.34577C7.45982 0.860915 7.37023 0.618483 7.21235 0.439683C7.07288 0.281737 6.89613 0.159914 6.69763 0.0849085C6.47291 1.52323e-07 6.21077 0 5.6865 0H2.02825C1.40481 0 1.09308 9.52019e-08 0.825813 0.12682C0.604446 0.231866 0.387131 0.430624 0.264502 0.640205C0.116449 0.893256 0.0957634 1.17124 0.0543936 1.72721C0.0183475 2.21165 0 2.70094 0 3.19444Z"
                                      fill="#E8E8E8"/>
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
                     class="profile ctweb-smsauth-menu-step <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? '' : 'hidden' ?> ">
                    <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                    <div class="form-group flex flex-col mb-2">
                        <label style="margin-bottom: 10px" class="ctweb-label" for="sms-auth-code"></label>
                        <div style="display: none">
                            <a class="ctweb-link"><?= GetMessage("SMS_AUTH_CHANGE_NUMBER_PHONE") ?></a>
                        </div>
                        <label style="display: none" class="ctweb-label"
                               for="sms-auth-code"><?= GetMessage("SMS_AUTH_CODE") ?></label>
                        <div class="col-sm-12 col-md-12">
                            <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                   class="form-control dark:bg-grayButton input_lk profile auth_code"
                                   autocomplete="off">
                        </div>
                        <span id="result"></span>
                    </div>

                    <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                            id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer"></div>
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
                <div class="ctweb-button-block profile mt-6">
                    <input class="btn btn dark:bg-dark-red rounded-md bg-light-red text-white xs:px-7 py-3
                             dark:shadow-md shadow-shadowDark font-light dark:hover:bg-hoverRedDark cursor-pointer
                              sm:w-72 px-5 w-56 get_code_button profile xs:text-md text-sm sm:font-normal"
                           id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>"
                           type="submit"
                           value="Сохранить"
                           onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">

                    <div style="display: none" class="ctweb-button-back">
                        <a class="ctweb-link"
                           id="<?= $jsParams['TEMPLATE']['BACK'] ?>"><?= GetMessage("SMS_AUTH_BACK") ?></a>
                    </div>

                    <div style="display: none" class="ctweb-button-send-code-again">
                        <a class="ctweb-link"
                           id="<?= $jsParams['TEMPLATE']['RESEND'] ?>"><?= GetMessage("SMS_AUTH_SEND_CODE_AGAIN") ?></a>
                    </div>

                    <div class="ctweb-button-new-code" id="new_code_block">
                        <a class="ctweb-link glowing-text"
                           id="<?= $mainID . 'REUSE' ?>"><?= GetMessage('SMS_AUTH_REUSE_CODE') ?></a>
                    </div>

                    <div>
                        <a class="ctweb-link"
                           id="<?= $jsParams['TEMPLATE']['CHANGE_PHONE'] ?>"><?= GetMessage("SMS_AUTH_CHANGE_PHONE") ?></a>
                    </div>


                    <div style="display:none">
                        <a class="ctweb-link" href="<? SITE_DIR ?>about/FAQ/"
                           id="<?= $jsParams['TEMPLATE']['MSG_NOT_COME'] ?>"><?= GetMessage("SMS_AUTH_CODE_NOT_RESPONSE") ?></a>
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
                             <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? '' : 'hidden' ?> ">
                            <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                            <div class="form-group mb-3 d-flex flex-column">
                                <label class="ctweb-label mb-3" for="sms-auth-code"></label>
                                <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                       class="form-control dark:bg-grayButton auth_code" autocomplete="off">
                                <span id="result"></span>
                            </div>

                            <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                                    id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer"></div>
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
                                <a class="ctweb-link font-14"
                                   id="<?= $jsParams['TEMPLATE']['BACK'] ?>"><?= GetMessage("SMS_AUTH_BACK") ?></a>
                            </div>

                            <div style="display: none" class="ctweb-button-send-code-again">
                                <a class="ctweb-link font-14"
                                   id="<?= $jsParams['TEMPLATE']['RESEND'] ?>"><?= GetMessage("SMS_AUTH_SEND_CODE_AGAIN") ?></a>
                            </div>

                            <div class="ctweb-button-new-code" id="new_code_block">
                                <a class="ctweb-link glowing-text font-14"
                                   id="<?= $mainID . 'REUSE' ?>"><?= GetMessage('SMS_AUTH_REUSE_CODE') ?></a>
                            </div>

                            <div>
                                <a class="ctweb-link font-14"
                                   id="<?= $jsParams['TEMPLATE']['CHANGE_PHONE'] ?>"><?= GetMessage("SMS_AUTH_CHANGE_PHONE") ?></a>
                            </div>
                            <div>
                                <a class="ctweb-link font-14" href="/about/FAQ/"
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
        <div class="ctweb-smsauth-menu-block z-50 p-8 fixed top-1/3 left-0 right-0 m-auto max-w-md w-full hidden rounded-xl dark:bg-darkBox bg-white rounded-lx"
             data-id="<?= $jsParams['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'] ?>">
            <div class="close_login_menu absolute top-1.5 right-1.5">
                <a class="close_header_box" href="">
                    <svg width="40" height="40" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.5"
                              d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z"
                              fill="#464646"/>
                        <path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z"
                              fill="white"/>
                    </svg>
                </a>
            </div>
            <div class="ctweb-smsauth-box">
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
                                     class="ctweb-smsauth-menu-step hidden mb-4 flex justify-center">
                                    <div class=" max-w-xs">
                                        <h3 class="text-center font-medium mb-4 text-textLight dark:text-textDarkLightGray text-lg">
                                            <?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                                        <div>
                                            <div class="form-group relative mb-4">
                                                <div>
                                                    <label class="text-xs font-normal text-textLight dark:text-textDarkLightGray"
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
                                                           class="p-5 dark:bg-grayButton checked:hover:bg-grayButton border-0
                                                   dark:text-white cursor-pointer text-textLight font-normal rounded-full bg-textDark
                                                   checked:focus:bg-grayButton mr-2"
                                                           id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                                        <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                                    <span class="text-xs font-normal text-textLight dark:text-textDarkLightGray">  <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP CODE WAITING -->
                                <div id="ctweb_form_step_3"
                                     class="ctweb-smsauth-menu-step <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? '' : 'hidden' ?> ">
                                    <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                                    <div class="form-group flex flex-col">
                                        <label class="ctweb-label" for="sms-auth-code"></label>
                                        <div>
                                            <a class="ctweb-link"><?= GetMessage("SMS_AUTH_CHANGE_NUMBER_PHONE") ?></a>
                                        </div>
                                        <label class="ctweb-label"
                                               for="sms-auth-code"><?= GetMessage("SMS_AUTH_CODE") ?></label>
                                        <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                               class="form-control dark:bg-grayButton auth_code" autocomplete="off">
                                        <span id="result"></span>
                                    </div>

                                    <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                                            id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer"></div>
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
                                <div class="ctweb-button-block flex items-center justify-center flex-col">
                                    <input class="btn link_menu_catalog get_code_button p-3 rounded-lg w-full max-w-xs dark:text-white
                                    cursor-pointer text-textLight font-normal text-lg dark:bg-dark-red mb-4"
                                           id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>"
                                           type="submit"
                                           value="<?= GetMessage("SMS_AUTH_GET_CODE") ?>"
                                           onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                                    <div class="flex flex-col w-full">
                                        <div class="ctweb-button-back hidden mb-2">
                                            <a class="ctweb-link"
                                               id="<?= $jsParams['TEMPLATE']['BACK'] ?>"><?= GetMessage("SMS_AUTH_BACK") ?></a>
                                        </div>

                                        <div class="ctweb-button-send-code-again mb-2">
                                            <a class="ctweb-link"
                                               id="<?= $jsParams['TEMPLATE']['RESEND'] ?>"><?= GetMessage("SMS_AUTH_SEND_CODE_AGAIN") ?></a>
                                        </div>

                                        <div class="ctweb-button-new-code mb-2" id="new_code_block">
                                            <a class="ctweb-link glowing-text"
                                               id="<?= $mainID . 'REUSE' ?>"><?= GetMessage('SMS_AUTH_REUSE_CODE') ?></a>
                                        </div>

                                        <div class="mb-2">
                                            <a class="ctweb-link"
                                               id="<?= $jsParams['TEMPLATE']['CHANGE_PHONE'] ?>"><?= GetMessage("SMS_AUTH_CHANGE_PHONE") ?></a>
                                        </div>

                                        <div class="mb-2">
                                            <a class="ctweb-link email-login flex flex-row items-center text-sm"
                                               id="<?= $jsParams['TEMPLATE']['AUTH_EMAIL_LOGIN'] ?>">
                                                 <span class="mr-2.5 p-2 dark:bg-grayButton rounded-full">
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                          d="M24 5.35328V20.2225L18.198 12.9707L17.0955 13.853L22.954 21.1768H1.04605L6.90454 13.853L5.80201 12.9707L0 20.2225V5.35326L11.9993 15.7588L24 5.35328ZM23.9992 2.82495V3.48279L11.9999 13.8883L0.0005625 3.4828V2.82495H23.9992Z"
                                                          fill="white"/>
                                                </svg>
                                                 </span>
                                                <?= GetMessage("SMS_AUTH_EMAIL_LOGIN") ?></a>
                                        </div>

                                        <div class="mb-2">
                                            <a class="ctweb-link" href="<? SITE_DIR ?>about/FAQ/"
                                               id="<?= $jsParams['TEMPLATE']['MSG_NOT_COME'] ?>"><?= GetMessage("SMS_AUTH_CODE_NOT_RESPONSE") ?></a>
                                        </div>
                                        <div class="mb-2">
                                            <a class="ctweb-link email-login flex flex-row items-center text-sm"
                                               href="/auth/?register=yes">
                                            <span class="mr-2.5 p-2 dark:bg-grayButton rounded-full">
                                            <svg width="16" height="17" viewBox="0 0 21 22" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10.4997 10.451C13.3856 10.451 15.7252 8.11148 15.7252 5.22551C15.7252 2.33954 13.3856 0 10.4997 0C7.61371 0 5.27417 2.33954 5.27417 5.22551C5.27417 8.11148 7.61371 10.451 10.4997 10.451Z"
                                                      fill="white"/>
                                                <path d="M10.5 13.0635C4.71286 13.0635 0 16.575 0 20.9017C0 21.1944 0.254125 21.4243 0.577556 21.4243H20.4224C20.7459 21.4243 21 21.1944 21 20.9017C21 16.575 16.2871 13.0635 10.5 13.0635Z"
                                                      fill="white"/>
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
                                  method="POST" class="ctweb-smsauth-menu-step hidden mb-4 flex justify-center">
                                <?= bitrix_sessid_post(); ?>
                                <div class="max-w-xs">
                                    <h3 class="text-center font-medium mb-4 text-textLight dark:text-textDarkLightGray text-lg">
                                        <?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                                    <div class="form-group flex flex-col">
                                        <input type="hidden" name="METHOD" placeholder="" value="EMAIL_AUTH"/>
                                        <label class="text-xs font-normal text-textLight dark:text-textDarkLightGray"><?= GetMessage("SMS_AUTH_EMAIL") ?></label>
                                        <input type="text" name="EMAIL" placeholder=""
                                               value="<?= $arResult['USER_VALUES']['EMAIL'] ?? '' ?>"
                                               class="form-control bg-textDark p-3 dark:bg-grayButton cursor-pointer
                                                                w-full text-textLight rounded-md
                                                    dark:text-white border-0 text-xl auth-by-email mb-4"
                                               id="<?= $mainID . "email" ?>"/>
                                        <div class="flex flex-row justify-between">
                                            <label class="text-xs font-normal text-textLight dark:text-textDarkLightGray"><?= GetMessage("SMS_AUTH_PASSWORD") ?></label>
                                            <span style="float: right">
                                        <a href="/auth/?forgot_password=yes"
                                           class="ctweb-link ctweb-link-fargot font-normal text-textLight dark:text-textDarkLightGray"
                                           idk="<?= $jsParams['TEMPLATE']['FORGOT_PASSWORD'] ?>">Забыли пароль?</a>
                                    </span>
                                        </div>
                                        <input type="password" name="PASSWORD" placeholder=""
                                               value="<?= $arResult['USER_VALUES']['PASSWORD'] ?? '' ?>"
                                               class="auth-by-email bg-textDark p-3 dark:bg-grayButton cursor-pointer
                                                                w-full text-textLight rounded-md
                                                    dark:text-white border-0 text-xl js__show-pass mb-4"
                                               id="<?= $mainID . "password" ?>"/>
                                        <div class="ctweb-error-alert" style="display: none"
                                             id="<?= $jsParams['TEMPLATE']['ERROR_ALERT'] ?>">
                                            <?= GetMessage("SMS_AUTH_ERROR_EMPTY_FIELD") ?>
                                        </div>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="SAVE_SESSION" value="Y"
                                                   class="p-5 dark:bg-grayButton checked:hover:bg-grayButton border-0
                                                   dark:text-white cursor-pointer text-textLight font-normal rounded-full bg-textDark
                                                   checked:focus:bg-grayButton mr-2"
                                                   id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                                <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                            <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?>
                                        </label>
                                    </div>
                                    <div class="ctweb-button-block">
                                        <input class="btn link_menu_catalog login_button p-3 rounded-lg w-full max-w-xs dark:text-white
                                    cursor-pointer text-textLight font-normal text-lg dark:bg-dark-red mb-4"
                                               id="<?= $jsParams['TEMPLATE']['LOGIN'] ?>"
                                               type="button"
                                               value="<?= GetMessage("SMS_AUTH_LOG_IN") ?>">
                                        <div class="mt-2">
                                            <a class="ctweb-link"
                                               id="<?= $jsParams['TEMPLATE']['AUTH_PHONE_LOGIN'] ?>">
                                                <?= GetMessage("AUTH_PHONE_LOGIN") ?></a>
                                        </div>
                                        <div>
                                            <a class="ctweb-link email-login"
                                               href="/auth/?register=yes"><?= GetMessage("EMAIL_AUTH_REGISTRATION") ?></a>
                                        </div>
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
