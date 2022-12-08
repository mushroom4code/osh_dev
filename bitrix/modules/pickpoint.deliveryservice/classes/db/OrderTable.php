<?php
namespace PickPoint\DeliveryService;

use \Bitrix\Main\Entity;

// 1.0.5 -> 1.0.6 changes:
/*
ALTER TABLE `b_pp_order_postamat` ADD `DISPATCH_DATE` datetime NULL
ALTER TABLE `b_pp_order_postamat` ADD `GETTING_TYPE` VARCHAR(5) NULL
ALTER TABLE `b_pp_order_postamat` ADD `POSTAGE_TYPE` VARCHAR(7) NULL
ALTER TABLE `b_pp_order_postamat` ADD `REGISTRY_ID` int(11) UNSIGNED DEFAULT 0
*/

/*
// 1.0.4 -> 1.0.5 changes:

$tmp = $DB->Query("SELECT * FROM `b_pp_order_postamat` LIMIT 1")->fetch();

if (!array_key_exists('STATUS_CODE', $tmp))
	$DB->Query("ALTER TABLE `b_pp_order_postamat` ADD `STATUS_CODE` VARCHAR(8) NULL");

if (!array_key_exists('STATUS_DATE', $tmp))
	$DB->Query("ALTER TABLE `b_pp_order_postamat` ADD `STATUS_DATE` VARCHAR(20) NULL");
*/

/**
 * Class OrderTable
 * @package PickPoint\DeliveryService
 */
class OrderTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_pp_order_postamat';
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
			new Entity\IntegerField('ORDER_ID', 
				array(
					'required' => true,					
				)
			),
			new Entity\TextField('PP_INVOICE_ID',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('POSTAMAT_ID',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('ADDRESS',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('NAME',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('SMS_PHONE',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('EMAIL',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('SETTINGS',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('WIDTH',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('HEIGHT',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('DEPTH',
				array(
					'required' => true,		
				)
			),
			new Entity\TextField('STATUS',
				array(
					'required' => true,		
				)
			),
			new Entity\BooleanField('CANCELED',
				array(					
					'values' => array('0', '1'),
					'default_value' => '0'
				)
			),
			new Entity\BooleanField('ARCHIVE',
				array(					
					'values' => array('0', '1'),
					'default_value' => '0'
				)
			),
			new Entity\StringField('STATUS_CODE', 
				array(					
				)
			),        
			new Entity\DatetimeField('STATUS_DATE',
				array(
				)
			),			
			new Entity\DatetimeField('DISPATCH_DATE',
				array(
				)
			),			
			new Entity\StringField('GETTING_TYPE', 
				array(					
				)
			),
			new Entity\StringField('POSTAGE_TYPE', 
				array(					
				)
			),			
			new Entity\IntegerField('REGISTRY_ID', 
				array(					
				)
			),			
			/*new Entity\ReferenceField(
                'REGISTRY',
                'PickPoint\DeliveryService\Registry',
                array('=this.REGISTRY_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
            ),			
			*/
        );
    }
}