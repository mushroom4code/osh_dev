<?
namespace WD\Antirutin\Plugins\Element;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\PluginElement;

class DeleteListValues extends PluginElement {
	
	protected $arFieldsFilter = [
		'PROPERTIES' => ['TYPE' => 'L', 'MULTIPLE' => 'Y'],
	];
	
	// *******************************************************************************************************************
	// Main methods
	// *******************************************************************************************************************
	
	/**
	 *	Execute!
	 */
	public function processElement($intElementId){
		$bResult = false;
		$strField = $this->get('field');
		$arDeleteEnumsId = $this->get('value');
		$arDeleteEnumsId = is_array($arDeleteEnumsId) ? array_filter($arDeleteEnumsId) : $arDeleteEnumsId;
		if(!strlen($strField)){
			$this->setError(static::getMessage('ERROR_NO_FIELD'));
			return false;
		}
		if(empty($arDeleteEnumsId)){
			$this->setError(static::getMessage('ERROR_NO_VALUE'));
			return false;
		}
		$arDeleteEnumsId = is_array($arDeleteEnumsId) ? $arDeleteEnumsId : (strlen($arDeleteEnumsId) ? [$arDeleteEnumsId] : []);
		if(!empty($arDeleteEnumsId)){
			if($intPropertyId = $this->isProperty($strField)){
				$arFeatures = [
					'PROPERTY_ID' => [$intPropertyId],
					'EMPTY_PROPERTIES' => true,
				];
				$arElement = IBlock::getElementArray($intElementId, $this->intIBlockId, $arFeatures, true);
				$mValue = $arElement['PROPERTIES'][$intPropertyId]['VALUE_ENUM_ID'];
				if(!empty($mValue)){
					$mValue = is_array($mValue) ? $mValue : [$mValue];
					$mValue = array_diff($mValue, $arDeleteEnumsId);
					if(empty($mValue)){
						$mValue = false;
					}
					$bResult = $this->setPropertyValue($intElementId, $intPropertyId, $mValue);
				}
				else{
					$bResult = true;
				}
			}
			else{
				$this->setError('Wrong property');
			}
		}
		else{
			$this->setError('No value selected.');
		}
		return $bResult;
	}
	
	// *******************************************************************************************************************
	// Other methods
	// *******************************************************************************************************************
	
	/**
	 *	AJAX: Load field input
	 */
	protected function loadSelectValue(&$arJson){
		return $this->buildSelectOptions($this->arPost['field']);
	}
	
	/**
	 *	
	 */
	protected function buildSelectOptions($strField, $arSelectedValues=null){
		$strResult = '';
		$arSelectedValues = is_array($arSelectedValues) ? $arSelectedValues : [];
		if($intPropertyId = $this->isProperty($strField)){
			$strResult .= sprintf('<option value="" data-default="Y">%s</option>', static::getMessage('VALUE_DEFAULT'));
			foreach($this->getEnums($intPropertyId) as $arEnum){
				$strResult .= sprintf('<option value="%s"%s>%s</option>', $arEnum['ID'], 
					(in_array($arEnum['ID'], $arSelectedValues) ? ' selected="selected"' : ''), $arEnum['VALUE']);
			}
		}
		return $strResult;
	}
	
	/**
	 *	
	 */
	protected function getEnums($intPropertyId){
		$arResult = [];
		$arProperty = IBlock::getPropertyById($intPropertyId, $this->intIBlockId);
		if(is_array($arProperty)){
			$strType = $arProperty['PROPERTY_TYPE'].(strlen($arProperty['USER_TYPE']) ? ':'.$arProperty['USER_TYPE'] : '');
			switch($strType){
				case 'L':
					$arResult = IBlock::getEnumsL($intPropertyId, $this->intIBlockId);
					break;
			}
		}
		return $arResult;
	}
	
}

?>