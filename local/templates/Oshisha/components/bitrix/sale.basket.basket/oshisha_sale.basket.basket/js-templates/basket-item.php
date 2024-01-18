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

$subscription_item_ids = array_column($arResult["CURRENT_USER_SUBSCRIPTIONS"]["SUBSCRIPTIONS"] ?? [], 'ITEM_ID');
$found_key = array_search((string)$arResult['ITEMS']['nAnCanBuy'][0]['PRODUCT_ID'], $subscription_item_ids);
$is_key_found = isset($found_key) && ($found_key !== false);
/**
 * @var CAllMain|CMain $APPLICATION
 * @var  CUser $USER
 */ ?>
<script id="basket-item-template" type="text/html">
    <div class="justify-between rounded-lg md:dark:py-5 md:dark:px-5 md:px-5 md:py-3 dark:py-0 dark:px-0 px-0 py-0
    dark:bg-darkBox bg-white dark:mb-4 mb-0 basket-items-list-item-container
    {{#SHOW_RESTORE}} basket-items-list-item-container-expend hidden{{/SHOW_RESTORE}}
    {{^SHOW_RESTORE}}flex{{/SHOW_RESTORE}}"
         id="basket-item-{{ID}}" data-gift="{{GIFT}}" data-entity="basket-item" data-id="{{ID}}">
        {{^SHOW_RESTORE}}
        <div class="basket-items-list-item-descriptions w-full flex flex-row flex-wrap justify-between">
            <div class="basket-items-list-item-descriptions-inner flex flex-row p-0 md:w-9/12 w-full"
                 id="basket-item-height-aligner-{{ID}}">
                <?php if (in_array('PREVIEW_PICTURE', $arParams['COLUMNS_LIST'])){ ?>
                <div class="basket-item-block-image h-auto p-3 bg-white rounded-lg flex justify-center align-center border mr-4">
                    {{#DETAIL_PAGE_URL}}
                    <a href="{{DETAIL_PAGE_URL}}" class="basket-item-image-link">
                        {{/DETAIL_PAGE_URL}}
                        <img class="basket-item-image h-32 w-32 object-contain" alt="{{NAME}}"
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
                <?php } ?>
                {{#DETAIL_PAGE_URL}}
                </a>
                {{/DETAIL_PAGE_URL}}
            </div>
            <?php } ?>
            {{#SHOW_LOADING}}
            <div class="basket-items-list-item-overlay"></div>
            {{/SHOW_LOADING}}
            <div class="basket-item-block-info flex flex-col justify-between w-inherit">
                <div>
                    <?php if (isset($mobileColumns['DELETE'])) { ?>
                        <span class="basket-item-actions-remove" data-entity="basket-item-delete"></span>
                    <?php } ?>
                    <h2 class="basket-item-info-name">
                        {{#DETAIL_PAGE_URL}}
                        <a href="{{DETAIL_PAGE_URL}}" class="basket-item-info-name-link">
                            {{/DETAIL_PAGE_URL}}
                            <span data-entity="basket-item-name"
                                  class="font-semibold dark:font-light md:text-lg text-textLight text-sm
                                  dark:text-textDarkLightGray">{{NAME}}</span>
                            {{#DETAIL_PAGE_URL}}
                        </a>
                        {{/DETAIL_PAGE_URL}}
                    </h2>
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
                    <div class="basket-item-block-properties contents">
                        <?php if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
                            foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName) {
                                switch (trim((string)$blockName)) {
                                    case 'props':
                                        if (in_array('PROPS', $arParams['COLUMNS_LIST'])) { ?>
                                            {{#PROPS}}
                                            {{#VKUS.length}}
                                            <div class="relative toggle_taste h-inherit">
                                                <div class="variation_taste flex flex-wrap flex-row mt-2 mb-2 js__tastes-list md:h-7 h-5 overflow-auto">
                                                    {{#PROPS}}
                                                    {{#VKUS}}
                                                    <span class="taste cursor-pointer js__taste h-fit md:px-2 px-1.5 mr-1 md:py-1
                                             py-0.5 mb-1 md:text-xs text-10 rounded-full" data-background="#{{VALUE}}"
                                                          id="{{ID}}">
                                                        {{NAME}}
                                                </span>
                                                    {{/VKUS}}
                                                    {{/PROPS}}
                                                </div>
                                            </div>
                                            {{/VKUS.length}}
                                            {{/PROPS}}
                                        <?php }
                                        break;
                                    case 'sku': ?>
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
                    {{#NOT_AVAILABLE}}
                    <div class="basket-items-list-item-warning-container">
                        <div class="md:my-4 my-2 md:text-md text-xs text-hover-red font-medium">
                            <?= Loc::getMessage('SBB_BASKET_ITEM_NOT_AVAILABLE') ?>.
                        </div>
                    </div>
                    {{/NOT_AVAILABLE}}
                </div>
                <div class="flex flex-row items-center relative">
                    {{^NOT_AVAILABLE}}
                    <div class="basket-items-list-item-amount justify-between items-end mr-4">
                        <div class="flex flex-row">
                            <?php if ($mobile->isMobile() || $mobile->isTablet()) { ?>
                                <div class="mobile_price mr-md-4">
                                    <div class="basket-items-list-item-price flex flex-row mb-3 <?= (!isset($mobileColumns['SUM']) ? 'hidden sm:block' : '') ?>">
                                        <div class="basket-item-block-price flex flex-col">
                                            {{#GIFT}}
                                            {{/GIFT}}
                                            <div class="basket-item-price-current flex justify-end">
                                                <span class="basket-item-price-current-text text-xl text-textLight
                                                dark:text-textDarkLightGray font-semibold"
                                                      id="basket-item-sum-price-{{ID}}">
                                                    {{{SUM_PRICE_FORMATED}}}
                                                </span>
                                            </div>
                                            {{^GIFT}}
                                            {{#SHOW_DISCOUNT_PRICE}}
                                            <div class="basket-item-price-old color-darkOsh">
                                                        <span class="basket-item-price-old-text"
                                                              id="basket-item-sum-price-old-{{ID}}">
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

                                    <div class="relative flex flex-row">
                                        {{#SHOW_SALE_PRICE}}
                                        <div class="flex flex-row items-center">
                                            <span class="line-through mr-2 text-md font-light text-textLight dark:text-textDarkLightGray">{{{SUM_OLD}}}₽</span>
                                            <span class="sale-percent py-1 px-3 text-sm rounded-md bg-light-red dark:bg-light-red text-white font-light "> - {{{SALE_PRICE_VAL}}}₽</span>
                                        </div>
                                        {{/SHOW_SALE_PRICE}}
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="basket-item-block-amount{{#NOT_AVAILABLE}} disabled {{/NOT_AVAILABLE}}
                                flex flex-row items-center justify-between w-full"
                                 data-entity="basket-item-quantity-block">
                                {{^GIFT}}
                                <span class="basket-item-amount-btn-minus rounded-full md:py-0 md:px-0 py-3.5 px-1.5
                                             dark:bg-dark md:dark:bg-darkBox bg-none no-select cursor-pointer flex
                                             items-center justify-center md:h-full h-auto md:w-full w-auto"
                                      data-max="{{AVAILABLE_QUANTITY}}"
                                      data-entity="basket-item-quantity-minus">
                                    <svg width="20" height="2" viewBox="0 0 22 2" fill="none"
                                         class="stroke-dark dark:stroke-white stroke-[1.5px]"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1H21" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                                {{/GIFT}}
                                <div class="basket-item-amount-filed-block">

                                    <input type="text" class="product-item-amount dark:bg-tagFilterGray bg-textDarkLightGray
                                        focus:border-none text-center border-none text-sm
                                        shadow-none py-2.5 px-3 md:mx-2 mx-1 outline-none rounded-md md:w-14 w-16"
                                           value="{{QUANTITY}}"
                                           {{#GIFT}} disabled="disabled" {{/GIFT}}
                                    {{#NOT_AVAILABLE}} disabled="disabled" {{/NOT_AVAILABLE}}

                                    data-value="{{QUANTITY}}" data-max="{{AVAILABLE_QUANTITY}}"
                                    data-entity="basket-item-quantity-field"
                                    id="basket-item-quantity-{{ID}}">
                                </div>
                                {{^GIFT}}
                                <span class="basket-item-amount-btn-plus no-select cursor-pointer flex items-center
                                            justify-center rounded-full md:p-0 p-1.5 dark:bg-dark md:dark:bg-darkBox
                                            bg-none md:h-full h-auto md:w-full w-auto" data-max="{{AVAILABLE_QUANTITY}}"
                                      data-entity="basket-item-quantity-plus">
                                    <svg width="20" height="20" viewBox="0 0 20 20"
                                         class="fill-light-red dark:fill-white" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18.8889 11.111H1.11111C0.503704 11.111 0 10.6073 0 9.9999C0 9.3925 0.503704 8.88879 1.11111 8.88879H18.8889C19.4963 8.88879 20 9.3925 20 9.9999C20 10.6073 19.4963 11.111 18.8889 11.111Z"></path>
                                        <path d="M10 20C9.39262 20 8.88892 19.4963 8.88892 18.8889V1.11111C8.88892 0.503704 9.39262 0 10 0C10.6074 0 11.1111 0.503704 11.1111 1.11111V18.8889C11.1111 19.4963 10.6074 20 10 20Z"></path>
                                    </svg>
                                </span>
                                {{/GIFT}}
                                {{#SHOW_LOADING}}
                                <div class="basket-items-list-item-overlay"></div>
                                {{/SHOW_LOADING}}
                            </div>
                        </div>
                        <div class="alert_quantity hidden w-auto absolute md:p-4 p-2 text-xs left-0 top-12 bg-[#FFE2E2]
                                    dark:bg-dark shadow-lg rounded-md z-20 text-hover-red dark:text-textDarkLightGray
                                     font-medium dark:font-light"
                             data-id="{{PRODUCT_ID}}"></div>
                    </div>
                    {{/NOT_AVAILABLE}}
                    {{#NOT_AVAILABLE}}
                    <div class="bx_catalog_item_controls">
                        <svg width="34" height="33" class="detail_popup <?= $is_key_found ? 'subscribed stroke-light-red' : ' stroke-black dark:stroke-white' ?>" viewBox="0 0 34 33" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M25.5762 11.0001C25.5762 8.81209 24.6884 6.71367 23.1081 5.16649C21.5279 3.61932 19.3846 2.75012 17.1498 2.75012C14.915 2.75012 12.7717 3.61932 11.1915 5.16649C9.61121 6.71367 8.72344 8.81209 8.72344 11.0001C8.72344 20.6251 4.51025 23.3751 4.51025 23.3751H29.7894C29.7894 23.3751 25.5762 20.6251 25.5762 11.0001Z"
                                   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19.5794 28.875C19.3325 29.2917 18.9781 29.6376 18.5517 29.8781C18.1253 30.1186 17.6419 30.2451 17.1498 30.2451C16.6577 30.2451 16.1743 30.1186 15.7479 29.8781C15.3215 29.6376 14.9671 29.2917 14.7202 28.875"
                                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div id="popup_mess"
                             class="catalog_popup absolute z-20 w-full left-0 <?= $USER->IsAuthorized() ? '' : 'noauth' ?>
                         <?= $is_key_found ? 'subscribed' : '' ?>"
                             data-subscription_id="<?= $is_key_found ? $arResult['CURRENT_USER_SUBSCRIPTIONS']['SUBSCRIPTIONS'][$found_key]['ID'] : '' ?>"
                             data-product_id="{{PRODUCT_ID}}">
                        </div>
                    </div>
                    {{/NOT_AVAILABLE}}
                    <?php if ($useActionColumn) { ?>
                        <div class="flex flex-row like-column ml-2">
                            <div class="like-block" title="Избранное">
                                <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
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
                            <div class="basket-items-list-item-remove ml-4">
                                <div class="basket-item-block-actions">
                                    <span class="basket-item-actions-remove" title="Удалить"
                                          data-entity="basket-item-delete">
                                        <svg width="24" height="28" viewBox="0 0 28 32" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M23.2445 6.08154L22.0036 25.4192C21.895 27.1128 21.8406 27.9597 21.4886 28.6018C21.1786 29.1672 20.711 29.6216 20.1475 29.9054C19.5074 30.2277 18.6904 30.2277 17.0566 30.2277H10.8399C9.20605 30.2277 8.38914 30.2277 7.74905 29.9054C7.18551 29.6216 6.71794 29.1672 6.40796 28.6018C6.05586 27.9597 6.00153 27.1128 5.89285 25.4192L4.65202 6.08154M1.55327 6.08154H26.3433M20.1458 6.08154L19.7265 4.77454C19.3201 3.50797 19.1168 2.87468 18.74 2.40647C18.4072 1.99301 17.9799 1.67298 17.4971 1.47572C16.9503 1.25232 16.308 1.25232 15.0229 1.25232H12.8736C11.5886 1.25232 10.9462 1.25232 10.3994 1.47572C9.91667 1.67298 9.4893 1.99301 9.1565 2.40647C8.77963 2.87468 8.57646 3.50797 8.17009 4.77454L7.75077 6.08154M17.047 12.5205V23.7887M10.8495 12.5205V23.7887"
                                                  class="stroke-light-red dark:stroke-white" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
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
        <div class="flex flex-col justify-between items-end end-column md:w-3/12 w-full">
            <?php if (!$mobile->isMobile() || $mobile->isTablet()) { ?>
                <div class="flex flex-row justify-between">
                    <div class="flex flex-col price-column ml-2">
                        <div class="basket-items-list-item-price mb-3 <?= (!isset($mobileColumns['SUM']) ? ' d-none d-sm-block' : '') ?>">
                            <div class="basket-item-block-price flex flex-column">
                                {{^GIFT}}
                                {{/GIFT}}
                                <div class="basket-item-price-current flex justify-end">
                                        <span class="basket-item-price-current-text text-2xl text-textLight
                                                dark:text-white font-semibold dark:font-normal"
                                              id="basket-item-sum-price-{{ID}}">
                                            {{{SUM_PRICE_FORMATED}}}
                                        </span>
                                </div>
                                {{^GIFT}}
                                {{#SHOW_DISCOUNT_PRICE}}
                                <div class="basket-item-price-old color-darkOsh">
                                            <span class="basket-item-price-old-text"
                                                  id="basket-item-sum-price-old-{{ID}}">
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
                        <div class="flex flex-row items-center">
                            <span class="line-through mr-2 text-md font-light text-textLight dark:text-textDarkLightGray">{{{SUM_OLD}}}₽</span>
                            <span class="sale-percent py-1 px-3 text-sm rounded-md bg-light-red dark:bg-light-red text-white font-light "> - {{{SALE_PRICE_VAL}}}₽</span>
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
        <div class="basket-items-list-item-descriptions flex column_section style_custom_sort justify-between">
            <div class="basket-items-list-item-descriptions-inner w-full flex flex-col"
                 id="basket-item-height-aligner-{{ID}}">
                <div class="basket-item-block-properties contents">
                    <?php if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
                        foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName) {
                            switch (trim((string)$blockName)) {
                                case 'props':
                                    if (in_array('PROPS', $arParams['COLUMNS_LIST'])) {
                                        ?>
                                        <div class="relative toggle_taste h-inherit md:w-1/2 w-full">
                                            <div class="variation_taste flex flex-wrap flex-row mt-2 mb-2 js__tastes-list md:h-7 h-5 overflow-auto">
                                                {{#PROPS}}
                                                {{#VKUS}}
                                                <span class="taste cursor-pointer js__taste h-fit md:px-2 px-1.5 mr-1 md:py-1
                                             py-0.5 mb-1 md:text-xs text-10 rounded-full" data-background="#{{VALUE}}"
                                                      id="{{ID}}">
                                                        {{NAME}}
                                                </span>
                                                {{/VKUS}}
                                                {{/PROPS}}
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    break;
                            }
                        }
                    } ?>
                </div>
                <?php if (in_array('PREVIEW_PICTURE', $arParams['COLUMNS_LIST'])) { ?>
                <div class="basket-item-block-image border_none m-0 align-self-center col-lg-3 col-md-3 p-3
                <?= (!isset($mobileColumns['PREVIEW_PICTURE']) ? ' d-none d-sm-block' : '') ?>">
                    {{#DETAIL_PAGE_URL}}
                    <a href="{{DETAIL_PAGE_URL}}" class="basket-item-image-link">
                        {{/DETAIL_PAGE_URL}}

                        <img class="basket-item-image w-40" alt="{{NAME}}"
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
            <div class="basket-item-block-info flex flex-col justify-between w-inherit">
                <div>
                    <?php if (isset($mobileColumns['DELETE'])) { ?>
                        <span class="basket-item-actions-remove d-block d-md-none"
                              data-entity="basket-item-delete"></span>
                    <?php } ?>
                    <h2 class="basket-item-info-name">
                        {{#DETAIL_PAGE_URL}}
                        <a href="{{DETAIL_PAGE_URL}}" class="basket-item-info-name-link font_12">
                            {{/DETAIL_PAGE_URL}}

                            <span data-entity="basket-item-name"
                                  class="font-semibold text-lg text-textLight dark:text-textDarkLightGray">{{NAME}}</span>

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
                    <div class="basket-items-list-item-amount mb-2 flex justify-center">
                        <div class="basket-item-block-amount{{#NOT_AVAILABLE}} disabled{{/NOT_AVAILABLE}}"
                             data-entity="basket-item-quantity-block">
                            {{^GIFT}}
                            <span class="basket-item-amount-btn-minus" data-entity="basket-item-quantity-minus"></span>
                            {{/GIFT}}
                            <div class="basket-item-amount-filed-block">
                                <input type="text" class="product-item-amount dark:bg-grayButton bg-textDarkLightGray
                                        focus:border-none text-center border-none text-sm
                                        shadow-none py-2.5 px-3 md:mx-2 mx-1 outline-none rounded-md md:w-14 w-16"
                                       value="{{QUANTITY}}"
                                       {{#NOT_AVAILABLE}} disabled="disabled" {{/NOT_AVAILABLE}}
                                data-value="{{QUANTITY}}" data-entity="basket-item-quantity-field"
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
        <div class="flex row_section items-center justify-between">
            <?php if ($useSumColumn) { ?>
                <div class="flex flex-col">
                    <div class="basket-items-list-item-price mb-3 <?= (!isset($mobileColumns['SUM']) ? ' d-none d-sm-block' : '') ?>">
                        <div class="basket-item-block-price flex flex-col">
                            {{^GIFT}}
                            {{/GIFT}}
                            <div class="basket-item-price-current flex">
                                <span class="basket-item-price-current-text text-xl text-textLight
                                                dark:text-textDarkLightGray font-semibold"
                                      id="basket-item-sum-price-{{ID}}">
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
                    <div class="flex flex-row items-center">
                        <span class="line-through mr-2 text-md font-light text-textLight dark:text-textDarkLightGray">{{{SUM_OLD}}}₽</span>
                        <span class="sale-percent py-1 px-3 text-sm rounded-md bg-light-red dark:bg-light-red text-white font-light "> - {{{SALE_PRICE_VAL}}}₽</span>
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