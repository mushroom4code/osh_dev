<?php

namespace CommonPVZ;


class SDEKDelivery extends CommonPVZ
{
    protected $delivery_name = 'SDEK';
    private string $token = '';

    protected function connect()
    {
        $postFieldsAr = array(
            "grant_type" => "client_credentials",
            "client_id" => $this->configs['setaccount'],
            "client_secret" => $this->configs['setsecure']
        );
        $strRequest = http_build_query($postFieldsAr);

        $authRequest = $this->request("https://api.cdek.ru/v2/oauth/token", $strRequest, 'POST');

        if ($authRequest === false) {
            $this->errors[] = "Ошибка, сервер CDEK вернул ответ не в JSON формате.";
            return false;
        }
        if (isset($authRequest->access_token) && !empty($authRequest->access_token)) {
            $this->token = $authRequest->access_token;
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
        if ($method==='GET') {
            $url .= "?$strReq";
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $strReq,
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error !== '') {
            $this->errors[] = $error;
            return false;
        }

        return json_decode($response);
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city)
    {
        $sdek_result = [];
        $sdek_city_code = $this->getSDEKCityCode($city_name);

        if ($this->token !== '' && $sdek_city_code) {
            $searchParams = ['city_code'=>$sdek_city_code];
            $sdek_result = $this->request('https://api.cdek.ru/v2/deliverypoints',
                http_build_query($searchParams), 'GET');
        }

        foreach ($sdek_result as $value) {
            $features_obj['type'] = 'Feature';
            $features_obj['id'] = $id_feature;
            $id_feature += 1;
            $features_obj['geometry'] = [
                'type' => 'Point',
                'coordinates' => [
                    $value->location->latitude,
                    $value->location->longitude
                ]
            ];
            $features_obj['properties'] = [
                'code_pvz' => $value->code,
                'type' => $value->type === 'PVZ' ? "PVZ" : 'POSTAMAT',
                'workTime'=> $value->work_time,
                'comment' => $value->address_comment,
                'fullAddress' => $value->location->address_full,
                'deliveryName' => 'СДЭК',
                'iconCaption' => 'СДЭК',
                'hintContent' => $value->location->address,
                "openEmptyBalloon" => true,
                "clusterCaption" => 'СДЭК',
            ];
            if (!empty($value->phones)) {
                $features_obj['properties']['phone'] = $value->phones[0]->number;
            }
            $features_obj['options'] = [
                'preset' => 'islands#darkGreenIcon'
            ];

            $result_array[] = $features_obj;
        }
    }

    /** TODO find by name not unambiguous result
     * @param $cityName
     * @return false|string
     */
    private function getSDEKCityCode($cityName)
    {
        if ($this->token !== '') {
            $ar = ['country_codes' => 'RU', 'city' => $cityName];
            $resp = $this->request('https://api.cdek.ru/v2/location/cities', http_build_query($ar), 'GET');
            if (!empty($resp) && count($resp)>0 && isset($resp[0]->code)) {
                return $resp[0]->code;
            }
        }
        return false;
    }

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    public function getPrice($array)
    {
        try {
            $sdek_city_code = $this->getSDEKCityCode($array['name_city']);

            if ($this->token !== '' && $sdek_city_code) {
                $searchParams = [
                    'tariff_code' => $this->configs['tariff'],
                    'from_location' => ['code'=>$this->configs['from']],
                    'to_location' => ['code'=>$sdek_city_code, 'address'=>$array['to']],
                    'packages' => ['weight'=>$array['weight']],
                ];

                $resp = $this->request('https://api.cdek.ru/v2/calculator/tariff',
                    json_encode($searchParams), 'POST');

                //todo errors calculation
                if (isset($resp->errors)) {
                    return false;
                }
                if (isset($resp->total_sum)) {
                    return round($resp->total_sum);
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        return 0;
    }
}