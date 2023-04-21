<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CAllMain|CMain $APPLICATION
 */

//use Bitrix\Conversion\Internals\MobileDetect;
//
//$mobile = new MobileDetect();
?>
<!--<div class="row">-->
<!--    <div class="title_footer">-->
<!--        <div class="h1">Новости</div>-->
<!---->
<!--        <a href=".--><?php //echo SITE_DIR ?><!--news/" class="link_menu_catalog link_red_button">Посмотреть все</a></div>-->
<!--    --><?php //if ($mobile->isMobile()) { ?>
<!--        <div class="mobile">-->
<!--            --><?php //$APPLICATION->IncludeComponent(
//                "bitrix:news.list",
//                "oshisha_news.list",
//                array(
//                    "IBLOCK_TYPE" => "news",
//                    "IBLOCK_ID" => "1",
//                    "NEWS_COUNT" => "2",
//                    "SORT_BY1" => "ACTIVE_FROM",
//                    "SORT_ORDER1" => "DESC",
//                    "SORT_BY2" => "SORT",
//                    "SORT_ORDER2" => "ASC",
//                    "FILTER_NAME" => "",
//                    "FIELD_CODE" => array(
//                        0 => "TAGS",
//                        1 => "DATE_CREATE",
//                        2 => "ACTIVE_FROM",
//                    ),
//                    "PROPERTY_CODE" => array(
//                        0 => "",
//                        1 => "",
//                    ),
//                    "CHECK_DATES" => "Y",
//                    "DETAIL_URL" => "",
//                    "AJAX_MODE" => "N",
//                    "AJAX_OPTION_SHADOW" => "Y",
//                    "AJAX_OPTION_JUMP" => "N",
//                    "AJAX_OPTION_STYLE" => "N",
//                    "AJAX_OPTION_HISTORY" => "N",
//                    "CACHE_TYPE" => "A",
//                    "CACHE_TIME" => "36000000",
//                    "CACHE_FILTER" => "N",
//                    "CACHE_GROUPS" => "Y",
//                    "PREVIEW_TRUNCATE_LEN" => "50",
//                    "ACTIVE_DATE_FORMAT" => "d.m.Y",
//                    "DISPLAY_PANEL" => "N",
//                    "SET_TITLE" => "N",
//                    "SET_STATUS_404" => "N",
//                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
//                    "ADD_SECTIONS_CHAIN" => "N",
//                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
//                    "PARENT_SECTION" => "",
//                    "PARENT_SECTION_CODE" => "",
//                    "DISPLAY_NAME" => "Y",
//                    "DISPLAY_TOP_PAGER" => "N",
//                    "DISPLAY_BOTTOM_PAGER" => "N",
//                    "PAGER_SHOW_ALWAYS" => "N",
//                    "PAGER_TEMPLATE" => "",
//                    "PAGER_DESC_NUMBERING" => "N",
//                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
//                    "PAGER_SHOW_ALL" => "N",
//                    "AJAX_OPTION_ADDITIONAL" => "",
//                    "COMPONENT_TEMPLATE" => "oshisha_news.list",
//                    "SET_BROWSER_TITLE" => "Y",
//                    "SET_META_KEYWORDS" => "Y",
//                    "SET_META_DESCRIPTION" => "Y",
//                    "SET_LAST_MODIFIED" => "N",
//                    "INCLUDE_SUBSECTIONS" => "Y",
//                    "DISPLAY_DATE" => "Y",
//                    "DISPLAY_PICTURE" => "Y",
//                    "DISPLAY_PREVIEW_TEXT" => "Y",
//                    "MEDIA_PROPERTY" => "",
//                    "SEARCH_PAGE" => "/search/",
//                    "USE_RATING" => "N",
//                    "USE_SHARE" => "N",
//                    "PAGER_TITLE" => "Новости",
//                    "PAGER_BASE_LINK_ENABLE" => "N",
//                    "SHOW_404" => "N",
//                    "MESSAGE_404" => "",
//                    "TEMPLATE_THEME" => "site",
//                    "STRICT_SECTION_CHECK" => "N",
//                    "SLIDER_PROPERTY" => ""
//                ),
//                false,
//                array(
//                    "ACTIVE_COMPONENT" => "Y"
//                )
//            ); ?>
<!--        </div>-->
<!--    --><?php //} else { ?>
<!--        <div class="desktop">-->
<!--            --><?php //$APPLICATION->IncludeComponent(
//                "bitrix:news.list",
//                "oshisha_news.list",
//                array(
//                    "IBLOCK_TYPE" => "news",
//                    "IBLOCK_ID" => "1",
//                    "NEWS_COUNT" => "3",
//                    "SORT_BY1" => "ACTIVE_FROM",
//                    "SORT_ORDER1" => "DESC",
//                    "SORT_BY2" => "SORT",
//                    "SORT_ORDER2" => "ASC",
//                    "FILTER_NAME" => "",
//                    "FIELD_CODE" => array(
//                        0 => "TAGS",
//                        1 => "DATE_CREATE",
//                        2 => "ACTIVE_FROM",
//                    ),
//                    "PROPERTY_CODE" => array(
//                        0 => "",
//                        1 => "",
//                    ),
//                    "CHECK_DATES" => "Y",
//                    "DETAIL_URL" => "",
//                    "AJAX_MODE" => "N",
//                    "AJAX_OPTION_SHADOW" => "Y",
//                    "AJAX_OPTION_JUMP" => "N",
//                    "AJAX_OPTION_STYLE" => "N",
//                    "AJAX_OPTION_HISTORY" => "N",
//                    "CACHE_TYPE" => "Y",
//                    "CACHE_TIME" => "36000000",
//                    "CACHE_FILTER" => "N",
//                    "CACHE_GROUPS" => "Y",
//                    "PREVIEW_TRUNCATE_LEN" => "100",
//                    "ACTIVE_DATE_FORMAT" => "d.m.Y",
//                    "DISPLAY_PANEL" => "N",
//                    "SET_TITLE" => "N",
//                    "SET_STATUS_404" => "N",
//                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
//                    "ADD_SECTIONS_CHAIN" => "N",
//                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
//                    "PARENT_SECTION" => "",
//                    "PARENT_SECTION_CODE" => "",
//                    "DISPLAY_NAME" => "Y",
//                    "DISPLAY_TOP_PAGER" => "N",
//                    "DISPLAY_BOTTOM_PAGER" => "N",
//                    "PAGER_SHOW_ALWAYS" => "N",
//                    "PAGER_TEMPLATE" => "",
//                    "PAGER_DESC_NUMBERING" => "N",
//                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
//                    "PAGER_SHOW_ALL" => "N",
//                    "AJAX_OPTION_ADDITIONAL" => "",
//                    "COMPONENT_TEMPLATE" => "oshisha_news.list",
//                    "SET_BROWSER_TITLE" => "Y",
//                    "SET_META_KEYWORDS" => "Y",
//                    "SET_META_DESCRIPTION" => "Y",
//                    "SET_LAST_MODIFIED" => "N",
//                    "INCLUDE_SUBSECTIONS" => "Y",
//                    "DISPLAY_DATE" => "Y",
//                    "DISPLAY_PICTURE" => "Y",
//                    "DISPLAY_PREVIEW_TEXT" => "Y",
//                    "MEDIA_PROPERTY" => "",
//                    "SEARCH_PAGE" => "/search/",
//                    "USE_RATING" => "N",
//                    "USE_SHARE" => "N",
//                    "PAGER_TITLE" => "Новости",
//                    "PAGER_BASE_LINK_ENABLE" => "N",
//                    "SHOW_404" => "N",
//                    "MESSAGE_404" => "",
//                    "TEMPLATE_THEME" => "site",
//                    "STRICT_SECTION_CHECK" => "N",
//                    "SLIDER_PROPERTY" => ""
//                ),
//                false,
//                array(
//                    "ACTIVE_COMPONENT" => "Y"
//                )
//            ); ?>
<!--        </div>-->
<!--    --><?php //} ?>
<!--</div>-->

<div class="row box_for_banner_footer">
    <div class="banners_footer mb-2">
        <? $APPLICATION->IncludeComponent(
            "bitrix:advertising.banner",
            "oshisha_banners",
            array(
                "ANIMATION_DURATION" => "500",
                "ARROW_NAV" => "1",
                "BS_ARROW_NAV" => "N",
                "BS_BULLET_NAV" => "N",
                "BS_CYCLING" => "N",
                "BS_EFFECT" => "fade",
                "BS_HIDE_FOR_PHONES" => "Y",
                "BS_HIDE_FOR_TABLETS" => "N",
                "BS_KEYBOARD" => "N",
                "BS_PAUSE" => "Y",
                "BS_WRAP" => "N",
                "BULLET_NAV" => "2",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "Y",
                "COMPONENT_TEMPLATE" => "oshisha_banners",
                "CONTROL_NAV" => "Y",
                "CYCLING" => "N",
                "DEFAULT_TEMPLATE" => "-",
                "DIRECTION_NAV" => "Y",
                "EFFECT" => "random",
                "EFFECTS" => "",
                "HEIGHT" => "400",
                "JQUERY" => "Y",
                "KEYBOARD" => "N",
                "NOINDEX" => "Y",
                "PARALL_HEIGHT" => "400",
                "QUANTITY" => "1",
                "SCALE" => "N",
                "SPEED" => "500",
                "TYPE" => "NEW_MINI_FOOTER",
                "WRAP" => "1"
            ),
            false,
            array(
                "ACTIVE_COMPONENT" => "Y"
            )
        ); ?>
    </div>
    <div class="banners_footer mb-2">
        <?php $APPLICATION->IncludeComponent(
            "bitrix:advertising.banner",
            "oshisha_banners",
            array(
                "ANIMATION_DURATION" => "500",
                "ARROW_NAV" => "1",
                "BS_ARROW_NAV" => "N",
                "BS_BULLET_NAV" => "N",
                "BS_CYCLING" => "N",
                "BS_EFFECT" => "slide",
                "BS_HIDE_FOR_PHONES" => "Y",
                "BS_HIDE_FOR_TABLETS" => "N",
                "BS_KEYBOARD" => "N",
                "BS_PAUSE" => "Y",
                "BS_WRAP" => "N",
                "BULLET_NAV" => "2",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "N",
                "COMPONENT_TEMPLATE" => "oshisha_banners",
                "CONTROL_NAV" => "Y",
                "CYCLING" => "N",
                "DEFAULT_TEMPLATE" => "bootstrap_v4",
                "DIRECTION_NAV" => "Y",
                "EFFECT" => "random",
                "EFFECTS" => "",
                "HEIGHT" => "400",
                "JQUERY" => "Y",
                "KEYBOARD" => "N",
                "NOINDEX" => "Y",
                "PARALL_HEIGHT" => "400",
                "QUANTITY" => "1",
                "SCALE" => "N",
                "SPEED" => "500",
                "TYPE" => "NEW_MINI_FOOTER_2",
                "WRAP" => "1"
            ),
            false,
            array(
                "ACTIVE_COMPONENT" => "Y"
            )
        ); ?>
    </div>
</div>
