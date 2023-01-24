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

        $arSelect = array('ID', 'EXTERNAL_ID', 'QUANTITY');
        $arFilter = array("IBLOCK_ID" => 12, 'ACTIVE' => 'Y');
        $res_ar = [];

        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        while ($ob = $res->GetNextElement(true, false)) {
            $res_ar[] = $ob->GetFields();
        }


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
        $arFilter = array("IBLOCK_ID" => 12, 'ACTIVE' => 'Y');
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

}