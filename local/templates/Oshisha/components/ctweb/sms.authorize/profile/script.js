;(function () {
    "use strict";

    BX.namespace('BX.Ctweb.SMSAuth');

    if (BX.Ctweb.SMSAuth.Controller instanceof Function)
        return;

    const STATE_INIT = 'INIT';
    const STATE_EXPIRED = 'EXPIRED';
    const STATE_PHONE_WAITING = '1';
    const STATE_USER_WAITING = '2';
    const STATE_CODE_WAITING = '3';
    const STATE_SUCCESS = '4';
    const STATE_CODE_NOT_VALID = '5';
    const STATE_CODE_REUSED = '6';
    const STATE_PHONE_EXISTS = '7';
    const STATE_CAPTCHA_WRONG = '8';

    const ERROR_CODE_NOT_CORRECT = 'CODE_NOT_CORRECT';
    const ERROR_TIME_EXPIRED = 'TIME_EXPIRED';
    const ERROR_PHONE_EXISTS = 'PHONE_EXISTS';
    const ERROR_CAPTCHA_WRONG = 'CAPTCHA_WRONG';

    BX.Ctweb.SMSAuth.Controller = function (params) {
        this.state = STATE_INIT;

        this.obMain = null;
        this.obCode = null;
        this.obTimer = null;
        this.obReuse = null;
        this.obSubmit = null;
        this.obState = null;
        this.obBack = null;
        this.obResend = null;
        this.obMessage = null;
        this.obChangePhone = null;
        this.obLogin = null;
        this.obRegistration = null;

        //email
        this.mailForm = null;
        this.obAuthPhoneLogin = null;
        this.obAuthEmailLogin = null;
        this.obEmail = null;
        this.obPassword = null;

        this.errotTitle = null;
        this.errorText = null;
        this.errorTitleExpired = null;
        this.errorTextExpired = null;
        this.errorAlert = null;

        this.timerId = null;

        this.timeLeft = 0;
        this.lastTime = (+new Date());

        this.constructor(params);
    };

    BX.Ctweb.SMSAuth.Controller.prototype.constructor = function (params) {
        this.obForm = BX(params['TEMPLATE']['MAIN_ID']);
        this.obCode = BX(params['TEMPLATE']['CODE']);
        this.obFormId = BX(params['TEMPLATE']['COMPONENT_ID_BUTTON_CODE']);
        this.obTimer = BX(params['TEMPLATE']['TIMER']);
        this.obReuse = BX(params['TEMPLATE']['MAIN_ID'] + 'REUSE');
        this.obSubmit = BX(params['TEMPLATE']['SUBMIT']);
        this.obState = BX(params['TEMPLATE']['STATE']);
        this.obBack = BX(params['TEMPLATE']['BACK']);
        this.obResend = BX(params['TEMPLATE']['RESEND']);
        this.obMessage = BX(params['TEMPLATE']['MSG_NOT_COME']);
        this.obPhone = BX(params['TEMPLATE']['MAIN_ID'] + 'phone');
        this.obChangePhone = BX(params['TEMPLATE']['CHANGE_PHONE']);
        this.obAuthEmailLogin = BX(params['TEMPLATE']['AUTH_EMAIL_LOGIN']);
        this.obRegistration = BX(params['TEMPLATE']['REGISTRATION']);
        //email
        this.mailForm = BX(params['TEMPLATE']['MAIL_FORM']);
        this.obAuthPhoneLogin = BX(params['TEMPLATE']['AUTH_PHONE_LOGIN']);
        this.obLogin = BX(params['TEMPLATE']['LOGIN']);
        this.obEmail = BX(params['TEMPLATE']['EMAIL']);
        this.obPassword = BX(params['TEMPLATE']['PASSWORD']);
        this.obForgotPass = BX(params['TEMPLATE']['FORGOT_PASSWORD']);

        this.errotTitle = BX(params['TEMPLATE']['ERROR_TITLE']);
        this.errorText = BX(params['TEMPLATE']['ERROR_TEXT']);
        this.errorTitleExpired = BX(params['TEMPLATE']['ERROR_TEXT_EXPIRED']);
        this.errorTextExpired = BX(params['TEMPLATE']['ERROR_TEXT_EXPIRED']);
        this.errorAlert = BX(params['TEMPLATE']['ERROR_ALERT']);
        this.id_button = params['TEMPLATE']['COMPONENT_ID_BUTTON_CODE'];

        this.timeLeft = params['DATA']['TIME_LEFT'] ? parseInt(params['DATA']['TIME_LEFT']) : 0;
        this.error_not_correct = params['DATA']['ERROR_ALERT_NOT_CORRECT'];
        this.error_empty_field = params['DATA']['ERROR_ALERT_EMPTY_FIELD'];
        let PRODUCT_URL = '';

        if (this.obPhone) {
            if (this.obPhone.value === '') {
                this.obState.value = STATE_PHONE_WAITING;
            }
            let phone = this.formatPhoneNumber(this.obPhone.value, false);
            // $(this.obPhone).mask("+7 999 999 99 99");
            // let phoneMask = new BX.MaskedInput({
            //     mask: '+7 (999) 999-99-99', // устанавливаем маску
            //     input: this.obPhone,
            //     placeholder: '_' // символ замены +7 ___ ___ __ __
            // });
            // phoneMask.setValue(phone);
        }
        if (this.obState) {
            this.setState(this.obState.value);
        }

        $(document).on('click', '.link_header_box', function (event) {
            event.preventDefault();
            if ($(this).attr('data-href') !== '') {
                PRODUCT_URL = $(this).attr('data-href');
            }
            $(document).find('[data-id="' + this.id_button + '"]').show();
        }.bind(this));

        $('.close_header_box').on('click', function (event) {
            event.preventDefault();
            $(document).find('[data-id="' + this.id_button + '"]').hide();
        }.bind(this));

        BX.bind(this.obBack, 'click', function () {
            event.preventDefault();
            this.setState(STATE_CODE_WAITING);
        }.bind(this));

        BX.bind(this.obReuse, 'click', function () {
            event.preventDefault();

            BX.style(this.obReuse, 'visibility', 'hidden');

            let form = $(this.obFormId);
            let url = form.attr('action');
            let data = form.serializeArray();

            data.push({name: 'method', value: 'REUSE_CODE'});

            this.getCode(data, url);
        }.bind(this));

        BX.bind(this.obChangePhone, 'click', function () {
            event.preventDefault();

            clearInterval(this.timerId);

            let form = $(this.obFormId);
            let url = form.attr('action');
            let data = form.serializeArray();

            data.push({name: 'method', value: 'CHANGE_PHONE'});

            this.getCode(data, url);

            this.setState(STATE_PHONE_WAITING);
        }.bind(this));

        BX.bind(this.obResend, 'click', function () {
            event.preventDefault();

            let form = $(this.obFormId);
            let url = form.attr('action');
            let data = form.serializeArray();

            data.push({name: 'method', value: 'REUSE_CODE'});

            this.getCode(data, url);
        }.bind(this));

        BX.bind(this.obAuthEmailLogin, 'click', function () {
            event.preventDefault();
            this.changeTypeAuth();
        }.bind(this));

        BX.bind(this.obAuthPhoneLogin, 'click', function () {
            event.preventDefault();
            this.changeTypeAuth();
        }.bind(this));

        BX.bind(this.obForgotPass, 'click', function () {
            event.preventDefault();
        }.bind(this))

        BX.bind(this.obLogin, 'click', function () {
            event.preventDefault();

            BX.hide(this.errorAlert);
            if (this.obEmail.value === '' || this.obPassword.value === '') {
                BX.show(this.errorAlert);
                BX.adjust(this.errorAlert, {text: BX.message('ERROR_ALERT_EMPTY_FIELD')});
            } else {
                let form = $(BX(this.mailForm));
                let url = form.attr('action');
                let data = form.serializeArray();
                this.EmailAuth(data, url, PRODUCT_URL);
            }
        }.bind(this));

        $('[data-id="' + this.id_button + '"]').on('submit', this.obFormId, function (event) {
            event.preventDefault();
            let form = $(this.obFormId);
            if ($(form).find('input[name="PASSWORD"]').val() !== $(form).find('input[name="CONFIRM_PASSWORD"]').val()) {
                $(form).find('input[name="CONFIRM_PASSWORD"]').closest('div').find('span.errors').remove();
                $(form).find('input[name="CONFIRM_PASSWORD"]').closest('div')
                    .append('<span class="color-redLight font-16 mt-2 mb-2 errors">' + BX.message('ERROR_NOT_CORRECT_PASSWORD') + '</span>');
            } else {
                let url = form.attr('action');
                let data = form.serializeArray();
                this.getCode(data, url, PRODUCT_URL);
            }

        }.bind(this));
    };

    BX.Ctweb.SMSAuth.Controller.prototype.getCode = function (data, url, product_url = '') {

        let Ctweb = this;
        data.push({name: 'is_ajax_post', value: 'Y'});
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function (response) {
                let objResponse = JSON.parse(response);
                let step = objResponse['STEP'];

                if (objResponse['ERROR_TYPE'] === 'LOGIN') {
                    this.errorAlert.style.visibility = "visible";
                    BX.adjust(this.errorAlert, {text: BX.message('ERROR_ALERT_NOT_CORRECT')});
                }

                if (step === null)
                    return

                if (objResponse['USER_VALUES'] !== null && objResponse['USER_VALUES']['PHONE'] !== null) {
                    Ctweb.phone = objResponse['USER_VALUES']['PHONE'];
                }

                if (step === STATE_SUCCESS) {
                    $('[data-id="'+this.id_button+'"]').hide();
                    if(product_url !== ''){
                        location.href = window.location.origin + product_url;
                    }else {
                        location.reload();
                    }
                } else {
                    if (objResponse['ERRORS'].length > 0) {
                        if (objResponse['ERRORS'][0] === ERROR_CODE_NOT_CORRECT) {
                            Ctweb.setState(STATE_CODE_NOT_VALID);
                        } else if (objResponse['ERRORS'][0] === ERROR_TIME_EXPIRED) {
                            Ctweb.setState(STATE_EXPIRED);
                        } else if (objResponse['ERRORS'][0] === ERROR_PHONE_EXISTS) {
                            Ctweb.setState(STATE_PHONE_EXISTS);
                        } else if (objResponse['ERRORS'][0] === ERROR_CAPTCHA_WRONG) {
                            Ctweb.setState(STATE_CAPTCHA_WRONG);
                        }
                    } else {
                        if (step === STATE_CODE_WAITING) {
                            Ctweb.timeLeft = objResponse['REUSE_TIME'];
                            Ctweb.lastTime = (+new Date());
                        }
                        Ctweb.setState(step);
                    }
                }
            }.bind(this)
        });
    };

    BX.Ctweb.SMSAuth.Controller.prototype.EmailAuth = function (data, url, product_url = '') {

        BX.adjust(this.obCode, {props: {value: ''}});
        data.push({name: 'is_ajax_post', value: 'Y'});
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function (response) {
                let objResponse = JSON.parse(response);
                let step = objResponse['STEP'];

                if (step === STATE_SUCCESS) {
                    $('[data-id="'+this.id_button+'"]').hide();
                    if(product_url !== ''){
                        location.href = window.location.origin + product_url;
                    }else {
                        location.reload();
                    }

                } else {
                    BX.show(this.errorAlert);
                    BX.adjust(this.errorAlert, {text: BX.message('ERROR_ALERT_NOT_CORRECT')});
                }
            }.bind(this)
        });
    };

    BX.Ctweb.SMSAuth.Controller.prototype.setState = function (state) {
        let prev = this.state;
        this.state = state;
        this.stateTransition(state, prev);
    };

    BX.Ctweb.SMSAuth.Controller.prototype.stateTransition = function (state, prev) {
        this.obState.value = state;

        $('.ctweb-smsauth-menu-step').addClass('hidden');

        // Элементы управления
        BX.hide(this.obSubmit);
        BX.hide(this.obBack.closest('div'));
        BX.hide(this.obReuse.closest('div'));

        switch (state) {
            case STATE_INIT:
                if (this.timeLeft > 0)
                    this.setState(STATE_CODE_WAITING);
                else
                    this.setState(STATE_PHONE_WAITING);
                break;
            case STATE_PHONE_WAITING:
                BX.toggleClass(BX(`ctweb_form_step_${state}`), 'hidden');
                BX.show(this.obSubmit);
                BX.hide(this.obMessage);
                BX.hide(this.obChangePhone);
                break;
            case STATE_USER_WAITING:
                break;
            case STATE_CODE_WAITING:
                BX.style(this.obReuse, 'visibility', 'hidden');
                BX.toggleClass(BX(`ctweb_form_step_${state}`), 'hidden');
                BX.show(this.obReuse.closest('div'));
                BX.show(this.obTimer);
                BX.show(this.obMessage);
                BX.show(this.obChangePhone);
                BX.hide(this.obResend.closest('div'));

                let phoneMask = this.formatPhoneNumber(this.obPhone.value);
                let objLabel = BX.findChildren(BX(`ctweb_form_step_3`), {"tag": "label"}, true);
                BX.adjust(objLabel[0], {text: `На номер ${phoneMask} было отправлено сообщение с кодом`});

                this.timerId = setInterval(function () {
                    let now = (+new Date());
                    let delta = (now - this.lastTime) / 1000;
                    this.lastTime = now;

                    let seconds_left = this.updateTime(delta);
                    this.renderTime(seconds_left);
                    if (seconds_left <= 0) {
                        this.setState(STATE_CODE_REUSED);
                        BX.style(this.obReuse, 'visibility', 'visible');
                    }
                }.bind(this), 100);

                this.obCode.value = '';
                BX.show(this.obCode.closest('div'));

                //установка курсора на начало
                $.fn.setCursorPosition = function (pos) {
                    if ($(this).get(0).setSelectionRange) {
                        $(this).get(0).setSelectionRange(pos, pos);
                    } else if ($(this).get(0).createTextRange) {
                        var range = $(this).get(0).createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', pos);
                        range.moveStart('character', pos);
                        range.select();
                    }
                };

                $(this.obCode).click(function () {
                    $(this).setCursorPosition(0);
                }).mask("9 9 9 9", {
                    completed: function () {
                        let code = this.obCode.value;
                        code = code.replace(/\s/g, '');
                        this.obCode.value = code;
                        document.getElementById('submit_code').click();
                    }.bind(this),
                    autoclear: false
                });

                break;
            case STATE_CODE_NOT_VALID:
                clearInterval(this.timerId);
                BX.toggleClass(BX('ctweb_form_step_error'), 'hidden');
                BX.hide(this.obSubmit);
                BX.adjust(this.errotTitle, {text: BX.message('SMS_AUTH_ERROR_CODE_NOT_CORRECT_TITLE')});
                BX.adjust(this.errorText, {text: BX.message('SMS_AUTH_ERROR_CODE_NOT_CORRECT_TEXT')});

                BX.show(this.obBack.closest('div'));
                BX.show(this.obResend.closest('div'));
                BX.show(this.obMessage);
                BX.show(this.obResend);
                break;
            case STATE_CAPTCHA_WRONG:
                clearInterval(this.timerId);
                BX.toggleClass(BX('ctweb_form_step_error'), 'hidden');
                BX.hide(this.obSubmit);
                BX.adjust(this.errotTitle, {text: BX.message('SMS_AUTH_ERROR_CAPTCHA_WRONG_TITLE')});
                BX.adjust(this.errorText, {text: BX.message('SMS_AUTH_ERROR_CAPTCHA_WRONG_TEXT')});

                BX.show(this.obChangePhone);
                break;
            case STATE_PHONE_EXISTS:
                BX.toggleClass(BX('ctweb_form_step_error'), 'hidden');
                BX.adjust(this.errotTitle, {text: BX.message('SMS_AUTH_ERROR_PHONE_EXISTS_TITLE')});
                BX.adjust(this.errorText, {text: BX.message('SMS_AUTH_ERROR_PHONE_EXISTS_TEXT')});
;
                BX.show(this.obChangePhone);
                break;
            case STATE_CODE_REUSED:
                clearInterval(this.timerId);
                BX.toggleClass(BX(`ctweb_form_step_${STATE_CODE_WAITING}`), 'hidden');
                BX.hide(this.obTimer);
                BX.show(this.obChangePhone);
                BX.show(this.obReuse.closest('div'));
                BX.show(this.obCode.closest('div'));

                break;
            case STATE_EXPIRED:
                BX.toggleClass(BX('ctweb_form_step_error'), 'hidden');
                clearInterval(this.timerId);
                BX.adjust(this.errotTitle, {text: BX.message('SMS_AUTH_ERROR_TIME_EXPIRED_TITLE')});
                BX.adjust(this.errorText, {text: BX.message('SMS_AUTH_ERROR_TIME_EXPIRED_TEXT')});

                BX.hide(this.obReuse.closest('div'));
                BX.show(this.obResend.closest('div'));
                BX.hide(this.obCode.closest('div'));
                BX.show(this.obMessage);

                break;
            default:
                throw new Error("No state found: " + state);
        }
    };

    BX.Ctweb.SMSAuth.Controller.prototype.formatPhoneNumber = function (str, withCode = true) {
        //Filter only numbers from the input
        let cleaned = ('' + str).replace(/\D/g, '');

        //Check if the input is of correct
        let match = cleaned.match(/^(\d|)?(\d{3})(\d{3})(\d{4})$/);

        if (match) {
            let intlCode = (match[1] ? "+" + match[1] : '');
            return [withCode ? intlCode : '', '(', match[2], ') ', match[3], '-', match[4]].join('')
        }

        return '';
    };

    /**
     * @return int
     */
    BX.Ctweb.SMSAuth.Controller.prototype.updateTime = function (dt) {
        this.timeLeft -= dt;

        return Math.floor(this.timeLeft);
    };

    BX.Ctweb.SMSAuth.Controller.prototype.renderTime = function (secondsLeft) {
        let minutes = Math.floor(secondsLeft / 60);
        minutes = minutes < 10 ? '0' + minutes : minutes;
        let seconds = Math.floor(secondsLeft % 60);
        seconds = seconds < 10 ? '0' + seconds : seconds;

        BX.adjust(this.obTimer, {html: [BX.message('SMS_AUTH_TIME_LEFT'), "<span class='glowing-text'>" + minutes, ':', seconds].join('') + "</span>"});
    };

    BX.Ctweb.SMSAuth.Controller.prototype.changeTypeAuth = function () {
        clearInterval(this.timerId);
        BX.hide(this.errorAlert);
        BX.toggleClass(this.mailForm, 'hidden');
        BX.toggleClass(this.obFormId, 'hidden');
    }

})();

function ValidateEmail(inputText) {
    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if (inputText.value.match(mailformat)) {
        alert("Valid email address!");
        return false;
    } else {
        alert("You have entered an invalid email address!");
        return false;
    }
}