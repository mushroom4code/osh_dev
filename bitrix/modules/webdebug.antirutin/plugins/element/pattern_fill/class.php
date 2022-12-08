<?
namespace WD\Antirutin\Plugins\Element;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\ValueItem,
	\WD\Antirutin\PluginElement;

class PatternFill extends PluginElement {
	
	const MACRO_ID = '{=this.Id}';
	
	protected $arFieldsFilter = [
		'FIELDS' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
		'PROPERTIES' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
		#
		'OFFERS.FIELDS' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
		'OFFERS.PROPERTIES' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
	];
	
	protected $arJs = [
		'/bitrix/js/iblock/iblock_edit.js',
	];
	
	// *******************************************************************************************************************
	// Main methods
	// *******************************************************************************************************************
	
	/**
	 *	Execute!
	 */
	public function processElement($intElementId){
		$strField = $this->get('field');
		if(Helper::isEmpty($strField)){
			$this->setError(static::getMessage('ERROR_NO_FIELD'));
			return false;
		}
		if($strOfferField = $this->isOfferField($strField)){
			$arOffers = $this->getElementOffers($intElementId);
			if(!empty($arOffers)){
				$intIBlockOffersId = $this->getOffersIBlockId();
				foreach($arOffers as $intOfferId){
					$strValue = $this->processPattern($intOfferId, $intIBlockOffersId);
					$bResult = $this->saveValues($intOfferId, $intIBlockOffersId, $strOfferField, $strValue);
				}
				$bResult = true;
			}
		}
		else{
			$strValue = $this->processPattern($intElementId);
			$bResult = $this->saveValues($intElementId, $this->intIBlockId, $strField, $strValue);
		}
		return $bResult;
	}
	
	/**
	 *	Process pattern
	 */
	protected function processPattern($intElementId, $intIBlockId=null){
		$intIBlockId = $intIBlockId ? $intIBlockId : $this->intIBlockId;
		$arSelect = ['IBLOCK_SECTION_ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_TEXT'];
		$arElement = IBlock::getElementArray($intElementId, $intIBlockId, ['FIELDS' => $arSelect]);
		$arFields = [
			'IBLOCK_ID' => $intIBlockId,
			'IBLOCK_SECTION_ID' => $arElement['IBLOCK_SECTION_ID'],
			'NAME' => $arElement['~NAME'],
			'CODE' => $arElement['~CODE'],
			'PREVIEW_TEXT' => $arElement['~PREVIEW_TEXT'],
			'DETAIL_TEXT' => $arElement['~DETAIL_TEXT'],
		];
		$strPattern = $this->get('pattern');
		$this->replaceId($intElementId, $strPattern);
		$obIPropTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($intIBlockId, $intElementId);
		$obValues = $obIPropTemplates->getValuesEntity();
		$obEntity = $obValues->createTemplateEntity();
		$obEntity->setFields($arFields);
		$templates = $obIPropTemplates->findTemplates();
		$strResult = \Bitrix\Iblock\Template\Engine::process($obEntity, $strPattern);
		unset($obIPropTemplates, $obValues, $obEntity, $arFields, $arElement);
		return $strResult;
	}
	
	/**
	 *	Replace ID macro
	 */
	protected function replaceId($intElementId, &$strPattern){
		$strPattern = str_replace(static::MACRO_ID, $intElementId, $strPattern);
	}
	
	/**
	 *	Save values for element
	 */
	protected function saveValues($intElementId, $intIBlockId, $strField, $strValue){
		$bResult = false;
		if($this->isField($strField)){
			$bResult = $this->update($intElementId, [$strField => $strValue]);
		}
		elseif($intPropertyId = $this->isProperty($strField)){
			$arProperty = IBlock::getPropertyById($intPropertyId, $intIBlockId);
			if($arProperty['PROPERTY_TYPE'] == 'S' && $arProperty['USER_TYPE'] == 'HTML'){
				$strValue = ['VALUE' => ['TYPE' => 'text', 'TEXT' => $strValue]];
			}
			$this->setPropertyValue($intElementId, $intPropertyId, $strValue, null, $intIBlockId);
			$bResult = true;
		}
		return $bResult;
	}
	
	/**
	 *	Get html for <select>
	 */
	protected function getMacrosForSelect($intIBlockId=null){
		$strObjectName = 'window.wdaInheritedPropertiesTemplates';
		$strObjectFunc = 'insertIntoInheritedPropertiesTemplate';
		$intIBlockId = $intIBlockId ? $intIBlockId : $this->intIBlockId;
		$arMacros = \CIBlockParameters::GetInheritedPropertyTemplateElementMenuItems($intIBlockId,
			$strObjectName.'.'.$strObjectFunc, '', $this->getTextareaId());
		foreach($arMacros as $key1 => $arGroup){
			foreach($arGroup['MENU'] as $key2 => $arItem){
				break;
			}
			if(is_array($arItem)){
				$arItem['TEXT'] = 'ID';
				$arItem['ONCLICK'] = preg_replace('#({.*?})#', static::MACRO_ID, $arItem['ONCLICK'], 1);
				$arMacros[$key1]['MENU'] = array_merge([$arItem], $arGroup['MENU']);
				break;
			}
		}
		$strResult = '<option value="">---</option>';
		foreach($arMacros as $arGroup){
			if(!empty($arGroup['MENU'])){
				$strResult .= sprintf('<optgroup label="%s" data-code="%s">', htmlspecialcharsbx($arGroup['TEXT']), $strGroup);
					foreach($arGroup['MENU'] as $arItem){
						$strResult .= sprintf('<option value="%s">%s</option>', htmlspecialcharsbx($arItem['ONCLICK']),
							$arItem['TEXT']);
					}
				$strResult .= '</optgroup>';
			}
		}
		return $strResult;
	}
	
	/**
	 *	Get id for textarea
	 */
	protected function getTextareaId(){
		return 'pattern_'.$this->getId();
	}
	
	/**
	 *	AJAX: Load html macros for select
	 */
	protected function loadMacrosSelect(&$arJson){
		$strField = $this->arPost['field'];
		$intIBlockId = $this->intIBlockId;
		if($strOfferField = $this->isOfferField($strField)){
			$intIBlockId = $this->getOffersIBlockId();
		}
		return $this->getMacrosForSelect($intIBlockId);
	}
	
}

?>