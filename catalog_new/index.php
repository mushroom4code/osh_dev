<?php

define("HIDE_SIDEBAR", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Enterego\EnteregoHelper;

global $APPLICATION;
$APPLICATION->SetPageProperty("title", "Новинки");
$APPLICATION->SetTitle("Новинки");
/**
 * @var  CAllMain|CMain $APPLICATION
 */

if (SITE_ID !== SITE_EXHIBITION) {
    $GLOBALS['ArFilter'] = array(
        'PROPERTY_NEW_VALUE' => 'Да',
    );

    if (defined('IS_MERCH_PROPERTY')) {
        $GLOBALS['ArFilter'] = array_merge($GLOBALS['ArFilter'], array(
            '!=PROPERTY_'.IS_MERCH_PROPERTY => 'Да'
        ));
    }

    $GLOBALS['PREFILTER_NAME'] = array(
        'PROPERTY_NEW_VALUE' => 'Да'
    );

    $parameters = EnteregoHelper::getDefaultCatalogParameters();
    $parameters['FILTER_NAME'] = 'ArFilter';
    $parameters['PREFILTER_NAME'] = 'ArrPreFilter';
    $parameters['SEF_FOLDER'] = '/catalog_new/';
    $parameters['ELEMENT_SORT_FIELD'] = 'DATE_CREATE';
    $parameters['ELEMENT_SORT_ORDER'] = 'desc';
    $parameters['ELEMENT_SORT_FIELD2'] = 'name';
    $parameters['ELEMENT_SORT_ORDER2'] = 'asc';
    $parameters['LIST_OFFERS_LIMIT'] = '0';

    $APPLICATION->IncludeComponent(
        "bitrix:catalog",
        "oshisha_catalog.catalog",
        $parameters,
        false
    );
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
