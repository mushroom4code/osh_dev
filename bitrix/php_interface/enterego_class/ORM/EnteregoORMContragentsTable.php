<?php
namespace Enterego\ORM;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
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
                'NAME_ORGANIZATION',
                [
                    'title' => "Наименование ориганизации",
                ]
            ),
            new StringField(
                'INN',
                [
                    'title' => "ИНН",
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

