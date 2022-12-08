<?php
namespace Osh\Delivery\Helpers;

class Pvz{
    public static function checkConsistency($arPvz){
        $center = ['lat' => 0, 'lon' => 0];
        $current = ['lat' => 0, 'lon' => 0];
        foreach($arPvz as $i => $pvz){
            $current['lat'] = floatval($pvz['gps_location']['latitude']);
            $current['lon'] = floatval($pvz['gps_location']['longitude']);
            if(is_nan($current['lat']) || is_nan($current['lon']) || self::checkInconsistency($current, $center)){
                $arPvz[$i]['consistent'] = false;
            }else{
                $arPvz[$i]['consistent'] = true;
            }
            $center['lat'] = ($center['lat'] * $i + $current['lat'])/($i + 1);
            $center['lon'] = ($center['lon'] * $i + $current['lon'])/($i + 1);
        }
        return $arPvz;
    }
    public static function checkInconsistency($current, $center){
        $notFirst = boolval($center['lat'] > 0);
        $consistent = array(
            'lat' => boolval(intval($current['lat']) < intval($center['lat']) + 2) && boolval(intval($current['lat']) > intval($center['lat']) - 2),
            'lon' => boolval(intval($current['lon']) < intval($center['lon']) + 2) && boolval(intval($current['lon']) > intval($center['lon']) - 2),
        );
        return $notFirst && !($consistent['lat'] && $consistent['lon']);
    }
}