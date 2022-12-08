<?
namespace Skyweb24\Loyaltyprogram\Profiles;
use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application,
	\Bitrix\Main\EventManager as BitrixEventManager,
	\Skyweb24\Loyaltyprogram,
	\Bitrix\Main\SystemException;
Loc::loadMessages(dirname(__DIR__).'/lang.php');

\Bitrix\Main\Loader::includeModule('sale');
/**
* main profile class
*/
class Profile{

	protected $profileSetting;
	protected $timePart;
	protected $globalSettings;
	protected $ranksObject;

	function __construct (){
		$this->globalSettings=Loyaltyprogram\Settings::getInstance();
		$this->ranksObject=new Loyaltyprogram\Ranks;
		
		$this->profileSetting=[
			'id'=>'new',
			'active'=>'N',
			'name'=>'',
			'type'=>'',
			'site'=>'',
			'date_setting'=>'',
			'settings'=>[],
			'email_settings'=>[],
			'sms_settings'=>[]
		];
		$this->timePart=[
			'hour'=>3600,
			'day'=>86400,
			'week'=>604800,
			'month'=>2592000
		];
	}

	public function setProperties(array $props){
	    foreach($props as $key=>$value){
	        $this->$key=$value;
        }
    }
	
	/**
	* fix that there were no duplicating records in bonus table
	*/
	protected function isAlreadyRow($fields){
		global $DB;
		if(count($fields)>0){
			$sql='select * from '.$this->globalSettings->getTableBonusList().' where 1=1';
			foreach($fields as $key=>$value){
				if(
				        $key=='status'
                        || $key=='date_add'
                        || $key=='date_remove'
                        || $key=='bonus'
                        || $key=='bonus_start'
                        || $key=='email'
                        || $key=='sms'
                        || $key=='add_comment'
                        || $key=='comments'
                ){continue;}
				/*if($key=='bonus' || $key=='bonus_start'){
					$value=round($value);
				}*/
				$sql.=' and '.$key.'='.$value;
			}
			$results=$DB->Query($sql);
			if($res = $results->Fetch()){
				return true;
			}
		}
		return false;
	}
	
	public function deleteProfile($idProfile){
		global $DB;
		$res=$DB->Query('select * from '.$this->globalSettings->getTableProfilesList().' where id='.$idProfile.';');
		if($row = $res->Fetch()){
			if(!empty($row['email_settings'])){
				$row['email_settings']=unserialize($row['email_settings']);
				foreach($row['email_settings'] as $nextEvent){
					foreach($nextEvent as $nextTemplate){
						if((int) $nextTemplate>0){
							\Bitrix\Main\Mail\Internal\EventMessageTable::delete($nextTemplate);
						}
					}
				}
			}
			if(!empty($row['sms_settings'])){
				$row['sms_settings']=unserialize($row['sms_settings']);
				foreach($row['sms_settings'] as $nextEvent){
					foreach($nextEvent as $nextTemplate){
						if((int) $nextTemplate>0){
							\Bitrix\Main\Sms\TemplateTable::delete($nextTemplate);
						}
					}
				}
			}

			$DB->Query('delete from '.$this->globalSettings->getTableProfilesList().' where id='.$idProfile.';');
			return true;
		}
		return false;
	}
	
	public function setProfile($settings=[]){
		$this->profileSetting=$settings;
		if(!empty($this->profileSetting['site'])){
			$this->profileSetting['site']=explode(',',$this->profileSetting['site']);
		}
	}
	
	public function isNew(){
		return $this->profileSetting['id']=='new';
	}
	
	/**
	* check order props with code skyweb24_bonus
	* if not - create this prop
	*/
	public function checkOrderProps(){
		$typePropsName=Loc::getMessage("skyweb24.loyaltyprogram_TYPE_PROPS_NAME");
		$persons=[];
		$res=\Bitrix\Sale\Internals\PersonTypeTable::getList();
		while($nextRes=$res->fetch()){
			$persons[$nextRes['ID']]=$nextRes['ID'];
		}
		$res=\Bitrix\Sale\Internals\OrderPropsGroupTable::getList(
			['filter'=>['%=NAME'=>'Skyweb24%']]
		);
		$groups=[];
		while($nextRes=$res->fetch()){
			$typePropsName=$nextRes['NAME'];
			$groups[$nextRes['ID']]=$nextRes;
			unset($persons[$nextRes['PERSON_TYPE_ID']]);
		}
		if(count($persons)>0){
			foreach($persons as $nextPerson){
				$res=\Bitrix\Sale\Internals\OrderPropsGroupTable::add([
					'PERSON_TYPE_ID'=>$nextPerson,
					'NAME'=>$typePropsName,
					'SORT'=>3
				]);
				$id = $res->getId();
				$groups[$id]=[
					'PERSON_TYPE_ID'=>$nextPerson,
					'NAME'=>$typePropsName,
					'ID'=>$id
				];
			}
		}
		$res=\Bitrix\Sale\Internals\OrderPropsTable::getList(
			['filter'=>['CODE'=>'skyweb24_bonus']]
		);
		while($nextRes=$res->fetch()){
			unset($groups[$nextRes['PROPS_GROUP_ID']]);
			$props[]=$nextRes;
		}
		
		if(count($groups)>0){
			foreach($groups as $nextGroup){
				try{
					$res=\Bitrix\Sale\Internals\OrderPropsTable::add([
						'PERSON_TYPE_ID'=>$nextGroup['PERSON_TYPE_ID'],
						'NAME'=>Loc::getMessage("skyweb24.loyaltyprogram_PROPS_NAME"),
						'TYPE'=>'NUMBER',
						'REQUIRED'=>'N',
						'DEFAULT_VALUE'=>'',
						'SORT'=>100,
						'USER_PROPS'=>'N',
						'IS_LOCATION'=>'N',
						'PROPS_GROUP_ID'=>$nextGroup['ID'],
						'DESCRIPTION'=>'',
						'IS_EMAIL'=>'N',
						'IS_PROFILE_NAME'=>'N',
						'IS_PAYER'=>'N',
						'IS_LOCATION4TAX'=>'N',
						'IS_FILTERED'=>'N',
						'CODE'=>'skyweb24_bonus',
						'IS_ZIP'=>'N',
						'IS_PHONE'=>'N',
						'IS_ADDRESS'=>'N',
						'ACTIVE'=>'Y',
						'UTIL'=>'N',
						'ENTITY_REGISTRY_TYPE'=>'ORDER',
						'INPUT_FIELD_LOCATION'=>'0',
						'MULTIPLE'=>'N',
						'SETTINGS'=>[
							'MIN'=>'0',
							'MAX'=>'',
							'STEP'=>''
						]					
					]);
				}catch (SystemException $e){//old version bitrix
					$res=\Bitrix\Sale\Internals\OrderPropsTable::add([
						'PERSON_TYPE_ID'=>$nextGroup['PERSON_TYPE_ID'],
						'NAME'=>Loc::getMessage("skyweb24.loyaltyprogram_PROPS_NAME"),
						'TYPE'=>'NUMBER',
						'REQUIRED'=>'N',
						'DEFAULT_VALUE'=>'',
						'SORT'=>100,
						'USER_PROPS'=>'N',
						'IS_LOCATION'=>'N',
						'PROPS_GROUP_ID'=>$nextGroup['ID'],
						'DESCRIPTION'=>'',
						'IS_EMAIL'=>'N',
						'IS_PROFILE_NAME'=>'N',
						'IS_PAYER'=>'N',
						'IS_LOCATION4TAX'=>'N',
						'IS_FILTERED'=>'N',
						'CODE'=>'skyweb24_bonus',
						'IS_ZIP'=>'N',
						'IS_PHONE'=>'N',
						'IS_ADDRESS'=>'N',
						'ACTIVE'=>'Y',
						'UTIL'=>'N',
						'INPUT_FIELD_LOCATION'=>'0',
						'MULTIPLE'=>'N',
						'SETTINGS'=>[
							'MIN'=>'0',
							'MAX'=>'',
							'STEP'=>''
						]					
					]);
				}
			}
		}
	}
	
	public function getProfile(){
		return $this->profileSetting;
	}
	
	public static function getProfileByType($type){
		$classNameNew='\Skyweb24\Loyaltyprogram\\Profiles\\'.$type;
		return new $classNameNew();
	}
	
	public static function getActiveProfileByType($type){
		$idActiveProfiles=[];
		global $DB;
		$tmpSettings=Loyaltyprogram\Settings::getInstance();
		$oprions=$tmpSettings->getOptions();
		//if($oprions['ref_active']=='Y'){
			$res=$DB->Query('select * from '.$tmpSettings->getTableProfilesList().' where type="'.$type.'" and active="Y" order by sort asc,id asc;');
			while($row = $res->Fetch()){
				$idActiveProfiles[]=$row['id'];
			}
		//}
		return $idActiveProfiles;
	}
	
	public static function getProfileById($id){
		global $DB;
		$tmpSettings=Loyaltyprogram\Settings::getInstance();
		$res=$DB->Query('select * from '.$tmpSettings->getTableProfilesList().' where id='.$id.';');
		$row = $res->Fetch();
		$row['settings']=unserialize($row['settings']);
		$row['email_settings']=unserialize($row['email_settings']);
		$row['sms_settings']=unserialize($row['sms_settings']);
		$classNameNew='\\Skyweb24\\Loyaltyprogram\\Profiles\\'.$row['type'];
		$profileO=new $classNameNew();
		$profileO->setProfile($row);
		return $profileO;
	}
		
	public function getUserByRef($ref){
		//get id user by ref value
		$options=$this->globalSettings->getOptions();
		if($options['ref_link_value']=='XML_ID'){
			$res =\Bitrix\Main\UserTable::getList([
				"select"=>["ID","NAME"],
				"filter"=>['=XML_ID'=>$ref]
			]);
			$ref=0;
			if($arRes = $res->fetch()){
			  $ref=$arRes['ID'];
			}
		}elseif($options['ref_link_value']=='LOGIN'){
			$res =\Bitrix\Main\UserTable::getList([
				"select"=>["ID","NAME"],
				"filter"=>['=LOGIN'=>$ref]
			]);
			$ref=0;
			if($arRes = $res->fetch()){
			  $ref=$arRes['ID'];
			}
		}elseif($options['ref_link_value']=='PROP'){
			$ar_res = \CUserTypeEntity::GetByID($options['ref_prop']);
			$res =\Bitrix\Main\UserTable::getList([
				"select"=>["ID","NAME"],
				"filter"=>['='.$options['ref_prop']=>$ref]
			]);
			$ref=0;
			if($arRes = $res->fetch()){
			  $ref=$arRes['ID'];
			}
		}else{
			// global $DB;
			// $results=$DB->Query('select * from b_user where id='.$ref);
			$ref = htmlspecialchars($ref);
			$ref = (int)$ref;
			$results = \CUser::GetByID($ref);
			$ref=0;
			if($arUser = $results->Fetch()){
				$ref=$arUser['ID'];
			}
		}
		return $ref;
	}
	
	private function checkUserGroup($idUser){
		$refGroups=$this->moduleOptions['ref_link_group'];
		if(!empty($refGroups)){
			$refGroups=explode(',',$refGroups);
			if(count($refGroups)>0){
				$tmpArr=array_intersect($refGroups, \CUser::GetUserGroup($idUser));
				if(count($tmpArr)>0){
					return true;
				}
			}
			return false;
		}
		return true;
	}
	
	public function getChainReferalByFirstParent($refUserId){
		global $DB;
		$options=$this->globalSettings->getOptions();
		$maxLevel=$options['ref_level'];
		$rewards=[];
		if($maxLevel>0){
			for($key=0; $key<$maxLevel; $key++){
				$res=$DB->Query('select * from '.$this->globalSettings->getTableUsersList().' where user='.$refUserId.';');
				if($row = $res->Fetch()){
					$rewards[]=$row['user'];
					if(empty($row['ref_user'])){
						break;
					}else{
						$refUserId=$row['ref_user'];
					}
				}else{
					break;
				}
			}
		}
		if(count($rewards)==0){
			$rewards[]=$refUserId;
		}
		return $rewards;
	}
	
	public function getChainReferal($userId){
		global $DB, $USER;
		$options=$this->globalSettings->getOptions();
		$maxLevel=$options['ref_level'];
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
	
		//$cookieRefId only unauthorized users!!!
		$cookieRefId=$request->getCookie("skwb24_loyaltyprogram_ref");
		if(empty($cookieRefId) && !empty($_SESSION['skwb24_loyaltyprogram_ref'])){
			$cookieRefId=$_SESSION['skwb24_loyaltyprogram_ref'];
		}
		if(!empty($USER) && $USER->IsAuthorized() && empty($_SESSION['sw24_register_ref'])){
			$cookieRefId='';
		}
		//check ref chain in table
		$refUserId=(!empty($cookieRefId))?$this->getUserByRef($cookieRefId):0;
		$userLevel=1;
		
		$res=$DB->Query('select * from '.$this->globalSettings->getTableUsersList().' where user='.$userId.';');
		if(!$row = $res->Fetch()){
			
			if($refUserId>0 && $this->checkUserGroup($refUserId)){
				$resRef=$DB->Query('select * from '.$this->globalSettings->getTableUsersList().' where user='.$refUserId.';');
				if($rowRef = $resRef->Fetch()){
					$userLevel=$rowRef['level']+1;
				}else{
					$DB->Insert($this->globalSettings->getTableUsersList(), [
						'user'=>$refUserId,
						'ref_user'=>0,
						'level'=>1
					], $err_mess.__LINE__);
					$userLevel=2;
				}
			}
			
			$typeRef=($refUserId>0)?'link':'simple';
			
			$DB->Insert($this->globalSettings->getTableUsersList(), [
				'user'=>$userId,
				'ref_user'=>$refUserId,
				'type'=>'"'.$typeRef.'"',
				'level'=>$userLevel,
				'date_create'=>'NOW()'
			], $err_mess.__LINE__);
			if($refUserId>0 && $typeRef=='link'){
				Loyaltyprogram\Statistic::setRegisterByLink($refUserId, $userId);
			}
			
			//fire event register in refsystem
			$event = new \Bitrix\Main\Event($this->globalSettings->getModuleId(), "OnRegisterInRefSystem",[
				'USER_ID'=>$userId,
				'REFERRAL_ID'=>$refUserId,
				'PROFILE_ID'=>$this->profileSetting['id']
			]);
			$event->send();
		}else{
			$refUserId=$row['ref_user'];
		}
		//get chain rewards
		$rewards=[];
		if($maxLevel>0){
			for($key=0; $key<$maxLevel; $key++){
				
				$res=$DB->Query('select * from '.$this->globalSettings->getTableUsersList().' where user='.$refUserId.';');
				if($row = $res->Fetch()){
					$rewards[]=$row['user'];
					if(empty($row['ref_user'])){
						break;
					}else{
						$refUserId=$row['ref_user'];
					}
				}else{
					break;
				}
			}
		}
		return $rewards;
	}
	
	protected function registerEvent($module, $moduleEvent, $event){
		$register=true;
		$handlers = BitrixEventManager::getInstance()->findEventHandlers($module, $moduleEvent);
		foreach($handlers as $nextHandler){
			if($nextHandler['TO_MODULE_ID']==$this->globalSettings->getModuleId() && $nextHandler['TO_CLASS']==$event){
				$register=false;
				break;
			}
		}
		if($register===true){
			BitrixEventManager::getInstance()->registerEventHandler(
				$module,
				$moduleEvent,
				$this->globalSettings->getModuleId(),
				"Skyweb24\\Loyaltyprogram\\Eventmanager",
				$event
			);
		}
		return true;
	}
	
	protected function registerAgent($function, $period=86400, $delay=0){
		$delayExec=ConvertTimeStamp(time()+$delay, "FULL", LANGUAGE_ID);
		\CAgent::AddAgent("\\Skyweb24\\Loyaltyprogram\\Eventmanager::".$function."();", $this->globalSettings->getModuleId(), "N", $period, "", "Y", $delayExec);
	}
	
	/*protected function unRegisterEvent($module, $moduleEvent, $event){
		$handlers = BitrixEventManager::getInstance()->findEventHandlers($module, $moduleEvent);
		foreach($handlers as $nextHandler){
			if($nextHandler['TO_MODULE_ID']==$this->globalSettings->getModuleId() && $nextHandler['TO_METHOD']==$event){
				BitrixEventManager::getInstance()->unRegisterEventHandler(
					$module,
					$moduleEvent,
					$this->globalSettings->getModuleId(),
					"Skyweb24\\Loyaltyprogram\\Eventmanager",
					$event
				);
				break;
			}
		}
	}*/
	
	private function getDescription(){
		$tmpPath=$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.Loyaltyprogram\Settings::getInstance()->getModuleId().'/include/descprofiles/'.strtolower($this->profileSetting['type']).'.html';
		if(file_exists($tmpPath)){
			$tmpStr=file_get_contents($tmpPath);
			if(LANG_CHARSET!='windows-1251'){
				$tmpStr=iconv('windows-1251', 'utf-8', $tmpStr);
			}
			return $tmpStr;
		}
		return false;
	}
	
	protected function drawRow($type){
		switch ($type){
			/*case 'lineMainSetting':?>
			<tr class="heading"><td colspan="2"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_LINE_MAIN")?></td></tr>
			<?break; case 'lineBonusAcc':?>
			<tr class="heading"><td colspan="2"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_LINE_BONUSACC")?></td></tr>
			<?break; case 'lineBonusWithdraw':?>
			<tr class="heading"><td colspan="2"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_LINE_BONUSWITHDRAW")?></td></tr>
			<?break;*/ case 'type':
			$tmpProfiles=new Loyaltyprogram\Profiles;
			$tmpProfiles=$tmpProfiles->getListProfiles();
			$desc=$this->getDescription();
			?>
			<tr><td width="40%" style="vertical-align:top;"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_TYPE")?></td><td>
				<?if($desc!==false){?>
				<div class="outer_desc">
					<a id="show_desc_profile" href="javascript:void(0);"><?=$tmpProfiles[$this->profileSetting['type']]?></a>
					<div style="display:none;" class="inner_desc"><?=$desc?></div>
					<script>
						BX('show_desc_profile').addEventListener('click', function (event) {
							let cblock=this.parentNode.querySelector('.inner_desc'),
								cView=(cblock.style.display=='block')?'none':'block';
								cblock.style.display=cView;
						});
					</script>
				</div>
				<?}else{?>
					<?=$tmpProfiles[$this->profileSetting['type']]?>
				<?}?>
			</td></tr>
			<?break; case 'baseCalculate':?>
			<tr><td width="40%" style="vertical-align:top;"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BASECALCULATE")?></td>
			<td>
				<select name="base_calculate">
					<?
					$selectedN='';
					if(!empty($this->profileSetting['settings']['base_calculate']) && $this->profileSetting['settings']['base_calculate']=='N'){
						$selectedN=' selected';
					}
					?>
					<option value="Y"><?=Loc::getMessage("skyweb24.loyaltyprogram_Y")?></option>
					<option value="N"<?=$selectedN?>><?=Loc::getMessage("skyweb24.loyaltyprogram_N")?></option>
				</select>
			</td></tr>
			<?break; case 'profileActive':
			$checked=($this->profileSetting['active']=='N' || empty($this->profileSetting['active']))?'':' checked="checked"';?>
			<tr><td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ACTIVE")?></td><td><input type="checkbox" name="active" value="Y"<?=$checked?> /></td></tr>
			<?break; case 'profileName':
				if(empty($this->profileSetting['name'])){
					$listProfiles=new \Skyweb24\Loyaltyprogram\Profiles;
					$availableProfiles=$listProfiles->getListProfiles();
					$profileName=$availableProfiles[$this->profileSetting['type']];
				}else{
					$profileName=$this->profileSetting['name'];
				}
			?>
			<tr><td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_NAME")?></td><td><input type="text" name="profile_name" value="<?=$profileName?>" /></td></tr>
			<?break; /*case 'nextCharge':
			if($this->profileSetting['id']!='new'){
				$period=Loyaltyprogram\Tools::getPeriod($this->profileSetting['settings']['bonuses']['bonus_period']);?>
				<tr><td colspan="2"><?\CAdminMessage::ShowMessage(["MESSAGE"=>Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_TURNOVER_NEXT", ['#CURRENT#'=>$bxDataStr, '#START#'=>$period['dateFrom']['format'], '#END#'=>$period['dateTo']['format']]), "TYPE"=>"OK","HTML"=>true]);?></td></tr>
			<?}?>
			<?break; case 'bonusSizeTurnover':
			$options=$this->globalSettings->getOptions();
			$bonusSizeTurnover=(!empty($this->profileSetting['settings']['bonuses']['bonus_size_turnover']))?$this->profileSetting['settings']['bonuses']['bonus_size_turnover']:0;?>
			<tr><td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_SIZE_TURNOVER")?></td><td><input type="number" min="0" step="1" name="bonus_size_turnover" value="<?=$bonusSizeTurnover?>" /> <?=$options['currency']?> </td></tr>
			<?break; case 'bonusSize':
			$bonusSize=(!empty($this->profileSetting['settings']['bonuses']['bonus_size']))?$this->profileSetting['settings']['bonuses']['bonus_size']:0;?>
			<tr><td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_SIZE")?></td><td><input type="number" min="0" step="1" name="bonus_size" value="<?=$bonusSize?>" /> <?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_UNITS_BONUS")?></td></tr>
			<?
			break; case 'basketRulesList';
			$selectedArr=(!empty($this->profileSetting['settings']['conditions']['basket_rules']))?$this->profileSetting['settings']['conditions']['basket_rules']:[];
			$basketRulesList=[0=>'...'];
			$discountIterator = \Bitrix\Sale\Internals\DiscountTable::getList([
				'select' => ["ID", "NAME"],
				'filter' => ['ACTIVE' => 'Y'],
				'order' => ["NAME" => "ASC"]
			]);
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BASKET_RULES")?></td>
				<td>
					<select size="4" multiple name="basket_rules[]">
						<option value="0">...</option>
						<?while ($discount = $discountIterator->fetch()){
							$selected=(count($selectedArr)>0 && in_array($discount['ID'], $selectedArr))?' selected="selected"':'';?>
							<option value="<?=$discount['ID']?>" <?=$selected?>><?=$discount['NAME']?> [<?=$discount['ID']?>]</option>
						<?}?>
					</select>
				</td>
			</tr>
			<?break; case 'bonusLive':
			$bonusLive=(!empty($this->profileSetting['settings']['bonuses']['bonus_live']))?$this->profileSetting['settings']['bonuses']['bonus_live']:0;
			$bonusTypeLive=[
				'day'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_DAY"),
				'week'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_WEEK"),
				'month'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_MONTH")
			];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_LIVE")?></td>
				<td>
					<input type="number" min="0" step="1" name="bonus_live" value="<?=$bonusLive?>" />
					<select name="bonus_live_type">
						<?foreach($bonusTypeLive as $key=>$value){
							$selected=(!empty($this->profileSetting['settings']['bonuses']['bonus_live_type']) && $this->profileSetting['settings']['bonuses']['bonus_live_type']==$key)?' selected="selected"':'';
							?>
							<option value="<?=$key?>" <?=$selected?>><?=$value?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?break; case 'bonusPeriod':
			$bonusPeriod=(!empty($this->profileSetting['settings']['bonuses']['bonus_period']))?$this->profileSetting['settings']['bonuses']['bonus_period']:'month';
			$bonusTypePeriod=[
				'week'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_WEEK"),
				'month'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_MONTH"),
				'quarter'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_QUARTER"),
				'year'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_YEAR")
			];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_PERIOD", ['#START#'=>$periodStart, '#END#'=>$periodEnd])?></td>
				<td>
					<select name="bonus_period">
						<?foreach($bonusTypePeriod as $key=>$value){
							$selected=($bonusPeriod==$key)?' selected="selected"':'';
							?>
							<option value="<?=$key?>" <?=$selected?>><?=$value?></option>
						<?}?>
					</select>
					<script>
					<?
						$dataArr=[];
						
						$dataArr['week']=Loyaltyprogram\Tools::getPeriod('week');
						$dataArr['month']=Loyaltyprogram\Tools::getPeriod('month');
						$dataArr['quarter']=Loyaltyprogram\Tools::getPeriod('quarter');
						$dataArr['year']=Loyaltyprogram\Tools::getPeriod('year');
					?>
						var bonusPeriod=document.querySelector('[name=bonus_period]');
						function setPeriod(){
							var periodData=<?=\CUtil::PhpToJSObject($dataArr)?>,
								bonusPeriod=document.querySelector('[name=bonus_period]');
							BX('sw24_bonus_period_from').innerHTML=periodData[bonusPeriod.value].dateFrom.format;
							BX('sw24_bonus_period_to').innerHTML=periodData[bonusPeriod.value].dateTo.format;
						}
						BX.ready(function(){
							setPeriod();
							bonusPeriod.addEventListener("change", setPeriod);
						})
					</script>
				</td>
			</tr>
			<?break; case 'bonusDelay':
			$bonusDelay=(!empty($this->profileSetting['settings']['bonuses']['bonus_delay']))?$this->profileSetting['settings']['bonuses']['bonus_delay']:0;
			$bonusTypeSelect=[
				'hour'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_HOUR"),
				'day'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_DAY"),
				'week'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_WEEK"),
				'month'=>Loc::getMessage("skyweb24.loyaltyprogram_TIME_MONTH")
			];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_DELAY")?></td>
				<td>
					<input type="number" min="0" step="1" name="bonus_delay" value="<?=$bonusDelay?>" />
					<select name="bonus_delay_type">
						<?foreach($bonusTypeSelect as $key=>$value){
							$selected=(!empty($this->profileSetting['settings']['bonuses']['bonus_delay_type']) && $this->profileSetting['settings']['bonuses']['bonus_delay_type']==$key)?' selected="selected"':'';
							?>
							<option value="<?=$key?>" <?=$selected?>><?=$value?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?break;*/case 'activeSite':
			$siteList=$this->globalSettings->getSites();
			$sizeList=count($siteList)>3?4:(count($siteList)+1);
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_SITES")?></td>
				<td>
					<select name="site[]" size="<?=$sizeList?>" multiple="multiple"><option value="">...</option>
						<?
						foreach($siteList as $key=>$value){
							$selected=(!empty($this->profileSetting['site']) && in_array($key, $this->profileSetting['site']))?' selected="selected"':'';
							?>
							<option value="<?=$key?>" <?=$selected?>><?=$value?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?break;case 'propBirthday':
			$propList=$this->globalSettings->getUsersProps(['string', 'date', 'datetime']);?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_PROP_BIRTHDAY")?></td>
				<td>
					<select name="prop_birthday"><option value="PERSONAL_BIRTHDAY"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_PERSONAL_BIRTHDAY")?></option>
						<?foreach($propList as $key=>$value){
							$label=(!empty($value['EDIT_FORM_LABEL']))?$value['EDIT_FORM_LABEL'].' ['.$key.']':$key;
							$selected=(!empty($this->profileSetting['settings']['propbirthday']) && $this->profileSetting['settings']['propbirthday']==$key)?' selected="selected"':'';
							?>
							<option value="<?=$key?>" <?=$selected?>><?=$label?></option>
						<?}?>
					</select>
				</td>
			</tr>
			<?break; case 'propCopyright':
			\Bitrix\Main\Loader::includeModule('catalog');
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_PROPCOPYRIGHT")?></td>
				<td>
					<select name="prop_copyright">
					<?
					$paramsProp=\CCatalogCondCtrlIBlockProps::GetControlShow(['SHOW_IN_GROUPS'=>['copyrighter']]);
					$selectProp=$this->profileSetting['settings']['prop_copyright'];
					foreach($paramsProp as $nextOptGroup){?>
						<optgroup label="<?=$nextOptGroup['label']?>">
						<?foreach($nextOptGroup['children'] as $prop){
							$propId=explode(':',$prop['controlId']);
							$propId=$propId[2];
							$selected=($selectProp==$propId)?' selected="selected"':'';?>
							<option value="<?=$propId?>"<?=$selected?>><?=$prop['label']?></option>
						<?}?>
						</optgroup>
					<?}?>
					</select>
				</td>
			</tr>
			<?break;case 'profileSort':
			$profileSort=empty($this->profileSetting['sort'])?100:$this->profileSetting['sort'];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_SORT")?></td>
				<td>
					<input type="number" min="0" step="1" name="sort" value="<?=$profileSort?>" />
				</td>
			</tr>
			<?break; /*case 'withdraw';
			$withdraw=empty($this->profileSetting['settings']['withdraw'])?0:$this->profileSetting['settings']['withdraw'];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_WITHDRAW")?></td>
				<td>
					<input type="number" min="0" step="1" name="withdraw" value="<?=$withdraw?>" />
					<select name="withdraw_unit">
					<?
						$selectedBonus=(!empty($this->profileSetting['settings']['withdraw_unit']) && $this->profileSetting['settings']['withdraw_unit']=='bonus')?' selected="selected"':'';
						$selectedPercent=(!empty($this->profileSetting['settings']['withdraw_unit']) && $this->profileSetting['settings']['withdraw_unit']=='percent')?' selected="selected"':'';
					?>
						<option value="bonus"<?=$selectedBonus?>><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_UNITS_BONUS")?></option>
						<option value="percent"<?=$selectedPercent?>><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_UNITS_PERCENT")?></option>
					</select>
				</td>
			</tr>
			<?break; case 'withdrawMax';
			$withdrawMax=empty($this->profileSetting['settings']['withdraw_max'])?0:$this->profileSetting['settings']['withdraw_max'];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_WITHDRAW_MAX")?></td>
				<td>
					<input type="number" min="0" step="1" name="withdraw_max" value="<?=$withdrawMax?>" />
				</td>
			</tr>
			<?break; case 'considDiscounts';
			$checked=(empty($this->profileSetting['settings']['consid_discounts']) || $this->profileSetting['settings']['consid_discounts']=='N')?'':' checked="checked"';?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_CONSID_DISCOUNTS")?></td>
				<td>
					<input type="checkbox" name="consid_discounts" value="Y"<?=$checked?>>
				</td>
			</tr>
			<?break; case 'minPrice';
			$limitMin=empty($this->profileSetting['settings']['min_price'])?0:$this->profileSetting['settings']['min_price'];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_MAX_PRICE")?></td>
				<td>
					<input type="number" min="0" step="1" name="min_price" value="<?=$limitMin?>" />
				</td>
			</tr>
			<?break; case 'bonusAdd';
			$bonusAdd=!isset($this->profileSetting['settings']['bonuses']['bonus_add'])?100:$this->profileSetting['settings']['bonuses']['bonus_add'];
			?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_SIZE_PER_ORDER")?></td>
				<td>
					<input type="number" min="0" step="1" name="bonus_add" value="<?=$bonusAdd?>" />
					<select name="bonus_unit">
					<?
						$selectedBonus=(!empty($this->profileSetting['settings']['bonuses']['bonus_unit']) && $this->profileSetting['settings']['bonuses']['bonus_unit']=='bonus')?' selected="selected"':'';
						$selectedPercent=(!empty($this->profileSetting['settings']['bonuses']['bonus_unit']) && $this->profileSetting['settings']['bonuses']['bonus_unit']=='percent')?' selected="selected"':'';
						$percentName=($this->profileSetting['type']=='Turnover')?Loc::getMessage("skyweb24.loyaltyprogram_PARAM_UNITS_PERCENT_TURNOVER"):Loc::getMessage("skyweb24.loyaltyprogram_PARAM_UNITS_PERCENT_ORDER");
					?>
						<option value="bonus"<?=$selectedBonus?>><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_UNITS_BONUS")?></option>
						<option value="percent"<?=$selectedPercent?>><?=$percentName?></option>
					</select>
				</td>
			</tr>
			<?break; case 'groupUsers';?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_BONUS_GROUPUSER")?></td>
				<td>
					<select multiple size="4" name="group_users[]">
					<?
						$selectedArr=(!empty($this->profileSetting['settings']['conditions']['group_users']))?$this->profileSetting['settings']['conditions']['group_users']:[];
					?>
						<option value="0">...</option>
						<?
							$result = \Bitrix\Main\GroupTable::getList([
								'select'  =>['NAME','ID','STRING_ID','C_SORT'],
								'order'  =>['NAME'=>'ASC']
							]);
							while ($arGroup = $result->fetch()){
								$selected=(count($selectedArr)>0 && in_array($arGroup['ID'], $selectedArr))?' selected="selected"':'';
								?>
								<option value="<?=$arGroup['ID']?>"<?=$selected?>><?=$arGroup['NAME']?></option>
							<?}?>
					</select>
				</td>
			</tr>
			<?break; case 'orderStatuses';?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ORDER_STATUSES")?></td>
				<td>
					<select name="order_statuses"><option value="0">...</option>
					<?foreach($this->globalSettings->getOrderStatuses() as $nextStatus){
						$selected=(!empty($this->profileSetting['settings']['order_statuses']) && $this->profileSetting['settings']['order_statuses']==$nextStatus['STATUS_ID'])?' selected="selected"':'';?>
						<option value="<?=$nextStatus['STATUS_ID']?>"<?=$selected?>>[<?=$nextStatus['STATUS_ID']?>] <?=$nextStatus['NAME']?></option>
					<?}?>
					</select>
				</td>
			</tr>
			<?break; case 'roundBonus';?>
			<tr>
				<td width="40%"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ROUND_BONUS")?></td>
				<td><select name="round_bonus"><option value="none"><?=Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ROUND_BONUS_NO")?></option>
				<?
					$listRound=[
						'ceil'=>Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ROUND_BONUS_CEIL"),
						'floor'=>Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ROUND_BONUS_FLOOR"),
						'round'=>Loc::getMessage("skyweb24.loyaltyprogram_PARAM_ROUND_BONUS_AUTO")
					];
					foreach($listRound as $keyRound=>$valRound){
						$selected=(!empty($this->profileSetting['settings']['round_bonus']) && $this->profileSetting['settings']['round_bonus']==$keyRound)?' selected="selected"':'';?>
						<option value="<?=$keyRound?>"<?=$selected?>><?=$valRound?></option>
					<?}
				?>
				</select></td>
			</tr>
			<?break;*/
		}
	}
	
	//sms setting
	protected function checkSMSMain(){
		$sites=array_keys($this->globalSettings->getSites());
		$isUpdate=false;
		foreach($this->profileSetting['sms_settings'] as $keyEvent=>&$valEvent){
			
			try{
				$res=\Bitrix\Main\Mail\Internal\EventTypeTable::getList(
					['filter'=>['EVENT_NAME'=>$keyEvent, 'EVENT_TYPE'=>'sms']]
				);
				$nextRes=$res->fetch();
			}catch (\Exception $e) {
				continue;
			}
			
			if($nextRes==false){
				$fieldsEvent=$this->SMSType($keyEvent);
				$resAdd=\Bitrix\Main\Mail\Internal\EventTypeTable::add($fieldsEvent);
				$id = $resAdd->getId();
			}
			foreach($valEvent as $keyTmplt=>&$nextTmplt){
				if($nextTmplt==0){
					$isUpdate=true;
					$fieldsEvent=$this->SMSTemplates($keyTmplt);
					$entity = \Bitrix\Main\Sms\TemplateTable::getEntity();
					$site = \Bitrix\Main\SiteTable::getEntity()->wakeUpObject($sites[0]);
					$template = $entity->createObject();
					foreach($fieldsEvent as $field => $value){
						$template->set($field, $value);
					}
					$template->addToSites($site);
					$template->save();
					$nextTmplt=$template->getId();
				}
			}
		}
		if($isUpdate){
			global $DB;
			$DB->Update($this->globalSettings->getTableProfilesList(), [
				'sms_settings'=>"'".serialize($this->profileSetting['sms_settings'])."'"
			], "where id='".$this->profileSetting['id']."'", $err_mess.__LINE__);
		}
	}
	
	//email setting
	protected function checkEmailMain(){
		
		//add a check for the existence of templates - and if deleted, create anew and overwrite in the profile!!!
		
		$isUpdate=false;
		foreach($this->profileSetting['email_settings'] as $keyEvent=>&$valEvent){
			$res=\Bitrix\Main\Mail\Internal\EventTypeTable::getList(
				['filter'=>['EVENT_NAME'=>$keyEvent]]
			);
			if(!$nextRes=$res->fetch()){
				$fieldsEvent=$this->mailType($keyEvent);
				$fieldsEvent['DESCRIPTION']=preg_replace('/[ ]{2,}|[\t]/', ' ', trim($fieldsEvent['DESCRIPTION']));
				$resAdd=\Bitrix\Main\Mail\Internal\EventTypeTable::add($fieldsEvent);
				$id = $resAdd->getId();
			}
			foreach($valEvent as $keyTmplt=>&$nextTmplt){
				if($nextTmplt==0){
					$isUpdate=true;
					$fieldsEvent=$this->mailTemplates($keyTmplt);
					$fieldsEvent['MESSAGE']=preg_replace('/[ ]{2,}|[\t]/', ' ', trim($fieldsEvent['MESSAGE']));
					$resAdd=\Bitrix\Main\Mail\Internal\EventMessageTable::add($fieldsEvent);
					//$id = $resAdd->getId();
					$nextTmplt=$resAdd->getId();
					$tmpSites=array_keys($this->globalSettings->getSites());
					foreach($tmpSites as $nextSite){
						\Bitrix\Main\Mail\Internal\EventMessageSiteTable::add([
							'EVENT_MESSAGE_ID'=>$nextTmplt,
							'SITE_ID'=>$nextSite
						]);
					}
				}
			}
		}
		//insert new email templates into profile table
		if($isUpdate){
			global $DB;
			$DB->Update($this->globalSettings->getTableProfilesList(), [
				'email_settings'=>"'".serialize($this->profileSetting['email_settings'])."'"
			], "where id='".$this->profileSetting['id']."'", $err_mess.__LINE__);
		}
	}
	
	protected function checkSMSList(){
		$this->profileSetting['sms_settings']=[];
	}
	
	private function getStatusSMSTemplates(){
		$activeIDS=[];
		global $DB;
		$results=$DB->Query('SHOW tables like "%b_sms_template%"');
		if(!$results->Fetch()){
			return [];
		}
		foreach($this->profileSetting['sms_settings'] as $keyEvent=>$nextEvent){
			$results=$DB->Query('select * from b_sms_template where EVENT_NAME="'.$keyEvent.'";');
			while($arTemplate = $results->Fetch()){
				if($arTemplate['ACTIVE']=='Y'){
					$activeIDS[]=$arTemplate['ID'];
				}
			}
		}
		return $activeIDS;
	}
	
	public function drawSMSList(){
		?>
		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage("skyweb24.loyaltyprogram_TAB_REF_SMS");?></td>
		</tr>
		<?
		$this->checkSMSList();
		$activeIDS=$this->getStatusSMSTemplates();
		if(count($this->profileSetting['sms_settings'])>0){
			foreach($this->profileSetting['sms_settings'] as $keyEvent=>$nextEvent){
				$SMSType=$this->SMSType($keyEvent);
				foreach($nextEvent as $keyTemplate=>$nextTemplate){
					$activeShecked=in_array($nextTemplate, $activeIDS)?Loc::getMessage("skyweb24.loyaltyprogram_ACTIVE"):Loc::getMessage("skyweb24.loyaltyprogram_NOACTIVE");?>
				<tr>
					<td width="50%"><?=Loc::getMessage("skyweb24.loyaltyprogram_".$SMSType['EVENT_NAME']."_".$keyTemplate)?></td>
					<td>
						<a href="/bitrix/admin/sms_template_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$nextTemplate?>" target="_blank">#<?=$nextTemplate?></a> <span>(<?=$activeShecked?>)</span><br>
					</td>
				</tr>
				<?}
			}
		}else{?>
			<tr>
				<td>
					<?=Loc::getMessage("skyweb24.loyaltyprogram_SMS_TEMPOPARY_UNAVAILABLE")?>
				</td>
			</tr>
		<?}?>
	<?}
	
	private function getStatusEmailTemplates(){
		$activeIDS=[];
		foreach($this->profileSetting['email_settings'] as $keyEvent=>$nextEvent){
			$rsMess = \CEventMessage::GetList($by="site_id", $order="desc", ['TYPE_ID'=>$keyEvent]);
			while($arMess = $rsMess->GetNext()){
				if($arMess["ACTIVE"]=='Y'){
					$activeIDS[]=$arMess["ID"];
					
				}
			}
		}
		return $activeIDS;
	}
	
	public function drawEmailList(){
		?>
		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage("skyweb24.loyaltyprogram_TAB_REF_MAIL");?></td>
		</tr>
		<?
		$this->checkEmailList();
		$activeIDS=$this->getStatusEmailTemplates();
		foreach($this->profileSetting['email_settings'] as $keyEvent=>$nextEvent){
			$mailType=$this->mailType($keyEvent);
			?>
			<?foreach($nextEvent as $keyTemplate=>$nextTemplate){
					$mailTemplate=$this->mailTemplates($keyTemplate);
					$activeShecked=in_array($nextTemplate, $activeIDS)?Loc::getMessage("skyweb24.loyaltyprogram_ACTIVE"):Loc::getMessage("skyweb24.loyaltyprogram_NOACTIVE");
					?>
			<tr>
				<td width="50%"><?=Loc::getMessage("skyweb24.loyaltyprogram_".$mailType['EVENT_NAME']."_".$keyTemplate)?></td>
				<td>
					<a href="/bitrix/admin/message_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$nextTemplate?>" target="_blank">#<?=$nextTemplate?></a> <span>(<?=$activeShecked?>)</span><br>
				</td>
			</tr>
			<?}?>
		<?}
	}
	//e. o. email setting
	
	protected function clearSites($sites){
		if(!empty($sites) && count($sites)>0){
			$sites=array_diff($sites, ['']);
			if(count($sites)>0){
				return implode(',',$sites);
			}
		}
		return '';
	}
	
	/**
	*condition block
	*/
	public function getBaseCondition($mode=''){
		$params=[
			'parentContainer'=>'popupPropsCont',
			'form'=>'',
			'formName'=>'pfofiles_edit_block',
			'sepID'=>'__',
			'prefix'=>'condTreeLoyalty',
			'messTree'=>[
				'SELECT_CONTROL'=>Loc::getMessage("skyweb24.loyaltyprogram_CONDITION_SELECT_GROUPCONTROL"),
				'ADD_CONTROL'=>Loc::getMessage("skyweb24.loyaltyprogram_CONDITION_ADD_GROUPCONTROL"),
				'DELETE_CONTROL'=>Loc::getMessage("skyweb24.loyaltyprogram_CONDITION_DELETE_CONTROL")
			]
		];
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
	
	public function getCurrentCondition($mode=''){
		$settings=$this->getProfile();
		$params=(!empty($settings['settings']['condition']))?$settings['settings']['condition']:[];
		if(empty($params['children'])){
			$params=$this->getStartCondition();
		}
		if($mode=='json'){
			return \Bitrix\Main\Web\Json::encode($params);
		}
		return $params;
	}
	
	private function setCondNode($key, $val){
		if(isset($val['number_action']) && empty($val['number_action'])){
			$val['number_action']=(string) Loyaltyprogram\Tools::getLastAction();
		}
		\Bitrix\Main\Loader::includeModule('iblock');
		$node=[];
		$node['id']=$key;
		foreach($val as $condKey=>$condVal){
			if($condKey=='controlId'){
				$node['controlId']=$condVal;
			}elseif($condKey=='aggregator'){
				$node['values']['All']=$condVal;
				//$node['group']=true;
				$node['children']=[];
			}elseif($condKey=='value'){
				$node['values']['True']=$condVal;
				$node['values']['value']=$condVal;
			}else{
				if(is_array($condVal)){
					$condVal=array_values(array_diff($condVal, ['']));
				}
				if(!empty($condVal)){
					if($node['controlId']=='productBasket' && (int) $condVal>0){
						$tmp_label=\CIBlockElement::GetList(array(),array('ID'=>$condVal),false,false,array('NAME'));
						if($tmp_label=$tmp_label->Fetch()){
							$node['labels']['product_basket'][0]=$tmp_label['NAME'];
						}
					}elseif($node['controlId']=='product' && (int) $condVal>0){
						$tmp_label=\CIBlockElement::GetList(array(),array('ID'=>$condVal),false,false,array('NAME'));
						if($tmp_label=$tmp_label->Fetch()){
							$node['labels']['product'][0]=$tmp_label['NAME'];
						}
					}
					elseif($node['controlId']=='sectionBasket' && (int) $condVal>0){
						$tmp_label=\CIBlockSection::GetList(array(),array('ID'=>(int) $condVal),false,array('NAME'));
						if($tmp_label=$tmp_label->Fetch()){
							$node['labels']['section_basket'][0]=$tmp_label['NAME'];
						}
					}
					elseif($node['controlId']=='productCat' && (int) $condVal>0){
						$tmp_label=\CIBlockSection::GetList(array(),array('ID'=>(int) $condVal),false,array('NAME'));
						if($tmp_label=$tmp_label->Fetch()){
							$node['labels']['product_cat'][0]=$tmp_label['NAME'];
						}
					}elseif($node['controlId']=='dateRegister' && $condKey=='date_register'){
					    $tmstmp=new \Bitrix\Main\Type\Date($condVal);
                        $node['timestamp']=$tmstmp->getTimestamp();
                    }
				}
				$node['values'][$condKey]=$condVal;
			}
		}
		return $node;
	}
	
	public function getTreeFromRequest($nameCond='condTreeLoyalty'){
		$cond=[];
		if(!empty($_REQUEST[$nameCond])){
			$tmpCond=$_REQUEST[$nameCond];
			foreach($tmpCond as $key=>$val){
				$keys=explode('__', $key);
				switch (count($keys)){
					case 1:
						$cond=$this->setCondNode($keys[0], $val);
						break;
					case 2:
						$cond['children'][]=$this->setCondNode($keys[1], $val);
						$levelCond_2=&$cond['children'][count($cond['children'])-1];
						//fix error number count
						$cCount=(!empty($cond['children']) && count($cond['children'])>0)?(count($cond['children'])-1):0;
						$levelCond_2['id']=$cCount;
						//e. o. fix error number count
						break;
					case 3:
						$levelCond_2['children'][]=$this->setCondNode($keys[2], $val);
						$levelCond_3=&$levelCond_2['children'][count($levelCond_2['children'])-1];
						//fix error number count
						$cCount=(!empty($levelCond_2['children']) && count($levelCond_2['children'])>0)?(count($levelCond_2['children'])-1):0;
						$levelCond_3['id']=$cCount;
						//e. o. fix error number count
						break;
					case 4:
						$levelCond_3['children'][]=$this->setCondNode($keys[3], $val);
						$levelCond_4=&$levelCond_3['children'][count($levelCond_3['children'])-1];
						break;
					case 5:
						$levelCond_4['children'][]=$this->setCondNode($keys[4], $val);
						break;
				}
			}
		}
		return $cond;
	}
	
	protected function checkConditionChildren($logic, $currentVal, $settingVal){
		$isVal=false;
		if(
			$logic=='more' && $currentVal>=$settingVal ||
			$logic=='less' && $currentVal<$settingVal
		){
			return true;
		}
		if(
			$logic=='more' && $currentVal<$settingVal ||
			$logic=='less' && $currentVal>=$settingVal
		){
			return false;
		}
		if(
			(is_array($settingVal) && !is_array($currentVal) && in_array($currentVal, $settingVal)) ||
			(is_array($currentVal) && !is_array($settingVal) && in_array($settingVal, $currentVal)) ||
			(is_array($settingVal) && is_array($currentVal) && count(array_intersect($currentVal, $settingVal))>0) ||
			($settingVal==$currentVal && !is_array($currentVal))
		){
			$isVal=true;
		}
		if(
			($logic=='Equal' && $isVal) ||
			($logic=='Not' && !$isVal)
		){
			return true;
		}else{
			return false;
		}
	}
	
	public function getRankCoeff($user_id){
		if(!empty($this->profileSetting['type']) && !empty($user_id)){
			global $DB;
			$profile_type=$this->profileSetting['type'];
			$results=$DB->Query('select * from skyweb24_loyal_rank_users, skyweb24_loyal_ranks where
				skyweb24_loyal_ranks.id=skyweb24_loyal_rank_users.rank_id
				and skyweb24_loyal_rank_users.user_id='.$user_id.'
				and skyweb24_loyal_rank_users.active="Y"
				and skyweb24_loyal_ranks.active="Y";'
			);
			if($row = $results->Fetch()){
				$profiles=unserialize($row['profiles']);
				if(in_array($profile_type, $profiles)){
					return $row['coeff'];
				}
			}
		}
		return 1;
	}
	
}

?>