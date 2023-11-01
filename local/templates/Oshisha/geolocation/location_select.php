<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

session_start(); // Обязательно запускаем (возобновляем) сессию! Без этого работать не будет!
// $_SESSION["sale_order_ajax"] = 'N';
// Достаем из Битрикс все местоположения

$curPage = $APPLICATION->GetCurPage(); // получение текущего url
CModule::IncludeModule('sale');

use \Bitrix\Sale\Location\LocationTable;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (empty($_SESSION['city_of_user'])) {
    $_SESSION['city_of_user'] = $arResult['REAL_REGION'];
}

if (isset($_GET["city"])) {
    $res = \Bitrix\Sale\Location\LocationTable::getList(array(
        'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
        'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
    ));

    while ($itemcity = $res->fetch()) {
        if ($itemcity['NAME_RU'] == $_GET["city"]) {
            $_SESSION["city_of_user"] = $itemcity['NAME_RU'];
            $_SESSION["id_region"] = $itemcity['CITY_ID'];
            $_SESSION["code_region"] = $itemcity['CODE'];
        }
    }
    $_SESSION["city_of_user"] = $_GET["city"];
}