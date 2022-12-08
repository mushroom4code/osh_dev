<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Localization\Loc;

/**
 * Class SaleOrderPayedEvent
 * @package Xzag\Telegram\Event
 */
class SaleOrderPayedEvent extends SaleOrderSavedEvent
{
    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        $is_new = $this->getEvent()->getParameter('IS_NEW');
        $oldValues = $this->getEvent()->getParameter('VALUES');

        return !$is_new && $oldValues['PAYED'] === 'N' && $this->order->isPaid();
    }

    /**
     * @return float
     */
    public function getPayedTotal(): float
    {
        $collection = $this->order->getPaymentCollection();
        return $collection->getPaidSum();
    }

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string
    {
        return Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_SALE_ORDER_PAYED_EVENT');
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        $payments = $this->getPayments();
        return array_merge(
            parent::getTemplateParams(),
            [
                'PAYMENT' => array_shift($payments),
            ]
        );
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'SALE_ORDER_PAYED';
    }
}
