<?
$ar = [];
$aMenuLinks = array(
    array(
        "О нас",
        "/about/o-nas/",
        array(),
        array(),
        ""
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