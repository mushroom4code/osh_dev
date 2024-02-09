<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\Entity\UserImportBase;
use Bitrix\Sale\Result;
use COption;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/lib/exchange/importonecpackage.php');

final class ImportOneCContragent extends ImportOneCBase
{

    /**
     * @param UserImportBase[] $items
     * @return Result
     */
    protected function import(array $items): Result
    {
        $result = new Result();
        $contragent = new EnteregoContragentExchange();
        $date = COption::GetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS')
            ?? ConvertTimeStamp(false, "FULL");
        $dateInsertUpdate = DateTime::createFromUserTime($date);

        if (!empty($items)) {
            $contragent->STATUS_CONTRAGENT = $items['СтатусКонтрагента'] == 'true' ? 1 : 0;
            $contragent->DATE_UPDATE = $dateInsertUpdate;
            $contragent->DATE_INSERT = $dateInsertUpdate;
            $contragent->STATUS_VIEW = $items['СтатусКонтрагента'] == 'true' ? 'Активен' : 'Ожидает подтверждения';
            $contragent->TYPE = $items['ТипКонтрагента'] ?? 'fiz';
            $contragent->NAME_ORGANIZATION = $items['ПолноеНаименование'] ?? 'Имя не заполнено';
            $contragent->INN = $items['ИНН'] ?? 'ИНН не заполнено';
            $contragent->ADDRESS = (string)$items['АдресРегистрации']['Представление'][0]['#'] ?? '';
            $contragent->EMAIL = (string)$items['Контакты']['Контакт'][0]['#'] ?? '';

            if (!empty($items['Контакты']['Контакт'])) {
                foreach ($items['Контакты']['Контакт'] as $contact) {
                    if ($contact['#']['Тип'][0]['#'] === 'Телефон рабочий') {
                        $contragent->PHONE_COMPANY = $contact['#']['Значение'][0]['#'] ?? '';
                    }
                    if ($contact['#']['Тип'][0]['#'] === 'Электронная почта') {
                        $contragent->EMAIL = $contact['#']['Значение'][0]['#'] ?? '';
                    }
                }
            }

            if (!empty($items['РасчетныеСчета']['РасчетныйСчет'])) {
                foreach ($items['РасчетныеСчета']['РасчетныйСчет'] as $bank) {
                    $contragent->RASCHET_CHET = $bank['#']['НомерСчета'][0]['#'] ?? '';
                    $contragent->BANK = $bank['#']['Банк'][0]['#']['Наименование'][0]['#'] ?? '';
                    $contragent->BIC = $bank['#']['Банк'][0]['#']['БИК'][0]['#'] ?? '';
                }
            }
            $contragent->XML_ID = $items['Ид'];
            $contragent->saveContragentDB();
        }

        return $result;
    }

    /**
     * @param $typeId
     * @return int
     */
    protected function resolveOwnerEntityTypeId($typeId): int
    {
        return  EntityType::UNDEFINED;
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