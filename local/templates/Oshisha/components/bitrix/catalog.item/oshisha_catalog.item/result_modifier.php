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

$item['PRICES_CUSTOM'] = EnteregoBasket::getPricesArForProductTemplate($item['ITEM_ALL_PRICES'][0],
    false, $item['ID']);