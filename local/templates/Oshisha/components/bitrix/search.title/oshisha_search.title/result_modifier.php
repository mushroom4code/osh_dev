<?php use Enterego\EnteregoSettings;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

$PREVIEW_WIDTH = intval($arParams["PREVIEW_WIDTH"]);
if ($PREVIEW_WIDTH <= 0) {
	$PREVIEW_WIDTH = 75;
}

$PREVIEW_HEIGHT = intval($arParams["PREVIEW_HEIGHT"]);

if ($PREVIEW_HEIGHT <= 0) {
	$PREVIEW_HEIGHT = 75;
}

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";
$arCatalogs = false;
$arResult["ELEMENTS"] = $arResult["SEARCH"] = [];

foreach ($arResult["CATEGORIES"] as $category_id => $arCategory) {
	foreach ($arCategory["ITEMS"] as $i => $arItem) {
		if (isset($arItem["ITEM_ID"])) {
			$arResult["SEARCH"][] = &$arResult["CATEGORIES"][$category_id]["ITEMS"][$i];
			if ($arItem["MODULE_ID"] == "iblock" && substr($arItem["ITEM_ID"], 0, 1) !== "S") {
				if ($arCatalogs === false) {
					$arCatalogs = [];
					if (CModule::IncludeModule("catalog")) {
						$rsCatalog = CCatalog::GetList(["sort" => "asc"]);
						while ($ar = $rsCatalog->Fetch()) {
							if ($ar["PRODUCT_IBLOCK_ID"]) {
								$arCatalogs[$ar["PRODUCT_IBLOCK_ID"]] = 1;
							} else {
								$arCatalogs[$ar["IBLOCK_ID"]] = 1;
							}
						}
					}
				}

				if (array_key_exists($arItem["PARAM2"], $arCatalogs)) {
					$arResult["ELEMENTS"]['PRODUCT'][$arItem["ITEM_ID"]] = ['ID' => $arItem["ITEM_ID"]];
				}
			}
		}
	}
}

if ((!empty($arResult["ELEMENTS"]['PRODUCT']))
	&& CModule::IncludeModule("iblock")) {
	$arConvertParams = $arResult["PRICES"] = [];
	if ('Y' == $arParams['CONVERT_CURRENCY']) {
		if (!CModule::IncludeModule('currency')) {
			$arParams['CONVERT_CURRENCY'] = 'N';
			$arParams['CURRENCY_ID'] = '';
		} else {
			$arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
			if (!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo))) {
				$arParams['CONVERT_CURRENCY'] = 'N';
				$arParams['CURRENCY_ID'] = '';
			} else {
				$arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
				$arConvertParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
			}
		}
	}

	$obParser = new CTextParser;
	if (is_array($arParams["PRICE_CODE"])) {
		$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices(0, $arParams["PRICE_CODE"]);
	}

	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"NAME",
		"IBLOCK_SECTION_ID",
		"ACTIVE",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"CATALOG_QUANTITY",
		"PRODUCT_ID",
		"PROPERTY_USE_DISCOUNT",
	);

	$arFilter =  array(
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
		"MIN_PERMISSION" => "R",
	);

	foreach ($arResult["PRICES"] as $value) {
		$arSelect[] = $value["SELECT"];
		$arFilter["CATALOG_SHOP_QUANTITY_" . $value["ID"]] = 1;
	}


	//  PRODUCT PARENT OFFERS && PRODUCT
	$arFilter["=ID"] = (array)array_keys($arResult["ELEMENTS"]['PRODUCT']);
	$arFilter["IBLOCK_ID"] = IBLOCK_CATALOG;
	$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
	while ($arProduct = $rsElements->Fetch()) {
		if ($arProduct["ACTIVE"] === "Y") {
			$arProduct["PRICES"] = CIBlockPriceTools::GetItemPrices(
				$arProduct["IBLOCK_ID"],
				$arResult["PRICES"],
				$arProduct,
				$arParams['PRICE_VAT_INCLUDE'],
				$arConvertParams);

			$arProduct["PICTURE"] = CFile::ResizeImageGet(
				$arProduct["PREVIEW_PICTURE"],
				array("width" => $PREVIEW_WIDTH, "height" => $PREVIEW_HEIGHT),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true)['src'];

			$discount = CIBlockElement::GetProperty($arProduct['IBLOCK_ID'], $arProduct['ID'], array("sort" => "asc"),
				array("CODE" => "USE_DISCOUNT"))->Fetch();

			$arProduct['USE_DISCOUNT'] = $discount['VALUE_ENUM'];

			$arResult["ELEMENTS"]['PRODUCT'][$arProduct["ID"]]['INFO'] = $arProduct;
		}
	}

//  BASKET
	$arBasketItems = array();
	$dbBasketItems = CSaleBasket::GetList(
		array("NAME" => "ASC", "ID" => "ASC"),
		array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL"),
		false,
		false,
		array("ID", "PRODUCT_ID", "QUANTITY",)
	);

	while ($arItems = $dbBasketItems->Fetch()) {
		$arResult['BASKET_ITEMS'][$arItems["PRODUCT_ID"]] = $arItems["QUANTITY"];
	}


	foreach ($arResult["ELEMENTS"]['PRODUCT'] as $searchItem) {
		$uniqueId = $this->GetEditAreaId($searchItem["ID"]);
		$arResult['ELEMENTS']['PRODUCT'][$searchItem['ID']]['INFO']['BUY_LINK'] = $uniqueId . '_buy_link';
		$arResult['ELEMENTS']['PRODUCT'][$searchItem['ID']]['INFO']['QUANTITY_DOWN_ID'] = $uniqueId . '_quant_down';
		$arResult['ELEMENTS']['PRODUCT'][$searchItem['ID']]['INFO']['QUANTITY_UP_ID'] = $uniqueId . '_quant_up';
		$arResult['ELEMENTS']['PRODUCT'][$searchItem['ID']]['INFO']['QUANTITY_ID'] = $uniqueId . '_quantity';
		$arResult['ELEMENTS']['PRODUCT'][$searchItem['ID']]['INFO']['PRICE_ID'] = $uniqueId . '_price';
		if (empty($searchItem['BASKET_QUANTITY'])) {
			$arResult['ELEMENTS']['PRODUCT'][$searchItem['ID']]['INFO']['BASKET_QUANTITY'] = 0;
		}
	}

	$dbStatistic = CSearchStatistic::GetList(
		array("TIMESTAMP_X" => 'DESC'),
		array("STAT_SESS_ID" => $_SESSION['SESS_SESSION_ID']),
		array('TIMESTAMP_X', 'PHRASE')
	);
	$dbStatistic->NavStart(3);
	$arResult['popularSearches'] = [];
	$component = $this->getComponent();
	while ($arStatistic = $dbStatistic->Fetch()) {
		$arResult['popularSearches'][] = $arStatistic;
	}
}

?>