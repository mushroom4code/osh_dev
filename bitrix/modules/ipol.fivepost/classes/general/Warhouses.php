<?php

namespace Ipol\Fivepost;


use Ipol\Fivepost\Bitrix\Controller\Warehouse;
use Ipol\Fivepost\Bitrix\Tools;

class Warhouses extends AbstractGeneral
{
    protected static function getFilePath(){
        return $_SERVER['DOCUMENT_ROOT'].self::getRelativePath();
    }

    public static function getRelativePath(){
        return Tools::getToolsPath().'wh.json';
    }

    public static function getWHInfo(){
        if(file_exists(self::getFilePath())){
            return json_decode(file_get_contents(self::getFilePath()),true);
        } else {
            return false;
        }
    }

    public static function getAjaxWH(){
        echo json_encode(self::getWHInfo());
    }

    public static function addAjaxWhInfo($request){
        $arUnsetted = array('action','WH_regionC');
        foreach ($arUnsetted as $key){
            if(array_key_exists(self::getMODULELBL().$key,$request)){
                unset($request[self::getMODULELBL().$key]);
            }
        }

        $result = self::addWHInfo($request);

        echo json_encode(array(
            'success' => $result->isSuccess(),
            'error'   => $result->getErrorText()
        ));
    }

    public static function writeWHInfo($params){
        if(!file_exists($_SERVER['DOCUMENT_ROOT'].Tools::getToolsPath())){
            mkdir($_SERVER['DOCUMENT_ROOT'].Tools::getToolsPath());
        }
        $arExisted = self::getWHInfo();
        if(!$arExisted){
            $arExisted = array();
        }
        $arExisted []= $params;

        file_put_contents(self::getFilePath(),json_encode($arExisted));
    }

    protected static function addWarehouse($arData){
        $controller = new Warehouse();
        return $controller->fromRequest($arData)
                           ->addWarehouse();
    }

    public static function addWHInfo($arData){

        $resultAdding = self::addWarehouse($arData);

        if($resultAdding->isSuccess()) {
            self::writeWHInfo($arData);
        }

        return $resultAdding;
    }
}