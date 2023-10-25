<?php

define("HIDE_SIDEBAR", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Enterego\EnteregoHelper;
global $APPLICATION;
$APPLICATION->SetPageProperty("title", "Хиты");
$APPLICATION->SetTitle("Хиты");
/**
 * @var  CAllMain|CMain $APPLICATION
 */
$nowDate = date("Y-m-d H:i:s");
$date = new DateTime($nowDate);

if (SITE_ID !== SITE_EXHIBITION) {
    $GLOBALS['ArrFilter'] = array(
        'PROPERTY_HIT_VALUE' => 'да',
    );

    if (defined('IS_MERCH_PROPERTY')) {
        $GLOBALS['ArrFilter'] = array_merge($GLOBALS['ArrFilter'], array(
            '!=PROPERTY_'.IS_MERCH_PROPERTY.'_VALUE' => 'Да'
        ));
    }

    $GLOBALS['ArrPreFilter'] = array(
        'PROPERTY_HIT_VALUE' => 'да'
    );

    $parameters = EnteregoHelper::getDefaultCatalogParameters();
    $parameters['FILTER_NAME'] = 'ArrFilter';
    $parameters['PREFILTER_NAME'] = 'ArrPreFilter';
    $parameters['SEF_FOLDER'] = '/hit/';
    $parameters['ELEMENT_SORT_FIELD'] = 'name';
    $parameters['ELEMENT_SORT_ORDER'] = 'asc';
    $parameters['ELEMENT_SORT_FIELD2'] = 'name';
    $parameters['ELEMENT_SORT_ORDER2'] = 'desc';
    $parameters['LIST_OFFERS_LIMIT'] = '0';

    $APPLICATION->IncludeComponent(
        "bitrix:catalog",
        "oshisha_catalog.catalog",
        $parameters,
        false
    );
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
