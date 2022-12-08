<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Result;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/lib/exchange/importonecpackage.php');

final class ImportOneCContragent extends ImportOneCBase
{

    /**
     * @param UserImportBase[] $items
     * @return mixed
     */
    protected function import(array $items)
    {
        $result = new Result();
        $contragent = new EnteregoContragentExchange();
        $contragent->loadContragentXMLId((string)$items['Ид']);

        $contragent->DATE_EDIT = (string)date(DATE_ATOM);
        $contragent->NAME_CONT = (string)$items['Наименование'];
        if(!empty($items['Адрес']['Представление'][0]['#'])){
            $contragent->ADDRESS = (string)$items['Адрес']['Представление'][0]['#'];
        }
        if(!empty($items['Инн'])){
            $contragent->INN = (string)$items['Инн'];
        }
        $contragent->ARCHIVED = $items['КонтрагентАрхивирован'] == 'true' ? 1 : 0;
        $contragent->CONTR_AGENT_ACTIVE = $items['ПодтверждениеКонтрагента'] == 'true' ? 1 : 0;

        $contragent->XML_ID = (string)$items['Ид'];

        $contragent->saveContragentDB();

        return $result;
    }

    /**
     * @param $typeId
     * @return int
     */
    protected function resolveOwnerEntityTypeId($typeId): int
    {
        return OneC\DocumentType::UNDEFINED;
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function resolveDocumentTypeId(array $fields): array
    {
        return $fields;
    }

    /**
     * @param array $rawFields
     * @return Result
     */
    public function parse(array $rawFields): Result
    {
        $result = new Result();
        $list = array();

        foreach ($rawFields[0] as $key => $raw) {
            $list[$key] = $raw[0]['#'];
        }

        $result->setData($list);

        return $result;
    }

    /**
     * @param array $items
     * @return Result
     */
    protected function convert(array $items): Result
    {
        $result = new Result();
        $result->setData($items);
        return $result;
    }

    /**
     * @param ImportBase[] $items
     * @return Result
     */
    protected function logger(array $items): Result
    {
        $result = new Result();
        $result->setData($items);
        return $result;

    }
}