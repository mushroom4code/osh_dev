<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_POST['action'] === 'getCityName') {
    exit(\CommonPVZ\DeliveryHelper::getCityName($_POST['codeCity']));
}

if ($_POST['action'] === 'getPVZList') {
    exit(json_encode(\CommonPVZ\DeliveryHelper::getAllPVZ($_POST['cityName'])));
}

if ($_POST['action'] === 'getPrice') {
    /*if ($_POST['delivery'] === 'PickPoint') {
        exit(json_encode(\CommonPVZ\PickPointDelivery::getPrice($_POST)));
    }*/
}