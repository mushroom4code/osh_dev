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
<div class="hides <?= empty($arResult['BASKET_ITEMS']) ? 'js--basket-empty' : 'js--basket-not-empty' ?>"
     id="personal_orders">
    <h5 class="mb-3 text-2xl font-medium text-textLight dark:text-textDarkLightGray">Заказы</h5>
    <div class="flex flex-row justify-between mb-4 items-center">
        <div class="flex justify-between items-center">
            <input type="text" data-range="true" data-multiple-dates-separator=" - "
                   class="datepicker-here dark:bg-grayButton bg-white dark:border-none border-borderColor text-sm
                         focus:border-borderColor shadow-none py-2 px-4 outline-none rounded-md w-auto date_input mr-3"/>
            <a class="sort_orders dark:bg-grayButton bg-textDark text-sm py-2 md:px-4 px-2 rounded-md w-full relative"
               href="javascript:void(0)">
                <div class="flex flex-row items-center">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="mr-1"
                         xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M18.9583 5.83331C18.9583 6.17849 18.6785 6.45831 18.3333 6.45831H1.66663C1.32145 6.45831 1.04163 6.17849 1.04163 5.83331C1.04163 5.48814 1.32145 5.20831 1.66663 5.20831H18.3333C18.6785 5.20831 18.9583 5.48814 18.9583 5.83331Z"
                              fill="#CD1D1D"/>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M16.4583 10C16.4583 10.3452 16.1785 10.625 15.8333 10.625H4.16663C3.82145 10.625 3.54163 10.3452 3.54163 10C3.54163 9.65483 3.82145 9.375 4.16663 9.375H15.8333C16.1785 9.375 16.4583 9.65483 16.4583 10Z"
                              fill="#CD1D1D"/>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M13.9583 14.1667C13.9583 14.5119 13.6785 14.7917 13.3333 14.7917H6.66663C6.32145 14.7917 6.04163 14.5119 6.04163 14.1667C6.04163 13.8215 6.32145 13.5417 6.66663 13.5417H13.3333C13.6785 13.5417 13.9583 13.8215 13.9583 14.1667Z"
                              fill="#CD1D1D"/>
                    </svg>
                    <span class="sort_orders_by">
                        <span class="md:text-sm text-xs text-textLight font-normal dark:text-textDarkLightGray">
                            Сортировать по
                        </span>
                    </span>
                </div>
                <div class="sort_orders_elements hidden absolute top-9 w-full rounded-b-lg z-30 p-3 pt-0 dark:bg-grayButton
                 bg-textDark right-0"
                     data-sort-status="<?= $orders_status ?>">
                    <ul>
                        <li class="order_sort_item text-xs mb-2" data-sort-order="new">Новые</li>
                        <li class="order_sort_item text-xs mb-2" data-sort-order="old">Старые</li>
                        <li class="order_sort_item text-xs mb-2" data-sort-order="cheap">Дешёвые</li>
                        <li class="order_sort_item text-xs mb-2" data-sort-order="expensive">Дорогие</li>
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

