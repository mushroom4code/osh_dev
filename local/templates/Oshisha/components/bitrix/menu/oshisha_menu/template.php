<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */


$this->setFrameMode(true);
if (empty($arResult["ALL_ITEMS"]))
    return;

CUtil::InitJSCore();

$menuBlockId = "catalog_menu_" . $this->randString();

$menu_for_JS = [];
foreach ($arResult["MENU_STRUCTURE"] as $itemID => $arColumns) {
    $HAS_CHILD = 0;
    if (is_array($arColumns) && count($arColumns) > 0)
        $HAS_CHILD = 1;
    if ($arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/diskont/' && $arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/hit/') {


        $menu_for_JS['MAIN'][] = [
            'LINK' => $arResult["ALL_ITEMS"][$itemID]["LINK"],
            'TEXT' => $arResult["ALL_ITEMS"][$itemID]["TEXT"],
            'ID' => $itemID,
            'HAS_CHILD' => $HAS_CHILD,
        ];
        if (is_array($arColumns) && count($arColumns) > 0) {
            foreach ($arColumns as $key => $arRow) {
                foreach ($arRow as $itemIdLevel_2 => $arLevel_3) {
                    $menu_for_JS['ELEMENT'][$itemID][] = [
                        'LINK' => $arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"],
                        'TEXT' => $arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"],
                    ];
                }
            }
            usort($menu_for_JS['ELEMENT'][$itemID], 'sort_by_name_menu');
        }
    }
}
?>
<nav class="box_with_menu_header">
    <ul class="ul_menu flex items-center flex-row">
        <li class="li_menu_header mr-7">
            <div class="dark:text-textDark shadow-md flex flex-row justify-center items-center text-textLight
            dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 w-48"
                 title="Просмотреть меню"
                 id="main_menus">
                <div class="span_bar mr-4">
                    <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 14H8.58333M1 1H18.3333H1ZM1 7.5H18.3333H1Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="text-white font-medium text-md">Каталог</span>
            </div>
        </li>

        <?php
        function sort_by_name_menu($a, $b)
        {
            if ($a["TEXT"] == $b["TEXT"]) {
                return 0;
            }
            return ($a["TEXT"] < $b["TEXT"]) ? -1 : 1;
        }

        $result = json_encode($menu_for_JS) ?>
        <li class="mr-7" data-role="bx-menu-item">
            <a class="font-medium dark:font-light dark:text-textDarkLightGray text-textLight" href="/diskont/">
                Дисконт</a>
        </li>
        <li class="mr-7" data-role="bx-menu-item">
            <a class="font-medium dark:font-light dark:text-textDarkLightGray text-textLight" href="/catalog_new/">Новинки</a>
        </li>
        <li class="mr-7" data-role="bx-menu-item">
            <a class="font-medium dark:font-light dark:text-textDarkLightGray text-textLight" href="/hit/">Хиты</a>
        </li>
        <li class="mr-7" data-role="bx-menu-item">
            <a class="font-medium dark:font-light dark:text-textDarkLightGray text-textLight" href="/brands/">Бренды</a>
        </li>
        <div class="open_menu" style="display: none" id="main_menu">
            <div class="parent_menu"></div>
            <div class="menu_items hide"></div>
        </div>
    </ul>
</nav>
<div class="overlay_top"></div>
<script type="text/javascript">
    let menu_items_array = <?= json_encode($menu_for_JS);?>;
    let icon_bar = $('#main_menus');
    let main_menu = $('#main_menu');
    let parent_menu = $('.parent_menu');
    let menu_items = $('.menu_items');
    let class_active = '';
    let hrefs = window.location.pathname;

    $('.overlay_top').on('click', function () {
        $(icon_bar).removeAttr('style');
        $(icon_bar).find('.span_bar').removeClass('open_menu');
        $(main_menu).hide(300);
        $(parent_menu).empty();
        $(menu_items).empty();
        $('.overlay_top').hide();

    });
    $(icon_bar).on('click', function () {
        let that = $(this).find('.span_bar').attr('class');
        //$('body').addClass('overlay_top');
        $('.overlay_top').show();
        if (that === "span_bar") {
            $(this).attr('style', 'flex-direction:row;transition:0.3s;');
            $(this).find('.span_bar').addClass('open_menu');
            $('#main_menu').addClass('main_menu');
            if (menu_items_array !== '') {
                $(menu_items_array.MAIN).each(function (key, value) {
                    if (value.TEXT === 'Кальяны') {
                        class_active = 'active_item_menu';
                    } else {
                        class_active = '';
                    }
                    if (value.HAS_CHILD == 1)
                        var print_strelka = '<i class="fa_icon fa fa-angle-right" aria-hidden="true"></i>';
                    else
                        var print_strelka = '';
                    $(parent_menu).append('<li onclick="location.href=\'' + value.LINK + '\'" class="li_menu_header none_mobile link_js ' + class_active + '" data-role="bx-menu-item">' +
                        '<span class="parent_category_menu"></span>' +
                        '<a class="link_menu_header parent_category" id="' + value.ID + '" href="javascript:void(0)">' +
                        '<span class="text_catalog_link">' + value.TEXT + '</span></a>' + print_strelka + '</li>');
                });

                $.each(menu_items_array.ELEMENT, function (key_item, value_item) {
                    $(parent_menu).find('li.active_item_menu').each(
                        function () {
                            let id = $(this).find('a').attr('id');

                            if (id === key_item) {
                                $(value_item).each(function (i, val) {
                                    $(menu_items).append('<div class="menu-item-line p-0">' +
                                        '<a class="link_menu_header link_menu" href="' + val.LINK + '">' +
                                        '<span class="text_catalog_link">' + val.TEXT + '</span></a></div')
                                })
                            }
                        }
                    );
                });
            }
            $(main_menu).show(300);
        } else {
            $(this).removeAttr('style');
            $(this).find('.span_bar').removeClass('open_menu');
            $(main_menu).hide(300);
            $(parent_menu).empty();
            $(menu_items).empty();
            //$('body').removeClass('overlay_top');
            $('.overlay_top').hide();
        }
        /* $('li.link_js').on('click', function () {
             let id = $(this).find('a').attr('id');
             $(document).find('.active_item_menu').removeClass('active_item_menu');

             $(this).addClass('active_item_menu');
             $(menu_items).hide().empty();

             $.each(menu_items_array.ELEMENT, function (key_item, value_item) {
                 if (id === key_item) {
                     $(value_item).each(function (i, val) {
                         $(menu_items).append('<a class="link_menu_header col-3 link_menu" href="' + val.LINK + '">' +
                             '<span class="text_catalog_link">' + val.TEXT + '</span></a>').show(200);
                     })
                 }
             });
         });*/
        $(document).on('mouseover', 'li.link_js .text_catalog_link', function () {
            let id = $(this).closest('li').find('a').attr('id');
            $(document).find('.active_item_menu').removeClass('active_item_menu');

            $(this).closest('li').addClass('active_item_menu');
            $(menu_items).addClass('hide').empty();

            $.each(menu_items_array.ELEMENT, function (key_item, value_item) {
                if (id === key_item) {
                    $(value_item).each(function (i, val) {
                        $(menu_items).append('<div class="menu-item-line">' +
                            '<a class="link_menu_header link_menu" href="' + val.LINK + '">' +
                            '<span class="text_catalog_link">' + val.TEXT + '</span></a></div>').removeClass('hide');
                    })
                }
            });

        });
    });


</script>