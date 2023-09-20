<?php
namespace Enterego\ORM;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\SystemException;

/**
 * Class EnteregoORMContragents
 *
 * Fields:
 *
 **/
class EnteregoORMRelationshipUserContragentsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ent_contragent_user_relationships';
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
                    'title' => "ID Контрагента",
                    'primary' => true,
                ]
            ),
            new IntegerField(
                'ID_CONTRAGENT',
                [
                    'title' => "ID Контрагента",
                ]
            ),
            new IntegerField(
                'USER_ID',
                [
                    'title' => "ID пользователя",
                ]
            ),
            new BooleanField(
                'STATUS',
                [
                    'title' => "Статус связи контрагента и пользователя",
                ]
            ),
        ];
    }
}

