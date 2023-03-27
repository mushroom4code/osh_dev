<?php

namespace Enterego;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\SystemException;
use Cassandra\Bigint;

/*
 * CREATE TABLE
 *
 *
 create table ent_auth_token (
    TOKEN      varchar(255) not null
        primary key,
    USER_ID    int          not null,
    EXPIRATION int          not null,
    constraint ent_auth_token_b_user_null_fk
        foreign key (USER_ID) references b_user (ID)
 );
 */
class AuthTokenTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'ent_auth_token';
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
            'TOKEN' => new StringField(
                'TOKEN',
                [
                    'primary' => true,
                    'required' => true,
                ]
            ),
            'USER_ID' => new IntegerField(
                'USER_ID',
                [
                    'required' => true,
                ]
            ),
            'EXPIRATION' => new IntegerField(
                'EXPIRATION',
                [
                    'required' => true
                ]
            )
        ];

    }
}


