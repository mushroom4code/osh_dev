<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start(); // Обязательно запускаем (возобновляем) сессию! Без этого работать не будет!
// $_SESSION["sale_order_ajax"] = 'N';
// Достаем из Битрикс все местоположения

$curPage = $APPLICATION->GetCurPage(); // получение текущего url
CModule::IncludeModule('sale');
use \Bitrix\Sale\Location\LocationTable;

$res = \Bitrix\Sale\Location\LocationTable::getList(array(
    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5'),
    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
));

$protocolType = explode('/',$_SERVER['SERVER_PROTOCOL']);

if(empty($_SESSION['city_of_user'])){
    $_SESSION['city_of_user'] = $arResult['REAL_REGION'];
}

if (isset($_POST['submitcity'])) {
    if (!empty($_POST['cityother'])) {
        while($itemcity = $res->fetch())
        {
            if($itemcity['NAME_RU'] == $_POST['cityother']){
                $_SESSION["city_of_user"] = $itemcity['NAME_RU'];
                $_SESSION["id_region"] = $itemcity['CITY_ID'];
                $_SESSION["code_region"] = $itemcity['CODE'];
                $_SESSION["real_region"] = 'n';
                header('Location: '.mb_strtolower($protocolType[0]).'://'.$_SERVER['HTTP_HOST'].$curPage);
            }
        }
    }

    list($first,$host) = explode(".",$_SERVER["SERVER_NAME"]);
   // $domen = SITE_SERVER_NAME;
    header('Location: '.mb_strtolower($protocolType[0]).'://'.SITE_SERVER_NAME.$_SERVER['REQUEST_URI']);
    exit;

}
if (isset($_GET["city"])) {
    while($itemcity = $res->fetch())
    {
        if($itemcity['NAME_RU'] == $_GET["city"]){
            $_SESSION["city_of_user"] = $itemcity['NAME_RU'];
            $_SESSION["id_region"] = $itemcity['CITY_ID'];
            $_SESSION["code_region"] = $itemcity['CODE'];
        }
    }
    $_SESSION["city_of_user"] = $_GET["city"];
    header('Location: '.mb_strtolower($protocolType[0]).'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?')));
}

$runames = array();
while($item = $res->fetch())
{
    $runames[] = $item["NAME_RU"];
}
sort($runames);
// запись всех местоположений bitrix в массив и сортировка по алфавиту

// Получение крупных городов
$moskow = array_search('Москва', $runames);
$st_petersburg = array_search('Санкт-Петербург', $runames);
$nizhny_novgorod = array_search('Нижний Новгород', $runames);
$yekaterinburg = array_search('Екатеринбург', $runames);
$permian = array_search('Пермь', $runames);
$novosibirsk = array_search('Новосибирск', $runames);
$kazan = array_search('Казань', $runames);