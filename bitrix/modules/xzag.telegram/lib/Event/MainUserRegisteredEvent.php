<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

/**
 * Class MainUserRegisteredEvent
 * @package Xzag\Telegram\Event
 */
class MainUserRegisteredEvent extends ConvertibleEvent
{
    const TYPE = 'OnAfterUserRegister';

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        // event gets triggered even if validation fails on registration form
        $userId = $this->getEvent()->getParameter('ID');
        if (!$userId) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ID' => $this->getEntityId()
        ];
    }

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string
    {
        return Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_MAIN_USER_REGISTERED_EVENT');
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        $userId = $this->getEvent()->getParameter('ID');

        return array_merge(
            parent::getTemplateParams(),
            [
                'USER' => $this->getUserById($userId),
                'LINK' => $this->getUserAdminUrl($userId),
            ]
        );
    }

    /**
     * @param $userId
     * @return string
     */
    protected function getUserAdminUrl($userId): string
    {
        $server = Context::getCurrent()->getServer();
        $uri = new Uri('/bitrix/admin/user_edit.php');
        $uri->setHost($server->getHttpHost());
        $uri->addParams([
            'ID' => $userId
        ]);

        return $uri->getUri();
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return (string)$this->getEvent()->getParameter('ID');
    }

    /**
     * @return string
     */
    public static function getModule(): string
    {
        return 'main';
    }

    /**
     * @param $eventData
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
     * @return string
     */
    public static function getName(): string
    {
        return 'MAIN_USER_REGISTERED';
    }
}
