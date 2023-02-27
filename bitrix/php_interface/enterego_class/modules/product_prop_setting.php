<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
IncludeModuleLangFile(__FILE__);
/**
 * @var CMain|CAllMain $APPLICATION
 * @var CAdminPage $adminPage
 * @var CAdminMenu $adminMenu
 */

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if (empty($adminMenu->aGlobalMenu)) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
$APPLICATION->SetAdditionalCSS("/bitrix/themes/" . ADMIN_THEME_ID . "/index.css");
$APPLICATION->SetTitle("Настройка свойств товара для быстрого просмотра");


$arProps = $res = [];
$iblock_id = IBLOCK_CATALOG;

$properties = CIBlockProperty::GetList(
    array("sort" => "asc", "name" => "asc"),
    array("IBLOCK_ID" => IBLOCK_CATALOG));

while ($prop_fields = $properties->GetNext()) {
    $arProps[$prop_fields['ID']]['NAME'] = $prop_fields['NAME'];
    $arProps[$prop_fields['ID']]['ACTIVE'] = $prop_fields['ACTIVE'];
    $arProps[$prop_fields['ID']]['ID'] = $prop_fields['ID'];
    $arProps[$prop_fields['ID']]['CODE'] = $prop_fields['CODE'];
}


$resQuery = Enterego\EnteregoSettings::getPropSetting($iblock_id, 'SEE_POPUP_WINDOW');

if (!empty($resQuery)) {
    while ($collectionPropChecked = $resQuery->Fetch()) {
        $res[$collectionPropChecked['ID']] = $collectionPropChecked;
    }
} ?>
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
if ($REQUEST_METHOD == "POST" && check_bitrix_sessid() && $_REQUEST['action'] === 'update_form') {

    foreach ($res as $id => $checked_prop) {
        $see_popup = 'N';
        if (isset($_REQUEST['checked_props'][$id])) {
            $see_popup = 'Y';
        }

        if ($see_popup != $checked_prop['SEE_POPUP_WINDOW']) {
            Enterego\EnteregoSettings::updatePropSetting($iblock_id, $see_popup, 'SEE_POPUP_WINDOW', $id);
        }
    }

    if ($apply != "") {
        LocalRedirect("/bitrix/php_interface/enterego_class/modules/product_prop_setting.php");
    } else {
        LocalRedirect("/bitrix/");
    }
}

$tabControl = new CAdminTabControl("tabControl", []);

if ($_REQUEST["mess"] == "ok")
    CAdminMessage::ShowMessage(array("MESSAGE" => 'Изменения сохранены', "TYPE" => "OK"));
?>
    <form method="POST" ENCTYPE="multipart/form-data" id=post_form name="post_form">
<?= bitrix_sessid_post(); ?>
    <input type="hidden" name="action" value="update_form">
    <div style="display: flex; flex-direction: column; justify-content: center;align-items: center">
        <table class="internal" style="margin-bottom: 3rem; max-height: 1000px;overflow: auto;display: block;">
            <tbody>
            <tr class="heading">
                <td colspan="4">Наименование</td>
                <td colspan="4">Активно</td>
                <td colspan="3">Показать</td>
            </tr>
            <?php foreach ($arProps as $prop) {
                if ($prop['ACTIVE'] === "Y") {
                    $prop['ACTIVE'] = 'Да';
                } else {
                    $prop['ACTIVE'] = 'Нет';
                } ?>
                <tr class="main-grid-row main-grid-row-body">
                    <td colspan="4"><b><?= $prop['NAME'] ?></b></td>
                    <td colspan="4" style="text-align: center">
                        <?= $prop['ACTIVE'] ?>
                    </td>
                    <td colspan="3" style="text-align: center">
                        <input type="checkbox" class="box_with_check" name="checked_props[<?= $prop['ID'] ?>]"
                               value="<?= $prop['CODE'] ?>"
                            <?php if ($res[$prop['ID']]['SEE_POPUP_WINDOW'] === 'Y') { ?>
                               checked
                               <?php } ?>id="on_sale"/>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php $tabControl->Buttons(
    array(
        "back_url" => "/bitrix/php_interface/enterego_class/modules/product_prop_setting.php",
    )
); ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">

<?php if ($ID > 0 && !$bCopy): ?>
    <input type="hidden" name="ID" value="<?= $ID ?>">
<?php endif; ?>
    <script type="text/javascript">
        let attributes_menu = document.querySelector('div#global_submenu_enterego').attributes;
        document.querySelector('div#global_submenu_desktop').attributes.class.value = 'adm-global-submenu';
        document.querySelector('a#global_menu_desktop').attributes.class.value = 'adm-default adm-main-menu-item adm-desktop';
        attributes_menu.class.value = 'adm-global-submenu adm-global-submenu-active adm-global-submenu-animate ';
        document.querySelector('span#global_menu_enterego').attributes.class.value = 'adm-default adm-main-menu-item adm-enterego adm-main-menu-item-active';
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
