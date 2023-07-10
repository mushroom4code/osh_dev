<?php

namespace Enterego;

class EnteregoDiscountHitsSelector
{
    public function getSectionProductsForFilter(string $link, array $params, bool $useActive = true): array
    {
        global $DB;
        $productIds = [];
        $sectionsIDS = [];
        $rsElements = \CIBlockElement::GetList(['ID', 'IBLOCK_SECTION_ID'], $GLOBALS[$params['FILTER_NAME']]);

        while ($arElement = $rsElements->Fetch()) {
            $productIds[] = $arElement['ID'];
        }
        if (!empty($productIds)) {
            $productIds = implode(',', $productIds);

            $strSql = "SELECT IBLOCK_SECTION_ID FROM b_iblock_section_element WHERE IBLOCK_ELEMENT_ID IN ($productIds) GROUP BY IBLOCK_SECTION_ID";

            $res = $DB->Query($strSql);
            while ($arRes = $res->Fetch()) {
                $sectionsIDS[] = $arRes['IBLOCK_SECTION_ID'];
            }
        }
        $categoryProduct = $this->getFilterCategoryProduct($sectionsIDS, [], $link, $useActive);

        $arCategory = $this->recursiveForeach($categoryProduct, $link);
        return $arCategory;
    }

    public function recursiveForeach($section, $linkNew): array
    {
        $arCategory = [];
        if (!empty($section)) {
            foreach ($section as $key => $item) {
                if ($key !== 0) {
                    if (isset($item[0])) {
                        $item[0]['CHILDS'] = $this->recursiveForeach($item, $linkNew);
                        $item[0]['SECTION_PAGE_URL'] = '/' . $linkNew . '/' . $item[0]['CODE'] . '/';
                        $arCategory[] = $item[0];
                    }
                }
            }
        }
        return $arCategory;
    }

    /**
     * @param array $sectionsIDS
     * @param array $filter
     * @param string $catFilter
     * @return array
     */
    public function getFilterCategoryProduct(array $sectionsIDS = [], array $filter = [], string $catFilter = '', bool $useActive): array
    {
        $resSection = [];
        $arSelect = array('ID', 'CODE', 'NAME', 'IBLOCK_SECTION_ID', 'ACTIVE');
        $sectionsFilter = array('ID' => $sectionsIDS);
        if ($useActive) {
            $sectionsFilter['ACTIVE'] = 'Y';
        }
        $categoryAr = \CIBlockSection::GetList(array("depth_level" => "ASC"),
            $sectionsFilter, false, $arSelect);
        $sectionsIDS = [];
        while ($arrayData = $categoryAr->Fetch()) {
            // узнаем количество доступных элементов в разделе
            $elementFilter = array('SECTION_ID' => $arrayData['ID']);
            if ($useActive) {
                $elementFilter['=AVAILABLE'] = 'Y';
            }
            $res = \CIBlockElement::GetList(false, $elementFilter, false, false, false);
            // добавляем категорию если в ней есть элементы
            if ($res->SelectedRowsCount() > 0) {
                $resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']][0] = $arrayData;

                if ($arrayData['IBLOCK_SECTION_ID']) {
                    $sectionsIDS[$arrayData['IBLOCK_SECTION_ID']] = $arrayData['IBLOCK_SECTION_ID'];
                }
            }
        }

        $this->recursiveGetFilterCategoryProduct($resSection, $sectionsIDS, 0, $useActive);
        if (!empty($resSection[0])) {
            if (!empty($filter) && $catFilter === 'catalog') {
                foreach ($resSection[0] as $item) {
                    $itemS = $item[0] ?: $item;
                    if (in_array($itemS['CODE'], $filter)) {
                        $resSection[0][$itemS['ID']] = $item;
                    } else {
                        unset($resSection[0][$itemS['ID']]);
                    }
                }
            }
        }
        return $resSection[0] ?? [];
    }

    private function recursiveGetFilterCategoryProduct(&$resSection, $sectionsIDS, $level = 0, bool $useActive)
    {
        $arFilter = array('ID' => $sectionsIDS);
        if ($useActive) {
            $arFilter['ACTIVE'] = 'Y';
        }
        $categoryAr = \CIBlockSection::GetList(array("depth_level" => "ASC"), $arFilter,
            false, array('ID', 'CODE', 'NAME', 'IBLOCK_SECTION_ID', 'ACTIVE'));
        $sectionsIDS = [];
        while ($arrayData = $categoryAr->Fetch()) {
            if (isset($resSection[$arrayData['ID']])) {
                if (isset($arrayData['IBLOCK_SECTION_ID']) && isset($resSection[0][$arrayData['IBLOCK_SECTION_ID']])) {
                    if (isset($resSection[0][$arrayData['IBLOCK_SECTION_ID']][$arrayData['ID']][0])) {
                        $resSection[0][$arrayData['IBLOCK_SECTION_ID']][$arrayData['ID']] =
                            array_replace_recursive($resSection[0][$arrayData['IBLOCK_SECTION_ID']][$arrayData['ID']] ?? [], $resSection[$arrayData['ID']]);
                    } else {
                        $resSection[0][$arrayData['IBLOCK_SECTION_ID']][$arrayData['ID']] = $resSection[$arrayData['ID']];
                    }
                    $resSection[0][$arrayData['IBLOCK_SECTION_ID']][$arrayData['ID']][0] = $arrayData;
                } else {
                    $resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']] = $resSection[$arrayData['ID']];
                    $resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']][0] = $arrayData;
                }
                unset($resSection[$arrayData['ID']]);
            } else {
                if (isset($resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']])) {
                    $resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']][0] = $arrayData;
                } else {
                    $resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']] = $arrayData;
                }
            }
            if ($arrayData['IBLOCK_SECTION_ID']) {
                $sectionsIDS[$arrayData['IBLOCK_SECTION_ID']] = $arrayData['IBLOCK_SECTION_ID'];
            }
        }
        if ($sectionsIDS) {
            $level++;
            $this->recursiveGetFilterCategoryProduct($resSection, $sectionsIDS, $level, $useActive);
        }
    }


}