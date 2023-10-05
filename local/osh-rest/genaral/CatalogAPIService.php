<?php

class CatalogAPIService extends \IRestService
{
    const SCOPE = 'catalog_rest';

    public static function OnRestServiceBuildDescription()
    {
        return array(
            static::SCOPE => array(
                static::SCOPE . '.actual_catalog' => array(__CLASS__, 'getActualCatalog'),
                static::SCOPE . '.products_without_photo' => array(__CLASS__, 'getProductsWithoutPhoto'),
                'options' => array()
            )
        );
    }

    public static function getActualCatalog($query, $n, \CRestServer $server): array
    {
        if ($query['error']) {
            throw new \Bitrix\Rest\RestException(
                'Message',
                'ERROR_CODE',
                \CRestServer::STATUS_PAYMENT_REQUIRED
            );
        }

        $res_ar = self::getProductsAndQuantityForSubsidiary($query['subsidiary'] ?? SITE_ID);
        return array('catalog' => $res_ar, 'count' => count($res_ar), 'response' => 'ok');
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

    public static function getProductsAndQuantityForSubsidiary($siteId)
    {
        $storesRes = \Bitrix\Catalog\StoreTable::getList([
                'filter' => ['SITE_ID' => $siteId],
                'select' => ['ID']
            ]
        );
        $storeArr = [];
        while ($store = $storesRes->fetch()) {
            $storeArr[] = $store['ID'];
        }

        $arSelect = array(
            'PRODUCT_ID',
            'EXTERNAL_ID' => 'PRODUCT.IBLOCK_ELEMENT.XML_ID',
            'QUANTITY'
        );
        $arFilter = array(
            'STORE.ACTIVE' => 'Y',
            'STORE.ID' => $storeArr,
            'PRODUCT.IBLOCK_ELEMENT.IBLOCK_ID' => IBLOCK_CATALOG,
            'PRODUCT.IBLOCK_ELEMENT.ACTIVE' => 'Y');
        $res_ar = [];


        $res = \Bitrix\Catalog\StoreProductTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect,
            'runtime' => array(
                new \Bitrix\Main\Entity\ExpressionField('QUANTITY', 'SUM(AMOUNT)')
            )
        ));

        while ($product = $res->fetch()) {
            $res_ar[] = $product;
        }

        return $res_ar;
    }

}