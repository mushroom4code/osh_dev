<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Result;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/lib/exchange/importonecpackage.php');

final class ImportOneCContragent extends ImportOneCBase
{

    /**
     * @param UserImportBase[] $items
     * @return Result
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function import(array $items)
    {
        $result = new Result();
        $contragent = new EnteregoContragentExchange();
        $contragent->loadContragentXMLId((string)$items['Ид']);
        $contragent->STATUS_CONTRAGENT = $items['СтатусКонтрагента'] == 'true' ? 1 : 0;
        $contragent->STATUS_VIEW = $items['СтатусКонтрагента'] == 'true' ? 'Активен' : 'Ожидает подтверждения';
        $contragent->TYPE = $items['ТипКонтрагента'] ?? 'fiz';
        $contragent->NAME_ORGANIZATION = (string)$items['ПолноеНаименование'];
        $contragent->INN = (string)$items['Инн'] ?? null;
        $contragent->ADDRESS = (string)$items['АдресРегистрации']['Представление'][0]['#'] ?? '';
// TODO  RASCHET_CHET BIC BANK PHONE_COMPANY EMAIL
        $contragent->EMAIL = (string)$items['Контакты']['Контакт'][0]['#'] ?? '';
        $contragent->PHONE_COMPANY = (string)$items['Контакты']['Контакт'][0]['#'] ?? '';
        $contragent->DATE_UPDATE = date(DATE_ATOM);

        $contragent->XML_ID = (string)$items['Ид'];

//        $contragent->saveContragentDB();

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