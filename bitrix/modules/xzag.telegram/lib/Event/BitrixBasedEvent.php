<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Xzag\Telegram\Container;

/**
 * Class BitrixBasedEvent
 * @package Xzag\Telegram\Event
 */
abstract class BitrixBasedEvent implements BitrixBasedEventInterface
{
    /**
     * @var Event
     */
    private $event;

    /**
     * BitrixBasedEvent constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        $key = static::getName();
        return Container::instance()->getOption("notifications[{$key}]") === 'Y';
    }

    /**
     * @param       $eventData
     * @param array $extraData
     *
     * @return EventResult
     */
    public static function handle($eventData, ...$extraData)
    {
        return Handler::handle(static::make($eventData));
    }
}
