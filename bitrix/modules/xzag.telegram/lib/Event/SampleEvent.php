<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Xzag\Telegram\Container;

/**
 * Class SampleEvent
 * @package Xzag\Telegram\Event
 */
class SampleEvent extends ConvertibleEvent
{
    const TYPE = 'OnSampleEvent';

    /**
     * @return string
     */
    public static function getModule(): string
    {
        return Container::instance()->getModuleId();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'XZAG_TELEGRAM_SAMPLE_EVENT';
    }

    /**
     * @param array $eventData
     * @return BitrixBasedEventInterface|SampleEvent
     */
    public static function make($eventData = []): BitrixBasedEventInterface
    {
        /**
         * @var array $eventData
         */
        return new static(new Event(static::getModule(), static::TYPE, $eventData));
    }

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string
    {
        return Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_SAMPLE_EVENT');
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        return array_merge(
            parent::getTemplateParams(),
            $this->getEvent()->getParameters()
        );
    }

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        return true;
    }
}
