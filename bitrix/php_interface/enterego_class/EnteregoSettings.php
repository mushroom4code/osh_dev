<?php

namespace Enterego;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use COption;

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

    public static function updatePropSetting($catalog_id = 1, $see_popup = '', $setting_name = '', $id_prop = 1)
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

    /** This method set sale type id for product && basket on date with checked
     * @return void
     */
    public static function getSalePriceOnCheckAndPeriod()
    {
        $check = COption::GetOptionString('activation_price_admin', 'USE_CUSTOM_SALE_PRICE');
        $dateOption = json_decode(COption::GetOptionString('activation_price_admin', 'PERIOD'));
        $now = strtotime(date_format(date_create('now'), 'Y-m-dTH:s'));
        if (!empty($dateOption->end) && !empty($dateOption->start)) {
            $start = strtotime(date($dateOption->start));
            $end = strtotime(date($dateOption->end));
            if ($check === 'true' && ($start <= $now && $end > $now)) {
                define("USE_CUSTOM_SALE_PRICE", true);
            } else {
                define("USE_CUSTOM_SALE_PRICE", false);
            }
        }
    }
}