<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$url = '/';
if ($_GET['lat_to'] && $_GET['lon_to']) {
    $url = 'yandexnavi://build_route_on_map?';
    $url .= http_build_query($_GET);
} else {
    LocalRedirect ("/404.php");
}

header('Location: ' . $url);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");