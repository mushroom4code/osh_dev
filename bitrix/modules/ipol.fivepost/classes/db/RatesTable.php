<?
namespace Ipol\Fivepost;

use \Bitrix\Main\ORM\Data\DataManager;
use \Bitrix\Main\ORM\Fields\FloatField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class RatesTable
 * @package Ipol\Fivepost
 */
class RatesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ipol_fivepost_rates';
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
            new IntegerField(
                'POINT_ID',
                [
                    'default' => 0,
                ]
            ),
            new StringField(
                'ZONE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateZone'],
                ]
            ),
            new StringField(
                'RATE_TYPE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateRateType'],
                ]
            ),
            new StringField(
                'RATE_CURRENCY',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateRateCurrency'],
                ]
            ),
            new FloatField(
                'RATE_VALUE',
                [
                    'default' => 0.0000,
                ]
            ),
            new FloatField(
                'RATE_EXTRA_VALUE',
                [
                    'default' => 0.0000,
                ]
            ),
            new FloatField(
                'VAT',
                [
                    'default' => 0.0000,
                ]
            ),
            new FloatField(
                'RATE_VALUE_WITH_VAT',
                [
                    'default' => 0.0000,
                ]
            ),
            new FloatField(
                'RATE_EXTRA_VALUE_WITH_VAT',
                [
                    'default' => 0.0000,
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
     * Returns validators for ZONE field.
     *
     * @return array
     */
    public static function validateZone()
    {
        return [
            new LengthValidator(null, 5),
        ];
    }

    /**
     * Returns validators for RATE_TYPE field.
     *
     * @return array
     */
    public static function validateRateType()
    {
        return [
            new LengthValidator(null, 50),
        ];
    }

    /**
     * Returns validators for RATE_CURRENCY field.
     *
     * @return array
     */
    public static function validateRateCurrency()
    {
        return [
            new LengthValidator(null, 3),
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
}