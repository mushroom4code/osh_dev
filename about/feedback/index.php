<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Обратная связь");
?>
<h5 class="mb-3" id="form"></h5>
<div class="mb-5">
    <form class="form_company form-form " id="support">
        <div class="form-form-wrap">
            <input type="hidden" name="recaptcha_token" value="">
            <div class="form-group mb-3">
                <label class="label_company">Обратная связь</label>
            </div>
            <div class="form-group mb-3">
                <input type="text" class="form-control input_lk" id="Name" name="NAME"
                       placeholder="Пожалуйста, представьтесь*">
                <div class="er_FORM_NAME error_field"></div>
            </div>
            <div class="form-group mb-3">
                <input type="text" data-name="PHONE-FORM" name="PHONE" class="form-control input_lk" id="phoneNumber"
                       placeholder="Мобильный телефон, чтобы связаться с вами*">
                <div class="er_FORM_PHONE error_field"></div>
            </div>
            <div class="form-group mb-3">
                <input type="text" data-name="EMAIL" name="EMAIL" class="form-control input_lk" id="phoneNumber"
                       placeholder="E-mail если хотите получить ответ на почту">
            </div>
            <div class="form-group mb-5">
                <textarea class="form-control input_lk" name="MESSAGE" id="text" placeholder="Сообщение*"></textarea>
                <div class="er_FORM_MESSAGE error_field"></div>
            </div>
            <div class="form-group mb-2">
                <div class="col-sm-10">
                    <input class="btn link_menu_catalog get_code_button"
                           type="submit"
                           value="Отправить"
                           onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                </div>
                <div class="error_form error_field"></div>
            </div>
        </div>
        <div class="form_block_ok">
            Сообщение отправлено.<br>
            Мы свяжемся с вами в самое ближайшее время!
        </div>
    </form>

</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php") ?>