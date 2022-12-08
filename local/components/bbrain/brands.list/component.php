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
\Bitrix\Main\Loader::includeModule("sale");
\Bitrix\Main\Loader::includeModule("highloadblock");
\Bitrix\Main\Loader::includeModule("catalog");
use Bitrix\Highloadblock as HL; 

		if( !$arParams['IBLOCK_ID'] )
		{
			return;
		}
	
				$hlbl = $arParams['IBLOCK_ID']; // Указываем ID нашего highloadblock блока
				$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 
				$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
				$EHL = $entity->getDataClass(); 
	
	
				$QUERY['select'] = array('*');
				$QUERY['order'] =  array("UF_NAME" => "ASC");
				$QUERY['filter'] =  array();
				
				$obData = $EHL::getList($QUERY); 	
				while( $arData = $obData->Fetch())
				{
					$arSections = explode("#", $arData['UF_SECTIONS']);
					foreach( $arSections as $_sect)
					{
						$arResult[$_sect][$arData['ID']] = $arData;
					}
					
					
					
				}					
		
	$this->IncludeComponentTemplate();

