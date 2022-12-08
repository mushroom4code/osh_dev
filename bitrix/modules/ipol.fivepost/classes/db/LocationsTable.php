<?
namespace Ipol\Fivepost;

use \Bitrix\Main\ORM\Data\DataManager;
use \Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Fields\DatetimeField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class LocationsTable
 * @package Ipol\Fivepost
 */
class LocationsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ipol_fivepost_locations';
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
                'LOCALITY_FIAS_CODE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateLocalityFiasCode'],
                ]
            ),
            new StringField(
                'BITRIX_CODE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateBitrixCode'],
                ]
            ),
            new BooleanField(
                'SYNC_IS_UPDATABLE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new DatetimeField(
                'SYNC_LAST_DATE',
                [
                    'required' => true,
                ]
            ),
            new StringField(
                'SYNC_HASH',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateSyncHash'],
                ]
            ),
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
     * Returns validators for BITRIX_CODE field.
     *
     * @return array
     */
    public static function validateBitrixCode()
    {
        return [
            new LengthValidator(null, 100),
        ];
    }

    /**
     * Returns validators for SYNC_HASH field.
     *
     * @return array
     */
    public static function validateSyncHash()
    {
        return [
            new LengthValidator(null, 32),
        ];
    }

    /**
     * Return number of rows with some data
     *
     * @return int
     */
    public static function getDataCount()
    {
        $result = self::getList(['select' => ['CNT'], 'runtime' => [new ExpressionField('CNT', 'COUNT(*)')]])->fetch();
        return $result['CNT'];
    }
}