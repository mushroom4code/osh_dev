<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if (empty($adminMenu->aGlobalMenu))
    /**
     * @var CMain|CAllMain $APPLICATION
     */
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/themes/" . ADMIN_THEME_ID . "/index.css");

$MESS ['admin_index_title'] = "Черная пятница/Распродажа";

$APPLICATION->SetTitle(GetMessage("admin_index_title"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$resOption = COption::GetOptionString('activation_price_admin', 'USE_CUSTOM_SALE_PRICE');
$dateOption = json_decode(COption::GetOptionString('activation_price_admin', 'PERIOD')); ?>
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

        .box_with_buttons {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
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

        .mr-3 {
            margin-right: 1.5rem;
        }

        .text_mess {
            margin: 2rem 0 1rem 0;
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

        input[type="datetime-local"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
        }
    </style>
    <div class="box_with_boxes">
        <div class="box_with_box_button">
            <div class="box_with_buttons">
                <label class="box_with_text" for="on_sale">
                    Включить скидки
                </label>
                <input type="checkbox" class="box_with_check" <?php if ($resOption === 'true') {
                    echo 'checked="On"';
                } else {
                    echo '';
                } ?>id="on_sale"/>
                <div class="">
                    <label class="box_with_text mr-3">C</label>
                    <input type="datetime-local" class="sale_on_start" value="<?= $dateOption->start ?? 0 ?>"
                           name="sale_on_start"/>
                </div>
                <div class="">
                    <label class="box_with_text mr-3">По</label>
                    <input type="datetime-local" class="sale_on_end" value="<?= $dateOption->end ?? 0 ?>"
                           name="sale_on_end"/>
                </div>
            </div>
            <div class="text_mess" id="box_with_box_button">
            </div>

        </div>
        <button class="btn_admin_sale" onclick="BX.getParamSalePrice();">
            Применить
        </button>
    </div>
    <script type="text/javascript">
        let attributes_menu = document.querySelector('div#global_submenu_enterego').attributes;
        document.querySelector('div#global_submenu_desktop').attributes.class.value = 'adm-global-submenu';
        document.querySelector('a#global_menu_desktop').attributes.class.value = 'adm-default adm-main-menu-item adm-desktop';
        attributes_menu.class.value = 'adm-global-submenu adm-global-submenu-active adm-global-submenu-animate ';
        document.querySelector('span#global_menu_enterego').attributes.class.value = 'adm-default adm-main-menu-item adm-enterego adm-main-menu-item-active';

        BX.getParamSalePrice = function () {
            let onSale = document.getElementById('on_sale');
            let checkOn = onSale.checked;
            let date_start = document.getElementsByClassName('sale_on_start')[0].value;
            console.log(date_start);
            let date_end = document.getElementsByClassName('sale_on_end')[0].value;
            let dom = document.getElementById('box_with_box_button');
            dom.innerHTML = "";

            BX.ajax({
                url: "/bitrix/php_interface/enterego_class/modules/sales_option.php",
                data: {action: 'SetParamSale', param: checkOn, date_start: date_start, date_end: date_end},
                method: "POST",
                onsuccess: function (data) {
                    let textRes = "Черная пятница отключена";

                    if (data === 'true') {
                        textRes = "Черная пятница включена"
                    }
                    dom.append(textRes);
                    // document.location.href = "https://osh-new.docker.oblako-1c.ru/bitrix/";
                }
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
