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

    if ($showUserContent || !$showUserContent && $arResult["ALL_ITEMS"][$itemID]['TEXT'] === 'Чай') {
        if ($arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/diskont/'
            && $arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/hit/' && !empty($arResult["ALL_ITEMS"][$itemID]['TEXT'])) {
            if ($arResult["ALL_ITEMS"][$itemID]['DEPTH_LEVEL'] == '1') {
                $menu_for_JS['MAIN'][] = [
                    'LINK' => $arResult["ALL_ITEMS"][$itemID]["LINK"],
                    'TEXT' => $arResult["ALL_ITEMS"][$itemID]["TEXT"],
                    'ID' => $itemID,
                    'HAS_CHILD' => $HAS_CHILD,
                ];
            }
            if (is_array($arColumns) && count($arColumns) > 0) {
                foreach ($arColumns as $key => $arRow) {
                    foreach ($arRow as $itemIdLevel_2 => $arLevel_3) {
                        $childsItems = [];
                        if ($arResult["ALL_ITEMS"][$itemIdLevel_2]['IS_PARENT']) {
                            foreach ($arLevel_3 as $child_id => $child) {
                                $childsItems[$child] = [
                                    'LINK' => $arResult["ALL_ITEMS"][$child]["LINK"],
                                    'TEXT' => $arResult["ALL_ITEMS"][$child]["TEXT"]
                                ];
                            }
                        }
                        $menu_for_JS['ELEMENT'][$itemID][$itemIdLevel_2] = [
                            'LINK' => $arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"],
                            'TEXT' => $arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"],
                            'ELEMENT' => $childsItems
                        ];

                    }
                }
                uasort($menu_for_JS['ELEMENT'][$itemID], 'sort_by_name_menu');
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
        <div class="open_menu main_menu" style="display: none" id="main_menu">
            <div class="parent_menu"></div>
            <div class="menu_items hide"></div>
            <div class="menu_items_child hide"></div>
        </div>
    </ul>
</nav>
<script type="text/javascript">
    const menu_items_array = <?= json_encode($menu_for_JS);?>;
    let class_active = '';

    $('#main_menus').on('click', function () {
        $('.overlay_top').show();

        const openButton = $(this).find('.span_bar');
        if (!openButton.hasClass('open_menu')) {
            $(this).attr('style', 'flex-direction:row;transition:0.3s;');
            openButton.addClass('open_menu')

            if (Object.keys(menu_items_array?.MAIN)?.length > 0) {
                $(menu_items_array.MAIN).each(function (key, value) {
                    let print_strelka = '';
                    let class_active = '';

                    if (value.TEXT === 'Кальяны') {
                        class_active = 'active_item_menu';
                    }

                    if (value.HAS_CHILD == 1)
                        print_strelka = '<i class="fa_icon fa fa-angle-right" aria-hidden="true"></i>';

                    if (value.LINK !== '' && value.LINK !== null && value.TEXT !== '' && value.TEXT !== null
                        && value.TEXT !== ' ') {
                        $('.parent_menu').append('<li onclick="location.href=\'' + value.LINK + '\'" ' +
                            'class="li_menu_header none_mobile link_js ' + class_active + '" data-role="bx-menu-item">' +
                            '<span class="parent_category_menu"></span>' +
                            '<a class="link_menu_header parent_category" id="' + value.ID + '" href="javascript:void(0)">' +
                            '<span class="text_catalog_link">' + value.TEXT + '</span></a>' + print_strelka + '</li>');
                    }
                });

                $.each(menu_items_array.ELEMENT, function (key_item, value_item) {
                    $('.parent_menu').find('li.active_item_menu').each(
                        function () {
                            if ($(this).find('a').attr('id') === key_item) {
                                createItemMenu(value_item, $('.menu_items'), key_item)
                            }
                        }
                    );
                });
            }
            $('#main_menu').show(300);
        } else {
            closeMenu($(this))
        }

        $(document).on('mouseover', 'li.link_js .text_catalog_link', function () {

            const li = $(this).closest('li')
            const id = li.find('a').attr('id');

            li.addClass('active_item_menu')
            $(document).find('.active_item_menu').removeClass('active_item_menu');
            $('.menu_items').addClass('hide').empty();

            $.each(menu_items_array.ELEMENT, function (key_item, value_item) {
                if (id === key_item) {
                    createItemMenu(value_item, $('.menu_items'), id)
                }
            });

        });
    });

    //
    $(document).on('click', '.child_js', function () {
        const that = $(this);
        const id = $(that).attr('id');
        const parentId = $(that).attr('data-parent-id');
        $('.menu_items_child').addClass('hide').empty();

        $.each(menu_items_array.ELEMENT, function (key_item, value_parent) {

            if (parentId === key_item) {

                $.each(value_parent, function (key_child, value_item) {
                    if (id === key_child) {
                        createItemMenu(value_item.ELEMENT, $('.menu_items_child'), parentId)
                    }
                });
            }
        });

    });

    $('.overlay_top').on('click', function () {
        closeMenu($('#main_menus'))
    });

    function closeMenu(icon_bar) {
        $(icon_bar).removeAttr('style');
        $(icon_bar).find('.span_bar').removeClass('open_menu');
        $('#main_menu').hide(300);
        $('.parent_menu').empty();
        $('.menu_items').empty();
        $('.menu_items_child').empty();
        $('.overlay_top').hide();
    }

    function createItemMenu(value_item, menu_items, parentId = 0) {
        $.each(value_item, function (i, val) {
            let down = '';
            let classChild = '';
            if (typeof val.ELEMENT === "object" && Object.keys(val.ELEMENT)?.length > 0) {
                down = '<i class="fa_icon fa fa-angle-right" aria-hidden="true"></i>';
                classChild = 'child_js';
            }

            const item = '<div class="menu-item-line col-6">' +
                '<a class="link_menu_header link_menu d-flex align-items-center justify-content-between ' + classChild + '" ' +
                'href="' + val.LINK + '" data-parent-id="' + parentId + '" id="' + i + '">' +
                '<span class="text_catalog_link">' + val.TEXT + '</span> ' + down + ' </a>' +
                '</div>';

            $(menu_items).append(item).removeClass('hide');
        })
    }
</script>