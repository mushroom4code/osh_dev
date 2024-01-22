<?php
namespace Enterego;

use COption;

class EnteregoExchange
{
    /**
     * Import contragent-company-user
     * @return array
     */
    public static function GetInfoForXML(): array
    {
        $arData = [];

        global $DB;
        $dateImport1C = strtotime(COption::GetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS'));

        /**
         * select contragents for XML
         */
        $sql = "SELECT * FROM ent_contagents ";
//        WHERE ent_contr_agents.DATE_UPDATE >

        $resultQueryContr = $DB->Query($sql);
        while ($arResultQuery = $resultQueryContr->Fetch()) {
            if (!empty($arResultQuery)) {
                $dateElement = strtotime($arResultQuery['DATE_UPDATE']);
                if ($dateElement > $dateImport1C || $dateImport1C === false) {
                    if($arResultQuery['INN'] === 'Не указан'){
                        $arResultQuery['INN'] = '';
                    }
                    if($arResultQuery['ADDRESS'] === 'Не указан'){
                        $arResultQuery['ADDRESS'] = '';
                    }

                    $arData['CONTRAGENTS'][] = [
                        'INN' => $arResultQuery['INN'],
                        'ID_CONTRAGENT' => $arResultQuery['ID_CONTRAGENT'],
                        'NAME_ORGANIZATION' => $arResultQuery['NAME_ORGANIZATION'],
                        'STATUS_CONTRAGENT' => $arResultQuery['STATUS_CONTRAGENT'],
                        'ADDRESS' => $arResultQuery['ADDRESS'],
                        'STATUS_VIEW' => $arResultQuery['STATUS_VIEW'],
                        'RASCHET_CHET' => $arResultQuery['RASCHET_CHET'],
                        'PHONE_COMPANY' => $arResultQuery['PHONE_COMPANY'],
                        'BIC' => $arResultQuery['BIC'],
                        'BANK' => $arResultQuery['BANK'],
                        'EMAIL' => $arResultQuery['EMAIL'],
                        'XML_ID' => $arResultQuery['XML_ID'],
                        'TYPE' => $arResultQuery['TYPE'],
                        'STATUS_PERSON' => $arResultQuery['STATUS_PERSON'],
                    ];
                }
            }
        }
        /**
         * select user+contr+comp for XML
         */
        $sqlComp = "SELECT ent_contragent_user_relationships.USER_ID,
                           ent_contragent_user_relationships.ID_CONTRAGENT,
                           ent_contragent_user_relationships.STATUS,
                           contragent.XML_ID,
                           userMeta.PERSONAL_PHONE,
                           userMeta.LOGIN,
                           userMeta.NAME,
                           userMeta.EMAIL,
                           contragent.DATE_UPDATE
                    FROM ent_contragent_user_relationships
                             LEFT JOIN b_user as userMeta
                                       ON (ent_contragent_user_relationships.USER_ID = userMeta.ID)
                             LEFT JOIN ent_contagents as contragent
                                       ON (contragent.ID_CONTRAGENT = ent_contragent_user_relationships.ID_CONTRAGENT)
                         ";
        $resultQueryCompany = $DB->Query($sqlComp);
        while ($arResultQueryCompany = $resultQueryCompany->Fetch()) {
            if (!empty($arResultQueryCompany)) {
                $dateElementsComp = strtotime($arResultQueryCompany['DATE_UPDATE']);
                if ($dateElementsComp > $dateImport1C || $dateImport1C === false)  {
                    $userID = $arResultQueryCompany['USER_ID'];
                    $contragent_id = !empty($arResultQueryCompany['XML_ID']) ?
                        ($arResultQueryCompany['XML_ID']) : ($arResultQueryCompany['ID_CONTRAGENT']);

                    $arData['USERS_CONTR_COMP'][$userID]['ID'] = $arResultQueryCompany['USER_ID'];
                    $arData['USERS_CONTR_COMP'][$userID]['NAME'] = $arResultQueryCompany['NAME'];
                    $arData['USERS_CONTR_COMP'][$userID]['PERSONAL_PHONE'] = $arResultQueryCompany['PERSONAL_PHONE'];
                    $arData['USERS_CONTR_COMP'][$userID]['LOGIN'] = $arResultQueryCompany['LOGIN'];
                    $arData['USERS_CONTR_COMP'][$userID]['EMAIL'] = $arResultQueryCompany['EMAIL'];
                    $arData['USERS_CONTR_COMP'][$userID]['CONTRAGENTS'][$contragent_id] = [
                        'CONTR_AGENT_ID' => $arResultQueryCompany['ID_CONTRAGENT'],
                        'XML_ID'=>$arResultQueryCompany['XML_ID'],
                        'INN' => $arResultQueryCompany['INN'],
                    ];
                }
            }
        }

        return $arData;
    }

}