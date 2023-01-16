<?php

namespace CommonPVZ;


class SDEKDelivery extends CommonPVZ
{
    protected $delivery_name = 'SDEK';
    private $token = '';

    protected function connect()
    {
        $postFieldsAr = array(
            "grant_type" => "client_credentials",
            "client_id" => $this->configs['setAccount'],
            "client_secret" => $this->configs['setSecure']
        );
        $strRequest = http_build_query($postFieldsAr);

        $authRequest = $this->request("https://api.cdek.ru/v2/oauth/token", $strRequest, 'POST');

        if ($authRequest === false) {
            $this->errors[] = "Ошибка, сервер CDEK вернул ответ не в JSON формате.";
            return false;
        }
        if (isset($authRequest['access_token']) && (($authRequest['access_token'] . "") != "")) {
            $this->token = $authRequest['access_token'];
            return true;
        } else {
            $this->errors[] = "Ошибка, ответ сервера CDEK не содержит свойства access_token или этот параметр пустой.";
            return false;
        }
    }

    private function request($url, $strReq, $method)
    {
        $curl = curl_init();
        $header = array();

        if ($this->token !== '') {
            $header[] = "Authorization: Bearer " . $this->token;
            $header[] = "Content-Type: application/json; charset=utf-8";
        } else {
            $header[] = "Content-Type: application/x-www-form-urlencoded";
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $strReq,
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);

        $error = curl_error($curl);
        if ($error !== '')
            $this->errors[] = $error;

        curl_close($curl);

        return $response;
    }

    public function getPVZ($city_name, &$result_array, &$id_feature, $code_city)
    {
        $sdek_result = [];
        $sdek_city_code = $this->getSDEKCityCode($city_name);

        if ($this->token !== '' && $sdek_city_code) {
            $requestObjectString = '{
                "city_code":"' . $sdek_city_code . '"
            }';

            $sdek_result = $this->request('https://api.cdek.ru/v2/deliverypoints', $requestObjectString, 'GET');
        }

        foreach ($sdek_result as $key => $value) {
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $value['location']['latitude'],
                    $value['location']['longitude']
                ]
            ];
            $features_obj['properties'] = [
                'code_pvz' => $value['code'],
                'fullAddress' => $value['location']['address_full'],
                'deliveryName' => 'СДЭК',
                'iconContent' => 'СДЭК',
                'hintContent' => $value['location']['address']
            ];
            $features_obj['options'] = [
                'preset' => 'islands#darkGreenIcon'
            ];

            $result_array[] = $features_obj;
        }
    }

    public function getSDEKCityCode($cityName)
    {
        if ($this->token !== '') {
            $ar = ['country_codes' => 'RU', 'city' => $cityName];
            $resp = $this->request('https://api.cdek.ru/v2/location/cities', http_build_query($ar), 'GET');
            return $resp['code'];
        }
        return false;
    }

    public function getPrice($array)
    {
        try {
            $sdek_city_code = $this->getSDEKCityCode($array['name_city']);

            if ($this->token !== '' && $sdek_city_code) {

                $requestObjectString = '{
                    "tariff_code":"136",
                    "from_location":{"code": "44"},
                    "to_location":{"code":"' . $sdek_city_code . '", "address": "' . $array['to'] . '"},
                    "packages":[{
                    "weight":"' . $array['weight'] . '"
                    }]
                }';

                $resp = $this->request('https://api.cdek.ru/v2/calculator/tariff', $requestObjectString, 'POST');

                return round($resp['total_sum']);
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;
    }
}