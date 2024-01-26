<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Result;

class EnteregoUser extends ImportOneCBase
{

    /**
     * @param UserImportBase[] $items
     * @return Result
     */
    protected function import(array $items)
    {
        $result = new Result();
        $user_object = new EnteregoUserExchange();
        $user_object->XML_ID = (string)$items['Ид'];
        $user_object->loadUserXMLId();
        $user_object->NAME = (string)$items['Имя'];;
        $user_object->LOGIN = (string)$items['Логин'];
        $user_object->EMAIL = (string)$items['Почта'];
        $user_object->PERSONAL_PHONE = (string)$items['ТелефонРабочий'];

        if ($items['КонтрагентыПользователя']) {
            foreach ($items['КонтрагентыПользователя'] as $contragent) {
                $user_object->contragents_user[] = $contragent['Ид'];
            }
        }

        $user_object->saveUserDB();

        return $result;
    }

    /**
     * @param $typeId
     * @return int
     */
    protected function resolveOwnerEntityTypeId($typeId): int
    {
        return OneC\DocumentType::USER_PROFILE;
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