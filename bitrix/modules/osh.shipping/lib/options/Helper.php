<?php

namespace Osh\Delivery\Options;

use Bitrix\Main\Config\Option;

class Helper
{
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

//    public static function checkbox($arOption){
//        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
//        $inpParams = array(
//            "type" => $arOption[3][0],
//            "id" => htmlspecialchars($arOption[0]),
//            "class" => "adm-designed-checkbox",
//            "name" => htmlspecialchars($arOption[0]),
//            "value" => "Y"
//        );
//        if($val == "Y"){
//            $inpParams["checked"] = "checked";
//        }
//        $labelParams = array(
//            "ATTRS" => array(
//                "for" => htmlspecialchars($arOption[0]),
//                "class" => "adm-designed-checkbox-label"
//            )
//        );
//        return self::input($inpParams).self::label($labelParams);
//    }
//
    public
    static function text($arOption)
    {
        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
        $inpParams = array(
            "type" => $arOption[3][0],
            "id" => htmlspecialchars($arOption[0]),
            "maxlength" => 255,
            "size" => $arOption[3][1],
            "name" => htmlspecialchars($arOption[0]),
            "value" => htmlspecialchars($val)
        );
        return self::input($inpParams, '');
    }

//    public static function password($arOption)
//    {
//        return self::text($arOption);
//    }
//
//    public static function number($arOption)
//    {
//        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
//        $inpParams = array(
//            "type" => $arOption[3][0],
//            "id" => htmlspecialchars($arOption[0]),
//            "min" => $arOption[3][3],
//            "max" => $arOption[3][2],
//            "size" => $arOption[3][1],
//            "name" => htmlspecialchars($arOption[0]),
//            "value" => htmlspecialchars($val),
//            "style" => 'border-radius:4px;min-height:17px;border: 1px solid #a3a5a5;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);padding:5px;width:50px'
//        );
//        return self::input($inpParams);
//    }
//
//    public static function textarea($arOption)
//    {
//        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
//        $taParams = array(
//            "ATTRS" => array(
//                "rows" => $arOption[3][1],
//                "cols" => $arOption[3][2],
//                "name" => htmlspecialchars($arOption[0])
//            ),
//            "VALUE" => htmlspecialchars($val)
//        );
//        return self::pairTag("textarea", $taParams);
//    }
//
//    public static function select($arOption)
//    {
//        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
//        $seParams = array(
//            "ATTRS" => array(
//                "size" => $arOption[3][1],
//                "name" => htmlspecialchars($arOption[0])
//            ),
//            "VALUE" => ""
//        );
//        foreach($arOption[3][2] as $item) {
//            $opParams = array(
//                "ATTRS" => ["value" => $item["ID"]],
//                "VALUE" => $item["NAME"]
//            );
//            if($item["ID"] == $val) {
//                $opParams["ATTRS"]["selected"] = "selected";
//            }
//            $seParams["VALUE"] .= self::pairTag("option", $opParams);
//        }
//        return self::pairTag("select", $seParams);
//    }
//
    /**
     * @param $arParams
     * @param string $param
     * @return string
     */
    public
    static function input($arParams, string $param)
    {
        if ($param === 'elems') {
            return self::singleTag("input", $arParams);
        } else {
            return self::generateHtml("input", $arParams);
        }

    }

//
//    public static function label($arParams)
//    {
//        return self::pairTag("label", $arParams);
//    }
//
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

//
    public
    static function pairTag($tagName, $tagParams)
    {
        $params = array();
        $innerHTML = $tagParams["VALUE"];
        foreach ($tagParams["ATTRS"] as $name => $value) {
            $params[] = $name . '="' . $value . '"';
        }
        return "<{$tagName} " . implode(" ", $params) . " >" . $innerHTML . "</{$tagName}>";
    }
}