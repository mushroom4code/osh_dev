<?php

namespace Bitrix\Sale\Exchange;

class EnteregoCompanyExchange
{

    public int $COMPANY_ID = 0;
    public string $PHONE_COMPANY = 'Не указан';
    public string $DATE_EDIT;
    public string $NAME_COMP;
    public string $ADDRESS = 'Не указан';
    public int $ARCHIVED;
    public string $TIMES = 'Не задано';
    public string $XML_ID;
    public string $STATUS_USER;

    public array $contragentIds;

    /**
     * Get and load company returned company object, if she's not empty
     * And result bool param searching company in DB
     */
    public function loadCompanyById()
    {
        global $DB;

        if (!empty($this->COMPANY_ID)) {
            $sql = "SELECT * FROM ent_company WHERE `COMPANY_ID` = {$this->COMPANY_ID}";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->COMPANY_ID = (int)$resultQuery['COMPANY_ID'];
                $sql_info = "SELECT * FROM  ent_company_contr_agent WHERE COMPANY_ID = {$this->COMPANY_ID}";
                $resultSelectInfo = $DB->Query($sql_info);
                $this->contragentIds = [];
                while ($arResult = $resultSelectInfo->Fetch()){
                    $this->contragentIds[] = $arResult['CONTR_AGENT_ID'];
                }
            }

        }
    }

    /**
     * Get and load company returned company object, if she's not empty
     * And result bool param searching company in DB
     * @param string $xml_id
     */
    public function loadCompanyByXMLId(string $xml_id)
    {
        global $DB;

        if (!empty($xml_id)) {
            $sql = "SELECT * FROM ent_company WHERE `XML_ID` = $xml_id";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->COMPANY_ID = (int)$resultQuery['COMPANY_ID'];
            }
        }
    }

    /**
     * Get and load company returned company object, if she's not empty
     * And result bool param searching company in DB
     * @param string $company_name
     * @param string $phone_company
     */
    public function loadCompanyByName(string $company_name, string $phone_company)
    {
        global $DB;
        if (!empty($company_name)) {
            $sql = "SELECT * FROM ent_company WHERE `NAME_COMP` = '$company_name' AND `PHONE_COMPANY` = '$phone_company'";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->COMPANY_ID = (int)$resultQuery['COMPANY_ID'];
            }
        }
    }

    /**
     * @param $user_id
     */
    public function setRole($user_id)
    {
        global $DB;
        $company_id = $this->COMPANY_ID;
        if (!empty($company_id) && !empty($user_id)) {
            $user_company = "INSERT INTO ent_user_company_role(USER_ID,COMPANY_ID,STATUS_USER)
                                VALUES ($user_id,$company_id,'admin')";
            $DB->Query($user_company);
        }
    }

    /**
     * Save new params in DB - UPDATE or INSERT
     * @return bool
     */
    public function saveCompanyDB(): bool
    {
        global $DB;
        $saveDB = false;

        if ($this->COMPANY_ID !== 0) {
            $phone = '';
            $address = '';
            $times= '';
            if($this->PHONE_COMPANY !== 'Не указан'){
               $phone = "PHONE_COMPANY= '{$this->PHONE_COMPANY}',";
            }
            if($this->ADDRESS !== 'Не указан'){
                $address = "ADDRESS= '{$this->ADDRESS}',";
            }
            if($this->TIMES !== 'Не задано'){
                $times = "TIMES= '{$this->TIMES}',";
            }
            $sql = "UPDATE ent_company SET $phone $address $times
            NAME_COMP= '{$this->NAME_COMP}', DATE_EDIT= '{$this->DATE_EDIT}', ARCHIVED= {$this->ARCHIVED}  
            WHERE COMPANY_ID = {$this->COMPANY_ID}";
        } else {
            $sql_new = "INSERT INTO ent_company(`PHONE_COMPANY`,`ADDRESS`,`TIMES`,`NAME_COMP`,`DATE_EDIT`,`ARCHIVED`)
                    VALUES ('{$this->PHONE_COMPANY}','{$this->ADDRESS}','{$this->TIMES}','{$this->NAME_COMP}',
                            '{$this->DATE_EDIT}',{$this->ARCHIVED})";
            $DB->Query($sql_new);
            $this->COMPANY_ID = $DB->LastID();
            $xml_id = empty($this->XML_ID) ? $this->COMPANY_ID : $this->XML_ID;
            $sql = "UPDATE ent_company SET XML_ID= '$xml_id' WHERE COMPANY_ID = {$this->COMPANY_ID }";
        }

        $resQuery = $DB->Query($sql);
        if ($resQuery->result) {
            $saveDB = true;
        }
        return $saveDB;
    }


}