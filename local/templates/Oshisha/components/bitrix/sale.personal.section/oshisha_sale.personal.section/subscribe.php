<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Fuser;
use DataBase_like;

/**
 * @var $APPLICATION CAllMain|CMain
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 */

global $USER;

if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y' && !$USER->IsAuthorized()) {
    LocalRedirect($arResult['PATH_TO_AUTH_PAGE']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_SUBSCRIBE_NEW"));


$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
$item_id = [];


$arIsd_product = [];
$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id,$FUser_id);
//print_r($count_likes );
foreach ($count_likes['USER'] as $product_id => $val) {
    if($val['Fav'][0] === 1 || $val['Fav'][0] === '1') {
        $arIsd_product[] = $product_id;
    }
}

global $FAVORITES_FILTER;
$FAVORITES_FILTER = array(
    'ID' => $arIsd_product
);
//print_r($FAVORITES_FILTER);
?>
<div class="hides static" id="personal_subscribe">
    <h1 class="mb-4">Избранное</h1>
    <div class="ml-lg-4 ml-md-4 ml-0 box_with_prod">
        <?php
		
if( count($arIsd_product) > 0) 
{
		$APPLICATION->IncludeComponent(
            "bitrix:catalog.section",
            "oshisha_catalog.section",
            array(
                "ACTION_VARIABLE" => "action",
                "ADD_PICT_PROP" => "-",
                "ADD_PROPERTIES_TO_BASKET" => "Y",
                "ADD_SECTIONS_CHAIN" => "N",
                "ADD_TO_BASKET_ACTION" => "ADD",
                "AJAX_MODE" => "N",
                "AJAX_OPTION_ADDITIONAL" => "",
                "AJAX_OPTION_HISTORY" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "BACKGROUND_IMAGE" => "-",
                "BASKET_URL" => "/personal/basket.php",
                "BROWSER_TITLE" => "-",
                "CACHE_FILTER" => "N",
                "CACHE_GROUPS" => "Y",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "A",
                "COMPATIBLE_MODE" => "Y",
                "CONVERT_CURRENCY" => "N",
                "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                "DETAIL_URL" => "",
                "DISABLE_INIT_JS_IN_COMPONENT" => "N",
                "DISPLAY_BOTTOM_PAGER" => "Y",
                "DISPLAY_COMPARE" => "N",
                "DISPLAY_TOP_PAGER" => "N",
                "ELEMENT_SORT_FIELD" => "sort",
                "ELEMENT_SORT_FIELD2" => "id",
                "ELEMENT_SORT_ORDER" => "asc",
                "ELEMENT_SORT_ORDER2" => "desc",
                "ENLARGE_PRODUCT" => "STRICT",
                "FILTER_NAME" => "FAVORITES_FILTER",
                "HIDE_NOT_AVAILABLE" => "L",
                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                "IBLOCK_ID" => IBLOCK_CATALOG,
                "IBLOCK_TYPE" => "1c_catalog",
                "INCLUDE_SUBSECTIONS" => "A",
                "LABEL_PROP" => array("VKUS"),
                "LABEL_PROP_MOBILE" => array(),
                "LABEL_PROP_POSITION" => "top-left",
                "LAZY_LOAD" => "N",
                "LINE_ELEMENT_COUNT" => "4",
                "LOAD_ON_SCROLL" => "N",
                "MESSAGE_404" => "",
                "MESS_BTN_ADD_TO_BASKET" => "В корзину",
                "MESS_BTN_BUY" => "Купить",
                "MESS_BTN_DETAIL" => "Подробнее",
                "MESS_BTN_SUBSCRIBE" => "Подписаться",
                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                "META_DESCRIPTION" => "-",
                "META_KEYWORDS" => "-",
                "OFFERS_LIMIT" => "5",
                "PAGER_BASE_LINK_ENABLE" => "N",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_TEMPLATE" => "round",
                "PAGER_TITLE" => "Товары",
                "PAGE_ELEMENT_COUNT" => "12",
                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                "PRICE_CODE" => BXConstants::PriceCode(),
                "PRICE_VAT_INCLUDE" => "Y",
                "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
                "PRODUCT_ID_VARIABLE" => "id",
                "PRODUCT_PROPS_VARIABLE" => "prop",
                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
                "PRODUCT_SUBSCRIPTION" => "Y",
                "PROPERTY_CODE_MOBILE" => array(),
                "RCM_PROD_ID" => $_REQUEST["PRODUCT_ID"],
                "RCM_TYPE" => "personal",
                "SECTION_CODE" => "",
                "SECTION_ID" => $_REQUEST["SECTION_ID"],
                "SECTION_ID_VARIABLE" => "SECTION_ID",
                "SECTION_URL" => "",
                "SECTION_USER_FIELDS" => array("", ""),
                "SEF_MODE" => "N",
                "SET_BROWSER_TITLE" => "Y",
                "SET_LAST_MODIFIED" => "N",
                "SET_META_DESCRIPTION" => "Y",
                "SET_META_KEYWORDS" => "Y",
                "SET_STATUS_404" => "N",
                "SET_TITLE" => "Y",
                "SHOW_404" => "N",
                "SHOW_ALL_WO_SECTION" => "Y",
                "SHOW_CLOSE_POPUP" => "N",
                "SHOW_DISCOUNT_PERCENT" => "N",
                "SHOW_FROM_SECTION" => "N",
                "SHOW_MAX_QUANTITY" => "N",
                "SHOW_OLD_PRICE" => "N",
                "SHOW_PRICE_COUNT" => "1",
                "SHOW_SLIDER" => "Y",
                "SLIDER_INTERVAL" => "3000",
                "SLIDER_PROGRESS" => "N",
                "TEMPLATE_THEME" => "blue",
                "USE_ENHANCED_ECOMMERCE" => "N",
                "USE_MAIN_ELEMENT_SECTION" => "N",
                "USE_PRICE_COUNT" => "N",
                "USE_PRODUCT_QUANTITY" => "N",
                "FILL_ITEM_ALL_PRICES"=>"Y",
            )
        );
}
else
{
	?>
	У Вас нет товаров в избранном
	<?
	
}
		?>
    </div>
</div>
