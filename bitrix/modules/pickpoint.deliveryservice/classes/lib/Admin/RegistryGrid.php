<?php
namespace PickPoint\DeliveryService\Admin;

use \PickPoint\DeliveryService\Admin\Grid\DBGrid;
use \PickPoint\DeliveryService\Bitrix\Adapter;
use \PickPoint\DeliveryService\RegistryTable;
use \Bitrix\Main\Localization\Loc;

/**
 * Class RegistryGrid
 * @package PickPoint\DeliveryService\Admin
 */
class RegistryGrid extends DBGrid
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
	protected $defaultButtons = [
        [
            'CAPTION' => 'PICKPOINT_DELIVERYSERVICE_ADMIN_REGISTRY_CREATE_BUTTON_101',
            'TYPE'    => 'button',
            'ONCLICK' => 'document.location.href = "pickpoint_deliveryservice_registry_create.php?type=101";',
        ],
		[
            'CAPTION' => 'PICKPOINT_DELIVERYSERVICE_ADMIN_REGISTRY_CREATE_BUTTON_102',
            'TYPE'    => 'button',
            'ONCLICK' => 'document.location.href = "pickpoint_deliveryservice_registry_create.php?type=102";',
        ],		
    ];
		
    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'number',
        ],
		[
            'id'          => 'FILENAME',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_FILENAME',
            'sort'        => 'FILENAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,			
        ],
		[
            'id'          => 'REGISTRY_NUMBER',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_REGISTRY_NUMBER',
            'sort'        => 'REGISTRY_NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => "%",
			'quickSearch' => "%",
        ],
		[
            'id'          => 'DATE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_DATE',
            'sort'        => 'DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
			'type'        => 'date',
        ],
		[
            'id'          => 'GETTING_TYPE',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_GETTING_TYPE',
            'sort'        => 'GETTING_TYPE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],		
		[
            'id'          => 'TRANSFER_CITY',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_TRANSFER_CITY',
            'sort'        => 'TRANSFER_CITY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,			
        ],
		[
            'id'          => 'IKN',
            'name'        => 'PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_IKN',
            'sort'        => 'IKN',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
			'type'        => 'number',
        ],		
    ];

    /**
     * @var array
     */
    protected $defaultRowActions = [];
    
    /**
     * Return ORM data mapper for data selection
     *
     * @return string
     */
    public function getDataMapper()
    {
        return RegistryTable::class;
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
		$ret['data']['GETTING_TYPE'] = (array_key_exists($ret['data']['GETTING_TYPE'], $gettingTypes)) ? $gettingTypes[$ret['data']['GETTING_TYPE']] : $ret['data']['GETTING_TYPE'];
		
		$ret['data']['FILENAME'] = "<a href=\"javascript:void(0);\" onclick=\"window.open('".$ret['data']['FILENAME']."')\">".Loc::getMessage('PICKPOINT_DELIVERYSERVICE_TABLE_REGISTRY_ACTION_DOWNLOAD')."</a>";
		
		return $ret;
    }   
}