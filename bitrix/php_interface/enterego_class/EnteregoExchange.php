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
        $sql = "SELECT ent_contr_agents.ADDRESS,ent_contr_agents.INN,ent_contr_agents.NAME_CONT,
                       ent_contr_agents.ARCHIVED,ent_contr_agents.CONTR_AGENT_ACTIVE,
                       ent_contr_agents.CONTR_AGENT_ID,ent_contr_agents.STATUS_PERSON,
                       ent_contr_agents.XML_ID as XML_ID_CONTRAGENT,
                       ent_contr_agents.DATE_EDIT,companyContrs.COMPANY_ID
                FROM ent_contr_agents
                LEFT JOIN ent_company_contr_agent as companyContrs 
                    ON (ent_contr_agents.CONTR_AGENT_ID = companyContrs.CONTR_AGENT_ID)";

        $resultQueryContr = $DB->Query($sql);
        while ($arResultQuery = $resultQueryContr->Fetch()) {
            if (!empty($arResultQuery)) {
                $dateElement = strtotime($arResultQuery['DATE_EDIT']);
                if ($dateElement > $dateImport1C) {
                    if($arResultQuery['INN'] === 'Не указан'){
                        $arResultQuery['INN'] = '';
                    }
                    if($arResultQuery['ADDRESS'] === 'Не указан'){
                        $arResultQuery['ADDRESS'] = '';
                    }

                    $arData['CONTRAGENTS'][] = [
                        'INN' => $arResultQuery['INN'],
                        'NAME' => $arResultQuery['NAME_CONT'],
                        'ADDRESS' => $arResultQuery['ADDRESS'],
                        'ARCHIVED' => $arResultQuery['ARCHIVED'],
                        'CONTR_AGENT_ACTIVE' => $arResultQuery['CONTR_AGENT_ACTIVE'],
                        'ID' => $arResultQuery['XML_ID_CONTRAGENT'],
                        'STATUS_PERSON' => $arResultQuery['STATUS_PERSON'],
                    ];
                }
            }
        }
        /**
         * select company for XML
         */
        $sqlComp = "SELECT * FROM ent_company
                      LEFT JOIN ent_company_contr_agent AS connection_comp_contr
                                ON (ent_company.COMPANY_ID = connection_comp_contr.COMPANY_ID);";

        $resultQueryCompany = $DB->Query($sqlComp);

        while ($arResultQueryCompany = $resultQueryCompany->Fetch()) {
            if (!empty($arResultQueryCompany)) {
                $dateElements = strtotime($arResultQueryCompany['DATE_EDIT']);
                if ($dateElements > $dateImport1C) {
                    $company_id = !empty($arResultQueryCompany['XML_ID']) ? ($arResultQueryCompany['XML_ID'])
                        : ($arResultQueryCompany['COMPANY_ID']);
                    if($arResultQueryCompany['TIMES'] === 'Не задано'){
                        $arResultQueryCompany['TIMES'] = '';
                    }
                    if($arResultQueryCompany['ADDRESS'] === 'Не указан'){
                        $arResultQueryCompany['ADDRESS'] = '';
                    }
                    if($arResultQueryCompany['PHONE_COMPANY'] === 'Не указан'){
                        $arResultQueryCompany['PHONE_COMPANY'] = '';
                    }

                    $arData['COMPANY'][$company_id]['NAME'] = $arResultQueryCompany['NAME_COMP'];
                    $arData['COMPANY'][$company_id]['ADDRESS'] = $arResultQueryCompany['ADDRESS'];
                    $arData['COMPANY'][$company_id]['ARCHIVED'] = $arResultQueryCompany['ARCHIVED'];
                    $arData['COMPANY'][$company_id]['TIMES'] = $arResultQueryCompany['TIMES'];
                    $arData['COMPANY'][$company_id]['COMPANY_ID'] = $company_id;
                    $arData['COMPANY'][$company_id]['PHONE'] = $arResultQueryCompany['PHONE_COMPANY'];
                    $arData['COMPANY'][$company_id]['CONTRAGENTS'][$arResultQueryCompany['CONTR_AGENT_ID']] = [
                        'CONTR_AGENT_ID' => $arResultQueryCompany['CONTR_AGENT_ID'],
                    ];
                }
            }
        }
        /**
         * select user+contr+comp for XML
         */
        $sqlComp = "SELECT ent_company_contr_agent.COMPANY_ID,
                           ent_company_contr_agent.CONTR_AGENT_ID,
                           ent_user_company_role.USER_ID,
                           userMeta.PERSONAL_PHONE,
                           userMeta.LOGIN,
                           userMeta.NAME,
                           userMeta.EMAIL,
                           company.NAME_COMP,
                           company.DATE_EDIT as DATE_COMPANY,
                           contragent.DATE_EDIT as DATE_CONTR, 
                           contragent.INN,
                           contragent.XML_ID as XML_ID_CONTRAGENT,
                           company.XML_ID as XML_ID_COMPANY
                    FROM ent_company_contr_agent
                             LEFT JOIN ent_user_company_role
                                       ON (ent_company_contr_agent.COMPANY_ID = ent_user_company_role.COMPANY_ID)
                                           AND STATUS_USER = 'admin'
                             LEFT JOIN b_user as userMeta
                                       ON (ent_user_company_role.USER_ID = userMeta.ID)
                             LEFT JOIN ent_contr_agents as contragent
                                       ON (contragent.CONTR_AGENT_ID = ent_company_contr_agent.CONTR_AGENT_ID)
                             LEFT JOIN ent_company as company
                                       ON (company.COMPANY_ID = ent_company_contr_agent.COMPANY_ID);";
        $resultQueryCompany = $DB->Query($sqlComp);
        while ($arResultQueryCompany = $resultQueryCompany->Fetch()) {
            if (!empty($arResultQueryCompany)) {
                $dateElementsComp = strtotime($arResultQueryCompany['DATE_COMPANY']);
                $dateElementsContr = strtotime($arResultQueryCompany['DATE_CONTR']);
                if ($dateElementsComp > $dateImport1C || $dateElementsContr > $dateImport1C) {
                    $userID = $arResultQueryCompany['USER_ID'];
                    $company_id = !empty($arResultQueryCompany['XML_ID_COMPANY']) ? ($arResultQueryCompany['XML_ID_COMPANY'])
                        : ($arResultQueryCompany['COMPANY_ID']);
                    $contragent_id = !empty($arResultQueryCompany['XML_ID_CONTRAGENT']) ?
                        ($arResultQueryCompany['XML_ID_CONTRAGENT']) : ($arResultQueryCompany['CONTR_AGENT_ID']);

                    $arData['USERS_CONTR_COMP'][$userID]['ID'] = $arResultQueryCompany['USER_ID'];
                    $arData['USERS_CONTR_COMP'][$userID]['NAME'] = $arResultQueryCompany['NAME'];
                    $arData['USERS_CONTR_COMP'][$userID]['PERSONAL_PHONE'] = $arResultQueryCompany['PERSONAL_PHONE'];
                    $arData['USERS_CONTR_COMP'][$userID]['LOGIN'] = $arResultQueryCompany['LOGIN'];
                    $arData['USERS_CONTR_COMP'][$userID]['EMAIL'] = $arResultQueryCompany['EMAIL'];
                    $arData['USERS_CONTR_COMP'][$userID]['COMPANY'][$company_id] = [
                        'COMPANY_NAME' => $arResultQueryCompany['NAME_COMP'],
                        'COMPANY_ID' => $company_id,
                    ];
                    $arData['USERS_CONTR_COMP'][$userID]['CONTRAGENTS'][$contragent_id] = [
                        'CONTR_AGENT_ID' => $contragent_id,
                        'INN' => $arResultQueryCompany['INN'],
                    ];
                }
            }
        }

        return $arData;
    }

}