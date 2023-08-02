<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Sale\Fuser;
use Bitrix\Catalog;
use DataBase_like;


/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var $USER CAllUser|CUser
 * @var string $componentPath
 *
 *  _________________________________________________________________________
 * |    Attention!
 * |    The following comments are for system use
 * |    and are required for the component to work correctly in ajax mode:
 * |    <!-- items-container -->
 * |    <!-- pagination-container -->
 * |    <!-- component-end -->
 * /     <!-- ajax_filter -->
 */

$this->setFrameMode(true);
foreach ($arResult['SECTIONS_ITEMS'] as $sectionKey => $sectionItems) { ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-2">
        <div class="h2"><?= $arResult['SECTIONS'][$sectionKey]['NAME'] ?></div>
        <a href="/hit/<?= $arResult['SECTIONS'][$sectionKey]['CODE'] ?>/"
           class="link color-redLight text-decoration-underline" data-use="show-more-1">
            Смотреть все
        </a>
    </div>

    <div class="mb-5">
        <?php $APPLICATION->IncludeComponent(
            "bitrix:catalog.top",
            "oshisha_catalog.top",
            array(
                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                "ADD_PICT_PROP" => $arParams['ADD_PICT_PROP'],
                "ADD_PROPERTIES_TO_BASKET" => $arParams["ADD_PROPERTIES_TO_BASKET"],
                "ADD_TO_BASKET_ACTION" => "ADD",
                "BASKET_URL" => $arParams["BASKET_URL"],
                "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                "CACHE_TIME" => $arParams["CACHE_TIME"],
                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                'COMPARE_NAME' => $arParams['COMPARE_NAME'],
                "COMPATIBLE_MODE" => $arParams['COMPATIBLE_MODE'],
                "COMPONENT_TEMPLATE" => "oshisha_catalog.top",
                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                "DETAIL_URL" => "",
                "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                "PAGE_ELEMENT_COUNT" => $arParams['PAGE_ELEMENT_COUNT'],
                "ELEMENT_SORT_FIELD" => "PROPERTY_" . SORT_POPULARITY,
                "ELEMENT_SORT_FIELD2" => "id",
                "ELEMENT_SORT_ORDER" => "desc",
                "ELEMENT_SORT_ORDER2" => "desc",
                "ENLARGE_PRODUCT" => "PROP",
                "ENLARGE_PROP" => "-",
                "FILTER_NAME" => "HITS_FILTER_" . $sectionKey,
                "HIDE_NOT_AVAILABLE" => "Y",
                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                'LABEL_PROP' => $arParams['LABEL_PROP'],
                'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
                'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
                "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                "MESS_BTN_ADD_TO_BASKET" => "Забронировать",
                "MESS_BTN_BUY" => "Купить",
                "MESS_BTN_COMPARE" => "Сравнить",
                "MESS_BTN_DETAIL" => "Подробнее",
                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                "OFFERS_FIELD_CODE" => array(
                    0 => "",
                    1 => "",
                ),
                "OFFERS_LIMIT" => "20",
                "OFFERS_SORT_FIELD" => "PROPERTY_" . SORT_POPULARITY,
                "OFFERS_SORT_FIELD2" => "id",
                "OFFERS_SORT_ORDER" => "desc",
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
                "SEF_MODE" => $arParams["SEF_MODE"],
                "SHOW_CLOSE_POPUP" => "N",
                "SHOW_DISCOUNT_PERCENT" => "N",
                "SHOW_MAX_QUANTITY" => "N",
                "SHOW_OLD_PRICE" => "N",
                "SHOW_PAGINATION" => "Y",
                "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                "SHOW_SLIDER" => "Y",
                "SLIDER_INTERVAL" => "3000",
                "SLIDER_PROGRESS" => "N",
                "TEMPLATE_THEME" => "blue",
                "USE_ENHANCED_ECOMMERCE" => "N",
                "USE_PRICE_COUNT" => "N",
                "USE_PRODUCT_QUANTITY" => "N",
                "VIEW_MODE" => "SLIDER",
                "PROPERTY_CODE" => array(
                    0 => "USE_DISCOUNT",
                    1 => "",
                ),
                "PRODUCT_PROPERTIES" => array(
                    "USE_DISCOUNT"
                ),
                'SECTION_ID' => $sectionKey,
                'SECTIONS' => $arResult['SECTIONS']
            ),
            false
        );
        ?>
    </div>
    <?php
}