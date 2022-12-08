<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$userId = intval($arParams["USER_ID"]);


if($userId <= 0){
	return;
}
if(!\Bitrix\Main\Loader::IncludeModule("skyweb24.loyaltyprogram")){
	return;
}

$componentsData=new \Skyweb24\Loyaltyprogram\Components($userId);
$arResult['REF_LINK'] = $componentsData->getRefLink();


$this->IncludeComponentTemplate();
?>