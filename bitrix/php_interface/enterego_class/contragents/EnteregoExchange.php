<?php

namespace Enterego\contragents;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Main;
use Enterego\ORM\EnteregoORMContragentsTable;

class EnteregoExchange
{
    /**
     * Import contragent-company-user
     * @param DateTime|string $stepDate
     * @param DateTime|string $dateStartImport1C
     * @param bool|int $id
     * @param string $type
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function GetInfoForXML(
        datetime|string $stepDate,
        datetime|string $dateStartImport1C,
        bool|int $id = 0,
        string   $type = ''): array
    {
        $arData = ['CONTRAGENTS' => [], 'USERS' => []];
        $dateExportStart = $_SESSION['START_DATETIME_EXPORT'];

        $step = !empty($stepDate) ? $stepDate : $dateStartImport1C;
        if (empty($type)) {
            /** select contragents for XML on date interval with id interval*/
            $filterContr = array(
                'select' => array('*'),
                'order' => array(
                    'ID_CONTRAGENT' => 'ASC'
                ),
                'limit' => 50,
            );

            if (!empty($step)) {
                $filterContr['filter'] = array(
                    ">DATE_UPDATE" => $step,
                );
            }

            $filterContr['filter']['<DATE_UPDATE'] = $dateExportStart;

            if (!empty($id)) {
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
                    'TIMESTAMP_X',
                    'USER_ID' => 'ID',
                    'XML_ID',
                    'RELATION_ID_CONTRAGENT' => 'RELATION.ID_CONTRAGENT',
                    'RELATION_STATUS' => 'RELATION.STATUS',
                    'CONTRAGENT',
                    'INN' => 'CONTRAGENT.INN',
                    'XML_ID_CONTR' => 'CONTRAGENT.XML_ID'
                ),
                'limit' => 50,
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
                "<TIMESTAMP_X" => $dateExportStart,
            );

            if (!empty($step)) {
                $filterUser['filter']['>TIMESTAMP_X'] = $step;
            }

            if (!empty($id)) {
                $filterUser['filter']['>ID'] = $id;
            }

            $resultQueryUsers = UserTable::getList($filterUser);

            if (!empty($resultQueryUsers)) {
                while ($arResultUser = $resultQueryUsers->Fetch()) {
                    if (!empty($arResultUser) && !empty($arResultUser['RELATION_ID_CONTRAGENT'])) {
                        $userID = $arResultUser['USER_ID'];
                        $arData['USERS'][$userID]['ID'] = $arResultUser['USER_ID'];
                        $arData['USERS'][$userID]['XML_ID'] = !empty($arResultUser['XML_ID']) ?
                            $arResultUser['XML_ID'] : $arResultUser['USER_ID'] ;
                        $arData['USERS'][$userID]['TIMESTAMP_X'] = $arResultUser['TIMESTAMP_X'];
                        $arData['USERS'][$userID]['NAME'] = $arResultUser['NAME'];
                        $arData['USERS'][$userID]['PERSONAL_PHONE'] = $arResultUser['PERSONAL_PHONE'];
                        $arData['USERS'][$userID]['LOGIN'] = $arResultUser['LOGIN'];
                        $arData['USERS'][$userID]['EMAIL'] = $arResultUser['EMAIL'];
                        $arData['USERS'][$userID]['CONTRAGENTS'][$arResultUser['RELATION_ID_CONTRAGENT']] = [
                            'ID_CONTRAGENT' => $arResultUser['RELATION_ID_CONTRAGENT'],
                            'XML_ID' => $arResultUser['XML_ID_CONTR'],
                            'INN' => $arResultUser['INN'],
                            'STATUS' => $arResultUser['RELATION_STATUS'],
                        ];
                    }
                }
            }
        }

        return $arData;
    }

}