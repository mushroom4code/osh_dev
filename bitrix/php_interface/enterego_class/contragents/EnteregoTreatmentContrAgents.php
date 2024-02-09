<?php namespace Enterego\contragents;


use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\EnteregoContragentExchange;

use COption;

class EnteregoTreatmentContrAgents {
    public function import(array $items)
    {
        $result = false;
        $contragent = new EnteregoContragentExchange();
        $date = COption::GetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS')
            ?? ConvertTimeStamp(false, "FULL");
        $dateInsertUpdate = DateTime::createFromUserTime($date);

        if (!empty($items)) {
            $contragent->STATUS_CONTRAGENT = $items['СтатусКонтрагента'][0]['#'] == 'true' ? 1 : 1;
            $contragent->DATE_UPDATE = $dateInsertUpdate;
            $contragent->DATE_INSERT = $dateInsertUpdate;
            $contragent->STATUS_VIEW = $items['СтатусКонтрагента'][0]['#'] == 'true' ? 'Активен' : 'Активен';
            $contragent->TYPE = $items['ТипКонтрагента'][0]['#'] ?? 'fiz';
            $contragent->NAME_ORGANIZATION = $items['ПолноеНаименование'][0]['#'] ?? 'Имя не заполнено';
            $contragent->INN = $items['ИНН'][0]['#'] ?? 'ИНН не заполнено';
            $contragent->ADDRESS = (string)$items['АдресРегистрации'][0]['#']['Представление'][0]['#'] ?? '';
            $contragent->EMAIL = (string)$items['Контакты'][0]['#']['Контакт'][0]['#'] ?? '';

            if (!empty($items['Контакты'][0]['#']['Контакт'])) {
                foreach ($items['Контакты'][0]['#']['Контакт'] as $contact) {
                    if ($contact['#']['Тип'][0]['#'] === 'Телефон рабочий') {
                        $contragent->PHONE_COMPANY = $contact['#']['Значение'][0]['#'] ?? '';
                    }
                    if ($contact['#']['Тип'][0]['#'] === 'Электронная почта') {
                        $contragent->EMAIL = $contact['#']['Значение'][0]['#'] ?? '';
                    }
                }
            }

            if (!empty($items['РасчетныеСчета'][0]['#']['РасчетныйСчет'])) {
                foreach ($items['РасчетныеСчета'][0]['#']['РасчетныйСчет'] as $bank) {
                    $contragent->RASCHET_CHET = $bank['#']['НомерСчета'][0]['#'] ?? '';
                    $contragent->BANK = $bank['#']['Банк'][0]['#']['Наименование'][0]['#'] ?? '';
                    $contragent->BIC = $bank['#']['Банк'][0]['#']['БИК'][0]['#'] ?? '';
                }
            }

            $contragent->XML_ID = $items["Ид"][0]['#'];
            $contragent->saveContragentDB();
            // TODO - обработка на связь с юзерами

            $result = true;
        }

        return $result;
    }
}