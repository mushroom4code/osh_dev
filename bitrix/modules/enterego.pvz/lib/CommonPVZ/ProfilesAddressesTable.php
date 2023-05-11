<?php

namespace CommonPVZ;

class ProfilesAddressesTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_profiles_addresses';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'PROFILE_ID' => array(
                'data_type' => 'integer',
            ),
            'USER_ID' => array(
                'data_type' => 'integer',
            ),
            'ADDRESS' => array(
                'data_type' => 'string',
            )
        );
    }

    public static function deleteAll() {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}