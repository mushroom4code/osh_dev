<?php

namespace Xzag\Telegram\Data;

use Bitrix\Main\Event;

/**
 * Class EventData
 * @package Xzag\Telegram\Data
 */
class EventData extends AbstractStructure
{
    /**
     * @var string|null
     */
    public $eventType;

    /**
     * @var array|null
     */
    public $eventData;

    /**
     * EventData constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->eventType = $event->getEventType();
        $this->eventData = $event->getParameters();
    }

    public function getEventType(): string
    {
        return $this->eventType ?? '';
    }
}
