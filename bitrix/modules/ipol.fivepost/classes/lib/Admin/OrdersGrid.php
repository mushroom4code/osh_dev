<?
namespace Ipol\Fivepost\Admin;

use \Bitrix\Main\Type\DateTime;

use \Ipol\Fivepost\Bitrix\Tools;
use \Ipol\Fivepost\OrdersTable;
use \Ipol\Fivepost\PointsTable;
use \Ipol\Fivepost\Admin\Grid\DatabaseGrid;
use \Ipol\Fivepost\Bitrix\Adapter;

/**
 * Class OrdersGrid
 * @package Ipol\Fivepost\Admin
 */
class OrdersGrid extends DatabaseGrid
{
    /**
     * @var string
     */
    protected $fetchMode = self::FETCH_AS_ARRAY;

    /**
     * @var array
     */
    protected $defaultSorting = ['ID' => 'DESC'];

    /**
     * @var array
     */
    protected $defaultButtons = [
        [
            'CAPTION' => 'TABLE_ORDERS_BTN_GET_STATUSES',
            'TYPE'    => 'button',
            'ONCLICK' => IPOL_FIVEPOST_LBL.'controller.getPage("main").actions.suncStatuses(this)',
        ],
    ];

    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'TABLE_ORDERS_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'number',
        ],
        [
            'id'          => 'BITRIX_ID',
            'name'        => 'TABLE_ORDERS_BITRIX_ID',
            'sort'        => 'BITRIX_ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'FIVEPOST_ID',
            'name'        => 'TABLE_ORDERS_FIVEPOST_ID',
            'sort'        => 'FIVEPOST_ID',
            'default'     => false,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'FIVEPOST_GUID',
            'name'        => 'TABLE_ORDERS_FIVEPOST_GUID',
            'sort'        => 'FIVEPOST_GUID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'STATUS',
            'name'        => 'TABLE_ORDERS_STATUS',
            'sort'        => 'STATUS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'BRAND_NAME',
            'name'        => 'TABLE_ORDERS_BRAND_NAME',
            'sort'        => 'BRAND_NAME',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'CLIENT_NAME',
            'name'        => 'TABLE_ORDERS_CLIENT_NAME',
            'sort'        => 'CLIENT_NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
            'quickSearch' => '%',
        ],
        [
            'id'          => 'CLIENT_EMAIL',
            'name'        => 'TABLE_ORDERS_CLIENT_EMAIL',
            'sort'        => 'CLIENT_EMAIL',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'CLIENT_PHONE',
            'name'        => 'TABLE_ORDERS_CLIENT_PHONE',
            'sort'        => 'CLIENT_PHONE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'PLANNED_RECEIVE_DATE',
            'name'        => 'TABLE_ORDERS_PLANNED_RECEIVE_DATE',
            'sort'        => 'PLANNED_RECEIVE_DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'date',
        ],
        [
            'id'          => 'SHIPMENT_DATE',
            'name'        => 'TABLE_ORDERS_SHIPMENT_DATE',
            'sort'        => 'SHIPMENT_DATE',
            'default'     => false,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'date',
        ],
        [
            'id'          => 'RECEIVER_LOCATION',
            'name'        => 'TABLE_ORDERS_RECEIVER_LOCATION',
            'sort'        => 'RECEIVER_LOCATION',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'SENDER_LOCATION',
            'name'        => 'TABLE_ORDERS_SENDER_LOCATION',
            'sort'        => 'SENDER_LOCATION',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'UNDELIVERABLE_OPTION',
            'name'        => 'TABLE_ORDERS_UNDELIVERABLE_OPTION',
            'sort'        => 'UNDELIVERABLE_OPTION',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'CARGOES',
            'name'        => 'TABLE_ORDERS_CARGOES',
            'sort'        => 'CARGOES',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'CURRENCY',
            'name'        => 'TABLE_ORDERS_CURRENCY',
            'sort'        => 'CURRENCY',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'DELIVERY_COST',
            'name'        => 'TABLE_ORDERS_DELIVERY_COST',
            'sort'        => 'DELIVERY_COST',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'DELIVERY_COST_CURRENCY',
            'name'        => 'TABLE_ORDERS_DELIVERY_COST_CURRENCY',
            'sort'        => 'DELIVERY_COST_CURRENCY',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PAYMENT_VALUE',
            'name'        => 'TABLE_ORDERS_PAYMENT_VALUE',
            'sort'        => 'PAYMENT_VALUE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PAYMENT_TYPE',
            'name'        => 'TABLE_ORDERS_PAYMENT_TYPE',
            'sort'        => 'PAYMENT_TYPE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PAYMENT_CURRENCY',
            'name'        => 'TABLE_ORDERS_PAYMENT_CURRENCY',
            'sort'        => 'PAYMENT_CURRENCY',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PRICE',
            'name'        => 'TABLE_ORDERS_PRICE',
            'sort'        => 'PRICE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PRICE_CURRENCY',
            'name'        => 'TABLE_ORDERS_PRICE_CURRENCY',
            'sort'        => 'PRICE_CURRENCY',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'FIVEPOST_STATUS',
            'name'        => 'TABLE_ORDERS_FIVEPOST_STATUS',
            'sort'        => 'FIVEPOST_STATUS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'FIVEPOST_EXECUTION_STATUS',
            'name'        => 'TABLE_ORDERS_FIVEPOST_EXECUTION_STATUS',
            'sort'        => 'FIVEPOST_EXECUTION_STATUS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'MESSAGE',
            'name'        => 'TABLE_ORDERS_MESSAGE',
            'sort'        => 'MESSAGE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'OK',
            'name'        => 'TABLE_ORDERS_OK',
            'sort'        => 'OK',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'UPTIME',
            'name'        => 'TABLE_ORDERS_UPTIME',
            'sort'        => 'UPTIME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
    ];

    /**
     * @var array
     */
    protected $defaultRowActions = [
        // Acceptable system icon classes are in \bitrix\js\main\popup\dist\main.popup.bundle.css
        // menu-popup-item-copy for documents

        'VIEW_BITRIX_ORDER' => [
            'ICONCLASS' => 'menu-popup-item-delegate',
            'TEXT'      => 'TABLE_ORDERS_ROW_VIEW_BITRIX_ORDER',
            'ONCLICK'   => 'window.open("sale_order_view.php?ID=#BITRIX_ID#")',
        ],
        'GET_ORDER_STATUS' => [
            'ICONCLASS' => 'menu-popup-item-view',
            'TEXT'      => 'TABLE_ORDERS_ROW_GET_ORDER_STATUS',
            'ONCLICK'   => IPOL_FIVEPOST_LBL.'controller.getPage("main").actions.suncOrderStatus("#BITRIX_ID#")',
        ],
        'GET_ORDER_STICKER' => [
            'ICONCLASS' => 'menu-popup-item-copy',
            'TEXT'      => 'TABLE_ORDERS_ROW_PRINT_STICKER',
            'ONCLICK'   => IPOL_FIVEPOST_LBL.'controller.getPage("main").actions.getSticker("#BITRIX_ID#", #BARK_GENERATE_BY_SERVER#)',
        ],
        'ERASE_ORDER' => [
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_ORDERS_ROW_ERASE_ORDER',
            'ONCLICK'   => IPOL_FIVEPOST_LBL.'controller.getPage("main").actions.eraseOrder("#BITRIX_ID#")',
        ],
        'DELETE_ORDER' => [
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_ORDERS_ROW_DELETE_ORDER',
            'ONCLICK'   => IPOL_FIVEPOST_LBL.'controller.getPage("main").actions.cancelOrder("#BITRIX_ID#")',
        ],
    ];

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
                'TEXT' => Tools::getMessage('TABLE_ORDER_PRINT'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("'.$this->getId().'");
                                    var ids  = grid.getRows().getSelectedIds();
                                    
                                    if (ids.length > 0) {
                                        '.IPOL_FIVEPOST_LBL.'controller.getPage("main").actions.printStickers(ids);
                                    }
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
     * @return Bitrix\Main\ORM\Data\DataManager
     */
    public function getDataMapper()
    {
        return OrdersTable::class;
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

        $undeliverableS = Adapter::getUndeliverableOptionVariants();
        $paymentS = Adapter::getPaymentTypeVariants();

        // Add human-readable texts instead of specific identifiers
        $ret['data']['UNDELIVERABLE_OPTION'] = (array_key_exists($ret['data']['UNDELIVERABLE_OPTION'], $undeliverableS)) ?
            $undeliverableS[$ret['data']['UNDELIVERABLE_OPTION']].'<br>['.$ret['data']['UNDELIVERABLE_OPTION'].']' :
            $ret['data']['UNDELIVERABLE_OPTION'];

        if ($pt = Adapter::convertPaymentTypes($ret['data']['PAYMENT_TYPE'])) {
            $ret['data']['PAYMENT_TYPE'] = (array_key_exists($pt, $paymentS)) ?
                $paymentS[$pt] : $ret['data']['PAYMENT_TYPE'];
        }

        $point = PointsTable::getByPointGuid($ret['data']['RECEIVER_LOCATION'], ['POINT_GUID', 'NAME', 'FULL_ADDRESS']);
        if (!empty($point) && $point['NAME'] && $point['FULL_ADDRESS']) {
            $ret['data']['RECEIVER_LOCATION'] = $point['NAME'].'<br>'.$point['FULL_ADDRESS'].'<br>['.$ret['data']['RECEIVER_LOCATION'].']';
        }

        if (!empty($ret['data']['UPTIME'])) {
            $ret['data']['UPTIME'] = DateTime::createFromTimestamp($ret['data']['UPTIME'])->format("H:i:s d.m.Y");
        }


        // Rows coloring by current order status
        // Beware:
        // - undocumented param 'attrs' used, version compatibility unknown
        // - drop .main-grid-cell background color required, check ipol_fivepost_orders.php
        $statusToColor = array(
            'ok'          => '#E2FCE2',
            'sended'      => '#E2FCE2',
            'valid'       => '#E2FCE2',
            'rejected'    => '#FFEDED',
            'warehouse'   => '#FCFCBF',
            'inpostamat'  => '#D9FFCE',
            'interrupted' => '#FFEDED',
            'lost'        => '#FFEDED',
            'reclaim'     => '#D9FFCE',
            'repickup'    => '#D9FFCE',
            'unclaimed'   => '#FFEDED',
            'done'        => '#ABFFAB',
            'canceled'    => '#CACACA',
        );
        $color = array_key_exists($ret['data']['STATUS'], $statusToColor) ? $statusToColor[$ret['data']['STATUS']] : '#fff';
        if (!empty($ret['data']['STATUS'])) {
            $ret['data']['STATUS'] = Tools::getMessage('STATUS_'.$ret['data']['STATUS']);
        }
        $ret['attrs'] = ['style' => "background: {$color};"];

        return $ret;
    }

    /**
     * Get row actions available for single row
     *
     * @param array $item
     * @return array
     */
    protected function getRowActions($item)
    {
        $status = $item['STATUS'];
        $ret = parent::getRowActions($item);

        if (!Adapter::statusIsSending($status))
            unset($ret['ERASE_ORDER']);

        if (Adapter::statusIsSending($status) || Adapter::statusIsFinal($status))
            unset($ret['GET_ORDER_STATUS']);

        if (!Adapter::statusIsReady($status))
            unset($ret['GET_ORDER_STICKER']);

        if (!Adapter::statusIsCancelable($status))
            unset($ret['DELETE_ORDER']);

        foreach ($ret as $index => $action) {
            $genByServer = $item['BARK_GENERATE_BY_SERVER'] === 'Y' ? 'true' : 'false';
            $ret[$index]['LINK']    = str_replace(['#BITRIX_ID#', '#FIVEPOST_ID#', '#BARK_GENERATE_BY_SERVER#'], [$item['BITRIX_ID'], $item['FIVEPOST_ID'], $genByServer], $action['LINK']);
            $ret[$index]['ONCLICK'] = str_replace(['#BITRIX_ID#', '#FIVEPOST_ID#', '#BARK_GENERATE_BY_SERVER#'], [$item['BITRIX_ID'], $item['FIVEPOST_ID'], $genByServer], $action['ONCLICK']);
        }

        return array_values($ret);
    }
}