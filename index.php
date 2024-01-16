<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
if (SITE_EXHIBITION == SITE_ID) {
    $APPLICATION->SetTitle("OSHISHA.shop");
} else {
    $APPLICATION->SetTitle("OSHISHA - Главная");
}
// Переменная для убора функционала под мобильное приложение
$showUserContent = Enterego\PWA\EnteregoMobileAppEvents::getUserRulesForContent();
if ($showUserContent) {
    if (IsModuleInstalled("advertising")):?>
        <div class="banners_box mt-3">
            <div class="banners_container">
                <div class="banner_small">
                    <div class="banner_box_item">
                        <?php $APPLICATION->IncludeComponent(
                            "bitrix:advertising.banner",
                            "oshisha_banners",
                            array(
                                "BS_ARROW_NAV" => "N",
                                "BS_BULLET_NAV" => "N",
                                "BS_CYCLING" => "N",
                                "BS_EFFECT" => "fade",
                                "BS_HIDE_FOR_PHONES" => "Y",
                                "BS_HIDE_FOR_TABLETS" => "N",
                                "BS_KEYBOARD" => "Y",
                                "BS_WRAP" => "Y",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "Y",
                                "COMPONENT_TEMPLATE" => "oshisha_banners",
                                "DEFAULT_TEMPLATE" => "bootstrap_v4",
                                "NOINDEX" => "N",
                                "QUANTITY" => "1",
                                "TYPE" => "NEW_MINI_TOP"
                            ),
                            false
                        ); ?>
                    </div>

                    <div class="banner_box_item">
                        <?php $APPLICATION->IncludeComponent(
                            "bitrix:advertising.banner",
                            "oshisha_banners",
                            array(
                                "BS_ARROW_NAV" => "N",
                                "BS_BULLET_NAV" => "N",
                                "BS_CYCLING" => "N",
                                "BS_EFFECT" => "fade",
                                "BS_HIDE_FOR_PHONES" => "Y",
                                "BS_HIDE_FOR_TABLETS" => "N",
                                "BS_KEYBOARD" => "Y",
                                "BS_WRAP" => "N",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "Y",
                                "COMPONENT_TEMPLATE" => "oshisha_banners",
                                "DEFAULT_TEMPLATE" => "-",
                                "NOINDEX" => "N",
                                "QUANTITY" => "2",
                                "TYPE" => "NEW_MINI_BOTTOM"
                            ),
                            false
                        ); ?>
                    </div>
                </div>
                <div class="banner_big">
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:advertising.banner",
                        "oshisha_banners",
                        array(
                            "BS_ARROW_NAV" => "Y",
                            "BS_BULLET_NAV" => "Y",
                            "BS_CYCLING" => "N",
                            "BS_INTERVAL" => "4000",
                            "BS_EFFECT" => "fade",
                            "BS_HIDE_FOR_PHONES" => "N",
                            "BS_HIDE_FOR_TABLETS" => "N",
                            "BS_KEYBOARD" => "Y",
                            "BS_PAUSE" => "Y",
                            "BS_WRAP" => "Y",
                            "CACHE_TIME" => "3600",
                            "CACHE_TYPE" => "A",
                            "COMPONENT_TEMPLATE" => "oshisha_banners",
                            "DEFAULT_TEMPLATE" => "bootstrap_v4",
                            "NOINDEX" => "Y",
                            "QUANTITY" => "5",
                            "TYPE" => "MAIN"
                        ),
                        false
                    ); ?>
                </div>
            </div>
        </div>
    <?php endif;

    if (SITE_ID !== SITE_EXHIBITION) {
        global $trendFilter;
        $trendFilter = array('PROPERTY_TREND' => '4');
        $actualBlockData = array(
            'select' => array('ID', 'UF_IMG', 'UF_STR', 'UF_LINK'),
            'order' => array('ID' => 'ASC'),
            'limit' => '50',
        );
        $resGetHlbActual = Enterego\EnteregoHelper::getHeadBlock('MainPageActual', $actualBlockData); ?>
        <div class="slider single-items box_slide box_with_actual_none">
            <div class="swiper-wrapper">
                <?php
                foreach ($resGetHlbActual as $items) {
                    ?>
                    <div class="swiper-slide image_box_wrap">
                        <a href="<?php echo $items['UF_LINK']; ?>" class=" image_box">
                            <img src="<?php echo $items['UF_IMG']; ?> " alt="actual"> </a>
                    </div>
                <?php }
                ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="navigation-slide">
            <span class="new_custom_button_slick_left" aria-hidden="true"><i class="fa fa-angle-left"
                                                                             aria-hidden="true"></i></span>
                <span class="new_custom_button_slick_right" aria-hidden="true"><i class="fa fa-angle-right"
                                                                                  aria-hidden="true"></i></span>
            </div>
        </div>
        <script type="text/javascript">
            var swiper = new Swiper('.box_slide', {
                slidesPerView: 4,
                adaptiveHeight: true,
                spaceBetween: 10,
                pagination: false,
                navigation: {
                    prevEl: '.new_custom_button_slick_left',
                    nextEl: '.new_custom_button_slick_right'
                },
                speed: 3000,

                autoplay: {
                    enabled: true,
                    delay: 4000,
                },
                breakpoints: {
                    320: {
                        slidesPerView: 2,
                        adaptiveHeight: true,
                        spaceBetween: 10
                    },
                    991: {
                        slidesPerView: 3,
                        adaptiveHeight: true,
                        spaceBetween: 10
                    },
                    1440: {
                        slidesPerView: 4,
                        adaptiveHeight: true,
                        spaceBetween: 10
                    },
                }
            });
        </script>
        <div class="box_with_banner_dop">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:advertising.banner",
                "oshisha_banners",
                array(
                    "BS_ARROW_NAV" => "N",
                    "BS_BULLET_NAV" => "N",
                    "BS_CYCLING" => "N",
                    "BS_EFFECT" => "fade",
                    "BS_HIDE_FOR_PHONES" => "N",
                    "BS_HIDE_FOR_TABLETS" => "N",
                    "BS_KEYBOARD" => "N",
                    "BS_WRAP" => "N",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "N",
                    "DEFAULT_TEMPLATE" => "-",
                    "NOINDEX" => "N",
                    "QUANTITY" => "1",
                    "TYPE" => "BANNERS_HOME_1",
                    "COMPONENT_TEMPLATE" => "oshisha_banners"
                ),
                false
            ); ?>
        </div>
        <?php $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "sect",
                "AREA_FILE_SUFFIX" => "bottom",
                "AREA_FILE_RECURSIVE" => "N",
                "EDIT_MODE" => "html",
            ),
            false,
            array('HIDE_ICONS' => 'Y')
        );

        if (defined('PROPERTY_USE_ON_MAIN_PAGE')) {
            $GLOBALS['FILTER_SALE'] = array(
                'PROPERTY_' . PROPERTY_USE_ON_MAIN_PAGE . '_VALUE' => 'Да'
            );
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.top",
                "oshisha_catalog.top",
                array(
                    "ACTION_VARIABLE" => "action",
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
                    "VALUE_TITLE_IN_TEMPLATE" => 'Распродажа',
                    "COMPONENT_TEMPLATE" => "oshisha_catalog.top",
                    "CONVERT_CURRENCY" => "N",
                    "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                    "DETAIL_URL" => "",
                    "DISPLAY_COMPARE" => "N",
                    "ELEMENT_COUNT" => "16",
                    "ELEMENT_SORT_FIELD" => "PROPERTY_" . SORT_POPULARITY,
                    "ELEMENT_SORT_FIELD2" => "ID",
                    "ELEMENT_SORT_ORDER" => "desc",
                    "ELEMENT_SORT_ORDER2" => "desc",
                    "ENLARGE_PRODUCT" => "PROP",
                    "ENLARGE_PROP" => "-",
                    "FILTER_NAME" => "FILTER_SALE",
                    "HIDE_NOT_AVAILABLE" => "Y",
                    "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                    "IBLOCK_ID" => IBLOCK_CATALOG,
                    "IBLOCK_TYPE" => "1c_catalog",
                    "LABEL_PROP" => "",
                    "LABEL_PROP_MOBILE" => "",
                    "LABEL_PROP_POSITION" => "top-left",
                    "LINE_ELEMENT_COUNT" => "20",
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
                    "SHOW_TITLE_IN_TEMPLATE" => "Y",
                    "PROPERTY_CODE" => array(
                        0 => "USE_DISCOUNT",
                        1 => "",
                    ),
                    "PRODUCT_PROPERTIES" => array(
                        "USE_DISCOUNT"
                    )
                ),
                false,
            );
        }
    }
} else { ?>
    <div class="bx-content static_wrap">
        <div class="p-lg-5 p-md-5 p-4 index-ugol br-10" style="background-color: #f0f0f0; border-radius: 10px">
            <div class="d-flex justify-content-lg-between justify-content-md-between flex-column height_100">
                <div>
                    <h5><b>
                            Представляем вам один из современных брендов в напитках! <br class="mb-2">
                            Чайный напиток "Ройбос" <i class="fa fa-leaf" aria-hidden="true"></i></b></h5>
                    <div class="mb-4 font-12">
                        Ройбуш (Ройбос) – это куст, который произрастает в Южной Африке. <br>
                        Кора имеет красный цвет, а вместо листьев на очень длинных ветках произрастают иголки.<br>
                        Ройбуш (Ройбос) приготавливают из коры и веток, которые мелко и крупно измельчают.<br>
                        Процесс ферментации происходит в течение восьми часов.<br>
                        Ройбуш (Ройбос) является сильным антиоксидантом средством,
                        что способствует омоложению организма в целом, благотворно влияет на состояние кожи.<br>
                    </div>
                    <img src="/images/roibush.png" class="br-10 mb-3"/>
                    <div class="font-12">
                        Настой Ройбуша (Ройбос) используют в качестве примочек и компрессов против кожных заболеваний
                        (дерматит, экзема, зуд, раздражение кожи)
                        Так как он является, природным источником тетрациклина это делает его бактерицидным
                        средством.<br>
                        Ройбуш (Ройбос) содержит огромное количество витаминов и минеральных веществ.<br>
                        Ройбуш (Ройбос) балансирует давление, рекомендован при аллергии, депрессии.<br>
                        Ройбуш (Ройбос) вообще не содержит кофеина и может применяться при снятия вздутия живота.<br>
                        Ройбуш (ройбос) имеет маленькое содержание танина (танин препятствует усвоению железа). <br>
                        <b>
                            P.s. Несмотря на все полезные свойства чайных напитков, необходимо отметить возможность аллергической реакции.
                            Употребление безопасно, если у человека нет индивидуальной непереносимости ингредиентов, входящих в состав.
                            Противопоказаниями так же являются: возраст до 18 лет - в связи с тем что  молодой организм не до конца сформирован,
                            беременность и период грудного вскармливания  - в связи с индивидуальной средой организма.
                            Мы можем помочь подобрать вам нужный товар по индивидуальным запросам! Пишите нам на форму обратной связи.
                        </b>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3 mt-5">
        <?php $GLOBALS['FILTER_UGOL'] = array(
            "SECTION_CODE" => "chay_new",
            "INCLUDE_SUBSECTIONS" => "Y",
        );
        $APPLICATION->IncludeComponent(
            "bitrix:catalog.top",
            "oshisha_catalog.top",
            array(
                "ACTION_VARIABLE" => "action",
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
                "VALUE_TITLE_IN_TEMPLATE" => 'Хиты продаж',
                "COMPONENT_TEMPLATE" => "oshisha_catalog.top",
                "CONVERT_CURRENCY" => "N",
                "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
                "DETAIL_URL" => "",
                "DISPLAY_COMPARE" => "N",
                "ELEMENT_COUNT" => "16",
                "ELEMENT_SORT_FIELD" => "PROPERTY_" . SORT_POPULARITY,
                "ELEMENT_SORT_FIELD2" => "ID",
                "ELEMENT_SORT_ORDER" => "desc",
                "ELEMENT_SORT_ORDER2" => "desc",
                "ENLARGE_PRODUCT" => "PROP",
                "ENLARGE_PROP" => "-",
                "FILTER_NAME" => "FILTER_UGOL",
                "HIDE_NOT_AVAILABLE" => "Y",
                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                "IBLOCK_ID" => IBLOCK_CATALOG,
                "IBLOCK_TYPE" => "1c_catalog",
                "LABEL_PROP" => "",
                "LABEL_PROP_MOBILE" => "",
                "LABEL_PROP_POSITION" => "top-left",
                "LINE_ELEMENT_COUNT" => "20",
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
                "SHOW_TITLE_IN_TEMPLATE" => "Y",
                "PROPERTY_CODE" => array(
                    0 => "USE_DISCOUNT",
                    1 => "",
                ),
                "PRODUCT_PROPERTIES" => array(
                    "USE_DISCOUNT"
                )
            ),
            false,
        ); ?></div>
    <div class="bx-content static_wrap">
        <div class="p-md-4 p-lg-4 p-3 index-ugol br-10" style="background-color: #f0f0f0; border-radius: 10px">
            <div class="d-flex justify-content-lg-between justify-content-md-between flex-column height_100">
                <div>
                    <div class="mb-4 font-12">
                        <h5><b>Как Ройбуш (Ройбос) можно приготовить?</b></h5>
                        Чай Ройбуш (Ройбос) употребляется в холодном и горячем виде. Заваривается до 3-х раз. <br>
                        В отличие от обычного черного и зеленого чая при настаивании и длительном томлении получает
                        полезные свойства.<br>
                        Ройбуш (Ройбос) заваривают не меньше 5-7 минут.<br>
                        После этого Ройбуш (Ройбос) помещают в микроволновой печь или в духовом шкаф чтобы он продолжал
                        там кипеть.<br>
                        Дозировка соблюдается как у обычного черного чая 1 ложка на 150-200 гр.<br>
                        Заваривается в обычных чайниках: фарфор, глина, фаянс.<br>
                        В отличии от обычного чая Ройбуш (Ройбос) не теряет свои вкусовые и полезные качества при
                        томлении и кипении.
                    </div>
                    <img src="/images/roibush-tea.jpg" class="br-10"/>
                </div>
            </div>
        </div>
    </div>
    <?php
}
// TODO - обработка лайки
//$update = new Enterego\EnteregoProcessing();
//$update->update_like_in_new_table();
//$update->update_favorites_product_users();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
