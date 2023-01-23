<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin.php"); ?>
    <div class="adm-workarea">
        <? $APPLICATION->IncludeComponent(
            "bitrix:rest.hook",
            ".default",
            [
                "SEF_MODE" => "Y",
                "SEF_FOLDER" => "/local/osh-rest/hook/",
                "COMPONENT_TEMPLATE" => ".default",
                "SEF_URL_TEMPLATES" => [
                    "list" => "",
                    "event_list" => "event/",
                    "event_edit" => "event/#id#/",
                    "ap_list" => "ap/",
                    "ap_edit" => "ap/#id#/",
                ]
            ],
            false
        ); ?>
        <br>
        <a href="jav * ascript:void(0)" class="adm-btn adm-btn-green"
           onclick="BX.PopupMenu.show('rest_hook_menu', this, [{
              'href':'/local/osh-rest/hook/event/0/',
              'text':'Исходящий вебхук'
           },{
              'href':'/local/osh-rest/hook/ap/0/',
              'text':'Входящий вебхук'
           }]); return false">
            Добавить вебхук
        </a>
    </div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");