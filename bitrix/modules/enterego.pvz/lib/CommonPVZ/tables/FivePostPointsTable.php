<?php

namespace CommonPVZ;

use \Bitrix\Main\ORM\Data\DataManager;
use \Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Fields\DatetimeField;
use \Bitrix\Main\ORM\Fields\FloatField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class PointsTable
 * @package Ipol\Fivepost
 */
class FivePostPointsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ent_fivepost_points';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                ]
            ),
            new StringField(
                'POINT_GUID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validatePointGuid'],
                ]
            ),
            new StringField(
                'BITRIX_CODE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateBitrixCode'],
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateName'],
                ]
            ),
            new StringField(
                'PARTNER_NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validatePartnerName'],
                ]
            ),
            new StringField(
                'TYPE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateType'],
                ]
            ),
            new StringField(
                'ADDITIONAL',
                [
                    'validation' => [__CLASS__, 'validateAdditional'],
                ]
            ),
            new StringField(
                'WORK_HOURS',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateWorkHours'],
                ]
            ),
            new StringField(
                'FULL_ADDRESS',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateFullAddress'],
                ]
            ),
            new StringField(
                'ADDRESS_COUNTRY',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateAddressCountry'],
                ]
            ),
            new StringField(
                'ADDRESS_ZIP_CODE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateAddressZipCode'],
                ]
            ),
            new StringField(
                'ADDRESS_REGION',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateAddressRegion'],
                ]
            ),
            new StringField(
                'ADDRESS_REGION_TYPE',
                [
                    'validation' => [__CLASS__, 'validateAddressRegionType'],
                ]
            ),
            new StringField(
                'ADDRESS_CITY',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateAddressCity'],
                ]
            ),
            new StringField(
                'ADDRESS_CITY_TYPE',
                [
                    'validation' => [__CLASS__, 'validateAddressCityType'],
                ]
            ),
            new StringField(
                'ADDRESS_STREET',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateAddressStreet'],
                ]
            ),
            new StringField(
                'ADDRESS_HOUSE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateAddressHouse'],
                ]
            ),
            new StringField(
                'ADDRESS_BUILDING',
                [
                    'validation' => [__CLASS__, 'validateAddressBuilding'],
                ]
            ),
            new FloatField(
                'ADDRESS_LAT',
                [
                    'default' => 0,
                ]
            ),
            new FloatField(
                'ADDRESS_LNG',
                [
                    'default' => 0,
                ]
            ),
            new StringField(
                'ADDRESS_METRO_STATION',
                [
                    'validation' => [__CLASS__, 'validateAddressMetroStation'],
                ]
            ),
            new StringField(
                'LOCALITY_FIAS_CODE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateLocalityFiasCode'],
                ]
            ),
            new IntegerField(
                'MAX_CELL_WIDTH',
                [
                    'default' => 0,
                ]
            ),
            new IntegerField(
                'MAX_CELL_HEIGHT',
                [
                    'default' => 0,
                ]
            ),
            new IntegerField(
                'MAX_CELL_LENGTH',
                [
                    'default' => 0,
                ]
            ),
            new IntegerField(
                'MAX_CELL_WEIGHT',
                [
                    'default' => 0,
                ]
            ),
            new IntegerField(
                'MAX_CELL_DIMENSIONS_HASH',
                [
                    'default' => 0,
                ]
            ),
            new BooleanField(
                'RETURN_ALLOWED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new StringField(
                'PHONE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validatePhone'],
                ]
            ),
            new BooleanField(
                'CASH_ALLOWED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new BooleanField(
                'CARD_ALLOWED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new BooleanField(
                'LOYALTY_ALLOWED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new StringField(
                'EXT_STATUS',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateExtStatus'],
                ]
            ),
            new StringField(
                'DELIVERY_SL',
                [
                    'validation' => [__CLASS__, 'validateDeliverySl'],
                ]
            ),
            new StringField(
                'LASTMILEWAREHOUSE_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateLastmilewarehouseId'],
                ]
            ),
            new StringField(
                'LASTMILEWAREHOUSE_NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateLastmilewarehouseName'],
                ]
            ),
            new StringField(
                'RATE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateRate'],
                ]
            ),
        ];
    }

    /**
     * Returns validators for POINT_GUID field.
     *
     * @return array
     */
    public static function validatePointGuid()
    {
        return [
            new LengthValidator(null, 36),
        ];
    }

    /**
     * Returns validators for BITRIX_CODE field.
     *
     * @return array
     */
    public static function validateBitrixCode()
    {
        return [
            new LengthValidator(null, 36),
        ];
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for PARTNER_NAME field.
     *
     * @return array
     */
    public static function validatePartnerName()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for TYPE field.
     *
     * @return array
     */
    public static function validateType()
    {
        return [
            new LengthValidator(null, 15),
        ];
    }

    /**
     * Returns validators for ADDITIONAL field.
     *
     * @return array
     */
    public static function validateAdditional()
    {
        return [
            new LengthValidator(null, 150),
        ];
    }

    /**
     * Returns validators for WORK_HOURS field.
     *
     * @return array
     */
    public static function validateWorkHours()
    {
        return [
            new LengthValidator(null, 2000),
        ];
    }

    /**
     * Returns validators for FULL_ADDRESS field.
     *
     * @return array
     */
    public static function validateFullAddress()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for ADDRESS_COUNTRY field.
     *
     * @return array
     */
    public static function validateAddressCountry()
    {
        return [
            new LengthValidator(null, 30),
        ];
    }

    /**
     * Returns validators for ADDRESS_ZIP_CODE field.
     *
     * @return array
     */
    public static function validateAddressZipCode()
    {
        return [
            new LengthValidator(null, 10),
        ];
    }

    /**
     * Returns validators for ADDRESS_REGION field.
     *
     * @return array
     */
    public static function validateAddressRegion()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for ADDRESS_REGION_TYPE field.
     *
     * @return array
     */
    public static function validateAddressRegionType()
    {
        return [
            new LengthValidator(null, 30),
        ];
    }

    /**
     * Returns validators for ADDRESS_CITY field.
     *
     * @return array
     */
    public static function validateAddressCity()
    {
        return [
            new LengthValidator(null, 60),
        ];
    }

    /**
     * Returns validators for ADDRESS_CITY_TYPE field.
     *
     * @return array
     */
    public static function validateAddressCityType()
    {
        return [
            new LengthValidator(null, 10),
        ];
    }

    /**
     * Returns validators for ADDRESS_STREET field.
     *
     * @return array
     */
    public static function validateAddressStreet()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for ADDRESS_HOUSE field.
     *
     * @return array
     */
    public static function validateAddressHouse()
    {
        return [
            new LengthValidator(null, 15),
        ];
    }

    /**
     * Returns validators for ADDRESS_BUILDING field.
     *
     * @return array
     */
    public static function validateAddressBuilding()
    {
        return [
            new LengthValidator(null, 10),
        ];
    }

    /**
     * Returns validators for ADDRESS_METRO_STATION field.
     *
     * @return array
     */
    public static function validateAddressMetroStation()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for LOCALITY_FIAS_CODE field.
     *
     * @return array
     */
    public static function validateLocalityFiasCode()
    {
        return [
            new LengthValidator(null, 36),
        ];
    }

    /**
     * Returns validators for PHONE field.
     *
     * @return array
     */
    public static function validatePhone()
    {
        return [
            new LengthValidator(null, 30),
        ];
    }

    /**
     * Returns validators for EXT_STATUS field.
     *
     * @return array
     */
    public static function validateExtStatus()
    {
        return [
            new LengthValidator(null, 20),
        ];
    }

    /**
     * Returns validators for DELIVERY_SL field.
     *
     * @return array
     */
    public static function validateDeliverySl()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for LASTMILEWAREHOUSE_ID field.
     *
     * @return array
     */
    public static function validateLastmilewarehouseId()
    {
        return [
            new LengthValidator(null, 36),
        ];
    }

    /**
     * Returns validators for LASTMILEWAREHOUSE_NAME field.
     *
     * @return array
     */
    public static function validateLastmilewarehouseName()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for RATE field.
     *
     * @return array
     */
    public static function validateRate()
    {
        return [
            new LengthValidator(null, 2000),
        ];
    }

    /**
     * Returns point data by point ID.
     *
     * @param  int $id
     * @param  array $select
     * @return array
     */
    public static function getByPointId($id, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['ID' => $id]]))->fetch();
    }

    /**
     * Returns point data by point GUID (5Post ID).
     *
     * @param  string $guid
     * @param  array $select
     * @return array
     */
    public static function getByPointGuid($guid, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['POINT_GUID' => $guid]]))->fetch();
    }

    /**
     * Return number of rows with some data
     *
     * @param  bool $onlyActive
     * @return int
     */
    public static function getDataCount($onlyActive = true)
    {
        $params = ['select' => ['CNT'], 'runtime' => [new ExpressionField('CNT', 'COUNT(*)')]];

        if ($onlyActive)
            $params['filter'] = ['SYNC_IS_ACTIVE' => 'Y'];

        $result = self::getList($params)->fetch();
        return $result['CNT'];
    }

    public static function deleteAll() {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}