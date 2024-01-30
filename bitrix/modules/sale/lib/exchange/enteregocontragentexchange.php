<?php

namespace Bitrix\Sale\Exchange;

use Bitrix\Main\Type\DateTime;
use Enterego\contragents\EnteregoContragents;

class EnteregoContragentExchange

{
    public int $ID_CONTRAGENT = 0;
    public int $STATUS_CONTRAGENT = 0;
    public string $STATUS_VIEW = '';
    public string $TYPE = 'fiz';
    public string $NAME_ORGANIZATION = '';
    public string $INN = 'Не указан';
    public string $RASCHET_CHET = '';
    public string $ADDRESS = 'Не указан';
    public string $BIC = '';
    public string $BANK = '';
    public string $PHONE_COMPANY = '';
    public string $EMAIL = '';
    public datetime $DATE_INSERT;
    public datetime $DATE_UPDATE;
    public string $XML_ID;


    public function saveContragentDB()
    {
        $saveDB = false;

        if (!empty($this->XML_ID)) {
            $saveDB = EnteregoContragents::addOrUpdateContragent(
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
                    'XML_ID' => $this->XML_ID,
                    'DATE_UPDATE' => $this->DATE_UPDATE,
                    'DATE_INSERT' => $this->DATE_INSERT,
                )
            );
        }

        return $saveDB;
    }

}