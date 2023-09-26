<?php

global $USER;

use Bitrix\Main\Application;
use Enterego\contagents\EnteregoContragents;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult = [];

$request = Application::getInstance()->getContext()->getRequest()->getJsonList();

if ($request->get('ACTION') === 'create') {
    $type = $request->get('TYPE');
    $phone = $request->get('PHONE_COMPANY');
    $name = $request->get('NAME');
    $arData = $result = [];
    $user_id = $USER->GetID();
    $curData = [
        'PHONE_COMPANY' => $phone,
        'NAME_ORGANIZATION' => $name
    ];

    if ($type === EnteregoContragents::typeUric || $type === EnteregoContragents::typeIp) {
        $arData = ['INN' => $request->get('INN')];
    } else {
        $arData = ['EMAIL' => $request->get('EMAIL')];
    }

    if (!empty($user_id) && !empty($phone) && !empty($name)) {

        $resQueryAllParams = EnteregoContragents::getContragentByFilter($curData);

        $resQueryCurrent = EnteregoContragents::getContragentByFilter($arData);
        $resQueryPHONE = EnteregoContragents::getContragentByFilter(['PHONE_COMPANY' => $phone]);

        if ($resQueryAllParams && $resQueryPHONE && $resQueryCurrent) {
            $result = EnteregoContragents::addContragent(
                $user_id,
                array_merge($curData,$arData)
            );
        } else {
            $result = ['error' => 'Контрагент с такими данными уже существует!'];
        }
    }
    $arResult = $result;
}

if ($request->get('ACTION') === 'getList') {
    $user_id = $USER->GetID();
    $error = ['error' => 'У вас нет контрагентов на этом сайте'];
    if (!empty($user_id)) {
        $res = EnteregoContragents::getContragentsByUserId($user_id);
        $arResult = !empty($res) ? $res : $error;
    } else {
        $arResult = $error;
    }
}

header('Content-Type: application/json');
echo json_encode($arResult);
