<? use Enterego\EnteregoBasket;
use Enterego\EnteregoHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$item = &$arResult['ITEM'];

if ($item["PREVIEW_PICTURE"]["ID"]) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $item["PREVIEW_PICTURE"]["ID"],
            array(
                'width' => 160,
                'height' => 160
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL
        ),
        CASE_UPPER
    );

} elseif ($item["DETAIL_PICTURE"]["ID"]) {
    $item["PREVIEW_PICTURE"] = array_change_key_case(
        CFile::ResizeImageGet(
            $item["DETAIL_PICTURE"]["ID"],
            array(
                'width' => 160,
                'height' => 160
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL
        ),
        CASE_UPPER
    );
}

/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP start */
$item['GROUPED_PRODUCTS'] = $item['GROUPED_PROPS_DATA'] = [];
$listGroupedProduct = $item['PROPERTIES']['PRODUCTS_LIST_ON_PROP']['VALUE'];
$item = EnteregoHelper::getListGroupedProduct($item['ID'], $listGroupedProduct, $item);
/** Enterego grouped product on prop PRODUCTS_LIST_ON_PROP end */


$useDiscount = ($item['PROPERTIES']['USE_DISCOUNT']['VALUE'] ?? 'Нет') === 'Да' ;
$item['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($item['ITEM_ALL_PRICES'][0],
    $useDiscount, $item['ID']);