<?/*************************************************
	 * View - Osh shipments table (using widget) *
	 *************************************************/

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\Shipment;
use Bitrix\Sale;

$MODULE_ID = 'osh.shipping';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/order.php');

$saleModulePermissions = $APPLICATION->GetGroupRight($MODULE_ID);
if($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));


$APPLICATION->SetTitle(GetMessage('OSH_COLLECTOR'));


require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");
/*
CModule::IncludeModule("osh.shipping");
$config = (array)COsh::getConfig();
$culture = (int)COsh::GetCultureId();
*/

/*
$APPLICATION->AddHeadString('<script type="text/javascript" charset="utf-8" src="'.$config['jsUrl'].'"></script>');
$APPLICATION->AddHeadString('<script type="text/javascript">Osh.init({apikey: \''.$config['adminApiKey'].'\',cultureId: '.$culture.'});</script>');
$APPLICATION->AddHeadString('<meta http-equiv="X-UA-Compatible" content="IE=9" />');
$APPLICATION->AddHeadString('<link rel="stylesheet" type="text/css" href="'.$config['cssUrl'].'" />');
$APPLICATION->AddHeadString('<link rel="stylesheet" type="text/css" href="/bitrix/js/osh.shipping/css/Osh.css" />');
*/
?>

<script type="text/javascript">
/*
    Osh.init({apikey: '<?=$config['adminApiKey'];?>', cultureId: <?=$culture?>});
    Osh.call_registry.ready = function() {
        Osh.get_shipments('#Osh-sdk-list', 1);
    }
*/
</script>
<div id="Osh-sdk-list"></div>

<? require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php"); ?>