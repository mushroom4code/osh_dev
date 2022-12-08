<?php
namespace PickPoint\DeliveryService\Admin;

use \PickPoint\DeliveryService\Admin\Grid\DBGrid;
use \PickPoint\DeliveryService\Bitrix\Adapter;
use \PickPoint\DeliveryService\OrderTable;
use \Bitrix\Main\Localization\Loc;

/**
 * Class RegistryOrdersGrid
 * @package PickPoint\DeliveryService\Admin
 */
class RegistryOrdersGrid extends DBGrid
{
    /**
     * @var string
     */    
    protected $fetchMode = 'object';
	
	/**
     * @var array
     */
    protected $defaultSorting = ['ID' => 'DESC'];	
		
    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'number',
        ],
        [
            'id'          => 'ORDER_ID',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_ORDER_ID',
            'sort'        => 'ORDER_ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
			'type'        => 'number',
        ],
        [
            'id'          => 'PP_INVOICE_ID',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_PP_INVOICE_ID',
            'sort'        => 'PP_INVOICE_ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'number',			
        ],		
		[
            'id'          => 'DISPATCH_DATE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_DISPATCH_DATE',
            'sort'        => 'DISPATCH_DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'date',
        ],
		[
            'id'          => 'ADDRESS',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_ADDRESS',
            'sort'        => 'ADDRESS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => "%",
			'quickSearch' => "%",
        ],
		[
            'id'          => 'POSTAMAT_ID',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_POSTAMAT_ID',
            'sort'        => 'POSTAMAT_ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'GETTING_TYPE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_GETTING_TYPE',
            'sort'        => 'GETTING_TYPE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'POSTAGE_TYPE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_POSTAGE_TYPE',
            'sort'        => 'POSTAGE_TYPE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'NAME',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_NAME',
            'sort'        => 'NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'SMS_PHONE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_SMS_PHONE',
            'sort'        => 'SMS_PHONE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
		[
            'id'          => 'EMAIL',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_EMAIL',
            'sort'        => 'EMAIL',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],		
    ];

    /**
     * @var array
     */
    protected $defaultRowActions = [];

    /**
     * Get grid action panel controls
     *
	 * @see component bitrix:main.ui.grid
     * @return array
     */
    public function getControls()
    {
        return [
            [
                'ID'   => 'create-registry',
                'TYPE' => 'BUTTON',
                'NAME' => 'create-registry',
                'TEXT' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_TABLE_ORDER_CONTROL_CREATE_REGISTRY'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();

                                    if (ids.length > 0) 
										'.PICKPOINT_DELIVERYSERVICE_LBL.'export.getPage("orders").makeRegistry(ids);                                        
                                ',
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
    
    /**
     * Return ORM data mapper for data selection
     *
     * @return string
     */
    public function getDataMapper()
    {
        return OrderTable::class;
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
		
		$gettingTypes = Adapter::getGettingTypes();
		$postageTypes = Adapter::getPostageTypes();
		
		$ret['data']['GETTING_TYPE'] = (array_key_exists($ret['data']['GETTING_TYPE'], $gettingTypes)) ? $gettingTypes[$ret['data']['GETTING_TYPE']] : $ret['data']['GETTING_TYPE'];
		$ret['data']['POSTAGE_TYPE'] = (array_key_exists($ret['data']['POSTAGE_TYPE'], $postageTypes)) ? $postageTypes[$ret['data']['POSTAGE_TYPE']] : $ret['data']['POSTAGE_TYPE'];
				
		return $ret;
    }   
}