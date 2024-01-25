<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\SystemException;
use Enterego\contragents\EnteregoContragents;
use Enterego\ORM\EnteregoORMContragentsTable;

class EnteregoContragentExchange

{
    public int $ID_CONTRAGENT = 0;
    public int $STATUS_CONTRAGENT;
    public string $STATUS_VIEW;
    public string $TYPE = 'fiz';
    public string $NAME_ORGANIZATION = '';
    public string $INN = 'Не указан';
    public string $RASCHET_CHET = '';
    public string $ADDRESS = 'Не указан';
    public string $BIC;
    public string $BANK;
    public string $PHONE_COMPANY = '';
    public string $EMAIL;
    public string $DATE_INSERT;
    public string $DATE_UPDATE;
    public string $XML_ID;

    /**
     * Get contragent for xml_id
     * @param string $xml_id
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function loadContragentXMLId(string $xml_id)
    {

        if (!empty($xml_id)) {
            $resultSelect = EnteregoORMContragentsTable::getList(
                array(
                    'select' => array('ID_CONTRAGENT'),
                    'filter' => array('XML_ID' => $xml_id),
                )
            )->fetch();

            if (!empty($resultSelect)) {
                $this->ID_CONTRAGENT = (int)$resultSelect['ID_CONTRAGENT'];
            }
        }
    }

    public function saveContragentDB(): ArgumentException|ObjectPropertyException|UpdateResult|SystemException|\Exception|array
    {

        if (!empty($this->XML_ID)) {
            try {
                $saveDB =  EnteregoORMContragentsTable::update(
                    array('XML_ID' => $this->XML_ID),
                    array(
                        'NAME_ORGANIZATION' => $this->NAME_ORGANIZATION,
                        'STATUS_VIEW' => $this->STATUS_VIEW,
                        'ADDRESS' => $this->ADDRESS,
                        'TYPE' => $this->TYPE,
                        'INN' => $this->INN,
                        'RASCHET_CHET' => $this->RASCHET_CHET,
                        'BIC' => $this->BIC,
                        'BANK' => $this->BANK,
                        'PHONE_COMPANY' => $this->PHONE_COMPANY,
                        'EMAIL' => $this->EMAIL,
                        'STATUS_CONTRAGENT' => $this->STATUS_CONTRAGENT,
                    )
                );
            } catch (\Exception $e) {
                $saveDB = $e;
            }

        } else {
            try {
                $saveDB = EnteregoContragents::addContragent(
                    0,
                    array(
                        'NAME_ORGANIZATION' => $this->NAME_ORGANIZATION,
                        'STATUS_VIEW' => $this->STATUS_VIEW,
                        'ADDRESS' => $this->ADDRESS,
                        'TYPE' => $this->TYPE,
                        'INN' => $this->INN,
                        'RASCHET_CHET' => $this->RASCHET_CHET,
                        'BIC' => $this->BIC,
                        'BANK' => $this->BANK,
                        'PHONE_COMPANY' => $this->PHONE_COMPANY,
                        'EMAIL' => $this->EMAIL,
                        'STATUS_CONTRAGENT' => $this->STATUS_CONTRAGENT,
                    )
                );
            } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
                $saveDB = $e;
            }

        }

        return $saveDB;
    }

}