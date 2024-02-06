<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php ShowMessage($arParams["~AUTH_RESULT"]);
ShowMessage($arResult['ERROR_MESSAGE']); ?>
<div class="bx-auth">
    <?php if ($arResult["AUTH_SERVICES"]): ?>
        <div class="bx-auth-title md:text-3xl text-lg dark:text-textDarkLightGray font-semibold dark:font-medium text-dark md:my-5 my-3">
            <?= GetMessage("AUTH_TITLE") ?></div>
    <?php endif ?>
    <div class="bx-auth-note md:text-3xl text-lg dark:text-textDarkLightGray font-semibold dark:font-medium text-dark md:my-5 my-3">
        <?= GetMessage("AUTH_PLEASE_AUTH") ?></div>
    <form name="form_auth" method="post" target="_top" action="<?= $arResult["AUTH_URL"] ?>">
        <input type="hidden" name="AUTH_FORM" value="Y"/>
        <input type="hidden" name="TYPE" value="AUTH"/>
        <?php if ($arResult["BACKURL"] <> ''): ?>
            <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
        <?php endif ?>
        <?php foreach ($arResult["POST"] as $key => $value): ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $value ?>"/>
        <?php endforeach ?>
        <table class="bx-auth-table">
            <div class="mb-3 md:mt-0 mt-5">
                <p class="dark:text-textDarkLightGray md:font-semibold font-normal dark:font-medium text-dark mb-2 md:text-base text-xs">
                    <?= GetMessage("AUTH_LOGIN") ?></p>
                <input class="bx-auth-input dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-2 px-4 outline-none rounded-md md:w-2/5 w-full"
                       type="text" name="USER_LOGIN" maxlength="255"
                       value="<?= $arResult["LAST_LOGIN"] ?>"/>
            </div>
            <div class="mb-3">
                <p class="dark:text-textDarkLightGray md:font-semibold font-normal dark:font-medium text-dark mb-2 md:text-base text-xs">
                    <?= GetMessage("AUTH_PASSWORD") ?></p>
                <input class="bx-auth-input dark:bg-grayButton bg-white dark:border-none border-borderColor
                         focus:border-borderColor shadow-none py-2 px-4 outline-none rounded-md md:w-2/5 w-full"
                       type="password" name="USER_PASSWORD" maxlength="255"
                       autocomplete="off"/>
                <?php if ($arResult["SECURE_AUTH"]): ?>
                    <span class="bx-auth-secure" id="bx_auth_secure" title="<?= GetMessage("AUTH_SECURE_NOTE") ?>"
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
            <?php if ($arResult["CAPTCHA_CODE"]): ?>
                <div>
                    <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>" width="180"
                         height="40" alt="CAPTCHA"/>
                </div>
                <div>
                    <div class="bx-auth-label"><?= GetMessage("AUTH_CAPTCHA_PROMT") ?>:</div>
                    <input class="bx-auth-input form-control" type="text" name="captcha_word" maxlength="50"
                           value="" size="15" autocomplete="off"/>
                </div>
            <?php endif; ?>
            <?php if ($arResult["STORE_PASSWORD"] == "Y"): ?>
                <div class="mb-4">
                    <input type="checkbox"
                           class="p-4 dark:bg-grayButton checked:hover:bg-grayButton border-0 dark:text-white
                                text-textLight font-normal rounded-full bg-textDark checked:focus:bg-grayButton mr-2"
                           id="USER_REMEMBER" name="USER_REMEMBER" value="Y"/>
                    <label class="text-xs dark:font-normal font-medium text-textLight dark:text-textDarkLightGray"
                           for="USER_REMEMBER">&nbsp;<?= GetMessage("AUTH_REMEMBER_ME") ?></label>
                </div>
            <?php endif ?>
            <div class="authorize-submit-cell mb-5">
                <input type="submit" class="shadow-md h-full flex flex-row justify-center items-center text-white
            dark:bg-dark-red bg-light-red md:py-2.5 py-3 px-4 rounded-5 md:w-40 w-full md:text-sm text-base" name="Login"
                       value="<?= GetMessage("AUTH_AUTHORIZE") ?>"/>
            </div>
        </table>
        <?php if ($arParams["NOT_SHOW_LINKS"] != "Y"): ?>
            <noindex>
                <p class="mb-3">
                    <a href="<?= $arResult["AUTH_FORGOT_PASSWORD_URL"] ?>"
                       class="ctweb-link email-login hover:underline flex flex-row items-center text-sm
                       dark:font-normal font-medium" rel="nofollow">
                        <span class="mr-2.5 p-2 dark:bg-grayButton border border-textDarkLightGray
                        dark:border-grayButton rounded-full">
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
                        <?= GetMessage("AUTH_FORGOT_PASSWORD_2") ?>
                    </a>
                </p>
            </noindex>
        <?php endif ?>
        <?php if ($arParams["NOT_SHOW_LINKS"] != "Y" && $arResult["NEW_USER_REGISTRATION"] == "Y" && $arParams["AUTHORIZE_REGISTRATION"] != "Y"): ?>
            <noindex>
                <p class="mb-5">
                    <a href="<?= $arResult["AUTH_REGISTER_URL"] ?>"
                       class="ctweb-link email-login hover:underline flex flex-row items-center text-sm
                       dark:font-normal font-medium" rel="nofollow">
                      <span class="mr-2.5 p-2 dark:bg-grayButton border border-textDarkLightGray
                       dark:border-grayButton rounded-full">
                        <svg width="16" height="17" viewBox="0 0 21 22" fill="none"
                             xmlns="http://www.w3.org/2000/svg"
                             class="dark:fill-white fill-light-red">
                            <path d="M10.4997 10.451C13.3856 10.451 15.7252 8.11148 15.7252 5.22551C15.7252 2.33954 13.3856 0 10.4997 0C7.61371 0 5.27417 2.33954 5.27417 5.22551C5.27417 8.11148 7.61371 10.451 10.4997 10.451Z"
                            />
                            <path d="M10.5 13.0635C4.71286 13.0635 0 16.575 0 20.9017C0 21.1944 0.254125 21.4243 0.577556 21.4243H20.4224C20.7459 21.4243 21 21.1944 21 20.9017C21 16.575 16.2871 13.0635 10.5 13.0635Z"
                            />
                        </svg>
                      </span>
                        <?= GetMessage("AUTH_REGISTER") ?>
                    </a>
                </p>
                <p class="text-sm font-medium text-iconGray"> <?= GetMessage("AUTH_FIRST_ONE") ?></p>
            </noindex>
        <?php endif ?>

    </form>
</div>
<script type="text/javascript">
    <?php if ($arResult["LAST_LOGIN"] <> ''):?>
    try {
        document.form_auth.USER_PASSWORD.focus();
    } catch (e) {
    }
    <?php else:?>
    try {
        document.form_auth.USER_LOGIN.focus();
    } catch (e) {
    }
    <?php endif?>
</script>

<?php if ($arResult["AUTH_SERVICES"]): ?>
    <?php
    $APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "",
        array(
            "AUTH_SERVICES" => $arResult["AUTH_SERVICES"],
            "CURRENT_SERVICE" => $arResult["CURRENT_SERVICE"],
            "AUTH_URL" => $arResult["AUTH_URL"],
            "POST" => $arResult["POST"],
            "SHOW_TITLES" => $arResult["FOR_INTRANET"] ? 'N' : 'Y',
            "FOR_SPLIT" => $arResult["FOR_INTRANET"] ? 'Y' : 'N',
            "AUTH_LINE" => $arResult["FOR_INTRANET"] ? 'N' : 'Y',
        ),
        $component,
        array("HIDE_ICONS" => "Y")
    );
    ?>
<?php endif ?>
