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

    Asset::getInstance()->addJs('https://use.fontawesome.com/d071b13f63.js');
    Asset::getInstance()->addJs('https://code.jquery.com/jquery-3.6.0.min.js');
    Asset::getInstance()->addJs("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.js");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css");
    Asset::getInstance()->addCss("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css");
    Asset::getInstance()->addJs("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js");
    Asset::getInstance()->addJS('https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js');
    Asset::getInstance()->addJs("https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/js/datepicker.js");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/css/datepicker.css");
    Asset::getInstance()->addCss("https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css");
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
<body class="bg-white dark:bg-dark dark:text-textDark min-h-screen">
<div id="panel">
    <?php $APPLICATION->ShowPanel(); ?>
</div>
<div>
    <header class="bg-white dark:bg-dark">
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
        <div class="flex flex-row border-b border-white-100 justify-center width-100 py-3">
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
                        <div class="bg-dark-red p-1 w-2 h-2 mr-0.5 rounded-full"></div>
                         <div class="bg-dark-red p-1 w-2 h-2 mr-1.5 rounded-full"></div>
                        <a href="#" class="leading-3">
                            <div class="place">
                                <?php $styleNone = '';
                                if (strripos($_SERVER['REQUEST_URI'], '/personal/order/make') !== false) {
                                    $styleNone = 'style="display:none;"';
                                } ?>
                        <button type="button" data-toggle="modal"
                                data-target="#placeModal"  <?= $styleNone ?>>
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
                                    $UserPropsTable = Bitrix\Sale\Internals\UserPropsTable::getList(array('filter' => [
                                        'USER_ID' => $user_id,
                                    ]));

                                    $result = Bitrix\Sale\Internals\UserPropsValueTable::getList(array('filter' => [
                                        'USER_PROPS_ID' => $UserPropsTable->fetch()['ID'],
                                        'NAME' => 'Город'
                                    ]));

                                    $code_region = $result->fetch()['VALUE'];
                                }
                            } ?>
                            <span id="city-title" class="dark:text-white text-textLight text-xs font-medium" data-city="<?= $code_region ?>">
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_current.php") ?>
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_select.php") ?>
                            </span>
                            <?php Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("city-title", ""); ?>
                        </button>
                    </div></a>
                    </span>
                    <a href="https://oshisha.net"
                       class="dark:text-white text-textLight text-xs ml-2 mr-2 font-medium">
                        Розничный сайт</a>
                </div>
                <div>
                    <a href="/about/o-nas/" class="text-xs dark:text-textDark mr-3 font-light hover:text-hover-red">О нас</a>
                    <?php if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/templates/Oshisha/images/presentation.pdf')) { ?>
                        <a href="/local/templates/Oshisha/images/presentation.pdf"
                           download class="text-xs mr-3 dark:text-textDark font-light hover:text-hover-red">Презентация</a>
                    <?php }
                    if ($USER->IsAuthorized()) { ?>
                        <a href="<?= $option->price_list_link; ?>"
                           class="text-xs dark:text-textDark text-textLight font-light mr-3 hover:text-hover-red">Прайс-лист</a>
                    <?php } else { ?>
                        <a href="/login/"
                           class="text-xs dark:text-textDark mr-3 font-light hover:text-hover-red">Прайс-лист</a>
                    <?php } ?>
                    <a href="/about/contacts/" class="text-xs dark:text-textDark font-light mr-3 hover:text-hover-red">Контакты</a>
                    <?php if ($USER->IsAuthorized()) { ?>
                        <a href="/about/delivery/" class="text-xs dark:text-textDark font-light mr-3 hover:text-hover-red">Доставка
                            и оплата</a>
                    <?php } ?>
                    <a href="javascript:void(0)"
                       class="text-xs dark:text-textDark callback js__callback font-light hover:text-hover-red">Обратный звонок</a>
                    <button type="button"
                            class="bg-gray-200 flex w-8 flex-none cursor-pointer rounded-full p-px ring-1 ring-inset
                            ring-gray-900/5 transition-colors duration-200 ease-in-out" onclick="toggleTheme(this)"
                            role="switch" aria-checked="true" aria-labelledby="switch-1-label">
                        <span class="sr-only">Agree to policies</span>
                        <!-- Enabled: "translate-x-3.5", Not Enabled: "translate-x-0" -->
                        <span aria-hidden="true"
                              class="translate-x-0 h-4 w-4 transform rounded-full bg-white shadow-sm ring-1
                              ring-gray-900/5 transition duration-200 ease-in-out js--togglerIcon"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php if ($mobile->isMobile()) { ?>
            <div class="header_top collapse" id="MenuHeader">
                <div class="mobile top_menu">
                    <div>
                        <?php $APPLICATION->IncludeComponent(
                            "bitrix:menu",
                            "oshisha_menu_mobile",
                            array(
                                "ROOT_MENU_TYPE" => "left",
                                "MENU_CACHE_TYPE" => "A",
                                "MENU_CACHE_TIME" => "36000000",
                                "MENU_CACHE_USE_GROUPS" => "Y",
                                "MENU_THEME" => "site",
                                "CACHE_SELECTED_ITEMS" => "N",
                                "MENU_CACHE_GET_VARS" => array(),
                                "MAX_LEVEL" => "3",
                                "CHILD_MENU_TYPE" => "left",
                                "USE_EXT" => "Y",
                                "DELAY" => "N",
                                "ALLOW_MULTI_SELECT" => "N",
                                "COMPONENT_TEMPLATE" => "bootstrap_v4"
                            ),
                            false
                        ); ?>
                        <div class="ul_menu ul_menu_2">
                            <div class="box_top_panel">
                                <a href="/about/o-nas/" class="link_menu_top">
                                    <span class="text_catalog_link not_weight">О нас</span>
                                </a>
                                <?php if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/templates/Oshisha/images/presentation.pdf')) { ?>
                                    <a href="/local/templates/Oshisha/images/presentation.pdf" download
                                       class="text_header "> <span
                                                class="text_catalog_link not_weight"> Презентация</span></a>
                                <?php } ?>
                                <a href="/about/contacts/" class="link_menu_top">
                                    <span class="text_catalog_link not_weight">Контакты</span>
                                </a>
                                <?php if ($USER->IsAuthorized()) { ?>
                                    <a href="/about/delivery/" class="link_menu_top ">
                                        <span class="text_catalog_link not_weight">Доставка и оплата</span>
                                    </a>
                                <?php } ?>

                                <a href="/about/FAQ/" class="link_menu_top ">
                                    <span class="text_catalog_link not_weight">FAQ</span>
                                </a>
                            </div>
                            <span class="bx-header-phone-number">
									<?php $APPLICATION->IncludeComponent(
                                        "bitrix:main.include",
                                        "",
                                        array(
                                            "AREA_FILE_SHOW" => "file",
                                            "PATH" => SITE_DIR . "include/telephone.php"
                                        ),
                                        false
                                    ); ?>
								</span>
                            <div class="box_with_contact">
                                <span><i class="fa fa-circle header_icon" aria-hidden="true"></i></span>
                                <span> <i class="fa fa-circle header_icon" aria-hidden="true"></i></span>

                                <a href="#" class=" ">
                                    <div class="place">
                                        <button type="button" class="place__button" data-toggle="modal"
                                                data-target="#placeModal">
                                    <span class="text_catalog_link not_weight">
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_current.php") ?>
                                    </span>
                                        </button>
                                    </div>
                                </a>
                            </div>
                            <a href="/about/feedback_new_site/" class="link_menu_top">
                                <span class="red_text text_font_13 font-weight-bold ">Написать отзыв</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="flex flex-col justify-center width-100 py-3 items-center">
            <!--        header menu search/login/basket/like     -->
            <div class="xl:container container flex flex-row justify-between items-center mb-4">
                <div class="flex flex-row">
                    <div class="w-44 mr-8">
                        <a href="<?= SITE_DIR ?>">
                            <?php $APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                "",
                                array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => "/local/templates/Oshisha/include/company_logo.php"),
                                false
                            ); ?>
                        </a>
                    </div>
                    <div class="dark:text-textDark text-sm font-extralight xs:hidden ">Вся продукция <br>для кальяна
                    </div>
                </div>
                <div class="flex flex-row items-center justify-between">
                    <div class="w-3/4">
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
    </header>

    <div class="section_wrapper min-h-550 flex flex-col justify-center items-center">
        <div class="xl:container container">
            <?php $needSidebar = preg_match("~^" . SITE_DIR . "(catalog|personal\/cart|personal\/order\/make)/~", $curPage); ?>
            <div class="bx-content <?= STATIC_P ?>">