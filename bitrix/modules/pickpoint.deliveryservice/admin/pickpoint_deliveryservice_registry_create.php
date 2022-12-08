<?php
use \PickPoint\DeliveryService\Admin\RegistryForm;
use \PickPoint\DeliveryService\Admin\RegistryOrdersGrid;
use \PickPoint\DeliveryService\Pickpoint\Statuses;
use \PickPoint\DeliveryService\Option;

use \Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "pickpoint.deliveryservice");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

// Define Getting Type
$type = (isset($_REQUEST['type'])) ? intval($_REQUEST['type']) : 0;
$allGettingTypes = Option::getVariants('getting_type');
if (!array_key_exists($type, $allGettingTypes)) {
	reset($allGettingTypes);
	$type = key($allGettingTypes);
}
// --

$APPLICATION->SetTitle(Loc::getMessage('PICKPOINT_DELIVERYSERVICE_ADMIN_REGISTRY_CREATE_TITLE_TYPE_'.$type));
$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

if (!CheckVersion(SM_VERSION, '17.0.0')) {
	$gridVersionLock = new CAdminMessage([
		'MESSAGE' => GetMessage("PICKPOINT_DELIVERYSERVICE_ADMIN_GRID_MIN_VERSION"),
		'TYPE' => 'ERROR', 
		'DETAILS' => GetMessage("PICKPOINT_DELIVERYSERVICE_ADMIN_GRID_MIN_VERSION_TEXT"),
		'HTML' => true
		]);
	echo $gridVersionLock->Show();
} else {
	// Registry create form
	RegistryForm::makeFormWindow($type);

	// Registry orders buttons, filter and grid
	$RegistryOrdersGrid = new RegistryOrdersGrid();

	// Filter preset
	$RegistryOrdersGrid->setDefaultFilterValues(['GETTING_TYPE' => $type, '=STATUS' => Statuses::getRegistryStates(), '=REGISTRY_ID' => 0]);

	$buttons = $RegistryOrdersGrid->getButtons();
	if (!empty($buttons)) {
		$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '.default', [
			'ALIGN'   => 'left',
			'BUTTONS' => $buttons,
		]);
	}

	$columns = $RegistryOrdersGrid->getFilterColumns();
	if (!empty($columns)) {
		$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '.default', [
			'GRID_ID'             => $RegistryOrdersGrid->getId(),
			'FILTER_ID'           => $RegistryOrdersGrid->getFilterId(),
			'FILTER'              => $columns,
			'ENABLE_LIVE_SEARCH'  => false,
			'ENABLE_LABEL'        => true,
			'DISABLE_SEARCH'      => false, // Quick search in FIND field
			// Undocumented ?
			'VALUE_REQUIRED_MODE' => false,
			'VALUE_REQUIRED'      => false,
		]);
	}

	$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '.default', [
		'GRID_ID'                   => $RegistryOrdersGrid->getId(),	
		'COLUMNS'                   => $RegistryOrdersGrid->getColumns(),
		'ROWS'                      => $RegistryOrdersGrid->getRows(),
		'NAV_OBJECT'                => $RegistryOrdersGrid->getPagination(),
		'AJAX_ID'                   => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		'AJAX_MODE'                 => 'Y',		
		'AJAX_OPTION_HISTORY'       => false,
		'AJAX_OPTION_JUMP'          => 'N',	
		'PAGE_SIZES'                => [
			['VALUE' => '10',   'NAME' => '10'], 
			['VALUE' => '20',   'NAME' => '20'], 
			['VALUE' => '50',   'NAME' => '50'], 
			['VALUE' => '100',  'NAME' => '100'], 
			['VALUE' => '200',  'NAME' => '200'],
			['VALUE' => '500',  'NAME' => '500'],
		],	
		'SHOW_ROW_CHECKBOXES'       => true,
		'SHOW_CHECK_ALL_CHECKBOXES' => true,			
		'SHOW_ROW_ACTIONS_MENU'     => false,
		'SHOW_GRID_SETTINGS_MENU'   => true,
		'SHOW_NAVIGATION_PANEL'     => true,
		'SHOW_PAGINATION'           => true,
		'SHOW_SELECTED_COUNTER'     => true,
		'SHOW_TOTAL_COUNTER'        => true,
		'SHOW_PAGESIZE'             => true,
		'SHOW_ACTION_PANEL'         => true,		
		'ALLOW_SORT'                => true,
		'ALLOW_COLUMNS_SORT'        => true,
		'ALLOW_COLUMNS_RESIZE'      => true,
		'ALLOW_HORIZONTAL_SCROLL'   => true,
		'ALLOW_PIN_HEADER'          => true,
		'TOTAL_ROWS_COUNT'          => $RegistryOrdersGrid->getPagination()->getRecordCount(),
		
		// Undocumented params
		'EDITABLE'                  => true,		
		
		// Group actions
		'ACTION_PANEL'              => [
			'GROUPS' => [ 
				'TYPE' => [ 
					'ITEMS' => $RegistryOrdersGrid->getControls(),
				]
			]
		],
	]);
}
	
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");