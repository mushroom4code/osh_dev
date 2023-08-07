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

$resOption = COption::GetOptionString('exhibition_info_admin_params', 'CHECKED_EXHIBITION');
$dateOption = json_decode(COption::GetOptionString('exhibition_info_admin_params', 'PERIOD'));?>
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
            width: 500px;
            padding: 0.5rem 0;
            align-items: center;
        }

        .border-bottom-2 {
            border-bottom: 2px solid white;
            margin-bottom: 1rem;
        }

        .box_with_box_button, .box_with_boxes {
            display: flex;
            flex-direction: column;
        }

        .mr-3 {
            margin-right: 1.5rem;
        }

        textarea,input[type="text"] {
            width: 100%
        }

        .text_mess {
            margin: 2rem 0 1rem 0;
            align-items: center;
        }

        .font-16 {
            font-size: 16px;
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

        input[type="datetime-local"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
        }
    </style>
    <div class="box_with_boxes">
        <div class="box_with_box_button">
            <div class="box_with_buttons box_with_boxes border-bottom-2">
                <div class="flex_button box_with_buttons" style="margin-bottom: 1.5rem">
                    <label class="box_with_text font-16" for="on_sale">
                       <b> Включить выставку</b><br><br>
                        <span style="font-size:13px; padding-top: 10px">(цены на сайте с выставкой изменятся на B2B)</span>
                    </label>
                    <input type="checkbox" class="box_with_check" <?php if ($resOption === 'true') {
                        echo 'checked="On"';
                    } else {
                        echo '';
                    } ?>id="on_sale"/>
                </div>
                <div class="flex_button box_with_buttons">
                    <div class="">
                        <label class="box_with_text mr-3 font-16">C</label>
                        <input type="datetime-local" class="sale_on_start" value="<?= $dateOption->start ?? 0 ?>"
                               name="sale_on_start"/>
                    </div>
                    <div class="">
                        <label class="box_with_text mr-3 font-16">По</label>
                        <input type="datetime-local" class="sale_on_end" value="<?= $dateOption->end ?? 0 ?>"
                               name="sale_on_end"/>
                    </div>
                </div>
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
            let dateStart = document.getElementsByClassName('sale_on_start')[0].value;
            let dateEnd = document.getElementsByClassName('sale_on_end')[0].value;

            BX.ajax({
                url: "/bitrix/php_interface/enterego_class/modules/sales_option.php",
                data: {
                    action: 'SetParamExhibition',
                    param: checkOn,
                    dateStart:dateStart,
                    dateEnd:dateEnd
                },
                method: "POST",
                onsuccess: function (data) {
                }
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
