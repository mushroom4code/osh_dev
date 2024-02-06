<?php

use Bitrix\Main\Localization\Loc;
use Enterego\contragents\EnteregoContragents;

Loc::loadMessages('template.php');


function showOrderBlock($listStatuses, $accountNumber)
{
    global $USER;
    foreach ($accountNumber as $order) {

        $classStatus = '';

        if ($order['STATUS_ID'] === 'N') {
            $classStatus = 'status_pending_payment bg-yellowSt text-black font-medium';
        } else if ($order['STATUS_ID'] === 'P') {
            $classStatus = 'status_payment_yes bg-tagFilterGray text-black font-medium';
        } else if ($order['STATUS_ID'] === 'F') {
            $classStatus = 'status_completed bg-greenLight dark:bg-greenButton text-white font-normal';
        } ?>
        <div class="flex flex-col bg-textDark dark:bg-darkBox md:rounded-3xl rounded-xl xl:p-8 p-5 mx-0 mb-5 sale-order-list-inner-container relative">
            <div class="sale-order-list-title-container">
                <h3 class="mb-2 title-orders-his">
                    <p class="mb-3 xl:text-2xl flex md:flex-row flex-col lg:text-lg text-base font-medium
                     text-textLight dark:text-textDarkLightGray md:items-center">
                        <?= Loc::getMessage('SPOL_TPL_ORDER') ?>
                        <?= Loc::getMessage('SPOL_TPL_NUMBER_SIGN') . $order['ACCOUNT_NUMBER'] ?>
                        <span class="md:text-xs md:ml-3 ml-0 text-10 font-normal text-textLight dark:text-textDarkLightGray">
                            <?= Loc::getMessage('SPOL_TPL_FROM_DATE') ?>&nbsp<?= $order['DATE_INSERT_FORMAT'] ?>
                        </span>
                    </p>
                    <div class="absolute top-0 right-0 xl:py-3 xl:px-5 md:py-2 py-1.5 px-4 md:rounded-bl-3xl md:rounded-tr-3xl
                    md:max-w-[250px] max-w-[200px] md:min-w-[200px] w-full flex items-center justify-center rounded-bl-xl rounded-tr-xl  <?= $classStatus ?>">
                        <span class="xl:text-[14px] md:text-xs text-10"><?= htmlspecialcharsbx($listStatuses['STATUS'][$order['STATUS_ID']]['NAME']) ?></span>
                    </div>
                </h3>
            </div>
            <div class="md:my-3 my-1 flex flex-row overflow-auto">
                <?php foreach ($order['PICTURE'] as $url) {
                    $url = !empty($url) ? $url : '/local/templates/Oshisha/images/no-photo.gif'; ?>
                    <img class="image_box_orders mr-3 rounded-md p-3 bg-white xl:max-w-32 max-w-28 object-container
                     xl:max-h-32 max-h-28" src="<?= $url ?>" alt="orderImage"/>
                <?php } ?>
            </div>
            <div class="col pt-3 wrap-order-l">
                <div class="sale-order-list-inner-row sale-order-list-inner-row sale-order-list-wrap md:items-center
                  flex md:flex-row flex-col justify-between">
                    <div class="sale-order-list-inner mr-3 md:mb-0 mb-3 md:w-auto w-full md:block flex justify-end">
                        <div class="sale-order-list-inner-row-body">
                            <div class="sale-order-list-payment">
                                <div class="sale-order-list-payment-price flex flex-row">
                                    <span class="sale-order-list-payment-element mr-2 font-semibold dark:font-medium
                                     text-textLight dark:text-textDarkLightGray xl:text-lg text-base">Итого: </span>
                                    <span class="sale-order-list-payment-number font-semibold dark:font-medium
                                    text-textLight dark:text-textDarkLightGray xl:text-lg text-base">
                                        <?= round($order['PRICE']) . ' ₽' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sale-order-list-inner-row flex flex-row">
                        <?php $userViewPrice = EnteregoContragents::getActiveContragentForUser($USER->GetID());
                        if ($userViewPrice) { ?>
                            <div class=" sale-order-list-repeat-container md:mr-3 mr-1 md:w-full w-1/2">
                                <a class="sale-order-list-repeat-link md:px-5 px-1 xl:py-3 py-2 dark:bg-dark-red rounded-md bg-light-red
                             dark:shadow-md shadow-shadowDark w-full ark:hover:bg-hoverRedDark cursor-pointer flex items-center justify-center
                            <?= $order['IS_NOT_ACTIVE_ITEMS_PRESENT'] === true ? 'js--not-active' : '' ?>"
                                   href="/personal/orders/?COPY_ORDER=Y&ID=<?= $order['ACCOUNT_NUMBER'] ?>">
                                    <svg viewBox="0 0 19 22" fill="none"
                                         xmlns="http://www.w3.org/2000/svg" class="mr-2 md:w-6 w-4 md:h-6 h-5">
                                        <path d="M9.49743 0.666656V3.63808C4.7569 3.63808 0.893433 7.64088 0.893433 12.5524C0.893433 17.4639 4.7569 21.4667 9.49743 21.4667C14.238 21.4667 18.1014 17.4639 18.1014 12.5524C18.1014 10.6011 17.485 8.79763 16.4527 7.32722L15.0897 8.73942C15.7835 9.83239 16.1894 11.1409 16.1894 12.5524C16.1894 16.3933 13.2046 19.4857 9.49743 19.4857C5.79022 19.4857 2.80543 16.3933 2.80543 12.5524C2.80543 8.71146 5.79022 5.61904 9.49743 5.61904V8.59047L14.2774 4.62856L9.49743 0.666656Z"
                                              fill="white"/>
                                    </svg>
                                    <span class="text-white md:text-[15px] text-xs">
                                    <?= Loc::getMessage('SPOL_TPL_REPEAT_ORDER') ?>
                                </span>
                                </a>
                                <div id="popup_mess_order_copy"></div>
                            </div>
                            <?php } ?>
                        <div class=" sale-order-list-about-container md:w-full w-1/2">
                            <a class="sale-order-list-about-link md:px-5 px-1 xl:py-3 py-2 dark:shadow-md md:w-max shadow-shadowDark
                            dark:hover:bg-black cursor-pointer dark:bg-grayButton rounded-md bg-lightGrayBg  w-full
                            flex items-center justify-center "
                               href="/personal/orders/<?= $order['ACCOUNT_NUMBER'] ?>/">
                                <svg class="md:w-7 w-4 md:h-6 h-5" viewBox="0 0 25 26" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12.5636 8.51667C13.372 8.51667 14.0272 7.83764 14.0272 7C14.0272 6.16237 13.372 5.48334 12.5636 5.48334C11.7553 5.48334 11.1 6.16237 11.1 7C11.1 7.83764 11.7553 8.51667 12.5636 8.51667Z"
                                          fill="white"/>
                                    <path d="M12.5636 14.5833C13.372 14.5833 14.0272 13.9043 14.0272 13.0667C14.0272 12.229 13.372 11.55 12.5636 11.55C11.7553 11.55 11.1 12.229 11.1 13.0667C11.1 13.9043 11.7553 14.5833 12.5636 14.5833Z"
                                          fill="white"/>
                                    <path d="M12.5636 20.65C13.372 20.65 14.0272 19.971 14.0272 19.1334C14.0272 18.2957 13.372 17.6167 12.5636 17.6167C11.7553 17.6167 11.1 18.2957 11.1 19.1334C11.1 19.971 11.7553 20.65 12.5636 20.65Z"
                                          fill="white"/>
                                </svg>
                                <span class="text-white md:text-[15px] text-xs">
                                    Подробности заказа
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

}