<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<form name="bform" method="post" target="_top" action="<?= $arResult["AUTH_URL"] ?>">
    <?php if ($arResult["BACKURL"] <> '') { ?>
        <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
    <?php } ?>
    <input type="hidden" name="AUTH_FORM" value="Y">
    <input type="hidden" name="TYPE" value="SEND_PWD">
    <div class="flex flex-row justify-between items-center">
        <h1 class="md:text-3xl text-lg dark:text-textDarkLightGray font-semibold dark:font-medium text-dark md:my-5 my-2">Забыли пароль?</h1>
        <div class="flex flex-row items-center md:p-0 p-4">
        <svg width="30" height="31" viewBox="0 0 34 35" class="mr-3 md:w-8 w-6" xmlns="http://www.w3.org/2000/svg">
            <path class="fill-light-red dark:fill-white"
                  d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"></path>
        </svg>
        <a href="<?= $arResult["AUTH_AUTH_URL"] ?>"
           class="font-medium md:text-lg text-sm dark:text-textDarkLightGray text-dark">Авторизация</a>
    </div>
    </div>
    <div class="dark:text-textDarkLightGray font-normal text-dark mb-10 md:text-lg text-xs">
        <?= GetMessage("sys_forgot_pass_note_email") ?>
    </div>
    <div class="form-group row">
        <div class="dark:text-textDarkLightGray font-semibold dark:font-medium text-dark mb-2 text-md">
            <?= GetMessage("sys_forgot_pass_login1") ?></div>
        <div class="flex md:flex-row flex-col mb-5 mr-5">
            <input type="text" class="dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-2 px-4 outline-none rounded-md md:w-2/5 w-full"
                   name="USER_LOGIN" value="<?= $arResult["USER_LOGIN"] ?>"/>
            <input type="hidden" class="" name="USER_EMAIL"/>
            <div class="md:mt-0 mt-3 h-full md:ml-3 ml-0">
                <button type="submit" class="btn shadow-md h-full flex flex-row justify-center items-center text-white
            dark:bg-dark-red bg-light-red md:py-3 py-2.5 px-4 rounded-5 w-40 md:text-lg text-sm"><?= GetMessage("AUTH_SEND") ?>
                    <svg width="25" height="20" viewBox="0 0 27 22" class="md:w-6 w-5 ml-3" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.6256 4.28745L9.09075 13.947L7.12987 20.6887V13.008L20.6256 4.28745ZM24.8643 0.505859L0.462891 7.82286L6.7424 12.2161L24.8643 0.505859ZM7.84745 21.5635L11.9893 16.9689L9.65785 15.3404L7.84745 21.5635ZM9.93153 14.3996L9.91554 14.4555L19.892 21.4163L26.133 0.829029L9.93153 14.3996Z"
                              fill="white"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <?php if ($arResult["PHONE_REGISTRATION"] && false): ?>
        <div style="margin-top: 16px">
            <div><b><?= GetMessage("sys_forgot_pass_phone") ?></b></div>
            <div><input type="text" name="USER_PHONE_NUMBER" value="<?= $arResult["USER_PHONE_NUMBER"] ?>"/></div>
            <div><?php echo GetMessage("sys_forgot_pass_note_phone") ?></div>
        </div>
    <?php endif; ?>
    <?php if ($arResult["USE_CAPTCHA"]): ?>
        <div style="margin-top: 16px">
            <div>
                <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>" width="180"
                     height="40" alt="CAPTCHA"/>
            </div>
            <div><?= GetMessage("system_auth_captcha") ?></div>
            <div><input type="text" name="captcha_word" maxlength="50" value=""/></div>
        </div>
    <?php endif ?>
</form>
<div class="md:w-2/5 w-full mt-5 text-sm"><?php ShowMessage($arParams["~AUTH_RESULT"]); ?></div>
<script type="text/javascript">
    document.bform.onsubmit = function () {
        document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;
    };
    document.bform.USER_LOGIN.focus();
</script>