<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Enterego\EnteregoHelper;
global $APPLICATION;
$APPLICATION->SetPageProperty("title", "Дисконт");
$APPLICATION->SetTitle("Дисконт");
$APPLICATION->AddChainItem('Дисконт','/diskont/');

?>
    <div id="o_nas" class="mb-5">


<?php

define("HIDE_SIDEBAR", true);

/**
 * @var  CAllMain|CMain $APPLICATION
 */

if (SITE_ID !== SITE_EXHIBITION) {
    $GLOBALS['ArFilter'] = array(
        'PROPERTY_USE_DISCOUNT_VALUE' => 'Да',
    );

    if (defined('IS_MERCH_PROPERTY')) {
        $GLOBALS['ArFilter'] = array_merge($GLOBALS['ArFilter'], array(
            '!=PROPERTY_'.IS_MERCH_PROPERTY.'_VALUE' => 'Да'
        ));
    }

    $GLOBALS['ArPreFilter'] = array(
        'PROPERTY_USE_DISCOUNT_VALUE' => 'Да',
    );

    $parameters = EnteregoHelper::getDefaultCatalogParameters();
    $parameters['FILTER_NAME'] = 'ArFilter';
    $parameters['PREFILTER_NAME'] = 'ArrPreFilter';
    $parameters['SEF_FOLDER'] = '/diskont/';
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
?>
	</div>
<style>
.section_wrapper{
justify-content:flex-start;
}
</style>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
