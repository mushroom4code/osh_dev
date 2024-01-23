<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $item
 * @var array $actualItem
 * @var array $minOffer
 * @var array $itemIds
 * @var array $price
 * @var array $measureRatio
 * @var bool $haveOffers
 * @var bool $showSubscribe
 * @var array $morePhoto
 * @var bool $showSlider
 * @var bool $itemHasDetailUrl
 * @var string $imgTitle
 * @var string $productTitle
 * @var string $buttonSizeClass
 * @var CatalogSectionComponent $component
 */
$mainId = $this->GetEditAreaId($item['ID']);

$arItemIDs = array(
    'ID' => $mainId,
    'DISCOUNT_PERCENT_ID' => $mainId . '_dsc_pict',
    'STICKER_ID' => $mainId . '_sticker',
    'BIG_SLIDER_ID' => $mainId . '_big_slider',
    'BIG_IMG_CONT_ID' => $mainId . '_bigimg_cont',
    'SLIDER_CONT_ID' => $mainId . '_slider_cont',
    'OLD_PRICE_ID' => $mainId . '_old_price',
    'PRICE_ID' => $mainId . '_price',
    'DISCOUNT_PRICE_ID' => $mainId . '_price_discount',
    'PRICE_TOTAL' => $mainId . '_price_total',
    'SLIDER_CONT_OF_ID' => $mainId . '_slider_cont_',
    'QUANTITY_ID' => $mainId . '_quantity',
    'QUANTITY_DOWN_ID' => $mainId . '_quant_down',
    'QUANTITY_UP_ID' => $mainId . '_quant_up',
    'QUANTITY_MEASURE' => $mainId . '_quant_measure',
    'QUANTITY_LIMIT' => $mainId . '_quant_limit',
    'BUY_LINK' => $mainId . '_buy_link',
    'ADD_BASKET_LINK' => $mainId . '_add_basket_link',
    'BASKET_ACTIONS_ID' => $mainId . '_basket_actions',
    'NOT_AVAILABLE_MESS' => $mainId . '_not_avail',
    'COMPARE_LINK' => $mainId . '_compare_link',
    'TREE_ID' => $mainId . '_skudiv',
    'DISPLAY_PROP_DIV' => $mainId . '_sku_prop',
    'DISPLAY_MAIN_PROP_DIV' => $mainId . '_main_sku_prop',
    'OFFER_GROUP' => $mainId . '_set_group_',
    'BASKET_PROP_DIV' => $mainId . '_basket_prop',
    'SUBSCRIBE_LINK' => $mainId . '_subscribe',
    'TABS_ID' => $mainId . '_tabs',
    'TAB_CONTAINERS_ID' => $mainId . '_tab_containers',
    'SMALL_CARD_PANEL_ID' => $mainId . '_small_card_panel',
    'TABS_PANEL_ID' => $mainId . '_tabs_panel'
);
$favorite = '';
$styleForTaste = '';
$taste = $item['PROPERTIES'][PROPERTY_KEY_VKUS];
$codeProp = $item['PROPERTIES']['CML2_TRAITS'];
$useDiscount = $item['PROPERTIES']['USE_DISCOUNT'];
$newProduct = $item['PROPERTIES'][PROP_NEW];
$hitProduct = $item['PROPERTIES'][PROP_HIT];
$rowResHidePrice = $item['PROPERTIES']['SEE_PRODUCT_AUTH']['VALUE'];

$show_price = true;
$priceBasket = 0;
$styleForNo = $href = $not_auth = $styleForTaste = '';
$productTitle = str_replace("\xC2\xA0", " ", $productTitle);
$jsonForModal = [];

$specialPrice = 0;
if (!empty($price['USER_PRICE'])) {
    $specialPrice = $price['USER_PRICE']['PRICE'];
}

if (!empty($price['SALE_PRICE']['PRICE']) &&
    ($useDiscount['VALUE_XML_ID'] == 'true' || USE_CUSTOM_SALE_PRICE)) {

    $specialPrice = ($specialPrice === 0 || $price['SALE_PRICE']['PRICE'] < $specialPrice)
        ? $price['SALE_PRICE']['PRICE']
        : $specialPrice;
}

if ($rowResHidePrice == 'Нет' && !$USER->IsAuthorized()) {
    $show_price = false;
    $not_auth = 'link_header_box';
}


if ($item['PRODUCT']['QUANTITY'] == '0') {
    $styleForNo = 'not_av';
}

foreach ($item['ACTUAL_BASKET'] as $key => $val) {
    if ($key == $item['ID']) {
        $priceBasket = $val;
    }
}

if (!$show_price) {
    $href = $item['DETAIL_PAGE_URL'];
    $item['DETAIL_PAGE_URL'] = 'javascript:void(0)';
}

$subscription_item_ids = array_column($arResult["CURRENT_USER_SUBSCRIPTIONS"]["SUBSCRIPTIONS"] ?? [], 'ITEM_ID');
$found_key = array_search((string)$item['ID'], $subscription_item_ids);
$is_key_found = isset($found_key) && ($found_key !== false);

if (($newProduct['VALUE'] == 'Да') && ($hitProduct['VALUE'] != 'Да')) { ?>
    <span class="taste bg-greenLight dark:bg-greenButton text-white absolute md:-left-4 -left-2 md:-top-3 -top-2 md:py-2.5
     py-2 px-0.5 md:px-1 rounded-full md:text-xs text-10 z-10 font-medium">NEW</span>
<?php }
if ($hitProduct['VALUE'] === 'Да') { ?>
    <span class="taste bg-yellowSt text-black absolute font-semibold md:-left-4 -left-2 md:-top-3 -top-2 md:py-2.5
     py-1.5 px-1 md:px-1.5 rounded-full md:text-xs text-10 z-10">ХИТ</span>
<?php } ?>
<div class="catalog-item-product dark:bg-darkBox border dark:border-0 border-gray-product rounded-xl md:px-4 py-4 px-3
 h-full relative <?= ($item['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>"
     data-product_id="<?= $item['ID'] ?>">
    <div class="bx_catalog_item_container position-relative h-full  <?= $taste['VALUE'] ? 'is-taste' : '' ?>">
        <?php
        $showToggler = false; // по умолчанию стрелки нет (случаи когда вкус 1)
        if ($taste['VALUE']) {
//            // TODO - 23 - число символов которое помещается в строку
            $boolUp = (mb_strlen($taste['VALUE'][0]) + mb_strlen($taste['VALUE'][1]) + mb_strlen($taste['VALUE'][2])) > 23;
            if (count($taste['VALUE']) > 1 && $boolUp || count($taste['VALUE']) > 3) {
                $showToggler = true;
            }
        }

        ?>
        <div class="item-product-info h-full flex flex-col justify-between">
            <div class="toggle_taste card-price <?= $taste['VALUE'] ? 'js__tastes' : '' ?> z-20 h-7 relative
            <?php if (!$show_price) { ?> blur-2xl <?php } ?>">
                <div class="variation_taste flex flex-wrap flex-row overflow-hidden md:h-7 h-5 js__tastes-list w-97">
                    <?php if ($taste['VALUE']) {
                        foreach ($taste['VALUE'] as $key => $name) {
                            foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
                                if ($key === $keys) {
                                    $color = explode('#', $value);

                                    $propId = $taste['ID'];
                                    $valueKey = abs(crc32($taste["VALUE_ENUM_ID"][$keys]));
                                    ?>
                                    <span class="taste cursor-pointer js__taste h-fit md:px-2 px-1.5 mr-1 md:py-1 py-0.5
                                    mb-1 md:text-xs text-10 rounded-full"
                                          data-prop-id="<?= "ArFilter_{$propId}" ?>"
                                          data-background="<?= '#' . $color[1] ?>"
                                          id="<?= "taste-ArFilter_{$propId}_{$valueKey}" ?>"
                                          data-filter-get='<?= "ArFilter_{$propId}_{$valueKey}" ?>'><?= $name ?></span>
                                <?php }
                            }
                        }
                    } ?>
                </div>
                <?php if ($showToggler) { ?>
                    <div class="absolute top-0 right-0 z-20">
                        <svg width="15" height="10" class="js__toggle-show rotate-0"
                             viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.1636 -0.00231934H6.34969H1.11438C0.218505 -0.00231934 -0.229434 1.41033 0.405145 2.23844L5.23917 8.54663C6.01373 9.5574 7.27356 9.5574 8.04812 8.54663L9.88653 6.14757L12.8821 2.23844C13.5074 1.41033 13.0594 -0.00231934 12.1636 -0.00231934Z"
                                  fill="#BFBFBF"/>
                        </svg>
                    </div>
                <?php } ?>
            </div>
            <div class="product-toggle">
                <div class="bx_catalog_item_overlay"></div>
                <div class="image_cart md:h-40 h-28 position-relative md:mb-3 mb-2 <?php if (!$show_price) { ?> blur-lg <?php } ?>
                    <?= $not_auth ?>" data-href="<?= $href ?>">
                    <a class="flex justify-center rounded-xl bg-white <?= $styleForTaste ?>"
                       href="<?= $item['DETAIL_PAGE_URL']; ?>">
                        <?php if (!empty($item['PREVIEW_PICTURE']['SRC'])) { ?>
                            <img src="<?= $item['PREVIEW_PICTURE']['SRC']; ?>" class="md:h-40 h-28"
                                 alt="<?= $productTitle ?>"/>
                        <?php } else { ?>
                            <img src="/local/templates/Oshisha/images/no-photo.gif" class="md:h-40 h-28"
                                 alt="no photo"/>
                        <?php } ?>
                    </a>
                    <div class="initialPopup absolute mb-2 top-20 right-4 z-20 p-2 bg-whiteOpacity rounded-full cursor-pointer"
                         data-product-id="<?= $item['ID'] ?>" data-area-quantity="<?= $arItemIDs['QUANTITY_ID'] ?>"
                         data-area-buy="<?= $arItemIDs['BUY_LINK'] ?>"
                         data-grouped-product="<?= htmlspecialchars(json_encode($item['PROPERTIES']['PRODUCTS_LIST_ON_PROP']['VALUE'])); ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.972 13.4274C14.2256 12.1625 15 10.4216 15 8.5C15 4.63401 11.866 1.5 8 1.5C4.13401 1.5 1 4.63401 1 8.5C1 12.366 4.13401 15.5 8 15.5C9.94437 15.5 11.7035 14.7072 12.972 13.4274ZM12.972 13.4274L18.5 19"
                                  stroke="#1A1A1A" stroke-width="1.8" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <?php if ($price['PRICE_DATA']['PRICE'] !== '') { ?>
                    <div class="bx_catalog_item_price md:mt-2 mb-2 mt-1 d-flex justify-content-end">
                        <div class="box_with_titles flex flex-row text-xs text-textLight justify-between dark:text-textDarkLightGray">
                            <?php
                            $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                'templates',
                                [
                                    'ID_PROD' => $item['ID_PROD'],
                                    'F_USER_ID' => $item['F_USER_ID'],
                                    'LOOK_LIKE' => false,
                                    'LOOK_FAVORITE' => true,
                                    'COUNT_LIKE' => $item['COUNT_LIKE'],
                                    'COUNT_FAV' => $item['COUNT_FAV'],
                                    'COUNT_LIKES' => $item['COUNT_LIKES'],
                                ],
                                $component,
                                [
                                    'HIDE_ICONS' => 'Y'
                                ]
                            );
                            $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                'templates',
                                array(
                                    'ID_PROD' => $item['ID_PROD'],
                                    'F_USER_ID' => $item['F_USER_ID'],
                                    'LOOK_LIKE' => true,
                                    'LOOK_FAVORITE' => false,
                                    'COUNT_LIKE' => $item['COUNT_LIKE'],
                                    'COUNT_FAV' => $item['COUNT_FAV'],
                                    'COUNT_LIKES' => $item['COUNT_LIKES'],
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y'),
                            );
                            ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="box_with_titles flex flex-row text-xs text-textLight dark:text-textDarkLightGray">
                        <div class="not_product">
                            Товара нет в наличии
                        </div>
                        <?php
                        $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                            'templates',
                            array(
                                'ID_PROD' => $item['ID_PROD'],
                                'F_USER_ID' => $item['F_USER_ID'],
                                'LOOK_LIKE' => false,
                                'LOOK_FAVORITE' => true,
                                'COUNT_LIKE' => $item['COUNT_LIKE'],
                                'COUNT_FAV' => $item['COUNT_FAV'],
                                'COUNT_LIKES' => $item['COUNT_LIKES'],
                            )
                            ,
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        );
                        $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                            'templates',
                            array(
                                'ID' => $item['ID_PROD'],
                                'F_USER_ID' => $item['F_USER_ID'],
                                'LOOK_LIKE' => true,
                                'LOOK_FAVORITE' => false,
                                'COUNT_LIKE' => $item['COUNT_LIKE'],
                                'COUNT_FAV' => $item['COUNT_FAV'],
                                'COUNT_LIKES' => $item['COUNT_LIKES'],
                            ),
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        ); ?>
                    </div>
                <?php } ?>
                <div class="box_with_title_like md:mb-3 mb-2 d-flex align-items-center h-9">
                    <div class="box_with_text mb-3">
                        <a class="bx_catalog_item_title line-clamp-2 md:text-sm text-xs font-medium text-textLight
                        dark:font-light dark:text-textDarkLightGray hover:text-hover-red
                        <?= $styleForNo . ' ' . $not_auth ?>"
                           href="<?= $item['DETAIL_PAGE_URL']; ?>"
                           data-href="<?= $href ?>"
                           title="<?= $productTitle; ?>">
                            <?= $productTitle; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
            $showSubscribeBtn = false;
            $compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCT_TPL_MESS_BTN_COMPARE')); ?>
            <div class="bx_catalog_item_controls relative product-toggle">
                <?php if ($price['PRICE_DATA']['PRICE'] !== '0' && $item['PRODUCT']['QUANTITY'] !== '0') { ?>
                    <div class="box_with_fav_bask flex md:flex-row flex-col md:justify-between md:items-center">
                        <?php if ($price['PRICE_DATA']['PRICE'] !== '') { ?>
                            <div class="box_with_price card-price font_weight_600  min-height-auto">
                                <div class="flex md:flex-col flex-row md:items-start items-center relative">
                                    <div class="bx_price md:text-xl text-lg font-semibold dark:font-medium md:mb-0 mb-1
                                        <?= $styleForNo ?> position-relative md:mr-0 mr-2">
                                        <?php
                                        if (!empty($specialPrice)) {
                                            echo(round($specialPrice));
                                        } else {
                                            echo(round($price['PRICE_DATA']['PRICE']));
                                        } ?>₽
                                    </div>
                                    <?php if (!empty($specialPrice)) { ?>
                                        <div class="font-10 d-lg-block d-mb-block d-flex flex-wrap align-items-center relative">
                                            <span class="line-through font-light decoration-red text-textLight
                                             dark:text-grayIconLights mr-2 md:text-lg text-xs">
                                                <?= $price['PRICE_DATA']['PRICE'] ?>₽</span>
                                            <span class="sale-percent text-light-red font-medium md:text-lg text-10
                                             md:inherit absolute md:top-auto md:left-auto -top-2 w-full left-[51%]">
                                                - <?= (round($price['PRICE_DATA']['PRICE']) - round($specialPrice)) ?>₽
                                            </span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if ($arResult['IS_SUBSCRIPTION_PAGE'] == 'Y'): ?>
                                <div class="detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                            <?= $is_key_found ? 'subscribed' : '' ?> min_card">
                                    <i class="fa fa-bell-o <?= $is_key_found ? 'filled' : '' ?>" aria-hidden="true"></i>
                                </div>
                                <div id="popup_mess" class="catalog_popup<?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                             <?= $is_key_found ? 'subscribed' : '' ?>"
                                     data-subscription_id="<?= $is_key_found ? $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][$found_key]['ID'] : '' ?>"
                                     data-product_id="<?= $item['ID']; ?>">
                                </div>
                            <? else: ?>
                                <div class="d-flex row-line-reverse justify-content-between box-basket">
                                    <?php if ($show_price && $item['ADD_TO_BASKET']) { ?>
                                        <div class="btn red_button_cart btn-plus add2basket flex justify-center
                                       dark:bg-dark-red bg-light-red py-2 px-3.5 rounded-5 items-center"
                                             data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                             data-product_id="<?= $item['ID']; ?>"
                                             data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                             id="<?= $arItemIDs['BUY_LINK']; ?>"
                                             <?php if ($priceBasket > 0): ?>style="display:none;"<?php endif; ?>
                                        >
                                            <span class="text-sm text-white md:hidden block">Добавить </span>
                                            <svg width="22" height="26" viewBox="0 0 18 22" fill="none"
                                                 class="md:block hidden"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.6017 18.9561V15.8498M13.6017 15.8498H16.4364M13.6017 15.8498H10.767M13.6017 15.8498V14.1413V12.5881"
                                                      stroke="white" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M12.9978 7.09848H14.1597C15.2019 7.09848 16.0701 7.97388 16.1567 9.11199M12.9978 7.09848H4.98235M12.9978 7.09848V6.00055C12.9978 4.83579 12.5756 3.71874 11.8239 2.89513C11.0724 2.07153 10.053 1.60883 8.99007 1.60883C7.92712 1.60883 6.90778 2.07153 6.15618 2.89513C5.4046 3.71874 4.98235 4.83579 4.98235 6.00055V7.09848M12.9978 7.09848V9.51393M9.76502 20.2737H3.15243C1.98009 20.2737 1.05814 19.1756 1.1555 17.8954L1.82345 9.11199C1.91 7.97388 2.7782 7.09848 3.82039 7.09848H4.98235M4.98235 7.09848V9.51393"
                                                      stroke="white" stroke-width="2" stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div class="product-item-amount-field-contain-wrap justify-center"
                                             <?php if ($priceBasket > 0){ ?>style="display:flex;"<?php } else { ?>
                                            style="display:none;"
                                        <?php } ?>
                                             data-product_id="<?= $item['ID']; ?>">
                                            <div class="product-item-amount-field-contain flex flex-row items-center
                                            justify-between w-full">
                                                <a class="btn-minus rounded-full md:py-0 md:px-0 py-3.5 px-1.5
                                                dark:bg-dark md:dark:bg-darkBox bg-none no-select add2basket
                                                cursor-pointer flex items-center justify-center md:h-full h-auto md:w-full w-auto"
                                                   id="<?= $arItemIDs['BUY_LINK']; ?>"
                                                   href="javascript:void(0)" data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                                   data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                   data-product_id="<?= $item['ID']; ?>">
                                                    <svg width="20" height="2" viewBox="0 0 22 2" fill="none"
                                                         class="stroke-dark dark:stroke-white stroke-[1.5px]"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1 1H21" stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                <div class="product-item-amount-field-block">
                                                    <input class="product-item-amount dark:bg-grayButton bg-textDarkLightGray
                                                focus:border-none text-center border-none text-sm
                                                 shadow-none py-2.5 px-3 md:mx-2 mx-1 outline-none rounded-md md:w-14 w-16 card_element"
                                                           id="<?= $arItemIDs['QUANTITY_ID'] ?>"
                                                           type="number"
                                                           data-product_id="<?= $item['ID']; ?>"
                                                           max="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                           data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                           value="<?= $priceBasket ?>">
                                                </div>
                                                <a class="btn-plus no-select add2basket cursor-pointer flex items-center
                                                 justify-center rounded-full md:p-0 p-1.5 dark:bg-dark md:dark:bg-darkBox
                                                  bg-none md:h-full h-auto md:w-full w-auto"
                                                   data-max-quantity="<?= $item['PRODUCT']['QUANTITY'] ?>"
                                                   id="<?= $arItemIDs['BUY_LINK']; ?>" href="javascript:void(0)"
                                                   data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                                   data-product_id="<?= $item['ID']; ?>"
                                                   title="Доступно <?= $item['PRODUCT']['QUANTITY'] ?> товар">
                                                    <svg width="20" height="20" viewBox="0 0 20 20"
                                                         class="fill-light-red dark:fill-white"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M18.8889 11.111H1.11111C0.503704 11.111 0 10.6073 0 9.9999C0 9.3925 0.503704 8.88879 1.11111 8.88879H18.8889C19.4963 8.88879 20 9.3925 20 9.9999C20 10.6073 19.4963 11.111 18.8889 11.111Z"/>
                                                        <path d="M10 20C9.39262 20 8.88892 19.4963 8.88892 18.8889V1.11111C8.88892 0.503704 9.39262 0 10 0C10.6074 0 11.1111 0.503704 11.1111 1.11111V18.8889C11.1111 19.4963 10.6074 20 10 20Z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                            <div class="alert_quantity hidden absolute md:p-4 p-2 text-xs left-0 top-12 bg-filterGray
                                            dark:bg-tagFilterGray w-full shadow-lg rounded-md z-20"
                                                 data-id="<?= $item['ID'] ?>"></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <? endif; ?>
                        <?php }
                        if (!$USER->IsAuthorized() && !$show_price) { ?>
                            <div class="btn-plus <?= $not_auth ?>"
                                 data-href="<?= $href ?>">
                                <span class="btn red_button_cart text-xs dark:text-textDark text-white font-medium
                                dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 md:w-auto w-full flex justify-center">
                                    Подробнее</span>
                            </div>
                        <?php } else if (!$item['ADD_TO_BASKET']) { ?>
                            <div>
                                <span class="btn red_button_cart text-xs dark:text-textDark text-white font-medium
                                dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 open-popup md:w-auto w-full"
                                      onclick="showHidePopupPrice(this)">Подробнее</span>
                                <div class="text-black font-extralight text-center hidden absolute p-5 shadow-lg popup-window-price
                                bg-filterGray dark:text-textDarkLightGray text-xs dark:bg-grayButton rounded-lg w-72
                                 z-50 left-0">
                                    <p class="flex justify-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24"
                                             class="mb-3 stroke-light-red dark:stroke-white"
                                             fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke-width="2"/>
                                            <line x1="12" y1="11" x2="12" y2="18" stroke-width="2"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="12" y1="7" x2="12" y2="6" stroke-width="2" stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                        </svg>
                                    </p>
                                    У вас нет активных контрагентов для совершения покупок на этом сайте!<br>
                                    Вы можете
                                    <a href="/personal/contragents/"
                                       class="text-light-red dark:text-white font-medium">Создать контрагента</a>
                                    и обратиться к менеджеру <br>
                                    или перейти на наш
                                    <a href="https://oshisha.net"
                                       class="text-light-red dark:text-white font-medium">Розничный сайт</a>
                                    <span class="absolute -right-2 -top-2 cursor-pointer"
                                          onclick="$(this).closest('div').toggleClass('hidden')">
                                        <svg width="25" height="25" viewBox="0 0 60 60" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.7"
                                                  d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z"
                                                  fill="#676767"></path>
                                            <path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z"
                                                  fill="white"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div style="clear: both;"></div>
                <?php } else { ?>
                    <div id="<?= $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="not_avail">
                        <div class="box_with_fav_bask">
                            <div class="not_product detail_popup text-xs dark:text-textDark text-white font-medium
                             flex justify-center flex-row items-center dark:bg-dark-red bg-light-red py-2 px-4
                             rounded-full text-center w-auto <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                             <?= $is_key_found ? 'subscribed' : '' ?>">
                                <svg width="18" height="17" class="mr-1 stroke-white
                                <?= $is_key_found ? 'subscribed' : ' ' ?>"
                                     viewBox="0 0 34 33" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M25.5762 11.0001C25.5762 8.81209 24.6884 6.71367 23.1081 5.16649C21.5279 3.61932 19.3846 2.75012 17.1498 2.75012C14.915 2.75012 12.7717 3.61932 11.1915 5.16649C9.61121 6.71367 8.72344 8.81209 8.72344 11.0001C8.72344 20.6251 4.51025 23.3751 4.51025 23.3751H29.7894C29.7894 23.3751 25.5762 20.6251 25.5762 11.0001Z"
                                          stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M19.5794 28.875C19.3325 29.2917 18.9781 29.6376 18.5517 29.8781C18.1253 30.1186 17.6419 30.2451 17.1498 30.2451C16.6577 30.2451 16.1743 30.1186 15.7479 29.8781C15.3215 29.6376 14.9671 29.2917 14.7202 28.875"
                                          stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Нет в наличии
                            </div>
                            <div class="detail_popup absolute z-20 w-full left-0 <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                <?= $is_key_found ? 'subscribed' : '' ?> min_card">
                                <i class="fa fa-bell-o <?= $is_key_found ? 'filled' : '' ?>" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <div id="popup_mess"
                             class="catalog_popup absolute z-20 w-full left-0 <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                         <?= $is_key_found ? 'subscribed' : '' ?>"
                             data-subscription_id="<?= $is_key_found ? $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][$found_key]['ID'] : '' ?>"
                             data-product_id="<?= $item['ID']; ?>">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        $emptyProductProperties = empty($item['PRODUCT_PROPERTIES']);
        if ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET'] && !$emptyProductProperties) { ?>
            <div id="<?= $arItemIDs['BASKET_PROP_DIV']; ?>" style="display: none;">
                <?php
                if (!empty($item['PRODUCT_PROPERTIES_FILL'])) {
                    foreach ($item['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo) {
                        ?>
                        <input type="hidden"
                               name="<?= $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<?= $propID; ?>]"
                               value="<?= htmlspecialcharsbx($propInfo['ID']); ?>">
                        <?php if (isset($item['PRODUCT_PROPERTIES'][$propID]))
                            unset($item['PRODUCT_PROPERTIES'][$propID]);
                    }
                }
                $emptyProductProperties = empty($item['PRODUCT_PROPERTIES']); ?>
            </div>
            <?php

        } else {
            if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']) {
                $canBuy = $item['JS_OFFERS'][$item['OFFERS_SELECTED']]['CAN_BUY'];

                unset($canBuy);
            }
            $boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $item['OFFERS_PROPS_DISPLAY']);
            $boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
            if ($boolShowProductProps || $boolShowOfferProps) { ?>
                <div class="bx_catalog_item_articul">
                    <?php if ($boolShowProductProps) {
                        foreach ($item['DISPLAY_PROPERTIES'] as $arOneProp) {
                            ?><br><strong><?= $arOneProp['NAME']; ?></strong> <?
                            echo(
                            is_array($arOneProp['DISPLAY_VALUE'])
                                ? implode(' / ', $arOneProp['DISPLAY_VALUE'])
                                : $arOneProp['DISPLAY_VALUE']
                            );
                        }
                    }
                    if ($boolShowOfferProps) { ?>
                        <span id="<?= $arItemIDs['DISPLAY_PROP_DIV']; ?>"
                              style="display: none;"></span>
                    <?php } ?>
                </div>
                <?php
            }
            if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']) {
                if (!empty($item['OFFERS_PROP'])) {
                    $arSkuProps = array();
                    if ($item['OFFERS_PROPS_DISPLAY']) {
                        foreach ($item['JS_OFFERS'] as $keyOffer => $arJSOffer) {
                            $strProps = '';
                            if (!empty($arJSOffer['DISPLAY_PROPERTIES'])) {
                                foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp) {
                                    $strProps .= '<br>' . $arOneProp['NAME'] . ' <strong>' . (
                                        is_array($arOneProp['VALUE'])
                                            ? implode(' / ', $arOneProp['VALUE'])
                                            : $arOneProp['VALUE']
                                        ) . '</strong>';
                                }
                            }
                            $item['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
                        }
                    }
                }
            }
        }
        ?>
    </div>
    <div id="result_box"></div>
</div>
