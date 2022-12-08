<?php

namespace Xzag\Telegram\Event;

/**
 * Class Factory
 * @package Xzag\Telegram\Event
 */
class Factory
{
    /**
     * @return ConvertibleEvent[]|string[]
     */
    public function getEventsOptions()
    {
        return [
            SaleOrderCreatedEvent::class,
            SaleOrderPayedEvent::class,
            MainUserRegisteredEvent::class,
            FormResultCreatedEvent::class
        ];
    }
}
