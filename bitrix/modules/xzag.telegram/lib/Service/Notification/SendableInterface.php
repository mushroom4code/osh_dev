<?php

namespace Xzag\Telegram\Service\Notification;

/**
 * Interface SendableInterface
 * @package Xzag\Telegram\Service\Notification
 */
interface SendableInterface
{
    /**
     * @param string $message
     * @return mixed
     */
    public function send(string $message);
}
