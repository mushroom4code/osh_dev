<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('webdebug.utilities')) {
	return;
}

if (!is_array($arParams['PROPERTIES'])) {
	$arParams['PROPERTIES'] = array();
}
if (!is_array($arParams['EXCLUDE_PROPERTIES'])) {
	$arParams['EXCLUDE_PROPERTIES'] = array();
}
if (strlen($arParams['MULTIPLE_SEPARATOR'])===0) {
	$arParams['MULTIPLE_SEPARATOR'] = ', ';
}
$arParams['NOGROUP_SHOW'] = $arParams['NOGROUP_SHOW']!='N' ? true : false;

$arExclude = &$arParams['EXCLUDE_PROPERTIES'];

$strSeparator = &$arParams['MULTIPLE_SEPARATOR'];

$arResult['PROPS_GROUPS'] = [];

if($arParams['IBLOCK_ID']){
	$arSavedData = \WD\Utilities\PropSorterTable::loadIBlockData($arParams['IBLOCK_ID']);
	$strHeaderIndex = 1;
	$strHeaderName = $arParams['~NOGROUP_NAME'];
	$bHeaderFound = false;
	foreach($arSavedData as $arProperty){
		$bHeader = !is_numeric($arProperty['PROPERTY_ID']);
		if($bHeader){
			$bHeaderFound = true;
			$strHeaderName = $arProperty['GROUP_TITLE'];
			$strHeaderIndex++;
			$arResult['PROPS_GROUPS']['GROUP_'.$strHeaderIndex] = [
				'NAME' => $strHeaderName,
				'ACTIVE' => $arProperty['ACTIVE'] == 'N' ? 'N' : 'Y',
				'ITEMS' => [],
			];
		}
		else{
			$bSkip = !$bHeaderFound && !$arParams['NOGROUP_SHOW'];
			if(!$bSkip) {
				if(!is_array($arResult['PROPS_GROUPS']['GROUP_'.$strHeaderIndex])){
					$arResult['PROPS_GROUPS']['GROUP_'.$strHeaderIndex] = ['ITEMS' => []];
				}
				$arResult['PROPS_GROUPS']['GROUP_'.$strHeaderIndex]['NAME'] = $strHeaderName;
				#
				if(is_array($arParams["PROPERTIES"])){
					foreach($arParams["PROPERTIES"] as $arProp) {
						if($arProp['ID'] == $arProperty['PROPERTY']['ID']){
							$strPropCode = &$arProp['CODE'];
							$bExcluded = is_array($arExclude) && strlen($strPropCode) && in_array($strPropCode, $arExclude);
							if(!isset($arProp['DISPLAY_VALUE']) && isset($arProp['VALUE'])){
								$arProp['DISPLAY_VALUE'] = $arProp['VALUE'];
							}
							$mValue = &$arProp['DISPLAY_VALUE'];
							if(!$bExcluded && (is_array($mValue) && empty($mValue) || is_string($mValue) && !strlen($mValue))){
								$bExcluded = true;
							}
							if(!$bExcluded && ($mValue === false || $mValue === NULL)){
								$bExcluded = true;
							}
							if(!$bExcluded){
								$mDValue = &$arProp['DISPLAY_VALUE'];
								$mDValue = isset($mDValue) ? (is_array($mDValue) ? implode($strSeparator, $mDValue) : $mDValue) 
									: (is_array($mValue) ? implode($strSeparator, $mValue) : $mValue);
								$arResult['PROPS_GROUPS']['GROUP_'.$strHeaderIndex]['ITEMS'][] = $arProp;
							}
						}
					}
				}
			}
		}
	}
	foreach($arResult['PROPS_GROUPS'] as $Key => $arGroup) {
		if (!is_array($arGroup['ITEMS']) || empty($arGroup['ITEMS']) || $arGroup['ACTIVE']=='N') {
			unset($arResult['PROPS_GROUPS'][$Key]);
		}
	}
}

$this->IncludeComponentTemplate();
?>