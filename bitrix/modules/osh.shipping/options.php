<?php
use Bitrix\Main\Context,
    Bitrix\Main\Loader,
    Osh\Delivery\Options\Config;

IncludeModuleLangFile(__FILE__);

$moduleId = "osh.shipping";

Loader::includeModule($moduleId);

$OSH_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if( ! ($OSH_RIGHT >= "R"))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$SOptions = new Config();
$request = Context::getCurrent()->getRequest();
if($request->isPost()){
    $SOptions->saveSettings();
}else{
    $SOptions->drawSettingsForm();
}