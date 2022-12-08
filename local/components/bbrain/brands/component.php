<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule("highloadblock");
use Bitrix\Highloadblock as HL; 

		if( !$arParams['IBLOCK_ID'] )
		{
			return;
		}

		
		$CurPage = $APPLICATION->GetCurPage();
		$arPage = [];
		$arPageTemp = explode("/", $CurPage);
		foreach( $arPageTemp as $key_p => $page )
		{
			if( $page != "" )
				$arPage[] = $page;
		}
		//Есть внутренние
		if( $CurPage != $arParams['SEF_URL'] )
		{
			$componentPage = 'detail';
			$arParams['CODE'] = end($arPage);
		}
		else
		{
			
			$componentPage = 'section';
		}
	$this->IncludeComponentTemplate($componentPage);

