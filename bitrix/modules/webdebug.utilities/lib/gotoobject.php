<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper,
	\WD\Utilities\HttpHeader,
	\WD\Utilities\JsHelper;

Helper::loadMessages();

/**
 * Class GotoObject
 * @package WD\Utilities
 */
class GotoObject {
	
	const MAX_COUNT = 10;
	
	public static function addJs(){
		global $APPLICATION;
		\CJSCore::init('window');
		$FileName = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.WDU_MODULE.'/goto.js';
		if (is_file($FileName)) {
			$JS = file_get_contents($FileName);
			$JS = 
'BX.message({
	"WDU_POPUP_GOTO_TITLE":"'.GetMessage('WDU_POPUP_GOTO_TITLE').'",
	"WDU_POPUP_GOTO_BUTTON_OK":"'.GetMessage('WDU_POPUP_GOTO_BUTTON_OK').'",
	"WDU_POPUP_GOTO_BUTTON_CANCEL":"'.GetMessage('WDU_POPUP_GOTO_BUTTON_CANCEL').'",
	"WDU_POPUP_GOTO_LOADING":"'.GetMessage('WDU_POPUP_GOTO_LOADING').'"
});'
				.$JS;
			$APPLICATION->addHeadString('<script>'.$JS.'</script>');
		}
	}
	
	public static function getTypes(){
		$arResult = array(
			'ELEMENT' => array(
				'NAME' => GetMessage('WDU_POPUP_ELEMENT'),
				'ICON' => 'element',
				'CALLBACK_EXISTS' => function(){
					return IsModuleInstalled('iblock');
				},
				'CALLBACK_SEARCH' => array(__CLASS__, 'callbackElement'),
			),
			'SECTION' => array(
				'NAME' => GetMessage('WDU_POPUP_SECTION'),
				'ICON' => 'section',
				'CALLBACK_EXISTS' => function(){
					return IsModuleInstalled('iblock');
				},
				'CALLBACK_SEARCH' => array(__CLASS__, 'callbackSection'),
				'VARIANTS' => array(
					'VIEW' => GetMessage('WDU_POPUP_ENTITY_VIEW'),
					'EDIT' => GetMessage('WDU_POPUP_ENTITY_EDIT'),
				),
				'DEFAULT_VARIANT' => 'EDIT',
			),
			'IBLOCK' => array(
				'NAME' => GetMessage('WDU_POPUP_IBLOCK'),
				'ICON' => 'iblock',
				'CALLBACK_EXISTS' => function(){
					return IsModuleInstalled('iblock');
				},
				'CALLBACK_SEARCH' => array(__CLASS__, 'callbackIBlock'),
				'VARIANTS' => array(
					'VIEW' => GetMessage('WDU_POPUP_ENTITY_VIEW'),
					'EDIT' => GetMessage('WDU_POPUP_ENTITY_EDIT'),
				),
				'DEFAULT_VARIANT' => 'VIEW',
			),
			'IBLOCK_TYPE' => array(
				'NAME' => GetMessage('WDU_POPUP_IBLOCK_TYPE'),
				'ICON' => 'iblock-type',
				'CALLBACK_EXISTS' => function(){
					return IsModuleInstalled('iblock');
				},
				'CALLBACK_SEARCH' => array(__CLASS__, 'callbackIBlockType'),
				'VARIANTS' => array(
					'VIEW' => GetMessage('WDU_POPUP_ENTITY_VIEW'),
					'EDIT' => GetMessage('WDU_POPUP_ENTITY_EDIT'),
				),
				'DEFAULT_VARIANT' => 'EDIT',
			),
			'USER' => array(
				'NAME' => GetMessage('WDU_POPUP_USER'),
				'ICON' => 'user',
				'CALLBACK_SEARCH' => array(__CLASS__, 'callbackUser'),
			),
			'ORDER' => array(
				'NAME' => GetMessage('WDU_POPUP_ORDER'),
				'ICON' => 'order',
				'CALLBACK_EXISTS' => function(){
					return IsModuleInstalled('sale');
				},
				'CALLBACK_SEARCH' => array(__CLASS__, 'callbackOrder'),
				'VARIANTS' => array(
					'VIEW' => GetMessage('WDU_POPUP_ENTITY_VIEW'),
					'EDIT' => GetMessage('WDU_POPUP_ENTITY_EDIT'),
				),
				'DEFAULT_VARIANT' => 'VIEW',
			),
		);
		foreach($arResult as $key => $arItem){
			if(isset($arItem['CALLBACK_EXISTS']) && call_user_func($arItem['CALLBACK_EXISTS'])===false){
				unset($arResult[$key]);
			}
		}
		return $arResult;
	}
	
	public static function search($strType, $strID, $arParams=array()){
		$arResult = array();
		$arTypes = static::getTypes();
		$arParams = is_array($arParams) ? $arParams : array();
		$arType = $arTypes[$strType];
		if(is_array($arType)){
			$arResult = call_user_func($arType['CALLBACK_SEARCH'], $strID, $arParams);
		}
		return $arResult;
	}
	
	protected static function getIBlockSectionsMode($intIBlockID){
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resIBlock = \CIBlock::getList(array(),array('ID'=>$intIBlockID));
			if($arIBlock = $resIBlock->getNext(false, false)){
				if(in_array($arIBlock['LIST_MODE'], array('C', 'S'))){
					return $arIBlock['LIST_MODE'];
				}
				else {
					return \COption::getOptionString('iblock', 'combined_list_mode') == 'Y' ? 'C' : 'S';
				}
			}
		}
		return false;
	}
	
	protected static function getIBlockTypeName($strIBlockTypeID){
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$arIBlockType = \CIBlockType::GetByIDLang($strIBlockTypeID, LANGUAGE_ID);
			return $arIBlockType['NAME'];
		}
		return false;
	}
	
	protected static function getIBlockName($intIBlockID){
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resIBlock = \CIBlock::getList(array(),array('ID'=>$intIBlockID));
			if($arIBlock = $resIBlock->getNext(false, false)){
				return $arIBlock['NAME'];
			}
		}
		return false;
	}
	
	protected static function getSectionName($intSectionID){
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resSection = \CIBlockSection::getList(array(),array('ID'=>$intSectionID),false,array('NAME'));
			if($arSection = $resSection->getNext(false, false)){
				return $arSection['NAME'];
			}
		}
		return false;
	}
	
	public static function callbackElement($strID, $arParams){
		$arResult = array();
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resElement = \CIBlockElement::getList(array(), array('ID'=>$strID), false, false, array('ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'IBLOCK_SECTION_ID', 'NAME', 'CODE'));
			if($arElement = $resElement->getNext()){
				$arResult[] = self::getElementWithUrlAndInfo($arElement, $arParams);
			}
			else{
				$resElement = \CIBlockElement::getList(array('CODE'=>'ASC'), array('=CODE'=>$strID), false, array('nTopCount' => self::MAX_COUNT), array('ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'IBLOCK_SECTION_ID', 'NAME', 'CODE'));
				while($arElement = $resElement->getNext()){
					$arResult[] = self::getElementWithUrlAndInfo($arElement, $arParams);
				}
			}
		}
		return $arResult;
	}
	public static function getElementWithUrlAndInfo($arElement, $arParams){
		$arElement['_URL'] = '/bitrix/admin/iblock_element_edit.php';
		$arElement['_URL'] .= sprintf('?IBLOCK_ID=%d&type=%s&ID=%d&lang=%s&find_section_section=%d&WF=Y',
			$arElement['IBLOCK_ID'], $arElement['IBLOCK_TYPE_ID'], $arElement['ID'], LANGUAGE_ID, IntVal($arElement['IBLOCK_SECTION_ID']));
		$arElement['_INFO'] = array();
		$arElement['_INFO']['ID'] = $arElement['ID'];
		if(strlen($arElement['CODE'])){
			$arElement['_INFO'][GetMessage('WDU_POPUP_ENTITY_CODE')] = $arElement['CODE'];
		}
		$arElement['_INFO'][GetMessage('WDU_POPUP_ENTITY_IBLOCK_ID')] = '['.$arElement['IBLOCK_ID'].'] '.self::getIBlockName($arElement['IBLOCK_ID']);
		if(strlen($arElement['IBLOCK_SECTION_ID'])){
			$arElement['_INFO'][GetMessage('WDU_POPUP_ENTITY_SECTION_ID')] = '['.$arElement['IBLOCK_SECTION_ID'].'] '.self::getSectionName($arElement['IBLOCK_SECTION_ID']);
		}
		return $arElement;
	}
	
	public static function callbackSection($strID, $arParams){
		$arResult = array();
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resSection = \CIBlockSection::getList(array(), array('ID'=>$strID), false, array('ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'IBLOCK_SECTION_ID', 'NAME', 'CODE'), false);
			if($arSection = $resSection->getNext()){
				$arResult[] = self::getSectionWithUrlAndInfo($arSection, $arParams);
			}
			else{
				$resSection = \CIBlockSection::getList(array('CODE'=>'ASC'), array('=CODE'=>$strID), false, array('ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'IBLOCK_SECTION_ID', 'NAME', 'CODE'), array('nTopCount' => self::MAX_COUNT));
				while($arSection = $resSection->getNext()){
					$arResult[] = self::getSectionWithUrlAndInfo($arSection, $arParams);
				}
			}
		}
		return $arResult;
	}
	public static function getSectionWithUrlAndInfo($arSection, $arParams){
		if($arParams['VARIANT']=='EDIT') {
			$arSection['_URL'] = '/bitrix/admin/iblock_section_edit.php';
			$arSection['_URL'] .= sprintf('?IBLOCK_ID=%d&type=%s&ID=%d&lang=%s&find_section_section=%d',
				$arSection['IBLOCK_ID'], $arSection['IBLOCK_TYPE_ID'], $arSection['ID'], LANGUAGE_ID, IntVal($arSection['IBLOCK_SECTION_ID']));
		}
		else {
			if(self::getIBlockSectionsMode($arSection['IBLOCK_ID'])=='C'){
				$arSection['_URL'] = '/bitrix/admin/iblock_list_admin.php';
				$arSection['IBLOCK_SECTION_ID'] = $arSection['ID'];
			}
			else{
				$arSection['_URL'] = '/bitrix/admin/iblock_section_admin.php';
				$arSection['IBLOCK_SECTION_ID'] = $arSection['ID'];
			}
			$arSection['_URL'] .= sprintf('?IBLOCK_ID=%d&type=%s&lang=%s&find_section_section=%d&SECTION_ID=%d',
				$arSection['IBLOCK_ID'], $arSection['IBLOCK_TYPE_ID'], LANGUAGE_ID, IntVal($arSection['IBLOCK_SECTION_ID']), $arSection['ID']);
		}
		$arSection['_INFO'] = array();
		$arSection['_INFO']['ID'] = $arSection['ID'];
		if(strlen($arSection['CODE'])){
			$arSection['_INFO'][GetMessage('WDU_POPUP_ENTITY_CODE')] = $arSection['CODE'];
		}
		$arSection['_INFO'][GetMessage('WDU_POPUP_ENTITY_IBLOCK_ID')] = '['.$arSection['IBLOCK_ID'].'] '.self::getIBlockName($arSection['IBLOCK_ID']);
		if(strlen($arSection['IBLOCK_SECTION_ID'])){
			$arSection['_INFO'][GetMessage('WDU_POPUP_ENTITY_SECTION_ID')] = '['.$arSection['IBLOCK_SECTION_ID'].'] '.self::getSectionName($arSection['IBLOCK_SECTION_ID']);
		}
		return $arSection;
	}
	
	public static function callbackIBlock($strID, $arParams){
		$arResult = array();
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resIBlock = \CIBlock::getList(array(), array('ID'=>$strID));
			if($arIBlock = $resIBlock->getNext()){
				$arResult[] = self::getIBlockWithUrlAndInfo($arIBlock, $arParams);
			}
			else{
				$resIBlock = \CIBlock::getList(array('CODE'=>'ASC'), array('CODE'=>$strID));
				while($arIBlock = $resIBlock->getNext()){
					$arResult[] = self::getIBlockWithUrlAndInfo($arIBlock, $arParams);
					if(count($arResult) == self::MAX_COUNT){
						break;
					}
				}
			}
		}
		return $arResult;
	}
	public static function getIBlockWithUrlAndInfo($arIBlock, $arParams){
		if($arParams['VARIANT']=='EDIT') {
			$arIBlock['_URL'] = '/bitrix/admin/iblock_edit.php';
			$arIBlock['_URL'] .= sprintf('?ID=%d&type=%s&lang=%s&admin=Y',
				$arIBlock['ID'], $arIBlock['IBLOCK_TYPE_ID'], LANGUAGE_ID);
		}
		else {
			if(self::getIBlockSectionsMode($arIBlock['ID'])=='C'){
				$arIBlock['_URL'] = '/bitrix/admin/iblock_list_admin.php';
			}
			else{
				$arIBlock['_URL'] = '/bitrix/admin/iblock_section_admin.php';
			}
			$arIBlock['_URL'] .= sprintf('?IBLOCK_ID=%d&type=%s&lang=%s&find_section_section=0',
				$arIBlock['ID'], $arIBlock['IBLOCK_TYPE_ID'], LANGUAGE_ID);
		}
		$arIBlock['_INFO'] = array();
		$arIBlock['_INFO']['ID'] = $arIBlock['ID'];
		if(strlen($arIBlock['CODE'])){
			$arIBlock['_INFO'][GetMessage('WDU_POPUP_ENTITY_CODE')] = $arIBlock['CODE'];
		}
		$arIBlock['_INFO'][GetMessage('WDU_POPUP_ENTITY_IBLOCK_TYPE_ID')] = '['.$arIBlock['IBLOCK_TYPE_ID'].'] '.self::getIBlockTypeName($arIBlock['IBLOCK_TYPE_ID']);
		return $arIBlock;
	}
	
	public static function callbackIBlockType($strID, $arParams){
		$arResult = array();
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$resIBlock = \CIBlockType::getList(array(), array('=ID'=>$strID));
			if($arIBlockType = $resIBlock->getNext()){
				$arIBlockType = \CIBlockType::GetByIDLang($arIBlockType['ID'], LANGUAGE_ID);
				$arResult[] = self::getIBlockTypeWithUrlAndInfo($arIBlockType, $arParams);
			}
		}
		return $arResult;
	}
	public static function getIBlockTypeWithUrlAndInfo($arIBlockType, $arParams){
		if($arParams['VARIANT']=='EDIT') {
			$arIBlockType['_URL'] = '/bitrix/admin/iblock_type_edit.php';
			$arIBlockType['_URL'] .= sprintf('?lang=%s&ID=%s',
				LANGUAGE_ID, $arIBlockType['IBLOCK_TYPE_ID']);
		}
		else {
			$arIBlockType['_URL'] = '/bitrix/admin/iblock_admin.php';
			$arIBlockType['_URL'] .= sprintf('?type=%s&lang=%s&admin=Y',
				$arIBlockType['IBLOCK_TYPE_ID'], LANGUAGE_ID);
		}
		$arIBlockType['_INFO'] = array();
		$arIBlockType['_INFO']['ID'] = $arIBlockType['IBLOCK_TYPE_ID'];
		if(strlen($arIBlockType['CODE'])){
			$arIBlockType['_INFO'][GetMessage('WDU_POPUP_ENTITY_CODE')] = $arIBlockType['CODE'];
		}
		return $arIBlockType;
	}
	
	public static function callbackUser($strID, $arParams){
		$arResult = array();
		$resUser = \CUser::getList($sortBy='ID', $sortOrder='ASC', array('ID'=>$strID));
		if($arUser = $resUser->getNext()){
			$arResult[] = self::getUserWithUrlAndInfo($arUser, $arParams);
		}
		else {
			$resUser = \CUser::getList($sortBy='LOGIN', $sortOrder='ASC', array('LOGIN_EQUAL_EXACT'=>$strID), array('NAV_PARAMS' => array('nTopCount' => self::MAX_COUNT)));
			while($arUser = $resUser->getNext()){
				$arResult[] = self::getUserWithUrlAndInfo($arUser, $arParams);
			}
		}
		return $arResult;
	}
	public static function getUserWithUrlAndInfo($arUser, $arParams){
		$strName = trim($arUser['LAST_NAME'].' '.$arUser['NAME']);
		if(!strlen($strName)){
			$strName = $arUser['LOGIN'];
		}
		$arUser['NAME'] = $strName;
		$arUser['_URL'] = '/bitrix/admin/user_edit.php';
		$arUser['_URL'] .= sprintf('?lang=%s&ID=%d',
			LANGUAGE_ID, $arUser['ID']);
		$arUser['_INFO'] = array();
		$arUser['_INFO']['ID'] = $arUser['ID'];
		$arUser['_INFO'][GetMessage('WDU_POPUP_USER_LOGIN')] = $arUser['LOGIN'];
		$arUser['_INFO'][GetMessage('WDU_POPUP_USER_EMAIL')] = $arUser['EMAIL'];
		return $arUser;
	}
	
	public static function callbackOrder($strID, $arParams){
		$arResult = array();
		if(\Bitrix\Main\Loader::includeModule('sale')){
			$resOrder = \CSaleOrder::getList(array(), array('ID'=>$strID));
			if($arOrder = $resOrder->getNext()){
				$arResult[] = self::getOrderWithUrlAndInfo($arOrder, $arParams);
			}
			if(class_exists('\Bitrix\Main\Numerator\Numerator') && class_exists('\Bitrix\Sale\Registry')){
				$numeratorsOrderType = \Bitrix\Main\Numerator\Numerator::getOneByType(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
				if($numeratorsOrderType && $numeratorsOrderType['id']){
					$resOrder = \CSaleOrder::getList(array(), array('ACCOUNT_NUMBER'=>$strID));
					if($arOrder = $resOrder->getNext()){
						$arResult[] = self::getOrderWithUrlAndInfo($arOrder, $arParams);
					}
				}
			}
		}
		return $arResult;
	}
	public static function getOrderWithUrlAndInfo($arOrder, $arParams){
		$strOrderNumber = strlen($arOrder['ACCOUNT_NUMBER']) && $arOrder['ACCOUNT_NUMBER']!=$arOrder['ID'] ? $arOrder['ACCOUNT_NUMBER'] : $arOrder['ID'];
		$arOrder['NAME'] = sprintf(GetMessage('WDU_POPUP_ORDER_NAME'), $strOrderNumber, $arOrder['DATE_INSERT']);
		if($arParams['VARIANT']=='EDIT') {
			$arOrder['_URL'] = '/bitrix/admin/sale_order_edit.php';
			$arOrder['_URL'] .= sprintf('?ID=%d&lang=%s',
				$arOrder['ID'], LANGUAGE_ID);
		}
		else{
			$arOrder['_URL'] = '/bitrix/admin/sale_order_view.php';
			$arOrder['_URL'] .= sprintf('?ID=%d&filter=Y&set_filter=Y&lang=%s',
				$arOrder['ID'], LANGUAGE_ID);
		}
		$arOrder['_INFO'] = array();
		$arOrder['_INFO']['ID'] = $arOrder['ID'];
		if(strlen($arOrder['ACCOUNT_NUMBER']) && $arOrder['ACCOUNT_NUMBER']!=$arOrder['ID'] ){
			$arOrder['_INFO'][GetMessage('WDU_POPUP_ORDER_ACCOUNT_NUMBER')] = $arOrder['ACCOUNT_NUMBER'];
		}
		if(\Bitrix\Main\Loader::includeModule('currency')){
			$arOrder['PRICE'] = \CurrencyFormat($arOrder['PRICE'], $arOrder['CURRENCY']);
		}
		$arOrder['_INFO'][GetMessage('WDU_POPUP_ORDER_PRICE')] = $arOrder['PRICE'];
		return $arOrder;
	}

}
