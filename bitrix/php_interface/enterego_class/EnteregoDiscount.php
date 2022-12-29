<?php

namespace Enterego;

class EnteregoDiscount
{
    public function getSectionProductsForFilter($id): array
    {
        global $DB;
        $productIds = [];
        $sectionsIDS = [];
        $rsElements = \CIBlockElement::GetList(['ID', 'IBLOCK_SECTION_ID'], array_merge(['SECTION_ID' => $id], $GLOBALS['arrFilter']));

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
        $categoryProduct = $this->getFilterDiscountCategoryProduct($sectionsIDS, [], 'discount');

        $arCategory = [];
        foreach ($categoryProduct as $section) {
            if (!isset($section[0]) || $section[0]['CODE'] === 'sale') {
                continue;
            }

            $link = 'discount';
            if (!empty($section)) {
                $section[0]['CHILD'] = $this->recursiveForeach($section, $link);
            }
            $arCategory[] = $section[0];
        }
        return $arCategory;
    }

    public function recursiveForeach($array, $linkNew): array
    {
        $htmlElem = '';
        $arCategory = [];
        if (!empty($array)) {
            foreach ($array as $key => $item) {
                if ($key !== 0) {
                    if (count($item) > 1) {
                        $item['CHILD'] = $this->recursiveForeach($item, $linkNew);
                        $arCategory[] = $item;
                    } else {
                        $item['CHILD'] = $this->recursiveForeach($item, $linkNew);
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
    public function getFilterDiscountCategoryProduct(array $sectionsIDS = [], array $filter = [], string $catFilter = ''): array
    {
        $resSection = [];
        $categoryAr = \CIBlockSection::GetList(array("depth_level" => "ASC"),
            array('ACTIVE' => 'Y', 'ID' => $sectionsIDS), false, array('ID', 'CODE', 'NAME', 'IBLOCK_SECTION_ID'));
        $sectionsIDS = [];
        while ($arrayData = $categoryAr->Fetch()) {
            // узнаем количество доступных элементов в разделе
            $res = \CIBlockElement::GetList(false, array('SECTION_ID' => $arrayData['ID'], '=AVAILABLE' => 'Y'), false, false, false);
            // добавляем категорию если в ней есть элементы
            if ($res->SelectedRowsCount() > 0) {
                $resSection[$arrayData['IBLOCK_SECTION_ID'] ?? 0][$arrayData['ID']][0] = $arrayData;

                if ($arrayData['IBLOCK_SECTION_ID']) {
                    $sectionsIDS[$arrayData['IBLOCK_SECTION_ID']] = $arrayData['IBLOCK_SECTION_ID'];
                }
            }
        }

        $this->recursiveGetFilterDiscountCategoryProduct($resSection, $sectionsIDS);
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

    private function recursiveGetFilterDiscountCategoryProduct(&$resSection, $sectionsIDS, $level = 0)
    {
        $categoryAr = \CIBlockSection::GetList(array("depth_level" => "ASC"), array('ACTIVE' => 'Y', 'ID' => $sectionsIDS),
            false, array('ID', 'CODE', 'NAME', 'IBLOCK_SECTION_ID'));
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
            $this->recursiveGetFilterDiscountCategoryProduct($resSection, $sectionsIDS, $level);
        }
    }


}