<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$PREVIEW_WIDTH = intval($arParams["PREVIEW_WIDTH"]);
if ($PREVIEW_WIDTH <= 0)
    $PREVIEW_WIDTH = 75;

$PREVIEW_HEIGHT = intval($arParams["PREVIEW_HEIGHT"]);
if ($PREVIEW_HEIGHT <= 0)
    $PREVIEW_HEIGHT = 75;

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

$arCatalogs = false;

$arResult["ELEMENTS"] = array();
$arResult["SEARCH"] = array();
foreach($arResult["CATEGORIES"] as $category_id => $arCategory)
{
    foreach($arCategory["ITEMS"] as $i => $arItem)
    {
        if(isset($arItem["ITEM_ID"]))
        {
            $arResult["SEARCH"][] = &$arResult["CATEGORIES"][$category_id]["ITEMS"][$i];
            if (
                $arItem["MODULE_ID"] == "iblock"
                && substr($arItem["ITEM_ID"], 0, 1) !== "S"
            )
            {
                if ($arCatalogs === false)
                {
                    $arCatalogs = array();
                    if (CModule::IncludeModule("catalog"))
                    {
                        $rsCatalog = CCatalog::GetList(array(
                            "sort" => "asc",
                        ));
                        while ($ar = $rsCatalog->Fetch())
                        {
                            if ($ar["PRODUCT_IBLOCK_ID"])
                                $arCatalogs[$ar["PRODUCT_IBLOCK_ID"]] = 1;
                            else
                                $arCatalogs[$ar["IBLOCK_ID"]] = 1;
                        }
                    }
                }

                if (array_key_exists($arItem["PARAM2"], $arCatalogs) || (int)$arItem["PARAM2"] == IBLOCK_CATALOG_OFFERS) {
                    $arResult["ELEMENTS"][$arItem["ITEM_ID"]] = $arItem["ITEM_ID"];
                }
            }
        }
    }
}

if (!empty($arResult["ELEMENTS"]) && CModule::IncludeModule("iblock"))
{
    $arConvertParams = array();
    if ('Y' == $arParams['CONVERT_CURRENCY'])
    {
        if (!CModule::IncludeModule('currency'))
        {
            $arParams['CONVERT_CURRENCY'] = 'N';
            $arParams['CURRENCY_ID'] = '';
        }
        else
        {
            $arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
            if (!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo)))
            {
                $arParams['CONVERT_CURRENCY'] = 'N';
                $arParams['CURRENCY_ID'] = '';
            }
            else
            {
                $arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
                $arConvertParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
            }
        }
    }

    $obParser = new CTextParser;

    if (is_array($arParams["PRICE_CODE"]))
        $arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices(0, $arParams["PRICE_CODE"]);
    else
        $arResult["PRICES"] = array();

    $arSelect = array(
        "ID",
        "IBLOCK_ID",
        "PREVIEW_TEXT",
        "PREVIEW_PICTURE",
        "DETAIL_PICTURE",
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

    $arFilter = array(
        "IBLOCK_LID" => SITE_ID,
        "IBLOCK_ACTIVE" => "Y",
        "ACTIVE_DATE" => "Y",
        "ACTIVE" => "Y",
        "CHECK_PERMISSIONS" => "Y",
        "MIN_PERMISSION" => "R",
    );

    foreach($arResult["PRICES"] as $value)
    {
        $arSelect[] = $value["SELECT"];
        $arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = 1;
    }

    $arFilter["=ID"] = $arResult["ELEMENTS"];
    $rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
    while($arElement = $rsElements->Fetch())
    {
	    if($arElement["ACTIVE"] === "Y") {
		    $arElement["PRICES"] = CIBlockPriceTools::GetItemPrices($arElement["IBLOCK_ID"], $arResult["PRICES"],
			    $arElement, $arParams['PRICE_VAT_INCLUDE'], $arConvertParams);
		    if ($arParams["PREVIEW_TRUNCATE_LEN"] > 0) {
			    $arElement["PREVIEW_TEXT"] = $obParser->html_cut($arElement["PREVIEW_TEXT"],
				    $arParams["PREVIEW_TRUNCATE_LEN"]);
		    }

			if(!empty($arElement['PROPERTY_CML2_LINK_VALUE'])){
				$arResult["ELEMENTS"][$arElement['PROPERTY_CML2_LINK_VALUE']]['OFFERS'][$arElement["ID"]] = $arElement;
			} else {
				$arResult["ELEMENTS"][$arElement["ID"]] = $arElement;
			}
	    }
    }
}

foreach($arResult["SEARCH"] as $i=>$arItem)
{
    switch($arItem["MODULE_ID"])
    {
        case "iblock":
            if(array_key_exists($arItem["ITEM_ID"], $arResult["ELEMENTS"]))
            {
                $arElement = &$arResult["ELEMENTS"][$arItem["ITEM_ID"]];

                if ($arParams["SHOW_PREVIEW"] == "Y")
                {
                    if ($arElement["PREVIEW_PICTURE"] > 0)
                        $arElement["PICTURE"] = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"], array("width"=>$PREVIEW_WIDTH, "height"=>$PREVIEW_HEIGHT), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                    elseif ($arElement["DETAIL_PICTURE"] > 0)
                        $arElement["PICTURE"] = CFile::ResizeImageGet($arElement["DETAIL_PICTURE"], array("width"=>$PREVIEW_WIDTH, "height"=>$PREVIEW_HEIGHT), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                }
            }
            break;
    }

    $arResult["SEARCH"][$i]["ICON"] = true;
}

foreach ($arResult['ELEMENTS'] as $product_id => $product) {
	$db_props = CIBlockElement::GetProperty($product['IBLOCK_ID'], $product['ID'], array("sort" => "asc"), Array("CODE"=>"USE_DISCOUNT"));
	if($ar_props = $db_props->Fetch()) {
		$arResult['ELEMENTS'][$product_id]['USE_DISCOUNT'] = $ar_props['VALUE_ENUM'];
	}
}

$arBasketItems = array();
$dbBasketItems = CSaleBasket::GetList(
	array("NAME" => "ASC", "ID" => "ASC"),
	array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL"),
	false,
	false,
	array("ID", "PRODUCT_ID", "QUANTITY",)
);

while ($arItems = $dbBasketItems->Fetch()) {
	$arBasketItems[$arItems["PRODUCT_ID"]] = $arItems["QUANTITY"];
}

foreach ($arBasketItems as $key => $val) {
	if ($key == $arResult['ELEMENTS'][$key]['ID']) {
		$arResult['ELEMENTS'][$key]['BASKET_QUANTITY'] = $val;
	}
}

foreach($arResult["ELEMENTS"] as $searchItem) {
	$uniqueId = $this->GetEditAreaId($searchItem["ID"]);
	$arResult['ELEMENTS'][$searchItem['ID']]['BUY_LINK'] = $uniqueId.'_buy_link';
	$arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_DOWN_ID'] = $uniqueId.'_quant_down';
	$arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_UP_ID'] = $uniqueId.'_quant_up';
	$arResult['ELEMENTS'][$searchItem['ID']]['QUANTITY_ID'] = $uniqueId.'_quantity';
	$arResult['ELEMENTS'][$searchItem['ID']]['PRICE_ID'] = $uniqueId.'_price';
	if (empty($searchItem['BASKET_QUANTITY'])) {
		$arResult['ELEMENTS'][$searchItem['ID']]['BASKET_QUANTITY'] = 0;
	}
}

$dbStatistic = CSearchStatistic::GetList(
	array("TIMESTAMP_X"=>'DESC'),
	array("STAT_SESS_ID" => $_SESSION['SESS_SESSION_ID']),
	array('TIMESTAMP_X', 'PHRASE')
);
$dbStatistic->NavStart(3);
$arResult['popularSearches'] = [];
$component = $this->getComponent();
while( $arStatistic = $dbStatistic->Fetch()){
	$arResult['popularSearches'][] = $arStatistic;
}

?>