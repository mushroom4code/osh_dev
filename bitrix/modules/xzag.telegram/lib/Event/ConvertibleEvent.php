<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Xzag\Telegram\Container;
use Xzag\Telegram\Service\Template\TemplateEngineInterface;

/**
 * Class ConvertibleEvent
 * @package Xzag\Telegram\Event
 */
abstract class ConvertibleEvent extends BitrixBasedEvent implements NotifiableEventInterface
{
    /**
     * @var TemplateEngineInterface
     */
    protected $templateService;

    /**
     * ConvertibleEvent constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct($event);

        $this->templateService = Container::instance()->getTemplateService();
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        $context = Context::getCurrent();
        $server = $context->getServer()->toArray();

        return [
            'SERVER' => $server,
            'SITE' => $this->getSite()
        ];
    }

    /**
     * @return string
     */
    public static function getTemplate(): string
    {
        $key = static::getName();
        return Container::instance()->getOption("messages[{$key}]", '');
    }
    public function convertNew($html): string
    {
        return $this->templateService->render(
            $html,
            $this->getTemplateParams()
        );
    }

    /**
     * @return string
     */
    public function convert(): string
    {
        return $this->templateService->render(
            static::getTemplate() ?: static::getDefaultTemplate(),
            $this->getTemplateParams()
        );
    }

    /**
     * @return array
     */
    protected function getSite(): array
    {
        $context = Context::getCurrent();
        $siteId = $context->getSite();
        $filter = $siteId ? ['LID' => $siteId] : ['DEF' => 'Y'];

        try {
            $site = SiteTable::getList([
                'filter' => $filter
            ])->fetch();

            if (!$site) {
                return [];
            }
        } catch (SystemException $e) {
            $site = [];
        }

        return $site;
    }

    /**
     * @param $userId
     *
     * @return array
     */
    protected function getUserById($userId): array
    {
        try {
            $user = $userId ? UserTable::getById($userId)->fetch() : [];
        } catch (SystemException $e) {
            $user = [];
        }

        return $user;
    }
}
