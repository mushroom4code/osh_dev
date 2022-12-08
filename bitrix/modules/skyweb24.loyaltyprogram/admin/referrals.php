<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main\Application,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Request,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Internals;
	Loc::loadMessages(__FILE__);

$module_id ='skyweb24.loyaltyprogram';

\Bitrix\Main\Loader::includeModule($module_id);
$listReferrals=new \Skyweb24\Loyaltyprogram\Referrals;

$context = Application::getInstance()->getContext();
$request = $context->getRequest();
if(!empty($request['ajax']) && $request['ajax']=='y'){
    if(!empty($request['userId']) && (int) $request['userId']>0 && empty($request['modifier'])){
        $exist=\Skyweb24\Loyaltyprogram\Tools::existUSer($request['userId']);
        echo \Bitrix\Main\Web\Json::encode(['existStatus'=>$exist]);
    }elseif(!empty($request['userId']) && (int) $request['userId']>0 && !empty($request['modifier']) && $request['modifier']=='notreferral'){
        $islistReferrals=$listReferrals->userIsReferral($request['userId']);
        $islistReferrals=($islistReferrals==true)?'referral':'notreferral';
        echo \Bitrix\Main\Web\Json::encode(['existStatus'=>$islistReferrals]);
    }elseif(!empty($request['setRef']) && $request['setRef']=='y'){
		$parentRef=!empty($request['refParent'])?$request['refParent']:0;
        $setRef=$listReferrals->setReferral2(
            $parentRef,
            $request['ref']
        );
        echo \Bitrix\Main\Web\Json::encode(['url'=>$setRef]);
    }
    die();
}

$listReferrals->initTableList();
$APPLICATION->SetTitle(Loc::getMessage("skyweb24.loyaltyprogram_ADMIN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
Asset::getInstance()->addJs('/bitrix/js/'.$module_id.'/script.js');

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/".$module_id."/lib/informer.php");
Skyweb24\Informer::createInfo();

$listReferrals->initFilter();
$listReferrals->setReferralForm();
if(!empty($_GET['mode']) && $_GET['mode']=='tree'){
    $ul=$listReferrals->getTreeList();
	CJSCore::Init(["jquery", "popup"]);
	$APPLICATION->AddHeadScript('/bitrix/js/skyweb24.loyaltyprogram/d3.v3.min.js');
	$APPLICATION->AddHeadScript('/bitrix/js/skyweb24.loyaltyprogram/refferal_tree.js');
	echo $ul;
	?>
	<p>
		<a href="<?echo $APPLICATION->GetCurPageParam("", ["mode"]);?>" class="adm-btn" title="<?=Loc::getMessage("skyweb24.loyaltyprogram_REF_TABLE_MODE")?>"><?=Loc::getMessage("skyweb24.loyaltyprogram_REF_TABLE_MODE")?></a>
	</p>
	<div id="tree-container"></div>
	<script>
	BX.ready(function(){
		setReferraltree(treeRefNet);
	})
	</script>
	<?
}else{
    $listReferrals->getTableList();
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>