<?php

namespace Enterego;

use Enterego\EnteregoDiscountHitsSelector;

class EnteregoHitsHelper
{
    public static function getHitsSectionsItemsArr(array $items, array $params, bool $useActive = true)
    {
        $cat = new EnteregoDiscountHitsSelector();
        $arFilterS = array('IBLOCK_ID' => IBLOCK_CATALOG);
        $GLOBALS[$params['FILTER_NAME']] = array_merge($GLOBALS[$params['FILTER_NAME']], $arFilterS);
        $sectionTree = $cat->getSectionProductsForFilter('hit', $params, $useActive);
        foreach ($sectionTree as $sectionKey => $section) {
            if ($section['ACTIVE'] != 'Y') {
                unset($sectionTree[$sectionKey]);
            }
        }
        $sectionsItemsArr = [];
        foreach ($items as $item) {
            if (empty($item['IBLOCK_SECTION_ID'])) {
                continue;
            }
            $rootSectionId = self::recursiveArraySearchRootParentSection($sectionTree, $item['IBLOCK_SECTION_ID']);
            if ($rootSectionId) {
                $sectionsItemsArr[$rootSectionId][$item['ID']] = $item;
            }
        }
        return $sectionsItemsArr;
    }

    public static function recursiveArraySearchRootParentSection(array $array, $parentId)
    {
        foreach ($array as $item) {
            if ($item['ID'] == $parentId) {
                return $item['ID'];
            } else if ($item['CHILDS']) {
                $id = self::recursiveArraySearchRootParentSection($item['CHILDS'], $parentId);
                if ($id) {
                    return $item['ID'];
                }
            }
        }
    }

    public static function getHitsSectionsItemsByPopularity(array $sectionsItemsArr)
    {
        foreach ($sectionsItemsArr as &$sectionItems) {
            foreach ($sectionItems as &$sectionItem) {
                $sectionItem['SERVICE_FIELD_POPULARITY'] = intval($sectionItem['PROPERTIES'][SORT_POPULARITY]['VALUE']);
            }
        }

        foreach ($sectionsItemsArr as $sectionItemsKey => &$sectionItems) {
            $popularity = array();
            foreach ($sectionItems as $key => $row) {
                $popularity[$key] = $row['SERVICE_FIELD_POPULARITY'];
            }
            array_multisort($popularity, SORT_DESC, $sectionItems);
        }

        $tempSectionsItemsArr = $sectionsItemsArr;
        foreach ($tempSectionsItemsArr as $sectionKey => $sectItems) {
            foreach ($sectItems as $sectionItemKey => $sectItem) {
                unset($sectionsItemsArr[$sectionKey][$sectionItemKey]);
                $sectionsItemsArr[$sectionKey][$sectItem['ID']] = $sectItem;
            }
        }
        return $sectionsItemsArr;
    }

    public static function getHitsSections(array $sectionsItemsArr)
    {
        $sectionsArr = [];
        $sectionsRes = \CIBlockSection::GetList([], ['ID' => array_keys($sectionsItemsArr), 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y']);
        while ($section = $sectionsRes->fetch()) {
            $sectionsArr[$section['ID']] = $section;
        }
        return $sectionsArr;
    }

    public static function checkIfHits($APPLICATION)
    {
        return ($APPLICATION->GetCurPage() === '/hit/');
    }
}