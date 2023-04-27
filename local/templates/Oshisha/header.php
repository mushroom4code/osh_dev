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
Extension::load("ui.bootstrap4");

$curPage = $APPLICATION->GetCurPage(true);
$MESS["CITY_CHOOSE_TITLE"] = 'Выберите город';
global $option_site;
$option = $option_site;
$MESS["CITY_CHOOSE_PLACEHOLDER"] = 'Ваш город ...';
?><!DOCTYPE html>
<html xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
<head>

    <!-- Yandex.Metrika counter -->
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
    Asset::getInstance()->addCss("https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css");
    Asset::getInstance()->addJs("https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/script.js");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/style.css");
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
<body class="bx-background-image">
<div id="panel"><?php $APPLICATION->ShowPanel(); ?>
</div>
<div id="bx_eshop_wrap">
    <header>
        <?php if (CHECKED_INFO) {
            $Option = json_decode(COption::GetOptionString('activation_info_admin', 'PERIOD')); ?>
            <div class="alert-info-setting">
                <p class="mb-0 text-center d-lg-block d-md-block d-none">
                    <?= !empty($Option->text_info) ?  $Option->text_info : '' ?>
                    <a href="<?= !empty($Option->link_info) ?  $Option->link_info : '/' ?>"
                       class="text-decoration-underline font-14 font-weight-bold color-white"> перейти по ссылке</a>.</p>
                <p class="mb-0 text-center d-lg-none d-md-none d-block">
                    <?= !empty($Option->text_info_mobile) ?  $Option->text_info_mobile : '' ?>
                    <a href="<?= !empty($Option->link_info) ?  $Option->link_info : '/' ?>"
                       class="text-decoration-underline font-14 font-weight-bold color-white"> перейти по ссылке</a>.</p>
            </div>
        <?php } ?>
        <div class="header_top_panel">
            <div class="header_logo_mobile">
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
            <div class="container_header flex_header">
                <div class="box_with_city flex_header col-5 pl-0">
                    <span class="d-flex flex-row align-items-center">
                        <img src="/local/assets/images/icon_location.svg" class="icon_location">
                        <a href="#" class="text_header">
                            <div class="place">
                                <?php $styleNone = '';
                                if (strripos($_SERVER['REQUEST_URI'], '/personal/order/make') !== false) {
                                    $styleNone = 'style="display:none;"';
                                } ?>
                        <button type="button" class="place__button" data-toggle="modal"
                                data-target="#placeModal" <?= $styleNone ?>>
                            <?php
                            // отключение композитного кеша вне компонента
                            Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("city-title");
                            // динамический контент
                            $code_region = $_SESSION["code_region"];

                            if (empty($_SESSION["code_region"])) {

                                $user_id = $USER->GetID();

                                if (!empty($user_id)) {

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
                            <span id="city-title" class="text_header" data-city="<?= $code_region ?>">
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_current.php") ?>
                                        <?php include($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/geolocation/location_select.php") ?>
                            </span>
                            <?php Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("city-title", ""); ?>
                        </button>
                    </div></a>
                    </span>
                    <a href="/about/feedback_new_site/" class="red_text text_font_13 ml-2 mr-2 font-weight-bold">Написать
                        отзыв</a>
                </div>
                <div class="box_with_menu_header flex_header flex_header_right col-7 pr-0">
                    <a href="/about/o-nas/" class="text_header">О нас</a>
                    <?php if ($USER->IsAuthorized()) { ?>
                        <a href="<?= $option->price_list_link; ?>" class="text_header ">Прайс-лист</a>
                    <?php } else { ?>
                        <a href="/login/" class="text_header ">Прайс-лист</a>
                    <?php } ?>
                    <a href="/about/contacts/" class="text_header">Контакты</a>
                    <?php if ($USER->IsAuthorized()) { ?>
                        <a href="/about/delivery/" class="text_header">Доставка и оплата</a>
                    <?php } ?>
                    <a href="javascript:void(0)" class="text_header callback js__callback">Обратный звонок</a>
                    <?php if ($USER->IsAuthorized()) { ?>
                        <a href="/personal/support/" class="text_header" style="display:none">Поддержка</a>
                    <?php } else { ?>
                        <a href="/about/FAQ/#support" class="text_header">Поддержка</a>
                    <?php } ?>
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
        <div class="container_header">
            <!--        header menu search/login/basket/like     -->
            <div class="header_box_logo">
                <div class="box_left_header">
                    <div class="header_logo_desktop">
                        <a href="<?= SITE_DIR ?>">
                            <?php $APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                "",
                                array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_DIR . "include/company_logo.php"),
                                false
                            ); ?>
                        </a>
                    </div>
                    <div class="text_header_menu"><span>Вся продукция <br>для кальяна</span></div>
                </div>
                <div class="box_right_header">
                    <div class="box_with_search">
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
                    <div class="block_menu_mobile bx-header-personal z-index-1200">
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
                <div class="box_with_menu">
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
    <div class="section_wrapper">
        <div class="container_header">
            <?php $needSidebar = preg_match("~^" . SITE_DIR . "(catalog|personal\/cart|personal\/order\/make)/~", $curPage); ?>
            <div class="bx-content <?= STATIC_P ?>">