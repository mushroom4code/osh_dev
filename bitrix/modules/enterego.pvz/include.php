<?php

use CommonPVZ\DellindeliveryApicore;

class sdekHelperAllPvz
{
    static $MODULE_ID = "enterego.pvz";
    static $sdek_tarifs = array(233,137,139,16,18,482,480,11,1,3,61,60,59,58,57,83);

    static function getExtraTarifs(){
        $arTarifs = self::$sdek_tarifs;
        $svdOpts = self::get('sdek_tarifs');
        $arReturn = array();
        foreach($arTarifs as $tarifId)
            $arReturn[$tarifId] = array(
                'NAME'  => GetMessage("ENTEREGO_PVZ_SDEK_tarif_".$tarifId."_NAME")." (".$tarifId.")",
                'DESC'  => GetMessage("ENTEREGO_PVZ_SDEK_tarif_".$tarifId."_DESCR"),
                'SHOW'  => (array_key_exists($tarifId, $svdOpts) && array_key_exists('SHOW', $svdOpts[$tarifId]) && $svdOpts[$tarifId]['SHOW']) ? $svdOpts[$tarifId]['SHOW'] : "N",
                'BLOCK' => (array_key_exists($tarifId, $svdOpts) && array_key_exists('BLOCK', $svdOpts[$tarifId]) && $svdOpts[$tarifId]['BLOCK']) ? $svdOpts[$tarifId]['BLOCK']: "N",
            );
        return $arReturn;
    }

    public static function get($option,$noRemake = true)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID,$option,self::getDefault($option));

        if($self && $noRemake) {
            $handlingType = self::getHandling($option);
            switch ($handlingType) {
                case 'serialize' :
                    $self = unserialize($self);
                    break;
                case 'json'      :
                    $self = json_decode($self,true);
                    break;
            }
        }

        return $self;
    }

    public static function getDefault($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt))
            return $opt[$option]['default'];
        return false;
    }

    public static function getHandling($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt) && array_key_exists('handling',$opt[$option]))
            return $opt[$option]['handling'];
        return false;
    }

    public static function collection()
    {
        $arOptions = array(
            'sdek_tarifs' => array(
                'group' => 'addingService',
                'hasHint' => '',
                'default' => 'a:0:{}', // Empty array
                'type' => "special",
                'handling' => 'serialize'
            ),
        );
        return $arOptions;
    }

    public static function getCurrentTarifs() {
        $arTarifs = self::$sdek_tarifs;
        $blocked = self::get('sdek_tarifs');
        if($blocked && count($blocked)){
            foreach($blocked as $key => $val)
                if(!array_key_exists('BLOCK',$val))
                    unset($blocked[$key]);
            if(count($blocked))
                foreach($arTarifs as $arTarifKey => $arTarif)
                    if(array_key_exists($arTarif,$blocked))
                        unset($arTarifs[$arTarifKey]);
        }

        return $arTarifs;
    }
}

CModule::AddAutoloadClasses("", array(
    '\CommonPVZ\DeliveryHelper' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DeliveryHelper.php',
    '\CommonPVZ\CommonPVZ' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/CommonPVZ.php',
    '\CommonPVZ\CommonDoorDeliveryHandler' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/handlerDelivery.php',
    '\CommonPVZ\OshishaDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/OshishaDelivery.php',
    '\CommonPVZ\DoorDeliveryProfile' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DoorDeliveryProfile.php',
    '\CommonPVZ\PVZDeliveryProfile' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/PVZDeliveryProfile.php',
    '\CommonPVZ\SDEKDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/SDEKDelivery.php',
    '\CommonPVZ\RussianPostDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/RussianPostDelivery.php',
    '\CommonPVZ\DellinDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DellinDelivery.php',
    '\CommonPVZ\DellindeliveryApicore' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DellindeliveryApicore.php',
    '\CommonPVZ\DellinPointsTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/DellinPointsTable.php',
    '\CommonPVZ\PEKDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/PEKDelivery.php',
    '\CommonPVZ\FivePostDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/FivePostDelivery.php',
    '\CommonPVZ\PickPointDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/PickPointDelivery.php',
    '\CommonPVZ\PickPointPointsTable' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/PickPointPointsTable.php',
    '\Enterego\EnteregoDBDelivery' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/EnteregoDBDelivery.php',
    '\Enterego\EnteregoDeliveries' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/EnteregoDeliveries.php',
    '\PecomKabinet' => '/bitrix/modules/enterego.pvz/lib/CommonPVZ/pecom_kabinet.php'
));

if ( file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php') )
{
    require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
}