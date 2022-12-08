<?php

namespace Xzag\Telegram\Service;

use Xzag\Telegram\Service\Notification\SendableInterface;

/**
 * Class NotificationService
 * @package Xzag\Telegram\Service
 */
class NotificationService
{
    /**
     * @var SendableInterface[]
     */
    private $providers;

    /**
     * @param SendableInterface|SendableInterface[] $providers
     * @return NotificationService
     */
    public function with($providers): NotificationService
    {
        $this->providers = is_array($providers) ? $providers : [$providers];
        return $this;
    }

    /**
     * @param string $message
     */
    public function send(string $message)
    {
        foreach ($this->providers as $provider) {
            $provider->send($message);
        }
    }
}
