<?php

use Bitrix\Iblock\EO_Element;
use Bitrix\Main;
function deleteProductOnNewOffers() {
	CModule::IncludeModule('iblock') || die();
	CModule::IncludeModule('sale') || die();
	$date  = date('Y-m-d H:i',strtotime('-1 day'));
	$productUpdate = СIBlockElement::GetList([],['>=TIMESTAMP_X'=> $date]);
	while ($product = $productUpdate->Fetch()){

	}
}
