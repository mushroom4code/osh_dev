<?php

namespace Enterego\UserPrice;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use CIBlockElement;
use Exception;

class PluginStatic
{

    /**
     * Добавить новое ценовое правило в базу
     *
     * @param int|string|null $PRODUCT_ID ID товара правила
     * @param int|string|null $IBLOCK_SECTION_ID    ID категории правила
     * @param int|string $USER_ID   ID пользователя правила
     * @param int|string $CATALOG_PRICE_ID ID типа цены для определения правила
     * @param bool $bPrimary Primary должен устанавливаться для всех правил, заданных непосредственно - чего не сказать про автоматические (например, подкатегории)
     * @returns int|false
     */
    public static function CreateSpecialPriceIDRule($PRODUCT_ID, $IBLOCK_SECTION_ID, $USER_ID, $CATALOG_PRICE_ID, $bPrimary)
    {
        $rowData = [
            'user_id'           => "'{$USER_ID}'",
            'catalog_price_id'  => "'{$CATALOG_PRICE_ID}'",
            'is_primary'        => "'".intval($bPrimary)."'",
        ];

        if(!$USER_ID) {
            // Должен быть указан пользователь
            return false;
        }

        if($value = $IBLOCK_SECTION_ID) {
            $rowData['iblock_section_id'] = "'{$value}'";
        }

        if($value = $PRODUCT_ID) {
            $rowData['product_id'] = "'{$value}'";
        }

        if(count($rowData) < 3) {
            // Нельзя добавить правило не привязанное
            // ни к товару, ни к категории
            return false;
        }

        $sqlKeys = implode(', ', array_keys($rowData));
        $sqlValues = implode(', ', array_values($rowData));

        $sql = "INSERT INTO `ent_user_price_rule` ({$sqlKeys}) VALUES ({$sqlValues})";

        $db = Application::getConnection();

        try {
            $db->query($sql);
            return $db->getInsertedId();
        } catch (Exception $ex) {

        }

        return false;
    }

    /**
     * Очистить все правила в базе для пользователя.
     *
     * @param int|string $USER_ID   ID пользователя правила
     * @return bool true - операция совершена
     */
    public static function ClearPrices($USER_ID)
    {
        if(!$USER_ID) {
            return false;
        }

        $sql = "DELETE FROM  `ent_user_price_rule` WHERE `user_id` = '{$USER_ID}'";

        $db = Application::getConnection();

        try {
            $db->query($sql);
            return true;
        } catch (Exception $ex) {

        }

        return false;
    }

    /**
     * Запрос строк из базы (SELECT) по ключевому полю (ID/etc)
     *
     * @param string $table
     * @param string $field
     * @param $value
     * @param array $outFields
     * @param int $limit
     * @return array
     * @throws SqlQueryException
     */
    public static function FetchDdRows(string $table, string $field, $value, array $outFields, int $limit): array
    {
        $sqlFields = join(', ', array_map(function($field) use ($table) {
            return "`{$table}`.`{$field}`";
        }, $outFields));

        $sql = "SELECT {$sqlFields} FROM `{$table}` WHERE `{$table}`.`{$field}` = '{$value}' LIMIT {$limit}";

        $db = Application::getConnection();
        $res = $db->query($sql);

        $result = [];

        while($arRes = $res->fetch())
        {
            $result[] = $arRes;
        }

        return $result;
    }

    /**
     * Обобщенный метод для получения в БД по значению поля X
     * значения поля Y (сопоставление)
     *
     * @param $table
     * @param $field
     * @param $value
     * @param $outField
     * @return false|int
     * @throws SqlQueryException
     */
    public static function QueryCorrelatedID($table, $field, $value, $outField)
    {
        $rows = static::FetchDdRows($table, $field, $value, [$outField], 1);

        if($result = $rows[0]) {
            return intval($result[$outField]);
        }

        return false;
    }

    /**
     * Найти пользователя (контргента) по XML_ID (Ид)
     * -> ID в базе
     *
     * @param $userXML_ID
     */
    public static function UserXMLIDToID($userXML_ID)
    {
        return static::QueryCorrelatedID('b_user', 'XML_ID', $userXML_ID, 'ID');
    }

    /**
     * Найти категорию (группу) по XML_ID (Ид)
     * -> ID в базе
     *
     * @param $userXML_ID
     */
    public static function ProductXMLIDToID($userXML_ID)
    {
        return static::QueryCorrelatedID('b_iblock_element', 'XML_ID', $userXML_ID, 'ID');
    }

    /**
     * Найти категорию (группу) по XML_ID (Ид)
     * -> ID в базе
     *
     * @param $userXML_ID
     */
    public static function CategoryXMLIDToID($userXML_ID)
    {
        return static::QueryCorrelatedID('b_iblock_section', 'XML_ID', $userXML_ID, 'ID');
    }

    /**
     * Найти категорию (группу) по XML_ID (Ид)
     * -> ID в базе
     *
     * @param $userXML_ID
     */
    public static function CatalogPriceXMLIDToID($userXML_ID)
    {
        return static::QueryCorrelatedID('b_catalog_group', 'XML_ID', $userXML_ID, 'ID');
    }

    /**
     * Вывод информации для отладки
     * @param string $text
     * @param int $tab
     */
    public static function _DebugPrint(string $text, int $tab = 0)
    {
        if (defined('DEBUGPRINT_ENABLED') && DEBUGPRINT_ENABLED) {
            $tabs = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $tab);
            echo $tabs . $text . PHP_EOL . '<br/>' . PHP_EOL;
        }
    }
    /**
     * Выбросить исключение или иным образом завалить импорт.
     *
     * @param string $message
     * @param \CSaleOrderLoader|null $loader
     */
    public static function _FailImport(string $message, \CSaleOrderLoader $loader = null)
    {
        if ($loader) {
            $loader->strError .= "\n" . $message;
        }
    }
}