<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
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
				if ((int)$arItem["PARAM2"] == IBLOCK_CATALOG_OFFERS) {
					$arResult["ELEMENTS"]['OFFERS'][$arItem["ITEM_ID"]] = ['ID' => $arItem["ITEM_ID"]];
				}
			}
		}
	}
}

if ((!empty($arResult["ELEMENTS"]['PRODUCT']) || !empty($arResult["ELEMENTS"]['OFFERS']))
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
		"ACTIVE",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"CATALOG_QUANTITY",
		"QUANTITY",
		"PRODUCT_ID",
		"PROPERTY_CML2_LINK",
		"PROPERTY_USE_DISCOUNT"
	);

	$arSelectOffers = array(
		"ID",
		"IBLOCK_ID",
		"NAME",
		"ACTIVE",
		"PREVIEW_PICTURE",
		"CATALOG_QUANTITY",
		"QUANTITY",
		"PRODUCT_ID",
		"PROPERTY_CML2_LINK",
		"PROPERTY_GRAMMOVKA_G",
		"PROPERTY_VKUS",
		"PROPERTY_SHTUK_V_UPAKOVKE",
		"PROPERTY_KOLICHESTVO_ZATYAZHEK",
		"PROPERTY_TSVET",
		"ACTIVE",
		"PROPERTY_USE_DISCOUNT"
	);

	$arFilter = $arFilterOffer = array(
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
		"MIN_PERMISSION" => "R",
	);

	foreach ($arResult["PRICES"] as $value) {
		$arSelect[] = $value["SELECT"];
		$arSelectOffers[] = $value["SELECT"];
		$arFilter["CATALOG_SHOP_QUANTITY_" . $value["ID"]] = 1;
		$arFilterOffer["CATALOG_SHOP_QUANTITY_" . $value["ID"]] = 1;
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

//			get offers all
			$offersIds = CCatalogSKU::getOffersList(
				$arProduct["ID"],
				0,
				["ACTIVE"=>"Y"],
				['ID'],
				[]
			);

			foreach ($offersIds[$arProduct["ID"]] as $key => $item){
				if(!isset($arResult["ELEMENTS"]['OFFERS'][$key])){
					$arResult["ELEMENTS"]['OFFERS'][$key] = $item;
				}
			}
		}
	}
//	OFFERS
	if (!empty($arResult["ELEMENTS"]['OFFERS'])) {
		$propTaste = [];
		$arFilterOffer['IBLOCK_ID'] = IBLOCK_CATALOG_OFFERS;
		$arFilterOffer["=ID"] = (array)array_keys($arResult["ELEMENTS"]['OFFERS']);
		$rsElementsOffers = CIBlockElement::GetList(array(), $arFilterOffer, false, false, $arSelectOffers);
		$properties = CIBlockProperty::GetPropertyEnum('VKUS',[], [
			"IBLOCK_ID" => $arFilterOffer['IBLOCK_ID'],]);
		while ($prop_fields = $properties->Fetch()) {
			$propTaste[$prop_fields['ID']]['name'] = $prop_fields["VALUE"];
			$propTaste[$prop_fields['ID']]['color'] = '#' . explode('#', $prop_fields["XML_ID"])[1];
		}
		while ($arElement = $rsElementsOffers->Fetch()) {
			if ($arElement["ACTIVE"] === "Y") {

				$arElement["PRICES"] = CIBlockPriceTools::GetItemPrices(
					$arElement["IBLOCK_ID"],
					$arResult["PRICES"],
					$arElement,
					$arParams['PRICE_VAT_INCLUDE'],
					$arConvertParams);

				// Get image
				$arElement["PICTURE"] = CFile::ResizeImageGet(
					$arElement["PREVIEW_PICTURE"],
					array("width" => $PREVIEW_WIDTH, "height" => $PREVIEW_HEIGHT),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true)['src'];

				// Get prop active for offer TODO offer props static
				if (!empty($arElement['PROPERTY_GRAMMOVKA_G_VALUE'])) {
					$arElement['PROPERTIES']['CODE'] = 'GRAMMOVKA_G';
					$arElement['PROPERTIES']['VALUE'] = $arElement['PROPERTY_GRAMMOVKA_G_VALUE'].' гр.';
				} else if(!empty($arElement['PROPERTY_SHTUK_V_UPAKOVKE_VALUE'])) {
					$arElement['PROPERTIES']['CODE'] = 'SHTUK_V_UPAKOVKE';
					$arElement['PROPERTIES']['VALUE'] = $arElement['PROPERTY_SHTUK_V_UPAKOVKE_VALUE'].'шт.';
				} else if(!empty($arElement['PROPERTY_KOLICHESTVO_ZATYAZHEK_VALUE'])) {
					$arElement['PROPERTIES']['CODE'] = 'KOLICHESTVO_ZATYAZHEK';
					$arElement['PROPERTIES']['VALUE'] = $arElement['PROPERTY_KOLICHESTVO_ZATYAZHEK_VALUE'];
				} else if(!empty($arElement['PROPERTY_TSVET_VALUE'])) {
					$arElement['PROPERTIES']['CODE'] = 'TSVET';
					$arElement['PROPERTIES']['VALUE'] = $arElement["PICTURE"];
				} else if(!empty($arElement['PROPERTY_VKUS_VALUE'])) {
					$arElement['PROPERTIES']['CODE'] = 'VKUS';
					foreach ((array)$arElement['PROPERTY_VKUS_VALUE'] as $id => $tasteName) {
						$arElement['PROPERTIES']['VALUE'][$id] = [
							'color' => $propTaste[$id]['color'],
							'name' => $propTaste[$id]['name']
						];
					}
				}


				$arResult["ELEMENTS"]['PRODUCT'][$arElement['PROPERTY_CML2_LINK_VALUE']]['OFFERS'][$arElement["ID"]] = $arElement;
				unset($arResult["ELEMENTS"]['OFFERS'][$arElement["ID"]]);
			}
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