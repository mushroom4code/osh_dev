<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Catalog;

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */
if ($arParams['PRODUCTS_VIEWED'] == 'Y') {
    $arIDs = array();
    $resultArray = array();
    foreach ($arResult['ITEMS'] as $key => $arItem) {
        $arIDs[$key] = $arItem['ID'];
    }

    $arIDs = $arResult['ORIGINAL_PARAMETERS']['GLOBAL_FILTER']['ID'];
    foreach ($arIDs as $id) {
        foreach ($arResult['ITEMS'] as $key => $arItem) {
            if ($id == $arItem['ID'])
                $resultArray[] = $arItem; //Отсортированный массив
        }
    }
    $arResult['ITEMS']= $resultArray;
}

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();