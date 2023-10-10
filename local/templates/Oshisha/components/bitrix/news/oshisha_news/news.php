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
if ($arParams["USE_RSS"] == "Y"):?>
    <?php if (method_exists($APPLICATION, 'addheadstring'))
        $APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" title="' . $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["rss"] . '" href="' . $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["rss"] . '" />'); ?>
<?php endif ?>
<h2 class="mt-4 mb-3"><b><?= $APPLICATION->GetTitle(); ?></b></h2>
<div class="mt-2 d-flex flex-row flex-wrap">
    <?php $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "oshisha_news.list",
        array(
            "IBLOCK_TYPE" => "news",
            "IBLOCK_ID" => NEWS_IBLOCK_ID,
            "SORT_BY1" => "ACTIVE_FROM",
            "SORT_ORDER1" => "DESC",
            "SORT_BY2" => "SORT",
            "SORT_ORDER2" => "ASC",
            "FILTER_NAME" => "",
            "FIELD_CODE" => array(
                0 => "TAGS",
                1 => "DATE_CREATE",
                2 => "",
            ),
            "PROPERTY_CODE" => array(
                0 => "",
                1 => "",
            ),
            "CHECK_DATES" => "Y",
            "DETAIL_URL" => "",
            "NEWS_COUNT" => "18",
            "AJAX_MODE" => "N",
            "AJAX_OPTION_SHADOW" => "Y",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "N",
            "AJAX_OPTION_HISTORY" => "N",
            "CACHE_TYPE" => "A",
            "CACHE_TIME" => "36000000",
            "CACHE_FILTER" => "N",
            "CACHE_GROUPS" => "Y",
            "PREVIEW_TRUNCATE_LEN" => "100",
            "ACTIVE_DATE_FORMAT" => "d.m.Y",
            "DISPLAY_PANEL" => "N",
            "SET_TITLE" => "N",
            "SET_STATUS_404" => "N",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
            "ADD_SECTIONS_CHAIN" => "N",
            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
            "PARENT_SECTION" => "",
            "PARENT_SECTION_CODE" => "",
            "DISPLAY_NAME" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "DISPLAY_BOTTOM_PAGER" => "Y",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_TEMPLATE" => "round",
            "PAGER_DESC_NUMBERING" => "N",
            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
            "PAGER_SHOW_ALL" => "N",
            "AJAX_OPTION_ADDITIONAL" => "",
            "COMPONENT_TEMPLATE" => "oshisha_news.list",
            "SET_BROWSER_TITLE" => "Y",
            "SET_META_KEYWORDS" => "Y",
            "SET_META_DESCRIPTION" => "Y",
            "SET_LAST_MODIFIED" => "N",
            "INCLUDE_SUBSECTIONS" => "Y",
            "DISPLAY_DATE" => "Y",
            "DISPLAY_PICTURE" => "Y",
            "DISPLAY_PREVIEW_TEXT" => "Y",
            "MEDIA_PROPERTY" => "",
            "SEARCH_PAGE" => "/search/",
            "USE_RATING" => "N",
            "USE_SHARE" => "N",
            "PAGER_TITLE" => "Блог",
            "PAGER_BASE_LINK_ENABLE" => "N",
            "SHOW_404" => "N",
            "MESSAGE_404" => "",
            "TEMPLATE_THEME" => "site",
            "STRICT_SECTION_CHECK" => "N",
            "SLIDER_PROPERTY" => ""
        ),
        false,
        array(
            "ACTIVE_COMPONENT" => "Y"
        )
    ); ?>
</div>