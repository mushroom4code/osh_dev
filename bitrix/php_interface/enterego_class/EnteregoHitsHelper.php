<?php

namespace Enterego;

use Enterego\EnteregoDiscountHitsSelector;

class EnteregoHitsHelper
{
    public static function getHitsSectionsItemsArr(array $params, bool $useActive = true)
    {
        $cat = new EnteregoDiscountHitsSelector();
        $arFilterS = array('IBLOCK_ID' => IBLOCK_CATALOG);
        $GLOBALS[$params['FILTER_NAME']] = array_merge($GLOBALS[$params['FILTER_NAME']], $arFilterS);
        $items = [];
        $sectionTree = $cat->getSectionProductsForFilter('hit', $params, $useActive, $items);
        foreach ($sectionTree as $sectionKey => $section) {
            if ($section['ACTIVE'] != 'Y') {
                unset($sectionTree[$sectionKey]);
            }
        }

        $searchRootDirectory = [];
        function getChild($rootSectionId, $child, &$searchRootDirectory): void
        {
            foreach ($child as $item) {
                $searchRootDirectory[$item['ID']] = $rootSectionId;
                if (!empty($item['CHILDS']) && count($item['CHILDS']) !== 0) {
                    getChild($rootSectionId, $item['CHILDS'], $searchRootDirectory);
                }
            }
        }

        foreach ($sectionTree as $TreeItem) {
            $rootSectionId = $TreeItem['ID'];
            $searchRootDirectory[$TreeItem['ID']] = $TreeItem['ID'];
            getChild($rootSectionId, $TreeItem['CHILDS'], $searchRootDirectory);
        }

        $sectionsItemsArr = [];
        foreach ($items as $item) {
            if (empty($item['IBLOCK_SECTION_ID']) && !isset($searchRootDirectory[$item['IBLOCK_SECTION_ID']])) {
                continue;
            }

            if (empty($sectionsItemsArr[$searchRootDirectory[$item['IBLOCK_SECTION_ID']]]) ||
                count($sectionsItemsArr[$searchRootDirectory[$item['IBLOCK_SECTION_ID']]]) < 25) {
                $sectionsItemsArr[$searchRootDirectory[$item['IBLOCK_SECTION_ID']]][$item['ID']] = $item;
            }
        }
        return $sectionsItemsArr;
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

    public static function checkIfStartsWithHit($APPLICATION) {
        return str_starts_with($APPLICATION->GetCurPage(), '/hit/');
    }
}