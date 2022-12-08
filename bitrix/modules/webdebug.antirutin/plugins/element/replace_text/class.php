<?
namespace WD\Antirutin\Plugins\Element;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\ValueItem,
	\WD\Antirutin\PluginElement;

class ReplaceText extends PluginElement {
	
	protected $arFieldsFilter = [
		'FIELDS' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
		'PROPERTIES' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
		'SEO' => true,
	];
	
	// *******************************************************************************************************************
	// Main methods
	// *******************************************************************************************************************
	
	/**
	 *	Execute!
	 */
	public function processElement($intElementId){
		if($this->isEmpty('field')){
			$this->setError(static::getMessage('ERROR_NO_FIELD'));
			return false;
		}
		$bResult = false;
		switch($this->get('mode')){
			case 'simple':
				$bResult = $this->replaceSimple($intElementId);
				break;
			case 'reg_exp':
				$bResult = $this->replaceRegExp($intElementId);
				break;
			case 'append':
				$bResult = $this->replaceAppend($intElementId);
				break;
			case 'prepend':
				$bResult = $this->replacePrepend($intElementId);
				break;
		}
		return $bResult;
	}
	
	/**
	 *	Get field/property values
	 */
	protected function getValues($intElementId){
		$arResult = [];
		$strField = $this->get('field');
		if($this->isField($strField)){
			$arFeatures = ['FIELDS' => [$strField]];
			$arElement = IBlock::getElementArray($intElementId, $this->intIBlockId, $arFeatures, true);
			$arResult[] = [
				'FIELD' => $strField,
				'VALUE' => $arElement[$strField],
			];
		}
		elseif($intPropertyId = $this->isProperty($strField)){
			$arFeatures = ['PROPERTY_ID' => [$intPropertyId], 'EMPTY_PROPERTIES' => true];
			$arElement = IBlock::getElementArray($intElementId, $this->intIBlockId, $arFeatures, true);
			$arProperty = $arElement['PROPERTIES'][$intPropertyId];
			if($arProperty['MULTIPLE'] == 'Y' && is_array($arProperty['~VALUE'])){
				foreach($arProperty['~VALUE'] as $key => $value){
					$arResult[] = [
						'FIELD' => $strField,
						'FIELD_ARRAY' => $arProperty,
						'VALUE' => $value,
						'DESCRIPTION' => $arProperty['DESCRIPTION'][$key],
					];
				}
			}
			else{
				$arResult[] = [
					'FIELD' => $strField,
					'FIELD_ARRAY' => $arProperty,
					'VALUE' => $arProperty['~VALUE'],
					'DESCRIPTION' => $arProperty['DESCRIPTION'],
				];
			}
		}
		elseif($strSeoField = $this->isSeoField($strField)){
			$arFeatures = ['SEO' => true];
			$arElement = IBlock::getElementArray($intElementId, $this->intIBlockId, $arFeatures, true);
			$arResult[] = [
				'FIELD' => $strField,
				'VALUE' => $arElement['SEO'][$strSeoField],
			];
		}
		foreach($arResult as $key => $arValue){
			$arResult[$key] = new ValueItem($arValue);
		}
		return $arResult;
	}
	
	/**
	 *	
	 */
	protected function replaceSimple($intElementId){
		$strSearch = $this->get('simple_search');
		$strReplace = $this->get('simple_replace');
		$bCaseSensitive = $this->get('simple_case_sensitive') == 'Y';
		if(!strlen($strSearch)){
			$this->setError(static::getMessage('ERROR_NO_SIMPLE_SEARCH'));
			return false;
		}
		$arValues = $this->getValues($intElementId);
		foreach($arValues as $key => $obValue){
			$mValue = $obValue->getValue();
			$bHtmlValue = is_array($mValue) && count($mValue) == 2 && isset($mValue['TEXT']) && isset($mValue['TYPE']);
			if($bHtmlValue){
				if($bCaseSensitive){
					$mValue['TEXT'] = str_replace($strSearch, $strReplace, $mValue['TEXT']);
				}
				else{
					$mValue['TEXT'] = str_ireplace($strSearch, $strReplace, $mValue['TEXT']);
				}
			}
			else{
				if($bCaseSensitive){
					$mValue = str_replace($strSearch, $strReplace, $mValue);
				}
				else{
					$mValue = str_ireplace($strSearch, $strReplace, $mValue);
				}
			}
			$obValue->setValue($mValue);
		}
		$this->saveValues($intElementId, $arValues);
		return true;
	}
	
	/**
	 *	
	 */
	protected function replaceRegExp($intElementId){
		$strSearch = $this->get('reg_exp_search');
		$strReplace = $this->get('reg_exp_replace');
		$intLimit = intVal($this->get('reg_exp_limit'));
		if(!is_numeric($intLimit) || $intLimit <= 0){
			$intLimit = -1;
		}
		if(!strlen($strSearch)){
			$this->setError(static::getMessage('ERROR_NO_REG_EXP_SEARCH'));
			return false;
		}
		$arValues = $this->getValues($intElementId);
		foreach($arValues as $key => $obValue){
			$mValue = $obValue->getValue();
			$bHtmlValue = is_array($mValue) && count($mValue) == 2 && isset($mValue['TEXT']) && isset($mValue['TYPE']);
			if($bHtmlValue){
				$mValue['TEXT'] = preg_replace($strSearch, $strReplace, $mValue['TEXT'], $intLimit);
			}
			else{
				$mValue = preg_replace($strSearch, $strReplace, $mValue, $intLimit);
			}
			$obValue->setValue($mValue);
		}
		$this->saveValues($intElementId, $arValues);
		return true;
	}
	
	/**
	 *	
	 */
	protected function replaceAppend($intElementId){
		$strText = $this->get('append_text');
		if(!strlen($strText)){
			$this->setError(static::getMessage('ERROR_NO_APPEND_TEXT'));
			return false;
		}
		$arValues = $this->getValues($intElementId);
		foreach($arValues as $key => $obValue){
			$mValue = $obValue->getValue();
			$bHtmlValue = is_array($mValue) && count($mValue) == 2 && isset($mValue['TEXT']) && isset($mValue['TYPE']);
			if($bHtmlValue){
				$mValue['TEXT'] = $mValue['TEXT'].$strText;
			}
			else{
				$mValue = $mValue.$strText;
			}
			$obValue->setValue($mValue);
		}
		$this->saveValues($intElementId, $arValues);
		return true;
	}
	
	/**
	 *	
	 */
	protected function replacePrepend($intElementId){
		$strText = $this->get('prepend_text');
		if(!strlen($strText)){
			$this->setError(static::getMessage('ERROR_NO_PREPEND_TEXT'));
			return false;
		}
		$arValues = $this->getValues($intElementId);
		foreach($arValues as $key => $obValue){
			$mValue = $obValue->getValue();
			$bHtmlValue = is_array($mValue) && count($mValue) == 2 && isset($mValue['TEXT']) && isset($mValue['TYPE']);
			if($bHtmlValue){
				$mValue['TEXT'] = $strText.$mValue['TEXT'];
			}
			else{
				$mValue = $strText.$mValue;
			}
			$obValue->setValue($mValue);
		}
		$this->saveValues($intElementId, $arValues);
		return true;
	}
	
	/**
	 *	Save values for element
	 */
	protected function saveValues($intElementId, $arValues){
		$bResult = false;
		$strField = $this->get('field');
		if(!empty($arValues)){
			if($this->isField($strField)){
				$obFirst = $this->cutMultipleValue($arValues, static::MULTIPLE_MODE_FIRST);
				if(is_object($obFirst)){
					$bResult = $this->update($intElementId, [$strField => $obFirst->getValue()]);
				}
			}
			elseif($intPropertyId = $this->isProperty($strField)){
				$obFirst = $this->cutMultipleValue($arValues, static::MULTIPLE_MODE_FIRST);
				if(is_object($obFirst)){
					$arProperty = $obFirst->getFieldArray();
					$arValues = $this->transformObjectToValue($arValues, $arProperty['MULTIPLE'] == 'Y', false,
						$arProperty['WITH_DESCRIPTION'] == 'Y');
					$this->setPropertyValue($intElementId, $intPropertyId, $arValues);
					$bResult = true;
				}
			}
			elseif($strSeoField = $this->isSeoField($strField)){
				$obFirst = $this->cutMultipleValue($arValues, static::MULTIPLE_MODE_FIRST);
				if(is_object($obFirst)){
					$strSeoFieldFull = IBlock::$arSeoMapElement[$strSeoField];
					$this->setSeoField($intElementId, $strSeoFieldFull, $obFirst->getValue());
				}
			}
		}
		return $bResult;
	}
	
}

?>