<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

?>
<?$APPLICATION->IncludeComponent("bitrix:breadcrumb","oshisha_breadcrumb",Array(
        "START_FROM" => "0", 
        "PATH" => "", 
        "SITE_ID" => "s1" 
    )
);?>
		  
<?$APPLICATION->IncludeComponent(
	"bbrain:brands",
	"",
	Array(
		"ID" => "BRANDS",
		"IBLOCK_ID" => 6,
		'SEF_URL' => '/brands/',
		'NAME' => 'Бренды',
		'COUNT' => 20

	)
);?>
<style>
.section_wrapper{
justify-content:flex-start;
}
.bx-breadcrumb{
	margin-top:-10px;
	margin-bottom:30px;
}
</style>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
