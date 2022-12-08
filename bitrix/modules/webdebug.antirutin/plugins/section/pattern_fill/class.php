<?
namespace WD\Antirutin\Plugins\Section;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\ValueItem,
	\WD\Antirutin\PluginSection;

class PatternFill extends PluginSection {
	
	const MACRO_ID = '{=this.Id}';
	
	protected $arFieldsFilter = [
		'FIELDS' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
		'PROPERTIES' => ['TYPE_FULL' => ['S', 'S:HTML'], 'IS_WRITEABLE' => 'Y'],
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
	public function processSection($intSectionId){
		if($this->isEmpty('field')){
			$this->setError(static::getMessage('ERROR_NO_FIELD'));
			return false;
		}
		$strValue = $this->processPattern($intSectionId);
		$bResult = $this->saveValues($intSectionId, $strValue);
		return $bResult;
	}
	
	/**
	 *	Process pattern
	 */
	protected function processPattern($intSectionId){
		$arSelect = ['IBLOCK_SECTION_ID', 'NAME', 'CODE', 'DESCRIPTION', 'UF_*'];
		$arSection = IBlock::getSectionArray($intSectionId, $this->intIBlockId, $arSelect);
		$arFields = [
			'IBLOCK_ID' => $this->intIBlockId,
			'IBLOCK_SECTION_ID' => $arSection['IBLOCK_SECTION_ID'],
			'NAME' => $arSection['~NAME'],
			'CODE' => $arSection['~CODE'],
			'DESCRIPTION' => $arSection['~DESCRIPTION'],
		];
		if(is_array($arSection['PROPERTIES'])){
			foreach($arSection['PROPERTIES'] as $strField => $strValue){
				if($this->isSectionProperty($strField)){
					$arFields[$strField] = $arSection['PROPERTIES']['~'.$strField];
				}
			}
		}
		$strPattern = $this->get('pattern');
		$this->replaceId($intSectionId, $strPattern);
		$obIPropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($this->intIBlockId, $intSectionId);
		$obValues = $obIPropTemplates->getValuesEntity();
		$obEntity = $obValues->createTemplateEntity();
		$obEntity->setFields($arFields);
		$templates = $obIPropTemplates->findTemplates();
		$strResult = \Bitrix\Iblock\Template\Engine::process($obEntity, $strPattern);
		unset($obIPropTemplates, $obValues, $obEntity, $arFields, $arSection);
		return $strResult;
	}
	
	/**
	 *	Replace ID macro
	 */
	protected function replaceId($intSectionId, &$strPattern){
		$strPattern = str_replace(static::MACRO_ID, $intSectionId, $strPattern);
	}
	
	/**
	 *	Save values for Section
	 */
	protected function saveValues($intSectionId, $strValue){
		$bResult = false;
		$strField = $this->get('field');
		if($this->isField($strField)){
			$bResult = $this->update($intSectionId, [$strField => $strValue]);
		}
		elseif($intPropertyId = $this->isSectionProperty($strField)){
			$bResult = $this->update($intSectionId, [$strField => $strValue]);
		}
		return $bResult;
	}
	
}

?>