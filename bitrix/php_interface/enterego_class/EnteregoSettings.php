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

    /**
     * @param string $constName
     * @param string $moduleId
     * @return bool
     */
    public static function getParamOnCheckAndPeriod(
        string $constName = '',
        string $moduleId = ''
    ): bool
    {
        $check = COption::GetOptionString($moduleId, $constName);
        $dateOption = json_decode(COption::GetOptionString($moduleId, 'PERIOD'));
        $bool_option_checked = false;

        if (!empty($dateOption->end) && !empty($dateOption->start)) {
            $start = strtotime(date($dateOption->start));
            $now = strtotime(date_format(date_create('now'), 'Y-m-dTH:s'));
            $end = strtotime(date($dateOption->end));
            if ($check === 'true' && ($start <= $now && $end > $now)) {
                $bool_option_checked = true;
            }
        }
        return $bool_option_checked;
    }


    /**
     * @return array
     */
    public static function getInfoBrowser(): array
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        preg_match("/(MSIE|Opera|Firefox|Chrome|Version)(?:\/| )([0-9.]+)/", $agent, $bInfo);
        $browserInfo = array();
        if (empty($bInfo) && strripos($agent, 'iPhone') !== false) {
            $bInfo[1] = "iPhone";
        }
        if ($bInfo[1] == "Version" || $bInfo[1] == "iPhone") {
            $browserInfo['name'] = "Safari";
        } else {
            $browserInfo['name'] = $bInfo[1];
        }
        $browserInfo['version'] = $bInfo[2];
        return $browserInfo;
    }

    /** Get disabled flag
     * @return bool
     */
    public static function getDisabledFlagCategory(): bool
    {
        $domain = $_SERVER['HTTP_HOST'];
        $categoryDisabled = false;
        if($domain === 'oshisha.cc') {
            $categoryDisabled = true;
        }

        return define("CATEGORY_DISABLED", $categoryDisabled);
    }
}