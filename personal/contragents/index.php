<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Bitrix\Main\Page\Asset;

/** @var CUser $USER
 * @var CAllMain|CMain $APPLICATION
 */

if ($USER->IsAuthorized()) {
    Asset::getInstance()->addJs("/personal/contragents/js/script.js"); ?>
    <div class="mobile_lk mb-5 flex flex-col xs:bg-white md:flex-row">
        <div class="sidebar_lk">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:menu",
                "",
                array(
                    "ALLOW_MULTI_SELECT" => "N",
                    "CHILD_MENU_TYPE" => "left",
                    "DELAY" => "N",
                    "MAX_LEVEL" => "1",
                    "MENU_CACHE_GET_VARS" => array(""),
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_TYPE" => "N",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "ROOT_MENU_TYPE" => "personal",
                    "USE_EXT" => "N"
                )
            ); ?>
        </div>
        <script>
            function changeTypeInn(elem) {
                if (elem.value === 'Юр.лицо') {
                    $('input[data-name="INN"]').inputmask({"mask": "9999999999"});
                    return;
                }

                if (elem.value === 'ИП') {
                    $('input[data-name="INN"]').inputmask({"mask": "999999999999"});
                    return;
                }

                if (elem.value === 'Физ. лицо') {
                    $('input[data-name="INN"]').inputmask({"mask": "999999999999"});
                }
            }
        </script>
        <div class="mb-5 w-full" id="content_box">
            <div id="createContragent"></div>
        </div>
    </div>
    <script src="/dist/app.generated.js" defer></script>
    <?php
} else {
    LocalRedirect('/login/?login=yes');
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
