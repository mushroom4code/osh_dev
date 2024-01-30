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
} else {
    global $USER;
    $name = '';

    if ($USER->IsAuthorized()) {
        $name = trim($USER->GetFirstName());
    }

    if (!$name) {
        $name = trim($USER->GetLogin());
    } ?>
    <div class="mobile_lk flex lg:flex-row flex-col mt-4 <?php if ($APPLICATION->GetCurPage() != '/personal/'): ?>private<?php endif; ?>">
        <?php
        if ($mobile->isMobile()) { ?>
            <div class="dark:bg-dark bg-lightGrayBg rounded-b-3xl border-b border-white-100">
                <div class="box_with_photo flex flex-row px-5 pb-6 pt-4 items-end rounded-b-3xl" style="
                background-image: url('/local/assets/images/profile_mobile.png');
                background-repeat: no-repeat;
                 background-position: right bottom;
                 background-size: 40%">
                    <svg width="85" height="77" viewBox="0 0 92 84" class="rounded-lg"
                         xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_1890_10033)">
                            <path d="M0 0H91.07V73.0449C91.07 78.5678 86.5928 83.0449 81.07 83.0449H9.99999C4.47715 83.0449 0 78.5678 0 73.0449V0Z"
                                  class="fill-lightGrayBg"/>
                            <rect x="0.5" y="0.5" width="91" height="84" rx="9.5"
                                  class="stroke-white  fill-[#393939]"/>
                            <path d="M15.1279 83.564L15.1279 74.9453C15.1279 69.4391 17.5266 64.1584 21.7964 60.2649C26.0661 56.3714 31.8571 54.1841 37.8954 54.1841H53.0737C59.1121 54.1841 64.9031 56.3714 69.1728 60.2649C73.4425 64.1584 75.8412 69.4391 75.8412 74.9453V83.564"
                                  class="fill-white"/>
                            <path d="M15.1279 83.564L15.1279 74.9453C15.1279 69.4391 17.5266 64.1584 21.7964 60.2649C26.0661 56.3714 31.8571 54.1841 37.8954 54.1841H53.0737C59.1121 54.1841 64.9031 56.3714 69.1728 60.2649C73.4425 64.1584 75.8412 69.4391 75.8412 74.9453V83.564"
                                  class="stroke-white fill-white" stroke-linecap="round"/>
                            <path d="M45.5367 42.0536C38.3279 42.0536 34.8005 40.4085 34.8005 35.2548C34.8005 30.0533 38.3376 28.4052 45.5367 28.4052C52.7359 28.4052 56.2312 30.0533 56.2312 35.2548C56.2312 40.4085 52.7038 42.0536 45.5367 42.0536ZM45.5367 48.0927C56.5487 48.0927 64.0236 44.3777 64.0236 35.2548C64.0236 26.084 56.5487 22.3721 45.5367 22.3721C34.4766 22.3721 27.0498 26.084 27.0498 35.2548C27.0498 44.3777 34.4766 48.0927 45.5367 48.0927Z"
                                  class="fill-white"/>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M61.9829 24.2725C57.9566 21.3628 52.6932 20.3278 42.6041 20.0302C32.4644 19.7012 30.226 13.034 29.8958 9.90526C30.4031 10.3785 31.5099 11.0164 33.2686 11.6701C32.3233 9.76471 32.1121 7.85645 32.1435 6.57031C33.3461 7.99409 38.743 11.1049 49.739 13.088C60.6079 15.0482 62.3877 21.3307 62.0434 24.2169C62.0437 24.2505 62.0438 24.2837 62.0434 24.3165C62.0395 24.3136 62.0356 24.3107 62.0316 24.3079C62.0312 24.3108 62.0308 24.3137 62.0304 24.3166C62.0146 24.3019 61.9988 24.2872 61.9829 24.2725Z"
                                  class="fill-white"/>
                        </g>
                    </svg>
                    <div class="ml-3 flex flex-col">
                        <a href="/personal/"
                           class="text-textDarkLightGray text-md font-semibold mb-2"
                           id="profile_people"><span><?= htmlspecialcharsbx($name) ?></span>
                        </a>
                        <div class="flex flex-row">
                            <a href="/personal/private/"
                               class="text-white text-xs font-light p-2 rounded-full bg-white mr-3">
                                <svg width="17" height="17" viewBox="0 0 21 21" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.6818 0.449219L13.0682 2.93797L18.2955 7.91547L20.9091 5.42672L15.6818 0.449219ZM10.4545 5.42672L0 15.3817V20.3592H5.22727L15.6818 10.4042L10.4545 5.42672Z"
                                          fill="black"/>
                                </svg>
                            </a>
                            <a href="/personal/cart/"
                               class="text-white text-xs font-light p-2 rounded-full bg-white mr-3">
                                <svg width="17" height="18" viewBox="0 0 22 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.6755 8.0095H17.0512C18.2852 8.0095 19.3131 8.95534 19.4156 10.185L20.2065 19.6752C20.3218 21.0584 19.2302 22.2448 17.8421 22.2448H4.01871C2.63067 22.2448 1.53909 21.0584 1.65436 19.6752L2.44521 10.185C2.54768 8.95534 3.57562 8.0095 4.80956 8.0095H6.18532M15.6755 8.0095H6.18532M15.6755 8.0095V6.82322C15.6755 5.56474 15.1756 4.35781 14.2857 3.46793C13.3959 2.57806 12.1889 2.07812 10.9304 2.07812C9.6719 2.07812 8.46501 2.57806 7.57512 3.46793C6.68525 4.35781 6.18532 5.56474 6.18532 6.82322V8.0095M15.6755 8.0095V12.7546M6.18532 8.0095V12.7546"
                                          stroke="black" stroke-width="2.7" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <a href="/?logout=yes&<?= bitrix_sessid_get() ?>"
                               class="text-white text-xs font-light p-2 rounded-full bg-white mr-3">
                                <svg width="17" height="17" viewBox="0 0 22 23" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.0933 11.5345H20.6523M20.6523 11.5345L17.4127 14.8962M20.6523 11.5345L17.4127 8.17285"
                                          stroke="black" stroke-width="2.7" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                    <path d="M17.1182 4.81089V3.69033C17.1182 2.4526 16.1512 1.44922 14.9584 1.44922H4.15974C2.96695 1.44922 2 2.4526 2 3.69033V19.3781C2 20.6159 2.96695 21.6192 4.15974 21.6192H14.9584C16.1512 21.6192 17.1182 20.6159 17.1182 19.3781V18.2576"
                                          stroke="black" stroke-width="2.5" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
        if ($APPLICATION->GetCurUri() === '/personal/' && $mobile->isMobile() ||
            strripos($APPLICATION->GetCurUri(), '/personal/') !== false && !$mobile->isMobile()): ?>
            <div class="sidebar_lk">
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
        <?php endif; ?>
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