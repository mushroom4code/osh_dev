<?php

use Bitrix\Conversion\Internals\MobileDetect;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

/** @var  CAllMain|CMain $APPLICATION
 ** @var  CAllUser $USER
 */
$mobile = new MobileDetect();
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeTemplateLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/" . SITE_TEMPLATE_ID . "/header.php");

CJSCore::Init(array("fx"));

$curPage = $APPLICATION->GetCurPage(true);
$MESS["CITY_CHOOSE_TITLE"] = 'Выберите город';
global $option_site;
$option = $option_site;
$MESS["CITY_CHOOSE_PLACEHOLDER"] = 'Ваш город ...';
?><!DOCTYPE html>
<html xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
<head>

    <!--     Yandex.Metrika counter-->
    <script type="text/javascript">
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(65421508, "init", {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            webvisor: true,
            ecommerce: "dataLayer"
        });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/65421508" style="position:absolute; left:-9999px;" alt=""/></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->

    <title><?php $APPLICATION->ShowTitle() ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo SITE_TEMPLATE_PATH; ?>/images/favicon.ico"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <?php
    Asset::getInstance()->addCss("/local/assets/js/arcticmodal/jquery.arcticmodal-0.3.css");
    Asset::getInstance()->addCss("/local/assets/js/arcticmodal/themes/simple.css");
    Asset::getInstance()->addJs("/local/assets/js/arcticmodal/jquery.arcticmodal-0.3.min.js");


    Asset::getInstance()->addCss("https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/script.js");

    //    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/style.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/tailwind.css");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/assets/js/list.js");

    //    Asset::getInstance()->addJs('https://use.fontawesome.com/d071b13f63.js');
    Asset::getInstance()->addJs('https://code.jquery.com/jquery-3.6.0.min.js');
    Asset::getInstance()->addJs("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.js");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css");
    Asset::getInstance()->addCss("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css");
    Asset::getInstance()->addJs("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js");
    Asset::getInstance()->addJS('https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js');
    Asset::getInstance()->addJs("https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/js/datepicker.js");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/css/datepicker.css");
    //    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css");
    Asset::getInstance()->addJs("/local/assets/js/swiper/swiper-bundle.min.js");
    Asset::getInstance()->addCss("/local/assets/js/swiper/swiper-bundle.min.css");
    /** Enterego
     * Mask with code country for user phone in forms
     */
    Asset::getInstance()->addJs("/local/assets/js/flags-mask/jquery.ui.widget.js");
    Asset::getInstance()->addJs("/local/assets/js/flags-mask/phonecode.js");
    Asset::getInstance()->addJs("/local/assets/js/flags-mask/counties.js");
    Asset::getInstance()->addCss("/local/assets/css/flags-mask/phonecode.css");
    $APPLICATION->ShowHead(); ?>
    <script src="//code-ya.jivosite.com/widget/VtGssOZJEq" async></script>
</head>
<body class="bg-white dark:bg-dark dark:text-textDark relative">
<div id="panel">
    <?php $APPLICATION->ShowPanel(); ?>
</div>
<div class="min-h-screen">
    <header class="bg-white dark:bg-dark sticky top-0 z-30 border-b md:border-0 border-white dark:border-grayIconLights">
        <?php if (CHECKED_INFO) {
            $Option = json_decode(COption::GetOptionString('activation_info_admin', 'PERIOD')); ?>
            <div class="alert-info-setting">
                <p class="mb-0 text-center d-lg-block d-md-block d-none">
                    <?= !empty($Option->text_info) ? $Option->text_info : '' ?>
                    <a href="<?= !empty($Option->link_info) ? $Option->link_info : '/' ?>"
                       class="text-decoration-underline font-14 font-weight-bold color-white"> подробнее</a>.</p>
                <p class="mb-0 text-center d-lg-none d-md-none d-block">
                    <?= !empty($Option->text_info_mobile) ? $Option->text_info_mobile : '' ?>
                    <a href="<?= !empty($Option->link_info) ? $Option->link_info : '/' ?>"
                       class="text-decoration-underline font-14 font-weight-bold color-white"> подробнее</a>.</p>
            </div>
        <?php } ?>
        <div class="flex flex-row border-0 md:border-b border-white-100 justify-center width-100 dark:bg-dark
        bg-lightGrayBg py-3 px-4 md:px-0">
            <div class="xs:flex hidden">
                <a href="<?= SITE_DIR ?>">
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => SITE_DIR . "include/company_logo_mobile.php"),
                        false
                    ); ?>
                </a>
            </div>
            <!--            TODO -->
            <div class="right_mobile_top">
                <div class="search_mobile"></div>
                <a class="box_for_menu" data-toggle="collapse" href="#MenuHeader" aria-controls="MenuHeader"
                   aria-expanded="false">
                    <div id="icon" class="Icon open">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
            </div>
            <!--            TODO -->
            <div class="xl:container container flex flex-row justify-between items-center">
                <div class="flex flex-row items-center">
                    <span class="flex flex-row items-center">
                        <div class="dark:bg-dark-red bg-light-red p-1 w-2 h-2 mr-0.5 rounded-full"></div>
                         <div class="dark:bg-dark-red bg-light-red p-1 w-2 h-2 mr-1.5 rounded-full"></div>
                        <a href="#" class="leading-3">
                            <div class="place">
                                <?php $styleNone = '';
                                if (strripos($_SERVER['REQUEST_URI'], '/personal/order/make') !== false) {
                                    $styleNone = 'style="display:none;"';
                                } ?>
                        <button type="button" data-toggle="modal" data-target="#placeModal"  <?= $styleNone ?>>
                            <?php
                            // отключение композитного кеша вне компонента
                            Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("city-title");
                            // динамический контент
                            $code_region = $_SESSION["code_region"];

                            if (empty($_SESSION["code_region"])) {

                                $user_id = $USER->GetID();

                                if (!empty($user_id)) {
                                    if (!CModule::IncludeModule('sale')) {
                                        return;
                                    }
                                    $UserPropsTable = Bitrix\Sale\Internals\UserPropsTable::getList(array('filter' => ['USER_ID' => $user_id,]));

                                    $result = Bitrix\Sale\Internals\UserPropsValueTable::getList(array('filter' => ['USER_PROPS_ID' => $UserPropsTable->fetch()['ID'],
                                        'NAME' => 'Город']));

                                    $code_region = $result->fetch()['VALUE'];
                                }
                            } ?>
                            <span id="city-title" class="dark:text-textDarkLightGray text-white text-13 font-medium"
                                  data-city="<?= $code_region ?>">
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_current.php") ?>
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_select.php") ?>
                            </span>
                            <?php Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("city-title", ""); ?>
                        </button>
                    </div>
                        </a>
                    </span>
                    <a href="https://oshisha.net"
                       class="dark:text-textDarkLightGray text-white text-13 ml-5 mr-2 font-normal dark:hover:text-white
             hover:text-hover-red">
                        Розничный сайт</a>
                </div>
                <div class="flex flex-row">
                    <div class="hidden md:flex flex-row">
                        <a href="/about/o-nas/"
                           class="text-13 text-textDarkLightGray mr-5 dark:font-light font-normal dark:hover:text-white
                            hover:text-hover-red">О нас</a>
                        <?php if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/templates/Oshisha/images/presentation.pdf')) { ?>
                            <a href="/local/templates/Oshisha/images/presentation.pdf"
                               download
                               class="text-13 mr-5 dark:text-textDarkLightGray text-white font-light dark:hover:text-white
                                hover:text-hover-red">Презентация</a>
                        <?php }
                        if ($USER->IsAuthorized()) { ?>
                            <a href="<?= $option->price_list_link; ?>"
                               class="text-13 dark:text-textDarkLightGray text-white dark:font-light font-normal mr-5
                               dark:hover:text-white hover:text-hover-red">Прайс-лист</a>
                        <?php } else { ?>
                            <a href="/login/"
                               class="text-13 dark:text-textDarkLightGray text-white dark:font-light font-normal mr-5
                                dark:hover:text-white hover:text-hover-redd">Прайс-лист</a>
                        <?php } ?>
                        <a href="/about/contacts/"
                           class="text-13 dark:text-textDarkLightGray text-white dark:font-light font-normal mr-5
                           dark:hover:text-white hover:text-hover-red">Контакты</a>
                        <?php if ($USER->IsAuthorized()) { ?>
                            <a href="/about/delivery/"
                               class="text-13 dark:text-textDarkLightGray text-white dark:font-light font-normal mr-5
                                dark:hover:text-white hover:text-hover-red">Доставка
                                и оплата</a>
                        <?php } ?>
                        <a href="javascript:void(0)"
                           class="text-13 dark:text-textDarkLightGray text-white dark:font-light font-normal
                           callback js__callback dark:hover:text-white hover:text-hover-red">Обратный звонок</a>
                    </div>
                    <button type="button"
                            class="bg-gray-200 ml-3 flex w-8 flex-none cursor-pointer rounded-full p-px ring-1 ring-inset
                            ring-gray-900/5 transition-colors duration-200 ease-in-out" onclick="toggleTheme(this)"
                            role="switch" aria-checked="true" aria-labelledby="switch-1-label">
                        <!-- Enabled: "translate-x-3.5", Not Enabled: "translate-x-0" -->
                        <span aria-hidden="true"
                              class="translate-x-0 h-4 w-4 transform rounded-full bg-white shadow-sm ring-1
                              ring-gray-900/5 transition duration-200 ease-in-out js--togglerIcon"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php if ($mobile->isMobile()) { ?>
            <!--            <div class="header_top collapse" id="MenuHeader">-->
            <!--                <div class="mobile top_menu">-->
            <!--                    <div>-->
            <!--                        --><?php //$APPLICATION->IncludeComponent(
            //                            "bitrix:menu",
            //                            "oshisha_menu_mobile",
            //                            array(
            //                                "ROOT_MENU_TYPE" => "left",
            //                                "MENU_CACHE_TYPE" => "A",
            //                                "MENU_CACHE_TIME" => "36000000",
            //                                "MENU_CACHE_USE_GROUPS" => "Y",
            //                                "MENU_THEME" => "site",
            //                                "CACHE_SELECTED_ITEMS" => "N",
            //                                "MENU_CACHE_GET_VARS" => array(),
            //                                "MAX_LEVEL" => "3",
            //                                "CHILD_MENU_TYPE" => "left",
            //                                "USE_EXT" => "Y",
            //                                "DELAY" => "N",
            //                                "ALLOW_MULTI_SELECT" => "N",
            //                                "COMPONENT_TEMPLATE" => "bootstrap_v4"
            //                            ),
            //                            false
            //                        ); ?>
            <!--                        <div class="ul_menu ul_menu_2">-->
            <!--                            <div class="box_top_panel">-->
            <!--                                <a href="/about/o-nas/" class="link_menu_top">-->
            <!--                                    <span class="text_catalog_link not_weight">О нас</span>-->
            <!--                                </a>-->
            <!--                                --><?php //if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/templates/Oshisha/images/presentation.pdf')) { ?>
            <!--                                    <a href="/local/templates/Oshisha/images/presentation.pdf" download-->
            <!--                                       class="text_header "> <span-->
            <!--                                                class="text_catalog_link not_weight"> Презентация</span></a>-->
            <!--                                --><?php //} ?>
            <!--                                <a href="/about/contacts/" class="link_menu_top">-->
            <!--                                    <span class="text_catalog_link not_weight">Контакты</span>-->
            <!--                                </a>-->
            <!--                                --><?php //if ($USER->IsAuthorized()) { ?>
            <!--                                    <a href="/about/delivery/" class="link_menu_top ">-->
            <!--                                        <span class="text_catalog_link not_weight">Доставка и оплата</span>-->
            <!--                                    </a>-->
            <!--                                --><?php //} ?>
            <!---->
            <!--                                <a href="/about/FAQ/" class="link_menu_top ">-->
            <!--                                    <span class="text_catalog_link not_weight">FAQ</span>-->
            <!--                                </a>-->
            <!--                            </div>-->
            <!--                            <span class="bx-header-phone-number">-->
            <!--									--><?php //$APPLICATION->IncludeComponent(
            //                                        "bitrix:main.include",
            //                                        "",
            //                                        array(
            //                                            "AREA_FILE_SHOW" => "file",
            //                                            "PATH" => SITE_DIR . "include/telephone.php"
            //                                        ),
            //                                        false
            //                                    ); ?>
            <!--								</span>-->
            <!--                            <div class="box_with_contact">-->
            <!--                                <span><i class="fa fa-circle header_icon" aria-hidden="true"></i></span>-->
            <!--                                <span> <i class="fa fa-circle header_icon" aria-hidden="true"></i></span>-->
            <!---->
            <!--                                <a href="#" class=" ">-->
            <!--                                    <div class="place">-->
            <!--                                        <button type="button" class="place__button" data-toggle="modal"-->
            <!--                                                data-target="#placeModal">-->
            <!--                                    <span class="text_catalog_link not_weight">-->
            <!--                                        --><?php //include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_current.php") ?>
            <!--                                    </span>-->
            <!--                                        </button>-->
            <!--                                    </div>-->
            <!--                                </a>-->
            <!--                            </div>-->
            <!--                            <a href="/about/feedback_new_site/" class="link_menu_top">-->
            <!--                                <span class="red_text text_font_13 font-weight-bold ">Написать отзыв</span>-->
            <!--                            </a>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->
        <?php }
        if (strripos($APPLICATION->GetCurPage(), '/personal/') === false ||
            strripos($APPLICATION->GetCurPage(), '/personal/') !== false && !$mobile->isMobile()): ?>
            <div class="flex xs:hidden flex-col justify-center width-100 md:py-5 py-3 items-center flex-wrap">
                <!--        header menu search/login/basket/like     -->
                <div class="xl:container container md:px-0 px-4 flex flex-row justify-between items-center md:mb-4 mb-2">
                    <div class="flex flex-row">
                        <div class="md:w-40 w-auto md:mr-7 mr-5">
                            <a href="<?= SITE_DIR ?>">
                                <svg height="35" viewBox="0 0 255 55" class="w-28 md:w-40" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.3259 46.6073C11.79 46.6073 8.10247 44.4464 8.10247 37.6771C8.10247 30.845 11.8 28.6802 19.3259 28.6802C26.8518 28.6802 30.5058 30.845 30.5058 37.6771C30.5058 44.4464 26.8183 46.6073 19.3259 46.6073ZM19.3259 54.5395C30.8377 54.5395 38.6518 49.6599 38.6518 37.6771C38.6518 25.6315 30.8377 20.7559 19.3259 20.7559C7.76389 20.7559 0 25.6315 0 37.6771C0 49.6599 7.76389 54.5395 19.3259 54.5395Z"
                                          fill="#FF0500"/>
                                    <path d="M61.7457 54.5394C50.9983 54.5394 41.042 52.9246 41.042 42.4937V39.2446C41.6756 40.1482 44.5518 42.1244 49.1411 42.4937C49.1411 45.9903 52.2152 47.3261 61.2697 47.3261C70.0359 47.3261 71.4539 46.6661 71.4539 44.1084C71.4539 41.6136 70.113 41.4486 60.3176 40.7297C48.0516 39.8928 42.0343 38.7888 42.0343 30.9077C42.0343 22.3587 50.5591 20.7596 60.5054 20.7596C71.0181 20.7596 78.6513 22.3704 78.6513 31.3674V34.9661C78.2959 33.7875 76.1639 32.2946 70.5019 31.4695V31.332C70.5019 28.6133 67.5653 27.9493 60.0293 27.9493C52.356 27.9493 50.1736 28.6133 50.1736 30.507C50.1736 32.5617 51.6419 32.8878 61.2596 33.7207C72.1579 34.6636 79.5933 35.0015 79.5933 43.7116C79.6033 52.9364 72.5032 54.5394 61.7457 54.5394Z"
                                          class="dark:fill-white fill-black"/>
                                    <path d="M154.725 54.5394C143.977 54.5394 134.021 52.9246 134.021 42.4937V39.2446C134.658 40.1482 137.531 42.1244 142.123 42.4937C142.123 45.9903 145.198 47.3261 154.252 47.3261C163.015 47.3261 164.433 46.6661 164.433 44.1084C164.433 41.6136 163.092 41.4486 153.3 40.7297C141.034 39.8928 135.013 38.7888 135.013 30.9077C135.013 22.3587 143.541 20.7596 153.488 20.7596C163.997 20.7596 171.634 22.3704 171.634 31.3674V34.9661C171.275 33.7875 169.143 32.2946 163.481 31.4695V31.332C163.481 28.6133 160.548 27.9493 153.012 27.9493C145.335 27.9493 143.156 28.6133 143.156 30.507C143.156 32.5617 144.621 32.8878 154.242 33.7207C165.137 34.6636 172.576 35.0015 172.576 43.7116C172.586 52.9364 165.486 54.5394 154.725 54.5394Z"
                                          class="dark:fill-white fill-black"/>
                                    <path d="M106.448 21.2154C99.6734 21.2154 94.1824 23.8281 90.2032 27.5015V10.0695C88.2928 10.2367 86.5022 11.2243 85.1748 12.8432C82.1912 16.3398 82.1208 19.5221 82.1208 19.5221V54.4569H90.22V36.1448C92.7308 32.7071 98.6007 29.4344 104.055 29.4344C110.736 29.4344 112.721 33.0332 112.721 39.9793V54.4608H120.87L120.971 36.3884C120.843 24.331 113.884 21.2154 106.448 21.2154Z"
                                          class="dark:fill-white fill-black"/>
                                    <path d="M199.427 21.2154C192.656 21.2154 187.161 23.8281 183.185 27.5015V10.0695C181.268 10.2313 179.47 11.2194 178.137 12.8432C175.153 16.3398 175.083 19.5221 175.083 19.5221V54.4569H183.185V36.1448C185.693 32.7071 191.566 29.4344 197.017 29.4344C203.701 29.4344 205.683 33.0332 205.683 39.9793V54.4608H213.835L213.933 36.3884C213.825 24.331 206.863 21.2154 199.427 21.2154Z"
                                          class="dark:fill-white fill-black"/>
                                    <path d="M245.96 44.297C241.797 46.4106 235.729 48.0097 231.328 48.0097C227.11 48.0097 224.415 47.7346 224.415 44.297C224.415 40.9614 228.063 39.7435 234.137 39.7435C239.303 39.7435 243.141 40.6825 245.974 41.7393L245.96 44.297ZM235.729 21.2153C229.9 21.2153 224.315 22.4333 218.633 24.5941L221.519 31.5284C226.001 29.9237 230.664 29.1211 235.35 29.1476C239.138 29.1476 245.96 29.6465 245.96 35.8579C241.815 34.2791 237.48 33.4912 233.121 33.5242C224.268 33.5242 216.303 36.184 216.303 44.6741C216.303 52.0485 221.184 54.999 228.994 54.999C234.877 55.0437 240.686 53.4678 245.954 50.3984V54.4529H254.103V37.952C254.013 25.5959 247.378 21.2153 235.729 21.2153Z"
                                          class="dark:fill-white fill-black"/>
                                    <path d="M123.502 21.7772V54.4569H131.604V21.7772H123.502ZM123.502 10.0695V18.3396H131.604V10.0891L123.502 10.0695Z"
                                          class="dark:fill-white fill-black"/>
                                    <path d="M17.1637 15.0512C6.68448 13.1575 5.24634 4.13308 5.3268 0C6.58391 1.8701 12.2258 5.95604 23.7208 8.56082C35.2158 11.1656 36.9858 19.5811 36.5701 23.3095C32.7921 18.8857 27.6463 16.9331 17.1637 15.0512Z"
                                          fill="#FF0500"/>
                                    <path d="M16.2619 17.6796C5.66199 17.2474 3.32209 8.49013 2.97681 4.38062C4.41158 6.06214 10.4323 9.32696 22.1251 10.3013C33.8178 11.2756 36.6136 19.5457 36.5835 23.3095C32.3663 19.4436 26.8618 18.0724 16.2619 17.6796Z"
                                          fill="#FF0500"/>
                                </svg>
                            </a>
                        </div>
                        <div class="dark:text-textDarkLightGray text-sm font-extralight md:block hidden">
                            Вся продукция <br>для кальяна
                        </div>
                    </div>
                    <div class="flex-row items-center justify-between flex">
                        <div class="w-3/4 mr-5 md:block hidden">
                            <?php $APPLICATION->IncludeComponent(
                                "bitrix:search.title",
                                "oshisha_search.title",
                                array(
                                    "CATEGORY_0" => array("iblock_1c_catalog"),
                                    "CATEGORY_0_TITLE" => "",
                                    "CATEGORY_0_iblock_1c_catalog" => array("all"),
                                    "CATEGORY_1" => array(),
                                    "CATEGORY_1_TITLE" => "",
                                    "CHECK_DATES" => "N",
                                    "CONTAINER_ID" => "title-search_desktop",
                                    "CONVERT_CURRENCY" => "N",
                                    "INPUT_ID" => "input_search_desktop",
                                    "NUM_CATEGORIES" => "2",
                                    "ORDER" => "date",
                                    "PAGE" => "#SITE_DIR#catalog/",
                                    "PREVIEW_HEIGHT" => "150",
                                    "PREVIEW_TRUNCATE_LEN" => "",
                                    "PREVIEW_WIDTH" => "150",
                                    "PRICE_CODE" => BXConstants::PriceCode(),
                                    "PRICE_VAT_INCLUDE" => "Y",
                                    "SHOW_INPUT" => "Y",
                                    "SHOW_OTHERS" => "N",
                                    "SHOW_PREVIEW" => "Y",
                                    "TEMPLATE_THEME" => "blue",
                                    "TOP_COUNT" => "5",
                                    "USE_LANGUAGE_GUESS" => "N"
                                ),
                                true
                            ); ?>
                        </div>
                        <div class="z-50 flex flex-row items-center justify-between">
                            <?php $APPLICATION->IncludeComponent(
                                "bitrix:sale.basket.basket.line",
                                "oshisha_sale.basket.basket.line",
                                array(
                                    "PATH_TO_BASKET" => SITE_DIR . "personal/cart/",
                                    "PATH_TO_PERSONAL" => SITE_DIR . "personal/",
                                    "SHOW_PERSONAL_LINK" => "N",
                                    "SHOW_NUM_PRODUCTS" => "Y",
                                    "SHOW_TOTAL_PRICE" => "Y",
                                    "SHOW_PRODUCTS" => "N",
                                    "POSITION_FIXED" => "N",
                                    "SHOW_AUTHOR" => "Y",
                                    "PATH_TO_REGISTER" => SITE_DIR . "login/",
                                    "PATH_TO_PROFILE" => SITE_DIR . "personal/"
                                ),
                                false,
                                array()
                            ); ?>
                        </div>
                    </div>
                </div>
                <?php if (!$mobile->isMobile()) { ?>
                    <div class="box_with_menu xl:container container">
                        <div class="menu_header">
                            <?php $APPLICATION->IncludeComponent(
                                "bitrix:menu",
                                "oshisha_menu",
                                array(
                                    "ROOT_MENU_TYPE" => "left",
                                    "MENU_CACHE_TYPE" => "A",
                                    "MENU_CACHE_TIME" => "36000000",
                                    "MENU_CACHE_USE_GROUPS" => "Y",
                                    "MENU_THEME" => "site",
                                    "CACHE_SELECTED_ITEMS" => "N",
                                    "MENU_CACHE_GET_VARS" => array(),
                                    "MAX_LEVEL" => "4",
                                    "CHILD_MENU_TYPE" => "left",
                                    "USE_EXT" => "Y",
                                    "DELAY" => "N",
                                    "IBLOCK_ID" => IBLOCK_CATALOG,
                                    "TYPE" => "1c_catalog",
                                    "ALLOW_MULTI_SELECT" => "N",
                                    "COMPONENT_TEMPLATE" => "bootstrap_v4",
                                    "SECTION_PAGE_URL" => "#SECTION_ID#/",
                                    "DETAIL_PAGE_URL" => "#SECTION_ID#/#ELEMENT_ID#",
                                ),
                                false
                            ); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php endif; ?>
    </header>

    <div class="section_wrapper min-h-550 flex flex-col items-center">
        <div class="container md:mb-8 mb-0 px-4 md:px-0">
            <?php $needSidebar = preg_match("~^" . SITE_DIR . "(catalog|personal\/cart|personal\/order\/make)/~", $curPage); ?>
            <div class="bx-content <?= STATIC_P ?>">