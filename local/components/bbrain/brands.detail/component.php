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
\Bitrix\Main\Loader::includeModule("catalog");
use Bitrix\Highloadblock as HL; 	
		if( !$arParams['IBLOCK_ID'] )
		{
			return;
		}

	if( $arParams['CODE'] != '' )
	{
			$hlbl = $arParams['IBLOCK_ID']; // Указываем ID нашего highloadblock блока
			$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 
			$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
			$EHL = $entity->getDataClass(); 


			$QUERY['select'] = array('*');
			$QUERY['order'] =  array("UF_NAME" => "ASC");
			$QUERY['filter'] =  array('UF_CODE' => $arParams['CODE']);
			
			$obData = $EHL::getList($QUERY); 	
			while( $arData = $obData->Fetch())
			{
				
				$arResult = $arData;
			}

		
	}
	
		if( !$arResult['ID'] )
		{	
			Iblock\Component\Tools::process404(
				trim($arParams["MESSAGE_404"]) ?: 'Страница не найдена'
				,true
				,"Y"
				,"Y"
				,$arParams["FILE_404"]
			);			
		}	
	$this->IncludeComponentTemplate();

