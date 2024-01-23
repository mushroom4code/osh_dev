<?php

namespace Bitrix\Sale\Exchange;

class EnteregoUserExchange
{

    public int $USER_ID = 0;
    public string $NAME = 'Пользователь';
    public string $EMAIL = '';
    public string $XML_ID;
    public string $PERSONAL_PHONE = '';
    public array $contragents_user = [];
    public array $company_user = [];

    /**
     * Get contragent for xml_id
     */
    public function loadUserId()
    {
        global $DB;

        if ($this->USER_ID) {
            $sql = "SELECT * FROM b_user WHERE `ID` = {$this->USER_ID}";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->PERSONAL_PHONE = $resultQuery['PERSONAL_PHONE'];
                $this->XML_ID = $resultQuery['XML_ID'];
                $this->EMAIL = $resultQuery['EMAIL'];
                $this->NAME = $resultQuery['NAME'];

            }
        }
    }

    /**
     * Get contragent User
     */
    public function loadContragentIdUser()
    {
        global $DB;
        $this->contragents_user = [];
        if ($this->USER_ID !== 0) {
            $CheckTable = "SELECT * FROM ent_user_contr_agent_role WHERE USER_ID = {$this->USER_ID}";
            $resultSelect = $DB->Query($CheckTable);
            while ($result = $resultSelect->Fetch()) {
                if (!empty($result['CONTR_AGENT_ID'])) {
                    $this->contragents_user[] = $result['CONTR_AGENT_ID'];
                }
            }
        }
    }

    /**
     * GET user for xml_id
     */
    public function loadUserXMLId()
    {
        global $DB;

        if ($this->XML_ID) {
            $sql = "SELECT * FROM b_user WHERE `XML_ID` = {$this->XML_ID}";
            $resQuery = $DB->Query($sql);
            if ($resultQuery = $resQuery->Fetch()) {
                $this->USER_ID = (int)$resultQuery['ID'];
            }
        }
    }


    /**
     * Get active and access contragents for user
     */
    public function GetActiveContrAgentForUserPrice()
    {
        global $DB;
        $this->contragents_user = [];
        if ($this->USER_ID !== 0) {
            $sql = "SELECT ent_user_contr_agent_role.CONTR_AGENT_ID
                        FROM ent_user_contr_agent_role
                                 LEFT JOIN ent_contagents as CONTRS
                                           ON (CONTRS.ID_CONTRAGENT = ent_user_contr_agent_role.CONTR_AGENT_ID)
                        WHERE STATUS_USER = 'admin'
                          AND CONTR_AGENT_ACTIVE = 1
                          AND ARCHIVED = 0
                          AND USER_ID = {$this->USER_ID}";
            $resQuery = $DB->Query($sql);

            if ($arResult = $resQuery->Fetch()) {
                $this->contragents_user[] = $arResult['CONTR_AGENT_ID'];
            }
        }
    }

    public function GetActiveContrAgentForUserForOrder()
    {
        global $DB;
        $this->contragents_user = [];
        if ($this->USER_ID !== 0) {
            $this->loadContragentIdUser();
            if ($this->contragents_user) {
                $contr_agent_ids = implode(',', $this->contragents_user);

                if ($contr_agent_ids) {
                    $sql = "SELECT * FROM  ent_contagents WHERE ID_CONTRAGENT IN($contr_agent_ids) AND CONTR_AGENT_ACTIVE = 1";
                    $resQuery = $DB->Query($sql);
                    $this->contragents_user = [];
                    while ($arResult = $resQuery->Fetch()) {
                        $this->contragents_user[$arResult['CONTR_AGENT_ID']] = $arResult;
                    }
                }

                $this->customArray();
            }
        }
    }


    /**
     * Get Contragents active and access for admin
     */
    public function GetContragentsUser()
    {
        global $DB;

        if ($this->USER_ID !== 0) {
            $this->loadContragentIdUser();
            if ($this->contragents_user) {
                $contr_agent_ids = implode(',', $this->contragents_user);

                if ($contr_agent_ids) {
                    $sql = "SELECT * FROM  ent_contagents WHERE ID_CONTRAGENT IN($contr_agent_ids)";
                    $resQuery = $DB->Query($sql);
                    $this->contragents_user = [];
                    while ($arResult = $resQuery->Fetch()) {
                        $this->contragents_user[$arResult['CONTR_AGENT_ID']] = $arResult;
                    }
                }

            }
        }
    }

    private function customArray()
    {
        global $DB;
        if ($this->contragents_user) {
            foreach ($this->contragents_user as $agent) {
                if (!empty($agent)) {
                    $sql = "SELECT ent_company_contr_agent.COMPANY_ID, ContragentWorkers.CONTR_AGENT_ID,
                                       ContragentWorkers.USER_ID
                                FROM ent_company_contr_agent
                                      INNER JOIN ent_user_contr_agent_role AS ContragentWorkers
                                            ON (ent_company_contr_agent.CONTR_AGENT_ID = ContragentWorkers.CONTR_AGENT_ID)
                                            AND ContragentWorkers.CONTR_AGENT_ID = {$agent['CONTR_AGENT_ID']}
                                            AND ContragentWorkers.STATUS_USER = 'user'";
                    $result_query = $DB->Query($sql);
                    while ($result = $result_query->Fetch()) {
                        $this->contragents_user[$agent['CONTR_AGENT_ID']]['COMPANY'][$result['COMPANY_ID']] = $result['COMPANY_ID'];
                        $this->contragents_user[$agent['CONTR_AGENT_ID']]['USER'][$result['USER_ID']] = $result['USER_ID'];
                    }
                }
            }
        }
    }

    /**
     * Get Company active and access for admin
     */
    public function GetCompanyForUser()
    {
        global $DB;

        if ($this->USER_ID !== 0) {
            $CheckTable = "SELECT * FROM ent_user_company_role WHERE USER_ID = {$this->USER_ID}";
            $resultSelect = $DB->Query($CheckTable);
            $this->company_user = [];
            while ($result = $resultSelect->Fetch()) {
                if (!empty($result['USER_ID']) || isset($result['USER_ID'])) {
                    $company_id = $result['COMPANY_ID'];
                    $USER_STATUS = $result['STATUS_USER'];
                    if (!empty($company_id)) {
                        if ($USER_STATUS === 'admin') {
                            $sql = "SELECT * FROM  ent_company WHERE `COMPANY_ID` = $company_id";
                            $resQuery = $DB->Query($sql);
                            $this->company_user['ADMIN'][] = $resQuery->Fetch();
                        }
                        if ($USER_STATUS === 'user') {
                            $sql = "SELECT * FROM  ent_company WHERE `COMPANY_ID` = $company_id AND ARCHIVED = 0";
                            $resQuery = $DB->Query($sql);
                            $this->company_user['USER'][] = $resQuery->Fetch();
                        }
                    }
                }
            }

        }
    }


    /**
     * Set role for user - contragent
     */
    protected function setRoleUserContragent()
    {
        global $DB;

        $sql_remove = "DELETE FROM ent_user_contr_agent_role WHERE USER_ID = {$this->USER_ID} ";
        $DB->Query($sql_remove);
        foreach ($this->contragents_user as $item => $id) {
            if (!empty($id)) {
                $sql_insert = "INSERT INTO ent_user_contr_agent_role(`USER_ID`,`CONTR_AGENT_ID`,`STATUS_USER`)
                                    VALUES ({$this->USER_ID},$id,'admin')";
                $DB->Query($sql_insert);
            }
        }

    }

    /**
     * Set role for user - company
     */

    protected function setRoleUserCompany()
    {
        global $DB;

        $sql_remove = "DELETE FROM ent_user_company_role WHERE USER_ID = {$this->USER_ID} ";
        $DB->Query($sql_remove);
        foreach ($this->company_user as $item => $id) {
            if (!empty($id)) {
                $sql_insert = "INSERT INTO ent_user_company_role(`USER_ID`,`COMPANY_ID`,`STATUS_USER`)
                                    VALUES ({$this->USER_ID},$id,'admin')";
                $DB->Query($sql_insert);
            }
        }

    }

    /**
     *Save user params in DB And Create new user
     */
    public function saveUserDB()
    {
        global $DB;

        if ($this->USER_ID !== 0) {
            $sql = "UPDATE b_user SET `NAME`= '{$this->NAME}', `EMAIL`= '{$this->EMAIL}',
                  `PERSONAL_PHONE` = {$this->PERSONAL_PHONE} WHERE `ID` = {$this->USER_ID}";
        } else {
            $login = $this->PERSONAL_PHONE;
            if($this->PERSONAL_PHONE === ''){
                $login = $this->EMAIL;
            }
            $sql = "INSERT INTO b_user(`XML_ID`,`LOGIN`,`NAME`,`EMAIL`,`PERSONAL_PHONE`)
                        VALUES ('{$this->XML_ID}','$login','{$this->NAME}','{$this->EMAIL}',{$this->PERSONAL_PHONE})";
        }
        $resQuery = $DB->Query($sql);

        if ($resQuery->result) {
            $this->USER_ID = (string)$DB->LastID();
            if ($this->contragents_user) {
                $this->setRoleUserContragent();
            }
            if ($this->company_user) {
                $this->setRoleUserCompany();
            }
        }

    }

}