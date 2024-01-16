<?php

namespace Enterego\PWA;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

/**
 * Class EnteregoORMContragents
 *
 * Fields:
 *
 **/
class EnteregoDBPushNotificationPWATable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ent_send_push_user';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new IntegerField(
                'ID',
                [
                    'title' => "ID",
                    'primary' => true,
                ]
            ),
            new IntegerField(
                'USER_ID',
                [
                    'title' => "USER_ID",
                    'default' => false,
                ]
            ),
            new TextField(
                'AUTH_TOKEN',
                [
                    'title' => "AUTH_TOKEN",
                ]
            ),
            new TextField(
                'PUBLIC_KEY',
                [
                    'title' => "PUBLIC_KEY",
                ]
            ),
            new TextField(
                'END_POINT',
                [
                    'title' => "END_POINT",
                ]
            ),
            new TextField(
                'CONTENT_ENCODING',
                [
                    'title' => "CONTENT_ENCODING",
                ]
            ),
            new DatetimeField(
                'DATE_INSERT',
                [
                    'title' => "Дата создания",
                    'default' => function () {
                        return new DateTime();
                    },
                ]
            ),
            new StringField(
                'USER_DEVICE',
                [
                    'title' => "USER_DEVICE",
                ]
            ),
        ];
    }
}
