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

    public function showCreateAnswer($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll)
    {
        /* @var $order /Bitrix/Sale/Order */

        $checkoutUrls = Config::getCheckoutUrl();
        $arCheckoutUrl = explode(";", $checkoutUrls);
        $oRequest = Context::getCurrent()->getRequest();
        $currentUrl = $oRequest->getRequestedPageDirectory() . "/";
        $cAsset = Asset::getInstance();
        $ymapsApikey = Config::getYMapsKey();
        $daDataToken = Config::getDaDataToken();
        $deliveryCost = Config::getCost();
        $startCost = Config::getStartCost();
        $limitBasket = Config::getLimitBasket();
        $basketPrice = $order->getPrice();
        \CJSCore::Init(array("osh_pickup"));

        if (in_array($currentUrl, $arCheckoutUrl)) {
            if (Config::isIncludeYaMaps()) {
                $cAsset->addJs('https://api-maps.yandex.ru/2.1.71/?lang=ru_RU&apikey=' . ($ymapsApikey ? '&apikey=' . $ymapsApikey : ''), true);
            }

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

                $deliveryAddress = $_SESSION['Osh']['delivery_address_info']['address'] ?? '';
                $jsNoPvz = <<<JS
                        <script type="text/javascript">
                            BX.ready(function(){
                                window.Osh.oshOrderUpdate.init({ 
                                    deliveryOptions: {
                                        PERIOD_DELIVERY: [],
                                        DA_DATA_TOKEN: '$daDataToken',
                                        YA_API_KEY: '$ymapsApikey',
                                        DELIVERY_COST: $deliveryCost,
                                        START_COST: $startCost,
                                        LIMIT_BASKET: $limitBasket,
                                        CURRENT_BASKET: $basketPrice,
                                        DA_DATA_ADDRESS: '$deliveryAddress',
                                        OSH_COURIER_ID: '93',
                                        OSH_PICKUP_ID: '40',
                                    }, 
                                    order:{DELIVERY:[{CHECKED:"Y", ID: {$deliveryId}}]} 
                                });
                            });
                        </script>
                    JS;
                $cAsset->addString($jsNoPvz);
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
}
