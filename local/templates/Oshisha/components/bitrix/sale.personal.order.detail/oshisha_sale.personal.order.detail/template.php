<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;
use Bitrix\Sale\Fuser;
use Enterego\EnteregoHelper;

if ($arParams['GUEST_MODE'] !== 'Y') {
    Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/script.js");
    Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/style.css");
}
CJSCore::Init(array('clipboard', 'fx'));
/** @var CUser|CAllUser $USER
 */

$orderIsNotActiveItemsPresent = false;
foreach ($arResult["BASKET"] as $orderBasketItem) {
    $product = CIBlockElement::GetByID($orderBasketItem['PRODUCT_ID'])->GetNext();
    if ($product['ACTIVE'] == 'N') {
        $orderIsNotActiveItemsPresent = true;
        break;
    }
}


$APPLICATION->SetTitle("");

if (!empty($arResult['ERRORS']['FATAL'])) {
    $component = $this->__component;
    foreach ($arResult['ERRORS']['FATAL'] as $code => $error) {
        if ($code !== $component::E_NOT_AUTHORIZED) {
            ShowError($error);
        }
    }


    if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED])) {
        $userName = $arResult["USER_NAME"];
        $paymentData[$payment['ACCOUNT_NUMBER']] = array(
            "payment" => $payment['ACCOUNT_NUMBER'],
            "order" => $arResult['ACCOUNT_NUMBER'],
            "allow_inner" => $arParams['ALLOW_INNER'],
            "only_inner_full" => $arParams['ONLY_INNER_FULL'],
            "refresh_prices" => $arParams['REFRESH_PRICES'],
            "path_to_payment" => $arParams['PATH_TO_PAYMENT']
        );
        $paymentSubTitle = Loc::getMessage('SPOD_TPL_BILL') . " " . Loc::getMessage('SPOD_NUM_SIGN') . $payment['ACCOUNT_NUMBER'];
        if (isset($payment['DATE_BILL'])) {
            $paymentSubTitle .= " " . Loc::getMessage('SPOD_FROM') . " " . $payment['DATE_BILL_FORMATED'];
        }
        $paymentSubTitle .= ",";
        ?>
        <div class="alert alert-danger"><?= $arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED] ?></div>
        <?php $authListGetParams = array(); ?>
        <?php $APPLICATION->AuthForm('', false, false, 'N', false); ?>
    <?php }
} else {
    if (!empty($arResult['ERRORS']['NONFATAL'])) {
        foreach ($arResult['ERRORS']['NONFATAL'] as $error) {
            ShowError($error);
        }
    } ?>
    <div class="sale-order-detail">
        <div class="">
            <?php if ($arParams['GUEST_MODE'] !== 'Y') { ?>
                <div class="flex flex-row items-center mb-3">
                    <svg width="30" height="31" viewBox="0 0 34 35" class="mr-3" xmlns="http://www.w3.org/2000/svg">
                        <path class="fill-light-red dark:fill-white"
                              d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"></path>
                    </svg>
                    <a href="<?= htmlspecialcharsbx($arResult["URL_TO_LIST"]) ?>"
                       class="font-medium text-sm dark:text-textDarkLightGray text-lightGrayBg link_home_orders">
                        К списку заказов</a>
                </div>
            <?php } ?>
            <div class="mb-3 border-2 border-textDark dark:border-darkBox rounded-xl">
                <div class="sale-order-detail-card">
                    <div class="title_order_detail mb-4">
                        <div class="flex-row flex bg-textDark dark:bg-darkBox mb-5 xl:p-8 md:p-5 p-3">
                            <div class="flex-row flex w-1/2">
                                <h4 class="mb-3 font-semibold text-xl dark:text-textDarkLightGray text-lightGrayBg"><b>
                                        Заказ № <?= $arResult["ACCOUNT_NUMBER"] ?></b></h4>
                                <p class="sale-order-detail-props mb-3">
                                    <b>от <?= $arResult["DATE_INSERT_FORMATED"] ?></b></p>
                            </div>
                            <span class="mb-1 w-1/2 text-sm font-medium p-4 bg-yellowSt">
                                <?= $arResult["STATUS"]["NAME"] ?>
                            </span>
                        </div>
                        <div class="flex flex-col custom_item xl:p-8 md:p-5 p-3">
                            <div class="flex flex-col custom_item pr-1">
                                <span class="mb-1"><b class="mr-1">Товаров:</b>
                                    <?= count($arResult['BASKET']); ?> </span>
                                <span class="mb-1">
                                    <b class="mr-1">Сумма доставки:</b>
                                        <?php
                                        $deliveryPrice = 0;
                                        foreach ($arResult['SHIPMENT'] as $shipment) {
                                            $deliveryPrice += $shipment["PRICE_DELIVERY"];
                                        }
                                        echo htmlspecialcharsbx(CurrencyFormat($deliveryPrice, $arResult['CURRENCY'])); ?>
                                </span>
                            </div>
                            <div class="flex flex-col custom_item">
                                <span class="mb-1"> <b
                                            class="mr-1">Способ доставки:</b>
                                    <?php foreach ($arResult['SHIPMENT'] as $shipment) {
                                        echo htmlspecialcharsbx($shipment["DELIVERY_NAME"]);
                                    } ?>
                                </span>
                                <span class="mb-1"> <b class="mr-1">Способ оплаты: </b> <?php
                                    foreach ($arResult['PAYMENT'] as $payment) {
                                        echo $payment['PAY_SYSTEM_NAME'];
                                    } ?>
                                </span>
                                <span class="mb-1">
                                    <b class="mr-1">Получатель: </b>
                                        <?php
                                        if ($userName <> '') {
                                            echo htmlspecialcharsbx($userName);
                                        } elseif (mb_strlen($arResult['FIO'])) {
                                            echo htmlspecialcharsbx($arResult['FIO']);
                                        } else {
                                            echo htmlspecialcharsbx($arResult["USER"]['LOGIN']);
                                        } ?>
                                </span>
                            </div>
                            <div class="flex flex-col custom_item">
                                <a href="<?= $arResult["URL_TO_COPY"] ?>"
                                   class="link_repeat_orders sale-order-list-repeat-link mb-1 <?= empty($arResult['BASKET_ITEMS']) ? 'js--basket-empty' : 'js--basket-not-empty' ?> <?= $orderIsNotActiveItemsPresent === true ? 'js--not-active' : '' ?>">
                                    <?= Loc::getMessage('SPOD_ORDER_REPEAT') ?></a>
                                <span class="mb-1"> <b
                                            class="mr-1">ИТОГО:</b>  <?= $arResult["PRICE_FORMATED"] ?> </span>
                                <div id="popup_mess_order_copy"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 mb-3 xl:p-8 md:p-5 p-3">
                        <div class="col">
                            <?php
                            $id_USER = $USER->GetID();
                            $FUser_id = Fuser::getId($id_USER);
                            $item_id = [];

                            foreach ($arResult['BASKET'] as $basketItem) {
                                $item_id[] = $basketItem['ID'];
                            }

                            $count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);

                            foreach ($arResult['BASKET'] as $basketItem) {

                                foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                                    $basketItem['COUNT_LIKES'] = $count;
                                }

                                foreach ($count_likes['USER'] as $keyLike => $count) {
                                    if ($keyLike == $basketItem['ID']) {
                                        $basketItem['COUNT_LIKE'] = $count['Like'][0];
                                        $basketItem['COUNT_FAV'] = $count['Fav'][0];
                                    }
                                }
                                $areaId = $basketItem['AREA_ID'];

                                $itemIds = array(
                                    'ID' => $areaId,
                                    'PICT' => $areaId . '_pict',
                                    'SECOND_PICT' => $areaId . '_secondpict',
                                    'PICT_SLIDER' => $areaId . '_pict_slider',
                                    'STICKER_ID' => $areaId . '_sticker',
                                    'SECOND_STICKER_ID' => $areaId . '_secondsticker',
                                    'QUANTITY' => $areaId . '_quantity',
                                    'QUANTITY_DOWN' => $areaId . '_quant_down',
                                    'QUANTITY_UP' => $areaId . '_quant_up',
                                    'QUANTITY_MEASURE' => $areaId . '_quant_measure',
                                    'QUANTITY_LIMIT' => $areaId . '_quant_limit',
                                    'BUY_LINK' => $areaId . '_buy_link',
                                    'BASKET_ACTIONS' => $areaId . '_basket_actions',
                                    'NOT_AVAILABLE_MESS' => $areaId . '_not_avail',
                                    'SUBSCRIBE_LINK' => $areaId . '_subscribe',
                                    'COMPARE_LINK' => $areaId . '_compare_link',
                                    'PRICE' => $areaId . '_price',
                                    'PRICE_OLD' => $areaId . '_price_old',
                                    'PRICE_TOTAL' => $areaId . '_price_total',
                                    'DSC_PERC' => $areaId . '_dsc_perc',
                                    'SECOND_DSC_PERC' => $areaId . '_second_dsc_perc',
                                    'PROP_DIV' => $areaId . '_sku_tree',
                                    'PROP' => $areaId . '_prop_',
                                    'DISPLAY_PROP_DIV' => $areaId . '_sku_prop',
                                    'BASKET_PROP_DIV' => $areaId . '_basket_prop',
                                );
                                $url = $basketItem['DETAIL_PAGE_URL'];
                                if (!empty($basketItem['PARENT'])) {
                                    $url = '/catalog/product/' . CIBlockElement::GetByID($basketItem['PARENT']['ID'])->Fetch()['CODE'] . '/';
                                } ?>
                                <div class="flex flex-row justify-between mb-5">
                                    <div class="flex flex-row">
                                        <div class="sale-order-detail-order-item-img-block mr-4 ">
                                            <a href="<?= $url ?>">
                                                <?php
                                                if ($basketItem['PICTURE']['SRC'] <> '') {
                                                    $imageSrc = $basketItem['PICTURE']['SRC'];
                                                } else {
                                                    $imageSrc = '/local/templates/Oshisha/images/no-photo.gif';
                                                }
                                                ?>
                                                <img class="sale-order-detail-order-item-img-container"
                                                     src="<?= $imageSrc ?>"/>
                                            </a>
                                        </div>
                                        <div class="sale-order-detail-order-item-properties flex flex-col
                                               items-start justify-between mb-2"
                                             style="min-width: 250px;">
                                            <div class="mb-2">
                                                <a class="sale-order-detail-order-item-title mb-3"
                                                   href="<?= $url ?>"><?= htmlspecialcharsbx($basketItem['NAME']) ?></a>
                                                <? if (isset($basketItem['PROP']) && is_array($basketItem['PROP'])) {
                                                    foreach ($basketItem['PROP'] as $itemProps) { ?>
                                                        <div
                                                                class="sale-order-detail-order-item-properties-type">
                                                            <?= htmlspecialcharsbx($itemProps) ?></div>
                                                        <?
                                                    }
                                                } ?>
                                            </div>
                                            <?php $res = EnteregoHelper::getItems($basketItem['PRODUCT_ID'],
                                                PROPERTY_KEY_VKUS);
                                            if (!empty($res)) {
                                                ?>
                                                <div class="variation_taste mb-5"
                                                     id="<?= count($res[PROPERTY_KEY_VKUS]); ?>">
                                                    <?php foreach ($res[PROPERTY_KEY_VKUS] as $key) { ?>
                                                        <span class="taste"
                                                              data-background="#<?= $key['VALUE'] ?>"
                                                              id="<?= $key['ID'] ?>">
                                                                <?= $key['NAME'] ?>
                                                            </span>
                                                        <?php
                                                    } ?>
                                                </div>
                                            <?php } ?>
                                            <div>
                                                <div class="sale-order-detail-order-item-properties text-right">
                                                    <strong class="bx-price"><?= $basketItem['FORMATED_SUM'] ?>
                                                        x <?= $basketItem['QUANTITY'] ?> шт.</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="align-self-end flex">
                                            <div class="sale-order-detail-order-item-properties flex flex-row">
                                                <div class="product-item-amount-field-contain flex
                                                    flex-row items-center">
                                                        <span class="btn-minus  minus_icon no-select"
                                                              id="<?= $itemIds['QUANTITY_DOWN_ID'] ?>"><span
                                                                    class="minus_icon"></span></span>
                                                    <div class="product-item-amount-field-block">
                                                        <input class="product-item-amount card_element"
                                                               id="<?= $itemIds['QUANTITY_ID'] ?>"
                                                               type="number"
                                                               value="<?= $basketItem['QUANTITY'] ?>">
                                                    </div>
                                                    <a class="btn-plus plus_icon no-select add2basket"
                                                       id="<? echo $itemIds['BUY_LINK']; ?>"
                                                       href="javascript:void(0)"
                                                       data-url="<?= $basketItem['DETAIL_PAGE_URL'] ?>"
                                                       data-product_id="<?= $basketItem['PRODUCT_ID']; ?>"
                                                       title="Добавить в корзину"></a>
                                                </div>
                                                <!--                                                    <div class="product-item-amount-field-contain">-->
                                                <!--                                                        <span class="btn-minus no-select minus_icon "-->
                                                <!--                                                              id="-->
                                                <? //= $itemIds['QUANTITY_DOWN_ID'] ?><!--"></span>-->
                                                <!--                                                        <div class="product-item-amount-field-block">-->
                                                <!--                                                            <input class="product-item-amount"-->
                                                <!--                                                                   id="-->
                                                <? //= $itemIds['QUANTITY_ID'] ?><!--" type="number"-->
                                                <!--                                                                   value="-->
                                                <? //= $price['MIN_QUANTITY'] ?><!--">-->
                                                <!--                                                        </div>-->
                                                <!--                                                        <span class="btn-plus no-select plus_icon"-->
                                                <!--                                                              id="-->
                                                <? //= $itemIds['QUANTITY_UP_ID'] ?><!--"></span>-->
                                                <!--                                                    </div>-->
                                                <a id="<?= $basketItem['BUY_LINK']; ?>"
                                                   href="javascript:void(0)"
                                                   rel="nofollow"
                                                   class="btn_basket add2basket basket_prod_detail"
                                                   data-url="<?= $url ?>"
                                                   data-product_id="<?= $basketItem['ID']; ?>"
                                                   title="Добавить в корзину">Забронировать</a>
                                            </div>
                                        </div>
                                        <div class="box_with_net ml-3">
                                            <?php
                                            /**
                                             * @var CatalogSectionComponent $component
                                             */
                                            $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                                'templates',
                                                array(
                                                    'ID_PROD' => $basketItem['ID'],
                                                    'F_USER_ID' => $FUser_id,
                                                    'LOOK_LIKE' => true,
                                                    'LOOK_FAVORITE' => true,
                                                    'COUNT_LIKE' => $basketItem['COUNT_LIKE'],
                                                    'COUNT_FAV' => $basketItem['COUNT_FAV'],
                                                    'COUNT_LIKES' => $basketItem['COUNT_LIKES'],
                                                ),
                                                $component,
                                                array('HIDE_ICONS' => 'Y')
                                            );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $javascriptParams = array(
        "url" => CUtil::JSEscape($this->__component->GetPath() . '/ajax.php'),
        "templateFolder" => CUtil::JSEscape($templateFolder),
        "templateName" => $this->__component->GetTemplateName(),
        "paymentList" => $paymentData,
        "returnUrl" => $arResult['RETURN_URL'],
    );
    $javascriptParams = CUtil::PhpToJSObject($javascriptParams); ?>
    <script>
        BX.Sale.PersonalOrderComponent.PersonalOrderDetail.init(<?=$javascriptParams?>);
    </script>
<?php }

