<?php

global $USER;

use Bitrix\Main\Application;
use Enterego\contagents\EnteregoContragents;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult = [];

$request = Application::getInstance()->getContext()->getRequest()->getJsonList();

if ($request->get('ACTION') === 'create') {
    $inn = $request->get('INN');
    $phone = $request->get('PHONE_COMPANY');
    $name = $request->get('NAME');
    $user_id = $USER->GetID();

    $result = [];
    if (!empty($user_id) && !empty($inn) && !empty($phone) && !empty($name)) {

        $resQuery = EnteregoContragents::getContragentByFilter([
            'INN' => $inn,
            'PHONE_COMPANY' => $phone,
            'NAME_ORGANIZATION' => $name
        ]);

        if ($resQuery) {
            $result = EnteregoContragents::addContragent(
                $user_id,
                [
                    'INN' => $inn,
                    'PHONE_COMPANY' => $phone,
                    'NAME_ORGANIZATION' => $name
                ]
            );
        } else {
            $result = ['error' => 'Контрагент с такими данными уже существует!'];
        }
    }
    $arResult = $result;
}

if ($request->get('ACTION') === 'getList') {
    $user_id = $USER->GetID();
    if (!empty($user_id)) {
        $arResult = EnteregoContragents::getContragentsByUserId($user_id);
    } else {
        $arResult = ['error' => 'У вас нет контрагентов на этом сайте'];
    }
}

header('Content-Type: application/json');
echo json_encode($arResult);
