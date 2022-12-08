<?
namespace WD\Antirutin\Plugins\Element;

use
	\WD\Antirutin\Helper,
	\WD\Antirutin\IBlock,
	\WD\Antirutin\PluginElement;

class LinkSection extends PluginElement {
	
	// *******************************************************************************************************************
	// Main methods
	// *******************************************************************************************************************
	
	/**
	 *	Execute!
	 */
	public function processElement($intElementId){
		$bResult = false;
		$strAction = $this->get('action');
		$intSelectedSectionId = $this->get('section');
		if(!strlen($strAction)){
			$this->setError(static::getMessage('ERROR_NO_ACTION'));
			return false;
		}
		if(is_string($intSelectedSectionId) && !strlen($intSelectedSectionId) && $strAction != 'set'){
			$this->setError(static::getMessage('ERROR_NO_SECTION'));
			return false;
		}
		if($intSelectedSectionId) {
			# Get element groups
			$arElementSectionsId = [];
			if(in_array($strAction, ['change', 'add', 'delete'])){
				$resSections = \CIBlockElement::getElementGroups($intElementId, true, ['ID', 'NAME']);
				while ($arSection = $resSections->getNext(false, false)) {
					$arElementSectionsId[] = $arSection['ID'];
				}
			}
			# Action!
			switch($strAction) {
				case 'set':
					$bResult = $this->update($intElementId, [
						'IBLOCK_SECTION_ID' => $intSelectedSectionId,
					]);
					break;
				case 'change':
					$bResult = $this->update($intElementId, [
						'IBLOCK_SECTION_ID' => $intSelectedSectionId,
						'IBLOCK_SECTION' => $arElementSectionsId,
					]);
					$bResult = $this->updateElementIndex($intElementId);
					break;
				case 'add':
					if(!in_array($intSelectedSectionId, $arElementSectionsId)){
						$arElementSectionsId[] = $intSelectedSectionId;
					}
					$this->setElementSection($intElementId, $arElementSectionsId);
					$bResult = true;
					break;
				case 'delete':
					$bResult = true;
					$mDeleteKey = array_search($intSelectedSectionId, $arElementSectionsId);
					if($mDeleteKey !== false){
						unset($arElementSectionsId[$mDeleteKey]);
						$this->setElementSection($intElementId, $arElementSectionsId);
						$bResult = true;
					}
					break;
			}
		}
		elseif($strAction == 'set' && is_string($intSelectedSectionId) && !strlen($intSelectedSectionId)){
			$this->setElementSection($intElementId, []);
			$bResult = true;
		}
		# Trigger for update
		if($bResult){
			$this->update($intElementId, [
				'TIMESTAMP_X' => new \Bitrix\Main\Type\DateTime(),
			]);
		}
		return $bResult;
	}
	
}

?>