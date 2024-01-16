<?php

namespace Enterego;

use CFile;
use CIBlockElement;
use CIBlockProperty;
use Enterego\EnteregoHelper;

class EnteregoGroupedProducts
{
    public static function getDataPropOffers($paramForCategory = false, $idSection = false): array
    {
        $arData = [];
        if (!$paramForCategory && !$idSection) {
            $arData = [
                'VKUS' => [
                    'CODE' => "VKUS",
                    'TYPE' => 'colorWithText',
                    'PREF' => '',
                ],
                'GRAMMOVKA_G' => [
                    'CODE' => "GRAMMOVKA_G",
                    'TYPE' => 'text',
                    'PREF' => 'гр.',
                ],
                'GRAMMOVKA_GR' => [
                    'CODE' => "GRAMMOVKA_GR",
                    'TYPE' => 'text',
                    'PREF' => 'гр.',
                ],
                'GRAMMOVKA_G_1' => [
                    'CODE' => "GRAMMOVKA_G_1",
                    'TYPE' => 'text',
                    'PREF' => 'гр.',
                ],
                'TSVET' => [
                    'CODE' => "TSVET",
                    'TYPE' => 'color',
                    'PREF' => '',
                ],
                'SHTUK_V_UPAKOVKE' => [
                    'CODE' => "SHTUK_V_UPAKOVKE",
                    'TYPE' => 'text',
                    'PREF' => 'шт.',
                ],
                'KOLICHESTVO_SHTUK_V_UPAKOVKE' => [
                    'CODE' => "KOLICHESTVO_SHTUK_V_UPAKOVKE",
                    'TYPE' => 'text',
                    'PREF' => 'шт.',
                ],
            ];
        }
        return $arData;
    }

    public static function getListGroupedProduct($prodId, $listGroupedProduct, &$item): array
    {

        if (!empty($listGroupedProduct)) {
            $rsMainPropertyValues = CIBlockElement::GetProperty(IBLOCK_CATALOG, $prodId,
                [], ['CODE' => 'OSNOVNOE_SVOYSTVO_TP']);

            $refPropsCode = [];
            while ($arMainPropertyValue = $rsMainPropertyValues->GetNext()) {
                $xmlId = $arMainPropertyValue['VALUE_XML_ID'];
                $rsRefProperty = CIBlockProperty::GetList([], ['XML_ID' => $xmlId]);
                if ($arRefProperty = $rsRefProperty->Fetch()) {
                    $refPropsCode[] = $arRefProperty['CODE'];
                }
            }

            foreach ($listGroupedProduct as $elemProp) {
                $item['GROUPED_PRODUCTS'][$elemProp] = CIBlockElement::GetList([],
                    [
                        'ID' => $elemProp,
                        'IBLOCK_CATALOG_ID' => IBLOCK_CATALOG,
                        'ACTIVE' => 'Y'
                    ], false, false,
                    [
                        "ID",
                        "ACTIVE",
                        "NAME",
                        "PREVIEW_PICTURE",
                        "CATALOG_QUANTITY",
                        "DETAIL_PICTURE",
                        "DETAIL_PAGE_URL",
                    ]
                )->Fetch();

                $elem = &$item['GROUPED_PRODUCTS'][$elemProp];
                $elem['MEASURE_RATIO'] = \Bitrix\Catalog\MeasureRatioTable::getList(array(
                    'select' => array('RATIO'),
                    'filter' => array('=PRODUCT_ID' => $elemProp)
                ))->fetch()['RATIO'] ?? 1;
                EnteregoHelper::setProductsActiveUnit($elem, true);
                $refPropsCode[] = 'USE_DISCOUNT';
                $elemProp === $prodId ? $elem['SELECTED'] = 'selected' : $elem['SELECTED'] = '';
                $elem['ACTUAL_BASKET'] = 0;
                if (!empty($elem) && (int)$elem['CATALOG_QUANTITY'] > 0 && $elem['ACTIVE'] === 'Y') {
                    foreach ($refPropsCode as $propCode) {
                        $groupProperty = [];
                        $propList = CIBlockElement::GetProperty(IBLOCK_CATALOG, $elemProp,
                            [], ['EMPTY' => 'N', 'ACTIVE' => "Y", 'CODE' => $propCode]);

                        while ($props = $propList->GetNext()) {

                            if (empty($elem['PROPERTIES'][$props['CODE']])) {
                                $elem['PROPERTIES'][$props['CODE']] = $props;
                            }

                            $groupProperty[$props['VALUE_ENUM'] ?? $props['VALUE']] = $elem['PROPERTIES'][$props['CODE']]['JS_PROP'][$props['VALUE_ENUM'] ?? $props['VALUE']] = [
                                'VALUE_ENUM' => $props['VALUE_ENUM'] ?? $props['VALUE'],
                                'VALUE_XML_ID' => $props['VALUE_XML_ID'],
                                'PROPERTY_VALUE_ID' => $props['PROPERTY_VALUE_ID'],
                                'CODE' => '/catalog/product/' . $elem['CODE'] . '/',
                                'PRODUCT_IDS' => $elem['ID'],
                                'PREVIEW_PICTURE' => CFile::GetPath(($elem['PREVIEW_PICTURE'] ?? $elem['DETAIL_PICTURE']))
                                    ?? '/local/templates/Oshisha/images/no-photo.gif',
                                'TYPE' => $props['CODE'],
                                'NAME' => $elem['NAME']
                            ];
                        }

                        $needAdd = true;
                        foreach ($item['GROUPED_PROPS_DATA'][$propCode] as $currentGroupProperty) {

                            if (count($currentGroupProperty) !== count($groupProperty)) {
                                continue;
                            }

                            $isDifferences = false;
                            foreach ($groupProperty as $key => $currentValue) {
                                if (isset($currentGroupProperty[$key])) {
                                    continue;
                                }
                                $isDifferences = true;
                            }
                            if (!$isDifferences) {
                                $needAdd = false;
                                break;
                            }
                        }
                        if ($needAdd) {
                            $item['GROUPED_PROPS_DATA'][$propCode][] = $groupProperty;
                        }
                    }
                } else {
                    unset($item['GROUPED_PRODUCTS'][$elemProp]);
                }
            }
        }
        return $item;
    }
}