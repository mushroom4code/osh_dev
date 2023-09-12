<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Enterego\EnteregoHelper;
if (empty($arResult["CATEGORIES"]))
    return;

?>
<div tabindex="0" id="search_results_container" class="bx_searche">
    <?php
    $dbStatistic = CSearchStatistic::GetList(
            array("TIMESTAMP_X"=>'DESC'),
            array("STAT_SESS_ID" => $_SESSION['SESS_SESSION_ID']),
            array('TIMESTAMP_X', 'PHRASE')
    );
    $dbStatistic->NavStart(3);
    $popularSearches = [];
    $component = $this->getComponent();
    while( $arStatistic = $dbStatistic->Fetch()){
        $popularSearches[] = $arStatistic;
    }

    $arBasketItems = array();
    $dbBasketItems = CSaleBasket::GetList(
        array("NAME" => "ASC", "ID" => "ASC"),
        array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL"),
        false,
        false,
        array("ID", "PRODUCT_ID", "QUANTITY",)
    );
    while ($arItems = $dbBasketItems->Fetch()) {
        $arBasketItems[$arItems["PRODUCT_ID"]] = $arItems["QUANTITY"];
    }
    foreach ($arBasketItems as $key => $val) {
        if ($key == $arResult['ELEMENTS'][$key]['ID']) {
            $arResult['ELEMENTS'][$key]['BASKET_QUANTITY'] = $val;
        }
    }

    $searchElementsIds = [];
    $uniqueIds = [];
    $searchElements = [];
    foreach($arResult["ELEMENTS"] as $searchItem) {
        $uniqueId = $this->GetEditAreaId($searchItem["ID"]);
        $arResult['ELEMENTS'][$searchItem['ID']]['BUY_LINK'] = $uniqueId.'_buy_link';
        $arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_DOWN_ID'] = $uniqueId.'_quant_down';
        $arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_UP_ID'] = $uniqueId.'_quant_up';
        $arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_ID'] = $uniqueId.'_quantity';
        $arResult['ELEMENTS'][$searchItem['ID']]['PRICE_ID'] = $uniqueId.'_price';
        if (empty($searchItem['BASKET_QUANTITY'])) {
            $arResult['ELEMENTS'][$searchItem['ID']]['BASKET_QUANTITY'] = 0;
        }
    }

?>
    <?foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
        <?foreach($arCategory["ITEMS"] as $i => $arItem):?>
            <?if($category_id === "all"):?>
                <div class="bx_item_block all_result" onclick="window.location='<?echo $arItem["URL"]?>';">
                    <div class="bx_item_element">
                        <span class="all_result_title">
                            <a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a>
                        </span>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            <?php elseif(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):

                $arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];
                if (!empty($arElement['PRICES_CUSTOM']['USER_PRICE']['VALUE'])) {
                    $specialPrice = $arElement['PRICES_CUSTOM']['USER_PRICE'];
                }

                if (!empty($arElement['PRICES_CUSTOM']['SALE_PRICE']['VALUE'])
                    && ( !isset($specialPrice) || $arElement['PRICES_CUSTOM']['SALE_PRICE']['VALUE'] < $specialPrice['VALUE'])) {

                    $specialPrice = $arElement['PRICES_CUSTOM']['SALE_PRICE'];
                }
            EnteregoHelper::setProductsActiveUnit($arElement, true);
            $arElement['MEASURE_RATIO'] = \Bitrix\Catalog\MeasureRatioTable::getList(array(
                'select' => array('RATIO'),
                'filter' => array('=PRODUCT_ID' => $arElement['ID'])
            ))->fetch()['RATIO'];
            foreach ($arElement['PRICES'] as &$priceRow) {
                $priceRow['PRINT_RATIO_PRICE'] = \CCurrencyLang::CurrencyFormat(
                    $priceRow['VALUE'] * $arElement['MEASURE_RATIO'],
                    $priceRow['CURRENCY'],
                    true
                );
            }
            ?>
                <div class="bx_item_block" onclick="window.location='<?= $arItem["URL"]?>';">
                    <?if (is_array($arElement["PICTURE"])):?>
                        <div class="bx_img_element">
                            <div class="bx_image" style="background-image: url('<?echo $arElement["PICTURE"]["src"]?>')"></div>
                        </div>
                    <?endif;?>
                    <div class="bx_item_element">
                        <a href="<?echo $arItem["URL"]?>"><?= $arElement["PREVIEW_TEXT"]?></a>

                        <i  id="search_result<?echo$arElement['ID']?>_detail_opener"
                           class="fa fa-angle-right" aria-hidden="true" tabindex="0"></i>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <?
                $textButton = 'Забронировать';
                $classButton = 'btn_basket';
                if ($arElement['BASKET_QUANTITY'] > 0) {
                    $textButton = 'Забронировано';
                    $classButton = 'addProductDetailButton';
                }
                ?>
                <div id="search_item_<?echo $arElement['ID']?>" class="bx_item_block_detail" style="display: none" tabindex="0">
                    <div class="d-flex flex-column prices-block">
                        <p>
                            <span class="font-14 mr-2">Розничная (до 10к)</span> -
                            <span class="font-14 ml-2">
                                <?= $arElement['PRICES']['Розничная']['PRINT_RATIO_PRICE'] ?? $arElement['PRICES']['Розничная']['PRINT_VALUE']?></span>
                        </p>
                        <p>
                            <span class="font-14 mr-2">Основная (до 30к)</span> -
                            <span class="font-14 ml-2">
                                <?= $arElement['PRICES']['Основная']['PRINT_RATIO_PRICE'] ?? $arElement['PRICES']['Основная']['PRINT_VALUE'] ?></span>
                        </p>
                        <p>
                            <span class="font-14 mr-2">b2b (от 30к)</span> -
                            <span class="font-14 ml-2">
                                <?= $arElement['PRICES']['b2b']['PRINT_RATIO_PRICE'] ?? $arElement['PRICES']['b2b']['PRINT_VALUE'] ?></span>
                        </p>
                    </div>
                    <div class="old_and_current_prices_block">
                        <?php if(!empty($specialPrice)) { ?>
                            <div class="product-item-detail-price-current"
                                 id="<?= $arElement['PRICE_ID'] ?>">
                                <?= $specialPrice['PRINT_VALUE'] ?>
                            </div>
                            <span class="span">Старая цена <?= $arElement['PRICES_CUSTOM']['PRICE_DATA'][0]['PRINT_VALUE_VAT'] ?></span>
                        <?php } ?>
                    </div>
                    <?if ($arElement['CATALOG_QUANTITY'] >= $arElement['MEASURE_RATIO']):?>
                        <div class="mb-lg-3 mb-md-3 mb-4 d-flex flex-row align-items-center bx_catalog_item bx_catalog_item_controls"
                            <?= (!$arElement['PRICES']['Основная']['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                             data-entity="quantity-block">
                            <div class="product-item-amount-field-contain">
                                <span class="btn-minus no-select minus_icon add2basket basket_prod_detail"
                                      data-url="<?= $arItem['URL'] ?>"
                                      data-product_id="<?= $arElement['ID']; ?>"
                                      id="<?= $arElement['QUANTITY_DOWN_ID'] ?>"
                                      data-measure-ratio="<?= $arElement['MEASURE_RATIO'] ?>"
                                      data-active-unit="<?= $arElement['ACTIVE_UNIT'] ?>"
                                      data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] / $arElement['MEASURE_RATIO'] ?>"
                                      tabindex="0">
                                </span>
                                <div class="product-item-amount-field-block">
                                    <input class="product-item-amount card_element cat-det"
                                           id="<?= $arElement['QUANTITY_ID'] ?>"
                                           type="number" value="<?= $arElement['BASKET_QUANTITY'] / $arElement['MEASURE_RATIO']?>"
                                           data-url="<?= $arItem['URL'] ?>"
                                           data-product_id="<?= $arElement['ID']; ?>"
                                           data-measure-ratio="<?= $arElement['MEASURE_RATIO'] ?>"
                                           data-active-unit="<?= $arElement['ACTIVE_UNIT'] ?>"
                                           data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] / $arElement['MEASURE_RATIO'] ?>"
                                    />
                                </div>
                                <span class="btn-plus no-select plus_icon add2basket basket_prod_detail"
                                      data-url="<?= $arItem['URL'] ?>"
                                      data-measure-ratio="<?= $arElement['MEASURE_RATIO'] ?>"
                                      data-active-unit="<?= $arElement['ACTIVE_UNIT'] ?>"
                                      data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] / $arElement['MEASURE_RATIO'] ?>"
                                      data-product_id="<?= $arElement['ID']; ?>"
                                      id="<?= $arElement['QUANTITY_UP_ID'] ?>" tabindex="0">
                                </span>
                            </div>
                            <div id="result_box"></div>
                            <div id="popup_mess"></div>
                        </div>
                    <?else:?>
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="not_product detail_popup">Нет в наличии</div>
                        </div>
                    <?endif;?>
                </div>
                <script>
                    $('#search_result<?echo $arElement['ID']?>_detail_opener').click(function(event) {
                        event.stopImmediatePropagation();
                        $("#search_item_<?echo $arElement['ID']?>").toggle("fast");
                        var matrix = $(this).css("transform");
                        if (matrix !== 'none') {
                            var values = matrix.split('(')[1].split(')')[0].split(',');
                            var a = values[0];
                            var b = values[1];
                            var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));
                        } else {
                            var angle = 0;
                        }
                        if (angle == 90) {
                            $(this).css({'transform' : 'rotate(0deg)', 'transition-duration' : '600ms'});
                            $('.bx_searche div.alert_quantity[data-id="<?echo $arElement['ID']?>"]').removeClass('show_block');
                            $('.bx_searche div.alert_quantity[data-id="<?echo $arElement['ID']?>"]').contents().remove();
                        } else {
                            $(this).css({'transform' : 'rotate(90deg)', 'transition-duration' : '600ms'});
                        }
                    });
                </script>
                <div class="alert_quantity" data-id="<?= $arElement['ID'] ?>"></div>
            <?endif;?>
        <?endforeach;?>
    <?endforeach;?>
    <?if(!empty($popularSearches)):?>
        <div class="bx_item_block popular_searches_title" onclick="">
            <span>Популярные запросы</span>
        </div>
        <?foreach ($popularSearches as $popularSearch):?>
            <div class="bx_item_block popular_searches_result" onclick="popularSearchResultSubmit(this)">
                <div class="bx_item_element"
                     onclick="window.location='<?echo $arResult["FORM_ACTION"].'?q='.$popularSearch["PHRASE"]?>';"
                >
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="popular_search_title">
                        <a href="<?echo $arResult["FORM_ACTION"].'?q='.$popularSearch["PHRASE"]?>">
                            <?echo $popularSearch["PHRASE"]?>
                        </a>
                    </span>
                </div>
                <div style="clear:both;"></div>
            </div>
        <?endforeach;?>
    <?endif;?>
</div>
<script>
    $('#search_results_container').focusout(function () {
        if(!event.relatedTarget
            || ((event.relatedTarget.getAttribute('id') != 'input_search_desktop')
            && ($('#search_results_container').find(event.relatedTarget).length != 1))) {
                setTimeout(function(){$('#search_results_container').parent().css("display", "none");}, 250);
        }
    })
</script>
