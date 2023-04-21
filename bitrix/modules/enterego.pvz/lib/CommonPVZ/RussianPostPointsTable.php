<?php

namespace CommonPVZ;

class RussianPostPointsTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_russianpost_points';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'CODE' => array(
                'data_type' => 'string',
            ),
            'INDEX' => array(
                'data_type' => 'string',
            ),
            'BITRIX_CODE' => array(
                'data_type' => 'string',
            ),
            'PHONE_NUMBER' => array(
                'data_type' => 'string',
            ),
            'WORK_TIME' => array(
                'data_type' => 'string',
            ),
            'COMMENT' => array(
                'data_type' => 'string',
            ),
            'FULL_ADDRESS' => array(
                'data_type' => 'string',
            ),
            'ADDRESS_LAT' => array(
                'data_type' => 'float',
            ),
            'ADDRESS_LNG' => array(
                'data_type' => 'float',
            ),
            'IS_PVZ' => array(
                'data_type' => 'string',
            )
        );
    }

    public static function deleteAll() {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}