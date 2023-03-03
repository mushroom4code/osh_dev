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

$aTabs = array(
    // PickPoint
    array(
        'DIV'     => 'pickpoint',
        'TAB'     => Loc::getMessage('EE_PVZ_OPTIONS_PP'),
        'TITLE'   => Loc::getMessage('EE_PVZ_OPTIONS_PP'),
        'OPTIONS' => array(
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
        'DIV'     => 'edit2',
        'TAB'     => Loc::getMessage('EE_PVZ_OPTIONS_FP'),
        'TITLE'   => Loc::getMessage('EE_PVZ_OPTIONS_FP'),
        'OPTIONS' => array(
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
            )
        )
    ),
    // СДЭК
    array(
        'DIV'     => 'edit3',
        'TAB'     => Loc::getMessage('EE_PVZ_OPTIONS_SDEK'),
        'TITLE'   => Loc::getMessage('EE_PVZ_OPTIONS_SDEK'),
        'OPTIONS' => array(
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
            )
        )
    ),
    // ПЭК
    array(
        'DIV'     => 'edit4',
        'TAB'     => Loc::getMessage('EE_PVZ_OPTIONS_PEK'),
        'TITLE'   => Loc::getMessage('EE_PVZ_OPTIONS_PEK'),
        'OPTIONS' => array(
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
    )
);


$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);

$tabControl->begin();
?>
    <form action="<?= $APPLICATION->getCurPage(); ?>?mid=<?=$module_id; ?>&lang=<?= LANGUAGE_ID; ?>" method="post">
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
                }
            }
        }
        $tabControl->buttons();
        ?>
        <input type="submit" name="apply"
               value="<?= Loc::GetMessage('EE_PVZ_OPTIONS_INPUT_APPLY'); ?>" class="adm-btn-save" />
        <input type="submit" name="default"
               value="<?= Loc::GetMessage('EE_PVZ_OPTIONS_INPUT_DEFAULT'); ?>" />
    </form>

<?php



$tabControl->end();


if ($request->isPost() && check_bitrix_sessid()) {

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

    LocalRedirect($APPLICATION->getCurPage().'?mid='.$module_id.'&lang='.LANGUAGE_ID);

} ?>

<script>
    BX.ready(()=>{
        BX.bind(BX('pickpoint_load_points'), 'click', BX.proxy((event)=>{
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
                data: {action: 'updatePickPointPoints'},
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
        }))
    })
</script>
