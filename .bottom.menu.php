<?php
global $presentationUrl;
$ar = [];
$server = file_exists($_SERVER["DOCUMENT_ROOT"].$presentationUrl) && !empty($presentationUrl);
$aMenuLinks = array(
    array(
        "О нас",
        "/about/o-nas/",
        array(),
        array(),
        ""
    ),
    array(
        "Презентация",
        "$presentationUrl",
        array(),
        array(),
        "$server"
    ),
    array(
        "Для бизнеса",
        "/about/for-business/",
        array(),
        array(),
        ""
    ),

);

if ($USER->IsAuthorized()) {
    $aMenuLinks[] = array(
        "Доставка и оплата",
        "/about/delivery/",
        array(),
        array(),
        "",
    );
}