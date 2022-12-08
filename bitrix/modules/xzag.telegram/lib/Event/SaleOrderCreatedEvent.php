<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Localization\Loc;

/**
 * Class SaleOrderCreatedEvent
 * @package Xzag\Telegram\Event
 */
class SaleOrderCreatedEvent extends SaleOrderSavedEvent
{
    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        $is_new = $this->getEvent()->getParameter('IS_NEW');
        return $is_new;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'IS_NEW' => $this->getEvent()->getParameter('IS_NEW')
            ]
        );
    }

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string
    {
        return Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_SALE_ORDER_CREATED_EVENT');
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'SALE_ORDER_CREATED';
    }
}
