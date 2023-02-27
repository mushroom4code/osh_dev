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

if ($arResult["SHOW_SMS_FIELD"] == true) {
    CJSCore::Init('phone_auth');
}
?>
<div class="bx-auth mb-5">
    <?php
    ShowMessage($arParams["~AUTH_RESULT"]);
    if ($arResult["SHOW_EMAIL_SENT_CONFIRMATION"]): ?>
        <p><?php echo GetMessage("AUTH_EMAIL_SENT") ?></p>
    <?php endif;
    if (!$arResult["SHOW_EMAIL_SENT_CONFIRMATION"] && $arResult["USE_EMAIL_CONFIRMATION"] === "Y"): ?>
        <p><?php echo GetMessage("AUTH_EMAIL_WILL_BE_SENT") ?></p>
    <?php endif ?>
    <noindex>
        <?php if ($arResult["SHOW_SMS_FIELD"] == true): ?>
            <form method="post" action="<?= $arResult["AUTH_URL"] ?>" name="regform">
                <input type="hidden" name="SIGNED_DATA" value="<?= htmlspecialcharsbx($arResult["SIGNED_DATA"]) ?>"/>
                <table class="data-table bx-registration-table">
                    <tbody>
                    <tr>
                        <td>
                            <span class="starrequired  color-redLight">*</span><?php echo GetMessage("main_register_sms_code") ?>
                        </td>
                        <td><input size="30" type="text" name="SMS_CODE"
                                   value="<?= htmlspecialcharsbx($arResult["SMS_CODE"]) ?>" autocomplete="off"/></td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <script>
                new BX.PhoneAuth({
                    containerId: 'bx_register_resend',
                    errorContainerId: 'bx_register_error',
                    interval: <?=$arResult["PHONE_CODE_RESEND_INTERVAL"]?>,
                    data:
                        <?=CUtil::PhpToJSObject([
                            'signedData' => $arResult["SIGNED_DATA"],
                        ])?>,
                    onError:
                        function (response) {
                            var errorDiv = BX('bx_register_error');
                            var errorNode = BX.findChildByClassName(errorDiv, 'errortext');
                            errorNode.innerHTML = '';
                            for (var i = 0; i < response.errors.length; i++) {
                                errorNode.innerHTML = errorNode.innerHTML + BX.util.htmlspecialchars(response.errors[i].message) + '<br>';
                            }
                            errorDiv.style.display = '';
                        }
                });
            </script>

            <div id="bx_register_error" style="display:none"><?php ShowError("error") ?></div>

            <div id="bx_register_resend"></div>

        <?php elseif (!$arResult["SHOW_EMAIL_SENT_CONFIRMATION"]): ?>

            <form method="post" action="<?= $arResult["AUTH_URL"] ?>" name="bform" enctype="multipart/form-data">
                <input type="hidden" name="recaptcha_token" id="recaptchaResponse">
                <?php echo bitrix_sessid_post(); ?>
                <input type="hidden" name="AUTH_FORM" value="Y"/>
                <input type="hidden" name="TYPE" value="REGISTRATION"/>
                <h5 class="mb-3"><?= GetMessage("AUTH_REGISTER") ?></h5>
                <div class="col-11 col-md-7 p-0 mb-2">
                    <p class="message_for_user_minzdrav font-14 mb-4">
                        Розничная дистанционная продажа (доставка) кальянов, табачной, никотинсодержащей продукции на
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
                        <div class="form-group mb-3 col-md-6 col-lg-6 col-12 pl-lg-0 pl-md-0 pl-0 pr-lg-3 pr-md-3 pr-0">
                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2 p-0 mb-2"
                                   for="main-profile-name"><?= GetMessage("AUTH_NAME") ?></label>
                            <input type="text" name="USER_NAME" maxlength="50"
                                   class="form-control input_lk bx-auth-input"
                                   value="<?= $arResult["USER_NAME"] ?>"/>
                        </div>
                        <div class="form-group mb-3 col-md-6 col-lg-6 col-12 p-0">
                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                   for="main-profile-name"><?= GetMessage("AUTH_LAST_NAME") ?></label>
                            <input type="text" name="USER_LAST_NAME" maxlength="50"
                                   class="form-control input_lk bx-auth-input"
                                   value="<?= $arResult["USER_LAST_NAME"] ?>"/>
                        </div>
                    </div>
                    <?php if ($arResult["EMAIL_REGISTRATION"]): ?>
                        <div class="form-group mb-3">
                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                   for="main-profile-name">
                                <?php if ($arResult["EMAIL_REQUIRED"]): ?>
                                    <span class="starrequired color-redLight  color-redLight">* </span>
                                <?php endif ?><?= GetMessage("AUTH_EMAIL") ?>
                            </label>
                            <input type="text" name="USER_EMAIL" maxlength="255"
                                   class="form-control input_lk bx-auth-input"
                                   required minlength="5"
                                   placeholder="_@_._"
                                   value="<?= $arResult["USER_EMAIL"] ?>"/>
                        </div>
                    <?php endif ?>
                    <input type="hidden" name="USER_LOGIN" value="<?= rand(105, 10000) ?>"/>

                    <div class="form-group mb-1">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                               for="main-profile-name">
                            <span class="starrequired color-redLight">* </span>
                            <?= GetMessage("AUTH_PASSWORD_REQ") ?>
                        </label>
                        <input type="password" name="USER_PASSWORD" maxlength="255"
                               minlength="6"
                               class="form-control input_lk bx-auth-input" required
                               value="<?= $arResult["USER_PASSWORD"] ?>" autocomplete="off"/>
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
                        <input type="password" name="USER_CONFIRM_PASSWORD" maxlength="255"
                               class="form-control input_lk bx-auth-input"
                               minlength="6"
                               value="<?= $arResult["USER_CONFIRM_PASSWORD"] ?>"
                               required
                               autocomplete="off"/>
                    </div>
                    <div class="d-flex flex-lg-row flex-md-row flex-column align-items-center">
                        <div class="form-group mb-3 width-auto">
                            <div class="d-flex flex-row align-items-center mb-2 position-relative">
                                <label class="col-form-label main-profile-form-label p-0"
                                       for="main-profile-name">
                                    <span class="starrequired color-redLight">* </span>
                                    <?= GetMessage("PERSONAL_BIRTHDAY") ?>
                                </label>
                                <i class="fa fa-question-circle-o font-20 color-redLight ml-2 block-icon-text"
                                   aria-hidden="true"></i>
                                <div class="d-none block-text br-10 p-3">
                                    <p class="m-0">
                                        Возраст необходимо указать для открытия информации не доступной к просмотру
                                        лицам не достигшим 18 лет.</p>
                                </div>
                            </div>
                            <input type="text" name="USER_PERSONAL_BIRTHDAY" required
                                   class="form-control input_lk bx-auth-input user-birthday readonly"
                                   inputmode="none"
                                   id="main-profile-brd"
                                   autocomplete="Off"
                                   value=""
                                   minlength="8"
                                   placeholder="dd/mm/yyyy"/>
                        </div>
                        <?php if ($arResult["PHONE_REGISTRATION"]): ?>
                            <div class="form-group mb-3 width-auto ml-lg-4 ml-md-4 ml-0">
                                <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label p-0 mb-2"
                                       for="main-profile-name">
                                    <?php if ($arResult["PHONE_REQUIRED"]): ?>
                                        <span class="starrequired color-redLight">* </span>
                                    <?php endif ?><?php echo GetMessage("main_register_phone_number") ?>
                                </label>
                                <div class="code position-relative">
                                    <input id="main-profile-phone" type="text" name="USER_PHONE_NUMBER"
                                           required
                                           minlength="10"
                                           data-input-type="phone"
                                           autocomplete="Off"
                                           value=""
                                           placeholder="+_ (___)-___-____" inputmode="text"
                                           class="form-control input_lk bx-auth-input"/>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="d-flex flex-row align-items-center font-14 mb-3 mt-3">
                        <input type="checkbox" required class="check_input form-check-input mt-0 ml-0"
                               id="soa-property-USER_RULES" name="USER_RULES"/>
                        <label class="bx-soa-custom-label mb-0 ml-3">
                            Я принимаю условия
                            <a class="color-redLight text-decoration-underline" href="/about/users_rules/">
                                Пользовательского соглашения
                            </a>
                        </label>
                    </div>
                    <div class="d-flex flex-row align-items-center font-14 mb-4">
                        <input type="checkbox" required class="check_input form-check-input mt-0 ml-0"
                               name="USER_POLITICS"/>
                        <label class="bx-soa-custom-label mb-0 ml-3">
                            Я принимаю условия
                            <a class="color-redLight text-decoration-underline" href="/about/politics/">
                                Политики конфиденциальности
                            </a>
                        </label>
                    </div>
                    <div class="mb-4">
                        <button type="submit" name="Register"
                                class="btn red_button_cart pl-3 pr-3 font-16"
                                onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                            <?= GetMessage("AUTH_REGISTER") ?>
                        </button>
                    </div>
                </div>
                <?php $APPLICATION->IncludeComponent("bitrix:main.userconsent.request", "",
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
            </form>

            <script>
                $(function () {
                    $('input[name="USER_PHONE_NUMBER"]').phonecode({
                        preferCo: 'ru'
                    });
                });
            </script>
            <script type="text/javascript">
                // $.inputmask.definitions['9'] = '';
                // $.inputmask.definitions['*'] = '[0-9]';
                $('input[name="USER_PHONE_NUMBER"]').inputmask("+7 (***)-***-****", {
                    minLength: 10,
                    removeMaskOnSubmit: true,
                    clearMaskOnLostFocus: true,
                    clearMaskOnLostHover: true,
                    clearIncomplete: true,
                    definitionSymbol: "*"
                });

                let date_now = new Date();
                let year_now = date_now.getFullYear();
                let date_datipicker = date_now.setFullYear(year_now - 18);

                $('input[name="USER_PERSONAL_BIRTHDAY"]').datepicker({
                    dateFormat: 'dd/mm/yyyy',
                    maxDate: date_now,
                    autoClose: true,
                    toggleSelected: false,
                    placeholder: "dd/mm/yyyy"
                });

                $("input[name='USER_EMAIL']").inputmask("email", {
                    removeMaskOnSubmit: true,
                    clearMaskOnLostFocus: true
                });

                $(".readonly").keydown(function (e) {
                    e.preventDefault();
                });

                document.bform.USER_NAME.focus();

                $('i.block-icon-text').on('click', function () {
                    $('.block-text').toggleClass('d-none');
                });

            </script>
        <?php endif; ?>
    </noindex>
</div>