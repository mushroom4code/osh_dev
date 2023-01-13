<?php

use Bitrix\Main\Page\Asset;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if (empty($adminMenu->aGlobalMenu))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetAdditionalCSS("/bitrix/themes/" . ADMIN_THEME_ID . "/index.css");
$MESS ['admin_index_title'] = "Настройка категорий прайс-листа";
$APPLICATION->SetTitle(GetMessage("admin_index_title"));
$category_new = [];
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$result = CIBlockSection::GetList(array("SORT" => "ASC"), array("IBLOCK_ID" => 12, 'DEPTH_LEVEL' => 1), false, array(), false);
while ($category = $result->Fetch()) {
    $category_new[$category['ID']]['ID'] = $category['ID'];
    $category_new[$category['ID']]['NAME'] = $category['NAME'];
}

$resOption = COption::getOptionString('priceList_xlsx', 'priceListArrayCustom'); ?>
    <style type="text/css">
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
            background-color: #8a8e8ac9;
            transition: 0.2s;
        }

        .box_with_id_category {
            margin: 0.5rem 0 !important;
            width: 500px;
        }

        .text_mess {
            margin: 2rem 0 1rem 0;
            align-items: center;
        }

        .box_with_ids {
            margin-bottom: 2rem;
            border-bottom: 1px solid white;
        }

        .d-flex, .box_with_boxes, .box_with_ids {
            display: flex;
        }

        .flex-row {
            flex-direction: row;
        }

        .box_with_boxes, .box_with_ids {
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .mt-6 {
            margin-top: 6rem;
        }

        .mr-2 {
            margin-right: 2rem;
        }

    </style>
    <div class="box_with_boxes mt-6">
        <div class="box_with_ids">
            <?php if (!empty($resOption)) {
                $result = json_decode($resOption);
                foreach ($result as $id) { ?>
                    <select class="box_with_id_category">
                        <option value="<?= $category_new[$id]['ID'] ?>"><?= $category_new[$id]['NAME'] ?></option>
                        <?php foreach ($category_new as $items) {
                            if ($items['ID'] !== $id) { ?>
                                <option value="<?= $items['ID'] ?>"><?= $items['NAME'] ?></option>
                            <?php }
                        } ?>
                        <option value="">Выбрать</option>
                    </select>
                <?php }
            } ?>
            <select class="box_with_id_category nulls">
                <option value="">Выбрать</option>
                <?php foreach ($category_new as $items) { ?>
                    <option value="<?= $items['ID'] ?>"><?= $items['NAME']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="d-flex flex-row">
            <button class="btn_admin_sale mr-2" onclick="BX.getParamPriceList();">Применить</button>
            <button class="btn_admin_sale" onclick="BX.addCategory();">Добавить категорию</button>
        </div>
        <div class="text_mess"></div>
    </div>
    <script type="text/javascript">
        BX.getParamPriceList = function () {
            let ids = '', dom = document.getElementsByClassName('text_mess'), arrParams = [], i;
            dom.innerHTML = "";
            if (document.getElementsByClassName('box_with_id_category').length !== 0) {
                ids = document.getElementsByClassName('box_with_id_category');
                for (i = 0; i < ids.length; i++) {
                    if (ids[i].value !== '') {
                        arrParams.push(ids[i].value);
                    }
                }
            }

            BX.ajax({
                url: "/bitrix/php_interface/enterego_class/modules/sales_option.php",
                data: {action: 'SetParamPriceList', param: JSON.stringify(arrParams)},
                method: "POST",
                onsuccess: function (data) {
                    let url = "https://oshisha.cc/bitrix/admin/";
                    document.location.href = url;
                    document.getElementsByClassName('text_mess')[0].append('Изменения сохранены');
                }
            });
        }
        BX.addCategory = function () {
            let dom = document.getElementsByClassName('box_with_ids'),
                Element = document.getElementsByClassName('nulls'),
                cloneElement = Element[0].cloneNode(true);
            dom[0].appendChild(cloneElement);
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");