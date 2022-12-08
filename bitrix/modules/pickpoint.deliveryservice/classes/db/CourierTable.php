<?php
namespace PickPoint\DeliveryService;

use \Bitrix\Main\Entity;

/**
 * Class CourierTable
 * @package PickPoint\DeliveryService
 */
class CourierTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'ppds_courier';
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
			new Entity\StringField('ORDER_NUMBER', 
				array(					
				)
			),
			new Entity\StringField('IKN', 
				array(
					'required' => true,	
				)
			),			
			new Entity\StringField('CITY', 
				array(
					'required' => true,					
				)
			),
			new Entity\IntegerField('CITY_LINK', 
				array(					
					'required' => true,	
					'default_value' => 0,
				)
			),			
			new Entity\TextField('ADDRESS',
				array(
					'required' => true,						
				)
			),
			new Entity\StringField('FIO', 
				array(
					'required' => true,					
				)
			),
			new Entity\StringField('PHONE', 
				array(
					'required' => true,					
				)
			),
			new Entity\DatetimeField('DATE', 
				array(
					'required' => true,					
				)
			),
			new Entity\IntegerField('TIME_START', 
				array(					
					'required' => true,	
					'default_value' => 0,
				)
			),					
			new Entity\IntegerField('TIME_END', 
				array(					
					'required' => true,	
					'default_value' => 0,
				)
			),
			new Entity\IntegerField('NUMBER', 
				array(					
					'required' => true,	
					'default_value' => 1,
				)
			),
            new Entity\IntegerField('WEIGHT', 
				array(					
					'required' => true,	
					'default_value' => 1,
				)
			), 
			new Entity\TextField('COMMENT', 
				array(										
				)
			),     
        );
    }
}