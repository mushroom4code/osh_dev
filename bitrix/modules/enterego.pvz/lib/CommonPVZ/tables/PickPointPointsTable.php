<?php

namespace CommonPVZ;

class PickPointPointsTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_pickpoint_points';
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
            'BITRIX_CODE' => array(
                'data_type' => 'string',
            ),
            'FULL_ADDRESS' => array(
                'data_type' => 'string',
            ),
            'ADDRESS_REGION' => array(
                'data_type' => 'string',
            ),
            'ADDRESS_LAT' => array(
                'data_type' => 'float',
            ),
            'ADDRESS_LNG' => array(
                'data_type' => 'float',
            ),
        );
    }

    public static function deleteAll() {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}