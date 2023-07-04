<?php

namespace CommonPVZ;

class OshishaSavedDeliveriesTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_oshisha_saved_deliveries';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'ZONE' => array(
                'data_type' => 'string',
            ),
            'LATITUDE' => array(
                'data_type' => 'string',
            ),
            'LONGITUDE' => array(
                'data_type' => 'string',
            ),
            'DISTANCE' => array(
                'data_type' => 'string',
            ),
        );
    }

    public static function deleteAll() {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}