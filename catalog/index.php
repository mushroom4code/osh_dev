<?php

define("HIDE_SIDEBAR", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Enterego\EnteregoHelper;
/**
 * @var  CAllMain|CMain $APPLICATION
 */

// сайт выставка Enterego
$iblock_id = IBLOCK_CATALOG;
$priceCodes = BXConstants::PriceCode();

if (SITE_ID === SITE_EXHIBITION) {
    $iblock_id = IBLOCK_CATALOG_EX;
    $priceCodes = [0 => "b2b"];
}

if (defined('IS_MERCH_PROPERTY')) {
    $GLOBALS['ArFilter'] = array(
        '!=PROPERTY_'.IS_MERCH_PROPERTY.'_VALUE' => 'Да'
    );
}

$parameters = EnteregoHelper::getDefaultCatalogParameters();
$parameters['FILTER_NAME'] = 'ArFilter';
$parameters['SEF_FOLDER'] = "/catalog/";
$parameters['ELEMENT_SORT_FIELD'] = "name";
$parameters['ELEMENT_SORT_ORDER'] = "asc";
$parameters['ELEMENT_SORT_FIELD2'] = "name";
$parameters['ELEMENT_SORT_ORDER2'] = "desc";
$parameters['LIST_OFFERS_LIMIT'] = "5";

$APPLICATION->IncludeComponent(
	"bitrix:catalog", 
	"oshisha_catalog.catalog",
    $parameters,
	false
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
