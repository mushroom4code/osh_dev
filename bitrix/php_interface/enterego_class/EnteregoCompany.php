<?php

namespace Enterego;

use CSaleOrder;

class EnteregoCompany
{

    public static function CreateTable($NAME_TABLE): bool
    {
        global $DB;

        $sql = '';
        $indexes = '';
        $create = false;

        if ($NAME_TABLE === 'ent_company') {
            $sql = "CREATE TABLE IF 
                        NOT EXISTS ent_company(
                                COMPANY_ID  INT(255) auto_increment PRIMARY KEY,
                                PHONE_COMPANY CHAR(255) DEFAULT NULL,
                                ADDRESS CHAR(1) DEFAULT NULL,
                                TIMES CHAR(255) DEFAULT NULL,
                                NAME_COMP VARCHAR(255),
                                ARCHIVED INT(1) DEFAULT 0 NOT NULL,
                                DATE_EDIT VARCHAR(255) DEFAULT '0000-01-01T00:00:00+00:00',
                                            )";
            $indexes = "CREATE INDEX ent_company_COMPANY_ID_INN_COMPANY_index
                            ON ent_company (COMPANY_ID, INN_COMPANY);";
        } else if ($NAME_TABLE === 'ent_contr_agents') {
            $sql = "CREATE TABLE IF NOT EXISTS ent_contr_agents(
                        CONTR_AGENT_ID  INT(255) auto_increment PRIMARY KEY,
                        INN VARCHAR(255) DEFAULT NULL,
                        ADDRESS TEXT(1) DEFAULT NULL,
                        CHET_CONTR_AGENT VARCHAR(255) DEFAULT NULL,
                        ARCHIVED INT(1) DEFAULT 0 NOT NULL,
                        NAME_CONT VARCHAR(255),
                        CONTR_AGENT_ACTIVE INT(1) DEFAULT 0 NOT NULL,
                        DATE_EDIT VARCHAR(255) DEFAULT '0000-01-01T00:00:00+00:00',
                        )";
            $indexes = "CREATE INDEX ent_contr_agents_CONTR_AGENT_ID_index
                            ON ent_contr_agents (CONTR_AGENT_ID);";
        } else if ($NAME_TABLE === 'ent_company_contr_agent') {
            $sql = "create table ent_company_contr_agent(
                        COMPANY_ID int(255) default NULL null,
                        CONTR_AGENT_ID int(255) default NULL null
                        )";
            $indexes = "create index ent_company_contr_agent_COMPANY_ID_CONTR_AGENT_ID_index
        on ent_company_contr_agent (COMPANY_ID, CONTR_AGENT_ID);";
        } else if ($NAME_TABLE === 'ent_user_company_role') {
            $sql = "create table ent_user_company(
                            USER_ID int(255) not null,
                            COMPANY_ID int(255) not null,
                            STATUS_USER varchar(255) not null
                        )";
            $indexes = "create index ent_user_company_role_USER_ID_COMPANY_ID_index
                on ent_user_company (USER_ID, COMPANY_ID)";
        } else if ($NAME_TABLE === 'ent_user_contr_agent_role') {
            $sql = "create table ent_user_contr_agent_role(
                    USER_ID int(255) not null,
                    CONTR_AGENT_ID int(255) not null,
                    STATUS_USER varchar(255) not null
                )";
            $indexes = "create index ent_user_contr_agent_role_USER_ID_CONTR_AGENT_ID_index
                 on ent_user_company (USER_ID, CONTR_AGENT_ID)";
        } else if ($NAME_TABLE === 'ent_add_workers') {
            $sql = "create table ent_add_workers(
                    USER_ID_ADMIN  int(255) not null,
                    USER_ID_WORKER int(255) not null
                );";
            $indexes = "create index ent_add_workers_USER_ID_ADMIN_USER_ID_WORKER_index
                on ent_add_workers (USER_ID_ADMIN, USER_ID_WORKER);";
        }

        $result = $DB->Query($sql);
        $DB->Query($indexes);
        if ($result->Fetch()) {
            $create = true;
        }

        return $create;
    }

    public static function DropTable($tableName): bool
    {
        global $DB;

        $sql = "DROP TABLE IF EXISTS $tableName";
        $DB->Query($sql);
        return true;
    }

    /**
     * Create workers
     * TODO сделать добалвение пользователя в таблицу в вебе при успешном доаблвении в бд
     * @param $user_id
     * @param $worker_id
     * @return bool
     */
    public static function CreateWorkers($user_id, $worker_id): bool
    {
        global $DB;

        if (!empty($user_id) && !empty($worker_id)) {
            $sql = "INSERT INTO ent_add_workers (`USER_ID_ADMIN`,`USER_ID_WORKER`)
                                VALUES ($user_id,$worker_id)";

            $result = $DB->Query($sql);
            return (bool)$result;
        } else {
            return false;
        }
    }

    /**
     * Get Workers active and access for admin
     * @param $user_id
     * @return array
     */
    public static function GetWorkers($user_id): array
    {
        $arUsers = [];

        global $DB;

        if (!empty($user_id)) {
            $CheckTable = "SELECT userTable.USER_ID_WORKER,workerRoleCompany.COMPANY_ID,
                                   workerRoleContrAgent.CONTR_AGENT_ID,userMeta.PERSONAL_PHONE,  userMeta.LOGIN,
                                   userMeta.NAME,userMeta.EMAIL,infoCompany.NAME_COMP,infoContragent.NAME_CONT, ACTIVE
                            FROM ent_add_workers as userTable
                                     LEFT JOIN ent_user_company_role as workerRoleCompany
                                                ON (userTable.USER_ID_WORKER = workerRoleCompany.USER_ID)
                                                    AND  workerRoleCompany.STATUS_USER = 'user'
                                     LEFT JOIN ent_user_contr_agent_role as workerRoleContrAgent
                                                ON (userTable.USER_ID_WORKER = workerRoleContrAgent.USER_ID)
                                                    AND  workerRoleContrAgent.STATUS_USER = 'user'
                                     LEFT JOIN b_user as userMeta
                                                ON (userTable.USER_ID_WORKER = userMeta.ID)
                                     LEFT JOIN ent_company as infoCompany
                                                ON (infoCompany.COMPANY_ID = workerRoleCompany.COMPANY_ID)
                                     LEFT JOIN ent_contr_agents as infoContragent
                                               ON (infoContragent.CONTR_AGENT_ID = workerRoleContrAgent.CONTR_AGENT_ID)
                            WHERE userTable.`USER_ID_ADMIN` = $user_id";
            $resultSelect = $DB->Query($CheckTable);
            while ($result = $resultSelect->Fetch()) {
                $arUsers['COMPANY'][] = [
                    'COMPANY_ID' => $result['COMPANY_ID'],
                    'NAME_COMP' => $result['NAME_COMP']
                ];
                $arUsers['CONTR_AGENT'][] = [
                    'CONTR_AGENT_ID' => $result['CONTR_AGENT_ID'],
                    'NAME_CONT' => $result['NAME_CONT']
                ];
                $arUsers['WORKERS'][] = $result;
            }

        }

        return $arUsers;
    }

    /**
     * Get workers for create contragent
     * @param $user_id
     * @return array
     */
    public static function GetWorkersInfo($user_id): array
    {
        $arUsers = [];

        global $DB;

        if (!empty($user_id)) {
            $CheckTable = "SELECT userTable.USER_ID_WORKER,userMeta.LOGIN,userMeta.NAME
                           FROM ent_add_workers as userTable
                                     LEFT JOIN b_user as userMeta
                                               ON (userTable.USER_ID_WORKER = userMeta.ID)
                           WHERE userTable.`USER_ID_ADMIN` =  $user_id;";
            $resultSelect = $DB->Query($CheckTable);
            while ($result = $resultSelect->Fetch()) {
                $arUsers['WORKERS'][] = $result;
            }

        }
        return $arUsers;
    }


    /**
     * Get active and access  orders, company and contragents for worker
     * @param $user_id
     * @param $worker_id
     * @return array
     */
    public static function GetAccessFromWorkers($user_id, $worker_id): array
    {
        $arUsers = [];

        global $DB;

        if (!empty($user_id)) {
            $CheckTable = "SELECT ent_user_company_role.COMPANY_ID,
                                   userCompany.COMPANY_ID as userCompanyID,
                                   userAgent.CONTR_AGENT_ID as userContrAgentID,
                                   contrAgentAdmin.CONTR_AGENT_ID,
                                   infoCompany.NAME_COMP,
                                   infoCompany.PHONE_COMPANY,
                                   infoContragent.NAME_CONT,
                                   infoContragent.INN
                            FROM ent_user_company_role
                                     LEFT JOIN ent_user_company_role as companyAdmin
                                                ON (ent_user_company_role.COMPANY_ID = companyAdmin.COMPANY_ID)
                                                    AND companyAdmin.STATUS_USER = 'admin'
                                     LEFT JOIN ent_user_company_role as userCompany
                                                ON (companyAdmin.COMPANY_ID = userCompany.COMPANY_ID)
                                                    AND userCompany.USER_ID = $worker_id
                                                    AND userCompany.STATUS_USER = 'user'
                                     LEFT JOIN ent_user_contr_agent_role as contrAgentAdmin
                                                ON (companyAdmin.USER_ID = contrAgentAdmin.USER_ID)
                                                    AND contrAgentAdmin.STATUS_USER = 'admin'
                                     LEFT JOIN ent_user_contr_agent_role  as userAgent
                                               ON (contrAgentAdmin.CONTR_AGENT_ID = userAgent.CONTR_AGENT_ID)
                                                   AND userAgent.USER_ID = $worker_id
                                                   AND userAgent.STATUS_USER = 'user'
                                     LEFT JOIN ent_company as infoCompany
                                                ON (infoCompany.COMPANY_ID = companyAdmin.COMPANY_ID)
                                                AND infoCompany.ARCHIVED = 0
                                     LEFT JOIN ent_contr_agents as infoContragent
                                               ON (infoContragent.CONTR_AGENT_ID = contrAgentAdmin.CONTR_AGENT_ID)
                            WHERE ent_user_company_role.`USER_ID` = $user_id
                            AND infoContragent.ARCHIVED = 0;";
            $resultSelect = $DB->Query($CheckTable);

            while ($result = $resultSelect->Fetch()) {
                if (!empty($result['CONTR_AGENT_ID'])) {
                    $arUsers['ADMIN_DATA']['CONTR_AGENT'][$result['CONTR_AGENT_ID']] = [
                        'CONTR_AGENT_ID' => $result['CONTR_AGENT_ID'],
                        'NAME_CONT' => $result['NAME_CONT'],
                        'INN' => $result['INN']
                    ];
                }
                $arUsers['ADMIN_DATA']['COMPANY'][$result['COMPANY_ID']] = [
                    'COMPANY_ID' => $result['COMPANY_ID'],
                    'NAME_COMP' => $result['NAME_COMP'],
                    'PHONE_COMPANY' => $result['PHONE_COMPANY']
                ];
                if (!empty($result['userContrAgentID'])) {
                    $arUsers['WORKER_DATA']['userContrAgentID'][$result['userContrAgentID']] = [
                        'CONTR_AGENT_ID' => $result['userContrAgentID'],
                    ];
                }
                if (!empty($result['userCompanyID'])) {
                    $arUsers['WORKER_DATA']['COMPANY'][$result['userCompanyID']] = [
                        'COMPANY_ID' => $result['userCompanyID']];
                }

            }

            $db_sales = CSaleOrder::GetList(array(), array('USER_ID' => $worker_id));
            while ($ar_sales = $db_sales->Fetch()) {
                $arUsers['WORKER_DATA']['ORDERS'][] = [
                    'ORDER_ID' => $ar_sales['ID']
                ];
            }
        }
        return $arUsers;
    }


    /**
     * Archived company or contragents - for admin
     * TODO сделать проверку на пользователя, админ или нет
     * @param $user_id
     * @param $name_id
     * @param $id
     * @param $archived
     * @param $method
     * @return bool
     */
    public static function Archived($user_id, $name_id, $id, $archived, $method): bool
    {
        global $DB;
        $result = false;
        if (!empty($user_id)) {
            $date = date(DATE_ATOM);
            if (!empty($method)) {
                $CheckTable = "UPDATE  $method SET ARCHIVED = $archived, DATE_EDIT = '$date'
                              WHERE  $name_id = $id";
                $DB->Query($CheckTable);
                $result = true;
            } else {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Edit and create controls worker access
     * @param $worker_id
     * @param array $contr_agent_id
     * @param array $company_id
     * @param array $company_id_true
     * @param array $contr_id_true
     * @return string
     */
    public static function editWorkerControls($worker_id, array $contr_agent_id, array $company_id, array $company_id_true, array $contr_id_true): string
    {
        global $DB;

        if (!empty($worker_id)) {
            $company_ids = implode("','", $company_id);

            if ($company_ids) {
                $sql = "DELETE FROM  ent_user_company_role 
                        WHERE COMPANY_ID IN('$company_ids') 
                        AND USER_ID = $worker_id";
                $DB->Query($sql);
                if (!empty($company_id_true)) {
                    foreach ($company_id_true as $id) {
                        $sql_insert = "INSERT INTO  ent_user_company_role(`COMPANY_ID`,`STATUS_USER`,`USER_ID`) 
                                       VALUES ($id,'user',$worker_id)";
                        $DB->Query($sql_insert);
                    }
                }
            }
            $contr_agent_ids = implode("','", $contr_agent_id);
            if ($contr_agent_ids) {
                $sql = "DELETE FROM  ent_user_contr_agent_role 
                        WHERE CONTR_AGENT_ID IN('$contr_agent_ids') 
                        AND USER_ID = $worker_id";
                $DB->Query($sql);
                if (!empty($contr_id_true)) {
                    foreach ($contr_id_true as $id_contr) {
                        $sql_insert = "INSERT INTO ent_user_contr_agent_role (`CONTR_AGENT_ID`,`STATUS_USER`,`USER_ID`)
                                       VALUES ($id_contr,'user',$worker_id)";
                        $DB->Query($sql_insert);
                    }
                }
            }

            $messageForOperation = 'success';
        } else {
            $messageForOperation = 'Некорректно введены данные для распределеняи прав сотруднику!';
        }
        return $messageForOperation;
    }
}
