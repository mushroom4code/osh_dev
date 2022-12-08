<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$module_id ='skyweb24.loyaltyprogram';
use \Bitrix\Main\Application,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Request,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Internals;
	Loc::loadMessages(__FILE__);

$module_id ='skyweb24.loyaltyprogram';

\Bitrix\Main\Loader::includeModule($module_id);
$listTransact=new \Skyweb24\Loyaltyprogram\Transact;
$listTransact->initTableList();
$APPLICATION->SetTitle(Loc::getMessage("skyweb24.loyaltyprogram_ADMIN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/".$module_id."/lib/informer.php");
Skyweb24\Informer::createInfo();

$listTransact->initFilter();
$listTransact->getTableList();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>