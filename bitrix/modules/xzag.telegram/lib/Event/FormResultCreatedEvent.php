<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use CForm;
use CFormResult;

/**
 * Class FormResultCreatedEvent
 * @package Xzag\Telegram\Event
 */
class FormResultCreatedEvent extends ConvertibleEvent
{
    const TYPE = 'OnAfterResultAdd';

    /**
     * @var array
     */
    private $webform;

    /**
     * @var array
     */
    private $answers;

    /**
     * FormResultCreatedEvent constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct($event);
        $this->webform = CForm::GetByID($event->getParameter('WEB_FORM_ID'))->Fetch();
        $this->answers = (new CFormResult())->GetDataByID(
            $event->getParameter('RESULT_ID'),
            [],
            $arResult,
            $arAnswer
        );
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'FORM_RESULT_CREATED';
    }

    /**
     * @return string
     */
    public static function getModule(): string
    {
        return 'form';
    }

    /**
     * @param $eventData
     *
     * @return BitrixBasedEventInterface
     */
    public static function make($eventData): BitrixBasedEventInterface
    {
        /**
         * @var array $eventData
         */
        return new static(new Event(static::getModule(), static::TYPE, $eventData));
    }

    /**
     * @param       $eventData
     * @param mixed ...$extraData
     *
     * @return EventResult
     */
    public static function handle($eventData, ...$extraData)
    {
        return parent::handle([
            'WEB_FORM_ID' => $eventData,
            'RESULT_ID' => array_shift($extraData)
        ]);
    }

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        return isset($this->webform['ID']);
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        return array_merge(
            parent::getTemplateParams(),
            [
                'FORM' => $this->webform,
                'RESULT' => $this->answers
            ]
        );
    }

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string
    {
        return Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_FORM_RESULT_CREATED_EVENT');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'FORM' => $this->webform,
            'RESULT' => $this->answers
        ];
    }
}
