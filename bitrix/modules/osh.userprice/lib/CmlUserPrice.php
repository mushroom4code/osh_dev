<?php

namespace Enterego\UserPrice;

use SimpleXMLElement;

class CmlUserPrice
{
    /**
     * Во время обмена - загрузить из XML-дерева информацию по индивидуальным ценам
     * (ent_userprice)
     *
     * @param string $xml_path
     * @throws Main\NotImplementedException
     */
    public static function LoadUserPriceRules($xml_path, $loader)
    {
        if (!$xml_path) {
            return;
        }

        $xml_file = file_get_contents($xml_path);
        $xml = simplexml_load_string($xml_file);

        if (!isset($xml->Контрагенты)) {
            return;
        }

        foreach ($xml->Контрагенты[0] as $xmlUser) {
            $userXML_ID = (string)$xmlUser->Ид;

            PluginStatic::_DebugPrint("Контрагент: " . $userXML_ID);

            if (!isset($xmlUser->Соглашения)) {
                // Для пользователя нет соглашений
                continue;
            }

            $userId = PluginStatic::UserXMLIDToID($userXML_ID);
            if (!$userId) {
                PluginStatic::_FailImport("Пользователь не найден в базе битрикса по XML_ID: {$userXML_ID}", $loader);
                continue;
            }

            if (!PluginStatic::ClearPrices($userId)) {
                PluginStatic::_FailImport("Не удалось очистить правила перед записью актуальных правил пользователя {$userId}", $loader);
                continue;
            }

            PluginStatic::_DebugPrint("Стерты индивидуальные правила цен для этого пользователя");

            foreach ($xmlUser->Соглашения as $xmlTerm) {
                if ($xmlTerm->Группы) {

                    foreach ($xmlTerm->Группы as $xmlGroup) {
                        $xmlID_iblock_section = (string) $xmlGroup->Группа;
                        $xmlID_catalog_price_type = (string) $xmlGroup->ТипЦены;
                        self::LoadUserPriceRules_Category($userId, $xmlID_iblock_section, $xmlID_catalog_price_type);
                    }
                }

                if ($xmlTerm->Товары) {

                    foreach ($xmlTerm->Товары as $xmlProduct) {
                        $xmlID_product = (string) $xmlProduct->Товар;
                        $xmlID_catalog_price_type = (string) $xmlProduct->ТипЦены;

                        self::LoadUserPriceRules_Product($userId, $xmlID_product, $xmlID_catalog_price_type);
                    }

                }
            }
        }
    }


    /**
     * Загрузить индивидуальную цену для группы товара
     *
     * @param $userId
     * @param string $xmlID_iblock_section
     * @param string $xmlID_catalog_price_type
     * @throws \Exception
     */
    private static function LoadUserPriceRules_Category($userId, string $xmlID_iblock_section, string $xmlID_catalog_price_type)
    {

        if (!$xmlID_iblock_section || !$xmlID_catalog_price_type) {
            // Invalid XML
            return;
        }

        $iblockSectionID = PluginStatic::CategoryXMLIDToID($xmlID_iblock_section);
        if (!$iblockSectionID) {
            PluginStatic::_FailImport("Не найдена Группа в базе сайте по XML_ID: {$xmlID_iblock_section}");
        }

        self::_AddUserPriceRuleCategory($userId, $xmlID_catalog_price_type, $iblockSectionID);

    }

    static private function _AddUserPriceRuleCategory($userId, $xmlID_catalog_price_type, $iblockSectionID, $level = 0)
    {
        $comment = $level == 0 ? '' : ' (автомат.)';
        PluginStatic::_DebugPrint("Правило для Группы: " . $iblockSectionID . $comment, $level);

        $catalogPriceID = PluginStatic::CatalogPriceXMLIDToID($xmlID_catalog_price_type);
        if (!$catalogPriceID) {
            PluginStatic::_FailImport("Не найден тип цены правила по XML_ID: {$xmlID_catalog_price_type}");
        }

        if (!PluginStatic::CreateSpecialPriceIDRule(null, $iblockSectionID, $userId, $catalogPriceID, $level == 0)) {
            PluginStatic::_FailImport("Не удалось добавить правило индивидуальных цен: null, {$iblockSectionID}, {$userId}, {$catalogPriceID}");
        }

        PluginStatic::_DebugPrint("Тип цены назначен: {$catalogPriceID}", $level + 1);

        foreach (self::LoadRecursiveCategories($iblockSectionID) as $child_iblock_sectionID) {
            static::_AddUserPriceRuleCategory($userId, $xmlID_catalog_price_type, $child_iblock_sectionID, $level + 1);
        }
    }


    static private function  LoadRecursiveCategories($iblockSectionID): array
    {
        $childrenBlocks = PluginStatic::FetchDdRows('b_iblock_section', 'IBLOCK_SECTION_ID', $iblockSectionID, ['ID'], 100);

        return array_column($childrenBlocks, 'ID');
    }

    /**
     * Загрузить индивидуальную цену для товара
     *
     * @param $userId
     * @param string $xmlID_product
     * @param string $xmlID_catalog_price_type
     * @throws \Exception
     */
    private function LoadUserPriceRules_Product($userId, string $xmlID_product, string $xmlID_catalog_price_type)
    {

        if (!$xmlID_product || !$xmlID_catalog_price_type) {
            // Invalid XML
            return;
        }

        $productID = PluginStatic::ProductXMLIDToID($xmlID_product);
        if (!$productID) {
            PluginStatic::_FailImport("Не найден Товар в базе сайте по XML_ID: {$xmlID_product}");
        }

        PluginStatic::_DebugPrint("Правило для Товара: {$productID}");

        $catalogPriceID = PluginStatic::CatalogPriceXMLIDToID($xmlID_catalog_price_type);
        if (!$catalogPriceID) {
            PluginStatic::_FailImport("Не найден тип цены правила по XML_ID: {$xmlID_catalog_price_type}");
        }

        if (!PluginStatic::CreateSpecialPriceIDRule($productID, null, $userId, $catalogPriceID, true)) {
            PluginStatic::_FailImport("Не удалось добавить правило индивидуальных цен: {$productID}, null, {$userId}, {$catalogPriceID}");
        }

        PluginStatic::_DebugPrint("Тип цены назначен: {$catalogPriceID}", 1);

    }
}