<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if (empty($adminMenu->aGlobalMenu)) /**
 * @var CMain|CAllMain $APPLICATION
 */ {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetAdditionalCSS("/bitrix/themes/" . ADMIN_THEME_ID . "/index.css");

$MESS ['admin_index_title'] = "Выставка на сайте";

$APPLICATION->SetTitle(GetMessage("admin_index_title"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$resOption = COption::GetOptionString('exhibition_info_admin', 'CHECKED_EXHIBITION'); ?>
    <style>
        .btn_admin_sale {
            padding: 7px 0;
            color: #ffffff;
            text-align: center;
            border: none;
            cursor: pointer;
            outline: none;
            width: 150px;
            border-radius: 5px;
            transition: 0.2s;
        }

        .btn_admin_sale {
            background-color: #8a8e8a;
        }

        .btn_admin_sale:hover {
            background-color: #18d318;
            transition: 0.2s;
        }

        .flex_button {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .box_with_buttons {
            width: 800px;
            border-bottom: 1px solid white;
            padding: 2rem 0;
            align-items: center;
        }

        .box_with_box_button, .box_with_boxes {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .box_with_box_button {
            margin: 3rem 0;
            align-items: center;
        }

        .box_with_boxes {
            display: flex;
            flex-direction: column;
        }

        .box_with_check {
            cursor: pointer;
            width: 18px;
            height: 18px;
            border-radius: 5px;
        }

        .box_with_text {
            font-size: 20px;
        }

        input[type="datetime-local"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
        }
    </style>
    <div class="box_with_boxes">
        <div class="box_with_box_button">
            <div class="box_with_buttons flex_button">
                <label class="box_with_text" for="on_sale">
                    Включить выставку<br>
                    (цены на сайте с выставкой изменятся на B2B)
                </label>
                <input type="checkbox" class="box_with_check" <?php if ($resOption === 'true') {
                    echo 'checked="On"';
                } else {
                    echo '';
                } ?>id="on_sale"/>
            </div>
        </div>
        <button class="btn_admin_sale" onclick="BX.getParamInfo();">
            Применить
        </button>
    </div>
    <script type="text/javascript">
        let attributes_menu = document.querySelector('div#global_submenu_enterego').attributes;
        document.querySelector('div#global_submenu_desktop').attributes.class.value = 'adm-global-submenu';
        document.querySelector('a#global_menu_desktop').attributes.class.value = 'adm-default adm-main-menu-item adm-desktop';
        attributes_menu.class.value = 'adm-global-submenu adm-global-submenu-active adm-global-submenu-animate ';
        document.querySelector('span#global_menu_enterego').attributes.class.value = 'adm-default adm-main-menu-item adm-enterego adm-main-menu-item-active';

        BX.getParamInfo = function () {
            let onSale = document.getElementById('on_sale');
            let checkOn = onSale.checked;

            BX.ajax({
                url: "/bitrix/php_interface/enterego_class/modules/sales_option.php",
                data: {
                    action: 'SetParamExhibition',
                    param: checkOn,
                },
                method: "POST",
                onsuccess: function (data) {
                }
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
