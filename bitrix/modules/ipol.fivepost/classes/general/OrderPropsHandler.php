<?

namespace Ipol\Fivepost;

use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Bitrix\Handler\Deliveries;

IncludeModuleLangFile(__FILE__);

class OrderPropsHandler extends abstractGeneral
{
    protected static $PVZprop     = 'PVZ';

    public static function onOrderCreate($oId,$arFields)
    {
        if(
            !\cmodule::includemodule('sale') ||
            !Deliveries::isActive() ||
            !self::controlProps()
        )
            return;

        if(Tools::getArrVal(PvzWidgetHandler::getSavingLink(),$_REQUEST)){
            $op = \CSaleOrderProps::GetList(array(),array("PERSON_TYPE_ID" =>$arFields['PERSON_TYPE_ID'],"CODE"=>self::$MODULE_LBL."PVZ"))->Fetch();
            if($op)
                self::saveProp(array(
                    "ORDER_ID"       => $oId,
                    "ORDER_PROPS_ID" => $op['ID'],
                    "NAME"           => Tools::getMessage('prop_PVZ_name'),
                    "CODE"           => self::$MODULE_LBL.self::getPVZprop(),
                    "VALUE"          => $_REQUEST[PvzWidgetHandler::getSavingLink()]
                ));
        }
    }

    public static function saveProp($arPropFields){
        if(!\CSaleOrderPropsValue::Add($arPropFields)){
            $prop = \CSaleOrderPropsValue::GetList(array(),array("ORDER_ID" => $arPropFields['ORDER_ID'],"ORDER_PROPS_ID" => $arPropFields['ORDER_PROPS_ID']))->Fetch();
            if($prop && !$prop['VALUE'])
                \CSaleOrderPropsValue::Update($prop['ID'],$arPropFields);
        }
    }

    public static function controlProps($mode=1){//1-add/update, 2-delete
        if(!\CModule::IncludeModule("sale"))
            return false;
        $arProps = array(
            array(// PVZ
                  'CODE'  => self::$MODULE_LBL.self::getPVZprop(),
                  'NAME'  => Tools::getMessage('prop_PVZ_name'),
                  'DESCR' => Tools::getMessage('prop_PVZ_descr')
            )
        );

        $return = true;
        foreach($arProps as $prop){
            $subReturn = self::handleProp($prop,$mode);
            if(!$subReturn)
                $return = $subReturn;
        }
        return $return;
    }

    protected static function handleProp($arProp,$mode){
        $tmpGet=\CSaleOrderProps::GetList(array("SORT" => "ASC"),array("CODE" => $arProp['CODE']));
        $existedProps=array();
        while($tmpElement=$tmpGet->Fetch())
            $existedProps[$tmpElement['PERSON_TYPE_ID']]=$tmpElement['ID'];

        if($mode=='1'){
            $return = true;
            $tmpGet = \CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
            $allPayers=array();
            while($tmpElement=$tmpGet->Fetch())
                if($tmpElement['ACTIVE'] == 'Y')
                    $allPayers[]=$tmpElement['ID'];

            foreach($allPayers as $payer){
                $tmpGet = \CSaleOrderPropsGroup::GetList(array("SORT" => "ASC"),array("PERSON_TYPE_ID" => $payer),false,array('nTopCount' => '1'));
                $tmpVal=$tmpGet->Fetch();
                $arFields = array(
                    "PERSON_TYPE_ID" => $payer,
                    "NAME" => $arProp['NAME'],
                    "TYPE" => "TEXT",
                    "REQUIED" => "N",
                    "DEFAULT_VALUE" => "",
                    "SORT" => 100,
                    "CODE" => $arProp['CODE'],
                    "USER_PROPS" => "N",
                    "IS_LOCATION" => "N",
                    "IS_LOCATION4TAX" => "N",
                    "PROPS_GROUP_ID" => $tmpVal['ID'],
                    "SIZE1" => 10,
                    "SIZE2" => 1,
                    "DESCRIPTION" => $arProp['DESCR'],
                    "IS_EMAIL" => "N",
                    "IS_PROFILE_NAME" => "N",
                    "IS_PAYER" => "N",
                    "IS_FILTERED" => "Y",
                    "IS_ZIP" => "N",
                    "UTIL" => "Y"
                );

                if(!array_key_exists($payer,$existedProps))
                    if(!\CSaleOrderProps::Add($arFields))
                        $return = false;
            }
            return $return;
        }
        if($mode=='2'){
            foreach($existedProps as $existedPropId) {
                if (!\CSaleOrderProps::Delete($existedPropId))
                    echo "Error delete prop id" . $existedPropId . "<br>";
            }

            return true;
        }

        return false;
    }

    public static function onBeforeOrderCreate(&$order,&$arFields)
    {

        return false;
    }

    /**
     * @return string
     */
    public static function getPVZprop()
    {
        return self::$PVZprop;
    }
}