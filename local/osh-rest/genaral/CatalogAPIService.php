<?php

class CatalogAPIService extends \IRestService
{
    const SCOPE = 'catalog_rest';

    public static function OnRestServiceBuildDescription()
    {
        return array(
            static::SCOPE => array(
                static::SCOPE . '.actual_catalog_mc' => array(__CLASS__, 'getActualCatalogMC'),
                static::SCOPE . '.actual_catalog_rz' => array(__CLASS__, 'getActualCatalogRZ'),
                static::SCOPE . '.products_without_photo' => array(__CLASS__, 'getProductsWithoutPhoto'),
                'options' => array()
            )
        );
    }

    public static function getActualCatalogMC($query, $n, \CRestServer $server): array
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
        }

        return array('catalog' => self::getProductsAndQuantityForStore('Москва'), 'count' => count($res_ar), 'response' => 'ok');
    }

    public static function getActualCatalogRZ($query, $n, \CRestServer $server): array
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
        }

        return array('catalog' => self::getProductsAndQuantityForStore('Рязань'), 'count' => count($res_ar), 'response' => 'ok');
    }

    public static function getProductsWithoutPhoto($query, $n, \CRestServer $server): array
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
        }

        $arSelect = array('ID', 'EXTERNAL_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE');
        $arFilter = array("IBLOCK_ID" => IBLOCK_CATALOG, 'ACTIVE' => 'Y');
        $res_ar = [];

        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        while ($ob = $res->GetNextElement(true, false)) {
            $fields = $ob->GetFields();
            if ($fields['PREVIEW_PICTURE'] === null || $fields['DETAIL_PICTURE'] === null) {
                $res_ar[] = $fields;
            }
        }

        return array('catalog' => $res_ar, 'count' => count($res_ar), 'response' => 'ok');
    }

    public static function getProductsAndQuantityForStore(string $storeTitle = 'Москва') {
        $arSelect = array(
            'PRODUCT_ID',
            'EXTERNAL_ID' => 'PRODUCT.IBLOCK_ELEMENT.XML_ID',
            'QUANTITY' => 'AMOUNT'
        );
        $arFilter = array(
            'STORE.ACTIVE'=>'Y',
            'STORE.TITLE' => $storeTitle,
            'PRODUCT.IBLOCK_ELEMENT.IBLOCK_ID' => IBLOCK_CATALOG,
            'PRODUCT.IBLOCK_ELEMENT.ACTIVE'=>'Y');
        $res_ar = [];


        $res = \Bitrix\Catalog\StoreProductTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect,
        ));

        while ($product = $res->fetch()) {
            $res_ar[] = $product;
        }

        return $res_ar;
    }

}