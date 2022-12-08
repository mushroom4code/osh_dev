<?
use Bitrix\Main\Context,
    Bitrix\Main\Type,
    Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Sale\Internals\ShipmentTable,
    Bitrix\Main\HttpApplication,
    Osh\Delivery\COshAPI,
    Osh\Delivery\Options\Config,
    Osh\Delivery\Logger;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

CJSCore::Init(array('ajax', 'jquery'));

define('OSH_DELIVERY_COMMON_ORDERS', 0);
define('OSH_DELIVERY_DIRECT_ORDERS', 1);
define('OSH_DELIVERY_EXPORT_ORDERS', 2);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if($saleModulePermissions < "U"){
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
Loader::includeModule('sale');
Loader::includeModule('currency');
Loader::IncludeModule("osh.shipping");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/sale/admin/order_shipment.php');

//--------------------------------------------
$oOshAPI = COshAPI::getInstance();
$sTokenString = $oOshAPI->getTokenString();
//--------------------------------------------
global $DB;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$bCreateAgent = (bool)($request["mode"] === "createAgent" && $request->isPost());

//\Osh\Delivery\Agents::createClearKladr();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/prolog.php");

$moduleConfig = new Config();

$oContext = Context::getCurrent();
$tableId = "osh_shipment_upload";
$curPage = $oContext->getRequest()->getRequestUri();
$lang = $oContext->getLanguage();
$siteId = $oContext->getSite();
$errors = '';
$sAdmin = new CAdminSorting($tableId,"ORDER_ID","DESC");
$lAdmin = new CAdminList($tableId,$sAdmin);

$filter = array(
    'filter_order_id_from',
    'filter_order_id_to',
    'filter_shipment_id_from',
    'filter_shipment_id_to',
    'filter_type',
    'filter_price_delivery_from',
    'filter_price_delivery_to',
    'filter_delivery_doc_num',
    'filter_account_num',
    'filter_osh_status',
    'filter_user_id',
    'filter_user_login',
    'filter_user_email'
);

$lAdmin->InitFilter($filter);

$arFilter = array();

$filter_order_id_from = intval($filter_order_id_from);
$filter_order_id_to = intval($filter_order_id_to);

if(intval($filter_price_delivery_from) > 0)
    $arFilter['>=PRICE_DELIVERY'] = $filter_price_delivery_from;
if(intval($filter_price_delivery_to) > 0)
    $arFilter['<=PRICE_DELIVERY'] = $filter_price_delivery_to;

if(strlen($filter_delivery_doc_num) > 0)
    $arFilter['DELIVERY_DOC_NUM'] = $filter_deducted;

if($filter_order_id_from > 0)
    $arFilter['>=ORDER_ID'] = $filter_order_id_from;
if($filter_order_id_to > 0)
    $arFilter['<=ORDER_ID'] = $filter_order_id_to;

if($filter_shipment_id_from > 0)
    $arFilter['>=ID'] = $filter_shipment_id_from;
if($filter_shipment_id_to > 0)
    $arFilter['<=ID'] = $filter_shipment_id_to;

if(strlen($filter_account_num) > 0)
    $arFilter['ORDER.ACCOUNT_NUMBER'] = $filter_account_num;

if(strlen($filter_osh_status) > 0){
    $arFilter['TRACKING_DESCRIPTION'] = $filter_osh_status;
}
if(strlen($filter_user_login) > 0)
    $arFilter["ORDER.USER.LOGIN"] = trim($filter_user_login);
if(strlen($filter_user_email) > 0)
    $arFilter["ORDER.USER.EMAIL"] = trim($filter_user_email);

if(IntVal($filter_user_id) > 0)
    $arFilter["ORDER.USER_ID"] = IntVal($filter_user_id);
switch(intval($filter_type)){
    case OSH_DELIVERY_COMMON_ORDERS:default:
        $arOshIds = \COshDeliveryHelper::getCommonDeliveries();
        break;
    case OSH_DELIVERY_DIRECT_ORDERS:
        $arOshIds = \COshDeliveryHelper::getDirectDeliveries();
        break;
    case OSH_DELIVERY_EXPORT_ORDERS:
        $arOshIds = \COshDeliveryHelper::getExportDeliveries();
        break;
}
$arFilterType = array(
    OSH_DELIVERY_COMMON_ORDERS => Loc::getMessage('OSH_DELIVERY_TYPE_COMMON'),
    OSH_DELIVERY_DIRECT_ORDERS => Loc::getMessage('OSH_DELIVERY_TYPE_DIRECT'),
    OSH_DELIVERY_EXPORT_ORDERS => Loc::getMessage('OSH_DELIVERY_TYPE_EXPORT')
);
if($arID = $lAdmin->GroupAction()){
    $shipments = array();

    $params = array(
        'select' => array('ID','ORDER_ID'),
        'filter' => array(
            'DELIVERY_ID' => $arOshIds,
        ),
        'limit' => 1000
    );
    if($request['action_target'] != 'selected'){
        $params['filter']['ID'] = $request['ID'];
    }
    $result = \Osh\Delivery\Helpers\Order::getByShipment($params);

    foreach($result as $arResult){
        if( ! isset($shipments[$arResult['ORDER_ID']]))
            $shipments[$arResult['ORDER_ID']] = array();
        $shipments[$arResult['ORDER_ID']][] = $arResult['ID'];
    }
    if(OSH_DELIVERY_DIRECT_ORDERS == $filter_type){
        $arDirectPackages = array();
        $arShipments = array();
    }
    if($request['action'] == 'fullfill' && Config::isAutomaticUpload()){
        $departureTypeSwitched = true;
        $moduleConfig->saveParam('departure_type',Config::DEPARTURE_TYPE_MAN);
    }
    foreach($shipments as $orderId => $ids){
        $isDeleted = false;
        /** @var \Bitrix\Sale\Order $currentOrder */
        $currentOrder = \Bitrix\Sale\Order::load($orderId);
        if( ! $currentOrder)
            continue;

        /** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
        $shipmentCollection = $currentOrder->getShipmentCollection();

        $arStatuses = Config::getTrackingStatuses();
        $arOrderStatuses = Config::getTrackingStatusesOrder();
        foreach($ids as $id){
            if(strlen($id) <= 0)
                continue;

            /** @var \Bitrix\Sale\Shipment $shipment */
            $shipment = $shipmentCollection->getItemById($id);
            if( ! $shipment){
                continue;
	    }
            $sShipmentAccountNumber = $currentOrder->getField('ACCOUNT_NUMBER');
            switch($request['action']){
                case "update":
                    @set_time_limit(0);
                    $arParams = array("external_id" => $sShipmentAccountNumber);
                    try{
                        $arResponse = COshDeliveryHelper::trackPackage($arParams);
                        $shipment->setField('TRACKING_DESCRIPTION',$arResponse['current_status']);
                        if( ! $shipment->getField('TRACKING_NUMBER')){
                            $shipment->setField('TRACKING_NUMBER',$arResponse['tracking_number']);
                        }
                        if( ! $shipment->getField('DELIVERY_DOC_NUM')){
                            $shipment->setField('DELIVERY_DOC_NUM',$arResponse['id']);
                        }
                        $shipment->setField('DELIVERY_DOC_DATE',Type\DateTime::createFromTimestamp(strtotime($arResponse['history'][0]['date'])));
                        $status = $arStatuses[$arResponse['current_status']];
                        $orderStatus = $arOrderStatuses[$arResponse['current_status']];
                        if(!empty($status)){
                            \Osh\Delivery\Helpers\Order::setStatus($shipment, $status);
                        }
                        if(!empty($orderStatus)){
                            \Osh\Delivery\Helpers\Order::setStatus($shipment, $orderStatus);
                            $isOrderSave = true;
                        }else{
                            $isOrderSave = false;
                        }
                        //---------------------------------------------------------------
                        $res = $shipment->save();
                        if($isOrderSave){
                            $shipment->getCollection()->getOrder()->save();
                        }
                        if( ! $res->isSuccess()){
                            $lAdmin->AddGroupError(implode('\n',$res->getErrorMessages()));
                        }
                    }catch(\Exception $e){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#OSH_MESSAGE#" => $e->getMessage());
                        $lAdmin->AddGroupError(Loc::getMessage("OSH_ERROR_GET_STATUS",$arReplaceErr));
                    }
                    break;
                case "fullfill":
                    @set_time_limit(0);
                    /*if($shipment->getField('DELIVERY_DOC_NUM')){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#TRACK_NUM#" => $shipment->getField('DELIVERY_DOC_NUM'));
                        ShowNote(Loc::getMessage("OSH_ERROR_TRACK_ALREADY",$arReplaceErr));
                        continue;
                    }elseif($shipment->getField('DEDUCTED') == "Y"){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#TRACK_NUM#" => $shipment->getField('TRACKING_NUMBER'));
                        ShowNote(Loc::getMessage("OSH_ERROR_SENT_ALREADY",$arReplaceErr));
                        continue;
                    }elseif(!empty($shipment->getField('TRACKING_NUMBER'))){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#TRACK_NUM#" => $shipment->getField('TRACKING_NUMBER'));
                        ShowNote(Loc::getMessage("OSH_ERROR_TRACK_ALREADY", $arReplaceErr));
                        continue;
                    }else*/if($shipment->getField('ALLOW_DELIVERY') == "N"){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber);
                        ShowNote(Loc::getMessage("OSH_ERROR_SEND_NOT_ALLOWED",$arReplaceErr));
                        continue;
                    }else{
                        switch(intval($filter_type)){
                            case OSH_DELIVERY_COMMON_ORDERS: case OSH_DELIVERY_EXPORT_ORDERS: default:
                                try{
                                    $result = COshDeliveryHelper::sendOrder($shipment);
                                    CAdminMessage::ShowNote($result);
                                }catch(\Exception $e){
                                    $shipment->setField("MARKED","Y");
                                    $shipment->setField("REASON_MARKED",$e->getMessage());
                                    $res = $shipment->save();
                                    $lAdmin->AddGroupError($e->getMessage());
                                    Logger::exception($e);
                                }
                                break;
                            case OSH_DELIVERY_DIRECT_ORDERS:
                                $arShipments[$sShipmentAccountNumber] = $shipment;
                                break;
                        }
                    }
                    break;
                case "remove":
                    if($shipment->getField('DELIVERY_DOC_NUM') && $shipment->getField('DEDUCTED') == "Y"){
                        $arRequest = $oOshAPI->Request("removePackage",array("external_id" => $sShipmentAccountNumber));
                        if(empty($arRequest["error"])){
                            if(Config::isDeduct()){
                                $shipment->setField('DEDUCTED','N');
                            }
                            $shipment->setField('XML_ID',"");
                            $shipment->setField('DELIVERY_DOC_NUM',"");
                            $shipment->setField('DELIVERY_DOC_DATE',null);
                            $shipment->setField('TRACKING_NUMBER',"");
                            $shipment->setField('TRACKING_DESCRIPTION',$arRequest['result']['current_status']);
                            $res = $shipment->save();
                            CAdminMessage::ShowNote(Loc::getMessage("OSH_REMOVE_SUCCESS",array("#ORDER#" => $sShipmentAccountNumber)));
                        }else{
                            $arReplaceErr = array("#ORDER#" => $sShipmentAccountNumber, "#OSH_MESSAGE#" => $arRequest["error"]["message"]);
                            $lAdmin->AddGroupError(Loc::getMessage("OSH_REMOVE_ERROR", $arReplaceErr));
                        }
                    }else{
                        $lAdmin->AddGroupError(Loc::getMessage("OSH_ERROR_STATUS",array("#ID#" => $id,"#SHIPMENT_NUM#" => $sShipmentAccountNumber)));
                    }
                    break;
                case "label":
                    $arParams = array("external_id" => $sShipmentAccountNumber);
                    try{
                        $arResponse = COshDeliveryHelper::trackPackage($arParams);
                        $linkUrl = $arResponse['label_url'];
                        $message = loc::getMessage('OSH_PRINT_LABEL', array('#ORDER#' => $sShipmentAccountNumber));
                        CAdminMessage::ShowMessage(array(
                            "MESSAGE" => <<<JS
                            <p>$message</p>
                            <script type='text/javascript'>
                                var labelWin = window.open('$linkUrl');
                                if(!!labelWin){
                                    labelWin.document.write('<img src="$linkUrl"/>');
                                    labelWin.document.close();
                                    setTimeout(function(){
                                        labelWin.focus();
                                        labelWin.print();
                                        labelWin.close();
                                    },1000);
                                }
                            </script>
JS
,                           "HTML" => true,
                            "TYPE" => "OK"
                        ));
                    }catch(\Exception $e){
                        $arReplaceErr = array("#ORDER#" => $sShipmentAccountNumber, "#OSH_MESSAGE#" => $e->getMessage());
                        $lAdmin->AddGroupError(Loc::getMessage("OSH_ERROR_GET_LABEL", $arReplaceErr));
                    }
                    break;
                case "allow_delivery":
                    $shipment->allowDelivery();
                    $shipment->save();
                    break;
            }
        }
    }
    if($departureTypeSwitched){
        $moduleConfig->saveParam('departure_type',Config::DEPARTURE_TYPE_AUTO);
    }
    if(!empty($arShipments) && intval($filter_type) == OSH_DELIVERY_DIRECT_ORDERS){
        try{
            $arDirectResult = COshDeliveryHelper::sendDirectOrders($arShipments);
            if(!empty($arDirectResult["SUCCESS"])){
                CAdminMessage::ShowNote(Loc::getMessage("OSH_SEND_SUCCESS_MULTY", array("#ORDER_IDS#" => implode(",",$arDirectResult["SUCCESS"]))));
            }
            if(!empty($arDirectResult["ERROR"])){
                $lAdmin->AddGroupError(Loc::getMessage("OSH_SEND_ERROR_MULTY", array("#ORDER_IDS#" => implode(",",$arDirectResult["ERROR"]))));
            }
        } catch (\Exception $e) {
            $lAdmin->AddGroupError($e->getMessage());
            Logger::exception($e);
        }
    }
}

$headers = array(
    array("id" => "ACCOUNT_NUMBER","content" => Loc::getMessage("SALE_ORDER_ACCOUNT_NUMBER"),"sort" => "ORDER.ACCOUNT_NUMBER", "default" => true),
    array('id' => 'ORDER_STATUS', "content" => Loc::getMessage("BITRIX_ORDER_STATUS"),"sort" => 'ORDER.STATUS.ID',"default" => true),
    array("id" => "TRACKING_NUMBER","content" => Loc::getMessage("SALE_ORDER_TRACKING_NUMBER"),"sort" => "TRACKING_NUMBER","default" => true),
    array("id" => "ID", "content" => Loc::getMessage("BITRIX_ORDER_SHIPMENT_ID"), "sort" => "ID", "default" => true),
    array("id" => "STATUS","content" => Loc::getMessage("SALE_ORDER_STATUS"),"sort" => 'STATUS.ID',"default" => true),
    array("id" => "ORDER_USER_NAME","content" => Loc::getMessage("SALE_ORDER_USER_NAME"),"sort" => "ORDER_USER_NAME","default" => true),
    array("id" => "REASON_MARKED","content" => Loc::getMessage("SALE_ORDER_REASON_MARKED_ID"),"default" => true),
    array("id" => "PRICE_DELIVERY","content" => Loc::getMessage("SALE_ORDER_PRICE_DELIVERY"),"sort" => "PRICE_DELIVERY","default" => false),
    array("id" => "DELIVERY_DOC_NUM","content" => Loc::getMessage("SALE_ORDER_DELIVERY_DOC_NUM"),"sort" => "DELIVERY_DOC_NUM","default" => false),
    array("id" => "DELIVERY_DOC_DATE","content" => Loc::getMessage("SALE_ORDER_DELIVERY_DOC_DATE"),"sort" => "DELIVERY_DOC_DATE","default" => false),
    array("id" => "RESPONSIBLE_BY","content" => Loc::getMessage("SALE_ORDER_DELIVERY_RESPONSIBLE_ID"),"sort" => "","default" => false),
    array("id" => "TRACKING_DESCRIPTION","content" => Loc::getMessage("OSH_CARRIAGE_STATUS"),"sort" => "TRACKING_DESCRIPTION","default" => false),
    array("id" => "PARAMETERS","content" => Loc::getMessage("SALE_ORDER_PARAMETERS"),"default" => false),
    array("id" => "CANCELED","content" => Loc::getMessage("SALE_ORDER_CANCELED"),"sort" => "CANCELED","default" => false),
    array("id" => "REASON_CANCELED","content" => Loc::getMessage("SALE_ORDER_REASON_CANCELED"),"default" => false),
    array("id" => "MARKED","content" => Loc::getMessage("OSH_SALE_ORDER_MARKED"),"sort" => "MARKED","default" => false),
);
$select = array(
    '*',
    'STATUS_NAME' => 'STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
    'ORDER.CURRENCY',
    'ORDER.ACCOUNT_NUMBER',
    'COMPANY_BY.NAME',
    'EMP_DEDUCTED_BY_NAME' => 'EMP_DEDUCTED_BY.NAME',
    'EMP_DEDUCTED_BY_LAST_NAME' => 'EMP_DEDUCTED_BY.LAST_NAME',
    'EMP_ALLOW_DELIVERY_BY_NAME' => 'EMP_ALLOW_DELIVERY_BY.NAME',
    'EMP_ALLOW_DELIVERY_BY_LAST_NAME' => 'EMP_ALLOW_DELIVERY_BY.LAST_NAME',
    'EMP_MARKED_BY_BY_NAME' => 'EMP_MARKED_BY.NAME',
    'EMP_MARKED_BY_LAST_NAME' => 'EMP_MARKED_BY.LAST_NAME',
    'ORDER_USER_NAME' => 'ORDER.USER.NAME',
    'ORDER_USER_LAST_NAME' => 'ORDER.USER.LAST_NAME',
    'ORDER_USER_ID' => 'ORDER.USER_ID',
    'ORDER_STATUS' => 'ORDER.STATUS_ID',
    'RESPONSIBLE_BY_LAST_NAME' => 'RESPONSIBLE_BY.LAST_NAME',
    'RESPONSIBLE_BY_NAME' => 'RESPONSIBLE_BY.NAME'
);
$arFilter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
$arFilter['DELIVERY_ID'] = $arOshIds;
$arFilter['!=SYSTEM'] = 'Y';
$arFilter["CANCELED"] = 'N';

$params = array(
    'select' => $select,
    'filter' => $arFilter,
    'order' => array($by => $order),
);

$usePageNavigation = true;
$navyParams = array();

$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($tableId));
if($navyParams['SHOW_ALL']){
    $usePageNavigation = false;
}else{
    $navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
    $navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}

if($usePageNavigation){
    $params['limit'] = $navyParams['SIZEN'];
    $params['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}

$totalPages = 0;

if($usePageNavigation){
    $countQuery = new \Bitrix\Main\Entity\Query(ShipmentTable::getEntity());
    $countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT','COUNT(1)'));
    $countQuery->setFilter($params['filter']);
    $totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
    unset($countQuery);
    $totalCount = (int)$totalCount['CNT'];

    if($totalCount > 0){
        $totalPages = ceil($totalCount / $navyParams['SIZEN']);

        if($navyParams['PAGEN'] > $totalPages)
            $navyParams['PAGEN'] = $totalPages;

        $params['limit'] = $navyParams['SIZEN'];
        $params['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
    }
    else{
        $navyParams['PAGEN'] = 1;
        $params['limit'] = $navyParams['SIZEN'];
        $params['offset'] = 0;
    }
}

$dbResultList = new CAdminResult(ShipmentTable::getList($params),$tableId);

if($usePageNavigation){
    $dbResultList->NavStart($params['limit'],$navyParams['SHOW_ALL'],$navyParams['PAGEN']);
    $dbResultList->NavRecordCount = $totalCount;
    $dbResultList->NavPageCount = $totalPages;
    $dbResultList->NavPageNomer = $navyParams['PAGEN'];
}else{
    $dbResultList->NavStart();
}


$lAdmin->NavText($dbResultList->GetNavPrint(Loc::getMessage("group_admin_nav")));

$lAdmin->AddHeaders($headers);

$allSelectedFields = array(
    "ORDER_ID" => false,
    "PAID" => false,
    "DATE_PAID" => false
);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();
$allSelectedFields = array_merge($allSelectedFields,array_fill_keys($visibleHeaders,true));

while($shipment = $dbResultList->Fetch()){
    $row = & $lAdmin->AddRow($shipment['ID'],$shipment);
    $filterParams = GetFilterParams("filter_");
    $sShipmentLink = <<<HTML
        <a href="sale_order_shipment_edit.php?order_id={$shipment['ORDER_ID']}&shipment_id={$shipment['ID']}&lang={$lang}{$filterParams}" target="_blank">{$shipment['ID']}</a>
HTML;
    $row->AddField("ID",$sShipmentLink);
    $sOrderLink = <<<HTML
        <a href="sale_order_edit.php?ID={$shipment['ORDER_ID']}&lang={$lang}{$filterParams}" target="_blank">{$shipment['ORDER_ID']}</a>
HTML;
    $row->AddField("ORDER_ID",$sOrderLink);
    $deliveryName = htmlspecialcharsbx($shipment['DELIVERY_NAME']);
    $sDeliveryLink = <<<HTML
        <a href="sale_delivery_service_edit.php?ID={$shipment['DELIVERY_ID']}&lang={$lang}{$filterParams}">{$deliveryName}</a>
HTML;
    $row->AddField("DELIVERY_NAME",$sDeliveryLink);
    $row->AddField("ACCOUNT_NUMBER",htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_ORDER_ACCOUNT_NUMBER']));
    $row->AddField("ALLOW_DELIVERY",($shipment["ALLOW_DELIVERY"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO"));
    $row->AddField("COMPANY_BY","<a href=\"sale_company_edit.php?ID=" . $shipment['COMPANY_ID'] . "&lang=" . $lang . GetFilterParams("filter_") . "\">" . htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_COMPANY_BY_NAME']) . "</a>");
    $row->AddField("ORDER_USER_NAME","<a href='/bitrix/admin/user_edit.php?ID=" . $shipment['ORDER_USER_ID'] . "&lang=" . $lang . "'>" . htmlspecialcharsbx($shipment['ORDER_USER_NAME']) . " " . htmlspecialcharsbx($shipment['ORDER_USER_LAST_NAME']) . "</a>");
    $row->AddField("PRICE_DELIVERY",\CCurrencyLang::CurrencyFormat($shipment['PRICE_DELIVERY'],$shipment['SALE_INTERNALS_SHIPMENT_ORDER_CURRENCY']));

    $row->AddField("DEDUCTED",(($shipment["DEDUCTED"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_DEDUCTED_ID'] . "\">" . htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_EMP_DEDUCTED_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_EMP_DEDUCTED_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_DEDUCTED']));
    $row->AddField("RESPONSIBLE_BY","<a href=\"user_edit.php?ID=" . $shipment['RESPONSIBLE_ID'] . "\">" . htmlspecialcharsbx($shipment['RESPONSIBLE_BY_NAME']) . " " . htmlspecialcharsbx($shipment['RESPONSIBLE_BY_LAST_NAME']) . "</a>");
    $row->AddField("ALLOW_DELIVERY",(($shipment["ALLOW_DELIVERY"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_ALLOW_DELIVERY_ID'] . "\">" . htmlspecialcharsbx($shipment['EMP_ALLOW_DELIVERY_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['EMP_ALLOW_DELIVERY_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_ALLOW_DELIVERY']));
    $row->AddField("CANCELED",(($shipment["CANCELED"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_CANCELED_ID'] . "\">" . htmlspecialcharsbx($shipment['EMP_CANCELED_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['EMP_CANCELED_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_CANCELED']));
    $row->AddField("MARKED",(($shipment["MARKED"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_MARKED_ID'] . "\">" . htmlspecialcharsbx($shipment['EMP_MARKED_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['EMP_MARKED_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_MARKED']));
    $row->AddField("REASON_MARKED",$shipment["REASON_MARKED"]);
    $row->AddField("STATUS",htmlspecialcharsbx($shipment['STATUS_NAME']));
    $orderStatusData = Osh\Delivery\Helpers\Order::getStatusData($shipment['ORDER_STATUS']);
    $row->AddField("ORDER_STATUS", $orderStatusData['NAME']);

    /*$arActions = array();
    $row->AddActions($arActions);*/
}

$lAdmin->AddGroupActionTable(array(
    "fullfill" => Loc::getMessage("OSH_SEND_FULLFILL"),
    "update" => Loc::getMessage("OSH_UPDATE_STATUS"),
    "remove" => Loc::getMessage("OSH_DELETE"),
    "label" => Loc::getMessage("OSH_LABEL"),
    "allow_delivery" => Loc::getMessage("OSH_ALLOW_DELIVERY")
));

$aContext[] = array(
    "ICON" => "properties",
    "TEXT" => Loc::getMessage("OSH_BTN_SETTINGS"),
    "TITLE" => Loc::getMessage("OSH_BTN_SETTINGS_TEXT"),
    "LINK" => "javascript:go2params()"
);

$aContext[] = array(
    "ICON" => "properties",
    "TEXT" => Loc::getMessage("OSH_BTN_SETTINGS_DOC"),
    "TITLE" => Loc::getMessage("OSH_BTN_SETTINGS_DOC_TEXT"),
    "LINK" => "javascript:go2doc()"
);

//$checkAgent = \Osh\Delivery\Agents::getCheckOrders();
$checkAgent = '';
if(!empty($checkAgent) && $checkAgent['ACTIVE'] == 'Y'){
    $aContext[] = array(
        "ICON" => "properties",
        "TEXT" => Loc::getMessage("OSH_BTN_CHECK_AGENT"),
        "TITLE" => Loc::getMessage("OSH_BTN_CHECK_AGENT_TEXT"),
        "LINK" => "javascript:go2agent({$checkAgent['ID']})"
    );
}
//$sendAgent = \Osh\Delivery\Agents::getSendOrders();
$sendAgent = '';
if(!empty($sendAgent) && $sendAgent['ACTIVE'] == 'Y'){
    $aContext[] = array(
        "ICON" => "properties",
        "TEXT" => Loc::getMessage("OSH_BTN_SEND_AGENT"),
        "TITLE" => Loc::getMessage("OSH_BTN_SEND_AGENT_TEXT"),
        "LINK" => "javascript:go2agent({$sendAgent['ID']})"
    );
}


$lAdmin->AddAdminContextMenu($aContext);?>
<?$lAdmin->AddFooter(
    array(
        array(
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $dbResultList->SelectedRowsCount()
        ),
        array(
            "counter" => true,
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value" => "0"
        ),
    )
);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("OSH_TITLE"));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?=$curPage?>?">
    <?
    $cafilter = array(
        Loc::getMessage("SHIPMENT_ORDER_ID"),
        Loc::getMessage("SHIPMENT_ID"),
        Loc::getMessage("OSH_DELIVERY_TYPE"),
        Loc::getMessage("SALE_ORDER_PRICE_DELIVERY"),
        Loc::getMessage("SALE_ORDER_DELIVERY_DOC_NUM"),
        Loc::getMessage("SALE_ORDER_ACCOUNT_NUM"),
        Loc::getMessage("OSH_CARRIAGE_STATUS"),
        Loc::getMessage("SALE_SHIPMENT_F_USER_ID"),
        Loc::getMessage("SALE_SHIPMENT_F_USER_LOGIN"),
        Loc::getMessage("SALE_SHIPMENT_F_USER_EMAIL")
    );
    $arOshStatuses = array("new","sent","delivered","removed");
    $oFilter = new CAdminFilter(
        $tableId . "_filter", $cafilter
    );

    $oFilter->Begin();
    ?>
    <tr>
        <td><?=Loc::getMessage("SHIPMENT_ORDER_ID")?>:</td>
        <td>
            <script type="text/javascript">
                function changeFilterOrderIdFrom(){
                    if (document.find_form.filter_order_id_to.value.length <= 0)
                        document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
                }
            </script>
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_FROM");?>
            <input type="text" name="filter_order_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_order_id_from) > 0) ? intval($filter_order_id_from) : ""?>" size="10">
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_TO");?>
            <input type="text" name="filter_order_id_to" value="<?=(intval($filter_order_id_to) > 0) ? intval($filter_order_id_to) : ""?>" size="10">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SHIPMENT_ID")?>:</td>
        <td>
            <script type="text/javascript">
                function changeFilterShipmentIdFrom(){
                    if (document.find_form.filter_shipment_id_to.value.length <= 0)
                        document.find_form.filter_shipment_id_to.value = document.find_form.filter_shipment_id_from.value;
                }
            </script>
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_FROM");?>
            <input type="text" name="filter_shipment_id_from" OnChange="changeFilterShipmentIdFrom()" value="<?=(intval($filter_shipment_id_from) > 0) ? intval($filter_shipment_id_from) : ""?>" size="10">
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_TO");?>
            <input type="text" name="filter_shipment_id_to" value="<?=(intval($filter_shipment_id_to) > 0) ? intval($filter_shipment_id_to) : ""?>" size="10">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("OSH_DELIVERY_TYPE");?>:</td>
        <td>
            <select name="filter_type">
                <?php foreach($arFilterType as $index => $type):?>
                    <option value="<?= $index?>" <?if($filter_type == $index):?>selected<?endif?>><?= $type?></option>
                <?php endforeach ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_PRICE_DELIVERY");?>:</td>
        <td>
            <?= Loc::getMessage("PRICE_DELIVERY_FROM");?>
            <input type="text" name="filter_price_delivery_from" value="<?=($filter_price_delivery_from != 0) ? htmlspecialcharsbx($filter_price_delivery_from) : '';?>" size="3">

            <?= Loc::getMessage("PRICE_DELIVERY_TO");?>
            <input type="text" name="filter_price_delivery_to" value="<?=($filter_price_delivery_to != 0) ? htmlspecialcharsbx($filter_price_delivery_to) : '';?>" size="3">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_DELIVERY_DOC_NUM");?>:</td>
        <td>
            <input type="text" name="filter_delivery_doc_num" value="<?=htmlspecialcharsbx($filter_delivery_doc_num);?>">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_ACCOUNT_NUM");?>:</td>
        <td>
            <input type="text" name="filter_account_num" value="<?=htmlspecialcharsbx($filter_account_num)?>">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("OSH_CARRIAGE_STATUS");?>:</td>
        <td>
            <select name="filter_osh_status">
                <?foreach($arOshStatuses as $statusName):?>
                    <option value="<?=$statusName?>" <?if($statusName == $filter_osh_status):?>selected<?endif?>><?=$statusName?></option>
                <?endforeach?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("SALE_SHIPMENT_F_USER_ID");?>:</td>
        <td>
            <?= FindUserID("filter_user_id",$filter_user_id,"","find_form");?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("SALE_SHIPMENT_F_USER_LOGIN");?>:</td>
        <td>
            <input type="text" name="filter_user_login" value="<?echo htmlspecialcharsEx($filter_user_login)?>" size="40">
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("SALE_SHIPMENT_F_USER_EMAIL");?>:</td>
        <td>
            <input type="text" name="filter_user_email" value="<?echo htmlspecialcharsEx($filter_user_email)?>" size="40">
        </td>
    </tr>
    <?
    $oFilter->Buttons(
        array(
            "table_id" => $tableId,
            "url" => $curPage,
            "form" => "find_form"
        )
    );

    $oFilter->End();
    ?>
</form>
<?
$lAdmin->DisplayList();
?>
<script type="text/javascript">
    function go2params(){
        window.open("/bitrix/admin/settings.php?lang=ru&mid=osh.shipping&mid_menu=1");
    }
    function go2doc(){
        window.open("https://osh.ru/help/integration/bitrix/bitrix-setting#article_27");
    }
    function go2agent(id){
        window.open("/bitrix/admin/agent_edit.php?ID=" + id + "&lang=ru");
    }
    function createAgent(){
        var wait = SHshowWait();
        BX.ajax.post(location.href,{mode:"createAgent"},function(d){
            var tableList = BX("osh_shipment_upload_result_div"),
                divAnswer = BX("create_agent_answer");
            if(!divAnswer){
                divAnswer = BX.create("div");
                divAnswer.id = "create_agent_answer";
                $(divAnswer).css({
                    fontWeight: 'bold',
                    margin: '10px'
                });
            }
            if(!!d){
                location.href = d;
                //divAnswer.innerHTML = d.toString();
            }else{
                divAnswer.innerHTML = "<?=Loc::getMessage("OSH_WRONG")?>";
            }
            if(!BX("create_agent_answer")){
                tableList.parentNode.insertBefore(divAnswer,tableList);
            }
            BX.closeWait(wait);
        });
    }
    function SHshowWait(){
        var overlayDiv = BX.showWait(document),
            waitDiv = BX.create("div");
        $(overlayDiv).css({
            position: 'fixed',
            background: 'rgba(0,0,0,0.2)',
            border: 'none',
            width: '100%',
            height: '100%',
            left: '0',
            top: '0'
        });
        $(waitDiv).css({
            background: '#e0e9ec',
            color: 'black',
            fontSize: '1.2em',
            margin: '30% auto',
            width: '20em',
            padding: '0.9em',
            height: '3em',
            boxSizing: 'border-box'
        });
        waitDiv.innerHTML = "<?=Loc::getMessage("OSH_WAIT")?>";
        overlayDiv.innerHTML = waitDiv.outerHTML;
        return overlayDiv;
    }
</script>
<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");