<?php

namespace Bitrix\Sale\Exchange;

use CUser;
use Enterego\ORM\EnteregoORMContragentsTable;
use Enterego\ORM\EnteregoORMRelationshipUserContragentsTable;

class EnteregoUserExchange
{

    public int $USER_ID = 0;
    public string $NAME = 'Пользователь';
    public string $EMAIL = '';
    public string $PERSONAL_PHONE = '';
    public array $contragents_user = [];
    public array $company_user = [];
    public string $LOGIN = '';
    public $ID = 0;
    public int $STATUS = 0;
    public \Bitrix\Main\Type\DateTime $TIMESTAMP_X;

    /**
     * Set role for user - contragent
     */
    protected function setRoleUserContragent()
    {
        $idsContr = [];
        $resultContragents = EnteregoORMContragentsTable::getList(
            array(
                'select' => array(
                    'ID_CONTRAGENT', 'XML_ID'
                ),
                'filter' => array(
                    '@XML_ID' => array_keys($this->contragents_user)
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
                        'USER_ID' => $this->ID,
                        '@ID_CONTRAGENT' => array_keys($idsContr)
                    ),
                )
            )->fetchAll();

            if (!empty($resultUserRelationships)) {

                foreach ($resultUserRelationships as $relation) {

                    $xml_id = $idsContr[$relation['ID_CONTRAGENT']]['XML_ID'];
                    EnteregoORMRelationshipUserContragentsTable::update(
                        $relation['ID'],
                        ['STATUS' => $this->contragents_user[$xml_id]['STATUS']]
                    );

                }
            } else {
                foreach ($idsContr as $contr) {
                    if ($this->contragents_user[$contr['XML_ID']]) {
                        EnteregoORMRelationshipUserContragentsTable::add([
                            'USER_ID' => $this->ID,
                            'ID_CONTRAGENT' => $contr['ID_CONTRAGENT'],
                            'STATUS' => $this->contragents_user[$contr['XML_ID']]['STATUS']
                        ]);
                    }
                }
            }
        }
    }


    /**
     *Save user params in DB And Create new user
     */
    public function saveUserDB()
    {
        $user = new CUser();
        $getUser = $user->GetById($this->ID)->Fetch();
         //        TODO - получение и проверка пользователя
//        1 - если
        if (!empty($getUser)) {
            $userIdRelation = $getUser['ID'];
        } else {
            $userOnXml = $user->GetList('', '', ['XML_ID' => $this->ID], ['FIELDS' => ['ID']])->Fetch();
            $userIdRelation = $userOnXml['ID'];
        }

        if (!empty($userIdRelation)) {
            $result = $user->Update($userIdRelation, [
                'TIMESTAMP_X' => $this->TIMESTAMP_X,
                'PERSONAL_NOTES' => 'Обновлена связь между пользователем и контр-том со стороны обмена  '
                    . ConvertTimeStamp(false, "FULL")
            ]);

        } else {
            $login = $this->PERSONAL_PHONE;
            if (empty($this->PERSONAL_PHONE)) {
                $login = $this->EMAIL;
            }
            $password = CUser::GeneratePasswordByPolicy([6]);
            $checkword = randString(8);
            $arFields = array(
                "EMAIL" => $this->EMAIL,
                "LOGIN" => $login,
                "NAME" => $this->NAME,
                "LAST_NAME" => $this->NAME,
                "ACTIVE" => "Y",
                "TIMESTAMP_X" => $this->TIMESTAMP_X,
                "GROUP_ID" => array(6),
                "PASSWORD" => $password,
                "CONFIRM_PASSWORD" => $password,
                "CONFIRM_CODE" => $checkword,
                "LID" => SITE_ID,
                'XML_ID' => $this->ID,
                'PERSONAL_PHONE' => $this->PERSONAL_PHONE,
                'PERSONAL_NOTES' => 'Обновлена связь между пользователем и контр-том со стороны обмена  '
                    . ConvertTimeStamp(false, "FULL")
            );

            $result = $user->Add($arFields);
            $this->ID = $result;
        }

        if ($result !== false) {
            if (!empty($this->contragents_user)) {
                $this->setRoleUserContragent();
            }
        }

    }

}