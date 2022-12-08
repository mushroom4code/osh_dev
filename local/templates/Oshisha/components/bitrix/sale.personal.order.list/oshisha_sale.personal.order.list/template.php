<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/script.js");
Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/style.css");
CJSCore::Init(array('clipboard', 'fx'));

Loc::loadMessages(__FILE__);

function showOrderBlock($arResult, $accountNumber){
    foreach ($accountNumber as $key => $order) {

        $classStatus = '';

        if ($order['STATUS_ID'] === 'N') {
            $classStatus = 'status_pending_payment';
        } else if ($order['STATUS_ID'] === 'P') {
            $classStatus = 'status_payment_yes';
        } else if ($order['STATUS_ID'] === 'F') {
            $classStatus = 'status_completed';
        }
        ?>
        <div class="row mx-0 mb-5 sale-order-list-inner-container">
            <div class="row mx-0 sale-order-list-title-container">
                <h3 class="mb-1 mt-1 title-orders-his">
                    <div>
                        <?= Loc::getMessage('SPOL_TPL_ORDER') ?>
                        <?= Loc::getMessage('SPOL_TPL_NUMBER_SIGN') . $order['ACCOUNT_NUMBER'] ?>
                        <span><?= Loc::getMessage('SPOL_TPL_FROM_DATE') ?>&nbsp<?= $order['DATE_INSERT_FORMAT'] ?></span>
                        <?php
                        if ($order['PAYED'] === 'N' && $order['CANCELED'] === 'N') {
                            echo '<span class="taste" style="background-color: rgb(245, 95, 92); color: black;">Новый</span>';
                        }
                        if ($order['PAYED'] === 'Y') {
                            echo '<span class="taste" style="background-color: rgb(245, 95, 92); color: black;">Оплачен</span>';
                        }
                        if ($order['CANCELED'] === 'Y') {
                            echo '<span class="taste" style="background-color: rgb(245, 95, 92); color: black;">Ожидает отмены</span>';
                        }
                        ?>
                    </div>
                    <div>
                        <span class="<?php echo $classStatus ?>"><?= htmlspecialcharsbx($arResult['INFO']['STATUS'][$order['STATUS_ID']]['NAME']) ?></span>
                    </div>
                </h3>
            </div>
            <div class="box_wth_delivery_number">
                <div class="mt-2" style="display:none;">
                    <span>Номер отслеживания:</span> <a href="#">24006875</a>
                </div>
            </div>
            <?php
            if (count($order['PICTURE']) <= 4) {
                $class_box = 'justify-content-evenly';
            } else {
                $class_box = 'justify-content-between';
            } ?>
            <div class="row mx-0 mb-4 mt-4 d-flex flex_class <?= $class_box ?>">
                <?php
                foreach ($order['PICTURE'] as $item => $url) {
                    if (!empty($url)) {
                        ?>
                        <img class="image_box_orders" src="<?= $url ?>"/>
                    <? } else { ?>
                        <img class="image_box_orders"
                             src="/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/images/no_photo.png"/>
                    <?php }
                } ?>
            </div>
            <div class="col pt-3  wrap-order-l">
                <div class="sale-order-list-inner-row sale-order-list-inner-row sale-order-list-wrap">
                    <div class="sale-order-list-inner-row">
                        <div class=" sale-order-list-about-container">
                            <a class="sale-order-list-about-link"
                               href="/personal/orders/<?= $order['ACCOUNT_NUMBER'] ?>">Подробности
                                заказа</a>
                        </div>

                        <div class=" sale-order-list-repeat-container">
                            <a class=" sale-order-list-repeat-link"
                               href="/personal/cart/"><?= Loc::getMessage('SPOL_TPL_REPEAT_ORDER') ?></a>
                        </div>
                        <?
                        if ($order['CAN_CANCEL'] !== 'N') {
                            ?>
                            <div class=" sale-order-list-cancel-container">
                                <a class="sale-order-list-cancel-link"
                                   href="/personal/cancel/<?= $order['ACCOUNT_NUMBER'] ?>?CANCEL=Y"><?= Loc::getMessage('SPOL_TPL_CANCEL_ORDER') ?></a>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                    <div class="sale-order-list-inner">
                        <div class="sale-order-list-inner-row-body">
                            <div class="sale-order-list-payment">
                                <div class="mb-1 sale-order-list-payment-price">
                                    <span class="sale-order-list-payment-element">Сумма заказа:</span>
                                    <span class="sale-order-list-payment-number"><?= $order['PRICE'] . ' ₽' ?> </span>
                                </div>
                            </div>
                        </div>
                        <div class="col sale-order-list-inner-row-template">
                            <a class="sale-order-list-cancel-payment" href="">
                                <i class="fa fa-long-arrow-left"></i> <?= Loc::getMessage('SPOL_CANCEL_PAYMENT') ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

}

if (!empty($arResult['ERRORS']['FATAL'])) {
    foreach ($arResult['ERRORS']['FATAL'] as $code => $error) {
        if ($code !== $component::E_NOT_AUTHORIZED)
            ShowError($error);
    }
    $component = $this->__component;
    if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED])) {
        ?>
        <div class="row">
            <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                <div class="alert alert-danger"><?= $arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED] ?></div>
            </div>
            <? $authListGetParams = array(); ?>
            <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                <? $APPLICATION->AuthForm('', false, false, 'N', false); ?>
            </div>
        </div>
        <?
    }

} else {
    if (!empty($arResult['ERRORS']['NONFATAL'])) {
        foreach ($arResult['ERRORS']['NONFATAL'] as $error) {
            ShowError($error);
        }
    }
    if (!count($arResult['ORDERS'])) {
        if ($_REQUEST["filter_history"] == 'Y') {
            if ($_REQUEST["show_canceled"] == 'Y') {
                ?>
                <h3><?= Loc::getMessage('SPOL_TPL_EMPTY_CANCELED_ORDER') ?></h3>
                <?
            } else {
                ?>
                <h3 style="display:none;"><?= Loc::getMessage('SPOL_TPL_EMPTY_HISTORY_ORDER_LIST') ?></h3>
                <?
            }
        } else {
            ?>
            <h3 ><?= Loc::getMessage('SPOL_TPL_EMPTY_ORDER_LIST') ?></h3>
            <?
        }
    }

    if (!count($arResult['ORDERS'])) {
        ?>
        <div class="row mb-3" style="display:none;">
            <div class="col">
                <a href="<?= htmlspecialcharsbx($arParams['PATH_TO_CATALOG']) ?>"
                   class="mr-4"><?= Loc::getMessage('SPOL_TPL_LINK_TO_CATALOG') ?></a>
            </div>
        </div>
        <?
    }
    if ($_REQUEST["filter_history"] !== 'Y' && $_REQUEST["show_canceled"] !== 'Y' && $_REQUEST['show_delivery'] !== 'Y') {
        $paymentChangeData = array();
        $orderHeaderStatus = null;

        foreach ($arResult['ORDERS'] as $key => $order) {
            $orderHeaderStatus = $order['ORDER']['STATUS_ID'];
            $id_status_order = $arResult['INFO']['STATUS'][$orderHeaderStatus]['ID'];
            $classStatus = '';
            if ($id_status_order === 'N') {
                $classStatus = 'status_pending_payment';
            } else if ($id_status_order === 'P') {
                $classStatus = 'status_payment_yes';
            } else if ($id_status_order === 'F') {
                $classStatus = 'status_completed';
            }
            //TODO optimize query
            $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $order['ORDER']['ACCOUNT_NUMBER']), false,
                false, array("*", "ORDER_PAYED", "ORDER_CANCELED"));
            $picture['url'] = [];
            if (!empty($ordersBasket)) {
                while ($result = $ordersBasket->Fetch()) {
                    $my_elements = CIBlockElement::GetList(
                        array("ID" => "ASC"),
                        array("ID" => $result['PRODUCT_ID']),
                        false,
                        false,
                        array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                    );
                    $ar_fields = $my_elements->GetNext();
                    $picture['url'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
                }
            }
            ?>
            <div class="row mx-0 mb-5 sale-order-list-inner-container">
                <div class="row mx-0 sale-order-list-title-container">
                    <h3 class="mb-1 mt-1 title-orders-his">
                        <div>
                            <?= Loc::getMessage('SPOL_TPL_ORDER') ?>
                            <?= Loc::getMessage('SPOL_TPL_NUMBER_SIGN') . $order['ORDER']['ACCOUNT_NUMBER'] ?>
                            <span><?= Loc::getMessage('SPOL_TPL_FROM_DATE') ?>&nbsp<?= $order['ORDER']['DATE_INSERT_FORMATED'] ?></span>
                            <?php
                            if ($order['ORDER']['PAYED'] === 'N' && $order['ORDER']['CANCELED'] === 'N') {
                                echo '<span class="taste" style="background-color: rgb(245, 95, 92); color: black;">Новый</span>';
                            }
                            if ($order['ORDER']['PAYED'] === 'Y') {
                                echo '<span class="taste" style="background-color: rgb(245, 95, 92); color: black;">Оплачен</span>';
                            }
                            if ($order['ORDER']['CANCELED'] === 'Y') {
                                echo '<span class="taste" style="background-color: rgb(245, 95, 92); color: black;">Ожидает отмены</span>';
                            }
                            ?>
                        </div>
                        <div>
                            <span class="<?php echo $classStatus ?>"><?= htmlspecialcharsbx($arResult['INFO']['STATUS'][$orderHeaderStatus]['NAME']) ?></span>
                        </div>
                    </h3>
                </div>
                <div class="box_wth_delivery_number" style="display:none;">
                    <div class="mt-2">
                        <span>Номер отслеживания:</span> <a href="#">24006875</a>
                    </div>
                </div>
                <?php
                if (count($picture['url']) <= 4) {
                    $class_box = 'justify-content-evenly';
                } else {
                    $class_box = 'justify-content-between';
                } ?>
                <div class="row mx-0 mb-4 mt-4 d-flex flex_class <?= $class_box ?>">
                    <?php
                    $pictureSTR = array_slice($picture['url'], 0, 6, true);
                    foreach ($pictureSTR as $item => $url) {
                        if (!empty($url)) {
                            ?>
                            <img class="image_box_orders" src="<?= $url ?>"/>
                        <? } else { ?>
                            <img class="image_box_orders"
                                 src="/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/images/no_photo.png"/>
                        <?php }
                    } ?>
                </div>
                <div class="col pt-3 wrap-order-l">
                    <?
                    $showDelimeter = false;
                    foreach ($order['PAYMENT'] as $payment) {
                        if ($order['ORDER']['LOCK_CHANGE_PAYSYSTEM'] !== 'Y') {
                            $paymentChangeData[$payment['ACCOUNT_NUMBER']] = array(
                                "order" => htmlspecialcharsbx($order['ORDER']['ACCOUNT_NUMBER']),
                                "payment" => htmlspecialcharsbx($payment['ACCOUNT_NUMBER']),
                                "allow_inner" => $arParams['ALLOW_INNER'],
                                "refresh_prices" => $arParams['REFRESH_PRICES'],
                                "path_to_payment" => $arParams['PATH_TO_PAYMENT'],
                                "only_inner_full" => $arParams['ONLY_INNER_FULL'],
                                "return_url" => $arResult['RETURN_URL'],
                            );
                        }
                    }
                    ?>

                    <div class="sale-order-list-inner-row sale-order-list-wrap">
                        <div class="sale-order-list-inner-row ">
                            <div class=" sale-order-list-about-container">
                                <a class="sale-order-list-about-link"
                                   href="<?= htmlspecialcharsbx($order["ORDER"]["URL_TO_DETAIL"]) ?>">Подробности
                                    заказа</a>
                            </div>

                            <div class=" sale-order-list-repeat-container">
                                <a class=" sale-order-list-repeat-link"
                                   href="/personal/orders/?COPY_ORDER=Y&ID=<?=$order["ORDER"]['ID']?>"><?= Loc::getMessage('SPOL_TPL_REPEAT_ORDER') ?></a>
                            </div>
                            <?
                            if ($order['ORDER']['CAN_CANCEL'] !== 'N') {
                                ?>
                                <div class=" sale-order-list-cancel-container">
                                    <a class="sale-order-list-cancel-link"
                                       href="<?= htmlspecialcharsbx($order["ORDER"]["URL_TO_CANCEL"]) ?>"><?= Loc::getMessage('SPOL_TPL_CANCEL_ORDER') ?></a>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                        <div class="sale-order-list-inner">
                            <div class="sale-order-list-inner-row-body">
                                <div class="sale-order-list-payment">
                                    <div class="mb-1 sale-order-list-payment-price">
                                        <span class="sale-order-list-payment-element">Сумма заказа:</span>
                                        <span class="sale-order-list-payment-number"><?= $payment['FORMATED_SUM'] ?></span>
                                    </div>

                                    <?php if ($order['ORDER']['IS_ALLOW_PAY'] == 'N' && $payment['PAID'] !== 'Y') {
                                        ?>
                                        <div class="sale-order-list-status-restricted-message-block">
                                            <span class="sale-order-list-status-restricted-message"><?= Loc::getMessage('SOPL_TPL_RESTRICTED_PAID_MESSAGE') ?></span>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col sale-order-list-inner-row-template">
                                <a class="sale-order-list-cancel-payment" href="">
                                    <i class="fa fa-long-arrow-left"></i> <?= Loc::getMessage('SPOL_CANCEL_PAYMENT') ?>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?
        }

    } else if ($_REQUEST["filter_history"] === 'Y') {
        $accountNumber = [];

        $products = CSaleOrder::GetList(array('ID' => 'ASC'), array(),
            false, false, array());

        while ($res = $products->Fetch()) {
            $accountNumber[] = $res;
        }

        if (count($accountNumber) === 0){?>
            <div>Заказов, соответсвующих выбранной категории нет</div>
        <?php
            return;
        }

        for ($i = 0; $i < count($accountNumber); $i++) {
            $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $accountNumber[$i]['ACCOUNT_NUMBER']));
            if (!empty($ordersBasket)) {
                while ($result = $ordersBasket->Fetch()) {
                    $my_elements = CIBlockElement::GetList(
                        array(),
                        array("ID" => $result['PRODUCT_ID']),
                        false,
                        false,
                        array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                    );
                    $ar_fields = $my_elements->GetNext();
                    $picture['url'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
                    $accountNumber[$i]['PICTURE'] = array_slice($picture['url'], 0, 6, true);
                }
            }
        }

        showOrderBlock($arResult, $accountNumber);
    } else if ($_REQUEST['show_canceled'] === 'Y') {
        $accountNumber = [];

        $products = CSaleOrder::GetList(array('ID' => 'ASC'), array('STATUS_ID' => "F"),
            false, false, array());

        while ($res = $products->Fetch()) {
            $accountNumber[] = $res;
        }

        if (count($accountNumber) === 0){?>
            <div>Заказов, соответсвующих выбранной категории нет</div>
            <?php
            return;
        }

        for ($i = 0; $i < count($accountNumber); $i++) {
            $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $accountNumber[$i]['ACCOUNT_NUMBER']));
            if (!empty($ordersBasket)) {
                while ($result = $ordersBasket->Fetch()) {
                    $my_elements = CIBlockElement::GetList(
                        array(),
                        array("ID" => $result['PRODUCT_ID']),
                        false,
                        false,
                        array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                    );
                    $ar_fields = $my_elements->GetNext();
                    $picture['url'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
                    $accountNumber[$i]['PICTURE'] = array_slice($picture['url'], 0, 6, true);
                }
            }
        }

        showOrderBlock($arResult, $accountNumber);
    } else if ($_REQUEST['show_delivery'] === 'Y') {
        $accountNumber = [];

        $products = CSaleOrder::GetList(array('ID' => 'ASC'), array('RESERVED' => "Y"),
            false, false, array());

        while ($res = $products->Fetch()) {
            $accountNumber[] = $res;
        }

        if (count($accountNumber) === 0){?>
            <div>Заказов, соответсвующих выбранной категории нет</div>
            <?php
            return;
        }

        for ($i = 0; $i < count($accountNumber); $i++) {
            $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $accountNumber[$i]['ACCOUNT_NUMBER']));
            if (!empty($ordersBasket)) {
                while ($result = $ordersBasket->Fetch()) {
                    $my_elements = CIBlockElement::GetList(
                        array(),
                        array("ID" => $result['PRODUCT_ID']),
                        false,
                        false,
                        array('ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                    );
                    $ar_fields = $my_elements->GetNext();
                    $picture['url'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
                    $accountNumber[$i]['PICTURE'] = array_slice($picture['url'], 0, 6, true);
                }
            }
        }

        showOrderBlock($arResult, $accountNumber);
    }

}
?>
