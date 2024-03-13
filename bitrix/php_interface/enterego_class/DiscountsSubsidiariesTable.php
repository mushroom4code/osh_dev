<?php

namespace Enterego;

/*
 * CREATE TABLE
 *
 *
 create table ent_discounts_subsidiaries(
	ID int auto_increment,
	DISCOUNT_ID int,
	SITE_ID varchar(100),
	primary key(ID)
);
 */
class DiscountsSubsidiariesTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_discounts_subsidiaries';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'DISCOUNT_ID' => array(
                'data_type' => 'integer',
            ),
            'SITE_ID' => array(
                'data_type' => 'string',
            )
        );
    }

    public static function deleteAll()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}