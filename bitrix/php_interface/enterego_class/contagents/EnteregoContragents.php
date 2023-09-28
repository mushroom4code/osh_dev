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

    public const typeUric = 'uric';
    public const typeFiz = 'fiz';
    public const typeIp = 'ip';

    public static function getContragentsByUserId(int $user_id = 0): array
    {
        $result = [];
        $ids_new = [];
        $resultUserRelationships = EnteregoORMRelationshipUserContragentsTable::getList(
            array(
                'select' => array(
                    'ID_CONTRAGENT',
                ),
                'filter' => array(
                    'USER_ID' => $user_id
                ),
            )
        );

        while ($ids_str = $resultUserRelationships->fetch()) {
            $ids_new[] = $ids_str['ID_CONTRAGENT'];
        }
        if (!empty($ids_new)) {
            $resultSelect = EnteregoORMContragentsTable::getList(
                array(
                    'select' => array('*'),
                    'filter' => array(
                        "@ID_CONTRAGENT" => $ids_new
                    ),
                )
            );
            if (!empty($resultSelect)) {
                while ($contargent = $resultSelect->fetch()) {
                    $result[] = $contargent;
                }
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
        )->fetch();

        if (empty($resultSelect)) {
            $addResult = EnteregoORMContragentsTable::add($arData);

            if ($addResult->isSuccess()) {
                $addResultRel = EnteregoORMRelationshipUserContragentsTable::add(array(
                    'ID_CONTRAGENT' => $addResult->getId(),
                    'USER_ID' => $user_id,
                ));
                $result = $addResultRel->isSuccess() ?
                    ['success' => 'Ожидайте подтверждения связи'] :
                    ['error' => 'Вы не смогли добавить контрагента - попробуйте еще раз'];
            }
        } else {
            $result = ['error' => ['code' => '', 'item' => $resultSelect]];
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
    public static function getContragentByFilter(array $filter = []): bool|array
    {
        $result = false;
        if (!empty($filter)) {
            $resultSelect = EnteregoORMContragentsTable::getList(
                array(
                    'select' => array('ID_CONTRAGENT','TYPE','NAME_ORGANIZATION','PHONE_COMPANY','EMAIL','INN'),
                    'filter' => $filter,
                )
            )->fetch();

            $result = empty($resultSelect) ? true : $resultSelect;
        }
        return $result;
    }
}