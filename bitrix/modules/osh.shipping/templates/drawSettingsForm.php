<?
global $APPLICATION;

use Bitrix\Main\Loader,
    Osh\Delivery\Options\Helper,
    Osh\Delivery\Options\Config;

Loader::includeModule('sale');

\CJSCore::Init(array('jquery'));

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/options.php');

$MODULE_ID = 'osh.shipping';

$OSH_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if (!($OSH_RIGHT >= 'R'))
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

$aTabs = array(
    array('DIV' => 'osh_edit_settings', 'TAB' => GetMessage('MAIN_TAB_SET'), 'TITLE' => GetMessage('OSH_EDITSETTINGS_TITLE')),
    array('DIV' => 'osh_props_settings', 'TAB' => GetMessage('OSH_ORDER_PROPS'), 'TITLE' => GetMessage('OSH_ORDER_PROPS_TITLE')),
    array('DIV' => 'osh_rights', 'TAB' => GetMessage('MAIN_TAB_RIGHTS'), 'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS')),
);

$tabControl = new \CAdminTabControl('tabControl', $aTabs);
$tabControl->Begin();

$arPvzStrictOptions = array(
    'N' => GetMessage('OSH_SETTINGS_STRICT_PVZ_N'),
    'Y' => GetMessage('OSH_SETTINGS_STRICT_PVZ_Y')
);
$activeModule = array(
    'N' => GetMessage('OSH_SETTINGS_MODULE_N'),
    'Y' => GetMessage('OSH_SETTINGS_MODULE_Y')
);

$arAddressTypeOptions = array(
    Config::ADDRESS_SIMPLE => GetMessage('OSH_SETTINGS_ADDRESS_TYPE_SIMPLE'),
    Config::ADDRESS_COMPLEX => GetMessage('OSH_SETTINGS_ADDRESS_TYPE_COMPLEX')
);

$arOptions = array(
    'osh_edit_settings' => array(
        'active' => array("type" => "select", 'name' => GetMessage('OSH_SETTINGS_ACTIVITY'), "multiple" => false,
            "options" => $activeModule),
        'ymaps_key' => array('type' => 'text', 'size' => '50', 'name' => GetMessage('OSH_SETTINGS_YMAPSKEY_NAME'),
            'hint' => GetMessage('OSH_SETTINGS_YMAPSKEY_HINT')),
        'da_data_token' => array('type' => 'text', 'size' => '50', 'name' => GetMessage('OSH_SETTINGS_DA_DATA_NAME'),
            'hint' => GetMessage('OSH_SETTINGS_DA_DATA_HINT')),
        'cost' => array('type' => 'text', 'size' => '5', 'name' => GetMessage('OSH_SETTINGS_COST')),
        "pvzStrict" => array("type" => "select", "multiple" => false, "options" => $arPvzStrictOptions,
            "name" => GetMessage('OSH_SETTINGS_STRICT_PVZ'), "hint" => GetMessage('OSH_SETTINGS_STRICT_PVZ_HINT')),
    ),
    "delivery_time_period" => array(
        "timeDelivery" => array(
            "name" => GetMessage('OSH_SETTINGS_TIME_DELIVERY_DAY'),
            "type" => "news",
            "id" => 'dayDelivery',
            "elems" => array(
                array("type" => "text", 'size' => '5', 'name' => 'timeDeliveryStartDay[]'),
                array("type" => "text", 'size' => '5', 'name' => 'timeDeliveryEndDay[]')
            )
        ),
        "timeDeliveryNight" => array(
            "name" => GetMessage('OSH_SETTINGS_TIME_DELIVERY_NIGHT'),
            "type" => "news",
            "id" => 'nightDelivery',
            "elems" => array(
                array("type" => "text", 'size' => '5', 'name' => 'timeDeliveryStartNight[]'),
                array("type" => "text", 'size' => '5', 'name' => 'timeDeliveryEndNight[]')
            )
        ),
    ),
    "osh_props_settings" => array(
        "address_type" => array("type" => "select", "multiple" => false, "onchange" => "this.form.submit();",
            "options" => $arAddressTypeOptions, "name" => GetMessage('OSH_SETTINGS_ADDRESS_TYPE'))
    )
);
$arPersonTypes = \COshDeliveryHelper::getOrderProps();

$isAddressSimple = (bool)($oshSettings['address_type'] == Config::ADDRESS_SIMPLE || empty($oshSettings['address_type']));

foreach ($arPersonTypes as $key => $personType) {
    if (!empty($personType["PROPS"])) {
        if ($isAddressSimple) {
            $arOptions['osh_props_settings']["address_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"],
                    "name" => GetMessage('OSH_SETTINGS_ADDRESS_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['osh_props_settings']["time_period_{$key}"] = array("type" => "select", "multiple" => false,
                "options" => $personType["PROPS"],
                "name" => GetMessage('OSH_SETTINGS_TIME_DELIVERY', array("#PERSON_TYPE#" => $personType["NAME"])));
        } else {
            $arOptions['osh_props_settings']["street_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"],
                    "name" => GetMessage('OSH_SETTINGS_STREET_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['osh_props_settings']["bld_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"],
                    "name" => GetMessage('OSH_SETTINGS_BLD_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['osh_props_settings']["corp_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"],
                    "name" => GetMessage('OSH_SETTINGS_CORP_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['osh_props_settings']["flat_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"],
                    "name" => GetMessage('OSH_SETTINGS_FLAT_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['delivery_time_period']["timeDelivery_id_{$key}"] =
                array("timeDelivery" => array("type" => "text", "name" => GetMessage('OSH_SETTINGS_TIME_DELIVERY_DAY')));
        }
    }
}

?>
<style>
    .flex-align-items, .flex-justify-content, .d-flex {
        display: flex;
    }

    .flex-row {
        flex-direction: row;
    }

    .flex-align-items {
        align-items: center;
    }

    .flex-column {
        flex-direction: column;
    }

    .flex-justify-content {
        justify-content: space-between;
    }

    .margin-right-20 {
        margin-right: 20px !important;
    }

    .margin-left-10 {
        margin-left: 10px;
    }

    .padding-10 {
        padding: 10px;
    }

    .button_red {
        border-bottom: none;
        outline: none;
        display: inline-block;
        background: linear-gradient(white, #d9e4e8);
        padding: 7px 33px;
        color: black;
        text-decoration: none;
        box-shadow: 0 2px 3px #b4b4b4;
        border-radius: 4px;
    }

    .button_red:hover{
        text-decoration: none;
    }

</style>
<form method="POST"
      action="<?= $APPLICATION->GetCurPage() . '?mid=' . $MODULE_ID . '&amp;lang=' . LANG . '&amp;mid_menu=1'; ?>"
      name="form1">
    <input type="hidden" name="lang" value="<?= LANG ?>"/>
    <input type="hidden" name="SID" value="<?= htmlspecialchars($SID) ?>"/>
    <input type="hidden" name="Time">
    <?= bitrix_sessid_post() ?>
    <? $tabControl->BeginNextTab(); ?>
    <tr class="heading">
        <td colspan="2"><?= GetMessage('SALE_SERVICE_AREA') ?></td>
    </tr>

    <?
    Helper::generate($arOptions['osh_edit_settings'], $oshSettings);

    Helper::generate($arOptions["delivery_time_period"], $oshSettings);

    $tabControl->BeginNextTab();
    Helper::generate($arOptions["osh_props_settings"], $oshSettings);

    $tabControl->BeginNextTab();

    $module_id = $MODULE_ID;
    $Update = $_REQUEST["Update"] . $_REQUEST["Apply"];
    $REQUEST_METHOD = $_SERVER["REQUEST_METHOD"];
    $GROUPS = $_REQUEST["GROUPS"];
    $RIGHTS = $_REQUEST["RIGHTS"];

    ?>

    <? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php"); ?>
    <? $tabControl->Buttons(); ?>
    <input type="submit" <? if ($OSH_RIGHT < "W") echo "disabled" ?> name="Update"
           value="<? echo GetMessage("MAIN_SAVE") ?>">
    <input type="hidden" name="Update" value="Y">
    <? $tabControl->End(); ?>
    <script type="text/javascript">
        function settingsAddRights(a) {
            let attr_id = a.attributes.dataid.value;
            let array_name_start, array_name_end, child_start_period, child_end_period, remove_button;
            let box_for_strings = document.querySelector('div[data-type=' + attr_id + ']');
            if (attr_id === 'dayDelivery') {
                array_name_start = 'timeDeliveryStartDay[]';
                array_name_end = 'timeDeliveryEndDay[]';
            } else {
                array_name_start = 'timeDeliveryStartNight[]';
                array_name_end = 'timeDeliveryEndNight[]';
            }

            child_start_period = BX.create('INPUT', {
                props: {
                    name: array_name_start,
                    type: 'text',
                    className: 'margin-right-20',
                    size: '5'
                }
            });

            child_end_period = BX.create('INPUT', {
                props: {
                    name: array_name_end,
                    type: 'text',
                    className: 'margin-right-20',
                    size: '5'
                }
            });

            remove_button = BX.create('A', {
                props: {
                    href: 'javascript:void(0)',
                    type: 'text',
                    className: 'flex-align-items',
                    events: {
                        click: settingsDeleteRow()
                    }
                },
                children: [
                    BX.create('IMG', {
                        props: {
                            src: '/bitrix/themes/.default/images/actions/delete_button.gif',
                            border: '0',
                            width: '20',
                            height: '20'
                        }
                    })]
            });

            let tbl = BX.create('DIV', {
                props: {
                    className: 'flex-row d-flex padding-10',
                },
                children: [child_start_period, child_end_period, remove_button]
            });
            BX.append(tbl, box_for_strings);
        }

        function settingsDeleteRow(el) {
            BX.remove(BX.findParent(el, {className: 'padding-10'}));
            return false;
        }
    </script>
</form>
