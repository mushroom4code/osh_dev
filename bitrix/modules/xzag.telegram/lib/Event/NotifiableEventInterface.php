<?php

namespace Xzag\Telegram\Event;

/**
 * Interface NotifiableEventInterface
 * @package Xzag\Telegram\Event
 */
interface NotifiableEventInterface
{
    /**
     * @return mixed
     */
    public static function getTemplate(): string;

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string;

    /**
     * @return array
     */
    public function getTemplateParams(): array;

    /**
     * @return string
     */
    public function convert(): string;

    /**
     * @return array
     */
    public function toArray(): array;
}
