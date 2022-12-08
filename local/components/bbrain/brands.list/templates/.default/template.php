<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


/*
	if($arResult['CODE'] != '')
		$APPLICATION->AddChainItem($arResult['NAME'], $arResult['CODE']);*/
	/*echo '<pre>';
	print_r($arResult);*/
	//echo count($arResult['ITEMS']);
	//print_r($arResult['NAV']);
	//print_r($arParams);
	$arParamsString = array(
		"ID" => $arParams['ID'],
		"IBLOCK_ID" => $arParams['IBLOCK_ID'],
		'SEF_URL' => $arParams['SEF_URL'],	
		'FIRST_ID' => $arParams['FIRST_ID'],	
	);
	
?>

<div class="mb-5 static" id="box_brands">
    <h1>Бренды</h1>
<?
		$rsSections = CIBlockSection::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>IBLOCK_CATALOG, 'ACTIVE'=>'Y', 'GLOBAL_ACTIVE'=>'Y', 'DEPTH_LEVEL' => 1), false, array('DEPTH_LEVEL','ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME'));

		while($arSection = $rsSections->GetNext()) {
			if( empty($arResult[$arSection['NAME']]) ) continue;
?>
<h5 class="mt-5 mb-3"><?=$arSection['NAME']?></h5>
<div class="box_brands ">
	<div class="box_with_brands_parents">
	<?foreach( $arResult[$arSection['NAME']] as $_brands):
	
		
	?>
			<div class="box_with_brands d-flex justify-content-center align-items-center col col-sm">
				<?if( $_brands['UF_FILE']):?>
				<?$UF_FILE = CFile::ResizeImageGet($_brands['UF_FILE'], array('width'=>150, 'height'=>55), BX_RESIZE_IMAGE_PROPORTIONAL, true)?>
				<a href="/brands/<?=$_brands['UF_CODE']?>/" class="logo_brand">
				<img src="<?=$UF_FILE['src']?>">
				</a>
				<?else:?>
               <a href="/brands/<?=$_brands['UF_CODE']?>/" class="d-flex justify-content-center align-items-center"><?=$_brands['UF_NAME']?></a>
			   <?endif;?>
            </div>
	<?endforeach;?>
	</div>
</div>

<?			
		}

?>	
</div>