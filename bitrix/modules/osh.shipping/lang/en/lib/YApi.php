<?php

class YApi
{
    //Ответ в формате XML. YMapsML
    public function SearchObjectByAddress($Address, $api_key)
    {
        $url = "http://geocode-maps.yandex.ru/1.x/?apikey=".$api_key."&geocode=" . urlencode($Address) . "&results=1";
        return file_get_contents($url);
    }

    //Ответ в формате XML. YMapsML
    public function SearchObject($Latitude, $Longitude)
    {
        $url = "http://geocode-maps.yandex.ru/1.x/?geocode=$Latitude,$Longitude&results=1";
        $result = file_get_contents($url);
        return $result;
    }

    //Url на Image
    public function GetUrlMapImage($ResultSearchObject, $zPosition, $Width, $Height )
    {
        $point = $this->GetPoint($ResultSearchObject);
        return "http://static-maps.yandex.ru/1.x/?ll=$point&size=$Width,$Height&z=$zPosition&l=map&pt=$point,pm2lbm&lang=ru-RU";
    }

    public function GetPoint($ResultSearchObject)
    {
        $xml = simplexml_load_string($ResultSearchObject);

        $point = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
        $point = str_replace(" ", ",", $point);

        return $point;
    }

    public function GetPointObject($ResultSearchObject)
    {
        return new YApiPoint($this->GetPoint($ResultSearchObject));
    }

    //http://www.movable-type.co.uk/scripts/latlong.html
    public function GetDistance($a, $b)
    {
        $R = 6371; // km
        $dLat = $this->toRad($b->Lat() - $a->Lat());
        $dLon = $this->toRad($b->Long() - $a->Long());
        $lat1 = $this->toRad($a->Lat());
        $lat2 = $this->toRad($b->Lat());

        $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $R * $c;

        return $d;
    }

    public function CheckMkad($address, $api_key)
    {
        if(empty($address)) return;

        $polyhon = YApiPolygon::GetMkadPolygon();
        $xml = $this->SearchObjectByAddress($address, $api_key);
        $coordinates = $this->GetPoint($xml);
        $point = new YApiPoint($coordinates);
        $is_mkad = $polyhon->IsInPolygon($point);
        $close_point = $polyhon->GetClosestPoint($point);
        $distance = $this->GetDistance($close_point, $point);

        $result = array(
            'point' => $point,
            'closest_point' => $close_point,
            'is_mkad' => $is_mkad,
            'distance' => $distance
        );

        return $result;
    }

    protected function toRad($v)
    {
        return $v * pi() / 180;
    }
}