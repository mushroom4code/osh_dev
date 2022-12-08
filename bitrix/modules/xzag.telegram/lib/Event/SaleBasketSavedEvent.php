<?php

namespace Xzag\Telegram\Event;

use Bitrix\Main\Event;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

/**
 * Class SaleBasketSavedEvent
 * @package Xzag\Telegram\Event
 */
class SaleBasketSavedEvent extends ConvertibleEvent
{
    const TYPE = 'OnSaleBasketSaved';

    /**
     * @var Basket
     */
    private $basket;

    /**
     * SaleBasketSavedEvent constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct($event);
        $this->basket = $this->getEvent()->getParameter('ENTITY');
    }

    /**
     * @return bool
     */
    public function isReportable(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'BASKET' => $this->basket instanceof Basket
                ? $this->basket->getFieldValues()
                : $this->getEvent()->getParameters()
        ];
    }

    /**
     * @return string
     */
    public static function getDefaultTemplate(): string
    {
        return 'User {{ NAME }} (#{{ USER_ID }}) updated basket for {{ PRICE }}';
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        if (!$this->basket instanceof Basket) {
            return [];
        }

        try {
            $price = $this->basket->getPrice();
            $userId = Fuser::getUserIdById($this->basket->getFUserId());
            $user = UserTable::getById($userId)->fetch();
        } catch (SystemException $e) {
            return [];
        }

        return array_merge(
            parent::getTemplateParams(),
            [
                'NAME' => $user['NAME'],
                'USER_ID' => $userId,
                'PRICE' => $price
            ]
        );
    }

    /**
     * @return string
     */
    public static function getModule(): string
    {
        return 'sale';
    }

    /**
     * @param $eventData
     * @return BitrixBasedEventInterface
     */
    public static function make($eventData): BitrixBasedEventInterface
    {
        /**
         * @var Event $eventData
         */
        return new static($eventData);
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'SALE_BASKET_SAVED';
    }
}
