<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Fuser;
use DataBase_like;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

$templateLibrary = array('popup', 'fx');
$currencyList = '';

if (!empty($arResult['CURRENCIES'])) {
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = array(
    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList,
    'ITEM' => array(
        'ID' => $arResult['ID'],
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'OFFERS_SELECTED' => $arResult['OFFERS_SELECTED'],
        'JS_OFFERS' => $arResult['JS_OFFERS']
    )
);
unset($currencyList, $templateLibrary);
$article = $arResult['PROPERTIES']['CML2_TRAITS'];


$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = array(
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
$obName = $templateData['JS_OBJ'] = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];
$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];
$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

$haveOffers = !empty($arResult['OFFERS']);
if ($haveOffers) {
    $actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
        ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
        : reset($arResult['OFFERS']);
    $showSliderControls = false;

    foreach ($arResult['OFFERS'] as $offer) {
        if ($offer['MORE_PHOTO_COUNT'] > 1) {
            $showSliderControls = true;
            break;
        }
    }
} else {
    $actualItem = $arResult;
    $showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}

$skuProps = array();
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;

$showDescription = !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['PRODUCT']['SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

$positionClassMap = array(
    'left' => 'product-item-label-left',
    'center' => 'product-item-label-center',
    'right' => 'product-item-label-right',
    'bottom' => 'product-item-label-bottom',
    'middle' => 'product-item-label-middle',
    'top' => 'product-item-label-top'
);

$discountPositionClass = 'product-item-label-big';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$labelPositionClass = 'product-item-label-big';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}
$certificate = $arResult['PROPERTIES']['CML2_SERT'];
$arPropCertificate = [];
$property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $certificate['IBLOCK_ID'], "CODE" => "CML2_SERT"));
while ($enum_fields = $property_enums->GetNext()) {
    $arPropCertificate['VALUE'][] = $enum_fields['VALUE'];
    $arPropCertificate['XML_ID'][] = $enum_fields['XML_ID'];
}
$item_id = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);

$item_id[] = $arResult['ID'];
$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id,$FUser_id);
foreach($count_likes['ALL_LIKE'] as $keyLike => $count) {
    $arResult['COUNT_LIKES'] = $count;
}
foreach ($count_likes['USER'] as $keyLike => $count) {
    $arResult['COUNT_LIKE'] = $count['Like'][0];
    $arResult['COUNT_FAV'] = $count['Fav'][0];
}
$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';
?>
    <div class="bx-catalog-element">
        <div class="box_with_photo_product row">
            <div class="col-md-5 col-sm-5 col-lg-5 col-12">
                <div class="product-item-detail-slider-container"
                <span class="product-item-detail-slider-close" data-entity="close-popup"></span>
                <div class="product-item-detail-slider-block<?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' :
                    '') ?>" data-entity="images-slider-block">
                    <div>
                            <span class="product-item-detail-slider-left carousel_elem_custom"
                                  data-entity="slider-control-left"
                                  style="display: none;"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                        <span class="product-item-detail-slider-right carousel_elem_custom"
                              data-entity="slider-control-right"
                              style="display: none;"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                        <div class="product-item-detail-slider-images-container" data-entity="images-container">
                            <?
                            if (!empty($actualItem['MORE_PHOTO'])) {
                                foreach ($actualItem['MORE_PHOTO'] as $key => $photo) {
                                    ?>
                                    <div class="product-item-detail-slider-image<?= ($key == 0 ? ' active' : '') ?>"
                                         data-entity="image" data-id="<?= $photo['ID'] ?>">
                                        <img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
                                             title="<?= $title ?>"<?= ($key == 0 ? ' itemprop="image"' : '') ?>>
                                    </div>
                                    <?
                                }
                            }

                            if ($arParams['SLIDER_PROGRESS'] === 'Y') {
                                ?>
                                <div class="product-item-detail-slider-progress-bar"
                                     data-entity="slider-progress-bar"
                                     style="width: 0;"></div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                    <div class="box_with_net">
                        <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                            'templates',
                            array(
                                'ID_PROD' => $arResult['ID'],
                                'F_USER_ID' => $FUser_id,
                                'LOOK_LIKE' => true,
                                'LOOK_FAVORITE' => true,
                                'COUNT_LIKE' => $arResult['COUNT_LIKE'],
                                'COUNT_FAV' => $arResult['COUNT_FAV'],
                                'COUNT_LIKES' => $arResult['COUNT_LIKES'],
                            )
                            ,
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        ); ?>
                        <a href="#" title="Поделиться"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></a>
                    </div>
                </div>

                <? if ($showSliderControls) {
                    if ($haveOffers) {
                        foreach ($arResult['OFFERS'] as $keyOffer => $offer) {
                            if (!isset($offer['MORE_PHOTO_COUNT']) || $offer['MORE_PHOTO_COUNT'] <= 0)
                                continue;

                            $strVisible = $arResult['OFFERS_SELECTED'] == $keyOffer ? '' : 'none';
                            ?>
                            <div class="product-item-detail-slider-controls-block"
                                 id="<?= $itemIds['SLIDER_CONT_OF_ID'] . $offer['ID'] ?>"
                                 style="display: <?= $strVisible ?>;">
                                <?
                                foreach ($offer['MORE_PHOTO'] as $keyPhoto => $photo) {
                                    ?>
                                    <div class="product-item-detail-slider-controls-image<?= ($keyPhoto == 0 ? ' active' : '') ?>"
                                         data-entity="slider-control"
                                         data-value="<?= $offer['ID'] . '_' . $photo['ID'] ?>">
                                        <img src="<?= $photo['SRC'] ?>">
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                            <?
                        }
                    } else {
                        ?>
                        <div class="product-item-detail-slider-controls-block"
                             id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
                            <?
                            if (!empty($actualItem['MORE_PHOTO'])) {
                                foreach ($actualItem['MORE_PHOTO'] as $key => $photo) {
                                    ?>
                                    <div class="product-item-detail-slider-controls-image<?= ($key == 0 ? ' active' : '') ?>"
                                         data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
                                        <img src="<?= $photo['SRC'] ?>">
                                    </div>
                                    <?
                                }
                            }
                            ?>
                        </div>
                        <?
                    }
                }
                ?>
            </div>
        </div>
        <?
        $showOffersBlock = $haveOffers && !empty($arResult['OFFERS_PROP']);
        $mainBlockProperties = array_intersect_key($arResult['DISPLAY_PROPERTIES'], $arParams['MAIN_BLOCK_PROPERTY_CODE']);
        $showPropsBlock = !empty($mainBlockProperties) || $arResult['SHOW_OFFERS_PROPS'];
        $showBlockWithOffersAndProps = $showOffersBlock || $showPropsBlock;
        ?>
        <div class="col-md-7 col-sm-7 col-lg-7 col-12">
            <div class="mb-3">
                <div class="mb-4">
                    <div class="variation_taste">
                        <?php
                        foreach ($arPropCertificate['VALUE'] as $key => $nameCertificate) {
                            foreach ($arPropCertificate['XML_ID'] as $keys => $value) {
                                if ($certificate['VALUE_XML_ID'][0] === $value) {
                                    $classActive = 'link_gram_active';
                                } else {
                                    $classActive = '';
                                }
                                if ($key === $keys) {
                                    ?>
                                    <span class="certificate_variations <?= $classActive ?>"
                                          id="<?php echo $value ?>">
                                        <?php echo $nameCertificate ?>
                                        </span>
                                <?php }
                            }
                        } ?>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <div class="form-check d-flex flex-row">
                        <div class="mr-5">
                            <input type="radio" class="form-check-input  mr-1 check_custom" value="yes" id="yes"
                                   name="checked" checked>
                            <label class="orderCancel" for="yes">Онлайн-формат</label>
                        </div>
                        <div class="mr-5">
                            <input type="radio" id="no" class="form-check-input  mr-1 check_custom" value="no"
                                   name="checked">
                            <label class="orderCancel" for="no">Физическая карта</label>
                        </div>
                    </div>
                </div>
                <div class="form-group  mb-3">
                    <label class="orderCancel" for="main-profile-name"><b>Имя
                            отправителя</b></label>
                    <input class="form-control input_lk" type="text" name="NAME" maxlength="50"
                           id="main-profile-name" value="">
                </div>
                <div class="form-group mb-3">
                    <div class="form-check d-flex flex-row">
                        <div class="mr-5">
                            <input type="radio" class="form-check-input  mr-1 check_custom" value="yes" id="yes"
                                   name="check" checked>
                            <label class="orderCancel" for="yes"><b>Указывать имя отправителя</b></label>
                        </div>
                    </div>
                </div>
                <div class="form-group  mb-3">
                    <label class="orderCancel" for="main-profile-name"><b>Имя
                            получателя</b></label>
                    <input class="form-control input_lk" type="text" name="NAME" maxlength="50"
                           id="main-profile-name" value="">
                </div>
                <div class="form-group mb-3 d-flex flex-column">
                    <label class="orderCancel"><b>Способ
                            доставки</b></label>
                    <div class="form-check d-flex flex-row">
                        <div class="mr-5">
                            <input type="radio" class="form-check-input  mr-1 check_custom" value="" id=""
                                   name="checkMessanger" checked>
                            <label class="orderCancel" for="yes">Телеграм</label>
                        </div>
                        <div class="mr-5">
                            <input type="radio" id="" class="form-check-input  mr-1 check_custom" value=""
                                   name="checkMessanger">
                            <label class="orderCancel" for="no">WhatsApp</label>
                        </div>
                        <div class="mr-5">
                            <input type="radio" id="" class="form-check-input  mr-1 check_custom" value=""
                                   name="checkMessanger">
                            <label class="orderCancel" for="no">Cмс</label>
                        </div>
                        <div class="mr-5">
                            <input type="radio" id="" class="form-check-input  mr-1 check_custom" value=""
                                   name="checkMessanger">
                            <label class="orderCancel" for="no">Почта</label>
                        </div>
                    </div>
                </div>
                <div class="form-group d-flex flex-column  mb-3">
                    <label class="orderCancel" for="main-profile-name"><b>Дата и время доставки</b></label>
                    <div class="d-flex flex-row align-items-center">
                        <input class="form-control input_lk" type="date" name="date" maxlength="50" id="" value="">
                        <span class="mr-2 ml-2">—</span>
                        <input class="form-control input_lk" type="time" name="date" maxlength="50" id="" value="">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <div class="form-check d-flex flex-row">
                        <div class="mr-5">
                            <input type="radio" class="form-check-input  mr-1 check_custom" value="afterPay" id="afterPay"
                                   name="afterPay" checked>
                            <label class="orderCancel" for="afterPay"><b>Сразу после оплаты</b></label>
                        </div>
                    </div>
                </div>
                <div class="form-group  mb-3">
                    <label class="orderCancel" for="main-profile-name"><b>Сообщение для получателя</b></label>
                    <textarea class="form-control input_lk" name="comment" maxlength="50"
                              id=""></textarea>
                </div>
                <div class="form-group d-flex flex-row  mb-3">
                    <div class="form-check mr-3">
                        <input type="checkbox" value="Y" name="" id="" class="check_input form-check-input">
                    </div>
                    <label class="orderCancel" for="main-profile-name">Сообщить мне, когда сертификат будет доставлен</label>
                </div>
            </div>

            <? foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName) {

                switch ($blockName) {
                    case 'price':
                        ?>
                        <div class=" d-flex flex-row align-items-center">
                            <div class="product-item-detail-price-current"
                                 id="<?= $itemIds['PRICE_ID'] ?>">
                            </div>
                        </div>

                        <?
                        break;
                    case 'quantityLimit':
                        if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
                            if ($haveOffers) {
                                ?>
                                <div class="mb-3" id="<?= $itemIds['QUANTITY_LIMIT'] ?>"
                                     style="display: none;">
                                        <span class="product-item-quantity"
                                              data-entity="quantity-limit-value"></span>
                                </div>
                                <?
                            } else {
                                if (
                                    $measureRatio
                                    && (float)$actualItem['PRODUCT']['QUANTITY'] > 0
                                    && $actualItem['CHECK_QUANTITY']
                                ) {
                                    ?>
                                    <div class="mb-3 text-center"
                                         id="<?= $itemIds['QUANTITY_LIMIT'] ?>">
                                        <span class="product-item-detail-info-container-title"><?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:</span>
                                        <span class="product-item-quantity"
                                              data-entity="quantity-limit-value">
													<?
                                                    if ($arParams['SHOW_MAX_QUANTITY'] === 'M') {
                                                        if ((float)$actualItem['PRODUCT']['QUANTITY'] / $measureRatio >= $arParams['RELATIVE_QUANTITY_FACTOR']) {
                                                            echo $arParams['MESS_RELATIVE_QUANTITY_MANY'];
                                                        } else {
                                                            echo $arParams['MESS_RELATIVE_QUANTITY_FEW'];
                                                        }
                                                    } else {
                                                        echo $actualItem['PRODUCT']['QUANTITY'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'];
                                                    }
                                                    ?>
												</span>
                                    </div>
                                    <?
                                }
                            }
                        }

                        break;

                    case 'quantity':
                        if ($arParams['USE_PRODUCT_QUANTITY']) {
                            if ($actualItem['PRODUCT']['QUANTITY'] != '0') {
                                ?>
                                <div class="mb-5 d-flex flex-row align-items-center bx_catalog_item" <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                     data-entity="quantity-block">
                                    <a id="<? echo $arResult['BUY_LINK']; ?>" href="javascript:void(0)"
                                       rel="nofollow" class="btn_basket add2basket basket_prod_detail"
                                       data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                       data-product_id="<?= $arResult['ID']; ?>"
                                       title="Добавить в корзину">Забронировать</a>
                                    <div id="result_box"></div>
                                    <div id="popup_mess"></div>
                                </div>
                            <? } else { ?>
                                <div class="mb-5 d-flex flex-row align-items-center justify-content-between  bx_catalog_item" <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                     data-entity="quantity-block">
                                    <div class="d-flex flex-row align-items-center">
                                        <a id="<? echo $arResult['BUY_LINK']; ?>" href="javascript:void(0)"
                                           rel="nofollow"
                                           class="add2basket basket_prod_detail detail_popup detail_disabled"
                                           data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                           data-product_id="<?= $arResult['ID']; ?>"
                                           title="Добавить в корзину">Забронировать</a>
                                    </div>
                                    <div id="popup_mess"></div>
                                    <div id="result_box" style="width: 100%;position: absolute;"></div>
                                </div>
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div class="not_product detail_popup">Нет в наличии</div>
                                </div>
                                <?
                            }
                        }

                        break;
                }
            }
            ?>

        </div>

    </div>
<?
if ($haveOffers) {
    $offerIds = array();
    $offerCodes = array();

    $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

    foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer) {
        $offerIds[] = (int)$jsOffer['ID'];
        $offerCodes[] = $jsOffer['CODE'];

        $fullOffer = $arResult['OFFERS'][$ind];
        $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

        $strAllProps = '';
        $strMainProps = '';
        $strPriceRangesRatio = '';
        $strPriceRanges = '';

        if ($arResult['SHOW_OFFERS_PROPS']) {
            if (!empty($jsOffer['DISPLAY_PROPERTIES'])) {
                foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property) {
                    $current = '<li class="product-item-detail-properties-item">
					<span class="product-item-detail-properties-name">' . $property['NAME'] . '</span>
					<span class="product-item-detail-properties-dots"></span>
					<span class="product-item-detail-properties-value">' . (
                        is_array($property['VALUE'])
                            ? implode(' / ', $property['VALUE'])
                            : $property['VALUE']
                        ) . '</span></li>';
                    $strAllProps .= $current;

                    if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']])) {
                        $strMainProps .= $current;
                    }
                }

                unset($current);
            }
        }

        if ($arParams['USE_PRICE_COUNT'] && count($jsOffer['ITEM_QUANTITY_RANGES']) > 1) {
            $strPriceRangesRatio = '(' . Loc::getMessage(
                    'CT_BCE_CATALOG_RATIO_PRICE',
                    array('#RATIO#' => ($useRatio
                            ? $fullOffer['ITEM_MEASURE_RATIOS'][$fullOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']
                            : '1'
                        ) . ' ' . $measureName)
                ) . ')';

            foreach ($jsOffer['ITEM_QUANTITY_RANGES'] as $range) {
                if ($range['HASH'] !== 'ZERO-INF') {
                    $itemPrice = false;

                    foreach ($jsOffer['ITEM_PRICES'] as $itemPrice) {
                        if ($itemPrice['QUANTITY_HASH'] === $range['HASH']) {
                            break;
                        }
                    }

                    if ($itemPrice) {
                        $strPriceRanges .= '<dt>' . Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_FROM',
                                array('#FROM#' => $range['SORT_FROM'] . ' ' . $measureName)
                            ) . ' ';

                        if (is_infinite($range['SORT_TO'])) {
                            $strPriceRanges .= Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                        } else {
                            $strPriceRanges .= Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_TO',
                                array('#TO#' => $range['SORT_TO'] . ' ' . $measureName)
                            );
                        }

                        $strPriceRanges .= '</dt><dd>' . ($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE']) . '</dd>';
                    }
                }
            }

            unset($range, $itemPrice);
        }

        $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
        $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
        $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
        $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
    }

    $templateData['OFFER_IDS'] = $offerIds;
    $templateData['OFFER_CODES'] = $offerCodes;
    unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => true,
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'SHOW_SKU_PROPS' => $arResult['SHOW_OFFERS_PROPS'],
            'OFFER_GROUP' => $arResult['OFFER_GROUP'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'VISUAL' => $itemIds,
        'DEFAULT_PICTURE' => array(
            'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
            'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
        ),
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'NAME' => $arResult['~NAME'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'BASKET_URL' => $arParams['BASKET_URL'],
            'SKU_PROPS' => $arResult['OFFERS_PROP_CODES'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        ),
        'OFFERS' => $arResult['JS_OFFERS'],
        'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
        'TREE_PROPS' => $skuProps
    );
} else {
    $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
    if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !$emptyProductProperties) {
        ?>
        <div id="<?= $itemIds['BASKET_PROP_DIV'] ?>" style="display: none;">
            <?
            if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])) {
                foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propId => $propInfo) {
                    ?>
                    <input type="hidden" name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
                           value="<?= htmlspecialcharsbx($propInfo['ID']) ?>">
                    <?
                    unset($arResult['PRODUCT_PROPERTIES'][$propId]);
                }
            }

            $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
            if (!$emptyProductProperties) {
                ?>
                <table>
                    <?
                    foreach ($arResult['PRODUCT_PROPERTIES'] as $propId => $propInfo) {
                        ?>
                        <tr>
                            <td><?= $arResult['PROPERTIES'][$propId]['NAME'] ?></td>
                            <td>
                                <?
                                if (
                                    $arResult['PROPERTIES'][$propId]['PROPERTY_TYPE'] === 'L'
                                    && $arResult['PROPERTIES'][$propId]['LIST_TYPE'] === 'C'
                                ) {
                                    foreach ($propInfo['VALUES'] as $valueId => $value) {
                                        ?>
                                        <label>
                                            <input type="radio"
                                                   name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
                                                   value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"checked"' : '') ?>>
                                            <?= $value ?>
                                        </label>
                                        <br>
                                        <?
                                    }
                                } else {
                                    ?>
                                    <select name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]">
                                        <?
                                        foreach ($propInfo['VALUES'] as $valueId => $value) {
                                            ?>
                                            <option value="<?= $valueId ?>" <?= ($valueId == $propInfo['SELECTED'] ? '"selected"' : '') ?>>
                                                <?= $value ?>
                                            </option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                    <?
                                }
                                ?>
                            </td>
                        </tr>
                        <?
                    }
                    ?>
                </table>
                <?
            }
            ?>
        </div>
        <?
    }

    $jsParams = array(
        'CONFIG' => array(
            'USE_CATALOG' => $arResult['CATALOG'],
            'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
            'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
            'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS' => true,
            'USE_SUBSCRIBE' => $showSubscribe,
            'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
            'ALT' => $alt,
            'TITLE' => $title,
            'MAGNIFIER_ZOOM_PERCENT' => 200,
            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
        ),
        'VISUAL' => $itemIds,
        'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
        'PRODUCT' => array(
            'ID' => $arResult['ID'],
            'ACTIVE' => $arResult['ACTIVE'],
            'PICT' => reset($arResult['MORE_PHOTO']),
            'NAME' => $arResult['~NAME'],
            'SUBSCRIPTION' => true,
            'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
            'ITEM_PRICES' => $arResult['ITEM_PRICES'],
            'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
            'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
            'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
            'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
            'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
            'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
            'SLIDER' => $arResult['MORE_PHOTO'],
            'CAN_BUY' => $arResult['CAN_BUY'],
            'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
            'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
            'MAX_QUANTITY' => $arResult['PRODUCT']['QUANTITY'],
            'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
            'CATEGORY' => $arResult['CATEGORY_PATH']
        ),
        'BASKET' => array(
            'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
            'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
            'EMPTY_PROPS' => $emptyProductProperties,
            'BASKET_URL' => $arParams['BASKET_URL'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
        )
    );
    unset($emptyProductProperties);
}

if ($arParams['DISPLAY_COMPARE']) {
    $jsParams['COMPARE'] = array(
        'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
        'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
        'COMPARE_PATH' => $arParams['COMPARE_PATH']
    );
}
?>
    </div>
    <script>
        BX.message({
            ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
            TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
            TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
            BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
            BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
            BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
            BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
            BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
            TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
            COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
            COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
            COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
            BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
            PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
            PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
            RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
            RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
            SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
        });

        var <?=$obName?> = new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
    </script>
<?
unset($actualItem, $itemIds, $jsParams);
