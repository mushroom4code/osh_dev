<?php

namespace CommonPVZ;

class ProfilesPropertiesTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_profiles_properties';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'SAVED_PROFILE_ID' => array(
                'data_type' => 'integer',
                'reference'
            ),
            'USER_ID' => array(
                'data_type' => 'integer',
            ),
            'CODE' => array(
                'data_type' => 'string',
            ),
            'VALUE' => array(
                'data_type' => 'string',
            )
        );
    }

    public static function deleteAll() {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}