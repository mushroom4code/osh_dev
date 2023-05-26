<?php

namespace Enterego;

class ProductsSubscriptionsTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName()
    {
        return 'ent_products_subscriptions';
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'PRODUCT_NAME' => array(
                'data_type' => 'string',
            ),
            'SUBSCRIPTION_CLICKS' => array(
                'data_type' => 'integer',
            ),
        );
    }

    public static function deleteAll()
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}