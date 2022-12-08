<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\EventResult;
use Throwable;
use Xzag\Telegram\Container;
use Xzag\Telegram\Data\ProxySettings;
use Xzag\Telegram\Service\Notification\TelegramNotification;
use Xzag\Telegram\Service\NotificationService;

/**
 * Class Handler
 * @package Xzag\Telegram\Event
 */
class Handler
{
    // const EVENT_TYPE_RECEIVED = 'EVENT_RECEIVED';
    const EVENT_TYPE_PROCESSED = 'EVENT_PROCESSED';
    const EVENT_TYPE_FAILED = 'EVENT_FAILED';
    // const EVENT_TYPE_SKIPPED = 'EVENT_SKIPPED';

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $chatId;

    /**
     * @var ProxySettings
     */
    private $proxy;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        $container = Container::instance();
        $this->token = $container->getOption('token');
        $this->chatId = $container->getOption('chat_id');
        $this->proxy = ProxySettings::make([
            'enabled' => $container->getOption('proxy[enabled]') === 'Y' ? 'on' : 'off',
            'host' => $container->getOption('proxy[host]'),
            'username' => $container->getOption('proxy[username]'),
            'password' => $container->getOption('proxy[password]'),
        ]);

        $this->debug = $container->getOption('debug') === 'Y';
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if (empty($this->token) || empty($this->chatId)) {
            return false;
        }

        return true;
    }

    /**
     * @param BitrixBasedEventInterface $event
     * @return EventResult
     */
    public static function handle(BitrixBasedEventInterface $event): EventResult
    {
        return (new static())->dispatch($event);
    }

    /**
     * @param BitrixBasedEventInterface $event
     * @return EventResult
     */
    public function dispatch(BitrixBasedEventInterface $event): EventResult
    {
        if (!$event instanceof BitrixBasedEvent) {
            return new EventResult(EventResult::UNDEFINED, false);
        }

        if (!$this->isValid()) {
            return new EventResult(EventResult::UNDEFINED, false);
        }

        $app = Container::instance();
        $logger = $app->getLogger();
        /**
         * @var $notificator NotificationService
         */
        $notificator = Container::get(NotificationService::class);

        try {
            if ($event instanceof NotifiableEventInterface && $event->isReportable() && $event->isEnabled()) {
                if ($this->debug) {
                    $logger->debug(
                        $event::getName(),
                        [
                            'data' => $event->getTemplateParams(),
                            'itemId' => $event->getEntityId()
                        ]
                    );
                }

                $message = $event->convert();
                $notification = (new TelegramNotification($this->token))->to($this->chatId);

                if ($this->proxy->isEnabled()) {
                    $notification->setProxy($this->proxy);
                }

                $notificator
                    ->with($notification)
                    ->send($message);

                $logger->info($event::getName(), [
                    'data' => [
                        'type' => $event::getName(),
                        'eventData' => $event->toArray(),
                        'message' => $message,
                    ],
                    'itemId' => $event->getEntityId()
                ]);
            }
        } catch (Throwable $e) {
            $logger->error(static::EVENT_TYPE_FAILED, [
                'data' => [
                    'type' => $event::getName(),
                    'eventData' => $event->toArray(),
                    'error' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ],
                'itemId' => $event->getEntityId()
            ]);

            return new EventResult(EventResult::ERROR, false);
        }

        return new EventResult(EventResult::SUCCESS, true);
    }
}
