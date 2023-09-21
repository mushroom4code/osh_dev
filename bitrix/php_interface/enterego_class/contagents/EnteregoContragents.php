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
     * @param int $user_id
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getContragentsByUserId(int $user_id = 0): array
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

    /**
     * @param int $user_id
     * @param array $arData
     * @return string[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function addContragent(int $user_id = 0, array $arData = []): array
    {
        $result = ['error' => 'Такой контрагент уже существует'];
        $resultSelect = EnteregoORMContragentsTable::getList(
            array(
                'select' => array('ID_CONTRAGENT'),
                'filter' => array($arData),
            )
        );

        if (empty($resultSelect->fetch())) {

            $addResult = EnteregoORMContragentsTable::add(
                array(
                    'INN' => $arData['INN'],
                    'PHONE_COMPANY' => $arData['PHONE_COMPANY'],
                    "NAME_ORGANIZATION" => $arData['NAME_ORGANIZATION'],
                )
            );

            if ($addResult->isSuccess()) {
                $addResultRel = EnteregoORMRelationshipUserContragentsTable::add(array(
                    'ID_CONTRAGENT' => $addResult->getId(),
                    'USER_ID' => $user_id,
                ));
                $result = $addResultRel->isSuccess() ? ['success' => 'Ждите подтверждения связей'] :
                    ['error' => 'Вы не смогли добавить контрагента'];
            }


        }

        return $result;
    }

    /**
     * @param array $filter
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getContragentByFilter(array $filter = []): bool
    {
        $result = false;
        if (!empty($filter)) {
            $resultSelect = EnteregoORMContragentsTable::getList(
                array(
                    'select' => array('ID_CONTRAGENT'),
                    'filter' => array($filter),
                )
            );

            if (empty($resultSelect->fetch())) {
                $result = true;
            }
        }
        return $result;
    }
}