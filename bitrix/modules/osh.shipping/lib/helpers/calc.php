<?php
namespace Osh\Delivery\Helpers;

use Osh\Delivery\OshHandler;

class Calc{
    public static function effectiveDimensions($arOrder){
        $arBasket = $arOrder['ORDER']['ITEMS'];
        $arDimensionFields = array('WIDTH', 'HEIGHT', 'LENGTH');
        $defaultDimensions = array(
            'LENGTH' => $arOrder['CONFIG']['MAIN']['LENGTH_VALUE'],
            'WIDTH' => $arOrder['CONFIG']['MAIN']['WIDTH_VALUE'],
            'HEIGHT' => $arOrder['CONFIG']['MAIN']['HEIGHT_VALUE']
        );
        $sumVolume = 0;
        $arDimensions = array();
        $arEffectiveDimensions = array();
        foreach($arBasket as $arItem){
            foreach($arDimensionFields as $field){
                if(empty($arItem[$field])){
                    $arItem[$field] = $defaultDimensions[$field];
                }else{
                    $arItem[$field] = $arItem[$field]/10;
                }
                $arDimensions[$field][] = $arItem[$field];
            }
            $sumVolume += $arItem['WIDTH'] * $arItem['HEIGHT'] * $arItem['LENGTH'] * $arItem['QUANTITY'];
        }
        $effectiveDimension = round(ceil(pow($sumVolume,1/3)*10)/10,1);
        $maxDimensionKey = null;
        $maxDimensionValue = $effectiveDimension;
        foreach($arDimensions as $key => $arItems){
            $arItems = array_unique($arItems);
            $maxDimension = max($arItems);
            if($maxDimensionValue < $maxDimension){
                $maxDimensionKey = $key;
                $maxDimensionValue = $maxDimension;
            }
        }
        if($maxDimensionKey === null){
            $arEffectiveDimensions = ['WIDTH' => ceil($maxDimensionValue), 'HEIGHT' => ceil($maxDimensionValue),
                'LENGTH' => ceil($maxDimensionValue)];
        }else{
            $arEffectiveDimensions['LENGTH'] = $maxDimensionValue;
            $remainingEffectiveDimension = round(ceil(sqrt($sumVolume/$maxDimensionValue)),1);
            foreach($arDimensions as $key => $arItems){
                if($key == 'LENGTH'){
                    continue;
                }
                $arEffectiveDimensions[$key] = ceil($remainingEffectiveDimension);
            }
        }
        return $arEffectiveDimensions;
    }
    public static function weightToKg($weight){
        return round($weight/1000, 3);
    }
    public static function orderWeight($arOrder){
        switch($arOrder['CONFIG']['MAIN']['CALC_ALGORITM']){
            case OshHandler::CALC_ALGO_WARE: case OshHandler::CALC_ALGO_WEIGHT:
                $weight = 0;
                foreach($arOrder['ORDER']['ITEMS'] as $arItem){
                    if($arItem['WEIGHT'] > 0){
                        $weight += $arItem['WEIGHT']*$arItem['QUANTITY'];
                    }else{
                        $weight += $arOrder['CONFIG']['MAIN']['WEIGHT_VALUE']*$arItem['QUANTITY'];
                    }
                }
                break;
            default:
                $weight = $arOrder['CONFIG']['MAIN']['WEIGHT_VALUE'];
        }
        return ceil($weight / 0.01) * 0.01;
    }
    public static function orderDimensions($arOrder){
        if(count($arOrder['ORDER']['ITEMS']) == 1 && !empty($arOrder['ORDER']['ITEMS'][0]['WIDTH'])
                && !empty($arOrder['ORDER']['ITEMS'][0]['HEIGHT']) && !empty($arOrder['ORDER']['ITEMS'][0]['LENGTH'])
                && $arOrder['ORDER']['ITEMS'][0]['QUANTITY'] == 1){
            $arOrderDimensions = array(
                'LENGTH' => ceil($arOrder['ORDER']['ITEMS'][0]['LENGTH']/10),
                'WIDTH' => ceil($arOrder['ORDER']['ITEMS'][0]['WIDTH']/10),
                'HEIGHT' => ceil($arOrder['ORDER']['ITEMS'][0]['HEIGHT']/10)
            );
        }else{
            switch($arOrder['CONFIG']['MAIN']['CALC_ALGORITM']){
                case OshHandler::CALC_ALGO_WARE:
                    $arOrderDimensions = self::effectiveDimensions($arOrder);
                    break;
                default:case OshHandler::CALC_ALGO_WEIGHT:
                    $arOrderDimensions = array(
                        'LENGTH' => $arOrder['CONFIG']['MAIN']['LENGTH_VALUE'],
                        'WIDTH' => $arOrder['CONFIG']['MAIN']['WIDTH_VALUE'],
                        'HEIGHT' => $arOrder['CONFIG']['MAIN']['HEIGHT_VALUE'],
                    );
            }
        }
        return $arOrderDimensions;
    }
}