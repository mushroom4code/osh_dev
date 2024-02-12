<?php namespace Enterego\contragents;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\EnteregoContragentExchange;
use CDataXML;
use COption;
use CUser;
use Enterego\ORM\EnteregoORMContragentsTable;
use Enterego\ORM\EnteregoORMRelationshipUserContragentsTable;

class EnteregoTreatmentContrAgents
{
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
            $resContr = $contragent->saveContragentDB();

            if ($resContr) {
                $user = new CUser();
                $getUser = $user->GetById($contragent->XML_ID)->Fetch();
                $userIdRelation = $getUser['ID'];
                $contr[$contragent->XML_ID] = [
                    'ID_CONTRAGENT' => $contragent->XML_ID,
                    'INN' => $contragent->INN,
                    'STATUS' => $contragent->STATUS_CONTRAGENT
                ];

                if (empty($userIdRelation)) {
                    $userOnXml = $user->GetList('', '', ['XML_ID' => $contragent->XML_ID], ['FIELDS' => ['ID']])->Fetch();
                    $userIdRelation = $userOnXml['ID'];

                    if (empty($userOnXml)) {
                        $userOnXml = $user->GetList('', '', ['EMAIL' => $contragent->EMAIL], ['FIELDS' => ['ID']])->Fetch();
                        $userIdRelation = $userOnXml['ID'];
                    }
                }
                $this->setRoleUserContragent($userIdRelation, $contr);
            }

            $result = true;
        }

        return $result;
    }

    protected function setRoleUserContragent($user_id, $contrIds)
    {
        $idsContr = [];
        if(!empty($user_id)){
            $resultContragents = EnteregoORMContragentsTable::getList(
                array(
                    'select' => array(
                        'ID_CONTRAGENT', 'XML_ID'
                    ),
                    'filter' => array(
                        '@XML_ID' => array_keys($contrIds)
                    ),
                )
            )->fetchAll();

            if (!empty($resultContragents)) {
                foreach ($resultContragents as $contr) {
                    $idsContr[$contr['ID_CONTRAGENT']] = $contr;
                }

                $resultUserRelationships = EnteregoORMRelationshipUserContragentsTable::getList(
                    array(
                        'select' => array(
                            'ID_CONTRAGENT', 'ID'
                        ),
                        'filter' => array(
                            'USER_ID' => $user_id,
                            '@ID_CONTRAGENT' => array_keys($idsContr)
                        ),
                    )
                )->fetchAll();

                if (!empty($resultUserRelationships)) {

                    foreach ($resultUserRelationships as $relation) {

                        $xml_id = $idsContr[$relation['ID_CONTRAGENT']]['XML_ID'];
                        EnteregoORMRelationshipUserContragentsTable::update(
                            $relation['ID'],
                            ['STATUS' => $contrIds[$xml_id]['STATUS']]
                        );

                    }
                } else {
                    foreach ($idsContr as $contr) {
                        if ($contrIds[$contr['XML_ID']]) {
                            EnteregoORMRelationshipUserContragentsTable::add([
                                'USER_ID' => $user_id,
                                'ID_CONTRAGENT' => $contr['ID_CONTRAGENT'],
                                'STATUS' => $contrIds[$contr['XML_ID']]['STATUS']
                            ]);
                        }
                    }
                }
            }
        }
    }

}

function startExport(){
//    $xml = new CDataXML();
//    $xml->Load($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/enterego_class/contragents/parseContarget.xml');
//    $contrs = $xml->GetArray()['КоммерческаяИнформация']['#']['Контрагенты'][0]['#']['Контрагент'];
//    foreach ($contrs as $item){
//        $value = $item["#"];
//        $writeContr = new EnteregoTreatmentContrAgents();
//        $writeContr->import($value);
//    }
//$time = !empty($_SESSION['START_DATETIME_EXPORT']) ? $_SESSION['START_DATETIME_EXPORT'] : ConvertTimeStamp(false, "FULL");
//COption::SetOptionString('DATE_IMPORT_CONTRAGENTS', 'DATE_IMPORT_CONTRAGENTS', $time);
}