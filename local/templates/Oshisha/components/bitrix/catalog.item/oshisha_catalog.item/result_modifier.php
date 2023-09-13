<?php
use Enterego\EnteregoBasket;
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

$useDiscount = ($item['PROPERTIES']['USE_DISCOUNT']['VALUE'] ?? 'Нет') === 'Да' ;
$item['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($item['ITEM_ALL_PRICES'][0],
    $useDiscount, $item['ID']);

$measureRatio = \Bitrix\Catalog\MeasureRatioTable::getList(array(
    'select' => array('RATIO'),
    'filter' => array('=PRODUCT_ID' => $item['ID'])
))->fetch()['RATIO'];

$item['MEASURE_RATIO'] = $item['ITEM_MEASURE_RATIOS'][$item['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];

EnteregoHelper::setProductsActiveUnit($item);