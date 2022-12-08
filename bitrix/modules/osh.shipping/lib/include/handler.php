<?
include('/bitrix/modules/osh.shipping/lib/helpers/order.php');

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset,
    Bitrix\Main\Context,
    Bitrix\Main\Event,
    Bitrix\Main\Web\Json,
    Osh\Delivery\Options\Config,
    Osh\Delivery\OshHandler,
    Osh\Delivery\Services\DateDelivery,
    Osh\Delivery\Services\TimeDelivery,
    Osh\Delivery\Helpers\Order,
    Osh\Delivery\Logger;

Loc::loadMessages(__FILE__);

class COshDeliveryHandler
{
    const MODULE_ID = "osh.shipping";
    const PICK_PVZ_HTML = <<<PICK_PVZ
        <div id="shd_pvz_pick" data-json=' %s ' data-delivery="%s" data-force="%s">
            <button type="button" class="btn btn_red radius_10 font_12" onclick="window.Osh.bxPopup.onPickerClick(this);">Выбрать адрес на карте</button>
        </div>
       PICK_PVZ;
    const PVZ_PACEHOLDER = '<div id="shd_pvz_info"><small>%s %s</small></div>';

    public function addCustomDeliveryServices(/* \Bitrix\Main\Event $event */)
    {
        $libPath = sprintf("/bitrix/modules/%s/lib", self::MODULE_ID);
        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS, array(
                '\Osh\Delivery\OshHandler' => $libPath . "/OshHandler.php",
                '\Osh\Delivery\ProfileHandler' => $libPath . "/ProfileHandler.php"
            )
        );
        return $result;
    }

    public function addCustomRestrictions()
    {
        $libPath = sprintf("/bitrix/modules/%s/lib", self::MODULE_ID);
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\Osh\Delivery\Restrictions\ExcludeLocation' => $libPath . "/restrictions/ExcludeLocation.php",
            )
        );
    }

    public function addCustomExtraServices()
    {
        $libPath = sprintf("/bitrix/modules/%s/lib", self::MODULE_ID);
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\Osh\Delivery\Services\DateDelivery' => $libPath . "/services/DateDelivery.php",
                '\Osh\Delivery\Services\TimeDelivery' => $libPath . "/services/TimeDelivery.php",
            )
        );
    }
//    public function showAjaxAnswer(&$arResult){
//    }
//    public function getPropData(&$arUserResult, $request, &$arParams, &$arResult){
//        if(!empty($arUserResult['PAY_SYSTEM_ID'])){
//            $_SESSION['Osh']['paysystem'] = $arUserResult['PAY_SYSTEM_ID'];
//        }
//    }
    public function showCreateAnswer($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        $checkoutUrls = Config::getCheckoutUrl();
        $arCheckoutUrl = explode(";", $checkoutUrls);
        $oRequest = Context::getCurrent()->getRequest();
        $currentUrl = $oRequest->getRequestedPageDirectory() . "/";
        $cAsset = Asset::getInstance();
        $ymapsApikey = Config::getYMapsKey();

        if (in_array($currentUrl, $arCheckoutUrl)) {
            if (Config::isIncludeYaMaps()) {
                $cAsset->addJs('https://api-maps.yandex.ru/2.1.71/?lang=ru_RU&apikey=' . ($ymapsApikey ? '&apikey=' . $ymapsApikey : ''), true);
            }
            \CJSCore::Init(array("osh_pickup"));
//            $cAsset->addCss(OSH_DELIVERY_FA_URL);
        }
        $propertyCollection = $order->getPropertyCollection();
        $oLocationCode = $propertyCollection->getDeliveryLocation();
        if (empty($oLocationCode)) {
            return;
        }
        $sLocationCode = $oLocationCode->getValue();
        $arLocation = COshDeliveryHelper::getLocationByCode($sLocationCode);
        if (empty($arLocation)) {
            return;
        }
        if (empty($arLocation["CITY"])) {
            $arResult["WARNING"]["REGION"][] = Loc::getMessage("OSH_NO_CITY");
            $arResult["WARNING"]["DELIVERY"][] = Loc::getMessage("OSH_NO_CITY");
        }
        if (empty($arUserResult["PAY_SYSTEM_ID"]) && !empty($_SESSION['Osh']['paysystem'])) {
            $arUserResult["PAY_SYSTEM_ID"] = $_SESSION['Osh']['paysystem'];
        }

        foreach ($arDeliveryServiceAll as $key => $oDelivery) {
            $deliveryId = $oDelivery->getId();
            if (COshDeliveryHelper::isPVZProfile($oDelivery)) {
                $pvzInfo = $_SESSION['Osh']['delivery_address_info'];
                if ("windows-1251" == LANG_CHARSET) {
                    $pvzInfo["address"] = iconv('utf-8', LANG_CHARSET, $pvzInfo["address"]);
                }
                $arParentConfig = $oDelivery->getParentService()->getConfigOuter();
                switch ($arParentConfig['COD']["CALCULATION_TYPE"]) {
                    case OshHandler::COD_ALWAYS:
                    default:
                        $isCod = true;
                        break;
                    case OshHandler::COD_NEVER:
                        $isCod = false;
                        break;
                    case OshHandler::COD_CERTAIN:
                        $isCod = array_intersect(array($arUserResult["PAY_SYSTEM_ID"]), $arParentConfig['COD']['SERVICES_LIST']) ? true : false;
                        break;
                }
                $pvzCode = false;
                $pvzAddress = false;
                if (!empty($pvzInfo)) {
                    $pvzAddress = $pvzInfo["address"] . '<br/>';
                }
                $personTypeId = $order->getPersonTypeId();
                $orderBasket = $order->getBasket();
                $arOrder = array(
                    "ORDER" => array(
                        "ITEMS" => $oDelivery->getBasket($orderBasket),
                    ),
                    "CONFIG" => $arParentConfig
                );

                $json = Json::encode(array(
                    "address_prop_id" => Config::getAddressPropId($personTypeId),
                    "time_period_id" => Config::getPeriodPropId($personTypeId),
                    "pvz_address" => $pvzInfo["address"],
                    "key" => Config::getYMapsKey(),
                    "cost" => Config::getCost(),
                ));

                $pvzCode = "";
                if (empty($pvzAddress)) {
                    $pvzCode = '<span style="color:red;">' . Loc::getMessage("OSH_NO_PVZ") . '</span>';
                    if ($arUserResult["DELIVERY_ID"] == $deliveryId) {
                        $arResult['WARNING']['DELIVERY'][] = Loc::getMessage("OSH_NO_PVZ");
                    }
                    $buttonText = Loc::getMessage("OSH_PICK_PVZ");
                } else {
                    $buttonText = Loc::getMessage("OSH_CHANGE_PVZ");
                }
                $jsNoPvz = <<<JS
                        <script type="text/javascript">
                            BX.ready(function(){
                                window.Osh.checkPvz({order:{DELIVERY:[{CHECKED:"Y", ID: {$deliveryId}}]}});
                            });
                        </script>
                    JS;
                $htmlPickPVZ = sprintf(self::PICK_PVZ_HTML, $json, $deliveryId, 1, $buttonText);
                $cAsset->addString($jsNoPvz);
                $htmlPlaceholder = sprintf(self::PVZ_PACEHOLDER, $pvzCode, $pvzAddress);
                $oDelivery->addDescription($htmlPlaceholder . $htmlPickPVZ);
            }
            $dateDateliveryId = DateDelivery::getId($deliveryId);
            $timeExtraServiceId = TimeDelivery::getId($deliveryId);
            if ($oDelivery instanceof \Osh\Delivery\ProfileHandler
                && $oDelivery->getExtraServices()->getItem($dateDateliveryId)) {
                $dateDeliveryDefault = $oDelivery->getExtraServices()->getItem($dateDateliveryId)->getValue();
                $nextDayTime = strtotime("+1day");
                $sevenDaysTime = strtotime("+7day");
                if ($nextDayTime > strtotime($dateDeliveryDefault) || strtotime($dateDeliveryDefault) > $sevenDaysTime) {
                    if ($nextDayTime > strtotime($dateDeliveryDefault)) {
                        $date2Set = date("d.m.Y", $nextDayTime);
                    } else {
                        $date2Set = date("d.m.Y", $sevenDaysTime);
                    }
                    $oDelivery->getExtraServices()->getItem($dateDateliveryId)->setValue($date2Set);

                    $shipment = \Osh\Delivery\Helpers\Order::getShipment($order);
                    if (!!$shipment) {
                        $arExtraServices = $shipment->getExtraServices();
                        $arExtraServices[$dateDateliveryId] = $date2Set;
                        $shipment->setExtraServices($arExtraServices);
                    }
                }
                if ($oDelivery->getExtraServices()->getItem($timeExtraServiceId)) {
                    $arDaysOff = DateDelivery::getDaysOff();
                    if (in_array($dateDeliveryDefault, $arDaysOff)) {
                        $oDelivery->getExtraServices()->getItem($timeExtraServiceId)->setValue(0);
                        $oDelivery->getExtraServices()->getItem($timeExtraServiceId)->disable();
                        $oDelivery->getExtraServices()->getItem($timeExtraServiceId)->setTitle(Loc::getMessage("OSH_DATE_DELIVERY_TIME_RESTRICTION"));

                        $shipment = \Osh\Delivery\Helpers\Order::getShipment($order);
                        if (!!$shipment) {
                            $arExtraServices = $shipment->getExtraServices();
                            $arExtraServices[$timeExtraServiceId] = 0;
                            $shipment->setExtraServices($arExtraServices);
                        }
                    }
                }
            }
        }
    }

    public function saveInNewOrderMethodPVZ(Event $event)
    {
        $order = $event->getParameter("ENTITY");
        $showNoPvzError = Config::getDataValue("pvzStrict");
        $bShowNoPvzError = (bool)($showNoPvzError == "Y");
        if ($order->isNew()) {
            $order = $event->getParameter("ENTITY");  // order

            $shipment = Order::getShipment($order);
            $oDelivery = $shipment->getDelivery();

            $deliveryId = $oDelivery->getId();
            $personTypeId = $order->getPersonTypeId();
            $propPvzId = Config::getPvzPropId($personTypeId);
            $oPropPvz = \Osh\Delivery\Helpers\Order::getProperty($order, $propPvzId);

//            $pvzIsset = !empty($_SESSION["Osh"]['delivery_address_info']['address']);
            $oshDeliveryIds = [];


            $arDelivery = \Bitrix\Sale\Delivery\Services\Table::getList(array('filter' => array('ACTIVE' => 'Y')));

            while ($obDel = $arDelivery->fetch()) {
                if ($obDel['CLASS_NAME'] == '\Osh\Delivery\ProfileHandler') {
                    $oshDeliveryIds[] = $obDel['ID'];
                }
            }

//            if ($pvzIsset) {
//                if(!\Osh\Delivery\Helpers\Order::setPropertyValue($oPropPvz, $_SESSION["Osh"]['delivery_address_info']['address'])){
//                    Logger::force(Loc::getMessage('OSH_NO_PVZ_PROP'));
//                }
//            }else
            if ($bShowNoPvzError && !defined("ADMIN_SECTION") && in_array($deliveryId, $oshDeliveryIds)) {
                return new \Bitrix\Main\EventResult(
                    \Bitrix\Main\EventResult::ERROR,
                    new \Bitrix\Sale\ResultError("Oshisha ($deliveryId) " . $oDelivery->getName() . ": " . Loc::getMessage("OSH_NO_PVZ"), 'SALE_EVENT_NO_PVZ'),
                    'sale'
                );
            }

            // todo Возможно когда-то придется доработать дату и время доставки
//            if(Config::isDateTimeMirror() && (
//                COshDeliveryHelper::isOshCourier($oDelivery)
//                || COshDeliveryHelper::isOshCourierMkad($oDelivery)
//                || COshDeliveryHelper::isSberCourier($oDelivery))){
//                $dateExtraServiceId = DateDelivery::getId($oDelivery->getId());
//                $extraServices = $shipment->getExtraServices();
//                $sDateDelivery = $extraServices[$dateExtraServiceId]?:date('d.m.Y',strtotime('+1day'));
//                $arCommentText = array();
//                if(!empty($sDateDelivery)){
//                    $arCommentText[] = $sDateDelivery;
//                    $timeExtraServiceId = TimeDelivery::getId($oDelivery->getId());
//                    if(!empty($extraServices[$timeExtraServiceId]) && COshDeliveryHelper::isOshCourier($oDelivery)){
//                        $arDaysOff = DateDelivery::getDaysOff();
//                        if(!in_array($sDateDelivery,$arDaysOff)){
//                            $arTimeIntervals = COshDeliveryHelper::getDeliveryTime();
//                            $arCommentText[] = $arTimeIntervals[intval($extraServices[$timeExtraServiceId])];
//                        }
//                    }
//                }
//                if(!empty($arCommentText)){
//                    $comment = $order->getField('USER_DESCRIPTION');
//                    $comment .= "\n\r".Loc::getMessage('OSH_API_DATE_TIME_SHC').implode(", ",$arCommentText);
//                    $order->setField('USER_DESCRIPTION', $comment);
//                }
//            }
        }
    }

//    public function sendOrderToOsh(Event $event) {
//        $name = $event->getParameter('NAME');
//        $value = $event->getParameter('VALUE');
//        if($name != 'STATUS_ID') return true;
//        $shipment = $event->getParameter('ENTITY');
//
//        if (!$shipment ){
//            return true;
//        }
//        $arOshIds = \COshDeliveryHelper::getDeliveries();
//        $oDelivery = $shipment->getDelivery();
//        if(!$oDelivery){
//            return true;
//        }
//        $arOrderShipmentId = $shipment->getDeliveryId();
//        $isOsh = (bool)(in_array($arOrderShipmentId,$arOshIds));
//        if(!$isOsh){
//            return true;
//        }
//        if($oDelivery->isDirect()){
//            return true;
//        }
//
//        $order = $shipment->getCollection()->getOrder();
//        $orderId = $order->getId();
//        $paySystemId = null;
//        $arPaymentCollection = $order->getPaymentCollection();
//        foreach($arPaymentCollection as $payment){
//            if($payment->isInner()){
//                continue;
//            }
//            $paySystemId = $payment->getPaymentSystemId();
//        }
//        $isExists = !!$orderId;
//        $isStatusMatched = (bool)($value == Config::getTriggerStatus());
//        $isAutomatic = Config::isAutomaticUpload();
//        if(!$isExists || !$isStatusMatched || !$isAutomatic){
//            return true;
//        }
//        $isShipped = boolval($shipment->getField('TRACKING_NUMBER'));
//        $isAllowed = $shipment->isAllowDelivery();
//        if (!$isShipped && $isAllowed) {
//            try{
//                $result = COshDeliveryHelper::sendOrder($shipment);
//            } catch (\Exception $e) {
//                $shipment->setField("MARKED","Y");
//                $shipment->setField("REASON_MARKED",$e->getMessage());
//                $res = $shipment->save();
//                Logger::exception($e);
//                return new \Bitrix\Main\EventResult(
//                    \Bitrix\Main\EventResult::ERROR,
//                    new \Bitrix\Sale\ResultError($e->getMessage(), 'code'), 'sale');
//            }
//            return true;
//        }else{
//            if($isShipped){
//                return new \Bitrix\Main\EventResult(
//                    \Bitrix\Main\EventResult::ERROR,
//                    new \Bitrix\Sale\ResultError(Loc::getMessage("OSH_API_ERROR_ALREADY_SHIPPED"), 'code'), 'sale');
//            }
//            if(!$isAllowed){
//                return new \Bitrix\Main\EventResult(
//                    \Bitrix\Main\EventResult::ERROR,
//                    new \Bitrix\Sale\ResultError(Loc::getMessage("OSH_API_ERROR_SHIP_NOT_ALLOWED"), 'code'), 'sale');
//            }
//        }
//        return true;
//    }
//    function onDeliveryServiceCalculate(Event $event) {
//
//    }
//    function onEpilog(){
//
//    }
}
