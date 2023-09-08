<?php
namespace Ipol\Fivepost\Admin;

use Ipol\Fivepost\Bitrix\Adapter;
use Ipol\Fivepost\Bitrix\Handler\Deliveries;
use Ipol\Fivepost\Bitrix\Handler\Order;
use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Fivepost\Handler\Enumerations;
use Ipol\Fivepost\Option;
use Ipol\Fivepost\PointsTable;
use Ipol\Fivepost\Warhouses;

class OrderSender
{
    private static $MODULE_ID  = IPOL_FIVEPOST;
    private static $MODULE_LBL = IPOL_FIVEPOST_LBL;

    public static $workMode;
    public static $workType;
    public static $orderId;
    public static $shipmentId;
    public static $status;

    // service
    protected static $arButtons;

    protected static function getMode()
    {
        switch(self::$workMode) {
            case 'order':    return 1; break;
            case 'shipment': return 2; break;
        }
        return false;
    }

    protected static function getId()
    {
        switch(self::getMode()) {
            case 1: return self::$orderId; break;
            case 2: return self::$shipmentId; break;
        }
        return false;
    }

    public static function init()
    {
        if (!Tools::isAdminSection())
            return false;

        global $APPLICATION;
        $dir = $APPLICATION->GetCurDir();

        $b24path = Tools::getB24URLs();

        // Standard BX support
        $check = ($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];
        if (
            strpos($check, "/bitrix/admin/sale_order_detail.php") !== false ||
            strpos($check, "/bitrix/admin/sale_order_view.php")   !== false
        ) {
            self::$workMode = 'order';
            self::$orderId  = $_REQUEST['ID'];
            self::$workType = 'standart';
        } elseif (strpos($dir, $b24path['ORDER']) !== false) {
            // B24 support
            self::$workMode = 'order';
            self::$orderId  = array_shift(explode('/', ltrim($dir, $b24path['ORDER'])));
            self::$workType = 'b24';
        }

        if (!self::$workType || !self::$workMode || !\CModule::IncludeModule('sale') || !Tools::isAdmin('R'))
            return false;

        // Prevent form loading for order history table AJAX calls
        if (isset($_REQUEST['table_id']) && $_REQUEST['table_id'] == 'table_order_history')
            return false;

        // Disable form for B24 new order window
        if (self::$orderId == 0 && self::$workType == 'b24') {
            return false;
        }

        if (Option::get('showInOrders') === 'Y' || Deliveries::is5PostDelivery(self::getId())) {
            // B24 button container adding
            if (self::$workType == 'b24') {
                \Bitrix\Main\UI\Extension::load('ui.buttons');
                \Bitrix\Main\UI\Extension::load('ui.buttons.icons');

                $containerHTML = '<div class="pagetitle-container" id="'.self::$MODULE_LBL.'btn_container"></div>';
                $APPLICATION->AddViewContent('inside_pagetitle', $containerHTML, 20000);

                \CJSCore::Init(array("window"));
                $APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/pubstyles.min.css");
                $APPLICATION->SetAdditionalCSS("/bitrix/panel/main/admin-public.min.css");
            }
            self::loadExportWindow();
            return true;
        }

        return false;
    }

    public static function loadExportWindow()
    {
        global $APPLICATION;

        $APPLICATION->AddHeadScript(Tools::getJSPath().'wndController.js');
        \CJSCore::Init(array('jquery'));

        // check for existance
        $data = Adapter::getOrderData(self::getId(), self::getMode());

        self::$status = $data->getStatus();

        self::generateExportOrderHtml($data);

        self::loadExportCSS();

        self::getOrderExportJs($data);
    }

    protected static function getOrderExportJs(\Ipol\Fivepost\Core\Order\Order $order)
    {
        $pathToWidjet = Tools::getJSPath().'widjet/widjet.js';
        $arItems = array();
        $order->getItems()->reset();
        while ($obItem = $order->getItems()->getNext()) {
            $arItems[] = $obItem->getFields();
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet)) {
            $GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
        }
        ?>
        <script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
        <script type="text/javascript">
                var <?=self::$MODULE_LBL?>export = new i5post_adminInterface({
                'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
                'label'    : '<?=self::$MODULE_ID?>',
                'logging'  : true
            });

            <?=self::$MODULE_LBL?>export.expander({
                orderId    : '<?=self::$orderId?>',
                orderNum   : '<?=Order::getOrderNumber(self::$orderId)?>',
                shipmentId : '<?=self::$shipmentId?>',
                fivepostId : '<?=$order->getLink()?>',
                wayBill    : '<?=$order->getField('waybill')?>',
                workMode   : '<?=self::$workMode?>',
                status     : '<?=self::$status?>',
                price      : '<?=$order->getPayment()->getGoods()->getAmount()?>',
                deliveryPr : '<?=$order->getPayment()->getDelivery()->getAmount()?>',
                payed      : '<?=$order->getPayment()->getPayed()->getAmount()?>',
                items      : <?=\CUtil::PhpToJSObject($arItems)?>,
                label      : '<?=self::$MODULE_LBL?>',
                sendCost   : <?=(Option::get('noCost') === 'Y') ? 'false' : 'true'?>,
                error      : false,
            });
        </script>

        <?include_once('OrderSenderPages/main.php')?>
        <?include_once('OrderSenderPages/gabs.php')?>
        <?include_once('OrderSenderPages/goods.php')?>
        <?//include_once('OrderSenderPages/additional.php')?>
        <?//include_once('OrderSenderPages/lots.php')?>

        <script type="text/javascript">
            $(document).ready(<?=self::$MODULE_LBL?>export.init);
        </script>
        <?
    }

    public static function generateExportOrderHtml(\Ipol\Fivepost\Core\Order\Order $order)
    {
        $obPVZInfo = ($order->getField('receiverLocation')) ? PointsTable::getByPointGuid($order->getField('receiverLocation')) : false;
        if ($obPVZInfo) {
            $obPVZInfo = $obPVZInfo['FULL_ADDRESS'];
        }
        ?>
        <div id="<?=self::$MODULE_LBL?>PLACEFORFORM">
            <table id="<?=self::$MODULE_LBL?>wndOrder">
                <tbody><tr><td><?=Tools::getMessage('LBL_STATUS')?></td><td><?=Tools::getMessage('STATUS_'.$order->getStatus())?></td></tr>
                <tr><td colspan="2"><small><?=Tools::getMessage('STATUS_'.$order->getStatus().'_DESCR')?></small></td></tr>
                <?if($order->getField('message')){?><tr><td colspan="2" class="<?=self::$MODULE_LBL?>warning"><?=$order->getField('message')?></td></tr><?}?>
                <?if($order->getLink()){?>
                    <tr><td><?=Tools::getMessage('LBL_FIVEPOST_GUID')?></td><td><?=$order->getLink()?></td></tr>
                <?}?>
                <?if($order->getField('cargoesCargoId')){?>
                    <?Tools::placeSOHeaderRow('CARGOES',self::$MODULE_LBL."export.getPage('main').ui.toggleBlock('cargoes')");?>
                    <tr class="<?=self::$MODULE_LBL?>block_cargoes"><td><?=Tools::getMessage('LBL_CARGOES_CARGO_ID')?></td><td><?=$order->getField('cargoesCargoId')?></td></tr>
                <?}?>
                <?if($order->getField('cargoesBarcode')){?>
                    <tr class="<?=self::$MODULE_LBL?>block_cargoes"><td><?=Tools::getMessage('LBL_CARGOES_BARCODE')?></td><td><?=$order->getField('cargoesBarcode')?></td></tr>
                <?}?>
                <?if($order->getField('cargoesSenderCargoId')){?>
                    <tr class="<?=self::$MODULE_LBL?>block_cargoes"><td><?=Tools::getMessage('LBL_CARGOES_SENDER_CARGO_ID')?></td><td><?=$order->getField('cargoesSenderCargoId')?></td></tr>
                <?}?>
                <?Tools::placeSOHeaderRow('COMMONDATA')?>
                <?Tools::placeSORow('number','hidden',$order->getNumber());?>
                <?$sd = $order->getField('senderCreateDate');
                Tools::placeSORow('senderCreateDate','sign',$sd['sign']);?>
                <tr><td>
                    <input type="hidden" value="<?=$sd['timestamp']?>" id="<?=self::$MODULE_LBL?>senderCreateDate">
                    <input type="hidden" value="RUB" id="<?=self::$MODULE_LBL?>currency">
                    <input type="hidden" value="RUB" id="<?=self::$MODULE_LBL?>deliveryCostCurrency">
                    <input type="hidden" value="RUB" id="<?=self::$MODULE_LBL?>paymentCurrency">
                    <input type="hidden" value="RUB" id="<?=self::$MODULE_LBL?>priceCurrency">
                </td></tr>
                <?if ($order->getField('barcode')) {?>
                    <?Tools::placeSORow('barcode','hidden',$order->getField('barcode'));?>
                <?}?>
                <tr><td colspan="2"><small style="color: green;"><?=Tools::getMessage($order->getField('barcodeGenerateByServer') ? 'LBL_BARCODE_BY_SERVER_Y' : 'LBL_BARCODE_BY_SERVER_N')?></small></td></tr>
                <?Tools::placeSORow('barcodeGenerateByServer','hidden',$order->getField('barcodeGenerateByServer'),false,false,"unseen");?>
                <tr><td colspan="2"><hr></td></tr>
                <tr>
                    <td><?=Tools::getMessage('LBL_plannedReceiveDate')?></td>
                    <td><?if ($order->getField('plannedReceiveDate')) {
                            $plannedReceiveDateSign = trim(substr($order->getField('plannedReceiveDate'),0,strpos($order->getField('plannedReceiveDate'),' ')));
                            $plannedReceiveDateTimestamp = (new \Bitrix\Main\Type\DateTime($order->getField('plannedReceiveDate')))->getTimestamp();
                        } else {
                            $plannedReceiveDateSign = Tools::getMessage('LBL_notSetted');
                            $plannedReceiveDateTimestamp = '';
                        }
                        if (Adapter::statusIsSending($order->getStatus())) {?>
                            <div class="adm-input-wrap adm-input-wrap-calendar">
                                <input type="hidden" id="<?=self::$MODULE_LBL?>plannedReceiveDate" name="<?=self::$MODULE_LBL?>plannedReceiveDate" value="<?=$plannedReceiveDateTimestamp;?>">
                                <input class="adm-input adm-input-calendar" id="<?=self::$MODULE_LBL?>plannedReceiveDate_helper" disabled="" name="<?=self::$MODULE_LBL?>plannedReceiveDate_helper" size="22" type="text" value="<?=$plannedReceiveDateSign;?>">
                                <span class="adm-calendar-icon" onclick="BX.calendar({node:this, field:'<?=self::$MODULE_LBL?>plannedReceiveDate_helper', form: '', bTime: true, bHideTime: true, callback_after: <?=self::$MODULE_LBL?>export.getPage('main').events.onPlannedReceiveDateChange});"></span>
                            </div>
                            <?
                        } else {
                            echo $plannedReceiveDateSign;
                        }
                    ?></td>
                </tr>
                <tr>
                    <td><?=Tools::getMessage('LBL_receiverLocation')?></td>
                    <td>
                        <input type="hidden" id="<?=self::$MODULE_LBL?>receiverLocation" name="<?=self::$MODULE_LBL?>receiverLocation" value="<?=$order->getField('receiverLocation')?>">
                        <span class="<?=self::$MODULE_LBL?>warning" style="display:none" id="<?=self::$MODULE_LBL?>pvzPickerPickerError"><?=Tools::getMessage('ERROR_NOPVZ')?></span>
                        <span id="<?=self::$MODULE_LBL?>hidLabel_receiverLocation"><?=$obPVZInfo?></span><br>
                        <small id="<?=self::$MODULE_LBL?>hidLabel_receiverLocationID"><?=$order->getField('receiverLocation')?></small><br>
                    </td>
                </tr>
                <tr>
                    <td></td><td>
                        <img src="<?=Tools::getImagePath()?>long_ajax.gif" id="<?=self::$MODULE_LBL?>pvzPickerPreloader">
                        <input id="<?=self::$MODULE_LBL?>pvzPickerPicker" type="button" style="display: none" onclick="<?=self::$MODULE_LBL?>export.getPage('main').act.selectNewPvz()" value="<?=(Adapter::statusIsSending($order->getStatus())) ? Tools::getMessage('BTN_SELECTPVZ') : Tools::getMessage('BTN_WATCHPVZ')?>" >
                    </td>
                </tr>
                <tr><td colspan="2"><hr></td></tr>
                <?
                $arSavedWH = Warhouses::getWHInfo();
                $arWH = array();
                if($arSavedWH){
                    foreach ($arSavedWH as $arSWH) {
                        $arWH [$arSWH[self::$MODULE_LBL.'WH_partnerLocationId']] =  $arSWH[self::$MODULE_LBL.'WH_name']." (".$arSWH[self::$MODULE_LBL.'WH_partnerLocationId'].")";
                    }
                }
                Tools::placeSORow('senderLocation','select',$order->getField('senderLocation'),$arWH);
                ?>

                <?//GOODS_SPECIAL?>
                <?Tools::placeSOHeaderRow('GOODS_SPECIAL',self::$MODULE_LBL."export.getPage('main').ui.toggleBlock('goodsSpecial')");?>
                <?Tools::placeSORow('brandName','text',$order->getField('brandName'),false,false,'block_goodsSpecial');?>
                <?Tools::placeSORow('undeliverableOption','select',$order->getField('undeliverableOption'),Adapter::getUO(),false,'block_goodsSpecial');?>
                <tr>
                    <td><?=Tools::getMessage('LBL_shipmentDate')?></td>
                    <td><?if ($order->getField('shipmentDate')) {
                            $shipmentDateSign = trim(substr($order->getField('shipmentDate'),0,strpos($order->getField('shipmentDate'),' ')));
                            $shipmentDateTimestamp = (new \Bitrix\Main\Type\DateTime($order->getField('shipmentDate')))->getTimestamp();
                        } else {
                            $shipmentDateSign = Tools::getMessage('LBL_notSetted');
                            $shipmentDateTimestamp = '';
                        }
                        if (Adapter::statusIsSending($order->getStatus())) {?>
                            <div class="adm-input-wrap adm-input-wrap-calendar">
                                <input type="hidden" id="<?=self::$MODULE_LBL?>shipmentDate" name="<?=self::$MODULE_LBL?>shipmentDate" value="<?=$shipmentDateTimestamp;?>">
                                <input class="adm-input adm-input-calendar" id="<?=self::$MODULE_LBL?>shipmentDate_helper" disabled="" name="<?=self::$MODULE_LBL?>shipmentDate_helper" size="22" type="text" value="<?=$shipmentDateSign;?>">
                                <span class="adm-calendar-icon" onclick="BX.calendar({node:this, field:'<?=self::$MODULE_LBL?>shipmentDate_helper', form: '', bTime: true, bHideTime: true, callback_after: <?=self::$MODULE_LBL?>export.getPage('main').events.onShipmentDateChange});"></span>
                            </div>
                            <?
                        } else {
                            echo $shipmentDateSign;
                        }
                    ?></td>
                </tr>
                <?//Tools::placeSORow('senderCargoId','text',false,false,false,'block_goodsSpecial');?>

                <?//GABARITES?>
                <?Tools::placeSOHeaderRow('GABARITES',self::$MODULE_LBL."export.getPage('main').ui.toggleBlock('gabariles')");?>
                <tr class="<?=self::$MODULE_LBL?>block_gabariles">
                    <td><?=Tools::getMessage('LBL_dimensions')?></td>
                    <td>
                        <div id="<?=self::$MODULE_LBL?>gabsPlace">
                            <span id="<?=self::$MODULE_LBL?>gabsLabel"> <?=$order->getGoods()->getLength()?> X <?=$order->getGoods()->getWidth()?> X <?=$order->getGoods()->getHeight()?></span>
                            <a href='javascript:void(0)' onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').edit('gabs')"><?=Tools::getMessage('BTN_EDIT')?></a>
                        </div>
                        <div id="<?=self::$MODULE_LBL?>gabsEditor">
                            <input type="text" name="<?=self::$MODULE_LBL?>length_edit" id="<?=self::$MODULE_LBL?>length_edit" class="<?=self::$MODULE_LBL?>gabsEdit" value=""> X
                            <input type="text" name="<?=self::$MODULE_LBL?>width_edit"  id="<?=self::$MODULE_LBL?>width_edit"  class="<?=self::$MODULE_LBL?>gabsEdit" value=""> X
                            <input type="text" name="<?=self::$MODULE_LBL?>height_edit" id="<?=self::$MODULE_LBL?>height_edit" class="<?=self::$MODULE_LBL?>gabsEdit" value="">
                            <a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').apply()">OK</a>
                        </div>

                        <input type="hidden" name="<?=self::$MODULE_LBL?>length" id="<?=self::$MODULE_LBL?>length" value="<?=$order->getGoods()->getLength()?>">
                        <input type="hidden" name="<?=self::$MODULE_LBL?>width"  id="<?=self::$MODULE_LBL?>width" value="<?=$order->getGoods()->getWidth()?>">
                        <input type="hidden" name="<?=self::$MODULE_LBL?>height" id="<?=self::$MODULE_LBL?>height" value="<?=$order->getGoods()->getHeight()?>">
                    </td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>block_gabariles">
                    <td><?=Tools::getMessage('LBL_weight')?></td>
                    <td>
                        <div id="<?=self::$MODULE_LBL?>weightPlace">
                            <span id="<?=self::$MODULE_LBL?>weightLabel"><?=$order->getGoods()->getWeight()?></span>
                            <a href='javascript:void(0)' onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').edit('weight')"><?=Tools::getMessage('BTN_EDIT')?></a>
                        </div>
                        <div id="<?=self::$MODULE_LBL?>weightEditor">
                            <input type="text" name="<?=self::$MODULE_LBL?>weight_edit" id="<?=self::$MODULE_LBL?>weight_edit" class="<?=self::$MODULE_LBL?>gabsEdit" value="">
                            <a href="javascript:void(0)" onclick="<?=self::$MODULE_LBL?>export.getPage('gabs').apply()">OK</a>
                        </div>
                        <input type="hidden" name="<?=self::$MODULE_LBL?>weight" id="<?=self::$MODULE_LBL?>weight" value="<?=$order->getGoods()->getWeight()?>">
                    </td>
                </tr>

                <?// RECEIVER?>
                <?$receiver = $order->getReceivers()->getFirst();?>
                <?Tools::placeSOHeaderRow('RECEIVER');?>

                <?Tools::placeSORow('clientName','text',$receiver->getFullName());?>
                <?Tools::placeSORow('clientEmail','text',$receiver->getEmail());?>
                <?Tools::placeSORow('clientPhone','text',$receiver->getPhone());?>
                <?// PICKUPS?>
                <tr class="<?=self::$MODULE_LBL?>delivery_pickup">
                    <td><?=Tools::getMessage('LBL_PickupPoint')?></td>
                    <td>
                        <span id="<?=self::$MODULE_LBL?>PickupPointError"></span>
                        <div id="<?=self::$MODULE_LBL?>PickupPointContainer"></div>
                    </td>
                </tr>


                <?// PAYMENT?>
                <?Tools::placeSOHeaderRow('PAYMENT')?>
                <?Tools::placeSORow('payment_isBeznal','checkbox',($order->getPayment()->getIsBeznal()),false,
                    "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.onPayedChange()\"");?>
                <?Tools::placeSORow('payment_sum','hidden',$order->getPayment()->getGoods()->getAmount());?>
                <?//Tools::placeSORow('payment_prepayment','text',$order->getPayment()->getPayed(),false,"onkeyup=\"".self::$MODULE_LBL."export.getPage('main').events.onPrepaymentChange()\"");?>
                <?Tools::placeSORow('deliveryCost','text',$order->getPayment()->getDelivery()->getAmount());?>
                <?Tools::placeSORow('paymentType','select',Adapter::convertPaymentTypes($order->getPayment()->getType()),Adapter::getPaymentTypes());?>
                <?//Tools::placeSORow('payment_ndsDefault','select',$order->getPayment()->getNdsDefault(),Adapter::getNDSTypes());?>
                <?//Tools::placeSORow('payment_ndsDelivery','select',$order->getPayment()->getNdsDelivery(),Adapter::getNDSTypes());?>

                <tr><td colspan="2"><hr></td></tr>
                <?Tools::placeSORow('price','hidden',$order->getPayment()->getCost()->getAmount());?>
                </tbody></table>
        </div>
        <?
    }

    protected static function loadExportCSS(){
        Tools::getCommonCss();
        ?>
        <style>
            #<?=self::$MODULE_LBL?>wndOrder{
                width:100%;
            }
            [class ^= "<?=self::$MODULE_LBL?>block_"] {
                display:none;
            }

            #<?=self::$MODULE_LBL?>documentType{
                max-width: 200px;
            }
            .<?=self::$MODULE_LBL?>unseen{
                display: none !important;
            }

            #<?=self::$MODULE_LBL?>DeliveryModeContainer{
                border-collapse: collapse;
            }

            #<?=self::$MODULE_LBL?>DeliveryModeContainer td{
                padding: 3px;
            }

            /*gabs*/
            .<?=self::$MODULE_LBL?>gabsEdit{
                width: 40px;
            }
            #<?=self::$MODULE_LBL?>gabsEditor,#<?=self::$MODULE_LBL?>weightEditor,#<?=self::$MODULE_LBL?>volumeEditor{
                display: none;
            }

            /* GABS & LOTS*/
            .<?=self::$MODULE_LBL?>cargoHeader,.<?=self::$MODULE_LBL?>lotHeader,.<?=self::$MODULE_LBL?>addHeader{
                background-color: #E0E8EA;
                color: #4B6267;
                font-size: 14px;
                text-align: center !important;
                text-shadow: 0px 1px #FFF;
                padding: 8px 4px 10px !important;
                height: 30px;
            }

            .<?=self::$MODULE_LBL?>cargoHeader td,.<?=self::$MODULE_LBL?>lotHeader td{
                text-align  : center !important;
                font-weight : bold;
            }

            .<?=self::$MODULE_LBL?>cargoHeader td:last-child,.<?=self::$MODULE_LBL?>lotHeader td:last-child{
                width: 40px;
            }

            #<?=self::$MODULE_LBL?>cargoEdit,#<?=self::$MODULE_LBL?>lotEdit{
                width: 100%;
            }

            .<?=self::$MODULE_LBL?>cargoExpand,.<?=self::$MODULE_LBL?>cargoDelete,.<?=self::$MODULE_LBL?>lotExpand,.<?=self::$MODULE_LBL?>lotDelete{
                width  : 15px;
                height : 15px;
                float  : left;
                cursor : pointer;
            }

            .<?=self::$MODULE_LBL?>cargoExpand,.<?=self::$MODULE_LBL?>lotExpand{
                margin: 0px 3px;
                background: url("<?=Tools::getImagePath()?>arrows.png");
            }

            .<?=self::$MODULE_LBL?>cargoExpand.<?=self::$MODULE_LBL?>Expanded,.<?=self::$MODULE_LBL?>lotExpand.<?=self::$MODULE_LBL?>Expanded{
                background-position-y: 15px !important;
                color: red !important;
            }

            .<?=self::$MODULE_LBL?>cargoExpand:hover,.<?=self::$MODULE_LBL?>lotExpand:hover{
                background-position-x: 15px;
            }

            .<?=self::$MODULE_LBL?>cargoDelete,.<?=self::$MODULE_LBL?>lotDelete{
                background: url("<?=Tools::getImagePath()?>closer.png");
                background-position-y: 15px;
            }
            .<?=self::$MODULE_LBL?>cargoDelete:hover,.<?=self::$MODULE_LBL?>lotDelete:hover{
                background-position-y: 0px !important;
            }

            .<?=self::$MODULE_LBL?>cargoItems input[type='text'], .<?=self::$MODULE_LBL?>lotItems input[type='text']{
                width: 80px;
            }

            .<?=self::$MODULE_LBL?>cargoItems, .<?=self::$MODULE_LBL?>lotItems{
                width  : 100%;
                border : 1px solid #E0E8EA;
                text-align: center !important;
            }

            .<?=self::$MODULE_LBL?>cargoParams input[type='text']{
                width: 144px;
            }

            .<?=self::$MODULE_LBL?>cargoParams{
                width  : 100%;
                border : 1px solid #E0E8EA;
                background-color: #EDF2F3;
            }

            .<?=self::$MODULE_LBL?>cargoDimensions{
                width: 30px !important;
            }


            .<?=self::$MODULE_LBL?>cargoItems th{
                background-color: #EDF2F3;
                padding: 2px;
            }

            .<?=self::$MODULE_LBL?>cargoItems td{
                text-align: center;
            }

            .<?=self::$MODULE_LBL?>newGood td{
                border-top: 1px solid #E0E8EA;
            }

            /*cargoMover lotMover*/
            #<?=self::$MODULE_LBL?>cargoMover, #<?=self::$MODULE_LBL?>cargoMover p,#<?=self::$MODULE_LBL?>lotMover, #<?=self::$MODULE_LBL?>lotMover p{
                width: 100px;
                text-align: center !important;
            }
            #<?=self::$MODULE_LBL?>cargoMover input[type='text']{
                width: 30px;
            }

            .<?=self::$MODULE_LBL?>lotItems{
                padding: 0px 5px;
            }

            .<?=self::$MODULE_LBL?>lotItems td{
                text-align: center !important;
            }

            .<?=self::$MODULE_LBL?>lotItemName{
                width: 200px;
            }

            /* ADDITIONAL */
            #<?=self::$MODULE_LBL?>goodsEdit{
                width : 100%;
            }
            #<?=self::$MODULE_LBL?>goodsEdit td{
                text-align : center;
            }
            #<?=self::$MODULE_LBL?>editCntrName{
                max-width: 180px;
            }
            .<?=self::$MODULE_LBL?>CisMarker{
                background-image: url(<?=Tools::getImagePath()?>details.png);
                width  : 13px;
                height : 15px;
                cursor : pointer;
                position: relative;
                top: 20px;
                left: 175px;
            }
            .<?=self::$MODULE_LBL?>QRSelector{
                margin-bottom: 5px;
                background-color: #f5f9f9;
                cursor : pointer;
                padding : 5px;
                word-wrap: break-word;
            }
            .<?=self::$MODULE_LBL?>QRSelector:hover{
                background-color: #E0E8EA;
            }

        </style>
        <?
    }



    protected static function addButton($html)
    {
        if(!isset(self::$arButtons))
        {
            self::$arButtons = array();
        }
        if(count(self::$arButtons) && count(self::$arButtons) % 3 === 0)
        {
            self::$arButtons []= '<br><br>';
        }
        self::$arButtons []= $html;
    }

    protected function getCountryArray(){
        $list = Enumerations::getCountryCodes();
        $arReturn = array();
        foreach ($list as $numCode => $letCode){
            $arReturn[$numCode] = Tools::getMessage('CNTRY_'.$letCode);
        }
        return $arReturn;
    }
}