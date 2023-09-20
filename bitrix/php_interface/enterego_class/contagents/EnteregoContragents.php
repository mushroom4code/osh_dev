<?php

namespace Enterego\contagents;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Enterego\ORM\EnteregoORMContragentsTable;
use Enterego\ORM\EnteregoORMRelationshipUserContragentsTable;

class EnteregoContragents
{
    /**
     * @param $user_id
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function get_contragents_by_user_id($user_id): array
    {
        $result = [];
        $ids_new = [];
        $resultUserRelishenships = EnteregoORMRelationshipUserContragentsTable::getList(
            array(
                'select' => array(
                    'ID_CONTRAGENT',
                ),
                'filter' => array(
                    'STATUS' => true,
                    'USER_ID' => $user_id
                ),
            )
        );
        while ($ids_str = $resultUserRelishenships->fetch()) {
            $ids_new[] = $ids_str['ID_CONTRAGENT'];
        }

        $resultSelect = EnteregoORMContragentsTable::getList(
            array(
                'select' => array('*'),
                'filter' => array(
                    'STATUS_CONTRAGENT' => true,
                    "@ID_CONTRAGENT" => $ids_new
                ),
            )
        );
        if ($resultSelect) {
            while ($contargent = $resultSelect->fetch()) {
                $result[] = $contargent;
            }
        }
        return $result;
    }

    public static function add_contragent($user_id)
    {
        $inn = '2242434';
        $phone = 'r4343443434344';
        $name = 'test 4';;
        $result = 'Такой контрагент уже существует';


        $resultSelect = EnteregoORMContragentsTable::getList(
            array(
                'select' => array('ID_CONTRAGENT'),
                'filter' => array(
                    'INN' => $inn,
                    "PHONE_COMPANY" => $phone,
                    "NAME_ORGANIZATION" => $name
                ),
            )
        );
        if (empty($resultSelect->fetch()[0])) {
            $addResult = EnteregoORMContragentsTable::add(
                array(
                    'INN' => $inn,
                    'PHONE_COMPANY' => $phone,
                    "NAME_ORGANIZATION" => $name
                )
            );
            if ($addResult->isSuccess()) {
                $addResultRel = EnteregoORMRelationshipUserContragentsTable::add(array(
                    'ID_CONTRAGENT' => $addResult->getId(),
                    'USER_ID' => $user_id,
                ));
                $result = $addResultRel->isSuccess() ? 'Ждите подтверждения связей' : 'Вы не смогли добавить контрагента';
            }
        }

        return $result;
    }
}