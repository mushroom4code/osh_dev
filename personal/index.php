<?php

use Bitrix\Conversion\Internals\MobileDetect;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Персональный раздел");
global $USER;
$mobile = new MobileDetect();
if (!$USER->IsAuthorized()) {
    LocalRedirect('/login/?login=yes');
} else { ?>
    <div class="mobile_lk flex md:flex-row flex-col <?php if ($APPLICATION->GetCurPage() != '/personal/'): ?>private<?php endif; ?>">
        <div class="sidebar_lk md:block <?php if ($APPLICATION->GetCurPage() != '/personal/'): ?>hidden<?php endif; ?>">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:menu",
                "",
                array(
                    "ALLOW_MULTI_SELECT" => "N",
                    "CHILD_MENU_TYPE" => "left",
                    "DELAY" => "N",
                    "MAX_LEVEL" => "1",
                    "MENU_CACHE_GET_VARS" => array(""),
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_TYPE" => "N",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "ROOT_MENU_TYPE" => "personal",
                    "USE_EXT" => "N"
                )
            ); ?>
        </div>
        <?php if ($APPLICATION->GetCurUri() != '/personal/'): ?>
            <div class="md:hidden flex flex-row items-center md:mt-0 mt-6 md:p-0 p-4">
                <svg width="30" height="31" viewBox="0 0 34 35" class="mr-3" xmlns="http://www.w3.org/2000/svg">
                    <path class="fill-light-red dark:fill-white"
                          d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"/>
                </svg>
                <a href="/personal/" class="font-medium text-lg dark:text-textDarkLightGray text-lightGrayBg">Меню</a>
            </div>
        <?php endif; ?>
    <?php if (!($APPLICATION->GetCurUri() === '/personal/' && $mobile->isMobile())): ?>
            <div id="content_box" class="w-full md:p-0 p-4">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:sale.personal.section",
                    "oshisha_sale.personal.section",
                    array(
                        "ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS" => array(
                            0 => "0",
                        ),
                        "ACCOUNT_PAYMENT_PERSON_TYPE" => "1",
                        "ACCOUNT_PAYMENT_SELL_CURRENCY" => "RUB",
                        "ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES" => "Y",
                        "ACCOUNT_PAYMENT_SELL_TOTAL" => array(
                            0 => "100",
                            1 => "200",
                            2 => "500",
                            3 => "1000",
                            4 => "5000",
                            5 => "",
                        ),
                        "ACCOUNT_PAYMENT_SELL_USER_INPUT" => "Y",
                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                        "ALLOW_INNER" => "N",
                        "CACHE_GROUPS" => "Y",
                        "CACHE_TIME" => "3600",
                        "CACHE_TYPE" => "A",
                        "CHECK_RIGHTS_PRIVATE" => "N",
                        "COMPATIBLE_LOCATION_MODE_PROFILE" => "N",
                        "COMPONENT_TEMPLATE" => "oshisha_sale.personal.section",
                        "CUSTOM_PAGES" => "",
                        "CUSTOM_SELECT_PROPS" => array(
                            0 => PROPERTY_KEY_VKUS,
                            1 => "",
                        ),
                        "MAIN_CHAIN_NAME" => "Мой кабинет",
                        "NAV_TEMPLATE" => "",
                        "ONLY_INNER_FULL" => "N",
                        "ORDERS_PER_PAGE" => "20",
                        "ORDER_DEFAULT_SORT" => "DATE_INSERT",
                        "ORDER_DISALLOW_CANCEL" => "N",
                        "ORDER_HIDE_USER_INFO" => array(
                            0 => "0",
                        ),
                        "ORDER_HISTORIC_STATUSES" => array(
                            0 => "F",
                        ),
                        "ORDER_REFRESH_PRICES" => "N",
                        "ORDER_RESTRICT_CHANGE_PAYSYSTEM" => array(
                            0 => "0",
                        ),
                        "PATH_TO_BASKET" => "/personal/cart",
                        "PATH_TO_CATALOG" => "/catalog/",
                        "PATH_TO_CONTACT" => "/about/contacts",
                        "PATH_TO_PAYMENT" => "/personal/order/payment/",
                        "PER_PAGE" => "20",
                        "PROFILES_PER_PAGE" => "20",
                        "PROP_1" => array(),
                        "PROP_2" => array(),
                        "SAVE_IN_SESSION" => "Y",
                        "SEF_FOLDER" => "/personal/",
                        "SEF_MODE" => "Y",
                        "SEND_INFO_PRIVATE" => "N",
                        "SET_TITLE" => "Y",
                        "SHOW_ACCOUNT_COMPONENT" => "Y",
                        "SHOW_ACCOUNT_PAGE" => "Y",
                        "SHOW_ACCOUNT_PAY_COMPONENT" => "Y",
                        "SHOW_BASKET_PAGE" => "Y",
                        "SHOW_CONTACT_PAGE" => "Y",
                        "SHOW_ORDER_PAGE" => "Y",
                        "SHOW_PRIVATE_PAGE" => "Y",
                        "SHOW_PROFILE_PAGE" => "Y",
                        "SHOW_SUBSCRIBE_PAGE" => "Y",
                        "USER_PROPERTY_PRIVATE" => "",
                        "USE_AJAX_LOCATIONS_PROFILE" => "N",
                        "SEF_URL_TEMPLATES" => array(
                            "index" => "index.php",
                            "orders" => "orders/",
                            "account" => "account/",
                            "subscriptions" => "subscriptions/",
                            "subscribe" => "subscribe/",
                            "profile" => "profiles/",
                            "profile_detail" => "profiles/#ID#/",
                            "private" => "private/",
                            "order_detail" => "orders/#ID#/",
                            "order_cancel" => "cancel/#ID#/",
                        )
                    ),
                    false
                ); ?>
            </div>
    <?php endif; ?>
    </div>
    <?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
}
