<?
use \Ipol\Fivepost\Bitrix\Tools;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule('ipol.fivepost');

if ($GLOBALS['APPLICATION']->GetGroupRight(IPOL_FIVEPOST) > 'D') // checking rights
{
    // Main menu block
    $aMenu = array(
        'parent_menu' => 'global_menu_store', // IM menu block
        'section' => 'fivepost',
        'sort' => 110,
        'text' => Tools::getMessage('MENU_MAIN_TEXT'),
        'title' => Tools::getMessage('MENU_MAIN_TITLE'),
        'icon' => 'ipol_fivepost_menu_icon', // CSS for icon
        'page_icon' => 'ipol_fivepost_page_icon', // CSS for icon
        'module_id' => IPOL_FIVEPOST,
        'items_id' => IPOL_FIVEPOST_LBL.'menu',
        'items' => array(),
    );

    // Parent pages
    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_ORDERS_TEXT'),
        'title' => Tools::getMessage('MENU_ORDERS_TITLE'),
        'module_id' => IPOL_FIVEPOST,
        'url' => 'ipol_fivepost_orders.php?lang='.LANGUAGE_ID,
        //"more_url" => array("ipol_fivepost_orders_edit.php")  // Use it for admin pages like "Edit order with ID=..." and it will be marked in this menu as "opened"
    );

    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_SYNC_DATA_TEXT'),
        'title' => Tools::getMessage('MENU_SYNC_DATA_TITLE'),
        'module_id' => IPOL_FIVEPOST,
        'url' => 'ipol_fivepost_sync_data.php?lang='.LANGUAGE_ID,
    );

    return $aMenu;
}
return false;