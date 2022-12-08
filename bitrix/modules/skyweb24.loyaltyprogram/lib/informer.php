<?
namespace Skyweb24;
use	Bitrix\Main\Localization\Loc;
class Informer{
	
	/**
	* set CAdminNotify about new version
	*/
	const ALARM_PERIOD=100; //date license expires in N days
	const MODULE_ID='skyweb24.loyaltyprogram'; //module id
	
	public static function getModuleInfo(){
		$moduleId=self::MODULE_ID;
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client_partner.php');
		$arModuleInfo = false;
		$updateModule=false;
		$arUpdateList = \CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, 'Y', [$moduleId], array('fullmoduleinfo' => 'Y'));
		if($arUpdateList && isset($arUpdateList['MODULE'])){
			foreach($arUpdateList['MODULE'] as $arModule){
				if($arModule['@']['ID'] == $moduleId){
					$arModuleInfo = $arModule['@'];
					if(!empty($arModule['#']['VERSION'])){
						$updateModule=$arModule['#']['VERSION'][count($arModule['#']['VERSION'])-1]['@']['ID'];
					}
					break;
				}
			}
		}
		
		$folders = array(
			"/local/modules/".$moduleId,
			"/bitrix/modules/".$moduleId,
		);
		foreach($folders as $folder)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].$folder))
			{
				$handle = opendir($_SERVER["DOCUMENT_ROOT"].$folder);
				if($handle){
					if($info = \CModule::CreateModuleObject($moduleId)){
						$arModuleInfo["MODULE_ID"] = $info->MODULE_ID;
						$arModuleInfo["MODULE_NAME"] = $info->MODULE_NAME;
						$arModuleInfo["MODULE_DESCRIPTION"] = $info->MODULE_DESCRIPTION;
						$arModuleInfo["MODULE_VERSION"] = $info->MODULE_VERSION;
						$arModuleInfo["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
						$arModuleInfo["MODULE_SORT"] = $info->MODULE_SORT;
						$arModuleInfo["MODULE_PARTNER"] = $info->PARTNER_NAME;
						$arModuleInfo["MODULE_PARTNER_URI"] = $info->PARTNER_URI;
						$arModuleInfo["IsInstalled"] = $info->IsInstalled();
						$arModuleInfo["DEMO"] = "N";
						if(defined(str_replace(".", "_", $info->MODULE_ID)."_DEMO")){
							$arModuleInfo["DEMO"] = "Y";
							if($info->IsInstalled())
							{
								if(\CModule::IncludeModuleEx($info->MODULE_ID) != MODULE_DEMO_EXPIRED)
								{
									//$arModuleInfo["DEMO_DATE"] = ConvertTimeStamp($GLOBALS["SiteExpireDate_".str_replace(".", "_", $info->MODULE_ID)], "SHORT");
									$arModuleInfo["DEMO_DATE"] = $GLOBALS["SiteExpireDate_".str_replace(".", "_", $info->MODULE_ID)];
								}
								else
									$arModuleInfo["DEMO_END"] = "Y";
							}
						}
					}
					closedir($handle);
				}
			}
		}

		$arModuleInfo["CURRENT_VERSION"]=$updateModule;
		self::setNotify($arModuleInfo);
		return $arModuleInfo;
	}
	
	public static function setNotify($arModuleInfo){
		if(!empty($arModuleInfo['CURRENT_VERSION'])){
			$info = Loc::getMessage("SKWB24_INFORMER_informer_updates_available").' "'.$arModuleInfo['NAME'].'" <a  href="/bitrix/admin/update_system_partner.php?tabControl_active_tab=tab2&amp;addmodule='.$arModuleInfo["MODULE_ID"].'&amp;lang='.LANGUAGE_ID.'">'.Loc::getMessage("SKWB24_INFORMER_informer_update").'</a>';
			$lastInfo=\Bitrix\Main\Config\Option::get(self::MODULE_ID, 'last_informer', '');
			if($lastInfo!=$info){
				\Bitrix\Main\Config\Option::set(self::MODULE_ID, 'last_informer', $info);
				$tag=str_replace('.','_',$arModuleInfo['ID']).'_new_version';
				\CAdminNotify::Add([
					'MESSAGE' => $info,
					'TAG' => $tag,
					'MODULE_ID' => $arModuleInfo['ID'],
					'ENABLE_CLOSE' => 'Y'
				]);
			}
		}
	}
	
	public static function getStyles(){
		return 
		'<style>
			.skyweb24_informer{
				margin-bottom: 20px;
				padding: 15px;
				border: 2px dashed #2980b9;
				background: rgba(41, 128, 185, 0.1);
				font-size: 15px;
				color: #34495e;
			}
			.skyweb24_informer p{margin:0 0 10px;}
			.skyweb24_informer .error{color:#eb3b5a; font-weight:bolder;}
			.skyweb24_informer .good{color:#20bf6b;}
			.skyweb24_informer .buttonsBlock{
			    display: flex;
			}
			.skyweb24_informer .buttonsBlock a{
			    margin-left: 10px;
				transition: .3s;
			}
			.skyweb24_informer .buttonsBlock a:first-of-type{
			    margin: 0;
			}
			.skyweb24_informer .buttonsBlock a:last-of-type{
			    margin-left: auto;
			}
			
			.skyweb24_informer .buttonsBlock a{
				border-radius: 3px;
				display: inline-block;
				height: 15px;
				padding: 10px 20px;
				position: relative;
				margin-left: 15px;
				text-decoration: none;
				line-height: 16px;
				color: #ffffff;
				font-size: 16px;
				font-weight: bold;
				text-shadow: 0 -1px 0 rgba(106, 109, 111, 0.3);
			}
			
			.skyweb24_informer .buttonsBlock a.documentation{
				border: 1px solid #2980b9 !important;
				background: #2980b9;
			}
				.skyweb24_informer .buttonsBlock a.documentation:hover{
					background: #3498db!important;
				}
			.skyweb24_informer .buttonsBlock a.review{
				border: 1px solid #27ae60 !important;
				background: #27ae60;
			}
				.skyweb24_informer .buttonsBlock a.review:hover{
					background: #1dd1a1
				}
			.skyweb24_informer .buttonsBlock a.question{
				border: 1px solid #c0392b !important;
				background: #c0392b;
			}
				.skyweb24_informer .buttonsBlock a.question:hover{
					background: #ff6b6b
				}
			.skyweb24_informer .buttonsBlock a.payment{
				border: 1px solid #0abde3;
				background: #0abde3;
			}
				.skyweb24_informer .buttonsBlock a.payment:hover{
					background: #48dbfb
				}
			.skyweb24_informer .buttonsBlock a.modules{
				border: 1px solid #2e86de;
				background: #2e86de;
			}
				.skyweb24_informer .buttonsBlock a.modules:hover{
					background: #54a0ff
				}
		</style>'
		;
	}
	
	public static function cacheInfo(){
		$filename = $_SERVER['DOCUMENT_ROOT'].'/upload/'.self::MODULE_ID.'_informer.txt';
		$cTime=0;
		if(file_exists($filename)){
			$cTime=filectime($filename);
		}
		if((time()-$cTime)>86400){
			$arModuleInfo=self::getModuleInfo();
			$strBlock='<section class="skyweb24_informer">';
			//title
			$strBlock.='<p class="title">';
			if($arModuleInfo['DEMO']=='Y'){
				$strBlock.='<span class="error">'.Loc::getMessage("SKWB24_INFORMER_attention").'</span> '.Loc::getMessage("SKWB24_INFORMER_demo_work").' <a href="http://marketplace.1c-bitrix.ru/solutions/'.$arModuleInfo["MODULE_ID"].'/">'.Loc::getMessage("SKWB24_INFORMER_buy").'</a>';
			}elseif(/*!empty($arModuleInfo["DATE_TO"]) && */MakeTimeStamp($arModuleInfo["DATE_TO"])<time()){
				$strBlock.='<span class="error">'.Loc::getMessage("SKWB24_INFORMER_attention").'</span> '.Loc::getMessage("SKWB24_INFORMER_lysens_expired").' '.$arModuleInfo["DATE_TO"].'. <a class="adm-btn adm-btn-save" href="http://marketplace.1c-bitrix.ru/solutions/'.$arModuleInfo["MODULE_ID"].'/">'.Loc::getMessage("SKWB24_INFORMER_buy_extension").'</a>';
			}elseif(/*!empty($arModuleInfo["DATE_TO"]) && */(MakeTimeStamp($arModuleInfo["DATE_TO"])-time())/86400<self::ALARM_PERIOD){
				$strBlock.='<span class="error">'.Loc::getMessage("SKWB24_INFORMER_attention").'</span> '.Loc::getMessage("SKWB24_INFORMER_lysens_expires").' '.$arModuleInfo["DATE_TO"].'. <a class="adm-btn adm-btn-save" href="http://marketplace.1c-bitrix.ru/solutions/'.$arModuleInfo["MODULE_ID"].'/">'.Loc::getMessage("SKWB24_INFORMER_buy_extension").'</a>';
			}elseif(!empty($arModuleInfo["DATE_TO"])){
				$strBlock.=Loc::getMessage("SKWB24_INFORMER_lysens_active").' '.$arModuleInfo["DATE_TO"].'.';
			}
			$strBlock.='</p>';
			//date module
			$strBlock.='<p class="dateinfo">';
			if($arModuleInfo['DEMO_END']=='Y'){
				$strBlock.='<span class="error">'.Loc::getMessage("SKWB24_INFORMER_demo_end").'</span>';
			}elseif(!empty($arModuleInfo['DEMO_DATE'])){
				$strBlock.=Loc::getMessage("SKWB24_INFORMER_remained").': '.FormatDate('ddiff', time(), $arModuleInfo["DEMO_DATE"]);
			}elseif(/*!empty($arModuleInfo["DATE_TO"]) && */MakeTimeStamp($arModuleInfo["DATE_TO"])<time()){
				$strBlock.='<span class="error">'.Loc::getMessage("SKWB24_INFORMER_lysens_end").'</span>';
			}elseif(/*!empty($arModuleInfo["DATE_TO"]) && */(MakeTimeStamp($arModuleInfo["DATE_TO"])-time())/86400<self::ALARM_PERIOD){
				$strBlock.='<span class="error">'.Loc::getMessage("SKWB24_INFORMER_lysens_remained").'</span>';
            }elseif(empty($arModuleInfo['CURRENT_VERSION'])){
				$strBlock.=Loc::getMessage("SKWB24_INFORMER_lysens_good");
			}
			$strBlock.='</p>';
			//version
			$strBlock.='<p class="version">';
			if(empty($arModuleInfo['CURRENT_VERSION'])){
				$strBlock.=Loc::getMessage("SKWB24_INFORMER_version_install").': <span class="good"><b>'.$arModuleInfo["MODULE_VERSION"].'</b></span>. '.Loc::getMessage("SKWB24_INFORMER_version_actual").'.';
			}else{
				$strBlock.=Loc::getMessage("SKWB24_INFORMER_version_install").': <span class="error"><b>'.$arModuleInfo["MODULE_VERSION"].'</b></span>. <a class="adm-btn adm-btn-save" href="/bitrix/admin/update_system_partner.php?tabControl_active_tab=tab2&amp;addmodule='.$arModuleInfo["MODULE_ID"].'&amp;lang='.LANGUAGE_ID.'">'.Loc::getMessage("SKWB24_INFORMER_updates_available").'</a>.';
			}
			$strBlock.='</p>';
			//links
			$idArr=explode('.',$arModuleInfo['MODULE_ID']);
			$strBlock.='<div class="buttonsBlock">';
				$strBlock.='<a class="documentation" href="https://skyweb24.ru/documentation/'.$idArr[1].'/" target="_blank">'.Loc::getMessage("SKWB24_INFORMER_button_docs").'</a>';
				$strBlock.='<a class="review" href="https://marketplace.1c-bitrix.ru/solutions/'.$arModuleInfo['MODULE_ID'].'/#tab-rating-link" target="_blank">'.Loc::getMessage("SKWB24_INFORMER_button_review").'</a>';
				$strBlock.='<a class="question" href="https://skyweb24.bitrix24.ru/online/go" target="_blank">'.Loc::getMessage("SKWB24_INFORMER_button_question").'</a>';
				$strBlock.='<a class="payment" href="https://skyweb24.bitrix24.ru/online/go" target="_blank">'.Loc::getMessage("SKWB24_INFORMER_button_work").'</a>';
				$strBlock.='<a class="modules" href="https://marketplace.1c-bitrix.ru/partners/detail.php?ID=981093.php" target="_blank">'.Loc::getMessage("SKWB24_INFORMER_button_all_decisions").'</a>';
			$strBlock.='</div>';
			$strBlock.='</section>';
			if($arModuleInfo['DEMO_END']=='Y'){
				return self::getStyles().$strBlock;
			}
			file_put_contents($filename, self::getStyles().$strBlock);
		}
		return file_get_contents($filename);
	}
	
	public static function createInfo(){
		echo self::cacheInfo();
	}
	
}
?>