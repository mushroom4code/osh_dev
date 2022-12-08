<?
use \Ipol\Fivepost\Admin\OrdersGrid;
use \Ipol\Fivepost\Bitrix\Tools;

use \Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "ipol.fivepost");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//if ($GLOBALS['APPLICATION']->GetGroupRight(IPOL_FIVEPOST) > 'D')

$APPLICATION->SetTitle(Tools::getMessage('ADMIN_ORDERS_TITLE'));
$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

if (!CheckVersion(SM_VERSION, '17.0.0')) {
    $gridVersionLock = new CAdminMessage([
        'MESSAGE' => GetMessage("ADMIN_GRID_MIN_VERSION"),
        'TYPE' => 'ERROR',
        'DETAILS' => GetMessage("ADMIN_GRID_MIN_VERSION_TEXT"),
        'HTML' => true
    ]);
    echo $gridVersionLock->Show();
} else {
    // Orders interface buttons, filter and grid
    $OrdersGrid = new OrdersGrid();

    $buttons = $OrdersGrid->getButtons();
    if (!empty($buttons)) {
        $APPLICATION->IncludeComponent('bitrix:ui.button.panel', '.default', [
            'ALIGN'   => 'left',
            'BUTTONS' => $buttons,
        ]);
    }

    $columns = $OrdersGrid->getFilterColumns();
    if (!empty($columns)) {
        $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '.default', [
            'GRID_ID'             => $OrdersGrid->getId(),
            'FILTER_ID'           => $OrdersGrid->getFilterId(),
            'FILTER'              => $columns,
            'ENABLE_LIVE_SEARCH'  => false,
            'ENABLE_LABEL'        => true,
            'DISABLE_SEARCH'      => false, // Quick search in FIND field
            // Undocumented ?
            'VALUE_REQUIRED_MODE' => false,
            'VALUE_REQUIRED'      => false,
        ]);
    }

    $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '.default', [
        'GRID_ID'                   => $OrdersGrid->getId(),
        'COLUMNS'                   => $OrdersGrid->getColumns(),
        'ROWS'                      => $OrdersGrid->getRows(),
        'NAV_OBJECT'                => $OrdersGrid->getPagination(),
        'AJAX_ID'                   => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'AJAX_MODE'                 => 'Y',
        'AJAX_OPTION_HISTORY'       => false,
        'AJAX_OPTION_JUMP'          => 'N',
        'PAGE_SIZES'                => [
            ['VALUE' => '10',   'NAME' => '10'],
            ['VALUE' => '20',   'NAME' => '20'],
            ['VALUE' => '50',   'NAME' => '50'],
            ['VALUE' => '100',  'NAME' => '100'],
            ['VALUE' => '200',  'NAME' => '200'],
            ['VALUE' => '500',  'NAME' => '500'],
        ],
        'SHOW_ROW_CHECKBOXES'       => true,
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ALLOW_SORT'                => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_PIN_HEADER'          => true,
        'TOTAL_ROWS_COUNT'          => $OrdersGrid->getPagination()->getRecordCount(),

        // Undocumented params
        'EDITABLE'                  => true,

        // Group actions
        'ACTION_PANEL'              => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => $OrdersGrid->getControls(),
                ]
            ]
        ],
    ]);

    \CJSCore::Init(array('jquery'));
    // CSS hack for grid coloring
    ?>
    <style>.main-grid-cell {background: none !important;}</style>
    <script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
    <script type="text/javascript">
        var <?=IPOL_FIVEPOST_LBL?>controller = new i5post_adminInterface({
            'ajaxPath': '<?=Tools::getJSPath()?>ajax.php',
            'label':    '<?=IPOL_FIVEPOST?>',
            'logging':  true
        });

        <?=IPOL_FIVEPOST_LBL?>controller.expander({});
        <?=IPOL_FIVEPOST_LBL?>controller.addPage('main', {
            init: function(){
                this.actions(this);
                this.grids(this);
            },
            actions: (function(self){
                self.actions = {
                    suncBtn: false,
                    suncStatuses: function(btnLink){
                        if (!self.actions.suncBtn) {
                            self.actions.suncBtn = $(btnLink);
                        }
                        self.actions.suncBtn.attr('disabled', 'disabled');
                        self.actions.suncBtn.css('opacity', 0.7);
                        self.self.ajax({
                            data: {<?=IPOL_FIVEPOST_LBL?>action: 'refreshStatusesAjax'},
                            success: self.actions.onSunc
                        });
                    },
                    onSunc: function(answer){
                        self.actions.suncBtn.removeAttr('disabled');
                        self.actions.suncBtn.css('opacity', "");
                        self.grids.reload();
                    },
                    suncOrderStatus: function(bitrixId){
                        self.self.ajax({
                            data: {<?=IPOL_FIVEPOST_LBL?>action: 'checkStatusByBitrixIAjax', bitrixId: bitrixId},
                            success: self.actions.onOrderSunc
                        });
                    },
                    onOrderSunc: function(answer){
                        self.grids.reload();
                    },
                    getSticker: function(bitrixId, barcGenerateByServer){
                        if (barcGenerateByServer === true) {
                            self.self.ajax({
                                data:     {<?=IPOL_FIVEPOST_LBL?>action: 'getStickerRequest', bitrixId: bitrixId},
                                dataType: 'json',
                                success:  self.actions.onOrderSticker
                            });
                        } else {
                            window.open("<?=Tools::getJSPath()."ajax.php?".IPOL_FIVEPOST_LBL."action=printBKsRequest&bitrixId="?>" + bitrixId);
                        }
                    },
                    onOrderSticker: function(answer){
                        if (answer.success) {
                            if (answer.files !== 'undefined') {
                                for (var i in answer.files) {
                                    if (!answer.files.hasOwnProperty(i))
                                        continue;
                                    window.open(answer.files[i]);
                                }
                            }
                            if (answer.errors) {
                                setTimeout(() => alert(answer.errors), 1500);
                            }
                        } else {
                            alert('<?=Tools::getMessage("MESS_STICKER_ERROR")?>' + answer.errors);
                        }
                    },
                    cancelOrder: function(bitrixId){
                        if (confirm('<?=Tools::getMessage('MESS_DOCANCEL')?>')) {
                            self.self.ajax({
                                data:     {<?=IPOL_FIVEPOST_LBL?>action: 'deleteOrder', bitrixId: bitrixId},
                                dataType: 'json',
                                success:  self.actions.onOrderCancel
                            });
                        }
                    },
                    onOrderCancel: function(answer){
                        if (answer.success) {
                            alert('<?=Tools::getMessage('MESS_CANCELED')?>');
                            self.grids.reload();
                        } else {
                            alert('<?=Tools::getMessage('MESS_NOTCANCELED')?>' + answer.error);
                        }
                    },
                    eraseOrder: function(bitrixId){
                        if (confirm('<?=Tools::getMessage('TABLE_ORDER_MESS_ERASE_ORDER')?>')) {
                            self.self.ajax({
                                data:     {<?=IPOL_FIVEPOST_LBL?>action: 'eraseOrderAjaxBid', bitrixId: bitrixId},
                                dataType: 'json',
                                success:  self.actions.onEraseOrder
                            });
                        }
                    },
                    onEraseOrder: function(answer){
                        if (answer.success) {
                            alert('<?=Tools::getMessage('TABLE_ORDER_MESS_ERASED')?>');
                            self.grids.reload();
                        } else {
                            alert('<?=Tools::getMessage('TABLE_ORDER_ERASE_ORDER_ERR')?>' + answer.error);
                        }
                    },
                    printStickers: function(ids){
                        self.self.ajax({
                            data     : {<?=IPOL_FIVEPOST_LBL?>action: 'getStickersRequest', ids: ids},
                            dataType : 'json',
                            success  : self.actions.onOrderSticker
                        });
                    },
                }
            }),
            grids: (function(self){
                self.grids = {
                    reload: function(){
                        self.grids.reloading('<?=$OrdersGrid->getId()?>');
                    },
                    reloading: function(gridId){
                        var reloadParams = {apply_filter: 'Y'/*, clear_nav: 'Y'*/};
                        var gridObject = BX.Main.gridManager.getById(gridId);

                        if (gridObject.hasOwnProperty('instance')) {
                            gridObject.instance.reloadTable('POST', reloadParams);
                        }
                    }
                }
            })
        });
        $(document).ready(<?=IPOL_FIVEPOST_LBL?>controller.init);
    </script>
    <?
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");