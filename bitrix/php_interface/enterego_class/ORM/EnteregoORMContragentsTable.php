<?php

namespace Enterego\ORM;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\SystemException;

/**
 * Class EnteregoORMContragents
 *
 * Fields:
 *
 **/
class EnteregoORMContragentsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ent_contagents';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new IntegerField(
                'ID_CONTRAGENT',
                [
                    'title' => "ID Контрагента",
                    'primary' => true,
                ]
            ),
            new BooleanField(
                'STATUS_CONTRAGENT',
                [
                    'title' => "Активность контрагента",
                    'default' => false,
                ]
            ),
            new StringField(
                'STATUS_VIEW',
                [
                    'title' => "Статус контрагента",
                    'default' => 'Ожидает подтверждения',
                ]
            ),
            new TextField(
                'TYPE',
                [
                    'title' => "Тип лица",
                ]
            ),
            new StringField(
                'NAME_ORGANIZATION',
                [
                    'title' => "Наименование ориганизации",
                    'validate' => function () {
                        return array(
                            function ($value) {
                                return !empty($value);
                            }
                        );
                    }
                ]
            ),
            new StringField(
                'INN',
                [
                    'title' => "ИНН",
                    'validate' => function () {
                        return array(
                            function ($value) {
                                return !empty($value);
                            }
                        );
                    }
                ]
            ),
            new StringField(
                'RASCHET_CHET',
                [
                    'title' => "Расчетный счет",
                ]
            ),
            new TextField(
                'ADDRESS',
                [
                    'title' => "Юридический адрес",
                ]
            ),
            new StringField(
                'BIC',
                [
                    'title' => "БИК",
                ]
            ),
            new StringField(
                'BANK',
                [
                    'title' => "Банк",
                ]
            ),
            new StringField(
                'PHONE_COMPANY',
                [
                    'title' => "Телефон",
                    'validate' => function () {
                        return array(
                            function ($value) {
                                return !empty($value);
                            }
                        );
                    }
                ]
            ),
            new StringField(
                'EMAIL',
                [
                    'title' => "EMAIL",
                ]
            ),
            new DatetimeField(
                'DATE_INSERT',
                [
                    'title' => "Дата создания",
                    'default' => function()
                    {
                        return new DateTime();
                    },
                ]
            ),
            new DatetimeField(
                'DATE_UPDATE',
                [
                    'title' => "Дата обновления",
                    'default' => function()
                    {
                        return new DateTime();
                    },
                ]
            ),
            new StringField(
                'XML_ID',
                [
                    'title' => "XML_ID",
                ]
            ),
        ];
    }
}

