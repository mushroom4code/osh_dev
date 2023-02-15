<?php
namespace Enterego;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

class EnteregoSettings
{
    public static function getPropSetting($catalog_id = 1, $prop_setting_name = '')
    {
        $connection = Application::getConnection();
        $resQuery = [];

        if (!empty($connection)) {
            try {
                $resQuery = $connection->query("SELECT ID,CODE,$prop_setting_name FROM b_iblock_property WHERE IBLOCK_ID=$catalog_id");
            } catch (SqlQueryException $e) {
            }
        }

        return $resQuery;
    }

    public static function updatePropSetting($catalog_id = 1, $see_popup = '',$setting_name = '',$id_prop = 1)
    {
        $connection = Application::getConnection();

        if (!empty($connection)) {
            try {
                $connection->query(
                    "UPDATE  b_iblock_property SET $setting_name='$see_popup' WHERE IBLOCK_ID=$catalog_id AND ID = $id_prop");
            } catch (SqlQueryException $e) {
            }
        }

    }
}