<?php use Bitrix\Main\Config\Option;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

$arProps = [];
$properties = CIBlockProperty::GetList(
    array("sort" => "asc", "name" => "asc"),
    array("IBLOCK_ID" => IBLOCK_CATALOG));

while ($prop_fields = $properties->GetNext()) {
    $arProps[$prop_fields['ID']]['NAME'] = $prop_fields['NAME'];
    $arProps[$prop_fields['ID']]['ACTIVE'] = $prop_fields['ACTIVE'];
    $arProps[$prop_fields['ID']]['ID'] = $prop_fields['ID'];
    $arProps[$prop_fields['ID']]['CODE'] = $prop_fields['CODE'];
}

if (empty($adminMenu->aGlobalMenu))
    /**
     * @var CMain|CAllMain $APPLICATION
     */
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/themes/" . ADMIN_THEME_ID . "/index.css");

$MESS ['admin_index_title'] = "Настройка свойств товара для быстрого просмотра";

$APPLICATION->SetTitle(GetMessage("admin_index_title"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

?>
    <link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
    <style>
        .box_with_check {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        th, td {
            white-space: nowrap;
        }
    </style>
<?php
if ($REQUEST_METHOD == "POST" && check_bitrix_sessid()) {


    Option::set("enterego_product", "POPUP_FAST_WINDOW_PROPS", json_encode($_REQUEST['checked_props']));

    if ($apply != "")
        // если была нажата кнопка "Применить" - отправляем обратно на форму.
        LocalRedirect("/bitrix/php_interface/enterego_class/modules/product_prop_setting.php");
    else
        // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
        LocalRedirect("/bitrix/");
}

$aTabs = array();
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($_REQUEST["mess"] == "ok")
    CAdminMessage::ShowMessage(array("MESSAGE" => 'Изменения сохранены', "TYPE" => "OK"));
?>
    <form method="POST" ENCTYPE="multipart/form-data" id=post_form name="post_form">
<?php echo bitrix_sessid_post();
$SETTINGS = json_decode(Option::get("enterego_product", "POPUP_FAST_WINDOW_PROPS"), 1);
?>
    <div style="display: flex; flex-direction: column; justify-content: center;align-items: center">
        <table class="internal" style="margin-bottom: 3rem; max-height: 1000px;overflow: auto;display: block;">
            <tbody>
            <tr class="heading">
                <td colspan="4">Наименование</td>
                <td colspan="3">Показать</td>
                <td colspan="4">Активно</td>
            </tr>
            <?php foreach ($arProps as $prop) {
                if ($prop['ACTIVE'] === 'Y') {
                    $prop['ACTIVE'] = 'Да';
                } else {
                    $prop['ACTIVE'] = 'Нет';
                } ?>
                <tr class="main-grid-row main-grid-row-body">
                    <td colspan="4"><b><?= $prop['NAME'] ?></b></td>
                    <td colspan="3" style="text-align: center">
                        <input type="checkbox" class="box_with_check" name="checked_props[<?=$prop['ID']?>]" value="<?= $prop['CODE'] ?>"
                               <?php if ($SETTINGS[$prop['ID']] === $prop['CODE']) {
                                   echo 'checked="On"';
                               } else {
                                   echo '';
                               } ?>id="on_sale"/>
                    </td>
                    <td colspan="4" style="text-align: center">
                        <?= $prop['ACTIVE'] ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php

$tabControl->Buttons(
    array(
        "back_url" => "/bitrix/php_interface/enterego_class/modules/product_prop_setting.php",
    )
);
?>
    <input type="hidden" name="lang" value="<?= LANG ?>">

<? if ($ID > 0 && !$bCopy): ?>
    <input type="hidden" name="ID" value="<?= $ID ?>">
<? endif; ?>
    <script type="text/javascript">
        let attributes_menu = document.querySelector('div#global_submenu_enterego').attributes;
        document.querySelector('div#global_submenu_desktop').attributes.class.value = 'adm-global-submenu';
        document.querySelector('a#global_menu_desktop').attributes.class.value = 'adm-default adm-main-menu-item adm-desktop';
        attributes_menu.class.value = 'adm-global-submenu adm-global-submenu-active adm-global-submenu-animate ';
        document.querySelector('span#global_menu_enterego').attributes.class.value = 'adm-default adm-main-menu-item adm-enterego adm-main-menu-item-active';

        BX.getParamSalePrice = function () {
            let onSale = document.getElementById('on_sale');
            let checkOn = onSale.checked;
            let dom = document.getElementById('box_with_box_button');
            dom.innerHTML = "";

            BX.ajax({
                url: "/bitrix/php_interface/enterego_class/modules/sales_option.php",
                data: {action: 'SetParamSale', param: checkOn},
                method: "POST",
                onsuccess: function (data) {
                    let textRes = '';

                    if (data === 'true') {
                        textRes = "Скидки успешно активированы"

                    } else {
                        textRes = "Скидки успешно отключены"
                    }
                    dom.append(textRes);
                    // document.location.href = "https://osh-new.docker.oblako-1c.ru/bitrix/";
                }
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
