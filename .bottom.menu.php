<?
$ar = [];
$server = file_exists($_SERVER["DOCUMENT_ROOT"].'/local/templates/Oshisha/images/presentaion.pdf');
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
        "/local/templates/Oshisha/images/presentaion.pdf",
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