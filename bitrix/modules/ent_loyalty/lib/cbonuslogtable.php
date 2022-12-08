<?php

namespace Enterego\Osh\Loyalty;

use Bitrix\Main\Entity;

//use \Bitrix\Main\Localization\Loc;
//Loc::loadMessages(dirname(__DIR__).'/lang.php');

/**
 * Регистр бонусных операций
 * @package Enterego\Osh\Loyalty
 */
class CBonusLogTable extends Entity\DataManager
{
    const TABLE_NAME = 'ent_bonus_log';

    /**
     * Внутренни ID записи в таблице (сайт)
     */
    const FIELD_ID = 'id';

    /**
     * ID заказа на сайте битрикс (не всегда известен)
     */
    const FIELD_BX_ORDER_ID = 'bitrix_order_id';

    /**
     * ID транзакций в таблице плагина skyweb24.loyaltyprogram
     */
    const FIELD_TRANSACTION_ID = 'transaction_id';

    /**
     * Статус транзакции по бонусами (один из возможных).
     */
    const FIELD_STATUS = 'status';

    public static function getTableName()
    {
        return self::TABLE_NAME;
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField(self::FIELD_ID, [
                'primary'       => true,
                'autocomplete'  => true,
                'required'      => true,
            ]),
            new Entity\IntegerField(self::FIELD_TRANSACTION_ID, [
                'required'      => true,
            ]),
            new Entity\StringField(self::FIELD_BX_ORDER_ID, [
                'required' => false,
            ]),
            new Entity\IntegerField(self::FIELD_STATUS, [
                'required' => false,
            ])
        );
    }
}
