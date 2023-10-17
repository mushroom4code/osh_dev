<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Enterego\EnteregoHitsHelper;
use Bitrix\Catalog;


/**
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var array $arParams
 * @var array $arResult
 * @var array $arCurSection
 * @var array $item
 */
if (isset($arParams['USE_COMMON_SETTINGS_BASKET_POPUP']) && $arParams['USE_COMMON_SETTINGS_BASKET_POPUP'] == 'Y') {
    $basketAction = isset($arParams['COMMON_ADD_TO_BASKET_ACTION']) ? $arParams['COMMON_ADD_TO_BASKET_ACTION'] : '';
} else {
    $basketAction = isset($arParams['SECTION_ADD_TO_BASKET_ACTION']) ? $arParams['SECTION_ADD_TO_BASKET_ACTION'] : '';
}

$sort = [
    'by' => [
        'by' => 'SERVICE_FIELD_POPULARITY', // 'PROPERTY_MINIMUM_PRICE',//'CATALOG_PRICE_'.$GLOBALS['PRICE_TYPE_ID'],
        'order' => 'DESC'
    ],
];

if ($_GET['sort_by']) {
    $_SESSION['sort']['by']['by'] = $_GET['sort_by'];
    $_SESSION['sort']['by']['order'] = $_GET['sort_order'];
}

if ($_SESSION['sort']) {
    $sort = [
        'by' => [
            'by' => $_SESSION['sort']['by']['by'],
            'order' => $_SESSION['sort']['by']['order']
        ],
    ];

    $defSortByVal = $_SESSION['sort']['by']['by'];
    $defSortOrder = $_SESSION['sort']['by']['order'];
    $defLim = $_SESSION['sort']['limit'];

    $ELEMENT_SORT_FIELD = $sort['by']['by'];
    $ELEMENT_SORT_ORDER = strtoupper($sort['by']['order']);
    $ELEMENT_SORT_FIELD2 = 'SORT';
    $ELEMENT_SORT_ORDER2 = 'ASC';
} else {
    $ELEMENT_SORT_FIELD2 = $sort['by']['by'];
    $ELEMENT_SORT_ORDER2 = strtoupper($sort['by']['order']);
    $ELEMENT_SORT_FIELD = 'SORT';
    $ELEMENT_SORT_ORDER = 'ASC';
}
if (empty($defSortByVal))
    $defSortBy = 'SORT_FIELD_3';

$sortBy = array(
    'SORT_FIELD_1',
    'SORT_FIELD_2',
    'SORT_FIELD_3',
    'SORT_FIELD_4',
    'SORT_FIELD_5',
    'SORT_FIELD_6',
    'SORT_FIELD_7',
    'SORT_FIELD_8',
    'SORT_FIELD_9'
);

$codeSortBy = array(
    'PROPERTY_MINIMUM_PRICE',//'CATALOG_PRICE_'.$GLOBALS['PRICE_TYPE_ID'],
    'PROPERTY_MINIMUM_PRICE',//'CATALOG_PRICE_'.$GLOBALS['PRICE_TYPE_ID'],
    'SORT',
    'SORT',
    'CREATED_DATE',
    'CREATED_DATE',
    'NAME',
    'NAME',
    'ID',
);
$sortOrder = array(
    'asc',
    'desc',
    'desc',
    'asc',
    'desc',
    'asc',
    'asc',
    'desc',
    'desc',
);
//print_r($sortBy);

$activeSort = 0;
$arBasketItems = array();
$dbBasketItems = CSaleBasket::GetList(
    array("NAME" => "ASC", "ID" => "ASC"),
    array("FUSER_ID" => $fUser, "LID" => SITE_ID, "ORDER_ID" => "NULL"),
    false,
    false,
    array("ID", "PRODUCT_ID", "QUANTITY",)
);
while ($arItems = $dbBasketItems->Fetch()) {
    if (strlen($arItems["CALLBACK_FUNC"]) > 0) {
        CSaleBasket::UpdatePrice($arItems["ID"],
            $arItems["CALLBACK_FUNC"],
            $arItems["MODULE"],
            $arItems["PRODUCT_ID"],
            $arItems["QUANTITY"]);
        $arItems = CSaleBasket::GetByID($arItems["ID"]);
    }

    $arBasketItems[$arItems["PRODUCT_ID"]] = $arItems["QUANTITY"];
}
// Печатаем массив, содержащий актуальную на текущий момент корзину
foreach ($sortBy as $key => $item) {
    if (empty($item))
        continue;


    if (!isset($defSortByVal) && isset($defSortBy) && $item == $defSortBy) {
        $defSortByVal = $codeSortBy[$key];
        $activeSort = $key;
    } elseif (isset($defSortByVal) && !isset($defSortBy) && $defSortByVal == $codeSortBy[$key] && $defSortOrder == $sortOrder[$key]) {
        $activeSort = $key;
        $defSortBy = $item;
    }
}

if ($_SESSION['sort']['by']['by'])
    $defSortByVal = $_SESSION['sort']['by']['by'];

if (!isset($sort['by']['by']) || empty($sort['by']['by']) && !empty($defSortByVal))
    $sort['by']['by'] = $defSortByVal;

if (!isset($sort['by']['order']) || empty($sort['by']['order']))
    $sort['by']['order'] = $sortOrder[(array_search($defSortBy, $sortBy) !== false ? array_search($defSortBy, $sortBy) : 'asc')];

$contentBlockClass = "col";

function sort_by_name($a, $b)
{
    if ($a["NAME"] == $b["NAME"]) {
        return 0;
    }
    return ($a["NAME"] < $b["NAME"]) ? -1 : 1;
}

$catalogElementField = $APPLICATION->get_cookie("PAGE_ELEMENT_COUNT") ? $APPLICATION->get_cookie("PAGE_ELEMENT_COUNT") : "24";
if ($_GET['page'] != '') {
    $APPLICATION->set_cookie("PAGE_ELEMENT_COUNT", $_GET['page'], false, "/", SITE_SERVER_NAME);
    $catalogElementField = intval($_GET['page']);
}
$arParams["PAGE_ELEMENT_COUNT"] = $catalogElementField;

//ORIENTATION
/**
 * @param string $itemType
 * @return string
 */
function setActiveColor(string $itemType = 'card'): string
{
    $type = 'card';
    if (!empty($_COOKIE['orientation'])) {
        $type = $_COOKIE['orientation'];
    }
    return $type === $itemType
        ? 'fill-lightGrayBg dark:fill-gray-slider-arrow' :
        'stroke-lightGrayBg dark:fill-darkBox';
}

?>
<div class="flex mb-4 flex-col mt-5 w-auto">
    <div class="flex mb-4 box_with_prod md:flex-row flex-col mt-5 w-auto">
        <?php if ($isFilter) : ?>
            <div class=" box_filter_catalog w-80 xl:flex hidden flex-col  <?= (isset($arParams['FILTER_HIDE_ON_MOBILE']) &&
            $arParams['FILTER_HIDE_ON_MOBILE'] === 'Y' ? ' d-none d-sm-block' : '') ?>">
                <div class="catalog-section-list-tile-list w-full bg-filterGray dark:bg-darkBox p-5 rounded-xl
             flex flex-col mb-4">
                    <?php foreach ($arResult['SECTION_LIST'] as $arSection): ?>
                        <div class="catalog-section-list-item-l">
                            <div class="catalog-section-list-item-wrap smart-filter-tog flex flex-row cursor-pointer justify-between"
                                 data-role="prop_angle"
                                 data-code-vis="<?= $arSection['ID'] ?>">
                                <a href="javascript:void(0)"
                                   class="text-sm font-semibold dark:font-medium text-dark dark:text-textDarkLightGray"><?= $arSection['NAME'] ?>
                                </a>
                                <?php if ($arSection['CHILDS']): ?>
                                    <span data-role="prop_angle"
                                          class="smart-filter-tog smart-filter-angle">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                         xmlns="http://www.w3.org/2000/svg" class="smart-filter-angles">
                                        <path d="M1.89089 4.49273C1.50382 4.88766 1.50382 5.52803 1.89089 5.92295L6.73974 10.8657C7.51402 11.6549 8.76861 11.6546 9.54249 10.8651L14.3895 5.91931C14.7766 5.52439 14.7766 4.88402 14.3895 4.48908C14.0024 4.09413 13.3748 4.09413 12.9878 4.48908L8.83927 8.72208C8.45223 9.1171 7.82464 9.117 7.4376 8.72208L3.29257 4.49273C2.90551 4.09778 2.27795 4.09778 1.89089 4.49273Z"
                                              fill="#838383"></path>
                                    </svg>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="catalog-section-list-item-sub
                        <?php if ($smartFil != '') { ?>active mb-2 mt-2  <?php } else { ?>hidden<?php } ?>"
                                 data-code="<?= $arSection['ID'] ?>">
                                <a class="font-semibold text-light-red dark:text-white text-sm"
                                   href="<?= $arSection['SECTION_PAGE_URL'] ?>">Все</a>
                            </div>
                            <div class="overflow-auto max-h-96 p-2">
                                <?php if ($arSection['CHILDS']):
                                    usort($arSection['CHILDS'], 'sort_by_name');
                                    foreach ($arSection['CHILDS'] as $arSectionSub):
                                        if (CIBlockSection::GetSectionElementsCount($arSectionSub['ID'], ['CNT_ACTIVE' => 'Y']) > 0) {
                                            ?>
                                            <div class="catalog-section-list-item-sub mb-2 <?php if ($smartFil != '') { ?>active<?php } else { ?>hidden<?php } ?>"
                                                 data-code="<?= $arSection['ID'] ?>">
                                                <a href="<?= $arSectionSub['SECTION_PAGE_URL'] ?>"
                                                   class="font-light text-dark dark:text-textDarkLightGray hover:text-light-red
                                            dark:hover:text-white text-sm">
                                                    <?= $arSectionSub['NAME'] ?></a>
                                            </div>
                                        <?php }
                                    endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
                <?php

                //region Filter
                if ($isFilter): ?>
                    <div class="bx-sidebar-block bg-filterGray dark:bg-darkBox p-5 rounded-xl <?= EnteregoHitsHelper::checkIfHits($APPLICATION) ? 'd-none' : '' ?>">
                        <?php

                        $APPLICATION->IncludeComponent("bitrix:catalog.smart.filter",
                            "oshisha_catalog.smart.filter", array(
                                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                "SECTION_ID" => $arCurSection['ID'],
                                "PREFILTER_NAME" => $arParams["PREFILTER_NAME"],
                                "FILTER_NAME" => $arParams["FILTER_NAME"],
                                "PRICE_CODE" => $arParams["PRICE_CODE"],
                                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                "CACHE_TIME" => $arParams["CACHE_TIME"],
                                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                "SAVE_IN_SESSION" => "Y",
                                "FILTER_VIEW_MODE" => $arParams["FILTER_VIEW_MODE"],
                                "XML_EXPORT" => "N",
                                "SECTION_TITLE" => "NAME",
                                "SECTION_DESCRIPTION" => "DESCRIPTION",
                                'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                                "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                "SEF_MODE" => $arParams["SEF_MODE"],
                                "SEF_RULE" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["smart_filter"],
                                "SMART_FILTER_PATH" => $arResult["VARIABLES"]["SMART_FILTER_PATH"],
                                "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                                "INSTANT_RELOAD" => $arParams["INSTANT_RELOAD"],
                            ),
                            array('HIDE_ICONS' => 'Y')
                        );
                        ?>
                    </div>
                <?php endif
                //			//endregion
                ?>
                <div class="filter-close js__filter-close">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="filter-view-bar hidden">
                    <div class="red_button_cart filter-view js__filter-close disabled_class">Применить</div>
                </div>
            </div>
        <?php endif ?>
        <?php global $GLOBAL_SECTION; ?>
        <div class="pb-4 <?= (($isFilter) ? "" : "col") ?> max-w-full w-fit ml-11">
            <div class="row navigation-wrap mb-5">
                <div class="col" id="navigation">
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:breadcrumb",
                        "oshisha_breadcrumb",
                        array(
                            "START_FROM" => "0",
                            "PATH" => "",
                            "SITE_ID" => "-"
                        ),
                        false,
                        array('HIDE_ICONS' => 'Y')
                    ); ?>
                </div>
            </div>
            <h1 class="text-3xl mb-2 font-semibold dark:font-medium"><?php $APPLICATION->ShowTitle(false); ?></h1>
            <p class="message_for_user_minzdrav text-sm text-textLight dark:text-iconGray dark:font-light mb-5"></p>
            <div id="osh-filter-horizontal2"></div>
            <div class="osh-block-panel <?= EnteregoHitsHelper::checkIfHits($APPLICATION) ? 'd-none' : '' ?>">
                <div id="osh-filter-horizontal" class="flex flex-col mb-5 mt-5">
                    <div class="flex flex-row justify-between items-center mb-6">
                        <div class="col_navigation mr-4">
                            <div class="count-per-page flex flex-row items-center">
                                <span class="font-semibold dark:font-normal text-md mr-3 text-textLight dark:text-textDarkLightGray">Показать</span>
                                <a href="?page=24"
                                   class="page_num py-2.5 px-3 rounded-full text-sm font-medium mr-1
                               <?php if ($arParams['PAGE_ELEMENT_COUNT'] == 24) { ?>
                               dark:bg-grayButton bg-lightGrayBg active text-white<?php } else { ?> bg-filterGray dark:bg-darkBox<?php } ?>">24</a>
                                <a href="?page=36"
                                   class="page_num py-2.5 px-3 rounded-full text-sm font-medium mr-1
                               <?php if ($arParams['PAGE_ELEMENT_COUNT'] == 36) { ?>
                               dark:bg-grayButton bg-lightGrayBg active text-white<?php } else { ?> bg-filterGray dark:bg-darkBox<?php } ?>">36</a>
                                <a href="?page=72"
                                   class="page_num py-2.5 px-3 rounded-full text-sm font-medium
                               <?php if ($arParams['PAGE_ELEMENT_COUNT'] == 72) { ?>
                               dark:bg-grayButton bg-lightGrayBg active text-white<?php } else { ?> bg-filterGray dark:bg-darkBox<?php } ?>">72</a>
                            </div>
                        </div>
                        <div class="flex flex-row">
                            <div class="sort-panel">
                                <div class="sort-panel-flex d-flex flex-row justify-content-end align-items-center ">
                                    <div class="sort_panel_wrap">
                                        <div class="sort_panel relative" id="">
                                            <a class="sort_order sort_tool bg-filterGray dark:bg-darkBox p-3 rounded-lg"
                                               href="#">
                                            <span class="sort_orders_by sort_caption text-sm text-textLight font-medium
                                            dark:text-textDarkLightGray"
                                                  style="min-width: 150px;">
                                                Сортировать по</span>
                                                <i class="fa fa-angle-down text-light-red dark:text-white text-xl font-semibold"
                                                   aria-hidden="true"></i>
                                            </a>
                                            <div class="sort_orders_element js__sort_orders_element hidden absolute
                                        bg-filterGray dark:bg-darkBox z-20 p-3 w-full">
                                                <ul>
                                                    <li class="catalog_sort_item mb-2 cursor-pointer
                                                    hover:text-light-red dark:hover:text-white js__catalog-sort-item text-xs"
                                                        data-sort="<?= 'PROPERTY_' . SORT_POPULARITY ?>"
                                                        data-order="DESC">По популярности
                                                    </li>
                                                    <li class="catalog_sort_item mb-2 cursor-pointer
                                                    hover:text-light-red dark:hover:text-white js__catalog-sort-item text-xs"
                                                        data-price-id="<?= $GLOBALS['PRICE_TYPE_ID'] ?>"
                                                        data-sort="<?= 'PROPERTY_' . SORT_PRICE ?>"
                                                        data-order="ASC">По возрастанию цены
                                                    </li>
                                                    <li class="catalog_sort_item mb-2 cursor-pointer
                                                    hover:text-light-red dark:hover:text-white js__catalog-sort-item text-xs"
                                                        data-price-id="<?= $GLOBALS['PRICE_TYPE_ID'] ?>"
                                                        data-sort="<?= 'PROPERTY_' . SORT_PRICE ?>"
                                                        data-order="DESC">По убыванию цены
                                                    </li>
                                                    <li class="catalog_sort_item mb-2 cursor-pointer
                                                    hover:text-light-red dark:hover:text-white js__catalog-sort-item text-xs"
                                                        data-sort="NAME"
                                                        data-order="ASC">По названию
                                                    </li>
                                                    <li class="catalog_sort_item mb-2 cursor-pointer
                                                     hover:text-light-red dark:hover:text-white js__catalog-sort-item text-xs"
                                                        data-sort="CREATED_DATE"
                                                        data-order="DESC">По новизне
                                                    </li>
                                                    <li class="catalog_sort_item cursor-pointer
                                                    hover:text-light-red dark:hover:text-white js__catalog-sort-item text-xs"
                                                        data-sort="<?= 'PROPERTY_' . SORT_BREND ?>"
                                                        data-order="DESC">По бренду
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!--                                TODO - убрать этот мусор на моб версии -->
                                    <div class="button_panel_wrap">
                                        <div class="sort_mobile"></div>
                                        <div class="icon_sort_bar xs-d-none" id="card_catalog"></div>
                                        <div class="icon_sort_line xs-d-none" id="line_catalog"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-row">
                                <a href="javascript:void(0)"
                                   onclick="BX.setCookie('orientation','card'); window.location.reload()"
                                   class="ml-3">
                                    <svg width="32" height="32" viewBox="0 0 28 28" fill="none"
                                         class="<?= setActiveColor('card') ?>"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <rect x="1" y="1" width="11.6418" height="11.6418" rx="1.5"/>
                                        <rect x="1" y="15.3582" width="11.6418" height="11.6418" rx="1.5"/>
                                        <rect x="15.3584" y="1" width="11.6418" height="11.6418" rx="1.5"/>
                                        <rect x="15.3584" y="15.3582" width="11.6418" height="11.6418" rx="1.5"/>
                                    </svg>
                                </a>
                                <a href="javascript:void(0)"
                                   onclick="BX.setCookie('orientation','line'); window.location.reload()"
                                   class="ml-2">
                                    <svg width="32" height="32" viewBox="0 0 28 28" fill="none"
                                         class="<?= setActiveColor('line') ?>"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <rect x="1" y="1" width="26" height="6.84211" rx="1.5"/>
                                        <rect x="1" y="10.579" width="26" height="6.84211" rx="1.5"/>
                                        <rect x="1" y="20.1578" width="26" height="6.84211" rx="1.5"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-row items-center">
                        <div id="osh-filter-horizontal-item" class="flex flex-row flex-wrap"
                             data-osh-filter-state="hide"></div>
                        <div id="osh-filter-horizontal-item-count" class="osh-filter-item mx-3 bg-filterGray
                     hidden flex-row items-center px-4 py-2 font-semibold text-center text-sm rounded-md dark:bg-darkBox"
                             onclick="smartFilter.allFilterShowHide()">
                        </div>
                        <div id="osh-filter-horizontal-item-remove" class="osh-filter-item hidden"
                             onclick="smartFilter.removeHorizontalFilterAll()">
                            <svg width="35" height="35" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 23.9531C18.5273 23.9531 23.9532 18.5273 23.9532 12C23.9532 5.46093 18.5156 0.046875 11.9883 0.046875C5.44918 0.046875 0.046875 5.46093 0.046875 12C0.046875 18.5273 5.46093 23.9531 12 23.9531ZM9.10548 18.9609C8.27343 18.9609 7.79293 18.5039 7.75778 17.6719L7.32418 8.10933H6.62108C6.30468 8.10933 6.03513 7.83983 6.03513 7.52343C6.03513 7.19528 6.30468 6.93748 6.62108 6.93748H9.28123V5.98828C9.28123 5.07418 9.87888 4.49998 10.7461 4.49998H13.1953C14.0625 4.49998 14.6601 5.07418 14.6601 5.98828V6.93748H17.3203C17.6367 6.93748 17.8945 7.19528 17.8945 7.52343C17.8945 7.83983 17.6367 8.10933 17.3203 8.10933H16.6406L16.207 17.6719C16.1601 18.5039 15.6797 18.9609 14.8476 18.9609H9.10548ZM10.4648 6.93748H13.4765V6.21093C13.4765 5.89453 13.2539 5.68358 12.9257 5.68358H11.0039C10.6875 5.68358 10.4648 5.89453 10.4648 6.21093V6.93748ZM9.82028 17.6719C10.1133 17.6719 10.289 17.4726 10.2773 17.1914L9.99608 9.33983C9.97263 9.05858 9.79683 8.87108 9.52733 8.87108C9.23438 8.87108 9.04683 9.07028 9.05858 9.33983L9.37498 17.2031C9.38668 17.4843 9.56248 17.6719 9.82028 17.6719ZM11.9765 17.6601C12.2695 17.6601 12.457 17.4726 12.457 17.1914V9.33983C12.457 9.07028 12.2695 8.87108 11.9765 8.87108C11.6836 8.87108 11.4961 9.07028 11.4961 9.33983V17.1914C11.4961 17.4726 11.6953 17.6601 11.9765 17.6601ZM14.1445 17.6719C14.4023 17.6719 14.5781 17.4843 14.5898 17.2031L14.9062 9.33983C14.9179 9.07028 14.7187 8.87108 14.4257 8.87108C14.1679 8.87108 13.9805 9.05858 13.9687 9.33983L13.6875 17.1914C13.6757 17.4726 13.8515 17.6719 14.1445 17.6719Z"
                                      class="fill-lightGrayBg dark:fill-white"/>
                            </svg>
                            <span class='d-inline-block osh-filter-horizontal-remove'></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box_for_tasted"></div>
            <?php
            if (ModuleManager::isModuleInstalled("sale")) {
                $arRecomData = array();
                $recomCacheID = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
                $obCache = new CPHPCache();
                if ($obCache->InitCache(36000, serialize($recomCacheID), "/sale/bestsellers")) {
                    $arRecomData = $obCache->GetVars();
                } elseif ($obCache->StartDataCache()) {
                    if (Loader::includeModule("catalog")) {
                        $arSKU = CCatalogSku::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
                        $arRecomData['OFFER_IBLOCK_ID'] = (!empty($arSKU) ? $arSKU['IBLOCK_ID'] : 0);
                    }
                    $obCache->EndDataCache($arRecomData);
                }
            }

            //region Catalog Section
            $sectionListParams = array(
                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                "CACHE_TIME" => $arParams["CACHE_TIME"],
                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                "COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
                "TOP_DEPTH" => $arParams["SECTION_TOP_DEPTH"],
                "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                "VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"],
                "SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
                "HIDE_SECTION_NAME" => ($arParams["SECTIONS_HIDE_SECTION_NAME"] ?? "N"),
                "ADD_SECTIONS_CHAIN" => ($arParams["ADD_SECTIONS_CHAIN"] ?? '')
            );
            if ($sectionListParams["COUNT_ELEMENTS"] === "Y") {
                $sectionListParams["COUNT_ELEMENTS_FILTER"] = "CNT_ACTIVE";
                if ($arParams["HIDE_NOT_AVAILABLE"] == "Y") {
                    $sectionListParams["COUNT_ELEMENTS_FILTER"] = "CNT_AVAILABLE";
                }
            }

            unset($sectionListParams);

            if ($arParams["USE_COMPARE"] == "Y") {
                $APPLICATION->IncludeComponent(
                    "bitrix:catalog.compare.list",
                    "bootstrap_v4", array(
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "NAME" => $arParams["COMPARE_NAME"],
                    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                    "COMPARE_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["compare"],
                    "ACTION_VARIABLE" => (!empty($arParams["ACTION_VARIABLE"]) ? $arParams["ACTION_VARIABLE"] : "action"),
                    "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                    'POSITION_FIXED' => $arParams['COMPARE_POSITION_FIXED'] ?? '',
                    'POSITION' => $arParams['COMPARE_POSITION'] ?? ''
                ),
                    $component,
                    array("HIDE_ICONS" => "Y")
                );
            }
            //endregion

            if ($_SESSION[$arParams["FILTER_NAME"]][$GLOBAL_SECTION['ID']]['hide_not_available'] == "Y") {
                $arParams["HIDE_NOT_AVAILABLE"] = "Y";
            }
            $curSection = CIBlockSection::GetByID($arCurSection['ID'])->fetch();
            global $ArFilter;
            $sectionParams = array(
                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "FILL_ITEM_ALL_PRICES" => "Y",
                "ACTIVE_BLOCK_YOU_SEE" => "Y",
                "ELEMENT_SORT_FIELD2" => $ELEMENT_SORT_FIELD2,
                "ELEMENT_SORT_ORDER2" => $ELEMENT_SORT_ORDER2,
                "ELEMENT_SORT_FIELD" => (EnteregoHitsHelper::checkIfStartsWithHit($APPLICATION)
                    && !(EnteregoHitsHelper::checkIfHits($APPLICATION))
                    && $curSection['DEPTH_LEVEL'] == '1') ? 'PROPERTY_' . SORT_BREND : $ELEMENT_SORT_FIELD,
                "ELEMENT_SORT_ORDER" => (EnteregoHitsHelper::checkIfStartsWithHit($APPLICATION)
                    && !(EnteregoHitsHelper::checkIfHits($APPLICATION))
                    && $curSection['DEPTH_LEVEL'] == '1') ? 'DESC' : $ELEMENT_SORT_ORDER,
                "PROPERTY_CODE" => (isset($arParams["LIST_PROPERTY_CODE"]) ? $arParams["LIST_PROPERTY_CODE"] : []),
                "PROPERTY_CODE_MOBILE" => $arParams["LIST_PROPERTY_CODE_MOBILE"],
                "META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
                "META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
                "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
                "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                "BASKET_URL" => $arParams["BASKET_URL"],
                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                "FILTER_NAME" => $arParams["FILTER_NAME"],
                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                "CACHE_TIME" => $arParams["CACHE_TIME"],
                "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                "SET_TITLE" => $arParams["SET_TITLE"],
                "MESSAGE_404" => $arParams["~MESSAGE_404"],
                "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                "SHOW_404" => $arParams["SHOW_404"],
                "FILE_404" => $arParams["FILE_404"],
                "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                "PAGE_ELEMENT_COUNT" => $arParams['PAGE_ELEMENT_COUNT'],
                "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                "PRICE_CODE" => $arParams["~PRICE_CODE"],
                "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

                "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
                "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                "PRODUCT_PROPERTIES" => (isset($arParams["PRODUCT_PROPERTIES"]) ? $arParams["PRODUCT_PROPERTIES"] : []),

                "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                "PAGER_TEMPLATE" => "oshisha.round",
                "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
                "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
                "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
                "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                "LAZY_LOAD" => $arParams["LAZY_LOAD"],
                "MESS_BTN_LAZY_LOAD" => $arParams["~MESS_BTN_LAZY_LOAD"],
                "LOAD_ON_SCROLL" => $arParams["LOAD_ON_SCROLL"],

                "OFFERS_CART_PROPERTIES" => (isset($arParams["OFFERS_CART_PROPERTIES"]) ? $arParams["OFFERS_CART_PROPERTIES"] : []),
                "OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
                "OFFERS_PROPERTY_CODE" => (isset($arParams["LIST_OFFERS_PROPERTY_CODE"]) ? $arParams["LIST_OFFERS_PROPERTY_CODE"] : []),
                "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
                "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
                "OFFERS_LIMIT" => (isset($arParams["LIST_OFFERS_LIMIT"]) ? $arParams["LIST_OFFERS_LIMIT"] : 0),

                "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                'HIDE_NOT_AVAILABLE' => $_GET['hide_not_available'] == 'Y' ? 'Y' : $arParams["HIDE_NOT_AVAILABLE"],
                'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],

                'LABEL_PROP' => $arParams['LABEL_PROP'],
                'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
                'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
                'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
                'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
                'PRODUCT_BLOCKS_ORDER' => $arParams['LIST_PRODUCT_BLOCKS_ORDER'],
                'PRODUCT_ROW_VARIANTS' => $arParams['LIST_PRODUCT_ROW_VARIANTS'],
                'ENLARGE_PRODUCT' => $arParams['LIST_ENLARGE_PRODUCT'],
                'ENLARGE_PROP' => isset($arParams['LIST_ENLARGE_PROP']) ? $arParams['LIST_ENLARGE_PROP'] : '',
                'SHOW_SLIDER' => $arParams['LIST_SHOW_SLIDER'],
                'SLIDER_INTERVAL' => isset($arParams['LIST_SLIDER_INTERVAL']) ? $arParams['LIST_SLIDER_INTERVAL'] : '',
                'SLIDER_PROGRESS' => isset($arParams['LIST_SLIDER_PROGRESS']) ? $arParams['LIST_SLIDER_PROGRESS'] : '',

                'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
                'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : []),
                'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
                'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                'MESS_SHOW_MAX_QUANTITY' => (isset($arParams['~MESS_SHOW_MAX_QUANTITY']) ? $arParams['~MESS_SHOW_MAX_QUANTITY'] : ''),
                'RELATIVE_QUANTITY_FACTOR' => (isset($arParams['RELATIVE_QUANTITY_FACTOR']) ? $arParams['RELATIVE_QUANTITY_FACTOR'] : ''),
                'MESS_RELATIVE_QUANTITY_MANY' => (isset($arParams['~MESS_RELATIVE_QUANTITY_MANY']) ? $arParams['~MESS_RELATIVE_QUANTITY_MANY'] : ''),
                'MESS_RELATIVE_QUANTITY_FEW' => (isset($arParams['~MESS_RELATIVE_QUANTITY_FEW']) ? $arParams['~MESS_RELATIVE_QUANTITY_FEW'] : ''),
                'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
                'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
                'MESS_BTN_SUBSCRIBE' => (isset($arParams['~MESS_BTN_SUBSCRIBE']) ? $arParams['~MESS_BTN_SUBSCRIBE'] : ''),
                'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
                'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),
                'MESS_BTN_COMPARE' => (isset($arParams['~MESS_BTN_COMPARE']) ? $arParams['~MESS_BTN_COMPARE'] : ''),

                'USE_ENHANCED_ECOMMERCE' => (isset($arParams['USE_ENHANCED_ECOMMERCE']) ? $arParams['USE_ENHANCED_ECOMMERCE'] : ''),
                'DATA_LAYER_NAME' => (isset($arParams['DATA_LAYER_NAME']) ? $arParams['DATA_LAYER_NAME'] : ''),
                'BRAND_PROPERTY' => (isset($arParams['BRAND_PROPERTY']) ? $arParams['BRAND_PROPERTY'] : ''),

                'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
                "ADD_SECTIONS_CHAIN" => $arParams["ADD_SECTIONS_CHAIN"],
                'ADD_TO_BASKET_ACTION' => $basketAction,
                'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
                'COMPARE_PATH' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['compare'],
                'COMPARE_NAME' => $arParams['COMPARE_NAME'],
                'USE_COMPARE_LIST' => 'Y',
                'BACKGROUND_IMAGE' => (isset($arParams['SECTION_BACKGROUND_IMAGE']) ? $arParams['SECTION_BACKGROUND_IMAGE'] : ''),
                'COMPATIBLE_MODE' => (isset($arParams['COMPATIBLE_MODE']) ? $arParams['COMPATIBLE_MODE'] : ''),
                'DISABLE_INIT_JS_IN_COMPONENT' => (isset($arParams['DISABLE_INIT_JS_IN_COMPONENT']) ? $arParams['DISABLE_INIT_JS_IN_COMPONENT'] : '')
            );
            if (EnteregoHitsHelper::checkIfHits($APPLICATION)) {
                $intSectionID = $APPLICATION->IncludeComponent(
                    "bitrix:enterego.hit_section",
                    ".default",
                    $sectionParams,
                    $component
                );
            } else {
                $intSectionID = $APPLICATION->IncludeComponent(
                    "bitrix:catalog.section",
                    "oshisha_catalog.section",
                    $sectionParams,
                    $component
                );
            }
            $GLOBALS['CATALOG_CURRENT_SECTION_ID'] = $intSectionID;

            if (ModuleManager::isModuleInstalled("sale")) {
                if (!empty($arRecomData)) {
                    if (!isset($arParams['USE_BIG_DATA']) || $arParams['USE_BIG_DATA'] != 'N') {
                        ?>
                        <div class="max-w-full">
                            <div class="col" data-entity="parent-container">
                                <div class="catalog-block-header" data-entity="header" data-showed="false"
                                     style="display: none; opacity: 0;">
                                    <?= GetMessage('CATALOG_PERSONAL_RECOM') ?>
                                </div>
                                <?php
                                $APPLICATION->IncludeComponent("bitrix:catalog.section",
                                    "oshisha_catalog.section", array(
                                        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                        "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                                        "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                                        "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                                        "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
                                        "PROPERTY_CODE" => ($arParams["LIST_PROPERTY_CODE"] ?? []),
                                        "PROPERTY_CODE_MOBILE" => $arParams["LIST_PROPERTY_CODE_MOBILE"],
                                        "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                                        "BASKET_URL" => $arParams["BASKET_URL"],
                                        "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                                        "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                                        "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                                        "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                                        "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                                        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                        "CACHE_TIME" => $arParams["CACHE_TIME"],
                                        "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                                        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                        "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                                        "PAGE_ELEMENT_COUNT" => 0,
                                        "PRICE_CODE" => $arParams["~PRICE_CODE"],
                                        "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                                        "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

                                        "SET_BROWSER_TITLE" => "N",
                                        "SET_META_KEYWORDS" => "N",
                                        "SET_META_DESCRIPTION" => "N",
                                        "SET_LAST_MODIFIED" => "N",
                                        "ADD_SECTIONS_CHAIN" => $arParams["ADD_SECTIONS_CHAIN"],

                                        "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                                        "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
                                        "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                                        "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                                        "PRODUCT_PROPERTIES" => (isset($arParams["PRODUCT_PROPERTIES"]) ? $arParams["PRODUCT_PROPERTIES"] : []),

                                        "OFFERS_CART_PROPERTIES" => (isset($arParams["OFFERS_CART_PROPERTIES"]) ? $arParams["OFFERS_CART_PROPERTIES"] : []),
                                        "OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
                                        "OFFERS_PROPERTY_CODE" => (isset($arParams["LIST_OFFERS_PROPERTY_CODE"]) ? $arParams["LIST_OFFERS_PROPERTY_CODE"] : []),
                                        "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                                        "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                                        "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
                                        "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
                                        "OFFERS_LIMIT" => (isset($arParams["LIST_OFFERS_LIMIT"]) ? $arParams["LIST_OFFERS_LIMIT"] : 0),

                                        "SECTION_ID" => $intSectionID,
                                        "SECTION_CODE" => "",
                                        "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                                        "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                                        "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                                        'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                        'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                        'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                                        'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],

                                        'LABEL_PROP' => $arParams['LABEL_PROP'],
                                        'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
                                        'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
                                        'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
                                        'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
                                        'PRODUCT_BLOCKS_ORDER' => $arParams['LIST_PRODUCT_BLOCKS_ORDER'],
                                        'PRODUCT_ROW_VARIANTS' => "[{'VARIANT':'3','BIG_DATA':true}]",
                                        'ENLARGE_PRODUCT' => $arParams['LIST_ENLARGE_PRODUCT'],
                                        'ENLARGE_PROP' => isset($arParams['LIST_ENLARGE_PROP']) ? $arParams['LIST_ENLARGE_PROP'] : '',
                                        'SHOW_SLIDER' => $arParams['LIST_SHOW_SLIDER'],
                                        'SLIDER_INTERVAL' => isset($arParams['LIST_SLIDER_INTERVAL']) ? $arParams['LIST_SLIDER_INTERVAL'] : '',
                                        'SLIDER_PROGRESS' => isset($arParams['LIST_SLIDER_PROGRESS']) ? $arParams['LIST_SLIDER_PROGRESS'] : '',

                                        "DISPLAY_TOP_PAGER" => 'N',
                                        "DISPLAY_BOTTOM_PAGER" => 'N',
                                        "HIDE_SECTION_DESCRIPTION" => "Y",

                                        "RCM_TYPE" => isset($arParams['BIG_DATA_RCM_TYPE']) ? $arParams['BIG_DATA_RCM_TYPE'] : '',
                                        "SHOW_FROM_SECTION" => 'Y',

                                        'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
                                        'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : []),
                                        'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                                        'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                                        'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
                                        'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                                        'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                                        'MESS_SHOW_MAX_QUANTITY' => (isset($arParams['~MESS_SHOW_MAX_QUANTITY']) ? $arParams['~MESS_SHOW_MAX_QUANTITY'] : ''),
                                        'RELATIVE_QUANTITY_FACTOR' => (isset($arParams['RELATIVE_QUANTITY_FACTOR']) ? $arParams['RELATIVE_QUANTITY_FACTOR'] : ''),
                                        'MESS_RELATIVE_QUANTITY_MANY' => (isset($arParams['~MESS_RELATIVE_QUANTITY_MANY']) ? $arParams['~MESS_RELATIVE_QUANTITY_MANY'] : ''),
                                        'MESS_RELATIVE_QUANTITY_FEW' => (isset($arParams['~MESS_RELATIVE_QUANTITY_FEW']) ? $arParams['~MESS_RELATIVE_QUANTITY_FEW'] : ''),
                                        'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
                                        'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
                                        'MESS_BTN_SUBSCRIBE' => (isset($arParams['~MESS_BTN_SUBSCRIBE']) ? $arParams['~MESS_BTN_SUBSCRIBE'] : ''),
                                        'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
                                        'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),
                                        'MESS_BTN_COMPARE' => (isset($arParams['~MESS_BTN_COMPARE']) ? $arParams['~MESS_BTN_COMPARE'] : ''),

                                        'USE_ENHANCED_ECOMMERCE' => (isset($arParams['USE_ENHANCED_ECOMMERCE']) ? $arParams['USE_ENHANCED_ECOMMERCE'] : ''),
                                        'DATA_LAYER_NAME' => (isset($arParams['DATA_LAYER_NAME']) ? $arParams['DATA_LAYER_NAME'] : ''),
                                        'BRAND_PROPERTY' => (isset($arParams['BRAND_PROPERTY']) ? $arParams['BRAND_PROPERTY'] : ''),

                                        'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
                                        'ADD_TO_BASKET_ACTION' => $basketAction,
                                        'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
                                        'COMPARE_PATH' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['compare'],
                                        'COMPARE_NAME' => $arParams['COMPARE_NAME'],
                                        'USE_COMPARE_LIST' => 'Y',
                                        'BACKGROUND_IMAGE' => '',
                                        'DISABLE_INIT_JS_IN_COMPONENT' => (isset($arParams['DISABLE_INIT_JS_IN_COMPONENT']) ? $arParams['DISABLE_INIT_JS_IN_COMPONENT'] : '')
                                    ),
                                    $component
                                );
                                ?>
                            </div>
                        </div>

                        <?php

                    }
                }
            }
            ?>
        </div>
    </div>
    <?php global $USER;
    if ($USER->IsAuthorized()) {
        global $arrFilterTop;
        $arrFilterTop = array();

        $fUser = CSaleBasket::GetBasketUserID();
        $basketUserId = (int)$fUser;
        if ($basketUserId <= 0) {
            $ids = array();
        }
        $ids = array_values(Catalog\CatalogViewedProductTable::getProductSkuMap(
            IBLOCK_CATALOG,
            $arResult['VARIABLES']['SECTION_ID'],
            $basketUserId,
            $arParams['SECTION_ELEMENT_ID'],
            $arParams['PAGE_ELEMENT_COUNT'],
            $arParams['DEPTH']
        ));

        $arrFilterTop['ID'] = $ids;
        if (!empty($arrFilterTop['ID'])) { ?>
            <div class="mb-5 mt-5 max-w-full">
                <div data-entity="parent-container">
                    <div data-entity="header" data-showed="false">
                        <h1 class="text-2xl"><b>Вы смотрели</b></h1>
                    </div>
                    <div class="by-card viewed-slider max-w-full">
                        <?php $APPLICATION->IncludeComponent(
                            "bitrix:catalog.top",
                            "oshisha_catalog.top",
                            array(
                                "ACTION_VARIABLE" => "action",
                                "PRODUCTS_VIEWED" => "Y",
                                "ADD_PICT_PROP" => "-",
                                "ADD_PROPERTIES_TO_BASKET" => "Y",
                                "ADD_TO_BASKET_ACTION" => "ADD",
                                "BASKET_URL" => "/personal/basket.php",
                                "CACHE_FILTER" => "N",
                                "CACHE_GROUPS" => "Y",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "A",
                                "COMPARE_NAME" => "CATALOG_COMPARE_LIST",
                                "COMPATIBLE_MODE" => "Y",
                                "COMPONENT_TEMPLATE" => "oshisha_catalog.top",
                                "CONVERT_CURRENCY" => "N",
                                "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                                "DETAIL_URL" => "",
                                "DISPLAY_COMPARE" => "N",
                                "ELEMENT_COUNT" => "16",
                                "ELEMENT_SORT_FIELD" => "timestamp_x",
                                "ELEMENT_SORT_FIELD2" => "id",
                                "ELEMENT_SORT_ORDER" => "asc",
                                "ELEMENT_SORT_ORDER2" => "desc",
                                "ENLARGE_PRODUCT" => "PROP",
                                "ENLARGE_PROP" => "-",
                                "FILTER_NAME" => "arrFilterTop",
                                "HIDE_NOT_AVAILABLE" => "Y",
                                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                                "IBLOCK_ID" => IBLOCK_CATALOG,
                                "IBLOCK_TYPE" => "1c_catalog",
                                "LABEL_PROP" => array(),
                                "LABEL_PROP_MOBILE" => "",
                                "LABEL_PROP_POSITION" => "top-left",
                                "LINE_ELEMENT_COUNT" => "4",
                                "MESS_BTN_ADD_TO_BASKET" => "Забронировать",
                                "MESS_BTN_BUY" => "Купить",
                                "MESS_BTN_COMPARE" => "Сравнить",
                                "MESS_BTN_DETAIL" => "Подробнее",
                                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                                "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
                                "OFFERS_FIELD_CODE" => $arParams["OFFERS_FIELD_CODE"],
                                "OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],
                                "OFFERS_LIMIT" => "4",
                                "OFFERS_SORT_FIELD" => "sort",
                                "OFFERS_SORT_FIELD2" => "id",
                                "OFFERS_SORT_ORDER" => "asc",
                                "OFFERS_SORT_ORDER2" => "desc",
                                "OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
                                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                                "PRICE_CODE" => BXConstants::PriceCode(),
                                "FILL_ITEM_ALL_PRICES" => "Y",
                                "PRICE_VAT_INCLUDE" => "Y",
                                "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
                                "PRODUCT_DISPLAY_MODE" => "Y",
                                "PRODUCT_ID_VARIABLE" => "id",
                                "PRODUCT_PROPS_VARIABLE" => "prop",
                                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                                "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
                                "PRODUCT_SUBSCRIPTION" => "Y",
                                "PROPERTY_CODE_MOBILE" => "",
                                "ROTATE_TIMER" => "30",
                                "SECTION_URL" => "",
                                "SEF_MODE" => "N",
                                "SHOW_CLOSE_POPUP" => "N",
                                "SHOW_DISCOUNT_PERCENT" => "N",
                                "SHOW_MAX_QUANTITY" => "N",
                                "SHOW_OLD_PRICE" => "N",
                                "SHOW_PAGINATION" => "Y",
                                "SHOW_PRICE_COUNT" => "1",
                                "SHOW_SLIDER" => "Y",
                                "SLIDER_INTERVAL" => "3000",
                                "SLIDER_PROGRESS" => "N",
                                "TEMPLATE_THEME" => "blue",
                                "USE_ENHANCED_ECOMMERCE" => "N",
                                "USE_PRICE_COUNT" => "N",
                                "USE_PRODUCT_QUANTITY" => "N",
                                "VIEW_MODE" => "SLIDER",
                                "BASKET_ITEMS" => $arBasketItems
                            ),
                            false
                        ); ?>
                    </div>
                </div>
            </div>
        <?php }
    } ?>
</div>
