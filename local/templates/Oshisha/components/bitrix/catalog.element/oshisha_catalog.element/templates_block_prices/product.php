<div class="col-md-6 col-sm-6 col-lg-6 product_left col-12">
    <div class="product-item-detail-slider-container <?php if (!empty($taste['VALUE'])) { ?> p-lg-md-25
                    <?php } ?>" id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
        <div class="variation_taste" style="max-width: 10%; height: 90%">
            <?php foreach ($taste['VALUE'] as $key => $nameTaste) {
                foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
                    if ($key === $keys) {
                        $color = explode('#', $value); ?>
                        <span class="taste" data-background="<?= '#' . $color[1] ?>"
                              id="<?= $color[0] ?>">
                                    <?= $nameTaste ?>
                                    </span>
                    <?php }
                }
            } ?>
        </div>
        <div class="product-item-detail-slider-block
                    <?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '') ?>"
             data-entity="images-slider-block">
            <div>
                            <span class="product-item-detail-slider-left carousel_elem_custom"
                                  data-entity="slider-control-left"
                                  style="display: none;"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                <span class="product-item-detail-slider-right carousel_elem_custom"
                      data-entity="slider-control-right"
                      style="display: none;"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                <div class="product-item-detail-slider-images-container" data-entity="images-container">
                    <?php if (!empty($actualItem['PICTURE'][0]['SRC'])) {
                        foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
                            <div class="product-item-detail-slider-image<?= ($key == 0 ? ' active' : '') ?>"
                                 data-entity="image" data-id="<?= $photo['ID'] ?>">
                                <img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
                                     title="<?= $title ?>"<?= ($key == 0 ? ' itemprop="image"' : '') ?>>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="product-item-detail-slider-image active" data-entity="image"
                             data-id="1">
                            <img src="/local/templates/Oshisha/images/no-photo.gif" itemprop="image">
                        </div>
                        <?
                    }
                    if ($arParams['SLIDER_PROGRESS'] === 'Y') { ?>
                        <div class="product-item-detail-slider-progress-bar"
                             data-entity="slider-progress-bar"
                             style="width: 0;"></div>
                    <?php } ?>
                </div>
            </div>
            <div class="box_with_net" <?php if (empty($taste['VALUE'])){ ?>style="padding: 20px;"<?php } ?>>
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
                <a href="#" class="delligate shared" title="Поделиться"
                   data-element-id="<?= $arResult['ID'] ?>">
                    <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                    <div class="shared_block">
                        <? $APPLICATION->IncludeComponent(
                            "arturgolubev:yandex.share",
                            "",
                            array(
                                "DATA_IMAGE" => "",
                                "DATA_RESCRIPTION" => "",
                                "DATA_TITLE" => $arResult['NAME'],
                                "DATA_URL" => 'https://' . SITE_SERVER_NAME . $arResult['DETAIL_PAGE_URL'],
                                "OLD_BROWSERS" => "N",
                                "SERVISE_LIST" => BXConstants::Shared(),
                                "TEXT_ALIGN" => "ar_al_left",
                                "TEXT_BEFORE" => "",
                                "VISUAL_STYLE" => "icons"
                            )
                        ); ?>
                    </div>
                </a>
            </div>
        </div>
        <?php
        if ($haveOffers) {
            foreach ($arResult['OFFERS'] as $keyOffer => $offer) {
                if (!isset($offer['MORE_PHOTO_COUNT']) || $offer['MORE_PHOTO_COUNT'] <= 0)
                    continue;

                $strVisible = $arResult['OFFERS_SELECTED'] == $keyOffer ? '' : 'none'; ?>
                <div class="product-item-detail-slider-controls-block mt-2"
                     id="<?= $itemIds['SLIDER_CONT_OF_ID'] . $offer['ID'] ?>"
                     style="display: <?= $strVisible ?>;">
                    <?php foreach ($offer['MORE_PHOTO'] as $keyPhoto => $photo) { ?>
                        <div class="product-item-detail-slider-controls-image<?= ($keyPhoto == 0 ? ' active' : '') ?>"
                             data-entity="slider-control"
                             data-value="<?= $offer['ID'] . '_' . $photo['ID'] ?>">
                            <img src="<?= $photo['SRC'] ?>">
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
        } else { ?>
            <div class="product-item-detail-slider-controls-block margin_block_element"
                 id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
                <?php if (!empty($actualItem['PICTURE']) && count($actualItem['PICTURE']) > 0) {
                    foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
                        <div class="product-item-detail-slider-controls-image<?= ($key == 0 ? ' active' : '') ?>"
                             data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
                            <img src="<?= $photo['SRC'] ?>">
                        </div>
                        <?php
                    }
                } ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<?php
$showOffersBlock = $haveOffers && !empty($arResult['OFFERS_PROP']);
$mainBlockProperties = array_intersect_key($arResult['DISPLAY_PROPERTIES'], $arParams['MAIN_BLOCK_PROPERTY_CODE']);
$showPropsBlock = !empty($mainBlockProperties) || $arResult['SHOW_OFFERS_PROPS'];
$showBlockWithOffersAndProps = $showOffersBlock || $showPropsBlock;?>
<div class="col-md-5 col-sm-6 col-lg-6 col-12 mt-lg-0 mt-md-0 mt-4 d-flex flex-column product_right justify-content-between">
    <h1 class="head-title"><?= $name ?></h1>
    <?php if ($isGift) { ?>
        <div>
            <h4 class="bx-title">Данная продукция не продается отдельно</h4>
        </div>
        <?php
    } else { ?>
        <p class="text_prev mb-4"><?= $arResult['PREVIEW_TEXT'] ?></p>
        <div class="d-flex flex-lg-column flex-md-column flex-column-reverse">
        <?php
        $height = 10;
        $strong = 0;
        if (isset($arResult['PROPERTIES'][PROP_STRONG_CODE]) && !empty($arResult['PROPERTIES'][PROP_STRONG_CODE]['VALUE'])) {
            switch ($arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE_SORT']) {
                case "1":
                    $strong = 0.5;
                    $color = "#07AB66";
                    break;
                case "2":
                    $strong = 1.5;
                    $color = "#FFC700";
                    break;
                case "3":
                    $strong = 2.5;
                    $color = "#FF7A00";
                    break;
            } ?>
            <div style="color: <?= $color ?>" class="column mt-lg-3 mt-md-3 mt-0 mb-4">
                <p class="condensation_text">
                    Крепость: <?= $arResult['PROPERTIES']['KREPOST_KALYANNOY_SMESI']['VALUE'] ?> </p>
                <div class="d-flex flex-row">
                    <?php for ($i = 0; $i < 3; $i++) { ?>
                        <div style="border-color: <?= $color ?>; <?= ($strong - $i) >= 1 ? "background-color: $color" : ''; ?>"
                             class="condensation">
                            <?php if ($strong - $i == 0.5) { ?>
                            <svg style="position: absolute; left: -5px; top: -1px" width="42"
                                 height="<?= $height ?>" xmlns="http://www.w3.org/2000/svg">
                                <path d="
                                                        M 20 <?= $height ?>
                                                        L 10 <?= $height ?>
                                                        Q 0 <?= $height / 2 ?> 10 0
                                                        L 30 0" stroke="<?= $color ?>" fill="<?= $color ?>"
                                />
                                <?php } ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        <?php }
        foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName) {
            switch ($blockName) {
                case 'price':
                    if ($show_price) {
                        if (USE_CUSTOM_SALE_PRICE && !empty($price['SALE_PRICE']['PRICE']) ||
                            $useDiscount['VALUE_XML_ID'] === 'true' && !empty($price['SALE_PRICE']['PRINT_PRICE'])) {
                            $price_new = $price['SALE_PRICE']['PRINT_PRICE'];
                            $price_id = $price['SALE_PRICE']['PRICE_TYPE_ID'];
                        } else {
                            $price_new = '<span class="font-14 card-price-text">от </span> ' . $price['PRICE_DATA'][1]['PRINT_PRICE'];
                            $price_id = $price['PRICE_DATA'][1]['PRICE_TYPE_ID'];
                        }
                        $styles = ''; ?>
                        <div class="mb-4 d-flex flex-column">
                            <div class="mb-3 d-flex flex-row align-items-center">
                                <div class="product-item-detail-price-current"
                                     id="<?= $itemIds['PRICE_ID'] ?>"><?= $price_new ?>
                                </div>
                                <?php if (USE_CUSTOM_SALE_PRICE && !empty($price['SALE_PRICE']['PRINT_PRICE']) ||
                                    $useDiscount['VALUE_XML_ID'] === 'true' &&
                                    !empty($price['SALE_PRICE']['PRINT_PRICE'])) {
                                    $styles = 'price-discount';
                                    $old_sum = (int)$price['PRICE_DATA'][0]['PRICE'] - (int)$price['SALE_PRICE']['PRICE'] ?? 0; ?>
                                    <span class="font-14 ml-3">
                                            <b class="decoration-color-red mr-2"><?= $price['PRICE_DATA'][0]['PRINT_PRICE']; ?></b>
                                            <b class="sale-percent"> - <?= $old_sum ?> руб.</b>
                                        </span>
                                <?php } ?>
                            </div>
                            <div class="d-flex flex-column prices-block">
                                <?php foreach ($price['PRICE_DATA'] as $items) { ?>
                                    <p>
                                        <span class="font-14 mr-2"><b><?= $items['NAME'] ?></b></span> -
                                        <span class="font-14 ml-2 <?= $styles ?>"><b><?= $items['PRINT_PRICE'] ?></b></span>
                                    </p>
                                <?php } ?>
                            </div>
                        </div>
                        <!--Бонусная система -->
                        <!--                            <div class="mb-5">-->
                        <!--                                <a href="#" class="link_bonus">Начислится бонусов за покупку: 11</a>-->
                        <!--                            </div>-->
                        </div>
                        <?php
                    }
                    break;
                case 'quantityLimit':
                    if ($show_price) {
                        $arParams['SHOW_MAX_QUANTITY'] = 'N';
                        if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
                            if ($haveOffers) { ?>
                                <div class="mb-3" id="<?= $itemIds['QUANTITY_LIMIT'] ?>"
                                     style="display: none;">
                                                                <span class="product-item-quantity"
                                                                      data-entity="quantity-limit-value"></span>
                                </div>
                            <?php } else {
                                if ($measureRatio && (float)$actualItem['PRODUCT']['QUANTITY'] > 0
                                    && $actualItem['CHECK_QUANTITY']) { ?>
                                    <div class="mb-3 text-center"
                                         id="<?= $itemIds['QUANTITY_LIMIT'] ?>">
                                        <span class="product-item-detail-info-container-title"><?= $arParams['MESS_SHOW_MAX_QUANTITY'] ?>:</span>
                                        <span class="product-item-quantity"
                                              data-entity="quantity-limit-value">
                                                                            <?php if ($arParams['SHOW_MAX_QUANTITY'] === 'M') {
                                                                                if ((float)$actualItem['PRODUCT']['QUANTITY'] / $measureRatio >= $arParams['RELATIVE_QUANTITY_FACTOR']) {
                                                                                    echo $arParams['MESS_RELATIVE_QUANTITY_MANY'];
                                                                                } else {
                                                                                    echo $arParams['MESS_RELATIVE_QUANTITY_FEW'];
                                                                                }
                                                                            } else {
                                                                                echo $actualItem['PRODUCT']['QUANTITY'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'];
                                                                            } ?>
                                                                        </span>
                                    </div>
                                    <?php
                                }
                            }
                        }
                    }
                    break;
                case 'quantity':
                    if ($show_price) {
                        if ($actualItem['PRODUCT']['QUANTITY'] != '0') { ?>
                            <div class="mb-lg-3 mb-md-3 mb-4 d-flex flex-row align-items-center bx_catalog_item bx_catalog_item_controls"
                                <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                 data-entity="quantity-block">
                                <div class="product-item-amount-field-contain mr-3">
                                                            <span class="btn-minus no-select minus_icon add2basket basket_prod_detail"
                                                                  data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                                                  data-product_id="<?= $arResult['ID']; ?>"
                                                                  id="<?= $itemIds['QUANTITY_DOWN_ID'] ?>"
                                                                  data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>">
                                                            </span>
                                    <div class="product-item-amount-field-block">
                                        <input class="product-item-amount card_element cat-det" id="<?= $itemIds['QUANTITY_ID'] ?>"
                                               type="number" value="<?= $priceBasket ?>"
                                               data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                               data-product_id="<?= $arResult['ID']; ?>"
                                               data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"/>
                                    </div>
                                    <span class="btn-plus no-select plus_icon add2basket basket_prod_detail"
                                          data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                          data-max-quantity="<?= $actualItem['PRODUCT']['QUANTITY'] ?>"
                                          data-product_id="<?= $arResult['ID']; ?>"
                                          id="<?= $itemIds['QUANTITY_UP_ID'] ?>"></span>
                                </div>
                                <a id="<?= $arResult['BUY_LINK']; ?>" href="javascript:void(0)" rel="nofollow"
                                   class="add2basket basket_prod_detail btn red_button_cart"
                                   data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>" data-product_id="<?= $arResult['ID']; ?>"
                                   title="Добавить в корзину">
                                    <img class="image-cart" src="/local/templates/Oshisha/images/cart-white.png"/>
                                </a>
                                <div id="result_box"></div>
                                <div id="popup_mess"></div>
                            </div>
                            <div class="alert_quantity" data-id="<?= $arResult['ID'] ?>"></div>
                            <div class="d-flex flex-lg-column flex-md-column flex-column-reverse">
                            <div class="mb-4 d-flex align-items-center">
                                <a href="#" class="link_prod_quant"><span>В наличии </span></a>
                                <a href="#" class="ml-lg-5 ml-md-5 ml-3 link_prod"><span>Посмотреть наличие в магазинах</span>
                                </a>
                            </div>
                        <?php }
                        else { ?>
                            <div class="bx_catalog_item_controls mb-5 d-flex flex-row align-items-center
                                                             bx_catalog_item"
                                <?= (!$actualItem['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                 data-entity="quantity-block">
                                <div class="d-flex flex-row align-items-center mr-3">
                                    <div class="product-item-amount-field-contain">
                                                                        <span class=" no-select minus_icon add2basket basket_prod_detail mr-3"
                                                                              style="pointer-events: none;">
                                                                        </span>
                                        <div class="product-item-amount-field-block">
                                            <input class="product-item-amount" id="<?= $itemIds['QUANTITY_ID'] ?>"
                                                   disabled="disabled" type="number" value="0">
                                        </div>
                                        <span class="no-select plus_icon add2basket basket_prod_detail ml-3"
                                              style="pointer-events: none;">
                                                                        </span>
                                    </div>
                                    <a id="<?= $arResult['BUY_LINK']; ?>" href="javascript:void(0)"
                                       rel="nofollow"
                                       class="basket_prod_detail detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth'?>
                                           <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : ''?> detail_disabled"
                                       data-url="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                       data-product_id="<?= $arResult['ID']; ?>"
                                       title="Добавить в корзину">Забронировать</a>
                                </div>
                                <div id="result_box" style="width: 100%;position: absolute;"></div>
                                <div class="detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth'?>
                                        <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : ''?>">
                                    <i class="fa fa-bell-o <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'filled' : ''?>" aria-hidden="true"></i>
                                </div>
                                <div id="popup_mess" class="popup_mess_prods <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : ''?>"
                                     data-subscription_id="<?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? $arResult['ITEM_SUBSCRIPTION']['ID'] : ''?>"
                                     data-product_id="<?= $arResult['ID']; ?>"></div>
                            </div>
                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div class="not_product detail_popup <?= $USER->IsAuthorized() ? '' : 'noauth'?>
                                        <?= $arResult['IS_SUBSCRIPTION_KEY_FOUND'] ? 'subscribed' : ''?>">
                                    Нет в наличии
                                </div>
                            </div>
                            <?php
                        }
                    }
                    break;
            }
        } ?>
        <div class="new_box d-flex flex-row align-items-center mb-lg-0 mb-md-0 mb-5">
            <span></span>
            <p>Наличие товара, варианты и стоимость доставки будут указаны далее при оформлении заказа. </p>
        </div>
        <?php if ($actualItem['PRODUCT']['QUANTITY'] != '0') { ?></div><?php } ?>
        <div class="ganerate_price_wrap ml-auto mt-5 w-75 font-weight-bold h5"
             <? if ($priceBasket > 0): ?><? else: ?>style="display:none;"<? endif; ?>>
            Итого:
            <div class="inline-block float-right ganerate_price">
                <?=
                ((int)substr(preg_replace('/[\D]/', '', $price_new), 0, -4)) * $priceBasket . ' ₽';
                ?>
            </div>
        </div>
    <?php } ?>
</div>