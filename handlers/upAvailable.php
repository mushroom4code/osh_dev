<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('sale');
CModule::IncludeModule('iblock');
$arTranslitParams = array("replace_space"=>"-","replace_other"=>"-");
$ibp = new CIBlockProperty;
$el = new CIBlockElement;

	
		
		//наличие товаров
		$resReadyProducts = CIBlockElement::GetList(
			array(),
			array(
				"IBLOCK_ID" => IBLOCK_CATALOG,
				"ACTIVE" => 'Y',
			//	'ID' => 66703

			),
			false,
			false,
			array(
				"ID",
				"CATALOG_AVAILABLE",
				"CATALOG_PRICE_2",
				"CATALOG_PRICE_8",
				"PROPERTY_DISKONT",
				"NAME"
			)
		);	 
		$ik = 0;
	//	echo '<pre>';
		while ($arFields = $resReadyProducts->Fetch())
		{
			//print_r($arFields);
			if( $arFields['CATALOG_AVAILABLE'] == 'Y')
				CIBlockElement::SetPropertyValueCode($arFields["ID"], "USE_AVAILABLE", '250365');
			else
				CIBlockElement::SetPropertyValueCode($arFields["ID"], "USE_AVAILABLE", '0');
		$arOptimal = [];
		$arOptimal[] = array(
			'ID'=>$arFields['CATALOG_PRICE_ID_2'],
			'CATALOG_GROUP_ID'=>2,
			'CURRENCY'=> 'RUB',
			'PRICE' => $arFields['CATALOG_PRICE_2'],			
		);
		if( intval($arFields['CATALOG_PRICE_8']) > 0 && $arFields['PROPERTY_DISKONT_VALUE'] == 'Да')
		$arOptimal[] = array(
			'ID'=>$arFields['CATALOG_PRICE_ID_8'],
			'CATALOG_GROUP_ID'=>8,
			'CURRENCY'=> 'RUB',
			'PRICE' => $arFields['CATALOG_PRICE_8'],			
		);	
print_r($arOptimal);		
			$arPrice = CCatalogProduct::GetOptimalPrice($arFields["ID"], 1, array(2), "N", $arOptimal, "s1");
	print_r($arPrice);


			CIBlockElement::SetPropertyValueCode($arFields["ID"], "MINIMUM_PRICE", $arPrice["DISCOUNT_PRICE"]);	
		}		
		
?>