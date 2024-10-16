<?use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);
if(!\Bitrix\Main\Loader::IncludeModule("skyweb24.loyaltyprogram")){
	ShowError('Module not included!');
	return;
}

class Skyweb24_OrderAjaxBonus2 extends \CBitrixComponent{
	public function onPrepareComponentParams($params){
		$params['MAX_BONUS'] = 0;
		$bonusPayClasses=\Skyweb24\Loyaltyprogram\Profiles\Profile::getActiveProfileByType('Orderpay');
		foreach($bonusPayClasses as $bonus){
			$bonusPay=\Skyweb24\Loyaltyprogram\Profiles\Profile::getProfileById($bonus);
			
			if(!empty($params['TMP_ORDER'])){
				$bonusPay->setOrder($params['TMP_ORDER']);
			}
			
			$pay=$bonusPay->getMaxBonus();
			
			if($pay>0&&$pay!==false){
				$params['MAX_BONUS']=$pay;
				break;
			}
		}
		$params['MESS_TITLE']=(empty($params['MESS_TITLE'])?Loc::getMessage('skyweb24.orderAjaxBonus_TITLE'):$params['MESS_TITLE']);
		$params['MESS_BONUS']=(empty($params['MESS_BONUS'])?Loc::getMessage('skyweb24.orderAjaxBonus_BONUS'):$params['MESS_BONUS']);
		$params['MESS_NO_BONUS']=(empty($params['MESS_NO_BONUS'])?Loc::getMessage('skyweb24.orderAjaxBonus_NO_BONUS'):$params['MESS_NO_BONUS']);
		$params['MESS_MAX']=(empty($params['MESS_MAX'])?Loc::getMessage('skyweb24.orderAjaxBonus_MAX_BONUS'):$params['MESS_MAX']);
		$params['MESS_BONUS_PAY_TOTAL']=(empty($params['MESS_BONUS_PAY_TOTAL'])?Loc::getMessage('skyweb24.orderAjaxBonus_MESS_BONUS_PAY_TOTAL'):$params['MESS_BONUS_PAY_TOTAL']);
		$params['MESS_ALL_BONUS']=(empty($params['MESS_ALL_BONUS'])?Loc::getMessage('skyweb24.orderAjaxBonus_ALL_BONUS'):$params['MESS_ALL_BONUS']);
		$params['MESS_HASBONUSADDED']=(empty($params['MESS_HASBONUSADDED'])?Loc::getMessage('skyweb24.bonus_hasbonusAdded'):$params['MESS_HASBONUSADDED']);
		return $params;
	}
	public function executeComponent(){
		if(\Bitrix\Main\Config\Option::get('skyweb24.loyaltyprogram','bonus_pay_active') == 'Y'){
			
			if(!empty($this->arParams['TMP_ARRESULT'])){
				$params['TMP_ARRESULT']['JS_DATA']['sw24_loyalty_max_bonus']=$this->arParams['MAX_BONUS'];
			}
			
			global $USER;
			$current_bonus = new \Skyweb24\Loyaltyprogram\Components();
			$basket = \Bitrix\Sale\Basket::loadItemsForFUser(
					\Bitrix\Sale\Fuser::getId(),
					\Bitrix\Main\Context::getCurrent()->getSite()
				);
			$basketItems = $basket->getBasketItems();
			$currency='RUB';
			foreach ($basketItems as $basketItem) {
				$currency=$basketItem->getField('CURRENCY');
				break;
			}

			$this->arResult['BASKET_PRICE']=$basket->getPrice();
			$this->arResult['CURRENT_BONUS'] = $current_bonus->getUserAccount($currency);
			$currencyList = CCurrency::GetList(($by="name"),($order="asc"),LANGUAGE_ID);
			$currencyes=[];
			while($currency =$currencyList->Fetch()){
				$currencyes[$currency['CURRENCY']]=$currency;
			}
			$this->arParams['currency']=$currencyes;

			CJSCore::Init(array('currency')); 
			$this->IncludeComponentTemplate($componentPage);

		}
	}
}
