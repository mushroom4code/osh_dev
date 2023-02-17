<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/script.js");
Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/style.css");
CJSCore::Init(array('clipboard', 'fx'));

Loc::loadMessages(__FILE__);


/**
 * @param array $filter
 * @return array
 */
global $USER;
$textError = '<div>Заказов, соответсвующих выбранной категории нет</div>';
function get_orders(array $filter = []): array
{

    $listOrders = [];

    $products = CSaleOrder::GetList(array('ID' => 'ASC'), $filter);

    while ($res = $products->Fetch()) {
        $listOrders[] = $res;
    }

    foreach ($listOrders as &$itemOrder) {
        $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $itemOrder['ID']), false, ['nTopCount' => 5]);
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
                $itemOrder['PICTURE'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
            }
        }
    }

    return $listOrders;
}

function showOrderBlock($arResult, $accountNumber)
{
    foreach ($accountNumber as $key => $order) {

        $classStatus = '';

        if ($order['STATUS_ID'] === 'N') {
            $classStatus = 'status_pending_payment';
        } else if ($order['STATUS_ID'] === 'P') {
            $classStatus = 'status_payment_yes';
        } else if ($order['STATUS_ID'] === 'F') {
            $classStatus = 'status_completed';
        } ?>
        <div class="row mx-0 mb-5 sale-order-list-inner-container">
            <div class="row mx-0 sale-order-list-title-container">
                <h3 class="mb-1 mt-1 title-orders-his">
                    <div>
                        <?= Loc::getMessage('SPOL_TPL_ORDER') ?>
                        <?= Loc::getMessage('SPOL_TPL_NUMBER_SIGN') . $order['ACCOUNT_NUMBER'] ?>
                        <span><?= Loc::getMessage('SPOL_TPL_FROM_DATE') ?>&nbsp<?= $order['DATE_INSERT_FORMAT'] ?></span>
                        <?php
                        if ($order['PAYED'] === 'N' && $order['CANCELED'] === 'N') {
                            echo '<span class="taste font-w-m-600" style="background-color: rgb(245, 95, 92); color: white;">Новый</span>';
                        }
                        if ($order['PAYED'] === 'Y') {
                            echo '<span class="taste font-w-m-600" style="background-color: rgb(245, 95, 92); color: white;">Оплачен</span>';
                        }
                        if ($order['CANCELED'] === 'Y') {
                            echo '<span class="taste font-w-m-600" style="background-color: rgb(245, 95, 92); color: white;">Ожидает отмены</span>';
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
                             src="/local/templates/Oshisha/images/no-photo.gif"/>
                    <?php }
                } ?>
            </div>
            <div class="col pt-3  wrap-order-l">
                <div class="sale-order-list-inner-row sale-order-list-inner-row sale-order-list-wrap">
                    <div class="sale-order-list-inner-row">
                        <div class=" sale-order-list-about-container">
                            <a class="sale-order-list-about-link font-w-m-600"
                               href="/personal/orders/<?= $order['ACCOUNT_NUMBER'] ?>/">Подробности
                                заказа</a>
                        </div>

                        <div class=" sale-order-list-repeat-container">
                            <a class=" sale-order-list-repeat-link font-w-m-600"
                               href="<?= $arResult['CURRENT_PAGE'].'?COPY_ORDER=Y&ID='.$order['ACCOUNT_NUMBER'] ?>"><?= Loc::getMessage('SPOL_TPL_REPEAT_ORDER') ?></a>
                            <div id="popup_mess_order_copy"></div>
                        </div>
                        <?
                        if ($order['CAN_CANCEL'] !== 'N') {
                            ?>
                            <div class=" sale-order-list-cancel-container">
                                <a class="sale-order-list-cancel-link font-w-m-600"
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
    $listOrders = [];
    $filter = array('USER_ID' => $USER->GetID());

    if ($_REQUEST["filter_history"] !== 'Y' && $_REQUEST["show_canceled"] !== 'Y' && $_REQUEST['show_delivery'] !== 'Y') {
        $filter = array('USER_ID' => $USER->GetID(), 'STATUS_ID' => ['N', 'P']);
    } else if ($_REQUEST['show_canceled'] === 'Y') {
        $filter = array('STATUS_ID' => "F", 'USER_ID' => $USER->GetID());

    } else if ($_REQUEST['show_delivery'] === 'Y') {
        $filter = array('RESERVED' => "Y", 'USER_ID' => $USER->GetID());
    }

    $listOrders = get_orders($filter);

    if (count($listOrders) === 0) {
        echo $textError;
        return;
    }

    showOrderBlock($arResult, $listOrders);
}
?>