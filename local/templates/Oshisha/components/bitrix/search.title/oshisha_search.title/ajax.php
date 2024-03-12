<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if (empty($arResult["CATEGORIES"]))
    return;

?>
<div tabindex="0" id="search_results_container"
     class="bx_searche flex flex-col bg-white dark:bg-grayButton rounded-b-xl shadow-lg z-50 relative">
    <?php
    $dbStatistic = CSearchStatistic::GetList(
        array("TIMESTAMP_X" => 'DESC'),
        array("STAT_SESS_ID" => $_SESSION['SESS_SESSION_ID']),
        array('TIMESTAMP_X', 'PHRASE')
    );
    $dbStatistic->NavStart(3);
    $popularSearches = [];
    $component = $this->getComponent();
    while ($arStatistic = $dbStatistic->Fetch()) {
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
    foreach ($arResult["ELEMENTS"] as $searchItem) {
        $uniqueId = $this->GetEditAreaId($searchItem["ID"]);
        $arResult['ELEMENTS'][$searchItem['ID']]['BUY_LINK'] = $uniqueId . '_buy_link';
        $arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_DOWN_ID'] = $uniqueId . '_quant_down';
        $arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_UP_ID'] = $uniqueId . '_quant_up';
        $arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_ID'] = $uniqueId . '_quantity';
        $arResult['ELEMENTS'][$searchItem['ID']]['PRICE_ID'] = $uniqueId . '_price';
        if (empty($searchItem['BASKET_QUANTITY'])) {
            $arResult['ELEMENTS'][$searchItem['ID']]['BASKET_QUANTITY'] = 0;
        }
    }

    foreach ($arResult["CATEGORIES"] as $category_id => $arCategory): ?>
        <?php foreach ($arCategory["ITEMS"] as $i => $arItem): ?>
            <?php if ($category_id === "all"): ?>
                <div class="bx_item_block all_result my-4" onclick="window.location='<? echo $arItem["URL"] ?>';">
                    <p class="bx_item_element">
                        <span class="all_result_title">
                            <a href="<?= $arItem["URL"] ?>"
                               class="text-base mr-3 dark:text-textDarkLightGray text-textLight justify-center
                               font-medium flex flex-row"><?= $arItem["NAME"] ?>
                                <svg width="22" height="24" viewBox="0 0 22 24" fill="none"
                                     class="ml-2 fill-light-red dark:fill-white"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.350112 11.5251C0.350112 13.8045 0.978731 16.0327 2.15647 17.928C3.33422 19.8233 5.00819 21.3005 6.9667 22.1728C8.92522 23.0451 11.0803 23.2733 13.1595 22.8286C15.2386 22.3839 17.1484 21.2863 18.6474 19.6745C20.1464 18.0627 21.1672 16.0091 21.5808 13.7735C21.9944 11.5378 21.7821 9.22054 20.9709 7.11463C20.1596 5.00871 18.7858 3.20876 17.0232 1.94237C15.2606 0.67599 13.1883 6.04534e-05 11.0684 6.04534e-05C8.22575 6.04534e-05 5.49951 1.2143 3.48943 3.37565C1.47936 5.53701 0.350112 8.46843 0.350112 11.5251ZM13.062 7.27233L16.1275 10.7298C16.1728 10.7798 16.2092 10.8384 16.2347 10.9027C16.2802 10.9569 16.3164 11.0193 16.3418 11.0871C16.3985 11.2251 16.4278 11.3742 16.4278 11.5251C16.4278 11.6759 16.3985 11.825 16.3418 11.963C16.2908 12.1045 16.2143 12.2337 16.1168 12.3433L12.9013 15.8008C12.6994 16.0178 12.4257 16.1398 12.1403 16.1398C11.8548 16.1398 11.5811 16.0178 11.3793 15.8008C11.1774 15.5838 11.064 15.2895 11.064 14.9825C11.064 14.6756 11.1774 14.3813 11.3793 14.1643L12.7726 12.6776H6.7811C6.49683 12.6776 6.22421 12.5561 6.0232 12.34C5.82219 12.1239 5.70927 11.8307 5.70927 11.5251C5.70927 11.2194 5.82219 10.9262 6.0232 10.7101C6.22421 10.494 6.49683 10.3726 6.7811 10.3726H12.8477L11.5079 8.86278C11.3117 8.64117 11.2055 8.34486 11.2125 8.03903C11.2196 7.7332 11.3393 7.4429 11.5454 7.23199C11.7515 7.02108 12.0271 6.90685 12.3115 6.91441C12.5959 6.92198 12.8659 7.05072 13.062 7.27233Z"
                                    />
                                </svg>
                            </a>
                        </span>
                    </p>
                    <div style="clear:both;"></div>
                </div>
            <?php elseif (isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):

                $arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];
                if (!empty($arElement['PRICES_CUSTOM']['USER_PRICE']['VALUE'])) {
                    $specialPrice = $arElement['PRICES_CUSTOM']['USER_PRICE'];
                }

                if (!empty($arElement['PRICES_CUSTOM']['SALE_PRICE']['VALUE'])
                    && (!isset($specialPrice) || $arElement['PRICES_CUSTOM']['SALE_PRICE']['VALUE'] < $specialPrice['VALUE'])) {

                    $specialPrice = $arElement['PRICES_CUSTOM']['SALE_PRICE'];
                }

                if (empty($arElement["PICTURE"]["src"])) {
                    $arElement["PICTURE"]["src"] = '/local/templates/Oshisha/images/no-photo.gif';
                }

                ?>
                <div class="bx_item_block py-3 px-4 flex flex-row border-b border-textDarkLightGray dark:border-tagFilterGray
                 justify-between">
                    <div class="bx_img_element mr-3 p-3 bg-white rounded-lg cursor-pointer border
                         border-textDarkLightGray dark:border-tagFilterGray">
                        <div class="bx_image w-16 h-16 bg-contain bg-no-repeat bg-center"
                             style="background-image: url('<?= $arElement["PICTURE"]["src"] ?>')"></div>
                    </div>
                    <div class="bx_item_element flex flex-col justify-between w-full">
                        <p class="mb-3 w-full cursor-pointer">
                            <a href="<?= $arItem["URL"] ?>"
                               class="text-base dark:text-textDarkLightGray text-textLight font-normal dark:font-light">
                                <?= $arElement["NAME"] ?></a>
                        </p>
                        <?php $textButton = 'Забронировать';
                        $classButton = 'btn_basket';
                        if ($arElement['BASKET_QUANTITY'] > 0) {
                            $textButton = 'Забронировано';
                            $classButton = 'addProductDetailButton';
                        } ?>
                        <div id="search_item_<?= $arElement['ID'] ?>" class="bx_item_block_detail">
                            <div class="flex flex-row items-center justify-end">
                            <span class="text-xl mr-3 dark:text-textDarkLightGray text-textLight font-semibold dark:font-medium">
                                <?= $arElement['PRICES']['b2b']['PRINT_VALUE'] ?>
                            </span>

                                <?php if ($arElement['CATALOG_QUANTITY'] > 0): ?>
                                    <div class="flex flex-row items-center
                                    bx_catalog_item bx_catalog_item_controls"
                                        <?= (!$arElement['PRICES']['b2b']['CAN_BUY'] ? ' style="display: none;"' : '') ?>
                                         data-entity="quantity-block">
                                        <div class="product-item-amount-field-contain mr-3 flex flex-row items-center">
                                <span class="btn-minus rounded-full md:py-0 md:px-0 py-3.5 px-1.5
                                                dark:bg-dark md:dark:bg-darkBox bg-none no-select add2basket
                                                cursor-pointer flex items-center justify-center md:h-full h-auto md:w-full w-auto"
                                      data-product_id="<?= $arElement['ID']; ?>"
                                      id="<?= $arElement['QUANTITY_DOWN_ID'] ?>"
                                      data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] ?>"
                                      tabindex="0">
                                    <svg width="30" height="2.3" viewBox="0 0 22 2" fill="none"
                                         class="stroke-dark dark:stroke-white stroke-2"
                                         xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1H21" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                </span>
                                            <div class="product-item-amount-field-block">
                                                <input class="product-item-amount card_element cat-det
                                         dark:bg-tagFilterGray bg-textDarkLightGray cursor-pointer
                                    focus:border-none text-center border-none text-base
                                     shadow-none py-3 px-2.5 mx-2 outline-none rounded-md w-14"
                                                       id="<?= $arElement['QUANTITY_ID'] ?>"
                                                       type="number" value="<?= $arElement['BASKET_QUANTITY'] ?>"
                                                       data-product_id="<?= $arElement['ID']; ?>"
                                                       data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] ?>"/>
                                            </div>
                                            <span class=" cursor-pointer flex items-center justify-center
                                                    rounded-full md:p-0 p-3 md:h-full h-auto md:w-full w-auto btn-plus
                                                    no-select plus_icon add2basket basket_prod_detail"
                                                  data-max-quantity="<?= $arElement['CATALOG_QUANTITY'] ?>"
                                                  data-product_id="<?= $arElement['ID']; ?>"
                                                  id="<?= $arElement['QUANTITY_UP_ID'] ?>" tabindex="0">
                                                <svg width="20" height="20" viewBox="0 0 20 20"
                                                     class="fill-light-red dark:fill-white"
                                                     xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18.8889 11.111H1.11111C0.503704 11.111 0 10.6073 0 9.9999C0 9.3925 0.503704 8.88879 1.11111 8.88879H18.8889C19.4963 8.88879 20 9.3925 20 9.9999C20 10.6073 19.4963 11.111 18.8889 11.111Z"></path>
                                            <path d="M10 20C9.39262 20 8.88892 19.4963 8.88892 18.8889V1.11111C8.88892 0.503704 9.39262 0 10 0C10.6074 0 11.1111 0.503704 11.1111 1.11111V18.8889C11.1111 19.4963 10.6074 20 10 20Z"></path>
                                        </svg>
                                </span>
                                        </div>
                                        <div id="result_box"></div>
                                        <div id="popup_mess"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-4 d-flex justify-content-between align-items-center">
                                        <div class="not_product detail_popup text-xs dark:text-textDark text-white font-medium
                                dark:bg-dark-red bg-light-red py-2 px-2.5 rounded-full text-center cursor-pointer
                                                                subscribed">
                                            <svg width="18" height="17" class="stroke-white
                                subscribed" viewBox="0 0 34 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M25.5762 11.0001C25.5762 8.81209 24.6884 6.71367 23.1081 5.16649C21.5279 3.61932 19.3846 2.75012 17.1498 2.75012C14.915 2.75012 12.7717 3.61932 11.1915 5.16649C9.61121 6.71367 8.72344 8.81209 8.72344 11.0001C8.72344 20.6251 4.51025 23.3751 4.51025 23.3751H29.7894C29.7894 23.3751 25.5762 20.6251 25.5762 11.0001Z"
                                                      stroke-width="3" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                                <path d="M19.5794 28.875C19.3325 29.2917 18.9781 29.6376 18.5517 29.8781C18.1253 30.1186 17.6419 30.2451 17.1498 30.2451C16.6577 30.2451 16.1743 30.1186 15.7479 29.8781C15.3215 29.6376 14.9671 29.2917 14.7202 28.875"
                                                      stroke-width="3" stroke-linecap="round"
                                                      stroke-linejoin="round"></path>
                                            </svg>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="alert_quantity" data-id="<?= $arElement['ID'] ?>"></div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php if (!empty($popularSearches)): ?>

        <div class="bx_item_block popular_searches_title" onclick="">
            <span class="text-lg ml-4 dark:text-textDarkLightGray text-textLight font-medium mb-3">Популярные запросы</span>
        </div>
        <div class="flex flex-row flex-wrap mb-5">
            <?php foreach ($popularSearches as $popularSearch): ?>
                <div class="bx_item_block popular_searches_result px-4 py-1 m-2"
                     onclick="popularSearchResultSubmit(this)">
                    <div class="bx_item_element"
                         onclick="window.location='<?= $arResult["FORM_ACTION"] . '?q=' . $popularSearch["PHRASE"] ?>';"
                    >
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <span class="popular_search_title">
                        <a href="<?= $arResult["FORM_ACTION"] . '?q=' . $popularSearch["PHRASE"] ?>"
                           class="text-sm mb-3 dark:text-textDarkLightGray text-textLight font-light underline">
                            <?= $popularSearch["PHRASE"] ?>
                        </a>
                    </span>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
    $('#search_results_container').focusout(function () {
        if (!event.relatedTarget
            || ((event.relatedTarget.getAttribute('id') != 'input_search_desktop')
                && ($('#search_results_container').find(event.relatedTarget).length != 1))) {
            setTimeout(function () {
                $('#search_results_container').parent().css("display", "none");
            }, 250);
        }
    })
</script>
