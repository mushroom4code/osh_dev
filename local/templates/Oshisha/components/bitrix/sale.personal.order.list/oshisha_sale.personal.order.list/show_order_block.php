<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages('template.php');

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
                            <a class=" sale-order-list-repeat-link font-w-m-600 <?= array_search('N', $order['ACTIVE']) !== false ? 'not-active' : '' ?>"
                               href="/personal/orders/?COPY_ORDER=Y&ID=<?= $order['ACCOUNT_NUMBER'] ?>"><?= Loc::getMessage('SPOL_TPL_REPEAT_ORDER') ?></a>
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