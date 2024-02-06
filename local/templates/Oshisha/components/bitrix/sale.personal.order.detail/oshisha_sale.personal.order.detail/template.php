<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;
use Bitrix\Sale\Fuser;
use Enterego\contragents\EnteregoContragents;
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
    }
    $classStatus = '';

    $contrAgentId = 0;
    foreach($arResult['ORDER_PROPS'] as $prop){
        if($prop['CODE'] === 'CONTRAGENT_ID'){
            $contrAgentId = $prop['VALUE'];
        }
    }

    $contrAgent = EnteregoContragents::getContrAgentNameOnOrder($contrAgentId);
    if ($arResult['STATUS_ID'] === 'N') {
        $classStatus = 'status_pending_payment bg-yellowSt text-black font-medium';
    } else if ($arResult['STATUS_ID'] === 'P') {
        $classStatus = 'status_payment_yes bg-tagFilterGray text-black font-medium';
    } else if ($arResult['STATUS_ID'] === 'F') {
        $classStatus = 'status_completed bg-greenLight dark:bg-greenButton text-white font-normal';
    } ?>
    <div class="sale-order-detail">
        <?php if ($arParams['GUEST_MODE'] !== 'Y') { ?>
            <div class="flex flex-row items-center mb-5">
                <svg width="25" height="26" viewBox="0 0 34 35" class="mr-3" xmlns="http://www.w3.org/2000/svg">
                    <path class="fill-light-red dark:fill-white"
                          d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"></path>
                </svg>
                <a href="<?= htmlspecialcharsbx($arResult["URL_TO_LIST"]) ?>"
                   class="font-medium text-sm dark:text-textDarkLightGray text-lightGrayBg link_home_orders">
                    К списку заказов</a>
            </div>
        <?php } ?>
        <div class="md:p-5">
            <div class="mb-3">
                <div class="sale-order-detail-card">
                    <div class="title_order_detail">
                        <div class="md:flex-row flex-col-reverse flex bg-textDark dark:bg-darkBox md:px-8 px-0 xl:py-3 md:py-3 rounded-t-3xl
                      relative">
                            <div class="flex-row flex md:w-1/2 items-center w-full md:justify-start justify-center">
                                <h4 class="font-semibold dark:font-medium xl:text-2xl text-base mb-0 text-textLight mr-3 md:p-0 p-3
                                 dark:text-textDarkLightGray">
                                    Заказ № <?= $arResult["ACCOUNT_NUMBER"] ?>
                                </h4>
                                <p class="sale-order-detail-props font-semibold xl:text-lg text-sm mb-0
                                dark:font-medium dark:text-textDarkLightGray text-textLight">
                                    от <?= $arResult["DATE_INSERT_FORMATED"] ?>
                                </p>
                            </div>
                            <span class="mb-1 md:w-1/2 xl:text-lg text-sm font-medium h-full md:absolute top-0 right-0
                            rounded-bl-none md:rounded-tr-3xl md:max-w-[500px] md:min-w-[200px] rounded-tl-3xl md:p-0 p-2
                            w-full flex items-center justify-center md:rounded-tl-none md:rounded-bl-3xl rounded-tr-3xl <?= $classStatus ?>">
                                <?= $arResult["STATUS"]["NAME"] ?>
                            </span>
                        </div>
                    </div>
                    <div class="border-2 border-t-0 border-textDark dark:border-darkBox md:rounded-b-[2rem] rounded-b-xl">
                        <div class="flex md:flex-row flex-col xl:px-8 xl:py-6 md:px-5 md:py-5 px-3 py-3 justify-between">
                            <div class="flex flex-col">
                                <div class="flex flex-col pr-1">
                                    <p class="mb-3 md:text-base text-xs font-normal dark:font-light text-textLight
                                     dark:text-textDarkLightGray">
                                        <span class="mr-2 font-semibold dark:font-light">Товаров:</span>
                                        <?= count($arResult['BASKET']); ?>
                                    </p>
                                    <p class="mb-3 md:text-base text-xs font-normal dark:font-light dark:text-textDarkLightGray text-textLight">
                                        <span class="mr-2 font-semibold dark:font-light">Сумма доставки:</span>
                                        <?php $deliveryPrice = 0;
                                        foreach ($arResult['SHIPMENT'] as $shipment) {
                                            $deliveryPrice += $shipment["PRICE_DELIVERY"];
                                        }
                                        echo htmlspecialcharsbx(CurrencyFormat($deliveryPrice, $arResult['CURRENCY'])); ?>
                                    </p>
                                </div>
                                <div class="flex flex-col">
                                    <p class="mb-3 md:text-base text-xs md:block flex flex-col dark:text-textDarkLightGray
                                    text-textLight">
                                        <span class="mr-2 md:mb-0 mb-1 md:text-base text-xs font-semibold dark:font-light">Способ доставки:</span>
                                        <span class="font-normal dark:font-light">
                                            <?php foreach ($arResult['SHIPMENT'] as $shipment) {
                                                echo htmlspecialcharsbx($shipment["DELIVERY_NAME"]);
                                            } ?>
                                        </span>
                                    </p>
                                    <p class="mb-3 md:text-base text-xs md:block flex flex-col dark:text-textDarkLightGray
                                    text-textLight">
                                        <span class="mr-2 md:mb-0 mb-1 md:text-base text-xs font-semibold dark:font-light">
                                            Способ оплаты: </span>
                                        <span class="font-normal dark:font-light">
                                        <?php foreach ($arResult['PAYMENT'] as $payment) {
                                            echo $payment['PAY_SYSTEM_NAME'];
                                        } ?>
                                        </span>
                                    </p>
                                    <p class="mb-3 md:text-base text-xs font-normal dark:font-light dark:text-textDarkLightGray text-textLight">
                                        <span class="mr-2 font-semibold dark:font-light">Получатель: </span>
                                        <?php if ($userName <> '') {
                                            echo htmlspecialcharsbx($userName);
                                        } elseif (mb_strlen($arResult['FIO'])) {
                                            echo htmlspecialcharsbx($arResult['FIO']);
                                        } else {
                                            echo htmlspecialcharsbx($arResult["USER"]['LOGIN']);
                                        } ?>
                                    </p>
                                    <p class="mb-3 md:text-base text-xs font-normal dark:font-light dark:text-textDarkLightGray text-textLight">
                                        <span class="mr-2 font-semibold dark:font-light">Контрагент: </span>
                                        <?= !empty($contrAgent['NAME_ORGANIZATION']) ? $contrAgent['NAME_ORGANIZATION']
                                            : 'Поле не заполнено, обратитесь к менеджеру' ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex md:flex-col flex-col-reverse justify-between mb-3 items-end">
                                <a href="<?= $arResult["URL_TO_COPY"] ?>"
                                   class="link_repeat_orders sale-order-list-repeat-link md:px-5 px-3 py-3
                                    dark:bg-dark-red rounded-md bg-light-red dark:shadow-md shadow-shadowDark w-full
                                     ark:hover:bg-hoverRedDark cursor-pointer flex items-center justify-center
                                     <?= empty($arResult['BASKET_ITEMS']) ? 'js--basket-empty' : 'js--basket-not-empty' ?>
                                     <?= $orderIsNotActiveItemsPresent === true ? 'js--not-active' : '' ?>">
                                    <svg viewBox="0 0 19 22" fill="none"
                                         xmlns="http://www.w3.org/2000/svg" class="mr-2 md:w-6 w-4 md:h-6 h-5">
                                        <path d="M9.49743 0.666656V3.63808C4.7569 3.63808 0.893433 7.64088 0.893433 12.5524C0.893433 17.4639 4.7569 21.4667 9.49743 21.4667C14.238 21.4667 18.1014 17.4639 18.1014 12.5524C18.1014 10.6011 17.485 8.79763 16.4527 7.32722L15.0897 8.73942C15.7835 9.83239 16.1894 11.1409 16.1894 12.5524C16.1894 16.3933 13.2046 19.4857 9.49743 19.4857C5.79022 19.4857 2.80543 16.3933 2.80543 12.5524C2.80543 8.71146 5.79022 5.61904 9.49743 5.61904V8.59047L14.2774 4.62856L9.49743 0.666656Z"
                                              fill="white"/>
                                    </svg>
                                    <span class="text-white md:text-[15px] text-xs">
                                        <?= Loc::getMessage('SPOD_ORDER_REPEAT') ?>
                                    </span>
                                </a>
                                <p class="font-bold dark:font-medium xl:text-2xl text-lg md:mb-0 mb-3 text-textLight mr-3
                                 dark:text-textDarkLightGray">
                                    Итого: <?= $arResult["PRICE_FORMATED"] ?>
                                </p>
                            </div>
                        </div>
                        <div class="xl:px-8 md:px-5 p-3 flex flex-col">
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
                                <div class="justify-between rounded-xl p-3 md:p-5 dark:bg-darkBox bg-textDark md:mb-3
                                mb-2 flex flex-row">
                                    <div class="flex flex-row md:w-auto w-11/12">
                                        <div class="sale-order-detail-order-item-img-block  md:h-auto md:p-3 p-2 h-fit
                                         bg-white min-w-max rounded-xl flex justify-center align-center md:mr-7 mr-2">
                                            <a href="<?= $url ?>" class="h-fit">
                                                <?php
                                                if ($basketItem['PICTURE']['SRC'] <> '') {
                                                    $imageSrc = $basketItem['PICTURE']['SRC'];
                                                } else {
                                                    $imageSrc = '/local/templates/Oshisha/images/no-photo.gif';
                                                }
                                                ?>
                                                <img class="sale-order-detail-order-item-img-container
                                                     md:h-28 md:w-28 w-12 h-12 object-contain"
                                                     src="<?= $imageSrc ?>"/>
                                            </a>
                                        </div>
                                        <div class="sale-order-detail-order-item-properties flex flex-col
                                               items-start justify-between mb-2 relative w-full"
                                             style="min-width: 250px;">
                                            <div class="mb-2">
                                                <a class="sale-order-detail-order-item-title font-medium
                                                dark:font-light md:text-lg text-textLight text-xs
                                                dark:text-textDarkLightGray md:line-clamp-none line-clamp-1"
                                                   href="<?= $url ?>"><?= htmlspecialcharsbx($basketItem['NAME']) ?></a>
                                                <?php if (isset($basketItem['PROP']) && is_array($basketItem['PROP'])) {
                                                    foreach ($basketItem['PROP'] as $itemProps) { ?>
                                                        <div class="sale-order-detail-order-item-properties-type">
                                                            <?= htmlspecialcharsbx($itemProps) ?></div>
                                                        <?php
                                                    }
                                                } ?>
                                            </div>
                                            <div>
                                                <div class="sale-order-detail-order-item-properties text-right">
                                                    <strong class="bx-price font-bold  dark:font-medium md:text-xl
                                                    text-textLight text-sm dark:text-textDarkLightGray"><?= $basketItem['FORMATED_SUM'] ?>
                                                        x <?= $basketItem['QUANTITY'] ?> шт.</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="h-auto box_with_net ml-1 flex flex-col justify-between items-center md:w-auto w-1/12">
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
                                                'LOOK_FAVORITE' => false,
                                                'COUNT_LIKE' => $basketItem['COUNT_LIKE'],
                                                'COUNT_FAV' => $basketItem['COUNT_FAV'],
                                                'COUNT_LIKES' => $basketItem['COUNT_LIKES'],
                                            ),
                                            $component,
                                            array('HIDE_ICONS' => 'Y')
                                        );
                                        ?>
                                        <div class="mt-3">
                                            <?php
                                            /**
                                             * @var CatalogSectionComponent $component
                                             */
                                            $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                                'templates',
                                                array(
                                                    'ID_PROD' => $basketItem['ID'],
                                                    'F_USER_ID' => $FUser_id,
                                                    'LOOK_LIKE' => false,
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

