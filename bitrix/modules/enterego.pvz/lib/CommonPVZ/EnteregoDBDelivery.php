<?php

namespace Enterego;

class EnteregoDBDelivery
{

    public static function getPoints5postForALLMap(string $LocationCode = '0000073738'): array
    {
        global $DB;
        $result = [];
        $queryDBPoints = $DB->Query("SELECT locality.LOCALITY_FIAS_CODE,
                                             points.ID,
                                             points.ADDITIONAL,
                                             points.FULL_ADDRESS,
                                             points.ADDRESS_COUNTRY,
                                             points.ADDRESS_ZIP_CODE,
                                             points.ADDRESS_REGION,
                                             points.ADDRESS_LAT,
                                             points.POINT_GUID,
                                             points.ADDRESS_LNG
                                      FROM ipol_fivepost_locations as locality
                                               INNER JOIN ipol_fivepost_points as points
                                                          ON (locality.LOCALITY_FIAS_CODE = points.LOCALITY_FIAS_CODE)
                                      WHERE locality.BITRIX_CODE = $LocationCode");
        if ($queryDBPoints->Fetch()) {
            while ($resultQuery = $queryDBPoints->Fetch()) {
                $result[] = $resultQuery;
            }
        }
        return $result;
    }

    // ee - sosonnyi
    public static function getPriceForPoint($zone = 1): int
    {
        global $DB;
        $queryDBPoints = $DB->Query("SELECT RATE_VALUE_WITH_VAT
                                      FROM ipol_fivepost_rates
                                      WHERE POINT_ID = $zone");
        while ($resultQuery = $queryDBPoints->Fetch()) {
            return (int)$resultQuery['RATE_VALUE_WITH_VAT'];
        }
        return 0;
    }
}