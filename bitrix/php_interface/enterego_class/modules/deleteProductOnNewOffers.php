<?php

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;

/**
 * @return array|void
 * @throws \Bitrix\Main\ArgumentException
 * @throws \Bitrix\Main\DB\SqlQueryException
 * @throws \Bitrix\Main\ObjectPropertyException
 * @throws \Bitrix\Main\SystemException
 *  - 4 param  in ProductTable::getList - it's offer type - product type with offer - 3
 */
function deleteProductOnNewOffers()
{
	$offers = [];
	CModule::IncludeModule('iblock') || die();
	CModule::IncludeModule('sale') || die();

	$db_res = ProductTable::getList(
		[
			'select' => ['ID'],
			'filter' => ['TYPE' => 4]
		],
	);

	while ($product = $db_res->Fetch()) {
		$offerXml = ElementTable::getList([
			'select' => ['XML_ID', 'NAME'],
			'filter' => ['ID' => $product['ID']]
		]);
		$offerData = $offerXml->fetch();
		$product['XML_ID'] = $offerData['XML_ID'];
		$product['NAME'] = $offerData['NAME'];

		$oldXml = strripos($offerData['XML_ID'], '#') !== false
			? explode('#', $offerData['XML_ID'])[1] : $offerData['XML_ID'];
		$offers[$oldXml] = $product;
	}

	if (!empty($offers)) {

		$listProduct = [];
		$idsOffers = array_keys($offers);
		$productXml = ElementTable::getList([
			'select' => ['NAME', 'ID', 'XML_ID'],
			'filter' => ['=XML_ID' => $idsOffers]
		]);

		while ($productData = $productXml->fetch()) {
			$listProduct[$productData['XML_ID']] = $productData;
		}

		getWithUpdateProductsInDB($listProduct, $offers);
	}


	return $offers;
}


/**
 * @throws \Bitrix\Main\DB\SqlQueryException
 */
function getWithUpdateProductsInDB($productData, $offersData): bool
{
	$connection = Application::getConnection();
	foreach ($productData as $xmlId => $product) {
		$id = $offersData[$xmlId]['ID'];
		$oldID = $product['ID'];
		$connection->query("UPDATE b_sale_basket SET PRODUCT_ID =$id WHERE PRODUCT_ID=$oldID");
		$xml = explode('#', $offersData[$xmlId]['XML_ID']);
//		if product not main - xml_id offer !== xml_id product this offer
		if($xml[0] !== $xml[1]){
			CIBlockElement::Delete($oldID);
		}
	}
	return true;
}