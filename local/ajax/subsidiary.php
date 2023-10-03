<?php

use Bitrix\Main\Application;
use Bitrix\Main\Context;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$request = Context::getCurrent()->getRequest();
$session = Application::getInstance()->getSession();

$selectSubsidiary = $request->get('subsidiary');
if (!empty($selectSubsidiary)) {
    $session->set('subsidiary', $selectSubsidiary);
    exit('success');
}





