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
// Переменная для убора функционала под мобильное приложение
$showUserContent = Enterego\PWA\EnteregoMobileAppEvents::getUserRulesForContent();
$menu_for_JS = [];
foreach ($arResult["MENU_STRUCTURE"] as $itemID => $arColumns) {
    $HAS_CHILD = 0;
    if (is_array($arColumns) && count($arColumns) > 0)
        $HAS_CHILD = 1;

    if ($showUserContent || !$showUserContent && $arResult["ALL_ITEMS"][$itemID]['TEXT'] === 'Чай'
        || !$showUserContent && $arResult["ALL_ITEMS"][$itemID]['TEXT'] === 'Уголь') {
        if ($arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/diskont/'
            && $arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/hit/' && !empty($arResult["ALL_ITEMS"][$itemID]['TEXT'])) {
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
}
?>
<nav class="box_with_menu_header">
    <ul class="ul_menu">
        <li class="li_menu_header">
            <a class="link_menu_header_catalog link_red_button" title="Просмотреть меню" href="javascript:void(0)"
               id="main_menus">
                <div class="span_bar">
                    <span class="bar_span span_1"></span>
                    <span class="bar_span span_2"></span>
                    <span class="small_bar span_3"></span>
                </div>
                <span class="text_catalog_button">Каталог</span>
            </a>
        </li>

        <?php
        function sort_by_name_menu($a, $b)
        {
            if ($a["TEXT"] == $b["TEXT"]) {
                return 0;
            }
            return ($a["TEXT"] < $b["TEXT"]) ? -1 : 1;
        }

        $result = json_encode($menu_for_JS);
        if ($showUserContent) { ?>
            <li class="li_menu_header  none_mobile" data-role="bx-menu-item">
                <a class="link_menu_header" href="/diskont/">
                    <span class="text_catalog_link">Дисконт</span>
                </a>
            </li>
            <li class="li_menu_header  none_mobile" data-role="bx-menu-item">
                <a class="link_menu_header" href="/catalog_new/">
                    <span class="text_catalog_link">Новинки</span>
                </a>
            </li>
            <li class="li_menu_header  none_mobile" data-role="bx-menu-item">
                <a class="link_menu_header" href="/hit/">
                    <span class="text_catalog_link">Хиты</span>
                </a>
            </li>
            <li class="li_menu_header none_mobile" data-role="bx-menu-item">
                <a class="link_menu_header" href="/news/">
                    <span class="text_catalog_link">Блог</span>
                </a>
            </li>
        <?php } ?>
        <!--		<li class="li_menu_header  none_mobile" data-role="bx-menu-item">-->
        <!--            <a class="link_menu_header" href="/promotions/">-->
        <!--                <span class="text_catalog_link">Акции</span>-->
        <!--            </a>-->
        <!--        </li>		-->
        <div class="open_menu" style="display: none" id="main_menu">
            <div class="parent_menu"></div>
            <div class="menu_items hide"></div>
        </div>
    </ul>
</nav>
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

                    if (value.LINK !== '' && value.LINK !== null && value.TEXT !== ''
                        && value.TEXT !== null && value.TEXT !== ' ') {
                        $(parent_menu).append('<li onclick="location.href=\'' + value.LINK + '\'" class="li_menu_header none_mobile link_js ' + class_active + '" data-role="bx-menu-item">' +
                            '<span class="parent_category_menu"></span>' +
                            '<a class="link_menu_header parent_category" id="' + value.ID + '" href="javascript:void(0)">' +
                            '<span class="text_catalog_link">' + value.TEXT + '</span></a>' + print_strelka + '</li>');
                    }
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