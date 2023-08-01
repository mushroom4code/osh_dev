<?php

/** @var $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);
Loader::includeModule($module_id);

$oshishaArPvzStrictOptions = array(
    'N' => GetMessage('OSH_SETTINGS_STRICT_PVZ_N'),
    'Y' => GetMessage('OSH_SETTINGS_STRICT_PVZ_Y')
);
$activeDelivery = array(
    'N' => GetMessage('ACTIVE_DELIVERY_N'),
    'Y' => GetMessage('ACTIVE_DELIVERY_Y')
);
$daysOfTheWeek = array(
    '1' => 'понедельник',
    '2' => 'вторник',
    '3' => 'среда',
    '4' => 'четверг',
    '5' => 'пятница',
    '6' => 'суббота',
    '0' => 'воскресенье'
);

$aTabs = array(
    array(
        'DIV' => 'common',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_COMMON'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_COMMON'),
        'OPTIONS' => array(
            array(
                'Common_defaultwidth',
                Loc::getMessage('Common_defaultwidth'),
                Option::get($module_id, 'Common_defaultwidth'),
                array('text')
            ),
            array(
                'Common_defaultlength',
                Loc::getMessage('Common_defaultlength'),
                Option::get($module_id, 'Common_defaultlength'),
                array('text')
            ),
            array(
                'Common_defaultheight',
                Loc::getMessage('Common_defaultheight'),
                Option::get($module_id, 'Common_defaultheight'),
                array('text')
            ),
            array(
                'Common_defaultweight',
                Loc::getMessage('Common_defaultweight'),
                Option::get($module_id, 'Common_defaultweight'),
                array('text')
            ),
            array(
                'Common_iscacheon',
                Loc::getMessage('Common_iscacheon'),
                Option::get($module_id, 'Common_iscacheon'),
                array('checkbox')
            ),
        )
    ),
    // PickPoint
    array(
        'DIV' => 'pickpoint',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_PP'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_PP'),
        'OPTIONS' => array(
            array(
                'PickPoint_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'PickPoint_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'PickPoint_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'PickPoint_door_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'PickPoint_host',
                Loc::getMessage('PickPoint_host'),
                Option::get($module_id, 'PickPoint_host'),
                array('text')
            ),
            array(
                'PickPoint_login',
                Loc::getMessage('PickPoint_login'),
                Option::get($module_id, 'PickPoint_login'),
                array('text')
            ),
            array(
                'PickPoint_password',
                Loc::getMessage('PickPoint_password'),
                Option::get($module_id, 'PickPoint_password'),
                array('text')
            ),
            array(
                'PickPoint_ikn',
                Loc::getMessage('PickPoint_ikn'),
                Option::get($module_id, 'PickPoint_ikn'),
                array('text')
            ),
        )
    ),
    // 5post
    array(
        'DIV' => '5post',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_FP'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_FP'),
        'OPTIONS' => array(
            array(
                'FivePost_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'FivePost_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'FivePost_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'FivePost_door_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'FivePost_apikey',
                Loc::getMessage('FivePost_apikey'),
                Option::get($module_id, 'FivePost_apikey'),
                array('text')
            ),
            array(
                'FivePost_login',
                Loc::getMessage('FivePost_login'),
                Option::get($module_id, 'FivePost_login'),
                array('text')
            ),
            array(
                'FivePost_password',
                Loc::getMessage('FivePost_password'),
                Option::get($module_id, 'FivePost_password'),
                array('text')
            ),
            array(
                'FivePost_maxweght',
                Loc::getMessage('FivePost_maxweight'),
                Option::get($module_id, 'FivePost_maxweight'),
                array('text')
            ),
        )
    ),
    // СДЭК
    array(
        'DIV' => 'sdek',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_SDEK'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_SDEK'),
        'OPTIONS' => array(
            array(
                'SDEK_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'SDEK_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'SDEK_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'SDEK_door_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'SDEK_setaccount',
                Loc::getMessage('SDEK_setaccount'),
                Option::get($module_id, 'SDEK_setaccount'),
                array('text')
            ),
            array(
                'SDEK_setsecure',
                Loc::getMessage('SDEK_setsecure'),
                Option::get($module_id, 'SDEK_setsecure'),
                array('text')
            ),
            array(
                'SDEK_tariff_code',
                Loc::getMessage('SDEK_tariff_code'),
                Option::get($module_id, 'SDEK_tariff_code'),
                array('text')
            ),
            array(
                'SDEK_from_location',
                Loc::getMessage('SDEK_from_location'),
                Option::get($module_id, 'SDEK_from_location'),
                array('text')
            ),
        )
    ),
    // ПЭК
    array(
        'DIV' => 'pek',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_PEK'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_PEK'),
        'OPTIONS' => array(
            array(
                'PEK_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'PEK_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'PEK_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'PEK_door_active'),
                array("selectbox", $activeDelivery)
            ),
            Loc::getMessage('PickPoint_ikn'),
            array(
                'PEK_login',
                Loc::getMessage('PEK_login'),
                Option::get($module_id, 'PEK_login'),
                array('text')
            ),
            array(
                'PEK_password',
                Loc::getMessage('PEK_password'),
                Option::get($module_id, 'PEK_password'),
                array('text')
            ),
            array(
                'PEK_sendercityid',
                Loc::getMessage('PEK_sendercityid'),
                Option::get($module_id, 'PEK_sendercityid'),
                array('text')
            ),
            array(
                'PEK_senderdistancetype',
                Loc::getMessage('PEK_senderdistancetype'),
                Option::get($module_id, 'PEK_senderdistancetype'),
                array('text')
            ),
            array(
                'PEK_apilink',
                Loc::getMessage('PEK_apilink'),
                Option::get($module_id, 'PEK_apilink'),
                array('text')
            )
        )
    ),
    // RussianPost
    array(
        'DIV' => 'russian_post',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_RUSSIAN_POST'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_RUSSIAN_POST'),
        'OPTIONS' => array(
            array(
                'RussianPost_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'RussianPost_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'RussianPost_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'RussianPost_door_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'RussianPost_authtoken',
                Loc::getMessage('RussianPost_authtoken'),
                Option::get($module_id, 'RussianPost_authtoken'),
                array('text')
            ),
            array(
                'RussianPost_authkey',
                Loc::getMessage('RussianPost_authkey'),
                Option::get($module_id, 'RussianPost_authkey'),
                array('text')
            ),
            array(
                'RussianPost_fromzip',
                Loc::getMessage('RussianPost_fromzip'),
                Option::get($module_id, 'RussianPost_fromzip'),
                array('text')
            )
        )
    ),
    // Dellin
    array(
        'DIV' => 'dellin',
        'TAB' => Loc::getMessage('EE_PVZ_OPTIONS_DELLIN'),
        'TITLE' => Loc::getMessage('EE_PVZ_OPTIONS_DELLIN'),
        'OPTIONS' => array(
            array(
                'Dellin_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'Dellin_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'Dellin_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'Dellin_door_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'Dellin_apikey',
                Loc::getMessage('Dellin_apikey'),
                Option::get($module_id, 'Dellin_apikey'),
                array('text')
            ),
            array(
                'Dellin_login',
                Loc::getMessage('Dellin_login'),
                Option::get($module_id, 'Dellin_login'),
                array('text')
            ),
            array(
                'Dellin_password',
                Loc::getMessage('Dellin_password'),
                Option::get($module_id, 'Dellin_password'),
                array('text')
            ),
            array(
                'Dellin_derivalkladr',
                Loc::getMessage('Dellin_derivalkladr'),
                Option::get($module_id, 'Dellin_derivalkladr'),
                array('text')
            ),

            array(
                'Dellin_derivalstarttime',
                Loc::getMessage('Dellin_derivalstarttime'),
                Option::get($module_id, 'Dellin_derivalstarttime'),
                array('text')
            ),
            array(
                'Dellin_derivalendtime',
                Loc::getMessage('Dellin_derivalendtime'),
                Option::get($module_id, 'Dellin_derivalendtime'),
                array('text')
            )
        )
    ),
    // Oshisha
    array(
        'DIV' => 'oshisha',
        'TAB' => Loc::getMessage('PVZ_OPTIONS_OSHISHA'),
        'TITLE' => Loc::getMessage('PVZ_OPTIONS_OSHISHA'),
        'OPTIONS' => array(
            array(
                'Oshisha_pvz_active',
                GetMessage('DELIVERY_SETTINGS_PVZ_ACTIVITY'),
                Option::get($module_id, 'Oshisha_pvz_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'Oshisha_door_active',
                GetMessage('DELIVERY_SETTINGS_DOOR_ACTIVITY'),
                Option::get($module_id, 'Oshisha_door_active'),
                array("selectbox", $activeDelivery)
            ),
            array(
                'Oshisha_ymapskey',
                GetMessage('OSH_SETTINGS_YMAPSKEY_NAME'),
                Option::get($module_id, 'Oshisha_ymapskey'),
                array("text")
            ),
            array(
                'Oshisha_dadatatoken',
                GetMessage('OSH_SETTINGS_DA_DATA_NAME'),
                Option::get($module_id, 'Oshisha_dadatatoken'),
                array("text")
            ),
            array(
                'Oshisha_dadata_secret',
                GetMessage('OSH_SETTINGS_DA_DATA_SECRET'),
                Option::get($module_id, 'Oshisha_dadata_secret'),
                array("text")
            ),
            array(
                'Oshisha_cost',
                GetMessage('OSH_SETTINGS_COST'),
                Option::get($module_id, 'Oshisha_cost'),
                array("text")
            ),
            array(
                'Oshisha_pvzstrict',
                GetMessage('OSH_SETTINGS_STRICT_PVZ'),
                Option::get($module_id, 'Oshisha_pvzstrict'),
                array("selectbox", $oshishaArPvzStrictOptions)
            ),
            array(
                'Oshisha_northdays',
                GetMessage('OSH_SETTINGS_NORTH_DAYS'),
                Option::get($module_id, 'Oshisha_northdays'),
                array("multiselectbox", $daysOfTheWeek)
            ),
            array(
                'Oshisha_southeastdays',
                GetMessage('OSH_SETTINGS_SOUTHEAST_DAYS'),
                Option::get($module_id, 'Oshisha_southeastdays'),
                array("multiselectbox", $daysOfTheWeek)
            ),
            array(
                'Oshisha_southwestdays',
                GetMessage('OSH_SETTINGS_SOUTHWEST_DAYS'),
                Option::get($module_id, 'Oshisha_southwestdays'),
                array("multiselectbox", $daysOfTheWeek)
            ),
        )
    )
);

$oshishaOptions = array(
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
);


$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);

$tabControl->begin();
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

    .button_red:hover {
        text-decoration: none;
    }

</style>
<form action="<?= $APPLICATION->getCurPage(); ?>?mid=<?= $module_id; ?>&lang=<?= LANGUAGE_ID; ?>" method="post">
    <?= bitrix_sessid_post(); ?>
    <?php
    foreach ($aTabs as $aTab) { // цикл по вкладкам
        if ($aTab['OPTIONS']) {
            $tabControl->beginNextTab();
            __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);

            if ($aTab['DIV'] === 'pickpoint') {
                ?>
                <tr>
                    <td>
                        <div style="display: none" id="pickpoint_load_points_label"
                             class="adm-info-message-wrap">
                    </td>
                    <td>
                        <input type="button" id="pickpoint_load_points"
                               value="<?= Loc::getMessage('PVZ_UPDATE_POINTS') ?>"/>
                        </div>
                    </td>
                </tr>
                <?php
            } elseif ($aTab['DIV'] === '5post') {
                ?>
                <tr>
                    <td>
                        <div style="display: none" id="fivepost_load_points_label"
                             class="adm-info-message-wrap">
                    </td>
                    <td>
                        <input type="button" id="fivepost_load_points"
                               value="<?= Loc::getMessage('PVZ_UPDATE_POINTS') ?>"/>
                        </div>
                    </td>
                </tr>
                <?php
            } elseif ($aTab['DIV'] === 'sdek') { ?>
                <tr>
                <td>
                    <table style="width: 200%; margin-top: 15px;">
                        <tr>
                            <td colspan="4" valign="top" align="center"><strong>Управление тарифами</strong>
                            </td>
                        </tr>
                        <?php $arTarifs = \CommonPVZ\SDEKDelivery::getSdekExtraTarifs(); ?>
                        <tr>
                            <th style="width:20px"></th>
                            <th>Название тарифа (код)</th>
                            <th>Отключить тариф для расчета</th>
                        </tr>
                        <?php
                        foreach ($arTarifs as $tarifId => $tarifOption) {
                            ?>
                            <tr>
                                <td style='text-align:center'><?php if ($tarifOption['DESC']) { ?><?php } ?></td>
                                <td><?= $tarifOption['NAME'] ?></td>
                                <td align='center'><input type='checkbox' name='SDEK_tarifs[<?= $tarifId ?>][BLOCK]'
                                                          value='Y' <?= ($tarifOption['BLOCK'] == 'Y') ? "checked" : "" ?>>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td colspan='2'><br></td>
                        </tr>
                    </table>
                </td>
                </tr><?
            } else if ($aTab['DIV'] === 'dellin') { ?>
                <tr>
                    <td>
                        <div style="display: none" id="dellin_load_points_label"
                             class="adm-info-message-wrap">
                    </td>
                    <td>
                        <input type="button" id="dellin_load_points"
                               value="<?= Loc::getMessage('PVZ_UPDATE_POINTS') ?>"/>
                        </div>
                    </td>
                </tr>

                <?
            } else if ($aTab['DIV'] === 'russian_post') { ?>
                <tr>
                <td>
                    <div style="display: none" id="russian_post_load_points_label"
                         class="adm-info-message-wrap">
                </td>
                <td>
                    <input type="button" id="russian_post_load_points"
                           value="<?= Loc::getMessage('PVZ_UPDATE_POINTS') ?>"/>
                    </div>
                </td>
                </tr><?
            } else if ($aTab['DIV'] === 'oshisha') { ?>
                <tr>
                    <td>Время доставки:</td>
                    <td>Время доставки настраивается в свойстве заказа "DELIVERYTIME_INTERVAL"</td>
                </tr>
                <tr>
                    <td>
                        <div style="display: none" id="oshisha_update_region_restrictions_label"
                             class="adm-info-message-wrap">
                    </td>
                    <td>
                        <input type="button" id="oshisha_update_region_restrictions"
                               value="<?= Loc::getMessage('OSH_REGION_RESTRICTIONS') ?>"/>
                        </div>
                    </td>
                </tr>
                <?
            }
        }
    }
    $tabControl->buttons();
    ?>
    <input type="submit" name="apply"
           value="<?= Loc::GetMessage('EE_PVZ_OPTIONS_INPUT_APPLY'); ?>" class="adm-btn-save"/>
    <input type="submit" name="default"
           value="<?= Loc::GetMessage('EE_PVZ_OPTIONS_INPUT_DEFAULT'); ?>"/>
</form>

<?php


$tabControl->end();


if ($request->isPost() && check_bitrix_sessid()) {
    $_REQUEST['SDEK_tarifs'] = ($_REQUEST['SDEK_tarifs']) ? serialize($_REQUEST['SDEK_tarifs']) : 'a:0:{}';
    Option::set($module_id, 'SDEK_tarifs', $_REQUEST['SDEK_tarifs']);
    foreach ($aTabs as $aTab) {
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption)) {
                continue;
            }
            if ($arOption['note']) {
                continue;
            }
            if ($request['apply']) {
                $optionValue = $request->getPost($arOption[0]);
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
            } elseif ($request['default']) {
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }

    LocalRedirect($APPLICATION->getCurPage() . '?mid=' . $module_id . '&lang=' . LANGUAGE_ID);

} ?>

<script>
    BX.ready(() => {
        BX.bind(BX('pickpoint_load_points'), 'click', BX.proxy((event) => {
            event.preventDefault()
            const label = BX('pickpoint_load_points_label');
            BX.cleanNode(label)
            BX.removeClass(label, 'adm-info-message-green')
            BX.removeClass(label, 'adm-info-message-red')

            BX.append(BX.create('div', {
                attrs: {className: 'adm-info-message'},
                text: 'Загружается...'
            }), label)
            BX.show(label);

            BX.ajax({
                url: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
                data: {sessid: BX.bitrix_sessid(), action: 'updatePickPointPoints'},
                method: 'POST',
                dataType: 'json',
                onsuccess: (data) => {
                    if (data.status === 'success') {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-green')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Успешно загружено'
                        }), label)

                    } else {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-red')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Ошибка'
                        }), label)
                    }
                },
            })
        }));
        BX.bind(BX('fivepost_load_points'), 'click', BX.proxy((event) => {
            event.preventDefault()
            const label = BX('fivepost_load_points_label');
            BX.cleanNode(label)
            BX.removeClass(label, 'adm-info-message-green')
            BX.removeClass(label, 'adm-info-message-red')

            BX.append(BX.create('div', {
                attrs: {className: 'adm-info-message'},
                text: 'Загружается...'
            }), label)
            BX.show(label);

            BX.ajax({
                url: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
                data: {sessid: BX.bitrix_sessid(), action: 'updateFivePostPoints'},
                method: 'POST',
                dataType: 'json',
                onsuccess: (data) => {
                    if (data.status === 'success') {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-green')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Успешно загружено'
                        }), label)
                    } else {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-red')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Ошибка'
                        }), label)
                    }
                },
            })
        }));
        BX.bind(BX('dellin_load_points'), 'click', BX.proxy((event) => {
            event.preventDefault()
            const label = BX('dellin_load_points_label');
            BX.cleanNode(label)
            BX.removeClass(label, 'adm-info-message-green')
            BX.removeClass(label, 'adm-info-message-red')

            BX.append(BX.create('div', {
                attrs: {className: 'adm-info-message'},
                text: 'Загружается...'
            }), label)
            BX.show(label);

            BX.ajax({
                url: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
                data: {sessid: BX.bitrix_sessid(), action: 'updateDellinPoints'},
                method: 'POST',
                dataType: 'json',
                onsuccess: (data) => {
                    if (data.status === 'success') {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-green')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Успешно загружено'
                        }), label)
                    } else {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-red')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Ошибка'
                        }), label)
                    }
                },
            })
        }));
        BX.bind(BX('russian_post_load_points'), 'click', BX.proxy((event) => {
            event.preventDefault()
            const label = BX('russian_post_load_points_label');
            BX.cleanNode(label)
            BX.removeClass(label, 'adm-info-message-green')
            BX.removeClass(label, 'adm-info-message-red')

            BX.append(BX.create('div', {
                attrs: {className: 'adm-info-message'},
                text: 'Загружается...'
            }), label)
            BX.show(label);

            BX.ajax({
                url: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
                data: {sessid: BX.bitrix_sessid(), action: 'updateRussianPostPoints'},
                method: 'POST',
                dataType: 'json',
                onsuccess: (data) => {
                    if (data.status === 'success') {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-green')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Успешно загружено'
                        }), label)
                    } else {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-red')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Ошибка'
                        }), label)
                    }
                },
            })
        }));
        BX.bind(BX('oshisha_update_region_restrictions'), 'click', BX.proxy((event) => {
            event.preventDefault()
            const label = BX('oshisha_update_region_restrictions_label');
            BX.cleanNode(label)
            BX.removeClass(label, 'adm-info-message-green')
            BX.removeClass(label, 'adm-info-message-red')

            BX.append(BX.create('div', {
                attrs: {className: 'adm-info-message'},
                text: 'Загружается...'
            }), label)
            BX.show(label);

            BX.ajax({
                url: '/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajax.php',
                data: {sessid: BX.bitrix_sessid(), action: 'updateOshishaRegionRestrictions'},
                method: 'POST',
                dataType: 'json',
                onsuccess: (data) => {
                    if (data.status === 'success') {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-green')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Успешно обновлено'
                        }), label)
                    } else {
                        BX.cleanNode(label)
                        BX.addClass(label, 'adm-info-message-red')
                        BX.append(BX.create('div', {
                            attrs: {className: 'adm-info-message'},
                            text: 'Ошибка'
                        }), label)
                    }
                },
            })
        }))
    })
</script>
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
