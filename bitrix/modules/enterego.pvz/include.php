<?php

use Bitrix\Main\Config\Option,
    CommonPVZ\DellindeliveryApicore,
    Bitrix\Main\Entity,
    Bitrix\Sale\Internals\PersonTypeTable,
    CommonPVZ\DeliveryHelper;

class HelperAllDeliveries
{
    static $MODULE_ID = "enterego.pvz";
    static $sdek_tarifs = array(136,137,138,139,233,234,1,3,5,10,11,12,15,16,17,18,57,58,59,60,61,62,63,483,482,481,480,83,378,376,368,366,363,361,486,485);
    const OSHISHA_ADDRESS_SIMPLE = "simple";
    const OSHISHA_ADDRESS_COMPLEX = "complex";
    static $oshisha_fields = array(
        'active',
        'pvzStrict',
        'address_type',
        'ymaps_key',
        'da_data_token',
        'timeDeliveryEndNight',
        'timeDeliveryStartNight',
        'timeDeliveryStartDay',
        'timeDeliveryEndDay',
        'cost',
//        'deduct',
//        'bitrix_stock',
//        'quantity_override'
    );

    static function getSdekExtraTarifs(){
        $arTarifs = self::$sdek_tarifs;
        $svdOpts = self::sdekGet('sdek_tarifs');
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

    public static function sdekGet($option,$noRemake = true)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID,$option,self::sdekGetDefault($option));

        if($self && $noRemake) {
            $handlingType = self::sdekGetHandling($option);
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

    public static function sdekGetDefault($option)
    {
        $opt = self::sdekCollection();
        if(array_key_exists($option,$opt))
            return $opt[$option]['default'];
        return false;
    }

    public static function sdekGetHandling($option)
    {
        $opt = self::sdekCollection();
        if(array_key_exists($option,$opt) && array_key_exists('handling',$opt[$option]))
            return $opt[$option]['handling'];
        return false;
    }

    public static function sdekCollection()
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

    static function getSdekTarifList($params=array()){
        $arList = array(
            'pickup'  => array(
                'usual'   => array(234,136,138),
                'heavy'   => array(15,17),
                'express' => array(483,481,62,63,5,10,12)
            ),
            'courier' => array(
                'usual'   => array(233,137,139),
                'heavy'   => array(16,18),
                'express' => array(482,480,11,1,3,61,60,59,58,57,83)
            ),
            'postamat' => array(
                'usual' => array(378,376,368,366),
                'express' => array(363,361,486,485)
            )
        );
        $blocked = self::sdekGet('sdek_tarifs');
        if($blocked && count($blocked) && (!array_key_exists('fSkipCheckBlocks',$params) || !$params['fSkipCheckBlocks'])){
            foreach($blocked as $key => $val)
                if(!array_key_exists('BLOCK',$val))
                    unset($blocked[$key]);
            if(count($blocked))
                foreach($arList as $tarType => $arTars)
                    foreach($arTars as $tarMode => $arTarIds)
                        foreach($arTarIds as $key => $arTarId)
                            if(array_key_exists($arTarId,$blocked))
                                unset($arList[$tarType][$tarMode][$key]);
        }
        $answer = $arList;
        if($params['type']){
            if(is_numeric($params['type'])) $type = ($params['type']==136)?$type='pickup':$type='courier';
            else $type = $params['type'];
            $answer = $answer[$type];

            if((array_key_exists('mode', $params) && $params['mode']) && array_key_exists($params['mode'], $answer))
                $answer = $answer[$params['mode']];
        }

        if(array_key_exists('answer',$params)){
            $answer = self::sdekArrVals($answer);
            if($params['answer'] == 'string'){
                $answer = implode(',',$answer);
                $answer = substr($answer,0,strlen($answer));
            }
        }
        return $answer;
    }

    static function sdekArrVals($arr){
        $return = array();
        foreach($arr as $key => $val)
            if(is_array($val))
                $return = array_merge($return,self::sdekArrVals($val));
            else
                $return []= $val;
        return $return;
    }

    public static function getOshishaDataValue($name)
    {
        return Option::get(self::$MODULE_ID, "Oshisha_{$name}");
    }

    public static function getOshishaYMapsKey()
    {
        return self::getOshishaDataValue('ymapskey');
    }

    public static function getOshishaDaDataToken()
    {
        return self::getOshishaDataValue('dadatatoken');
    }

    public static function getOshishaCost()
    {
        return self::getOshishaDataValue('cost');
    }

    public static function getOshishaStartCost()
    {
        return 299;
    }

    public static function getOshishaLimitBasket(){
        return 4000;
    }

    public static function getOshishaPersonTypes(){
        $arSelectPt = array("ID","NAME_WS");
        $arOrderPt = array("SORT" => "ASC");
        $arFilter = array("ACTIVE" => "Y");
        $arRuntime = array(new Entity\ExpressionField('NAME_WS', "CONCAT('(',%s,') ',%s)",array("LID","NAME")));
        $dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelectPt, "order" => $arOrderPt,
            "filter" => $arFilter, "runtime" => $arRuntime));
        $arPersonTypes = array();
        while($arPersonType = $dbPersonTypes->fetch()){
            $arPersonTypes[$arPersonType["ID"]] = array("NAME" => $arPersonType["NAME_WS"]);
        }
        return $arPersonTypes;
    }

    public static function getOshishaOptionsData()
    {
        $data = array();
        foreach (self::$oshisha_fields as $field) {
            $data[$field] = self::getOshishaDataValue($field);
        }
//        $this->data['debug'] = boolval($this->data['debug']);
//        $this->data['direct'] = boolval($this->data['direct']);
        $arPersonTypes = self::getOshishaPersonTypes();
        $isAddressSimple = boolval($data["address_type"] != self::OSHISHA_ADDRESS_COMPLEX);
//        if($isAddressSimple) {
//            $this->data["mirror_pvz_address"] = boolval($this->getDataValue('mirror_pvz_address'));
//        }
        foreach ($arPersonTypes as $id => $name) {
//            $this->fields[] = 'pvz_prop_'.$id;
//            $this->data['pvz_prop_'.$id] = $this->getDataValue('pvz_prop_'.$id);
            if ($isAddressSimple) {
                self::$oshisha_fields[] = 'address_prop_id_' . $id;
                self::$oshisha_fields[] = 'time_period_' . $id;
                $data['address_prop_id_' . $id] = self::getOshishaDataValue('address_prop_id_' . $id);
                $data['time_period_' . $id] = self::getOshishaDataValue('time_period_' . $id);

            } else {
                self::$oshisha_fields[] = 'street_prop_id_' . $id;
                $data['street_prop_id_' . $id] = self::getOshishaDataValue('street_prop_id_' . $id);
                self::$oshisha_fields[] = 'corp_prop_id_' . $id;
                $data['corp_prop_id_' . $id] = self::getOshishaDataValue('corp_prop_id_' . $id);
                self::$oshisha_fields[] = 'bld_prop_id_' . $id;
                $data['bld_prop_id_' . $id] = self::getOshishaDataValue('bld_prop_id_' . $id);
                self::$oshisha_fields[] = 'flat_prop_id_' . $id;
                $data['flat_prop_id_' . $id] = self::getOshishaDataValue('flat_prop_id_' . $id);
            }
            return $data;
        }
    }

    public static function generate($arOptions, $arConfigData)
    {
        foreach ($arOptions as $optionName => $arOption):?>
            <tr>
            <?php if ($arOption['type'] === 'news') { ?>
                <tr class="heading" dataId="<?= $arOption["id"] ?>">
                    <td colspan="2">
                        <?= $arOption["name"] ?>
                    </td>
                </tr>
            <?php } ?>
            <? if ($arOption["type"] == "NOTE"): ?>
                <td colspan="2" align="center">
                    <?= BeginNote(); ?>
                    <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;"/><?= $arOption["name"] ?>
                    <?= EndNote(); ?>
                </td>
            <? elseif ($arOption["type"] == "heading"): ?>
                <td colspan="2" class="heading">
                    <?= $arOption["name"] ?>
                </td>
            <?
            else:
                $value = isset($arConfigData[$optionName]) ? $arConfigData[$optionName] : $arOption["default"];

                if ($arOption["type"] !== 'news') {
                    ?>
                    <td class="field-name" style="width:45%"><?= $arOption["name"] ?>:</td>
                <?php } else { ?><td class="field-name" style="width:45%"></td><?php } ?>
                <td>
                    <? switch ($arOption["type"]):
                        case "text":
                        case "email":
                        case "number";
                            $arOption["name"] = $optionName;
                            $arOption["value"] = $value;
                            echo self::input($arOption, '');
                            break;
                        case "textarea":
                            $arParams = array(
                                "ATTRS" => array(
                                    "name" => $optionName
                                ),
                                "VALUE" => $value
                            );
                            echo self::pairTag($arOption["type"], $arParams);
                            break;
                        case "news":
                            $elem = '';
                            $id = $arOption["id"];
                            if ($id === 'dayDelivery') {
                                $start_json = Option::get('osh.shipping', 'osh_timeDeliveryStartDay');
                                $end_json = Option::get('osh.shipping', 'osh_timeDeliveryEndDay');
                            } else {
                                $start_json = Option::get('osh.shipping', 'osh_timeDeliveryStartNight');
                                $end_json = Option::get('osh.shipping', 'osh_timeDeliveryEndNight');
                            }

                            $start = json_decode($start_json);
                            $end = json_decode($end_json);

                            $delete = "<a href='javascript:void(0)' class='flex-align-items' 
                                        onClick='settingsDeleteRow(this)'> 
                                       <img src='/bitrix/themes/.default/images/actions/delete_button.gif' 
                                        border='0' width='20' height='20'/></a>";

                            if (count($start) == 0) {
                                $start[] = '';
                                $end[] = '';
                            }
                            $dayItem = $arOption["elems"][0];
                            $nightItem = $arOption["elems"][1];

                            foreach ($start as $key => $elems_start) {

                                $elem .= "<div class='flex-row d-flex padding-10'>";
                                $dayItem['value'] = $elems_start;
                                $elem .= self::input($dayItem, 'elems');

                                $nightItem['value'] = $end[$key];
                                $elem .= self::input($nightItem, 'elems');

                                $elem .= $delete;
                                $elem .= "</div>";
                            }
                            $dop_insert = "<a href='javascript:void(0)' onclick='settingsAddRights(this)' 
                                    hidefocus='true' class='button_red' dataId='$id'>Добавить поле </a>";

                            echo "<div class='flex-justify-content flex-column'><div data-type='$id' id='$id'>$elem</div>
                              <div><div class='margin-left-10'>$dop_insert</div> 
                              </div></div>";


                            break;
                        case "select":
                            if ($arOption["multiple"]) {
                                $values = explode("|", $value);
                            } else {
                                $values = [$value];
                            }
                            ?>

                            <select name="<?= $optionName ?><? if ($arOption["multiple"]): ?>[]<? endif ?>"
                                <?php if ($arOption["multiple"]): ?>
                                    multiple
                                    <?php if ($arOption["size"]): ?>
                                        size="<?php echo $arOption["size"] ?>"
                                    <? endif ?>
                                <? endif ?>
                                    <? if ($arOption["onchange"]): ?>onChange="<?= $arOption["onchange"] ?>"<? endif ?>
                            >
                                <? foreach ($arOption["options"] as $id => $text): ?>
                                    <option <? if (in_array($id, $values)) echo " selected " ?>
                                            value="<?= $id ?>"><?= $text ?></option>
                                <? endforeach ?>

                            </select>
                            <? break;
                    endswitch ?>
                    <? if (!empty($arOption["hint"])): ?>
                        <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;cursor:pointer"
                             title="<?= $arOption["hint"] ?>"
                            <? if (!empty($arOption["href"])): ?>
                                onclick="window.open('<?= $arOption["href"] ?>');"
                            <? endif ?>
                        />
                    <? endif ?>
                    <? if (!empty($arOption["link"])): ?>
                        <a href="<?= $arOption["link"]['href'] ?>" target="blank"
                           style="padding-left: 10px;"><?= $arOption["link"]['text'] ?></a>
                    <? endif ?>
                </td>
            <? endif ?>

            </tr><?
        endforeach;
    }

    static function input($arParams, string $param)
    {
        if ($param === 'elems') {
            return self::singleTag("input", $arParams);
        } else {
            return self::generateHtml("input", $arParams);
        }
    }

    public
    static function singleTag($tag, $tagParams)
    {
        $params = array();
        foreach ($tagParams as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }

        return "<$tag " . implode(" ", $params) . " class='margin-right-20' />";
    }

    public
    static function generateHtml($tagName, $tagParams)
    {
        $params = array();
        foreach ($tagParams as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }

        return "<{$tagName} " . implode(" ", $params) . " />";
    }

    public static function getDeliveriesStatuses() {
        $deliveriesStatuses = [];
        foreach (DeliveryHelper::getConfigs() as $delivery => $values) {
            $deliveriesStatuses[$delivery] = $values['active'];
        }
        return $deliveriesStatuses;
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

if(!\CJSCore::IsExtRegistered('osh_pickup')){
    \CJSCore::RegisterExt(
        "osh_pickup",
        array(
            "js" => "/bitrix/js/".HelperAllDeliveries::$MODULE_ID."/pickup.js",
            "css" => "/bitrix/css/".HelperAllDeliveries::$MODULE_ID."/styles.css",
            "lang" => "/bitrix/modules/".HelperAllDeliveries::$MODULE_ID."/lang/".LANGUAGE_ID."/js/pickup.php",
            "rel" => Array("ajax","popup"),
            "skip_core" => false,
        )
    );
}