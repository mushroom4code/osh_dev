<?php

use Bitrix\Sale\Exchange\EnteregoCompanyExchange;
use Bitrix\Sale\Exchange\EnteregoContragentExchange;
use Enterego\EnteregoCompany;
use Bitrix\Main\Mail\Event;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult = [];

if (!empty($_POST['company_array'])) {
    $arFields = [];
    $company_array = json_decode($_POST['company_array']);
    $user_id = $company_array->user_id;
    if (!empty($user_id)) {

        $initCompanyObject = new EnteregoCompanyExchange();
        $initCompanyObject->loadCompanyByName((string)$company_array->CompanyName, $company_array->CompanyTelephone);
        if ($initCompanyObject->COMPANY_ID === 0) {
            $initCompanyObject->ARCHIVED = 0;
            $initCompanyObject->DATE_EDIT = date(DATE_ATOM);
            $initCompanyObject->NAME_COMP = $company_array->CompanyName;
            $initCompanyObject->ADDRESS = $company_array->CompanyAddress;
            $initCompanyObject->PHONE_COMPANY = $company_array->CompanyTelephone;
            $initCompanyObject->TIMES = $company_array->CompanyTime;
            $initCompanyObject->STATUS_USER = 'admin';
            $initCompanyObject->saveCompanyDB();
            if ($initCompanyObject->COMPANY_ID !== 0) {
                $initCompanyObject->setRole($user_id);
                $result = $arFields;
            } else {
                $result = ['error' => 'Не удалось создать компанию'];
            }
        } else {
            $result = ['error' => 'Компания с таким именем уже существует'];
        }
        $arResult = $result;
    }
}
if (!empty($_POST['CreateContragent'])) {
    $contr_agent_array = json_decode($_POST['CreateContragent']);
    $workers = json_decode($contr_agent_array->workers);
    $company = json_decode($contr_agent_array->company);
    $result = [];
    if (!empty($contr_agent_array->user_id)) {
        $contrObject = new EnteregoContragentExchange();
        $contrObject->loadContragentINN($contr_agent_array->INN);
        if ($contrObject->CONTR_AGENT_ID === 0) {
            $contrObject->CONTR_AGENT_ACTIVE = 0;
            $contrObject->DATE_EDIT = (string)date(DATE_ATOM);
            $contrObject->NAME_CONT = $contr_agent_array->NAME_CONT;
            $contrObject->ADDRESS = $contr_agent_array->UrAddress;
            $contrObject->ARCHIVED = 0;
            $contrObject->STATUS_PERSON = $contr_agent_array->statusPerson;
            $contrObject->INN = $contr_agent_array->INN;
            $contrObject->XML_ID = '0';
            $contrObject->saveContragentDB();
            $contrObject->CreateContragents($contr_agent_array->user_id, $workers, $company);
            $result = [
                'CONTR_AGENT_ID' => $this->CONTR_AGENT_ID,
                'INN' => $this->INN,
                'UR_ADDRESS', $this->ADDRESS,
                'USER_ID' => $contr_agent_array->user_id,
                'STATUS' => $this->STATUS_PERSON,
                'NAME_CONT' => $this->NAME_CONT,
                'ARCHIVED' => $this->ARCHIVED,
                'CONTR_AGENT_ACTIVE' => $this->CONTR_AGENT_ACTIVE
            ];
        } else {
            $result = ['error' => 'Контрагент с таким ИНН уже существует!'];
        }
    }
    $arResult = $result;
}
if (!empty($_POST['archived'])) {

    $contr_agent_array_archived = json_decode($_POST['archived']);
    $user_id = $contr_agent_array_archived->USER_ID;
    $archived = $contr_agent_array_archived->ARCHIVED;
    $id = $contr_agent_array_archived->CONTR_AGENT_ID;
    $method = $contr_agent_array_archived->method;
    $name_id = '';

    if ($method === 'ent_contr_agents') {
        $name_id = 'CONTR_AGENT_ID';
    } else if ($method === 'ent_company') {
        $name_id = 'COMPANY_ID';
    }

    if (!empty($user_id)) {
        $result = EnteregoCompany::Archived($user_id, $name_id, $id, $archived, $method);
        $arResult = $result;
    }
}

if (!empty($_POST['edit'])) {

    $name_id = '';
    $Params = [];

    $arParams = json_decode($_POST['edit']);
    $user_id = $arParams->USER_ID;
    $id = $arParams->ID;
    $method = $arParams->method;
    $arData = $arParams->arParams;

    if ($method === 'ent_contr_agents') {
        $name_id = 'CONTR_AGENT_ID';
        $Params = [
            'NAME' => $arData->NAME_CONT,
            'ADDRESS' => $arData->UrAddress,
            'INN' => $arData->INN,
            'STATUS_USER' => $arData->statusPerson,
            'company' => json_decode($arData->company),
            'workers' => json_decode($arData->workers)
        ];

    } else if ($method === 'ent_company') {
        $name_id = 'COMPANY_ID';
        $Params = [
            'NAME' => $arData->NAME,
            'ADDRESS' => $arData->UrAddress,
            'TIMES' => $arData->TIMES,
            'PHONE' => $arData->PHONE,
        ];
    }

    if (!empty($user_id)) {
        $contrObject = new EnteregoContragentExchange();
        $result = $contrObject->Edit($user_id, $name_id, $id, $method, $Params);
        $arResult = $result;
    } else {
        $arResult = 'USER_ID пуст';
    }
}

if (!empty($_POST['createWorker'])) {
    $createWorker = json_decode($_POST['createWorker']);

    $user_id = $createWorker->user_id;
    $login = $createWorker->LOGIN;
    $type = $createWorker->TYPE;
    $value = $createWorker->VALUE;
    $phone = $createWorker->PHONE;

    if ($type === "EMAIL") {
        \CEvent::send(
            "BLOG_POST_BROADCAST",
            SITE_ID,
            array(
                "EMAIL_TO" => $value,
                "AUTHOR" => 'Author',
                "MESSAGE_TITLE" => 'Title Message',
                "MESSAGE_TEXT" => 'Text Message',
                "MESSAGE_PATH" => $_SERVER['REQUEST_URI']
            )
        );
    }

    $dbUsers = CUser::GetList($sort_by, $sort_ord, array('LOGIN' => htmlspecialchars($value)));
    $dbUsersPhone = CUser::GetList($sort_by, $sort_ord, array('PERSONAL_PHONE' => $phone));

    if ($arUserPhone = $dbUsersPhone->Fetch()){
        if (!empty($arUserPhone['ID'])){
            echo 'Пользователь с таким телефоном уже существет';
            return;
        }
    }

    if ($arUser = $dbUsers->Fetch()) {
        if (!empty($arUser['ID'])) {
            echo 'Пользователь с такой почтой уже существет';
            return;
        }
    } else {
        $user = new CUser;
        $arFields = array(
            "LOGIN" => $value,
            "XML_ID" => $arUser['ID'],
            "NAME" => $login,
            "$type" => $value,
            "LID" => "s1",
            "ACTIVE" => "Y",
            "PASSWORD" => '$6$rG21KnAV1IDghn2w$eaK5trSORSOS3tUEbVtOg8U8FIyS1QhkyOYHlDOKoDwXI2Ytu4GsuTi//41ai/fHDatyvHiOdU0fWMwOoxxa67/',
            "CONFIRM_PASSWORD" => '$6$rG21KnAV1IDghn2w$eaK5trSORSOS3tUEbVtOg8U8FIyS1QhkyOYHlDOKoDwXI2Ytu4GsuTi//41ai/fHDatyvHiOdU0fWMwOoxxa67/',
            "GROUP_ID" => array(6),
            "PERSONAL_PHONE" => $phone,
        );

        $new_user_id = $user->Add($arFields);
        $newUserForMe = EnteregoCompany::CreateWorkers($user_id, $new_user_id);
        $arResult = $new_user_id;
    }

}
if (!empty($_POST['editWorkerControls'])) {

    $worker_array_controls = json_decode($_POST['editWorkerControls']);
    $worker_id = $worker_array_controls->USER_ID;
    $contr_agent_id = json_decode($worker_array_controls->CONTR_AGENT_ID);
    $company_id = json_decode($worker_array_controls->COMPANY_ID);
    $company_id_true = json_decode($worker_array_controls->COMPANY_ID_TRUE);
    $contr_id_true = json_decode($worker_array_controls->CONTR_AGENT_TRUE);

    if (!empty($worker_id)) {
        $result = EnteregoCompany::editWorkerControls($worker_id, $contr_agent_id, $company_id, $company_id_true, $contr_id_true);
        $arResult = $result;
    }
}


header('Content-Type: application/json');
echo json_encode($arResult);
