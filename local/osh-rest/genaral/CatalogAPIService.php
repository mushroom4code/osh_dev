<?php

use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Rest\RestException;

class CatalogAPIService extends IRestService
{
    const SCOPE = 'catalog_rest';

    public static function OnRestServiceBuildDescription(): array
    {
        return array(
            static::SCOPE => array(
                static::SCOPE . '.actual_catalog' => array(__CLASS__, 'getActualCatalog'),
                static::SCOPE . '.products_without_photo' => array(__CLASS__, 'getProductsWithoutPhoto'),
                'options' => array()
            )
        );
    }

    public static function getActualCatalog($query, $n, CRestServer $server): array
    {
        if ($query['error']) {
            throw new RestException(
                'Message',
                'ERROR_CODE',
                CRestServer::STATUS_PAYMENT_REQUIRED
            );
        }

        $res_ar = self::getProductsAndQuantityForSubsidiary();
        return array('catalog' => $res_ar, 'count' => count($res_ar), 'response' => 'ok');
    }

    public static function getProductsWithoutPhoto($query, $n, CRestServer $server): array
    {
        if ($query['error']) {
            throw new RestException(
                'Message',
                'ERROR_CODE',
                CRestServer::STATUS_PAYMENT_REQUIRED
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

    public static function getProductsAndQuantityForSubsidiary(): array
    {
        $arSelect = array(
            'ID',
            'STORE_EXTERNAL_ID' => 'STORE.XML_ID',
            'EXTERNAL_ID' => 'PRODUCT.IBLOCK_ELEMENT.XML_ID',
            'QUANTITY'
        );
        $arFilter = array(
            'STORE.ACTIVE' => 'Y',
            'STORE.SITE_ID' => SUBSIDIARY_SITE_LIST,
            'PRODUCT.IBLOCK_ELEMENT.IBLOCK_ID' => IBLOCK_CATALOG,
            'PRODUCT.IBLOCK_ELEMENT.ACTIVE' => 'Y');

        $res = StoreProductTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect,
            'runtime' => array(
                new ExpressionField('QUANTITY', 'SUM(AMOUNT)')
            )
        ));

        $res_ar = [];
        while ($arStoreProduct = $res->fetch()) {
            $res_ar[] = [
                'ID' => $arStoreProduct['ID'],
                'STORE_EXTERNAL_ID' => $arStoreProduct['STORE_EXTERNAL_ID'],
                'EXTERNAL_ID' => $arStoreProduct['EXTERNAL_ID'],
                'QUANTITY' => (float) $arStoreProduct['QUANTITY'],
            ];
        }

        return $res_ar;
    }

}