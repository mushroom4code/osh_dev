<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$module_id ='skyweb24.loyaltyprogram';
use \Bitrix\Main\Application,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Localization\Loc;
	
Loc::loadMessages(__FILE__);



$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$module_id ='skyweb24.loyaltyprogram';

\Bitrix\Main\Loader::includeModule($module_id);

global $APPLICATION;
$rights=$APPLICATION->GetGroupRight($module_id);
if($rights<'E'){
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

function langCorrection($str){
	return LANG_CHARSET=='windows-1251'?iconv("UTF-8", LANG_CHARSET, $str):$str;
}

$APPLICATION->SetTitle(Loc::getMessage("skyweb24.loyaltyprogram_DOC_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/".$module_id."/lib/informer.php");
Skyweb24\Informer::createInfo();

$xml = @simplexml_load_file('https://skyweb24.ru/marketplace/documentation.xml');
if($xml==false){
	if($ch = curl_init()){
		$options = [
			CURLOPT_URL            => 'https://skyweb24.ru/marketplace/documentation.xml',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => ['User-Agent' => 'Mozilla/5.0 (Windows NT 5.1; rv:34.0) Gecko/20100101 Firefox/34.0']
		];
		curl_setopt_array($ch, $options);
		$file = curl_exec($ch);
		$xml=@simplexml_load_string($file);
	}
}
?>
<script src="https://cdn.bitrix24.ru/b841019/crm/site_button/loader_2_4wly34.js"></script>
<?
if($xml!=false){
	$docClass=new \Skyweb24\Loyaltyprogram\Documentation;
	foreach ($xml->module as $nextModule) {
		if($nextModule->code==$module_id){
			$types=['document'=>[], 'article'=>[], 'video'=>[]];
			$typeTiltles=[
				'document'=>Loc::getMessage("skyweb24.loyaltyprogram_DOC_DOCUMENTATION"),
				'article'=>Loc::getMessage("skyweb24.loyaltyprogram_DOC_ARTICLE"),
				'video'=>Loc::getMessage("skyweb24.loyaltyprogram_DOC_VIDEO"),
				'promo'=>Loc::getMessage("skyweb24.loyaltyprogram_DOC_PROMO")
			];
			foreach ($nextModule->articles->article as $nextArticle) {
				if($nextArticle->active=='Y'){
					$types[(string) $nextArticle->type][]=$nextArticle;
				}
			}
			foreach($types as $keyType=>$nextType){
				if(count($nextType)>0){?>
					<h2><?=$typeTiltles[$keyType]?></h2>
					<section class="developers <?=$keyType?>">
					<?foreach($nextType as $nextDoc){?>
						<article class="<?=$docClass->getEffect();?>">
							<a href="<?=$nextDoc->link?>" target="_blank">
								<?if(!empty($nextDoc->data)){?>
									<time><?=$nextDoc->data?></time>
								<?}?>
								<figure><img src="<?=$docClass->getImgUrl($nextDoc->img)?>"></figure>
								<header><?=$docClass::getLangString($nextDoc->name)?></header>
							</a>
							
						</article>
					<?}?>
					</section>
				<?}
			}
			break;
		}
	 }
}else{
	CAdminMessage::ShowMessage([
		"MESSAGE"=>Loc::getMessage("skyweb24.loyaltyprogram_DOC_NOT_AVAILABLE")
	]); 
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>