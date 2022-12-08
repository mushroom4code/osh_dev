<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain  $APPLICATION
 */
$APPLICATION->SetTitle("Заказы");
LocalRedirect('/personal/');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
