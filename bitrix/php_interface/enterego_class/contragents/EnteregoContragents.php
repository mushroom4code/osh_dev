<?php

namespace Enterego\contragents;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CUser;
use Enterego\ORM\EnteregoORMContragentsTable;
use Enterego\ORM\EnteregoORMRelationshipUserContragentsTable;

class EnteregoContragents
{

    /**
     * @param int $user_id
     * @param int $contragent_id
     * @return array|string[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function setRelationShip(int $user_id = 0, int $contragent_id = 0): array
    {
        $result = [];
        $res = EnteregoORMRelationshipUserContragentsTable::getList(
            array(
                'select' => array(
                    'ID_CONTRAGENT',
                ),
                'filter' => array(
                    'USER_ID' => $user_id,
                    'ID_CONTRAGENT' => $contragent_id
                ),
            )
        )->fetch();

        $resultSelect = EnteregoORMContragentsTable::getList(
            array(
                'select' => array('ID_CONTRAGENT'),
                'filter' => array(
                    "ID_CONTRAGENT" => $contragent_id
                ),
            )
        )->fetch();

        if (empty($res) && !empty($resultSelect)) {

            $addResultRel = EnteregoORMRelationshipUserContragentsTable::add(array(
                'ID_CONTRAGENT' => $contragent_id,
                'USER_ID' => $user_id,
            ));
// TODO обновить дату связи
            $user = new CUser();
            $user->Update($user_id, ['PERSONAL_NOTES' => 'Обновлена связь между пользователем и контр-том '
                    . ConvertTimeStamp(false, "FULL")]
            );

            $result = $addResultRel->isSuccess() ?
                ['success' => 'Ожидайте подтверждения связи'] :
                ['error' => 'Вы не смогли запросить связь - попробуйте еще раз или обратитесь к менеджеру'];
        }
        return $result;
    }

    /**
     * @param int $user_id
     * @param array[] $filters
     * @param array $filterRelation
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getContragentsByUserId(int $user_id = 0, array $filters = [],array $filterRelation = []): array
    {
        $result = [];

        if ($user_id !== 0) {

            $ids_new = [];
            $arData = [];
            $filterRelation['USER_ID'] = $user_id;
            $resultUserRelationships = EnteregoORMRelationshipUserContragentsTable::getList(
                array(
                    'select' => array(
                        'ID_CONTRAGENT', 'STATUS'
                    ),
                    'filter' => $filterRelation,
                )
            );

            if (!empty($resultUserRelationships)) {

                while ($ids_str = $resultUserRelationships->fetch()) {
                    $ids_new[] = $ids_str['ID_CONTRAGENT'];
                    $arData[$ids_str['ID_CONTRAGENT']] = $ids_str['STATUS'];
                }

                if (!empty($ids_new)) {
                    $filters["@ID_CONTRAGENT"] = $ids_new;

                    $resultSelect = EnteregoORMContragentsTable::getList(
                        array(
                            'select' => array('*'),
                            'filter' => $filters,
                        )
                    );

                    if (!empty($resultSelect)) {

                        while ($contArgent = $resultSelect->fetch()) {
                            // TODO придумать другую проверку статуса
                            $contArgent['STATUS_VIEW'] = 'Ожидает подтверждения';

                            if ($contArgent['STATUS_CONTRAGENT'] == 1 && $arData[$contArgent['ID_CONTRAGENT']] == 1) {
                                $contArgent['STATUS_VIEW'] = 'Активен';
                            }

                            $result[] = $contArgent;
                        }
                    }
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
        $result = [];
        $resultSelect = EnteregoORMContragentsTable::getList(
            array(
                'select' => array("*"),
                'filter' => array('INN' => $arData['INN'] ?? ''),
            )
        )->fetch();

        if (empty($resultSelect)) {
            $addResult = EnteregoORMContragentsTable::add($arData);

            if ($addResult->isSuccess()) {
                $newId = $addResult->getId();
                EnteregoORMContragentsTable::update(
                    array('ID_CONTRAGENT' => $newId),
                    array('XML_ID' => uniqid('contrxml_'))
                );

                $addResultRel = $user_id !== 0 ? EnteregoORMRelationshipUserContragentsTable::add(
                    array(
                        'ID_CONTRAGENT' => $newId,
                        'USER_ID' => $user_id,
                    )
                ) : false;

                $user = new CUser();
                $user->Update($user_id, ['PERSONAL_NOTES' => 'Обновлена связь между пользователем и контр-том '
                        . ConvertTimeStamp(false, "FULL")]
                );

                $result = $addResultRel->isSuccess() ?
                    ['success' => 'Ожидайте подтверждения связи'] :
                    ['error' => 'Вы не смогли добавить контрагента - попробуйте еще раз'];

            }
        } else {
            $result = [
                'error' => [
                    'code' => 'Контрагент с такими данными уже существует!',
                    'item' => $resultSelect
                ]
            ];
        }

        return $result;
    }

    /**
     * @param array $arData
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function addOrUpdateContragent(array $arData = []): bool
    {
        $result = false;
        $resultSelect = EnteregoORMContragentsTable::getList(
            array(
                'select' => array('ID_CONTRAGENT', 'XML_ID'),
                'filter' => array('XML_ID' => $arData['XML_ID']),
            )
        )->fetch();

        if (empty($resultSelect)) {
            $addResult = EnteregoORMContragentsTable::add($arData);
            if ($addResult->isSuccess()) {
                $result = true;
            }
        } else {
            $newArData = $arData;
            unset($newArData['XML_ID']);
            unset($newArData['DATE_INSERT']);
            $updateResult = EnteregoORMContragentsTable::update(array('ID_CONTRAGENT' => $resultSelect['ID_CONTRAGENT']), $newArData);
            if ($updateResult->isSuccess()) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param int $user_id
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getActiveContragentForUser(int $user_id = 0): bool
    {
        $result = false;
        $ids_new = [];
        $resultUserRelationships = EnteregoORMRelationshipUserContragentsTable::getList(
            array(
                'select' => array(
                    "ID_CONTRAGENT",
                ),
                'filter' => array(
                    "USER_ID" => $user_id,
                    "STATUS" => 1
                ),
            )
        );

        if (!empty($resultUserRelationships)) {
            while ($ids_str = $resultUserRelationships->fetch()) {
                $ids_new[] = $ids_str['ID_CONTRAGENT'];
            }
            if (!empty($ids_new)) {
                $resultSelect = EnteregoORMContragentsTable::getList(
                    array(
                        'select' => array('ID_CONTRAGENT'),
                        'filter' => array(
                            "@ID_CONTRAGENT" => $ids_new,
                            "STATUS_CONTRAGENT" => 1
                        ),
                    )
                );
                if (!empty($resultSelect->fetch())) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $contr_id
     * @return false|array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getContrAgentNameOnOrder(int $contr_id = 0): false|array
    {
        $result = [];
        if ($contr_id !== 0) {
            $result = EnteregoORMContragentsTable::getList(
                array(
                    'select' => array('ID_CONTRAGENT', 'NAME_ORGANIZATION'),
                    'filter' => array(
                        "ID_CONTRAGENT" => $contr_id,
                        "STATUS_CONTRAGENT" => 1
                    ),
                )
            )->fetch();
        }
        return $result;
    }
}