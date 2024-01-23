<?php

namespace Bitrix\Sale\Exchange;

class EnteregoContragentExchange

{
    public int $ARCHIVED;
    public int $CONTR_AGENT_ID = 0;
    public int $CONTR_AGENT_ACTIVE;
    public string $INN = 'Не указан';
    public string $DATE_EDIT;
    public string $NAME_CONT;
    public string $ADDRESS = 'Не указан';
    public string $STATUS_PERSON = '';
    public string $XML_ID;


    /**
     * Get contragent for xml_id
     */
    public function loadContragentId()
    {
        global $DB;

        if ($this->CONTR_AGENT_ID) {
            $sql = "SELECT * FROM ent_contagents WHERE `ID_CONTRAGENT` = {$this->CONTR_AGENT_ID}";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->CONTR_AGENT_ID = $resultQuery['CONTR_AGENT_ID'];
                $this->XML_ID = $resultQuery['XML_ID'];
                $this->INN = $resultQuery['INN'];
                $this->ADDRESS = $resultQuery['ADDRESS'];
                $this->STATUS_PERSON = $resultQuery['STATUS_PERSON'];
                $this->CONTR_AGENT_ACTIVE = $resultQuery['CONTR_AGENT_ACTIVE'];
                $this->NAME_CONT = $resultQuery['NAME_CONT'];
                $this->ARCHIVED = $resultQuery['ARCHIVED'];
            }
        }
    }

    /**
     * Get contragent for xml_id
     * @param string $xml_id
     */
    public function loadContragentXMLId(string $xml_id)
    {
        global $DB;

        if (!empty($xml_id)) {
            $sql = "SELECT * FROM ent_contagents WHERE `XML_ID` = $xml_id";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->ID_CONTRAGENT = (int)$resultQuery['ID_CONTRAGENT'];

            }
        }
    }

    /**
     * * Get contragent for INN
     * @param string $inn
     */
    public function loadContragentINN(string $inn)
    {
        global $DB;

        if (!empty($xml_id)) {
            $sql = "SELECT * FROM ent_contagents WHERE `INN` = $inn";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->ID_CONTRAGENT = (int)$resultQuery['ID_CONTRAGENT'];
            }
        }
    }

    /**
     * Set role for user or contragent
     * @param $user_id
     * @param $contragent_id
     * @param $status
     */
    protected function setRole($user_id, $contragent_id, $status)
    {
        global $DB;
        $user_company = "INSERT INTO ent_user_contr_agent_role(USER_ID,CONTR_AGENT_ID,STATUS_USER)
                                VALUES ($user_id,$contragent_id,'$status')";
        $DB->Query($user_company);
    }

    /**
     * Create Contragents
     * @param $user_id
     * @param array $workers
     * @param array $company
     */
    public function CreateContragents($user_id, array $workers, array $company)
    {
        $this->SetConnectionCompanyContrs($this->CONTR_AGENT_ID, $company, $workers);
        $this->setRole($user_id, $this->CONTR_AGENT_ID, 'admin');
    }

    /**
     * Set connection for company and contragent
     * @param $contr_agent_id
     * @param array $arDataContrCompany
     * @param array $workersAr
     * @return string
     */
    public function SetConnectionCompanyContrs($contr_agent_id, array $arDataContrCompany, array $workersAr): string
    {

        global $DB;
        $resultSetConnection = '';

        if ($contr_agent_id !== 0) {

            $sql_delete = "DELETE FROM ent_company_contr_agent WHERE CONTR_AGENT_ID = $contr_agent_id ";
            $DB->Query($sql_delete);
            if (!empty($arDataContrCompany)) {
                foreach ($arDataContrCompany as $idCompany) {
                    $sql = "INSERT INTO ent_company_contr_agent(CONTR_AGENT_ID,COMPANY_ID)  VALUES ($contr_agent_id,$idCompany)";
                    $DB->Query($sql);
                }
            }

            $sql_delete = "DELETE FROM ent_user_contr_agent_role WHERE CONTR_AGENT_ID = $contr_agent_id AND STATUS_USER = 'user'";
            $DB->Query($sql_delete);
            if (!empty($arDataContrCompany)) {
                foreach ($workersAr as $idWorker) {
                    $sql = "INSERT INTO ent_user_contr_agent_role(CONTR_AGENT_ID,USER_ID,STATUS_USER) 
                                VALUES ($contr_agent_id,$idWorker,'user')";
                    $DB->Query($sql);
                }
            }
        }
        return $resultSetConnection;
    }

    /**
     * Edit company or contragents info - for admin
     * TODO сделать проверку на пользователя, админ или нет
     * TODO добавить просталвение прав на контрагента при корректировании + связи
     * @param $user_id
     * @param $name_id
     * @param $id
     * @param $method
     * @param array $arParams
     * @return bool
     */
    public function Edit($user_id, $name_id, $id, $method, array $arParams): bool
    {
        global $DB;
        $result = false;
        if (!empty($user_id)) {
            if (!empty($method)) {
                $params = '';
                $name = $arParams['NAME'];
                $address = $arParams['ADDRESS'];
                $date = date(DATE_ATOM);
                if ($method === 'ent_contr_agents') {
                    $company = $arParams["company"];
                    $workers = $arParams["workers"];
                    $INN = $arParams['INN'];
                    $params = "NAME_CONT = '$name',ADDRESS ='$address',INN = '$INN',DATE_EDIT = '$date'";
                    $this->SetConnectionCompanyContrs($id, $company, $workers);
                } else if ($method === 'ent_company') {
                    $TIMES = $arParams['TIMES'];
                    $PHONE = $arParams['PHONE'];
                    $params = "NAME_COMP = '$name',ADDRESS ='$address',TIMES = '$TIMES',PHONE_COMPANY ='$PHONE',
                    DATE_EDIT = '$date'";
                }
                $CheckTable = "UPDATE $method  SET $params
                              WHERE  $name_id = $id";
                $result_update = $DB->Query($CheckTable);
                if ($result_update) {
                    $result = true;
                }
            } else {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function saveContragentDB(): bool
    {
        global $DB;
        $saveDB = false;

        if ($this->CONTR_AGENT_ID !== 0) {
            $inn = '';
            $address = '';
            if ($this->INN !== 'Не указан') {
                $inn = "INN= '{$this->INN}',";
            }
            if ($this->ADDRESS !== 'Не указан') {
                $address = "ADDRESS= '{$this->ADDRESS}',";
            }
            $sql = "UPDATE ent_contagents SET $inn $address
                    NAME_CONT= '{$this->NAME_CONT}', DATE_EDIT= '{$this->DATE_EDIT}', ARCHIVED= {$this->ARCHIVED},
                    CONTR_AGENT_ACTIVE = $this->CONTR_AGENT_ACTIVE  WHERE ID_CONTRAGENT = {$this->ID_CONTRAGENT}";
        } else {
            $sql_new = "INSERT INTO ent_contagents(`INN`,`ADDRESS`,`CONTR_AGENT_ACTIVE`,`NAME_CONT`,`DATE_EDIT`,
                             `ARCHIVED`,`STATUS_PERSON`)
                    VALUES ('{$this->INN}','{$this->ADDRESS}','{$this->CONTR_AGENT_ACTIVE}','{$this->NAME_CONT}',
                    '{$this->DATE_EDIT}',{$this->ARCHIVED},'{$this->STATUS_PERSON}')";
            $DB->Query($sql_new);
            $new_xml_id = (string)$DB->LastID();
            $xml_id = $this->XML_ID !== '0' ? $this->XML_ID : $new_xml_id;
            $this->ID_CONTRAGENT = $xml_id;
            $sql = "UPDATE ent_contagents SET XML_ID= '$xml_id' WHERE ID_CONTRAGENT = $new_xml_id";
        }

        $resQuery = $DB->Query($sql);
        if ($resQuery->result) {
            $saveDB = true;
        }
        return $saveDB;

    }

}