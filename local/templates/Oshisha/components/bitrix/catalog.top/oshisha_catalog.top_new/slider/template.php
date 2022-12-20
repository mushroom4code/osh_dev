<?php

use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use DataBase_like;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var array $item */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
/** @var array $skuTemplate */

/** @var array $templateData */
Loader::includeModule('sale');
Loader::includeModule('catalog');


$this->setFrameMode(true);

$intRowsCount = count($arResult['ITEMS']);
$strRand = $this->randString();
$strContID = 'cat_top_cont_' . $strRand;
$item_id = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
foreach ($arResult['ITEMS'] as $item => $arOneRow) {
    foreach ($arOneRow as $keyItem => $arItem) {
        $item_id[] = $arItem['ID'];
    }
}


$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);

?>
<div id="<?= $strContID; ?>"
     class="bx_catalog_tile_home_type_2 col2 <?= $templateData['TEMPLATE_CLASS']; ?>">
    <div class="bx_catalog_tile_section" data-init="<?= count($arResult['ITEMS']) ?>">
        <?php
        $boolFirst = true;
        $arRowIDs = array();
        foreach ($arResult['ITEMS'] as $keyRow => $arOneRow) {
        $strRowID = 'cat-top-' . $keyRow . '_' . $strRand;
        $arRowIDs[] = $strRowID;

        foreach ($arOneRow

        as $keyItem => $arItem) {
        $arItem['COUNT_FAV'] = '';
        $arItem['COUNT_LIKES'] = '';
        $arItem['COUNT_LIKE'] = '';
        foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
            if ($keyLike == $arItem['ID']) {
                $arItem['COUNT_LIKES'] = $count;
            }
        }
        foreach ($count_likes['USER'] as $keyLike => $count) {
            if ($keyLike == $arItem['ID']) {
                $arItem['COUNT_LIKE'] = $count['Like'][0];
                $arItem['COUNT_FAV'] = $count['Fav'][0];
            }
        }
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
        $strMainID = $this->GetEditAreaId($arItem['ID']);

        $priceBasket = 0;
        foreach ($arItem['ACTUAL_BASKET'] as $key => $val) {
            if ($key == $arItem['ID']) {
                $priceBasket = $val;
            }
        }

        $arItemIDs = array(
            'ID' => $strMainID,
            'PICT' => $strMainID . '_pict',
            'SECOND_PICT' => $strMainID . '_secondpict',
            'MAIN_PROPS' => $strMainID . '_main_props',

            'QUANTITY' => $strMainID . '_quantity',
            'QUANTITY_DOWN' => $strMainID . '_quant_down',
            'QUANTITY_UP' => $strMainID . '_quant_up',
            'QUANTITY_MEASURE' => $strMainID . '_quant_measure',
            'BUY_LINK' => $strMainID . '_buy_link',
            'BASKET_ACTIONS' => $strMainID . '_basket_actions',
            'NOT_AVAILABLE_MESS' => $strMainID . '_not_avail',
            'SUBSCRIBE_LINK' => $strMainID . '_subscribe',
            'COMPARE_LINK' => $strMainID . '_compare_link',

            'DSC_PERC' => $strMainID . '_dsc_perc',
            'SECOND_DSC_PERC' => $strMainID . '_second_dsc_perc',

            'PROP_DIV' => $strMainID . '_sku_tree',
            'PROP' => $strMainID . '_prop_',
            'DISPLAY_PROP_DIV' => $strMainID . '_sku_prop',
            'BASKET_PROP_DIV' => $strMainID . '_basket_prop'
        );

        $strObName = 'ob' . preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);
        $productTitle = (
        isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
            ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
            : $arItem['NAME']
        );


        $minPrice = false;
        if (isset($arItem['MIN_PRICE']) || isset($arItem['RATIO_PRICE']))
            $minPrice = (isset($arItem['RATIO_PRICE']) ? $arItem['RATIO_PRICE'] : $arItem['MIN_PRICE']);

        $rsElement = CIBlockElement::GetList(array(), array('ID' => $arItem['ID']), false,
            false, array('ID', 'IBLOCK_SECTION_ID'));
        $arElement = $rsElement->Fetch();
        $newStrPrice = '';
        $priceProduct = [];
        $price = [];
        $useDiscount = $arItem['PROPERTIES']['USE_DISCOUNT'];
        foreach ($arItem['ITEM_ALL_PRICES'] as $key => $PRICE) {

            foreach ($PRICE['PRICES'] as $price_key => $price_val) {


                if (USE_CUSTOM_SALE_PRICE || $useDiscount['VALUE_XML_ID'] == 'true') {
                    if ($price_key == SALE_PRICE_TYPE_ID) {
                        $price['SALE_PRICE'] = $price_val;
                    }
                    if ((int)$price_val['PRICE_TYPE_ID'] === BASIC_PRICE) {
                        $price['PRICE_DATA'][1] = $price_val;
                        $price['PRICE_DATA'][1]['NAME'] = 'Основная (до 30к)';
                    }
                }

                if ((int)$price_val['PRICE_TYPE_ID'] === RETAIL_PRICE) {
                    $price['PRICE_DATA'][0] = $price_val;
                    $price['PRICE_DATA'][0]['NAME'] = 'Розничная (до 10к)';
                } else if ((int)$price_val['PRICE_TYPE_ID'] === BASIC_PRICE) {
                    $price['PRICE_DATA'][1] = $price_val;
                    $price['PRICE_DATA'][1]['NAME'] = 'Основная (до 30к)';
                } elseif ((int)$price_val['PRICE_TYPE_ID'] === B2B_PRICE) {
                    $price['PRICE_DATA'][2] = $price_val;
                    $price['PRICE_DATA'][2]['NAME'] = 'b2b (от 30к)';
                }
                ksort($price['PRICE_DATA']);
            }
        }

        $taste = $arItem['PROPERTIES']['VKUS'];
        $catalog = CIBlockSection::GetList(array(), array('ID' => $arElement['IBLOCK_SECTION_ID'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']),
            false, array('*', 'UF_*'));
        $catalogProduct = $catalog->Fetch();

        ?>
        <div class="<?= ($arItem['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>">
            <div class="bx_catalog_item_container product-item <? if ($catalogProduct['UF_HIDE_PRICE'] == 1 && !$USER->IsAuthorized()): ?>blur_photo<? endif; ?>"
                 id="<?= $strMainID; ?>">
                <div>
                    <div class="variation_taste">
                        <?php foreach ($taste['VALUE'] as $key => $name) {
                            foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
                                if ($key === $keys) {
                                    $color = explode('#', $value); ?>
                                    <span class="taste" data-background="<?= '#' . $color[1] ?>"
                                          id="<?php echo $color[0] ?>">
                                    <?php echo $name ?>
                            </span>
                                <?php }
                                continue;
                            }

                        } ?>

                    </div>
                </div>
                <div>
                    <div class="image_cart">
                        <?php if (!empty($arItem['PREVIEW_PICTURE']['SRC'])) { ?>
                            <a id="<?= $arItemIDs['PICT']; ?>"
                               href="/catalog/<?= $catalogProduct['CODE'] . '/' . $arItem['CODE']; ?>/">
                                <img src="<?= $arItem['PREVIEW_PICTURE']['SRC']; ?>"
                                     id="<?= $arItemIDs['PICT']; ?>"/>
                            </a>
                        <?php } else { ?>
                            <a id="<?= $arItemIDs['PICT']; ?>"
                               href="/catalog/<?= $catalogProduct['CODE'] . '/' . $arItem['CODE']; ?>/">
                                <img src="/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/images/no_photo.png"
                                     id="<?= $arItemIDs['PICT']; ?>"/>
                            </a>
                        <?php } ?>
                    </div>
                    <?php if (!empty($price['PRICE_DATA'][1]['PRINT_PRICE'])) { ?>
                        <div class="bx_catalog_item_price">
                            <div class="box_with_titles">
                                <div class="box_with_price d-flex flex-column">
                                    <div class="d-flex flex-row align-items-center">
                                        <?php
                                        $sale = false;
                                        if (!empty($price['SALE_PRICE'])) {
                                            $price_new = $price['SALE_PRICE']['PRINT_PRICE'];
                                            $price_id = $price['SALE_PRICE']['PRICE_TYPE_ID'];
                                            $sale = true;
                                        } else {
                                            $price_new = $price['PRICE_DATA'][1]['PRINT_PRICE'];
                                            $price_id = $price['PRICE_DATA'][1]['PRICE_TYPE_ID'];
                                        } ?>
                                        <div class="bx_price" id="<?= $price_id ?>">
                                            <?= $price_new ?>
                                        </div>
                                        <?php if (!$sale) { ?>
                                            <div class="info-prices-box-hover cursor-pointer ml-2">
                                                <i class="fa fa-info-circle info-price" aria-hidden="true"></i>
                                                <div class="position-absolute d-hide">
                                                    <div class="d-flex flex-column prices-block">
                                                        <?php foreach ($price['PRICE_DATA'] as $items) { ?>
                                                            <p class="mb-1">
                                                                <span class="font-11 mb-2"><?= $items['NAME'] ?></span><br>
                                                                <span class="font-12"><b><?= $items['PRINT_PRICE'] ?></b></span>
                                                            </p>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <?php if (!empty($price['SALE_PRICE'])) { ?>
                                        <div class="after_price">
                                            Старая цена: <?= $price['PRICE_DATA'][1]['PRINT_PRICE'] ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                    'templates',
                                    array(
                                        'ID_PROD' => $arItem['ID'],
                                        'F_USER_ID' => $FUser_id,
                                        'LOOK_LIKE' => true,
                                        'LOOK_FAVORITE' => false,
                                        'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                        'COUNT_FAV' => $arItem['COUNT_FAV'],
                                        'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                                    )
                                    ,
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                ); ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="box_with_titles">
                            <div class="not_product">
                                Товара нет в наличии
                            </div>
                            <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                'templates',
                                array(
                                    'ID_PROD' => $arItem['ID'],
                                    'F_USER_ID' => $FUser_id,
                                    'LOOK_LIKE' => true,
                                    'LOOK_FAVORITE' => false,
                                    'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                    'COUNT_FAV' => $arItem['COUNT_FAV'],
                                    'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                                )
                                ,
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            ); ?>
                        </div>
                    <?php } ?>
                    <div class="box_with_title_like">
                        <div class="box_with_text">
                            <a class="bx_catalog_item_title"
                               href="/catalog/<?= $catalogProduct['CODE'] . '/' . $arItem['CODE']; ?>/"
                               title="<?= $productTitle; ?>">
                                <?= $productTitle; ?>
                            </a>
                        </div>
                    </div>

                    <?php
                    $showSubscribeBtn = false;
                    $compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCT_TPL_MESS_BTN_COMPARE'));
                    if (!isset($arItem['OFFERS']) || empty($arItem['OFFERS'])) { ?>
                    <div class="bx_catalog_item_controls">
                        <?php if ($arItem['PRODUCT']['QUANTITY'] !== '0') { ?>

                        <div class="box_with_fav_bask">
                            <div class="btn red_button_cart btn-plus add2basket"
                                 data-url="<?= $arItem['DETAIL_PAGE_URL'] ?>"
                                 data-product_id="<?= $arItem['ID']; ?>"
                                 data-max-quantity="<?= $arItem['PRODUCT']['QUANTITY'] ?>"
                                 id="<?= $arItemIDs['BUY_LINK']; ?>"
                                 <? if ($priceBasket > 0): ?>style="display:none;"<? endif; ?>>В корзину
                            </div>
                            <div class="product-item-amount-field-contain-wrap"
                                 <? if ($priceBasket > 0): ?>style="display:block;"<? endif; ?>
                                 data-product_id="<?= $arItem['ID']; ?>">

                                <div class="product-item-amount-field-contain d-flex flex-row align-items-center">
                                    <a class="btn-minus  minus_icon no-select add2basket"
                                       id="<?= $arItemIDs['BUY_LINK']; ?>"
                                       href="javascript:void(0)" data-url="<?= $item['DETAIL_PAGE_URL'] ?>"
                                       data-product_id="<?= $arItem['ID']; ?>">
                                        <span class="minus_icon"></span>
                                    </a>
                                    <div class="product-item-amount-field-block">
                                        <input class="product-item-amount card_element"
                                               id="<?= $arItemIDs['QUANTITY_ID'] ?>" type="number"
                                               value="<?= $priceBasket ?>">
                                    </div>

                                    <a class="btn-plus plus_icon no-select add2basket" data-max-quantity="
                                                <?= $arItem['PRODUCT']['QUANTITY'] ?>"
                                       id="<?= $arItemIDs['BUY_LINK']; ?>"
                                       href="javascript:void(0)" data-url="<?= $arItem['DETAIL_PAGE_URL'] ?>"
                                       data-product_id="<?= $arItem['ID']; ?>"
                                       title="Добавить в корзину"></a>
                                </div>
                            </div>
                            <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                'templates',
                                array(
                                    'ID_PROD' => $arItem['ID'],
                                    'F_USER_ID' => $FUser_id,
                                    'LOOK_LIKE' => false,
                                    'LOOK_FAVORITE' => true,
                                    'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                    'COUNT_FAV' => $arItem['COUNT_FAV'],
                                    'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                                )
                                ,
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            ); ?>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                    <?
                    } else { ?>
                    <div id="<?= $arItemIDs['NOT_AVAILABLE_MESS']; ?>"
                    <div class="box_with_fav_bask">
                        <div class="not_product detail_popup">
                            Нет в наличии
                        </div>
                        <div class="detail_popup min_card"><i class="fa fa-bell-o"
                                                              aria-hidden="true"></i></div>
                        <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                            'templates',
                            array(
                                'ID_PROD' => $arItem['ID'],
                                'F_USER_ID' => $FUser_id,
                                'LOOK_LIKE' => false,
                                'LOOK_FAVORITE' => true,
                                'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                'COUNT_FAV' => $arItem['COUNT_FAV'],
                                'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                            )
                            ,
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        ); ?>
                    </div>
                    <div id="popup_mess"></div>
                    <div style="clear: both;"></div>
                </div>
                <?php }
                if ($arParams['DISPLAY_COMPARE']) {
                    $arJSParams['COMPARE'] = array(
                        'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
                        'COMPARE_PATH' => $arParams['COMPARE_PATH']
                    );
                }
                }
                else {
                    if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']) {
                        $canBuy = $arItem['JS_OFFERS'][$arItem['OFFERS_SELECTED']]['CAN_BUY'];

                        unset($canBuy);
                    }
                    $boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $arItem['OFFERS_PROPS_DISPLAY']);
                    $boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
                    if ($boolShowProductProps || $boolShowOfferProps) { ?>
                        <div class="bx_catalog_item_articul">
                            <?php if ($boolShowProductProps) {
                                foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp) {
                                    ?><br><strong><? echo $arOneProp['NAME']; ?></strong> <?
                                    echo(
                                    is_array($arOneProp['DISPLAY_VALUE'])
                                        ? implode(' / ', $arOneProp['DISPLAY_VALUE'])
                                        : $arOneProp['DISPLAY_VALUE']
                                    );
                                }
                            }
                            if ($boolShowOfferProps) { ?>
                                <span id="<?php $arItemIDs['DISPLAY_PROP_DIV']; ?>"
                                      style="display: none;"></span>
                            <?php } ?>
                        </div>
                        <?php
                    }
                    if ('Y' == $arParams['PRODUCT_DISPLAY_MODE']) {
                        if (!empty($arItem['OFFERS_PROP'])) {
                            $arSkuProps = array();

                            if ($arItem['OFFERS_PROPS_DISPLAY']) {
                                foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer) {
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
                                    $arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
                                }
                            }

                            if ($arParams['DISPLAY_COMPARE']) {
                                $arJSParams['COMPARE'] = array(
                                    'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
                                    'COMPARE_PATH' => $arParams['COMPARE_PATH']
                                );
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
        <div id="result_box"></div>
    </div>
    <?php }

    $boolFirst = false;
    } ?>
</div>
<?php
if (1 < $intRowsCount) {
    $arJSParams = array(
        'cont' => $strContID,
        'left' => array(
            'id' => $strContID . '_left_arr',
            'className' => 'bx_catalog_tile_slider_arrow_left',
            'classNameIcon' => 'fa fa-angle-left',
        ),
        'right' => array(
            'id' => $strContID . '_right_arr',
            'className' => 'bx_catalog_tile_slider_arrow_right',
            'classNameIcon' => 'fa fa-angle-right',
        ),
        'rows' => $arRowIDs,
        'rotate' => (0 < $arParams['ROTATE_TIMER']),
        'rotateTimer' => $arParams['ROTATE_TIMER']
    );
    if ('Y' == $arParams['SHOW_PAGINATION']) {
        $arJSParams['pagination'] = array(
            'id' => $strContID . '_pagination',
            'className' => 'bx_catalog_tile_slider_pagination'
        );
    }

} ?>
</div>
