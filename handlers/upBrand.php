<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader; 
use \Bitrix\Main\Application,
    \Bitrix\Main\Web\Uri,
    \Bitrix\Main\Web\HttpClient;

Loader::includeModule("iblock"); 
Loader::includeModule("highloadblock"); 
use Bitrix\Highloadblock as HL; 
$hlbl = 6; // Указываем ID нашего highloadblock блока
$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 
$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$EHL = $entity->getDataClass(); 

$arTranslitParamsCode = array("replace_space"=>"-","replace_other"=>"-");
 
 
 
		$rsSections = CIBlockSection::GetList(array('DEPTH_LEVEL'=>'DESC'), array('IBLOCK_ID'=>IBLOCK_CATALOG, 'ACTIVE'=>'Y', 'GLOBAL_ACTIVE'=>'Y'), false, array('DEPTH_LEVEL','ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME'));

		while($arSection = $rsSections->GetNext()) {
	

			$arSections[$arSection['ID']] = $arSection;
			if( $arSection['DEPTH_LEVEL'] == 1 )
			{
				$arRootCat[$arSection['ID']] = $arSection['ID'];
			}
			if( $arSection['DEPTH_LEVEL'] == 2 )
			{
				$arRootCat[$arSection['ID']] = $arSection['IBLOCK_SECTION_ID'];
			}
		}


		$resProd = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>IBLOCK_CATALOG, 'ACTIVE'=>'Y'), false,false, array('ID', 'IBLOCK_SECTION_ID'));
		while( $row = $resProd->Fetch())
		{
			$countCat[$row['ID']] = $arRootCat[$row['IBLOCK_SECTION_ID']];
		}
 

 
 
 
	$rsElement = $DB->Query("SELECT IBLOCK_ELEMENT_ID, PROPERTY_2134 FROM  b_iblock_element_prop_s20");
	while( $rowEL = $rsElement->Fetch() )
	{
		$arBrand[$rowEL['PROPERTY_2134']]++; 
		
		$arBrandSection[$rowEL['PROPERTY_2134']][$countCat[$rowEL['IBLOCK_ELEMENT_ID']]] =  $arSections[$countCat[$rowEL['IBLOCK_ELEMENT_ID']]]['NAME'];
	}
	/*foreach( $arBrand as $id_brand => $_brand )
	{
		CIBlockElement::SetPropertyValues($id_brand, 25, $_brand, 'ELEMENTS');
	}*/



//print_r($arBrandSection); exit();
				$QUERY['select'] = array('ID', 'UF_NAME', 'UF_XML_ID');
				$QUERY['order'] =  array("ID" => "DESC");
				$QUERY['filter'] =  array();
				
				$obData = $EHL::getList($QUERY); 	
				while( $arData = $obData->Fetch())
				{
					$arFields = array(
					'UF_CODE' => Cutil::translit( trim($arData['UF_NAME']),"ru", $arTranslitParamsCode),
					'UF_SECTIONS' => implode("#", $arBrandSection[$arData['UF_XML_ID']])
					);
					if( !isset($arBrand[$arData['UF_XML_ID']]) )
					{
						 $arFields['UF_ACTIVE'] = 0;
						 $EHL::update($arData['ID'], $arFields);
					}
					else
					{
						$arFields['UF_ACTIVE'] = 1;
						 $EHL::update($arData['ID'], $arFields);
					}		
				}

?>