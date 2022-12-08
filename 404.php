<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404", "Y");

@define("HIDE_SIDEBAR", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

/**
 * @var CAllMain|CMain $APPLICATION
 */
$APPLICATION->SetTitle("Страница не найдена"); ?>

<div class="d-flex flex-column section_404 justify-content-center align-items-center mb-5 mt-5">
    <div class="box_with_404 mb-5"></div>
    <div class="text-center mb-5"><b>Кажется, такой страницы нет на сайте...<br>
            Перейдите на <a href="/" class="red_text">главную</a> или обратитесь в
            <a href="/about/FAQ/#support" class="red_text">поддержку</a></b>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
