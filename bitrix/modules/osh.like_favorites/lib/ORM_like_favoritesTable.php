<?php

namespace Bitrix\Like;

use Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\Entity;
/**
 * Class FavoriteTable
 *
 * Fields:
 * <ul>
 * <li> F_USER_ID int mandatory
 * <li> I_BLOCK_ID int optional
 * <li> LIKE_USER int optional default 0
 * <li> FAVORITE int optional default 0
 * </ul>
 *
 * @package Bitrix\Like
 **/
class ORM_like_favoritesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ent_like_favorite';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap(): array
    {
        return [
            new IntegerField(
                'F_USER_ID',
                [
                    'title' => "Пользователь",
                    'primary' => true,
                ]
            ),
            new IntegerField(
                'I_BLOCK_ID',
                [
                    'title' => "Блок",
                    'primary' => true,
                ]
            ),
            new IntegerField(
                'LIKE_USER',
                [
                    'default' => 0,
                    'title' => "Лайки",

                ]
            ),
            new IntegerField(
                'FAVORITE',
                [
                    'default' => 0,
                    'title' => 'Избранное'
                ]
            ),
            new Entity\ExpressionField('LIKE_USERS', 'SUM(%s)',array('LIKE_USER')),
        ];
    }
}

