<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Result;

class EnteregoImport extends ImportOneCBase
{

    /**
     * @param UserImportBase[] $items
     * @return mixed
     */
    protected function import(array $items)
    {
        $result = new Result();
        $companyObject = new EnteregoCompanyExchange();

        $companyObject->loadCompanyByXMLId((string)$items['Ид']);
        if (!empty($items['Контакты']['Контакт'])) {
            foreach ($items['Контакты']['Контакт'] as $item => $tel) {
                if ($tel['#']['Тип'][0]['#'] === 'Телефон рабочий') {
                    $companyObject->PHONE_COMPANY = $tel['#']['Значение'][0]['#'];
                }
            }
        }
        if (!empty($items['Адрес']['Представление'][0]['#'])) {
            $companyObject->ADDRESS = (string)$items['Адрес']['Представление'][0]['#'];
        }
        $companyObject->DATE_EDIT = date(DATE_ATOM);
        $companyObject->NAME_COMP = (string)$items['Наименование'];
        $companyObject->ARCHIVED = $items['КомпанияАрхивирована'] == 'true' ? 1 : 0;
        $companyObject->XML_ID = (string)$items['Ид'];

        $companyObject->saveCompanyDB();

        $result->isSuccess();

        return $result;
    }

    /**
     * @param $typeId
     * @return int
     */
    protected
    function resolveOwnerEntityTypeId($typeId): int
    {
        return OneC\DocumentType::UNDEFINED;
    }

    /**
     * @param array $fields
     * @return array
     */
    protected
    function resolveDocumentTypeId(array $fields): array
    {
        return $fields;
    }

    /**
     * @param array $rawFields
     * @return Result
     */
    public
    function parse(array $rawFields): Result
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
    protected
    function convert(array $items): Result
    {
        $result = new Result();
        $result->setData($items);
        return $result;
    }

    /**
     * @param ImportBase[] $items
     * @return Result
     */
    protected
    function logger(array $items): Result
    {
        $result = new Result();
        $result->setData($items);
        return $result;

    }
}