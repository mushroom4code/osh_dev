<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/** @global CMain $APPLICATION */

if ($APPLICATION->GetGroupRight('pickpoint.deliveryservice') > 'D') {
    $aMenu = array(
        'parent_menu' => 'global_menu_store',
        'section' => 'pickpoint',
        'sort' => 110,
        'text' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_MAIN_TITLE"),
        'title' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_MAIN_TITLE"),
        'icon' => 'pickpoint_menu_icon',
        'page_icon' => 'pickpoint_page_icon',
		'module_id' => 'pickpoint.deliveryservice',
		'items_id' => 'menu_pickpoint_deliveryservice',
		'items' => array(),		        
    );
	
	$aMenu['items'][] = array(
		'text' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_EXPORT_TITLE"),
		'title' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_EXPORT_TITLE"),
		'module_id' => 'pickpoint.deliveryservice',		
		'url' => 'pickpoint_export.php?lang='.LANGUAGE_ID,
		"more_url" => array("pickpoint_edit.php")
	);
	
	$aMenu['items'][] = array(
		'text' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_COURIER_TITLE"),
		'title' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_COURIER_TITLE"),
		'module_id' => 'pickpoint.deliveryservice',		
		'url' => 'pickpoint_deliveryservice_courier.php?lang='.LANGUAGE_ID,
	);
	
	$aMenu['items'][] = array(
		'text' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_REGISTRY_TITLE"),
		'title' => Loc::getMessage("PICKPOINT_DELIVERYSERVICE_MENU_REGISTRY_TITLE"),
		'module_id' => 'pickpoint.deliveryservice',		
		'url' => 'pickpoint_deliveryservice_registry.php?lang='.LANGUAGE_ID,
		"more_url" => array("pickpoint_deliveryservice_registry_create.php")
	);

    return $aMenu;
}

return false;