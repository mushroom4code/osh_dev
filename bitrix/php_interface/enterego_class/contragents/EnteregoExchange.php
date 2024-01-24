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
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function GetInfoForXML(): array
    {
        $arData = [];

        $dateImport1C = strtotime(COption::GetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS'));
        if (!$dateImport1C) {
            $dateImport1C = date(DATE_ATOM);
        }
        /**
         * select contragents for XML
         * TODO - получение контров только тех у которых дата создания и изменения отличается от даты обмена
         * TODO - + переделка под orm
         */

        $resultQueryContr = EnteregoORMContragentsTable::getList(
            array(
                'select' => array('*'),
                'filter' => array(
                    ">=DATE_UPDATE" => $dateImport1C
                ),
                'order' => array(
                    'DATE_UPDATE' => 'DESC'
                ),
                'limit' => '100'
            )
        );

        if (!empty($resultQueryContr)) {
            while ($arResultQuery = $resultQueryContr->Fetch()) {
                $arData['CONTRAGENTS'][] = $arResultQuery;
            }
        }
        /**
         * select user+contr+comp for XML
         * TODO - связь между пользователем и контром + сам пользователь + ORM
         */
        $resultQueryUsers = UserTable::getList(
            array(
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
                'filter' => array(
                    ">=TIMESTAMP_X" => $dateImport1C,
                ),
                'limit' => '100',
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
                    ))
            ));

        if (!empty($resultQueryUsers)) {
            while ($arResultQueryCompany = $resultQueryUsers->Fetch()) {
                if (!empty($arResultQueryCompany) && !empty($arResultQueryCompany['RELATION_ID_CONTRAGENT'])) {
                    $userID = $arResultQueryCompany['USER_ID'];
                    $arData['USERS_CONTR_COMP'][$userID]['ID'] = $arResultQueryCompany['USER_ID'];
                    $arData['USERS_CONTR_COMP'][$userID]['NAME'] = $arResultQueryCompany['NAME'];
                    $arData['USERS_CONTR_COMP'][$userID]['PERSONAL_PHONE'] = $arResultQueryCompany['PERSONAL_PHONE'];
                    $arData['USERS_CONTR_COMP'][$userID]['LOGIN'] = $arResultQueryCompany['LOGIN'];
                    $arData['USERS_CONTR_COMP'][$userID]['EMAIL'] = $arResultQueryCompany['EMAIL'];
                    $arData['USERS_CONTR_COMP'][$userID]['CONTRAGENTS'][$arResultQueryCompany['RELATION_ID_CONTRAGENT']] = [
                        'ID_CONTRAGENT' => $arResultQueryCompany['RELATION_ID_CONTRAGENT'],
                        'INN' => $arResultQueryCompany['INN'],
                        'STATUS' => $arResultQueryCompany['RELATION_STATUS'],
                    ];
                }
            }
        }

        return $arData;
    }

}