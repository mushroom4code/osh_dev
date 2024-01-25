<?php

namespace Enterego\contragents;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use COption;
use Bitrix\Main;
use Enterego\ORM\EnteregoORMContragentsTable;

class EnteregoExchange
{
    /**
     * Import contragent-company-user
     * @param string $stepDate
     * @param bool|int $id
     * @param string $type
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function GetInfoForXML(
        string   $stepDate = '',
        bool|int $id = 0,
        string   $type = ''): array
    {
        $arData = ['CONTRAGENTS' => [], 'USERS' => []];
        $dateExportStart = ConvertTimeStamp(false, "FULL");
        $dateStartImport1C = COption::GetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS');

        if (empty($type)) {
            /** select contragents for XML on date interval with id interval*/
            $filterContr = array(
                'select' => array('*'),
                'order' => array(
                    'ID_CONTRAGENT' => 'ASC'
                ),
                'limit' => 10,
            );

            if (!empty($dateStartImport1C) || !empty($stepDate)) {
                $filterContr['filter'] = array(
                    ">=DATE_UPDATE" => $dateStartImport1C || $stepDate,
                );
            }

            $filterContr['filter']['<=DATE_UPDATE'] = $dateExportStart;

            if ($id > 0) {
                $filterContr['filter'][">ID_CONTRAGENT"] = $id;
            }

            $resultQueryContr = EnteregoORMContragentsTable::getList(
                $filterContr
            );

            if (!empty($resultQueryContr)) {
                while ($arResultQuery = $resultQueryContr->Fetch()) {
                    $arData['CONTRAGENTS'][$arResultQuery['ID_CONTRAGENT']] = $arResultQuery;
                }
            }

        } else {
            /** select user+contr+comp for XML */
            
            $filterUser = array(
                'select' => array(
                    'NAME',
                    'LOGIN',
                    'PERSONAL_PHONE',
                    'EMAIL',
                    'USER_ID' => 'ID',
                    'RELATION_ID_CONTRAGENT' => 'RELATION.ID_CONTRAGENT',
                    'RELATION_STATUS' => 'RELATION.STATUS',
                    'CONTRAGENT',
                    'INN' => 'CONTRAGENT.INN'
                ),
                'limit' => 10,
                'runtime' => array(
                    new Main\Entity\ReferenceField(
                        'RELATION',
                        'Enterego\ORM\EnteregoORMRelationshipUserContragentsTable',
                        array(
                            '=this.ID' => 'ref.USER_ID',
                        ),
                        array(
                            "join_type" => 'INNER'
                        )
                    ), new Main\Entity\ReferenceField(
                        'CONTRAGENT',
                        'Enterego\ORM\EnteregoORMContragentsTable',
                        array(
                            '=this.RELATION_ID_CONTRAGENT' => 'ref.ID_CONTRAGENT',
                        ),
                        array(
                            "join_type" => 'INNER'
                        )
                    )),
                'order' => array(
                    'ID' => 'ASC'
                ),
            );

            $filterUser['filter'] = array(
                "<=TIMESTAMP_X" => $dateExportStart,
            );

            if (!empty($dateStartImport1C) || !empty($stepDate)) {
                $filterUser['filter']['>=TIMESTAMP_X'] = $dateStartImport1C || $stepDate;
            }

            if ($id > 0) {
                $filterUser['filter']['>ID'] = $id;
            }

            $resultQueryUsers = UserTable::getList($filterUser);

            if (!empty($resultQueryUsers)) {
                while ($arResultQueryCompany = $resultQueryUsers->Fetch()) {
                    if (!empty($arResultQueryCompany) && !empty($arResultQueryCompany['RELATION_ID_CONTRAGENT'])) {
                        $userID = $arResultQueryCompany['USER_ID'];
                        $arData['USERS'][$userID]['ID'] = $arResultQueryCompany['USER_ID'];
                        $arData['USERS'][$userID]['TIMESTAMP_X'] = $arResultQueryCompany['TIMESTAMP_X'];
                        $arData['USERS'][$userID]['NAME'] = $arResultQueryCompany['NAME'];
                        $arData['USERS'][$userID]['PERSONAL_PHONE'] = $arResultQueryCompany['PERSONAL_PHONE'];
                        $arData['USERS'][$userID]['LOGIN'] = $arResultQueryCompany['LOGIN'];
                        $arData['USERS'][$userID]['EMAIL'] = $arResultQueryCompany['EMAIL'];
                        $arData['USERS'][$userID]['CONTRAGENTS'][$arResultQueryCompany['RELATION_ID_CONTRAGENT']] = [
                            'ID_CONTRAGENT' => $arResultQueryCompany['RELATION_ID_CONTRAGENT'],
                            'INN' => $arResultQueryCompany['INN'],
                            'STATUS' => $arResultQueryCompany['RELATION_STATUS'],
                        ];
                    }
                }
            }
        }

        return $arData;
    }

}