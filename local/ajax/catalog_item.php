<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Fuser;
use Enterego\EnteregoBasket;
use Enterego\EnteregoGroupedProducts;

CModule::IncludeModule("iblock");
Loader::includeModule('main');
CModule::IncludeModule("sale");

$request = Context::getCurrent()->getRequest();
$jsonList = $request->getJsonList();
$action = $jsonList->get('action');

/**
 * @param $prodId
 * @param $listGroupedProduct
 * @param $arItems
 * @return array
 * @throws ArgumentException
 * @throws ObjectPropertyException
 * @throws SystemException
 */
function getGroupedProduct($prodId, $listGroupedProduct, $arItems)
{
    $prices = [];
    $arItems['GROUPED_PRODUCTS'] = $arItems['GROUPED_PROPS_DATA'] = $arResult = [];
    if (!empty($prodId)) {
        $arResult = EnteregoGroupedProducts::getListGroupedProduct($prodId, $listGroupedProduct, $arItems,false);
        $arResult['SETTING'] = EnteregoGroupedProducts::getDataPropOffers();
        $arResult['PRICE_GREAT'] = BASIC_PRICE;
        $arResult['SALE'] = USE_CUSTOM_SALE_PRICE;

        $rsPrice = PriceTable::getList([
            'select' => ['PRODUCT_ID', 'PRICE', 'CATALOG_GROUP_ID', 'CATALOG_GROUP'],
            'filter' => [
                'PRODUCT_ID' => $listGroupedProduct,
                'CATALOG_GROUP_ID' => [SALE_PRICE_TYPE_ID, B2B_PRICE],
            ],
        ])->fetchAll();

        foreach ($rsPrice as $price) {
            $prices[$price['PRODUCT_ID']]['PRICES'][$price['CATALOG_GROUP_ID']] = $price;
        }

        foreach ($prices as $productId => $product) {
            if (isset($arResult['GROUPED_PRODUCTS'][$productId])) {
                try {
                    $useDiscount = $arResult['GROUPED_PRODUCTS'][$productId]['PROPERTIES']['USE_DISCOUNT']['VALUE_XML_ID'] ?? false;
                    $arResult['GROUPED_PRODUCTS'][$productId]['PRODUCT']['PRICE'] =
                        EnteregoBasket::getPricesArForProductTemplate($product, $useDiscount, $productId)['PRICE_DATA']['PRICE'];
                } catch (SqlQueryException|LoaderException $e) {
                    $arResult['GROUPED_PRODUCTS'][$productId]['PRODUCT']['PRICE'] = [];
                }
            }
        }

        $dbBasketItems = \CSaleBasket::GetList(
            array(),
            array("FUSER_ID" => Fuser::getId(), "LID" => SITE_ID, "ORDER_ID" => "NULL"),
            false,
            false,
            array("ID", "PRODUCT_ID", "QUANTITY",)
        );
        while ($arItems = $dbBasketItems->Fetch()) {
            if (isset($arResult['GROUPED_PRODUCTS'][$arItems["PRODUCT_ID"]])) {
                $arResult['GROUPED_PRODUCTS'][$arItems["PRODUCT_ID"]]['PRODUCT']['ACTUAL_BASKET'] = $arItems["QUANTITY"];
            }
        }
    }
    return $arResult;
}

$jsonForModal = [];
if ($action === 'fastProduct') {
    $morePhoto = [];
    $prodId = $jsonList->get('prodId');
    $groupedProduct = $jsonList->get('groupedProduct');

    $specialPrice = 0;
    $prop_see_in_window = [];
    $item = CIBlockElement::GetList([], ['ID' => $prodId], false, false,
        ['ID', 'PROPERTY_MORE_PHOTO','PROPERTY_CML2_PICTURES', 'DETAIL_PAGE_URL', 'NAME', 'DETAIL_PICTURE',
            'PROPERTY_LINEYKA',
            'CATALOG_QUANTITY', 'QUANTITY', 'CATALOG_PRICE_' . B2B_PRICE, IS_DISCOUNT_VALUE, 'PREVIEW_TEXT',
            'CATALOG_PRICE_' . SALE_PRICE_TYPE_ID, 'PROPERTY_ADVANTAGES_PRODUCT'])->GetNext();

    $photo = CIBlockElement::GetProperty(IBLOCK_CATALOG, $prodId,[],['CODE'=>'MORE_PHOTO']);
    while ($morePhotoS = $photo->GetNext()) {
        $morePhoto[] = $morePhotoS;
    }

    $item['PRODUCT'] = [
        'CATALOG_QUANTITY' => $item['CATALOG_QUANTITY'],
        'QUANTITY' => $item['QUANTITY'],
        'PRICE' => $item['CATALOG_PRICE_' . B2B_PRICE],
        'SALE_PRICE' => round($item['CATALOG_PRICE_' . SALE_PRICE_TYPE_ID]),
        'SALE_BOOL' => $item['PROPERTY_USE_DISCOUNT_VALUE_VALUE'] === 'Да',
    ];

    if (!empty($price['USER_PRICE'])) {
        $specialPrice = $price['USER_PRICE']['PRICE'];
    }

//    if (!empty($price['SALE_PRICE']['PRICE']) &&
//        ($useDiscount['VALUE_XML_ID'] ?? false == 'true' || USE_CUSTOM_SALE_PRICE)) {
//
//        $specialPrice = ($specialPrice === 0 || $price['SALE_PRICE']['PRICE'] < $specialPrice)
//            ? $price['SALE_PRICE']['PRICE']
//            : $specialPrice;
//    }

    if (empty($morePhoto[0])) {
        $morePhoto[0]['SRC'] = '/local/templates/Oshisha/images/no-photo.gif';
    }

    try {
        $item['GROUPED_PRODUCT'] = getGroupedProduct($prodId, json_decode($groupedProduct), $item);
    } catch (ObjectPropertyException $e) {
    } catch (ArgumentException $e) {
    } catch (SystemException $e) {
    }
// TODO - допилить получение переменных - все-таки подумать брать ли некоторые из шиблона
// TODO - или вырезать кусок из получения данных по связ товарам
    $jsonForModal = [
        'ID' => $prodId,
        'TYPE_PRODUCT' => 'PRODUCT',
        'DETAIL_PAGE_URL' => $item['DETAIL_PAGE_URL'],
        'MORE_PHOTO' => $morePhoto,
        'PRODUCT' => $item['PRODUCT'],
        'PREVIEW_PICTURE' => CFile::GetPath(($item['PREVIEW_PICTURE'] ?? $item['DETAIL_PICTURE']))
            ?? '/local/templates/Oshisha/images/no-photo.gif',
//        'USE_DISCOUNT' => $useDiscount['VALUE'],
//        'ACTUAL_BASKET' => $priceBasket,
        'SALE_PRICE' => round($specialPrice),
        'NAME' => $item['NAME'],
        'LIKE' => [
            'ID_PROD' => $item['ID_PROD'],
            'F_USER_ID' => $item['F_USER_ID'],
            'COUNT_LIKE' => $item['COUNT_LIKE'] ?? 0,
            'COUNT_LIKES' => $item['COUNT_LIKES'] ?? 0,
            'COUNT_FAV' => $item['COUNT_FAV'] ?? 0,
        ],
        'USE_CUSTOM_SALE_PRICE' => USE_CUSTOM_SALE_PRICE,
        'DESCRIPTION' => html_entity_decode($item['PREVIEW_TEXT']) ?? 'Линейка - '.$item['PROPERTY_LINEYKA_VALUE'],
        'GROUPED_PRODUCT' =>
            [
                'GROUPED_PROPS_DATA' => $item['GROUPED_PRODUCT']['GROUPED_PROPS_DATA'],
                'GROUPED_PRODUCTS' => $item['GROUPED_PRODUCT']['GROUPED_PRODUCTS'],
                'SETTING' => $item['GROUPED_PRODUCT']['SETTING']
            ]
    ];
    echo json_encode($jsonForModal);
} else {
    echo json_encode(['errors' => 'yes']);
}
exit();