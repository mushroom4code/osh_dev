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

if ($arParams['PROFILE_AUTH'] == "Y"):
?>
    <div style="display: block" class="ctweb-smsauth-menu-block profile col-12 col-md-7">
        <div class="ctweb-smsauth-box profile">
            <div>
                <div class="row">
                    <form id="<?= $mainID ?>" class="ctweb-smsauth-menu-form profile"
                          action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                          method="POST" name="auth">
                        <?php echo bitrix_sessid_post(); ?>
                        <input type="hidden" name="FORM_ID" value="<?= $arResult['FORM_ID'] ?>">
                        <input type="hidden" name="PROFILE_AUTH" value="<?= $arParams['PROFILE_AUTH']?>">
                        <input type="hidden" name="recaptcha_token" value="">
                        <input id="<?= $jsParams['TEMPLATE']['STATE'] ?? Manager::STEP_PHONE_WAITING ?>" type="hidden"
                               name=""
                               value="<?= $arResult['STEP'] ?>">
                        <?php if ($arResult['AUTH_RESULT'] === 'SUCCESS') : ?>
                            <? ShowNote(GetMessage('SMS_PHONE_SAVED')); ?>
                        <?php elseif ($arResult['AUTH_RESULT'] === 'FAILED'): ?>
                            <? ShowError($arResult["strProfileError"]); ?>
                        <?php endif;?>
                        <h5 class="mt-2 mb-4"><b>Изменение номера телефона</b></h5>

                        <!--STEP PHONE WAITING-->
                        <div id="ctweb_form_step_1"
                             class="ctweb-smsauth-menu-step d-none">
                            <p class="form-group mb-2 col-sm-12 col-md-12 profile-asterisk">* Будет выслан код подтверждения</p>
                            <div class="form-group mb-2">
                                <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                                       for="smsauth-phone"><?= GetMessage("SMS_AUTH_PHONE") ?></label>
                                <span id="flag"></span>
                                <div class="col-sm-12 col-md-12 code position-relative">
                                    <input type="text" name="PHONE"
                                           data-input-type="phone"
                                           placeholder="+_ (___)-___-____"
                                           inputmode="text"
                                           value="<?= $arParams['USER_PHONE'] ?? '' ?>"
                                           class="form-control profile input_lk auth-phone-profile"
                                           id="<?= $mainID . "phone" ?>"
                                           autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                        <div class="checkbox d-none">
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
                     class="profile ctweb-smsauth-menu-step <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? '' : 'd-none' ?> ">
                    <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                    <div class="form-group mb-2">
                        <label style="margin-bottom: 10px" class="ctweb-label" for="sms-auth-code"></label>
                        <div style="display: none">
                            <a class="ctweb-link"><?= GetMessage("SMS_AUTH_CHANGE_NUMBER_PHONE") ?></a>
                        </div>
                        <label style="display: none" class="ctweb-label"
                               for="sms-auth-code"><?= GetMessage("SMS_AUTH_CODE") ?></label>
                        <div class="col-sm-12 col-md-12">
                            <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                   class="form-control input_lk profile auth_code" autocomplete="off">
                        </div>
                        <span id="result"></span>
                    </div>

                    <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                            id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer"></div>
                    <input type="submit" id="submit_code" class="d-none">
                </div>

                <!-- ERROR STEP -->
                <div id="ctweb_form_step_error" class="ctweb-smsauth-menu-step d-none">
                    <h3 class="ctweb-title" id="<?= $jsParams['TEMPLATE']['ERROR_TITLE'] ?>"></h3>
                    <div class="form-group">
                        <label class="ctweb-label ctweb-label-error"
                               id="<?= $jsParams['TEMPLATE']['ERROR_TEXT'] ?>"></label>
                    </div>
                </div>

                <!--Навигация по форме авторизации-->
                <div class="ctweb-button-block profile">
                    <input class="btn link_menu_catalog get_code_button profile"
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
                <!-- STEP AUTH EMAIL LOGIN -->
                <form id="<?= $jsParams['TEMPLATE']['MAIL_FORM'] ?>"
                      action="/bitrix/components/ctweb/sms.authorize_profile/ajax.php"
                      method="POST" class="ctweb-smsauth-menu-step d-none">
                    <?= bitrix_sessid_post(); ?>
                    <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                    <div class="form-group">
                        <input type="hidden" name="METHOD" placeholder="" value="EMAIL_AUTH"/>
                        <label class="ctweb-label"><?= GetMessage("SMS_AUTH_EMAIL") ?></label>
                        <input type="text" name="EMAIL" placeholder=""
                               value="<?= $arResult['USER_VALUES']['EMAIL'] ?? '' ?>"
                               class="form-control auth-by-email"
                               id="<?= $mainID . "email" ?>"/>
                        <label class="ctweb-label"><?= GetMessage("SMS_AUTH_PASSWORD") ?></label>
                        <span style="float: right"><a href="/auth/?forgot_password=yes" class="ctweb-link ctweb-link-fargot"
                                                      idk="<?= $jsParams['TEMPLATE']['FORGOT_PASSWORD'] ?>">Забыли пароль?</a></span>
                        <input type="password" name="PASSWORD" placeholder=""
                               value="<?= $arResult['USER_VALUES']['PASSWORD'] ?? '' ?>"
                               class="form-control auth-by-email"
                               id="<?= $mainID . "password" ?>"/>
                        <div class="ctweb-error-alert" style="display: none"
                             id="<?= $jsParams['TEMPLATE']['ERROR_ALERT'] ?>">
                            <?= GetMessage("SMS_AUTH_ERROR_EMPTY_FIELD") ?>
                        </div>
                    </div>
                    <div class="ctweb-button-block">
                        <input class="btn link_menu_catalog login_button"
                               id="<?= $jsParams['TEMPLATE']['LOGIN'] ?>"
                               type="button"
                               value="<?= GetMessage("SMS_AUTH_LOG_IN") ?>">
                        <div>
                            <a class="ctweb-link"
                               id="<?= $jsParams['TEMPLATE']['AUTH_PHONE_LOGIN'] ?>">
                                <?= GetMessage("AUTH_PHONE_LOGIN") ?></a>
                        </div>
                    </div>
                </form>
                <div class="ctweb-button-block">
                    <div>
                        <a class="ctweb-link email-register"
                            id="<?= $jsParams['TEMPLATE']['REGISTRATION'] ?>"
                            href="/login/?register=yes"><?= GetMessage("EMAIL_AUTH_REGISTRATION") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?else:?>
    <div style="display: none" class="ctweb-smsauth-menu-block radius_10 position-absolute">
        <div class="close_login_menu">
            <a class="close_header_box" href="">
                <span class="login_span_bar login_span"></span>
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
                        <form id="<?= $mainID ?>" class="ctweb-smsauth-menu-form"
                              action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                              method="POST" name="auth">
                            <?php echo bitrix_sessid_post(); ?>
                            <input type="hidden" name="FORM_ID" value="<?= $arResult['FORM_ID'] ?>">
                            <input type="hidden" name="PROFILE_AUTH" value="<?= $arParams['PROFILE_AUTH']?>">
                            <input type="hidden" name="recaptcha_token" value="">
                            <input id="<?= $jsParams['TEMPLATE']['STATE'] ?? Manager::STEP_PHONE_WAITING ?>" type="hidden"
                                   name=""
                                   value="<?= $arResult['STEP'] ?>">

                            <!--STEP PNONE WAITING-->
                            <div id="ctweb_form_step_1"
                                 class="ctweb-smsauth-menu-step d-none">
                                <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                                <div class="form-group">
                                    <label class="ctweb-label"
                                           for="smsauth-phone"><?= GetMessage("SMS_AUTH_PHONE") ?></label>
                                    <span id="flag"></span>
                                    <div class="code position-relative">
                                        <input type="text" name="PHONE" placeholder="+_ (___)-___-____" inputmode="text"
                                               data-input-type="phone"
                                               value="<?= $arResult['USER_VALUES']['PHONE'] ?? '' ?>"
                                               class="form-control custom_style_auth auth-phone" id="<?= $mainID . "phone" ?>"
                                               autocomplete="off"/>
                                    </div>
                                </div>
                                <div class="checkbox d-none">
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
                                 class="ctweb-smsauth-menu-step <?= ($arResult['STEP'] === Manager::STEP_CODE_WAITING) ? '' : 'd-none' ?> ">
                                <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></h3>

                                <div class="form-group">
                                    <label class="ctweb-label" for="sms-auth-code"></label>
                                    <div>
                                        <a class="ctweb-link"><?= GetMessage("SMS_AUTH_CHANGE_NUMBER_PHONE") ?></a>
                                    </div>
                                    <label class="ctweb-label"
                                           for="sms-auth-code"><?= GetMessage("SMS_AUTH_CODE") ?></label>
                                    <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                           class="form-control auth_code" autocomplete="off">
                                    <span id="result"></span>
                                </div>

                                <div <?= $arResult['REUSE_TIME'] <= 0 ? 'style="display: none"' : 0 ?>
                                        id="<?= $jsParams['TEMPLATE']['TIMER'] ?>" class="ctweb-timer"></div>
                                <input type="submit" id="submit_code" class="d-none">
                            </div>

                            <!-- ERROR STEP -->
                            <div id="ctweb_form_step_error" class="ctweb-smsauth-menu-step d-none">
                                <h3 class="ctweb-title" id="<?= $jsParams['TEMPLATE']['ERROR_TITLE'] ?>"></h3>
                                <div class="form-group">
                                    <label class="ctweb-label ctweb-label-error"
                                           id="<?= $jsParams['TEMPLATE']['ERROR_TEXT'] ?>"></label>
                                </div>
                            </div>

                            <!--Навигация по форме авторизации-->
                            <div class="ctweb-button-block">
                                <input class="btn link_menu_catalog get_code_button"
                                       id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>"
                                       type="submit"
                                       value="<?= GetMessage("SMS_AUTH_GET_CODE") ?>"
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

                                <div class="mt-2">
                                    <a class="ctweb-link email-login"
                                       id="<?= $jsParams['TEMPLATE']['AUTH_EMAIL_LOGIN'] ?>"><?= GetMessage("SMS_AUTH_EMAIL_LOGIN") ?></a>
                                </div>

                                <div>
                                    <a class="ctweb-link" href="<? SITE_DIR ?>about/FAQ/"
                                       id="<?= $jsParams['TEMPLATE']['MSG_NOT_COME'] ?>"><?= GetMessage("SMS_AUTH_CODE_NOT_RESPONSE") ?></a>
                                </div>
                                <div>
                                    <a class="ctweb-link email-login" href="/auth/?register=yes"><?= GetMessage("EMAIL_AUTH_REGISTRATION") ?></a>
                                </div>
                            </div>
                        </form>
                        <!-- STEP AUTH EMAIL LOGIN -->
                        <form id="<?= $jsParams['TEMPLATE']['MAIL_FORM'] ?>"
                              action="/bitrix/components/ctweb/sms.authorize/ajax.php"
                              method="POST" class="ctweb-smsauth-menu-step d-none">
                            <?= bitrix_sessid_post(); ?>
                            <h3 class="ctweb-title"><?= GetMessage("SMS_AUTH_OR_REGISTER_TITLE") ?></h3>
                            <div class="form-group">
                                <input type="hidden" name="METHOD" placeholder="" value="EMAIL_AUTH"/>
                                <label class="ctweb-label"><?= GetMessage("SMS_AUTH_EMAIL") ?></label>
                                <input type="text" name="EMAIL" placeholder=""
                                       value="<?= $arResult['USER_VALUES']['EMAIL'] ?? '' ?>"
                                       class="form-control auth-by-email"
                                       id="<?= $mainID . "email" ?>"/>
                                <label class="ctweb-label"><?= GetMessage("SMS_AUTH_PASSWORD") ?></label>
                                <span style="float: right"><a href="/auth/?forgot_password=yes" class="ctweb-link ctweb-link-fargot"
                                                              idk="<?= $jsParams['TEMPLATE']['FORGOT_PASSWORD'] ?>">Забыли пароль?</a></span>
                                <input type="password" name="PASSWORD" placeholder=""
                                       value="<?= $arResult['USER_VALUES']['PASSWORD'] ?? '' ?>"
                                       class="form-control auth-by-email"
                                       id="<?= $mainID . "password" ?>"/>
                                <div class="ctweb-error-alert" style="display: none"
                                     id="<?= $jsParams['TEMPLATE']['ERROR_ALERT'] ?>">
                                    <?= GetMessage("SMS_AUTH_ERROR_EMPTY_FIELD") ?>
                                </div>
                            </div>
                            <div class="ctweb-button-block">
                                <input class="btn link_menu_catalog login_button"
                                       id="<?= $jsParams['TEMPLATE']['LOGIN'] ?>"
                                       type="button"
                                       value="<?= GetMessage("SMS_AUTH_LOG_IN") ?>">
                                <div class="mt-2">
                                    <a class="ctweb-link"
                                       id="<?= $jsParams['TEMPLATE']['AUTH_PHONE_LOGIN'] ?>">
                                        <?= GetMessage("AUTH_PHONE_LOGIN") ?></a>
                                </div>
                                <div>
                                    <a class="ctweb-link email-login" href="/auth/?register=yes"><?= GetMessage("EMAIL_AUTH_REGISTRATION") ?></a>
                                </div>
                            </div>
                        </form>
                        <div class="ctweb-button-block">
                            <div><a class="ctweb-link email-register"
                                    id="<?= $jsParams['TEMPLATE']['REGISTRATION'] ?>"
                                    href="/login/?register=yes"><?= GetMessage("EMAIL_AUTH_REGISTRATION") ?></a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
<?endif;?>

<script>

    $('input.auth-phone').phonecode({
        preferCo: 'ru'
    });

    $('input.auth-phone-profile').phonecode({
        phone_val :$('input.auth-phone-profile').val(),
    });


    $('input.auth-phone').inputmask("+7 (999)-999-9999", {
        minLength: 10,
        removeMaskOnSubmit: true,
        clearMaskOnLostFocus: true,
        clearMaskOnLostHover: true,
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
    ))?>);

    BX(function () {
        new BX.Ctweb.SMSAuth.Controller(<?= json_encode($jsParams) ?>);
    });
</script>
