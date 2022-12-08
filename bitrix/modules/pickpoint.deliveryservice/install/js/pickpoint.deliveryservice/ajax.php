<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$module_id = "pickpoint.deliveryservice";
CModule::IncludeModule($module_id);

\PickPoint\DeliveryService\SubscribeHandler::getAjaxAction($_REQUEST[PICKPOINT_DELIVERYSERVICE_LBL.'action']);