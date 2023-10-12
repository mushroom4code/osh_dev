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
    if (strripos($arResult["ALL_ITEMS"][$itemID]["LINK"], '/diskont/') === false
        && $arResult["ALL_ITEMS"][$itemID]["LINK"] !== '/catalog/hit/') {


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
    <ul class="ul_menu flex items-center flex-row relative">
        <li class="li_menu_header mr-7">
            <div class="dark:text-textDark shadow-md flex flex-row justify-center items-center text-textLight
            dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 w-48"
                 title="Просмотреть меню"
                 id="open_menu">
                <div class="mr-4 span_bar">
                    <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 14H8.58333M1 1H18.3333H1ZM1 7.5H18.3333H1Z" stroke="white" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
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
        <div class="open_menu absolute top-10 flex-row bg-filterGray rounded-xl p-8 dark:bg-darkBox max-w-6xl w-full shadow-md"
             style="display: none"
             id="main_menu">
            <div class="parent_menu mr-5 w-full max-w-xs"></div>
            <div class="menu_items hidden flex-row flex-wrap w-full content-start pl-4 border-l border-textDarkLightGray
             dark:border-tagFilterGray"></div>
        </div>
    </ul>
</nav>
<div class="overlay_top"></div>
<script type="text/javascript">
    let menu_items_array = <?= json_encode($menu_for_JS);?>;
    let icon_bar = $('#open_menu');
    let main_menu = $('#main_menu');
    let parent_menu = $('.parent_menu');
    let menu_items = $('.menu_items');
    let class_active = '';
    let hrefs = window.location.pathname;
    console.log(menu_items_array)
    $('.overlay_top').on('click', function () {
        $(icon_bar).removeAttr('style');
        $(icon_bar).find('.span_bar').removeClass('open_menu');
        $(main_menu).hide(300);
        $(parent_menu).empty();
        $(menu_items).empty();
        $('.overlay_top').hide();

    });
    $(icon_bar).on('click', function () {
        $('.parent_menu').empty();
        $('.menu_items').empty();
        $('.overlay_top').show();
        if (!$(this).find('.span_bar').hasClass('open_menu')) {
            $(this).attr('style', 'flex-direction:row;transition:0.3s;');
            $(this).find('.span_bar').addClass('open_menu');
            $('#main_menu').addClass('main_menu flex');
            if (menu_items_array?.MAIN.length !== 0) {
                $(menu_items_array.MAIN).each(function (key, value) {
                    class_active = '';
                    if (value.TEXT === 'Кальяны') {
                        class_active = 'active_item_menu';
                    }

                    if (value.HAS_CHILD == 1)
                        var print_strelka = '<i class="fa_icon fa fa-angle-right" aria-hidden="true"></i>';
                    else
                        var print_strelka = '';

                    $(parent_menu).append('<li onclick="location.href=\'' + value.LINK + '\'" class="li_menu_header mb-3 mr-3 link_js ' + class_active + '" data-role="bx-menu-item">' +
                        '<span class="parent_category_menu"></span>' +
                        '<a class="link_menu_header parent_category" id="' + value.ID + '" href="javascript:void(0)">' +
                        '<span class="text_catalog_link text-md dark:font-medium text-dark dark:text-borderColor ' +
                        'hover:text-hover-red font-semibold dark:hover:text-white flex flex-row items-center">' +
                        '<svg width="40" height="16" viewBox="0 0 60 36" fill="none" class="mr-3" xmlns="http://www.w3.org/2000/svg">' +
                        '<path d="M59.2924 2.38227C58.8456 2.20106 58.1322 1.82441 57.282 1.59762C56.4318 1.35773 55.2831 0.958853 54.1115 0.858564C52.928 0.734913 51.5901 0.698444 50.1279 0.769102C48.7005 0.885346 47.2514 1.18849 45.6975 1.59022C44.1453 1.9908 42.7253 2.65749 41.2085 3.38687C39.7281 4.14131 38.3787 5.08266 37.0277 6.10777C35.7507 7.1665 34.5393 8.33862 33.4315 9.58482C32.3985 10.8652 31.3716 12.1826 30.5807 13.5884C29.7653 14.9845 29.0621 16.4221 28.5533 17.8923C27.4649 20.8029 27.0313 23.7979 26.9857 26.4652C26.9897 27.8186 27.0695 29.0744 27.237 30.2283C27.3835 31.3754 27.6706 32.4341 27.8923 33.29C27.9818 33.6917 28.1077 34.0758 28.2319 34.4154C28.4502 33.9573 28.6872 33.474 28.9516 32.9453C30.265 30.34 32.1796 26.9473 34.4185 23.77C35.5336 22.179 36.757 20.6662 37.9497 19.2986C38.5816 18.6416 39.1924 18.0096 39.7691 17.4102C40.3891 16.8535 40.9743 16.3258 41.5105 15.8403C42.0923 15.4033 42.6222 15.0055 43.094 14.6511C43.5465 14.2756 44.02 14.0574 44.375 13.8317C45.0987 13.4032 45.5118 13.1587 45.5118 13.1587C45.5118 13.1587 45.118 13.4368 44.4314 13.9178C44.0975 14.173 43.649 14.4209 43.2279 14.8283C42.7914 15.2135 42.2997 15.6472 41.7618 16.1201C41.2689 16.6421 40.7361 17.2022 40.1725 17.7971C39.654 18.429 39.1058 19.0957 38.5406 19.7863C37.4739 21.2234 36.4015 22.791 35.4362 24.4332C33.4925 27.7029 31.8902 31.1611 30.8121 33.7931C30.5089 34.5345 30.2536 35.1972 30.0343 35.7824C30.4958 35.6593 31.0822 35.5288 31.7147 35.2952C32.517 35.0166 33.5529 34.7294 34.5569 34.3197C35.5849 33.9265 36.6807 33.4621 37.7861 32.9333C38.9092 32.4125 40.0033 31.8005 41.1241 31.1566C42.2438 30.5155 43.3043 29.7822 44.3579 29.0277C45.3813 28.2436 46.3973 27.4516 47.3193 26.5746C48.2304 25.6732 49.1507 24.8276 49.9461 23.8503C51.5821 21.9762 52.9759 19.9715 54.1115 17.8558C54.7024 16.8592 55.1936 15.699 55.6751 14.6505C56.1623 13.61 56.576 12.4556 56.988 11.3969C57.4034 10.3575 57.7875 9.30447 58.1265 8.27879C58.4952 7.22234 58.8171 6.30151 59.0844 5.54478C59.3926 4.79205 59.5852 4.00512 59.759 3.54186C59.9192 3.01876 60.0012 2.7424 60.0012 2.7424C60.0012 2.7424 59.7528 2.59196 59.2924 2.38227Z" ' +
                        'class="fill-light-red dark:fill-tagFilterGray"/> ' +
                        '<path d="M23.0374 25.802C21.4464 24.6863 19.9353 23.4623 18.5671 22.2685C17.9118 21.6406 17.2793 21.028 16.6787 20.4514C16.1209 19.8314 15.5966 19.2462 15.1072 18.71C14.6712 18.1305 14.2746 17.596 13.9185 17.1265C13.543 16.6735 13.3276 16.2005 13.1002 15.8455C12.6706 15.1235 12.4267 14.7087 12.4267 14.7087C12.4267 14.7087 12.7048 15.1025 13.188 15.7891C13.4393 16.1224 13.6889 16.5703 14.0986 16.9914C14.4838 17.4296 14.9168 17.9214 15.3904 18.4593C15.9112 18.9505 16.4725 19.485 17.0673 20.0462C17.6981 20.5648 18.3671 21.113 19.0566 21.6782C20.492 22.7409 22.0613 23.8173 23.7024 24.7826C24.3816 25.1883 25.0699 25.545 25.756 25.9114C25.9828 22.2418 26.7361 18.6143 27.8638 15.3105C27.2176 14.4751 26.5623 13.6528 25.8415 12.9001C24.9406 11.9872 24.0984 11.067 23.1177 10.2727C21.2424 8.63669 19.2401 7.24348 17.1249 6.10611C16.1283 5.51521 14.9664 5.02402 13.9179 4.54309C12.8797 4.05589 11.7264 3.64049 10.6671 3.23079C9.62661 2.8171 8.57472 2.43418 7.5479 2.09513C6.49202 1.72361 5.57346 1.40166 4.81389 1.13441C4.06116 0.827849 3.27252 0.63354 2.81097 0.459744C2.29015 0.301903 2.01265 0.21814 2.01265 0.21814C2.01265 0.21814 1.85822 0.469432 1.64682 0.929278C1.46733 1.37488 1.0901 2.08944 0.862174 2.93961C0.623419 3.78694 0.223403 4.93627 0.125964 6.10839C3.31858e-05 7.29305 -0.0358656 8.6327 0.0365018 10.0943C0.149327 11.5206 0.451902 12.9673 0.854766 14.5235C1.2582 16.0746 1.92318 17.4963 2.65142 19.0143C3.407 20.4907 4.34721 21.84 5.37574 23.1934C6.4339 24.4726 7.60602 25.6835 8.85165 26.7929C10.1298 27.8226 11.45 28.85 12.8581 29.6438C14.2513 30.4592 15.6895 31.1629 17.1591 31.67C20.0686 32.7601 23.0636 33.1932 25.7332 33.2399C25.8472 33.2399 25.9446 33.2279 26.0529 33.2245C25.7674 31.3544 25.6637 29.4455 25.699 27.5286C24.8129 26.9719 23.9229 26.426 23.0374 25.802Z"' +
                        ' class="fill-light-red dark:fill-tagFilterGray"/> </svg><span> ' + value.TEXT + '</span></span></a>' + print_strelka + '</li>');
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
            $('.overlay_top').hide();
        }
        $(document).on('mouseover', 'li.link_js .text_catalog_link', function () {

            let id = $(this).closest('li').find('a').attr('id');
            $(document).find('.active_item_menu').removeClass('active_item_menu');

            $(this).closest('li').addClass('active_item_menu');
            $(menu_items).addClass('hidden').empty();

            $.each(menu_items_array.ELEMENT, function (key_item, value_item) {
                if (id === key_item) {
                    $(value_item).each(function (i, val) {
                        $(menu_items).append('<div class="menu-item-line mb-3 mr-3 w-1/4 h-auto">' +
                            '<a class="link_menu_header link_menu" href="' + val.LINK + '">' +
                            '<span class="text_catalog_link text-sm text-dark dark:font-light dark:text-borderColor ' +
                            'dark:hover:text-white text-medium hover:text-hover-red">'
                            + val.TEXT + '</span></a></div>').removeClass('hidden').addClass('flex');
                    })
                }
            });

        });
    });


</script>