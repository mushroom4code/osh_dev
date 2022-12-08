<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("sale");
use \Bitrix\Sale\Location\GeoIp;
// Если выбран город
if($_SESSION["city_of_user"]) {
    // выводим его
    echo $_SESSION["city_of_user"];
}else{
    // если нет, выводим определенный по IP
    $ip = $_SERVER["REMOTE_ADDR"];
    if(!empty($_SERVER["HTTP_X_REAL_IP"])){
        $ip = $_SERVER["HTTP_X_REAL_IP"];
    }

    $obBitrixGeoIPResult = \Bitrix\Main\Service\GeoIp\Manager::getDataResult($ip, 'ru');
    if($obBitrixGeoIPResult !== null){
        if($obResult = $obBitrixGeoIPResult->getGeoData()){

            $_SESSION['GEOIP'] = get_object_vars($obResult);
            $city = isset($_SESSION['GEOIP']['cityName']) && $_SESSION['GEOIP']['cityName'] ? $_SESSION['GEOIP']['cityName'] : '';
        }
    }

    function getLocationByCode(string $locationCode){
        return \CSaleLocation::getLocationIDbyCODE($locationCode);
    }

    $_SESSION["city_of_user"] =  $_SESSION['GEOIP']['cityName'];
    $_SESSION["id_region"] = getLocationByCode(\Bitrix\Sale\Location\GeoIp::getLocationCode($ip, 'ru'));
    $_SESSION["code_region"] = \Bitrix\Sale\Location\GeoIp::getLocationCode($ip, 'ru');
    $_SESSION["real_region"] = 'n';

    $cityName = $_SESSION['GEOIP']['cityName'];

    echo $cityName;
}
if(empty($_SESSION["city_of_user"] || $cityName)){
    $_SESSION["city_of_user"] =  'Москва';
    $_SESSION["id_region"] = 84;
    $_SESSION["code_region"] = '0000073738';
    $_SESSION["real_region"] = 'n';

    echo $_SESSION["city_of_user"];
}                                
?>