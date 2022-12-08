<?php

namespace Xzag\Telegram\Event;

interface BitrixBasedEventInterface
{
    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @return string
     */
    public static function getModule(): string;

    /**
     * @param $eventData
     * @return BitrixBasedEventInterface
     */
    public static function make($eventData): BitrixBasedEventInterface;

    /**
     * @return string|null
     */
    public function getEntityId(): string;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return bool
     */
    public function isReportable(): bool;
}
