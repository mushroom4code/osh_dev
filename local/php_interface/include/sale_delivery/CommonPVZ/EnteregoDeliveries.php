<?php
namespace Enterego;
use Bitrix\Main\EventResult;
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php');

// в процессе
class EnteregoDeliveries
{
    private static $clientId = 'Qzidnsik5xW7cyJ86Vu89zwshwTq6iQF';
    private static $clientSecret = 'eyQN5S9Kvn5xcJWIHjWQFMGxVH6FfMbZ';
    private static $token = false;
    private static $error = null;
    private static $cityFrom = '44';
    private static $delPrice = 888;

    private function request($url, $strReq, $method = 'POST')
    {
        $curl = curl_init();
        $header = array();

        if (self::$token) {
            $header[] = "Authorization: Bearer " . self::$token;
            $header[] = "Content-Type: application/json; charset=utf-8";
        } else {
            $header[] = "Content-Type: application/x-www-form-urlencoded";
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $strReq,
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }

    // получение авторизационного токена
    public static function auth()
    {
        $postFieldsAr = array(
            "grant_type" => "client_credentials",
            "client_id" => self::$clientId,
            "client_secret" => self::$clientSecret
        );
        $strRequest = http_build_query($postFieldsAr);
        $authRequest = self::request("https://api.cdek.ru/v2/oauth/token", $strRequest);

        if ($authRequest === false) {
            self::$error = "Ошибка, сервер CDEK вернул ответ не в JSON формате.";
            return false;
        }
        if (isset($authRequest->access_token) and (($authRequest->access_token . "") != "")) {
            self::$token = $authRequest->access_token;
            return true;
        } else {
            self::$error = "Ошибка, ответ сервера CDEK не содержит свойства access_token или этот параметр пустой.";
            return false;
        }
    }

    public static function getSDEKPrice($weight, $cityTo, $addressPVZ)
    {
        $requestObjectString = '{
        "tariff_code":"136",
        "from_location":{"code": "' . self::$cityFrom . '"},
        "to_location":{"code":"' . $cityTo . '", "address": "'. $addressPVZ .'"},
        "packages":[{
        "weight":"' . $weight . '"
        }]
        }';

        if (self::$token) {
            return self::request('https://api.cdek.ru/v2/calculator/tariff', $requestObjectString);
        }
        return false;
    }

    public static function getCityCode($cityName)
    {
        $ar = ['country_codes' => 'RU', 'city' => $cityName];
        return self::request('https://api.cdek.ru/v2/location/cities', http_build_query($ar), 'GET');
    }

    /**
     * @param null $delPrice
     */
    public static function setDelPrice(int $delPrice): void
    {
        self::$delPrice = $delPrice;
    }



    public static function assignDeliveryPrice(&$result) {

        echo '<pre>';
        print_r('$result');
        echo '</pre>';

        /*$order = $event->getParameter('ENTITY');
        $shipmentCollection = $order->getShipmentCollection();

        foreach($shipmentCollection as $shipment) {
            if(!$shipment->isSystem())
                $shipment->setBasePriceDelivery(self::$delPrice, false);
        }*/
    }

        /*$name = $event->getParameter('NAME');
        $value = $event->getParameter('VALUE');
        $ent = $event->getParameter('ENTITY');


        if ($name == 'PRICE_DELIVERY') {
            $value = self::$delPrice;
            $event->addResult(
                new EventResult(
                    EventResult::SUCCESS, array('VALUE' => $value)
                )
            );
        }
        echo '<pre>';
        print_r($name . ' - '. $value);
        echo '</pre>';*/
        /*if ($name === 'PRICE')
        {
            $value = floor($value);
            $event->addResult(
                new Main\EventResult(
                    Main\EventResult::SUCCESS, array('VALUE' => $value)
                )
            );
        }

        $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE'] = 666;
        $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE_FORMATED'] = '777 hee,';
        echo '<pre>';
        print_r($arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE']);
        echo '</pre>';
        if (self::$delPrice != 0) {
            $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE'] = self::$delPrice;
            $arResult['JS_DATA']['TOTAL']['DELIVERY_PRICE_FORMATED'] = self::$delPrice . ' руб.';
        }*/
/*
    }*/

}

