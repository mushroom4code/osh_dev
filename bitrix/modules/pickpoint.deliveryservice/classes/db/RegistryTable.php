<?php
namespace PickPoint\DeliveryService;

use \Bitrix\Main\Entity;

/**
 * Class RegistryTable
 * @package PickPoint\DeliveryService
 */
class RegistryTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'ppds_registry';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID',
				array(
					'primary' => true,
					'autocomplete' => true
				)
			),	
			new Entity\StringField('REGISTRY_NUMBER',
				array(					
				)
			),
			new Entity\DatetimeField('DATE',
				array(
					'required' => true,
				)
			),
			new Entity\StringField('GETTING_TYPE',
				array(
					'required' => true,
				)
			),
			new Entity\StringField('IKN',
				array(
					'required' => true,
				)
			),			
			new Entity\StringField('TRANSFER_CITY',
				array(
					'required' => true,
				)
			),
			new Entity\IntegerField('TRANSFER_CITY_LINK',
				array(					
					'required' => true,
					'default_value' => 0,
				)
			),		
			new Entity\StringField('FILENAME',
				array(
					'required' => true,
				)
			),  
        );
    }
}