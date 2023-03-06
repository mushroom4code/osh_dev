<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_ORDER_PAGE'] !== 'Y') {
    LocalRedirect($arParams['SEF_FOLDER']);
}

global $USER;
if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y' && !$USER->IsAuthorized()) {
    LocalRedirect($arResult['PATH_TO_AUTH_PAGE']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$orders_status = $_REQUEST['show_canceled'] ? 'show_canceled' : ($_REQUEST['show_delivery'] ? 'show_delivery' : 'filter_history');


$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDERS"), $arResult['PATH_TO_ORDERS']);
?>
<div class="hides <?= empty($arResult['BASKET_ITEMS']) ? 'js--basket-empty' : 'js--basket-not-empty'?>" id="personal_orders" >
    <h5 class="mb-4"><b>Заказы</b></h5>
    <div class="d-flex flex-row justify-content-between mb-4 align-items-center">
        <div style="display: none">
            <span class="sort_orders retail_orders">Розничные заказы</span>
            <span class="sort_orders wholesale_orders" style="display:none;">Оптовые заказы</span>
        </div>
        <div class="d-flex row_section justify-content-between align-items-center">
            <input type="text" data-range="true" data-multiple-dates-separator=" - "
                   class="datepicker-here form-control date_input mr-3"/>
            <a class="sort_orders" href="javascript:void(0)"><span class="sort_orders_by">Сортировать по</span><i
                        class="fa fa-angle-down"
                        aria-hidden="true"></i>
                <div class="sort_orders_elements" data-sort-status="<?= $orders_status ?>">
                    <ul>
                        <li class="order_sort_item" data-sort-order="new">Новые</li>
                        <li class="order_sort_item" data-sort-order="old">Старые</li>
                        <li class="order_sort_item" data-sort-order="cheap">Дешёвые</li>
                        <li class="order_sort_item" data-sort-order="expensive">Дорогие</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:sale.personal.order.list",
        "oshisha_sale.personal.order.list",
        array(
            "PATH_TO_DETAIL" => $arResult["PATH_TO_ORDER_DETAIL"],
            "PATH_TO_CANCEL" => $arResult["PATH_TO_ORDER_CANCEL"],
            "PATH_TO_CATALOG" => $arParams["PATH_TO_CATALOG"],
            "PATH_TO_COPY" => $arResult["PATH_TO_ORDER_COPY"],
            "PATH_TO_BASKET" => $arParams["PATH_TO_BASKET"],
            "PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
            "SAVE_IN_SESSION" => $arParams["SAVE_IN_SESSION"],
            "ORDERS_PER_PAGE" => $arParams["ORDERS_PER_PAGE"],
            "SET_TITLE" => $arParams["SET_TITLE"],
            "ID" => $arResult["VARIABLES"]["ID"],
            "NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
            "ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
            "HISTORIC_STATUSES" => $arParams["ORDER_HISTORIC_STATUSES"],
            "ALLOW_INNER" => $arParams["ALLOW_INNER"],
            "ONLY_INNER_FULL" => $arParams["ONLY_INNER_FULL"],
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => $arParams["CACHE_TIME"],
            "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
            "DEFAULT_SORT" => $arParams["ORDER_DEFAULT_SORT"],
            "DISALLOW_CANCEL" => $arParams["ORDER_DISALLOW_CANCEL"],
            "RESTRICT_CHANGE_PAYSYSTEM" => $arParams["ORDER_RESTRICT_CHANGE_PAYSYSTEM"],
            "REFRESH_PRICES" => $arParams["ORDER_REFRESH_PRICES"],
            "CONTEXT_SITE_ID" => $arParams["CONTEXT_SITE_ID"],
            "AUTH_FORM_IN_TEMPLATE" => 'Y',
        ),
        $component
    )
    ?></div>

