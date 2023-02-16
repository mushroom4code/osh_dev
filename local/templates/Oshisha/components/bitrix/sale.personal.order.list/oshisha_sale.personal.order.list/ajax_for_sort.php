<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('sale')) {
    exit();
}
use Bitrix\Main\Localization\Loc;

$listStatuses = [];
$listStatusNames = Bitrix\Sale\OrderStatus::getAllStatusesNames(LANGUAGE_ID);
foreach($listStatusNames as $key => $data)
{
    $listStatuses['STATUS'][$key] = array('ID'=>$key,'NAME'=>$data);
}

function sortByField(string $field, string $params, ?string $element = null, ?string $index = null): array
{
    global $USER;
    $listOrders = [];


    $products = CSaleOrder::GetList(array($field => $params), array($element => $index, "USER_ID" => $USER->GetID()));

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

function showOrderBlock($listStatuses, $accountNumber)
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
                        <span class="<?php echo $classStatus ?>"><?= htmlspecialcharsbx($listStatuses['STATUS'][$order['STATUS_ID']]['NAME']) ?></span>
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
                               href="/personal/cart/"><?= Loc::getMessage('SPOL_TPL_REPEAT_ORDER') ?></a>
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

$url = substr(json_decode($_POST['url'], true), 1, -2);

switch (json_decode($_POST['typeSort'], true)) {
    case "Дешёвые":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            $listOrders = sortByField("PRICE", 'ASC');
        } else if ($url === 'show_canceled') {
            $listOrders = sortByField("PRICE", 'ASC', 'STATUS_ID', 'F');
        } else if ($url === 'show_delivery') {
            $listOrders = sortByField("PRICE", 'ASC', 'RESERVED', 'Y');
        }
        break;
    case "Дорогие":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            $listOrders = sortByField("PRICE", 'DESC');
        } else if ($url === 'show_canceled') {
            $listOrders = sortByField("PRICE", 'DESC', 'STATUS_ID', 'F');
        } else if ($url === 'show_delivery') {
            $listOrders = sortByField("PRICE", 'DESC', 'RESERVED', 'Y');
        }
        break;
    case "Старые":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            $listOrders = sortByField("DATE_INSERT", 'ASC');
        } else if ($url === 'show_canceled') {
            $listOrders = sortByField("DATE_INSERT", 'ASC', 'STATUS_ID', 'F');
        } else if ($url === 'show_delivery') {
            $listOrders = sortByField("DATE_INSERT", 'ASC', 'RESERVED', 'Y');
        }
        break;
    case "Новые":
        if ($url !== 'show_canceled' && $url !== 'show_delivery') {
            $listOrders = sortByField("DATE_INSERT", 'DESC');
        } else if ($url === 'show_canceled') {
            $listOrders = sortByField("DATE_INSERT", 'DESC', 'STATUS_ID', 'F');
        } else if ($url === 'show_delivery') {
            $listOrders = sortByField("DATE_INSERT", 'DESC', 'RESERVED', 'Y');
        }
        break;
}


if (isset($listOrders) && $listOrders !== false) {
    showOrderBlock($listStatuses, $listOrders);
    die();
} else {
    echo 'error';
    die();
}

