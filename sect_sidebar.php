<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
   <div class="sidebar_lk col-md-3">
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "sect",
                "AREA_FILE_SUFFIX" => "sidebar",
                "AREA_FILE_RECURSIVE" => "Y",
                "EDIT_MODE" => "html",
            ),
            false,
            array('HIDE_ICONS' => 'Y')
        ); ?>
    </div>
