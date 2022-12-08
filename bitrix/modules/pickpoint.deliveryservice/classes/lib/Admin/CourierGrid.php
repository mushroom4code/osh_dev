<?php
namespace PickPoint\DeliveryService\Admin;

use \PickPoint\DeliveryService\Admin\Grid\DBGrid;
use \PickPoint\DeliveryService\CourierTable;

/**
 * Class CourierGrid
 * @package PickPoint\DeliveryService\Admin
 */
class CourierGrid extends DBGrid
{
    /**
     * @var string
     */    
    protected $fetchMode = 'default'; // As array
	
	/**
     * @var array
     */
    protected $defaultSorting = ['ID' => 'DESC'];	
	
	/**
     * @var array
     */
	protected $defaultButtons = [
        [
            'CAPTION' => 'PICKPOINT_DELIVERYSERVICE_ADMIN_COURIER_FORM_BUTTON',
            'TYPE'    => 'button',
            'ONCLICK' => 'PICKPOINT_DELIVERYSERVICE_export.getPage("main").open();',
        ]
    ];	
	
    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'number',
        ],
        [
            'id'          => 'ORDER_NUMBER',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_ORDER_NUMBER',
            'sort'        => 'ORDER_NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',			
        ],
        [
            'id'          => 'DATE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_DATE',
            'sort'        => 'DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'date',
        ],		
		[
            'id'          => 'TIME_START',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_TIME_START',
            'sort'        => 'TIME_START',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'TIME_END',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_TIME_END',
            'sort'        => 'TIME_END',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'CITY',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_CITY',
            'sort'        => 'CITY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'ADDRESS',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_ADDRESS',
            'sort'        => 'ADDRESS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => "%",
			'quickSearch' => "%",
        ],
		[
            'id'          => 'FIO',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_FIO',
            'sort'        => 'FIO',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'PHONE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_PHONE',
            'sort'        => 'PHONE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'COMMENT',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_COURIER_COMMENT',
            'sort'        => 'COMMENT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
    ];

    /**
     * @var array
     */
    protected $defaultRowActions = [
      /*
		'EDIT' => [
            'ICONCLASS' => 'edit',
            'TEXT'      => 'TABLE_COURIER_BUTTON_EDIT',
            'ONCLICK'   => 'alert("edit action")',
        ],

        'DOWNLOAD_LABEL' => [
            'ICONCLASS' => 'download',
            'TEXT'      => 'TABLE_COURIER_BUTTON_DOWNLOAD_LABELS',
            'ONCLICK'   => 'alert("download action")',
        ],        
		*/
    ];    
    
    /**
     * Return ORM data mapper for data selection
     *
     * @return string
     */
    public function getDataMapper()
    {
        return CourierTable::class;
    }

    /**
     * Get single data item in grid row format
     * 
     * @param array $item
     * @return array
     */
    protected function getRow($item)
    {
        $ret = parent::getRow($item);        
		
		// Minutes from 00:00 to hours
		$ret['data']['TIME_START'] = (string) intval($ret['data']['TIME_START'] / 60).':00';
		$ret['data']['TIME_END']   = (string) intval($ret['data']['TIME_END'] / 60).':00';
		
		$ret['data']['DATE'] = ConvertDateTime($ret['data']['DATE'], "DD.MM.YYYY");
		
		return $ret;
    }

    /**
     * Get row actions available for single row
     *
     * @param array $item
     * @return array
     */
    /*protected function getRowActions($item)
    {
        $ret = parent::getRowActions($item);
        return array_values($ret);
    }*/
}