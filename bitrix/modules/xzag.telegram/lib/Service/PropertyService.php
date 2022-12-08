<?php

namespace Xzag\Telegram\Service;

use CIBlockElement;
use CIBlockFormatProperties;

/**
 * Class PropertyService
 * @package Xzag\Telegram\Service
 */
class PropertyService
{
    /**
     * @param int $iblockId
     *
     * @return array
     */
    public function getProperties(int $iblockId): array
    {
        $element = CIBlockElement::GetByID($iblockId)->GetNextElement();

        if (!$element) {
            return [];
        }

        return $element->GetProperties();
    }

    /**
     * @param array $properties
     *
     * @return array
     */
    public function formatProperties(array $properties): array
    {
        if (empty($properties)) {
            return array();
        }

        $result = array();

        foreach ($properties as $prop) {
            if ($prop['XML_ID'] == 'CML2_LINK' || $prop['PROPERTY_TYPE'] == 'F') {
                continue;
            }

            if (is_array($prop["VALUE"]) && empty($prop["VALUE"])) {
                continue;
            }

            if (!is_array($prop["VALUE"]) && mb_strlen($prop["VALUE"]) <= 0) {
                continue;
            }

            $displayProperty = CIBlockFormatProperties::GetDisplayValue(array(), $prop, '');

            $mxValues = '';

            if ('E' == $prop['PROPERTY_TYPE']) {
                if (!empty($displayProperty['LINK_ELEMENT_VALUE'])) {
                    $mxValues = array();

                    foreach ($displayProperty['LINK_ELEMENT_VALUE'] as $arTempo) {
                        $mxValues[] = $arTempo['NAME'] . ' [' . $arTempo['ID'] . ']';
                    }
                }
            } elseif ('G' == $prop['PROPERTY_TYPE']) {
                if (!empty($displayProperty['LINK_SECTION_VALUE'])) {
                    $mxValues = array();

                    foreach ($displayProperty['LINK_SECTION_VALUE'] as $arTempo) {
                        $mxValues[] = $arTempo['NAME'] . ' [' . $arTempo['ID'] . ']';
                    }
                }
            }
            if (empty($mxValues)) {
                $mxValues = $displayProperty["DISPLAY_VALUE"];
            }

            $result[] = array(
                'ID' => $prop["ID"],
                'CODE' => htmlspecialcharsback($prop['CODE']),
                'NAME' => htmlspecialcharsback($prop["NAME"]),
                'VALUE' => htmlspecialcharsback(strip_tags(is_array($mxValues) ? implode("/ ", $mxValues) : $mxValues))
            );
        }

        return $result;
    }
}
