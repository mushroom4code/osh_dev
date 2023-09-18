<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $mobileColumns
 * @var array $arResult
 * @var array $arParams
 * @var string $templateFolder
 */

use Bitrix\Conversion\Internals\MobileDetect;

$mobile = new MobileDetect();
$usePriceInAdditionalColumn = in_array('PRICE', $arParams['COLUMNS_LIST']) && $arParams['PRICE_DISPLAY_MODE'] === 'Y';
$useSumColumn = in_array('SUM', $arParams['COLUMNS_LIST']);
$useActionColumn = in_array('DELETE', $arParams['COLUMNS_LIST']);

$restoreColSpan = 2 + $usePriceInAdditionalColumn + $useSumColumn + $useActionColumn;

$positionClassMap = array(
    'left' => 'basket-item-label-left',
    'center' => 'basket-item-label-center',
    'right' => 'basket-item-label-right',
    'bottom' => 'basket-item-label-bottom',
    'middle' => 'basket-item-label-middle',
    'top' => 'basket-item-label-top'
);

$discountPositionClass = '';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$labelPositionClass = '';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

/**
 * @var CAllMain|CMain $APPLICATION
 * @var  CUser $USER
 */ ?>
<script id="basket-item-template" type="text/html">
    <div class="d-flex justify-content-center basket-items-list-item-container{{#SHOW_RESTORE}}
     basket-items-list-item-container-expend{{/SHOW_RESTORE}}"
         id="basket-item-{{ID}}" data-gift="{{GIFT}}" data-entity="basket-item" data-id="{{ID}}">
        {{^SHOW_RESTORE}}
        <div class="basket-items-list-item-descriptions d-flex row_section justify-content-between">
            <div class="basket-items-list-item-descriptions-inner col-lg-7 col-md-12 col-12 p-0"
            <div class="basket-items-list-item-descriptions-inner col-lg-7 col-md-12 col-12 p-0"
                 id="basket-item-height-aligner-{{ID}}">
                <?php if (in_array('PREVIEW_PICTURE', $arParams['COLUMNS_LIST'])){ ?>
                <div class="basket-item-block-image col-lg-3 col-md-3 p-3
                 <?= (!isset($mobileColumns['PREVIEW_PICTURE']) ? ' d-none d-sm-block' : '') ?>">
                    {{#DETAIL_PAGE_URL}}
                    <a href="{{DETAIL_PAGE_URL}}" class="basket-item-image-link">
                        {{/DETAIL_PAGE_URL}}

                        <img class="basket-item-image" alt="{{NAME}}"
                             src="{{{IMAGE_URL}}}{{^IMAGE_URL}}/local/templates/Oshisha/images/no-photo.gif{{/IMAGE_URL}}">

                        {{#SHOW_LABEL}}
                        <div class="basket-item-label-text basket-item-label-big <?= $labelPositionClass ?>">
                            {{#LABEL_VALUES}}
                            <div
                                    {{#HIDE_MOBILE}} class="d-none d-sm-block" {{
                            /HIDE_MOBILE}}>
                            <span title="{{NAME}}">{{NAME}}</span>
                        </div>
                        {{/LABEL_VALUES}}
                </div>
                {{/SHOW_LABEL}}
                <?php if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y') { ?>
                    {{#DISCOUNT_PRICE_PERCENT}}
                    <div class="basket-item-label-ring basket-item-label-small <?= $discountPositionClass ?>">
                        -{{DISCOUNT_PRICE_PERCENT_FORMATED}}
                    </div>
                    {{/DISCOUNT_PRICE_PERCENT}}
                    <?
                }
                ?>
                {{#DETAIL_PAGE_URL}}
                </a>
                {{/DETAIL_PAGE_URL}}
            </div>
            <?
            } ?>
            {{#SHOW_LOADING}}
            <div class="basket-items-list-item-overlay"></div>
            {{/SHOW_LOADING}}
            <div class="basket-item-block-info d-flex flex-column justify-content-between width-inherit">
                <div>
                    <?php if (isset($mobileColumns['DELETE'])) { ?>
                        <span class="basket-item-actions-remove d-block d-md-none"
                              data-entity="basket-item-delete"></span>
                    <?php } ?>
                    <h2 class="basket-item-info-name">
                        {{#DETAIL_PAGE_URL}}
                        <a href="{{DETAIL_PAGE_URL}}" class="basket-item-info-name-link">
                            {{/DETAIL_PAGE_URL}}

                            <span data-entity="basket-item-name">{{NAME}}</span>

                            {{#DETAIL_PAGE_URL}}
                        </a>
                        {{/DETAIL_PAGE_URL}}
                    </h2>
                    {{#NOT_AVAILABLE}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="text-center border border-danger rounded">
                            <?= Loc::getMessage('SBB_BASKET_ITEM_NOT_AVAILABLE') ?>.
                        </div>
                    </div>
                    {{/NOT_AVAILABLE}}
                    {{#DELAYED}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="alert alert-warning text-center">
                            <?= Loc::getMessage('SBB_BASKET_ITEM_DELAYED') ?>.
                            <a href="javascript:void(0)" data-entity="basket-item-remove-delayed">
                                <?= Loc::getMessage('SBB_BASKET_ITEM_REMOVE_DELAYED') ?>
                            </a>
                        </div>
                    </div>
                    {{/DELAYED}}
                    {{#WARNINGS.length}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="alert alert-warning alert-dismissable" data-entity="basket-item-warning-node">
                            <span class="close" data-entity="basket-item-warning-close">&times;</span>
                            {{#WARNINGS}}
                            <div data-entity="basket-item-warning-text">{{{.}}}</div>
                            {{/WARNINGS}}
                        </div>
                    </div>
                    {{/WARNINGS.length}}
                    <div class="basket-item-block-properties">
                        <?
                        if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
                            foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName) {
                                switch (trim((string)$blockName)) {
                                    case 'props':
                                        if (in_array('PROPS', $arParams['COLUMNS_LIST'])) {
                                            ?>
                                            <div class="variation_taste mt-2 mb-2">
                                                {{#PROPS}}
                                                {{#VKUS}}
                                                <span class="taste" data-background="#{{VALUE}}" id="{{ID}}">
                                                        {{NAME}}
                                                </span>
                                                {{/VKUS}}
                                                {{/PROPS}}
                                            </div>
                                            <!--                                            Бонусная система -->
                                            <!--   <a href="#" class="link_in_basket p-0">Начислится баллов за покупку: 11 </a>-->
                                            <?
                                        }

                                        break;
                                    case 'sku':
                                        ?>
                                        {{#SKU_BLOCK_LIST}}
                                        {{#IS_IMAGE}}
                                        <div class="basket-item-property basket-item-property-scu-image"
                                             data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name">{{NAME}}</div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    {{#SKU_VALUES_LIST}}
                                                    <li class="basket-item-scu-item{{#SELECTED}} selected{{/SELECTED}}
																		{{#NOT_AVAILABLE_OFFER}} not-available{{/NOT_AVAILABLE_OFFER}}"
                                                        title="{{NAME}}"
                                                        data-entity="basket-item-sku-field"
                                                        data-initial="{{#SELECTED}}true{{/SELECTED}}{{^SELECTED}}false{{/SELECTED}}"
                                                        data-value-id="{{VALUE_ID}}"
                                                        data-sku-name="{{NAME}}"
                                                        data-property="{{PROP_CODE}}">
																				<span class="basket-item-scu-item-inner"
                                                                                      style="background-image: url({{PICT}});"></span>
                                                    </li>
                                                    {{/SKU_VALUES_LIST}}
                                                </ul>
                                            </div>
                                        </div>
                                        {{/IS_IMAGE}}

                                        {{^IS_IMAGE}}
                                        <div class="basket-item-property basket-item-property-scu-text"
                                             data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name">{{NAME}}</div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    {{#SKU_VALUES_LIST}}
                                                    <li class="basket-item-scu-item{{#SELECTED}} selected{{/SELECTED}}
																		{{#NOT_AVAILABLE_OFFER}} not-available{{/NOT_AVAILABLE_OFFER}}"
                                                        title="{{NAME}}"
                                                        data-entity="basket-item-sku-field"
                                                        data-initial="{{#SELECTED}}true{{/SELECTED}}{{^SELECTED}}false{{/SELECTED}}"
                                                        data-value-id="{{VALUE_ID}}"
                                                        data-sku-name="{{NAME}}"
                                                        data-property="{{PROP_CODE}}">
                                                        <span class="basket-item-scu-item-inner">{{NAME}}</span>
                                                    </li>
                                                    {{/SKU_VALUES_LIST}}
                                                </ul>
                                            </div>
                                        </div>
                                        {{/IS_IMAGE}}
                                        {{/SKU_BLOCK_LIST}}

                                        {{#HAS_SIMILAR_ITEMS}}
                                        <div class="basket-items-list-item-double"
                                             data-entity="basket-item-sku-notification">
                                            <div class="alert alert-info alert-dismissable text-center">
                                                {{#USE_FILTER}}
                                                <a href="javascript:void(0)"
                                                   class="basket-items-list-item-double-anchor"
                                                   data-entity="basket-item-show-similar-link">
                                                    {{/USE_FILTER}}
                                                    <?= Loc::getMessage('SBB_BASKET_ITEM_SIMILAR_P1') ?>
                                                    {{#USE_FILTER}}</a>{{/USE_FILTER}}
                                                <?= Loc::getMessage('SBB_BASKET_ITEM_SIMILAR_P2') ?>
                                                {{SIMILAR_ITEMS_QUANTITY}} {{MEASURE_TEXT}}
                                                <br>
                                                <a href="javascript:void(0)"
                                                   class="basket-items-list-item-double-anchor"
                                                   data-entity="basket-item-merge-sku-link">
                                                    <?= Loc::getMessage('SBB_BASKET_ITEM_SIMILAR_P3') ?>
                                                    {{TOTAL_SIMILAR_ITEMS_QUANTITY}} {{MEASURE_TEXT}}?
                                                </a>
                                            </div>
                                        </div>
                                        {{/HAS_SIMILAR_ITEMS}}
                                        <?
                                        break;
                                    case 'columns':
                                        ?>
                                        {{#COLUMN_LIST}}
                                        {{#IS_IMAGE}}
                                        <div class="basket-item-property-custom basket-item-property-custom-photo
														{{#HIDE_MOBILE}}d-none d-sm-block{{/HIDE_MOBILE}}"
                                             data-entity="basket-item-property">
                                            <div class="basket-item-property-custom-name">{{NAME}}</div>
                                            <div class="basket-item-property-custom-value">
                                                {{#VALUE}}
                                                <span>
																	<img class="basket-item-custom-block-photo-item"
                                                                         src="{{{IMAGE_SRC}}}"
                                                                         data-image-index="{{INDEX}}"
                                                                         data-column-property-code="{{CODE}}">
																</span>
                                                {{/VALUE}}
                                            </div>
                                        </div>
                                        {{/IS_IMAGE}}
                                        <div>
                                        </div>
                                        {{#IS_LINK}}
                                        <div class="basket-item-property-custom basket-item-property-custom-text
														{{#HIDE_MOBILE}}d-none d-sm-block{{/HIDE_MOBILE}}"
                                             data-entity="basket-item-property">
                                            <div class="basket-item-property-custom-name">{{NAME}}</div>
                                            <div class="basket-item-property-custom-value"
                                                 data-column-property-code="{{CODE}}"
                                                 data-entity="basket-item-property-column-value">
                                                {{#VALUE}}
                                                {{{LINK}}}{{^IS_LAST}}<br>{{/IS_LAST}}
                                                {{/VALUE}}
                                            </div>
                                        </div>
                                        {{/IS_LINK}}
                                        {{/COLUMN_LIST}}
                                        <?
                                        break;
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="d-flex flex-lg-row flx-md-row flex-column align-items-center width_100">
                    <div class="basket-items-list-item-amount justify-content-between align-items-end">
                        <div class="d-flex flex-row">
                            <?php if ($mobile->isMobile() || $mobile->isTablet()) { ?>
                                <div class="mobile_price mr-md-4">
                                    <div class="basket-items-list-item-price d-flex flex-row <?= (!isset($mobileColumns['SUM']) ? ' d-none d-sm-block' : '') ?>">
                                        <div class="basket-item-block-price d-flex flex-column align-items-end">
                                            {{#GIFT}}
                                            {{/GIFT}}
                                                <div class="basket-item-price-current d-flex justify-content-end">
                                                    <span class="basket-item-price-current-text" id="basket-item-sum-price-{{ID}}">
                                                        {{{SUM_PRICE_FORMATED}}}
                                                    </span>
                                                </div>
                                            {{^GIFT}}
                                                {{#SHOW_DISCOUNT_PRICE}}
                                                    <div class="basket-item-price-old color-darkOsh">
                                                        <span class="basket-item-price-old-text" id="basket-item-sum-price-old-{{ID}}">
                                                            {{{SUM_FULL_PRICE_FORMATED}}}
                                                        </span>
                                                    </div>
                                                    <div class="basket-item-price-difference">
                                                        <span id="basket-item-sum-price-difference-{{ID}}"
                                                              style="white-space: nowrap;">
                                                            <b class="sale-percent"> - {{{SUM_DISCOUNT_PRICE_FORMATED}}}</b>
                                                        </span>
                                                    </div>
                                                {{/SHOW_DISCOUNT_PRICE}}
                                            {{/GIFT}}
                                            {{#SHOW_LOADING}}
                                            <div class="basket-items-list-item-overlay"></div>
                                            {{/SHOW_LOADING}}
                                        </div>
                                        <i class="fa fa-caret-down font-20 ml-2 js--open-price-list"
                                           aria-hidden="true"></i>
                                    </div>

                                    <div class="position-relative d-flex flex-row">
                                        {{#SHOW_SALE_PRICE}}
                                            <div class="text-right font-11">
                                                <b class="decoration-color-red mr-2 font-16">{{{SUM_OLD}}}₽</b>
                                                <br>
                                                <b class="sale-percent"> - {{{SALE_PRICE_VAL}}}₽</b>
                                            </div>
                                        {{/SHOW_SALE_PRICE}}
                                        <div class="box-with-prices-net d-none position-absolute p-2">
                                            {{#PRICES_NET}}
                                                {{#PRICE_DATA}}
                                                    <p class="font-12 mb-2"><b>{{{NAME}}}</b> - <b>{{{VAL}}} ₽</b></p>
                                                {{/PRICE_DATA}}
                                            {{/PRICES_NET}}
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="basket-item-block-amount{{#NOT_AVAILABLE}} disabled{{/NOT_AVAILABLE}}"
                                 data-entity="basket-item-quantity-block">
                                {{^GIFT}}
                                <span class="basket-item-amount-btn-minus" data-max="{{AVAILABLE_QUANTITY_WITH_RATIO}}"
                                      data-entity="basket-item-quantity-minus"></span>
                                {{/GIFT}}
                                <div class="basket-item-amount-filed-block">

                                    <input type="text" class="product-item-amount" value="{{QUANTITY_WITH_RATIO}}"
                                           {{#GIFT}} disabled="disabled" {{/GIFT}}
                                    {{#NOT_AVAILABLE}} disabled="disabled" {{/NOT_AVAILABLE}}

                                    data-value="{{QUANTITY_WITH_RATIO}}" data-max="{{AVAILABLE_QUANTITY_WITH_RATIO}}"
                                    data-entity="basket-item-quantity-field"
                                    id="basket-item-quantity-{{ID}}">
                                </div>
                                {{^GIFT}}
                                <span class="basket-item-amount-btn-plus" data-max="{{AVAILABLE_QUANTITY_WITH_RATIO}}"
                                      data-entity="basket-item-quantity-plus"></span>
                                {{/GIFT}}
                                {{#SHOW_LOADING}}
                                <div class="basket-items-list-item-overlay"></div>
                                {{/SHOW_LOADING}}
                            </div>
                        </div>
                        <div class="alert_quantity" data-id="{{PRODUCT_ID}}"></div>
                    </div>
                    <?php if ($useActionColumn) { ?>
                        <div class="d-flex flex-row like-column ml-2">
                            <div class="like-block"><?php
                                $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                    'templates',
                                    array(
                                        'ID_PROD' => "{{ID}}",
                                        'F_USER_ID' => $USER->getId(),
                                        'LOOK_LIKE' => false,
                                        'LOOK_FAVORITE' => true,
                                        'COUNT_LIKE' => $arResult['COUNT_LIKE'],
                                        'COUNT_FAV' => "{{COUNT_FAV}}",//$arResult['COUNT_FAV'],
                                        'COUNT_LIKES' => $arResult['COUNT_LIKES'],
                                    ),
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                ); ?></div>
                            <div class="basket-items-list-item-remove">
                                <div class="basket-item-block-actions">
                            <span class="basket-item-actions-remove" data-entity="basket-item-delete">
                                <i class="fa fa-trash-o" title="Удалить товар" aria-hidden="true"></i></span>
                                    {{#SHOW_LOADING}}
                                    <div class="basket-items-list-item-overlay"></div>
                                    {{/SHOW_LOADING}}
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-between align-items-end end-column col-lg-5 col-md-12 col-12 pr-0">
            <?php if (!$mobile->isMobile() || $mobile->isTablet()) { ?>
                <div class="d-flex flex-row width_100 justify-content-between">
                    <div class="box-with-prices-net p-2">
                        {{#PRICES_NET}}
                        {{#PRICE_DATA}}
                        <p class="font-12 mb-2"><b>{{{NAME}}}</b> - <b>{{{VAL}}} ₽</b></p>
                        {{/PRICE_DATA}}
                        {{/PRICES_NET}}
                    </div>
                    <div class="d-flex flex-column price-column ml-2">
                        <div class="basket-items-list-item-price mb-1 <?= (!isset($mobileColumns['SUM']) ? ' d-none d-sm-block' : '') ?>">
                            <div class="basket-item-block-price d-flex flex-column align-items-end">
                                {{^GIFT}}
                                {{/GIFT}}
                                    <div class="basket-item-price-current d-flex justify-content-end">
                                        <span class="basket-item-price-current-text" id="basket-item-sum-price-{{ID}}">
                                            {{{SUM_PRICE_FORMATED}}}
                                        </span>
                                    </div>
                                {{^GIFT}}
                                {{#SHOW_DISCOUNT_PRICE}}
                                    <div class="basket-item-price-old color-darkOsh">
                                            <span class="basket-item-price-old-text" id="basket-item-sum-price-old-{{ID}}">
                                                {{{SUM_FULL_PRICE_FORMATED}}}
                                            </span>
                                    </div>
                                    <div class="basket-item-price-difference">
                                        <span id="basket-item-sum-price-difference-{{ID}}"
                                              style="white-space: nowrap;">
                                            <b class="sale-percent"> - {{{SUM_DISCOUNT_PRICE_FORMATED}}}</b>
                                        </span>
                                    </div>
                                {{/SHOW_DISCOUNT_PRICE}}
                                {{/GIFT}}
                                {{#SHOW_LOADING}}
                                <div class="basket-items-list-item-overlay"></div>
                                {{/SHOW_LOADING}}
                            </div>
                        </div>
                        {{#SHOW_SALE_PRICE}}
                            <div class="text-right font-11">
                                <b class="decoration-color-red mr-2 font-16">{{{SUM_OLD}}}₽</b>
                                <br>
                                <b class="sale-percent"> - {{{SALE_PRICE_VAL}}}₽</b>
                            </div>
                        {{/SHOW_SALE_PRICE}}
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    {{/SHOW_RESTORE}}
    </div>
</script>

<!--GRID template-->
<script id="basket-item-grid-template" type="text/html">
    <div class="d-flex justify-content-center width_custom_scripts basket-items-list-item-container{{#SHOW_RESTORE}}
     basket-items-list-item-container-expend{{/SHOW_RESTORE}}"
         id="basket-item-{{ID}}" data-gift="{{GIFT}}" data-entity="basket-item" data-id="{{ID}}">
        {{^SHOW_RESTORE}}
        <div class="basket-items-list-item-descriptions d-flex column_section style_custom_sort justify-content-between">
            <div class="basket-items-list-item-descriptions-inner width_100 d-flex flex-column"
                 id="basket-item-height-aligner-{{ID}}">
                <div class="basket-item-block-properties">
                    <?php if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
                        foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName) {
                            switch (trim((string)$blockName)) {
                                case 'props':
                                    if (in_array('PROPS', $arParams['COLUMNS_LIST'])) {
                                        ?>
                                        <div class="variation_taste mt-2 mb-2">
                                            {{#PROPS}}
                                            {{#VKUS}}
                                            <span class="taste" data-background="#{{VALUE}}" id="{{ID}}">
                                                        {{NAME}}
                                                </span>
                                            {{/VKUS}}
                                            {{/PROPS}}
                                        </div>
                                        <?
                                    }
                                    break;
                            }
                        }
                    }
                    ?>
                </div>
                <?php if (in_array('PREVIEW_PICTURE', $arParams['COLUMNS_LIST'])) { ?>
                <div class="basket-item-block-image border_none m-0 align-self-center col-lg-3 col-md-3 p-3
                <?= (!isset($mobileColumns['PREVIEW_PICTURE']) ? ' d-none d-sm-block' : '') ?>">
                    {{#DETAIL_PAGE_URL}}
                    <a href="{{DETAIL_PAGE_URL}}" class="basket-item-image-link">
                        {{/DETAIL_PAGE_URL}}

                        <img class="basket-item-image" alt="{{NAME}}"
                             src="{{{IMAGE_URL}}}{{^IMAGE_URL}}/local/templates/Oshisha/images/no-photo.gif{{/IMAGE_URL}}">

                        {{#SHOW_LABEL}}
                        <div class="basket-item-label-text basket-item-label-big <?= $labelPositionClass ?>">
                            {{#LABEL_VALUES}}
                            <div {{#HIDE_MOBILE}} class="d-none d-sm-block" {{
                            /HIDE_MOBILE}}>
                            <span title="{{NAME}}">{{NAME}}</span>
                        </div>
                        {{/LABEL_VALUES}}
                </div>
                {{/SHOW_LABEL}}
                <?php if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y') { ?>
                    {{#DISCOUNT_PRICE_PERCENT}}
                    <div class="basket-item-label-ring basket-item-label-small <?= $discountPositionClass ?>">
                        -{{DISCOUNT_PRICE_PERCENT_FORMATED}}
                    </div>
                    {{/DISCOUNT_PRICE_PERCENT}}
                <?php } ?>
                {{#DETAIL_PAGE_URL}}
                </a>
                {{/DETAIL_PAGE_URL}}
            </div>
            <?php } ?>
            {{#SHOW_LOADING}}
            <div class="basket-items-list-item-overlay"></div>
            {{/SHOW_LOADING}}
            <div class="basket-item-block-info d-flex flex-column justify-content-between width-inherit">
                <div>
                    <?php if (isset($mobileColumns['DELETE'])) { ?>
                        <span class="basket-item-actions-remove d-block d-md-none"
                              data-entity="basket-item-delete"></span>
                    <?php } ?>
                    <h2 class="basket-item-info-name">
                        {{#DETAIL_PAGE_URL}}
                        <a href="{{DETAIL_PAGE_URL}}" class="basket-item-info-name-link font_12">
                            {{/DETAIL_PAGE_URL}}

                            <span data-entity="basket-item-name">{{NAME}}</span>

                            {{#DETAIL_PAGE_URL}}
                        </a>
                        {{/DETAIL_PAGE_URL}}
                    </h2>
                    {{#NOT_AVAILABLE}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="alert alert-danger text-center">
                            <?= Loc::getMessage('SBB_BASKET_ITEM_NOT_AVAILABLE') ?>.
                        </div>
                    </div>
                    {{/NOT_AVAILABLE}}
                    {{#DELAYED}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="alert alert-warning text-center">
                            <?= Loc::getMessage('SBB_BASKET_ITEM_DELAYED') ?>.
                            <a href="javascript:void(0)" data-entity="basket-item-remove-delayed">
                                <?= Loc::getMessage('SBB_BASKET_ITEM_REMOVE_DELAYED') ?>
                            </a>
                        </div>
                    </div>
                    {{/DELAYED}}
                    {{#WARNINGS.length}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="alert alert-warning alert-dismissable" data-entity="basket-item-warning-node">
                            <span class="close" data-entity="basket-item-warning-close">&times;</span>
                            {{#WARNINGS}}
                            <div data-entity="basket-item-warning-text">{{{.}}}</div>
                            {{/WARNINGS}}
                        </div>
                    </div>
                    {{/WARNINGS.length}}
                    <div class="basket-item-block-properties">
                        <?php if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
                            foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName) {
                                switch (trim((string)$blockName)) {
                                    case 'sku':
                                        ?>
                                        {{#SKU_BLOCK_LIST}}
                                        {{#IS_IMAGE}}
                                        <div class="basket-item-property basket-item-property-scu-image"
                                             data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name">{{NAME}}</div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    {{#SKU_VALUES_LIST}}
                                                    <li class="basket-item-scu-item{{#SELECTED}} selected{{/SELECTED}}
																		{{#NOT_AVAILABLE_OFFER}} not-available{{/NOT_AVAILABLE_OFFER}}"
                                                        title="{{NAME}}"
                                                        data-entity="basket-item-sku-field"
                                                        data-initial="{{#SELECTED}}true{{/SELECTED}}{{^SELECTED}}false{{/SELECTED}}"
                                                        data-value-id="{{VALUE_ID}}"
                                                        data-sku-name="{{NAME}}"
                                                        data-property="{{PROP_CODE}}">
																				<span class="basket-item-scu-item-inner"
                                                                                      style="background-image: url({{PICT}});"></span>
                                                    </li>
                                                    {{/SKU_VALUES_LIST}}
                                                </ul>
                                            </div>
                                        </div>
                                        {{/IS_IMAGE}}

                                        {{^IS_IMAGE}}
                                        <div class="basket-item-property basket-item-property-scu-text"
                                             data-entity="basket-item-sku-block">
                                            <div class="basket-item-property-name">{{NAME}}</div>
                                            <div class="basket-item-property-value">
                                                <ul class="basket-item-scu-list">
                                                    {{#SKU_VALUES_LIST}}
                                                    <li class="basket-item-scu-item{{#SELECTED}} selected{{/SELECTED}}
																		{{#NOT_AVAILABLE_OFFER}} not-available{{/NOT_AVAILABLE_OFFER}}"
                                                        title="{{NAME}}"
                                                        data-entity="basket-item-sku-field"
                                                        data-initial="{{#SELECTED}}true{{/SELECTED}}{{^SELECTED}}false{{/SELECTED}}"
                                                        data-value-id="{{VALUE_ID}}"
                                                        data-sku-name="{{NAME}}"
                                                        data-property="{{PROP_CODE}}">
                                                        <span class="basket-item-scu-item-inner">{{NAME}}</span>
                                                    </li>
                                                    {{/SKU_VALUES_LIST}}
                                                </ul>
                                            </div>
                                        </div>
                                        {{/IS_IMAGE}}
                                        {{/SKU_BLOCK_LIST}}

                                        {{#HAS_SIMILAR_ITEMS}}
                                        <div class="basket-items-list-item-double"
                                             data-entity="basket-item-sku-notification">
                                            <div class="alert alert-info alert-dismissable text-center">
                                                {{#USE_FILTER}}
                                                <a href="javascript:void(0)"
                                                   class="basket-items-list-item-double-anchor"
                                                   data-entity="basket-item-show-similar-link">
                                                    {{/USE_FILTER}}
                                                    <?= Loc::getMessage('SBB_BASKET_ITEM_SIMILAR_P1') ?>
                                                    {{#USE_FILTER}}</a>{{/USE_FILTER}}
                                                <?= Loc::getMessage('SBB_BASKET_ITEM_SIMILAR_P2') ?>
                                                {{SIMILAR_ITEMS_QUANTITY}} {{MEASURE_TEXT}}
                                                <br>
                                                <a href="javascript:void(0)"
                                                   class="basket-items-list-item-double-anchor"
                                                   data-entity="basket-item-merge-sku-link">
                                                    <?= Loc::getMessage('SBB_BASKET_ITEM_SIMILAR_P3') ?>
                                                    {{TOTAL_SIMILAR_ITEMS_QUANTITY}} {{MEASURE_TEXT}}?
                                                </a>
                                            </div>
                                        </div>
                                        {{/HAS_SIMILAR_ITEMS}}
                                        <?php
                                        break;
                                    case 'columns': ?>
                                        {{#COLUMN_LIST}}
                                        {{#IS_IMAGE}}
                                        <div class="basket-item-property-custom basket-item-property-custom-photo
														{{#HIDE_MOBILE}}d-none d-sm-block{{/HIDE_MOBILE}}"
                                             data-entity="basket-item-property">
                                            <div class="basket-item-property-custom-name">{{NAME}}</div>
                                            <div class="basket-item-property-custom-value">
                                                {{#VALUE}}
                                                <span>
																	<img class="basket-item-custom-block-photo-item"
                                                                         src="{{{IMAGE_SRC}}}"
                                                                         data-image-index="{{INDEX}}"
                                                                         data-column-property-code="{{CODE}}">
																</span>
                                                {{/VALUE}}
                                            </div>
                                        </div>
                                        {{/IS_IMAGE}}
                                        <div>
                                        </div>
                                        {{#IS_LINK}}
                                        <div class="basket-item-property-custom basket-item-property-custom-text
														{{#HIDE_MOBILE}}d-none d-sm-block{{/HIDE_MOBILE}}"
                                             data-entity="basket-item-property">
                                            <div class="basket-item-property-custom-name">{{NAME}}</div>
                                            <div class="basket-item-property-custom-value"
                                                 data-column-property-code="{{CODE}}"
                                                 data-entity="basket-item-property-column-value">
                                                {{#VALUE}}
                                                {{{LINK}}}{{^IS_LAST}}<br>{{/IS_LAST}}
                                                {{/VALUE}}
                                            </div>
                                        </div>
                                        {{/IS_LINK}}
                                        {{/COLUMN_LIST}}
                                        <?php
                                        break;
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div>
                    <div class="basket-items-list-item-amount mb-2 d-flex justify-content-center">
                        <div class="basket-item-block-amount{{#NOT_AVAILABLE}} disabled{{/NOT_AVAILABLE}}"
                             data-entity="basket-item-quantity-block">
                            {{^GIFT}}
                            <span class="basket-item-amount-btn-minus" data-entity="basket-item-quantity-minus"></span>
                            {{/GIFT}}
                            <div class="basket-item-amount-filed-block">
                                <input type="text" class="product-item-amount" value="{{QUANTITY_WITH_RATIO}}"
                                       {{#NOT_AVAILABLE}} disabled="disabled" {{/NOT_AVAILABLE}}
                                data-value="{{QUANTITY_WITH_RATIO}}" data-entity="basket-item-quantity-field"
                                id="basket-item-quantity-{{ID}}">
                            </div>
                            {{^GIFT}}
                            <span class="basket-item-amount-btn-plus" data-entity="basket-item-quantity-plus"></span>
                            {{/GIFT}}
                            {{#SHOW_LOADING}}
                            <div class="basket-items-list-item-overlay"></div>
                            {{/SHOW_LOADING}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex row_section align-items-center justify-content-between">
            <?php if ($useSumColumn) { ?>
                <div class="d-flex flex-column">
                    <div class="basket-items-list-item-price<?= (!isset($mobileColumns['SUM']) ? ' d-none d-sm-block' : '') ?>">
                        <div class="basket-item-block-price d-flex flex-column align-items-end">
                            {{^GIFT}}
                            {{/GIFT}}
                            <div class="basket-item-price-current d-flex">
                                <span class="basket-item-price-current-text font_18" id="basket-item-sum-price-{{ID}}">
                                    {{{SUM_PRICE_FORMATED}}}
                                </span>
                            </div>
                            {{^GIFT}}
                                {{#SHOW_DISCOUNT_PRICE}}
                                <div class="basket-item-price-old">
                                        <span class="basket-item-price-old-text" id="basket-item-sum-price-old-{{ID}}">
                                            {{{SUM_FULL_PRICE_FORMATED}}}
                                        </span>
                                </div>
                                <div class="basket-item-price-difference">
                                    <span id="basket-item-sum-price-difference-{{ID}}" style="white-space: nowrap;">
                                        <b class="sale-percent"> - {{{SUM_DISCOUNT_PRICE_FORMATED}}}</b>
                                    </span>
                                </div>
                                {{/SHOW_DISCOUNT_PRICE}}
                            {{/GIFT}}
                            {{#SHOW_LOADING}}
                            <div class="basket-items-list-item-overlay"></div>
                            {{/SHOW_LOADING}}
                        </div>
                    </div>
                    {{#SHOW_SALE_PRICE}}
                        <div class="text-right font-11">
                            <b class="decoration-color-red mr-2 font-16">{{{SUM_OLD}}}₽</b>
                            <br>
                            <b class="sale-percent"> - {{{SALE_PRICE_VAL}}}₽</b>
                        </div>
                    {{/SHOW_SALE_PRICE}}
                </div>
            <?php }
            if ($useActionColumn) { ?>
                <div class="d-flex flex-row width_100 justify-content-between">
                    <div>
                        <?php
                        $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                            'templates',
                            array(
                                'ID_PROD' => '{{ID}}',
                                'F_USER_ID' => $USER->getId(),
                                'LOOK_LIKE' => false,
                                'LOOK_FAVORITE' => true,
                                'COUNT_LIKE' => $item['COUNT_LIKE'],
                                'COUNT_FAV' => $item['COUNT_FAV'],
                                'COUNT_LIKES' => $item['COUNT_LIKES'],
                            )
                            ,
                            $component,
                            array('HIDE_ICONS' => 'Y')
                        ); ?>
                    </div>
                    <div class="basket-items-list-item-remove">
                        <div class="basket-item-block-actions">
                            <span class="basket-item-actions-remove" data-entity="basket-item-delete">
                                <i class="fa fa-trash-o" title="Удалить товар" aria-hidden="true"></i></span>
                            {{#SHOW_LOADING}}
                            <div class="basket-items-list-item-overlay"></div>
                            {{/SHOW_LOADING}}
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    {{/SHOW_RESTORE}}
    </div>
</script>