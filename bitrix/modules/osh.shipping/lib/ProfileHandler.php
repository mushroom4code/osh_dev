<?php

namespace Osh\Delivery;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Application,
    Bitrix\Main\Page\Asset,
    Bitrix\Sale\Order,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Shipment,
    Bitrix\Sale\Delivery\Services\Manager,
    Bitrix\Sale\Delivery\Services\Table as DST,
    Osh\Delivery\Options\Config,
    Osh\Delivery\COshAPI,
    Osh\Delivery\OshHandler,
//    Osh\Delivery\Helpers\Calc,
    Osh\Delivery\Services\DateDelivery,
    Osh\Delivery\Services\TimeDelivery,
    Osh\Delivery\YApi,
    Osh\Delivery\Logger;

Loc::loadMessages(__FILE__);
Loader::includeModule('osh.shipping');
Loader::includeModule('sale');

class ProfileHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    protected static $isProfile = true;
    protected static $parent = null;
    protected static $whetherAdminExtraServicesShow = false;
    private $category = null;
    private $courier = null;
    private $group = null;
    private $profileName = null;
    private static bool $isOshPickup;
    const MODULE_ID = 'osh.shipping';
    const COUNTRY_RU = 'RU';
    const COUNTRY_KZ = 'KZ';
    const COUNTRY_BY = 'BY';

    public function __construct(array $initParams)
    {
        if($initParams['CONFIG'] === false) $initParams['CONFIG'] = array();
        parent::__construct($initParams);
        $this->parent = Manager::getObjectById($this->parentId);
        if($this->id <= 0 && strlen($initParams['PROFILE_ID']) > 0) {
            $arAvailableProfiles = $this->getParentService()->getAvailableProfiles();
            $arProfileParams = $arAvailableProfiles[$initParams['PROFILE_ID']];
            $this->category = $arProfileParams['category'];
            $this->courier = $arProfileParams['courier'];
            $this->group = $arProfileParams['group'];
            $this->name = $arProfileParams['name'];
            $this->profileName = $arProfileParams['name'];
            //$this->description = $arProfileParams['description'];
        } else {
            $this->category = $this->config['MAIN']['CATEGORY'];
            $this->courier = $this->config['MAIN']['COURIER'];
            $this->group = $this->config['MAIN']['GROUP'];
            $this->profileName = $this->config['MAIN']['NAME'];
        }

        $this->setDefaultLogo();
        if($this->isOshCourier() || $this->isOshCourierMkad() || $this->isSberCourier() || $this->isOshPickup()) {
            self::$whetherAdminExtraServicesShow = true;
        }
        if($this->isPvz()) {
            $this->mapJS();
//            $oRequest = Context::getCurrent()->getRequest();
//            if($oRequest->isAdminSection() && '/bitrix/admin/sale_order_edit.php' == $oRequest->getRequestedPage()) {
//                $this->addAdminJS();
//            }
        }
    }

    public function mapJS(){
        $cAsset = Asset::getInstance();

        $cAsset->addJs('/bitrix/js/osh.shipping/async.js', true);
//        $cAsset->addJs('/bitrix/js/osh.shipping/mkad.js', true);
    }

    public static function getClassTitle()
    {
        return Loc::getMessage('OSH_CLASS_TITLE');
    }

    public static function getClassDescription()
    {
        return Loc::getMessage('OSH_CLASS_DESC');
    }

    /**
     * Получаем список полей из админки из базового класса
     * @return array
     */
    public static function getAdminFieldsList(): array
    {
        $result = parent::getAdminFieldsList();
        // Если это "Самовывоз со склада", даем возможность выбрать склады
        if(self::$isOshPickup) {
            $result["STORES"] = true;
        }
        return $result;
    }

    public function getCourier()
    {
        return $this->courier;
    }

    private function setDefaultLogo()
    {
        $sDocumentRoot = Application::getDocumentRoot();
        $logoPath = \CFile::GetPath($this->logotip);
        if(!$this->logotip || !file_exists($sDocumentRoot . $logoPath) || empty($logoPath)) {
            $this->logotip = \COshDeliveryHelper::getDefaultLogo($this->courier);
        }
        return $this->logotip;
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {
        $oCalculationResult = new \Bitrix\Sale\Delivery\CalculationResult();
        try {
            $cost = Options\Config::getCost();
            $startCost = Options\Config::getStartCost();
            $distance = ceil(($_SESSION['Osh']['delivery_address_info']['distance'] ?? 0) - 0.8);

            $limitBasket = Options\Config::getLimitBasket();
            if ($shipment->getOrder()->getPrice() >= $limitBasket) {
                $delivery_price = max($distance - 5, 0) * $cost;
            } else {
                $delivery_price = $startCost + $distance * $cost;
            }
            $oCalculationResult->setDeliveryPrice($delivery_price);

        } catch(\Exception $e) {
            $oCalculationResult->addError(new \Bitrix\Main\Error($e->getMessage()));
//            Logger::exception($e);
        }
        return $oCalculationResult;
    }

    public function isCompatible(Shipment $shipment)
    {
        if(!Config::isModuleActive() || empty(Config::getYMapsKey())) return false;

        $calcResult = $this->calculateConcrete($shipment);
        return $calcResult->isSuccess();
    }

    public function convertCurrency($price)
    {
        $currency = $this->getParentService()->getCurrency() !== 'RUB' ? $this->getParentService()->getCurrency() : $this->getCurrency();
        if($currency !== 'RUB') {
            Loader::includeModule("currency");
            $arFilter = array("CURRENCY" => $currency);
            $by = "date";
            $order = "desc";
            $arRate = \CCurrencyRates::GetList($by, $order, $arFilter)->Fetch();
            if(!empty($arRate)) {
                return $price / floatval($arRate["RATE"]);
            }
            $arRate = \CCurrency::GetByID($currency);
            if(!empty($arRate['AMOUNT'])) {
                return $price / floatval($arRate["AMOUNT"]);
            }
        }
        return $price;
    }

    public function setSort($sort)
    {
        if($sort > 0) {
            $this->sort = $sort;
        }
    }

//    public function getOrderData(Shipment $shipment)
//    {
//
//        $order = $shipment->getCollection()->getOrder();
//        $propertyCollection = $order->getPropertyCollection();
//        $DeliveryLocation = $propertyCollection->getDeliveryLocation();
//        if(empty($DeliveryLocation)) {
//            throw new \Exception(Loc::getMessage('OSH_NO_LOCATION_PROP'));
//        }
//
//        $locationCode = $DeliveryLocation->getValue();
//        if(empty($locationCode)) {
//            throw new \Exception(Loc::getMessage('OSH_EMPTY_LOCATION'));
//        }
//        $arLocation = $this->getLocation($locationCode);
//        if(!$arLocation || empty($arLocation['CITY'])) {
//            throw new \Exception(Loc::getMessage('OSH_EMPTY_LOCATION'));
//        }
//
//
//        $config = $this->getParentService()->getConfig();
//
//        if(!empty($config['MAIN']['ITEMS']['API_KEY_YANDEX']['VALUE'])) {
//            $api_key = $config['MAIN']['ITEMS']['API_KEY_YANDEX']['VALUE'];
//        } else {
//            throw new \Exception(Loc::getMessage('OSH_EMPTY_API_KEY_YANDEX'));
//        }
//
//        $ya = new \Osh\Delivery\YApi();
//        return $ya->CheckMkad($arLocation['CITY'], $api_key);
//
////
////
////
////
////        $arOrder['CONFIG'] = $this->getParentService()->getConfigOuter();
////        $arOrder['LOCAL_CONFIG'] = $this->config;
////        $propertyFio = $propertyCollection->getProfileName();
////        if(empty($propertyFio)){
////            $propertyFio = $propertyCollection->getPayerName();
////            if(empty($propertyFio)){
////                throw new \Exception(Loc::getMessage('OSH_NO_FIO_PROP'));
////            }
////        }
////        $arOrder['FIO'] = trim($propertyFio->getValue());
////        $propertyEmail = $propertyCollection->getUserEmail();
////        if(empty($propertyEmail)){
////            throw new \Exception(Loc::getMessage('OSH_NO_EMAIL_PROP'));
////        }
////        $arOrder['EMAIL'] = $propertyEmail->getValue();
////        $arOrder['DESC'] = $order->getField('USER_DESCRIPTION');
////        $propertyPhone = $propertyCollection->getPhone();
////        if(empty($propertyPhone)){
////            throw new \Exception(Loc::getMessage('OSH_NO_PHONE_PROP'));
////        }
////        $arOrder['PHONE'] = $propertyPhone->getValue();
////        $propertyZip = $propertyCollection->getDeliveryLocationZip();
////        if(!empty($propertyZip)){
////            $arLocation['ZIP'] = $propertyZip->getValue();
////        }
////        $orderBasket = $order->getBasket();
////        if(empty($orderBasket)){
////            throw new \Exception(Loc::getMessage('OSH_EMPTY_BASKET'));
////        }
////        $arOrder['ORDER']['ITEMS'] = $this->getBasket($orderBasket);
////        $arOrder['ORDER']['DIMENSIONS'] = Helpers\Calc::orderDimensions($arOrder);
////        $arOrder['ORDER']['WEIGHT'] = Helpers\Calc::orderWeight($arOrder);
////        $arOrder['PRICE'] = roundEx($orderBasket->getPrice(),SALE_VALUE_PRECISION);
////        $arOrder['ORDER']['ID'] = intval($order->getId());
////        if($arOrder['ORDER']['ID'] > 0){
////            $arOrder['ORDER']['PAYED'] = $order->isPaid();
////            $arOrder['ORDER']['PRICE'] = $order->getPrice();
////        }else{
////            $arOrder['ORDER']['PAYED'] = false;
////        }
////        $arOrder['ADDRESS'] = $arLocation;
////        if(Config::isAddressSimple()){
////            $addressPropId = Config::getAddressPropId($personTypeId);
////            $propAddress = Helpers\Order::getProperty($order, $addressPropId);
////            if(empty($propAddress)){
////                $propAddress = $propertyCollection->getAddress();
////            }
////            if(empty($propAddress)){
////                if(!$this->isPvz()){
////                    $arOrder['ADDRESS']['STREET'] = '';
////                }
////                //throw new \Exception(Loc::getMessage('OSH_EMPTY_ADDRESS'));
////            }else{
////                $arOrder['ADDRESS']['STREET'] = $propAddress->getValue();
////            }
////        }else{
////            $streetPropId = Config::getStreetPropId($personTypeId);
////            $streetProp = Helpers\Order::getProperty($order, $streetPropId);
////            if(empty($streetProp)){
////                if(!$this->isPvz()){
////                    $arOrder['ADDRESS']['STREET'] = '';
////                }
////                //throw new \Exception(Loc::getMessage('OSH_EMPTY_STREET'));
////            }else{
////                $arOrder['ADDRESS']['STREET'] = $streetProp->getValue();
////            }
////            $bldPropId = Config::getBldPropId($personTypeId);
////            $bldProp = $propertyCollection->getItemByOrderPropertyId($bldPropId);
////            if(empty($bldProp)){
////                if(!$this->isPvz()){
////                    $arOrder['ADDRESS']['BLD'] = '';
////                }
////                //throw new \Exception(Loc::getMessage('OSH_EMPTY_BLD'));
////            }else{
////                $arOrder['ADDRESS']['BLD'] = $bldProp->getValue();
////            }
////            $corpPropId = Config::getCorpPropId($personTypeId);
////            $corpProp = $propertyCollection->getItemByOrderPropertyId($corpPropId);
////            if(!empty($corpProp) && !$this->isPvz()){
////                $arOrder['ADDRESS']['CORP'] = $corpProp->getValue();
////            }
////            $flatPropId = Config::getFlatPropId($personTypeId);
////            $flatProp = $propertyCollection->getItemByOrderPropertyId($flatPropId);
////            if(empty($flatProp)){
////                if(!$this->isPvz()){
////                    $arOrder['ADDRESS']['FLAT'] = '';
////                }
////                //throw new \Exception(Loc::getMessage('OSH_EMPTY_FLAT'));
////            }else{
////                $arOrder['ADDRESS']['FLAT'] = $flatProp->getValue();
////            }
////        }
////        $paymentIds = $order->getPaymentSystemId();
////        if(empty($paymentIds) && !empty($_SESSION['Osh']['paysystem'])){
////            $paymentIds = array($_SESSION['Osh']['paysystem']);
////        }
////        if(!$order->isPaid()){
////            if($arOrder["ADDRESS"]["COUNTRY_CODE"] == self::COUNTRY_BY || $arOrder["ADDRESS"]["COUNTRY_CODE"] == self::COUNTRY_KZ){
////                $arOrder['ORDER']['COD'] = false;
////            }else{
////                switch($arOrder['CONFIG']['COD']['CALCULATION_TYPE']){
////                    case OshHandler::COD_ALWAYS:default:
////                        $arOrder['ORDER']['COD'] = true;
////                        break;
////                    case OshHandler::COD_NEVER:
////                        $arOrder['ORDER']['COD'] = false;
////                        break;
////                    case OshHandler::COD_CERTAIN:
////                        if(empty($paymentIds) || $paymentIds[0] == 0){
////                            $arOrder['ORDER']['COD'] = false;
////                        }else{
////                            $codPaymentIds = $arOrder['CONFIG']['COD']['SERVICES_LIST'];
////                            $arOrder['ORDER']['COD'] = array_intersect($paymentIds,$codPaymentIds)?true:false;
////                        }
////                        break;
////                }
////            }
////        }else{
////            $arOrder['ORDER']['COD'] = false;
////        }
////        if(empty($paymentIds) || $paymentIds[0] == 0){
////            $arOrder['ORDER']['PAYMENT'] = 'cash';
////        }else{
////            $cashPaymentId = Config::getCashPayments();
////            $arOrder['ORDER']['PAYMENT'] = array_intersect($cashPaymentId,$paymentIds)?'cash':'card';
////        }
////        $arOrder['METHOD_ID'] = $this->getMethodId();
////        if($this->isPvz()){
////            if($arOrder['ORDER']['ID'] > 0){
////                $order = $shipment->getCollection()->getOrder();
////                $propertyCollection = $order->getPropertyCollection();
////                $propPvzId = Config::getPvzPropId($personTypeId);
////                $propPvz = $propertyCollection->getItemByOrderPropertyId($propPvzId);
////                if(empty($propPvz)){
////                    throw new \Exception(Loc::getMessage('OSH_NO_PVZ_PROP'));
////                }
////                $arOrder['PVZ_CODE'] = $propPvz->getValue();
////            }
////            if($arOrder['ORDER']['ID'] > 0 || Config::isCheckPvz()){
////                $arPvz = $this->checkAvailablePvz($arOrder);
////                if(empty($arPvz)){
////                    throw new \Exception($this->name.' - '.Loc::getMessage('OSH_NO_PVZ_AVAILABLE'));
////                }
////                if(count($arPvz) == 1 && empty($arOrder['PVZ_CODE']) && empty($_SESSION['Osh'][$this->id]['PVZ_ID'])){
////                    $arOrder['PVZ_CODE'] = $arPvz[0]['id'];
////                    $_SESSION['Osh'][$this->id]['PVZ_ID'] = $arPvz[0]['id'];
////                    $_SESSION['Osh'][$this->id]['PVZ_INFO'] = $arPvz[0];
////                }
////                if(!empty($_SESSION['Osh'][$this->id]['PVZ_ID']) && $arOrder['ORDER']['COD']){
////                    $haveNoCard = $arOrder['ORDER']['PAYMENT'] == 'card' && $_SESSION['Osh'][$this->id]['PVZ_INFO']['card'] === 'false';
////                    $haveNoCod = $arOrder['ORDER']['PAYMENT'] == 'cash' && $_SESSION['Osh'][$this->id]['PVZ_INFO']['cod'] === 'false';
////                    if($haveNoCard || $haveNoCod){
////                        unset($_SESSION['Osh'][$this->id]['PVZ_ID'], $_SESSION['Osh'][$this->id]['PVZ_INFO']);
////                    }
////                }
////            }
////        }
////        if($arOrder['ORDER']['ID'] > 0){
////            if($arOrder['ORDER']['COD'] && $arOrder['ADDRESS']['COUNTRY_CODE'] != self::COUNTRY_RU){
////                throw new \Exception($this->name.' - '.Loc::getMessage('OSH_NO_COD'));
////            }
////            if($this->isOshCourier() || $this->isOshCourierMkad() || $this->isSberCourier()){
////                $dateExtraServiceId = DateDelivery::getId($this->id);
////                $extraServices = $shipment->getExtraServices();
////                $arOrder['ORDER']['DATE_DELIVERY'] = $extraServices[$dateExtraServiceId]?:date('d.m.Y',strtotime('+1day'));
////                if($this->isOshCourier()){
////                    $timeExtraServiceId = TimeDelivery::getId($this->id);
////                    if(!empty($arOrder['ORDER']['DATE_DELIVERY']) && !empty($extraServices[$timeExtraServiceId])){
////                        $arDaysOff = DateDelivery::getDaysOff();
////                        if(!in_array($arOrder['ORDER']['DATE_DELIVERY'],$arDaysOff)){
////                            $arOrder['ORDER']['TIME_DELIVERY'] = intval($extraServices[$timeExtraServiceId]);
////                        }
////                    }
////                }
////            }
////        }else{
////            $nowMoscow12 = new \DateTime('now 12:00', new \DateTimeZone('Europe/Moscow'));
////            $nowMoscow21 = new \DateTime('now 20:50', new \DateTimeZone('Europe/Moscow'));
////            $nowLocal = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
////            if($this->isOshToday() &&
////                    ($nowLocal->getTimestamp() > $nowMoscow12->getTimestamp()) &&
////                    ($nowLocal->getTimestamp() < $nowMoscow21->getTimestamp())){
////                throw new \Exception(Loc::getMessage('OSH_ONE_DAY_NOT_AVAILABLE'));
////            }
////        }
////        $arOrder['IS_DIRECT'] = $this->isDirect();
////        if($arOrder['IS_DIRECT']){
////            $arOrder['SENDER'] = $this->getSenderData();
////            if($arOrder['ORDER']['ID'] > 0 || Config::isCheckPvz()){
////                if(in_array($this->category,array('delivery-point-to-door','delivery-point-to-delivery-point'))){
////                    $arParamsPvz = array(
////                        'kladr_id' => $arOrder['SENDER']['KLADR'],
////                        'shipping_method' => $arOrder['METHOD_ID'],
////                        'courier' => $arOrder['COURIER'],
////                        'self_pick_up' => true,
////                        'limits' => array(
////                            'length' => floatval($arOrder['ORDER']['DIMENSIONS']['LENGTH']),
////                            'width' => floatval($arOrder['ORDER']['DIMENSIONS']['WIDTH']),
////                            'height' => floatval($arOrder['ORDER']['DIMENSIONS']['HEIGHT']),
////                            'weight' => $arOrder['ORDER']['WEIGHT']
////                        )
////                    );
////                    $arAvailablePvz = \COshDeliveryHelper::getPvz($arParamsPvz);
////                    if(empty($arAvailablePvz)){
////                        throw new \Exception($this->name.' - '.Loc::getMessage('OSH_NO_PVZ_AVAILABLE_SENDER'));
////                    }
////                    $bPvzFound = false;
////                    foreach($arAvailablePvz as $pvz){
////                        if($pvz['id'] == $arOrder['SENDER']['PVZ_CODE']){
////                            $bPvzFound = true;
////                        }
////                    }
////                    if(!$bPvzFound){
////                        throw new \Exception($this->name.' - '.Loc::getMessage('OSH_NO_PVZ_AVAILABLE_SENDER'));
////                    }
////                }
////            }
////        }
////        if(!$this->isExport() && !$this->isDirect()){
////            $stock = !empty($arOrder['LOCAL_CONFIG']['STOCK']['STOCK'])? $arOrder['LOCAL_CONFIG']['STOCK']['STOCK'] : Config::getDefaultStock();
////            if(!empty($stock)){
////                $arOrder['STOCK'] = \COshDeliveryHelper::getStockById($stock);
////                if(in_array('logistic',$arOrder['STOCK']['roles']) && in_array('fulfilment',$arOrder['STOCK']['roles'])){
////                    if(($arOrder['LOCAL_CONFIG']['STOCK']['IS_FULFILMENT'] == 'Y' || $arOrder['LOCAL_CONFIG']['MAIN']['IS_FULFILMENT'] == 'Y')){
////                        $arOrder['IS_FULFILMENT'] = true;
////                    }else{
////                        $arOrder['IS_FULFILMENT'] = false;
////                    }
////                }else{
////                    if(in_array('logistic',$arOrder['STOCK']['roles'])){
////                        $arOrder['IS_FULFILMENT'] = false;
////                    }
////                    if(in_array('fulfilment',$arOrder['STOCK']['roles'])){
////                        $arOrder['IS_FULFILMENT'] = true;
////                    }
////                }
////            }else{
////                if($arOrder['LOCAL_CONFIG']['MAIN']['IS_FULFILMENT'] == 'Y' || $arOrder['LOCAL_CONFIG']['STOCK']['IS_FULFILMENT'] == 'Y'){
////                    $arOrder['IS_FULFILMENT'] = true;
////                }else{
////                    $arOrder['IS_FULFILMENT'] = false;
////                }
////            }
////        }
////        return $arOrder;
//    }

    private function getSenderData()
    {
        $senderLocation = $this->config['DIRECT']['LOCATION'];
        if(empty($senderLocation)) {
            throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_LOCATION'));
        }
        $arOrder['SENDER'] = \COshDeliveryHelper::getLocationByCode($senderLocation);
        if(empty($arOrder['SENDER'])) {
            throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_LOCATION'));
        }
        if($this->config['DIRECT']['RECIEVER_TYPE'] == 'SET') {
            $arOrder['SENDER']['FIO'] = Config::getDataValue('direct_reciever');
            $arOrder['SENDER']['PHONE'] = Config::getDataValue('direct_phone');
            $arOrder['SENDER']['EMAIL'] = Config::getDataValue('direct_email');
        } else {
            $arOrder['SENDER']['FIO'] = $this->config['DIRECT']['RECIEVER'];
            $arOrder['SENDER']['PHONE'] = $this->config['DIRECT']['PHONE'];
            $arOrder['SENDER']['EMAIL'] = $this->config['DIRECT']['EMAIL'];
        }
        if(empty($arOrder['SENDER']['FIO'])) {
            throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_FIO'));
        }
        if(empty($arOrder['SENDER']['PHONE'])) {
            throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_PHONE'));
        }
        if(empty($arOrder['SENDER']['EMAIL'])) {
            throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_EMAIL'));
        }
        if($this->config['DIRECT']['DATE_TIME_TYPE'] == 'SET') {
            $arOrder['SENDER']['DATE_TYPE'] = Config::getDataValue('direct_date_type');
            $arOrder['SENDER']['DELAY'] = Config::getDataValue('direct_date_delay');
        } else {
            $arOrder['SENDER']['DATE_TYPE'] = $this->config['DIRECT']['DATE_TYPE'];
            $arOrder['SENDER']['DELAY'] = $this->config['DIRECT']['DELAY'];
        }
        $dateFrom = date('Y-m-d', strtotime('+1 day'));
        switch($arOrder['SENDER']['DATE_TYPE']) {
            case Config::DATE_NEAR:
            default:
                $arOrder['SENDER']['DATE'] = $dateFrom;
                break;
            case Config::DATE_DELAY:
                $delay = $arOrder['SENDER']['DELAY'] + 1;
                $arOrder['SENDER']['DATE'] = date('Y-m-d', strtotime("+{$delay} day"));
                break;
        }
        $dateTo = date('Y-m-d', strtotime($arOrder['SENDER']['DATE'] . ' +7 day'));
        $daysOff = \COshDeliveryHelper::getDaysOff($dateFrom, $dateTo);
        foreach($daysOff as $day) {
            if(strtotime($arOrder['SENDER']['DATE']) >= strtotime($day)) {
                $arOrder['SENDER']['DATE'] = date('Y-m-d', strtotime($arOrder['SENDER']['DATE'] . " +1 day"));
            }
        }
        if(in_array($this->category, array('door-to-door', 'door-to-delivery-point'))) {
            if($this->config['DIRECT']['ADDRESS_TYPE'] == 'SET') {
                $arOrder['SENDER']['ZIP'] = Config::getDataValue('direct_zip');
                $arOrder['SENDER']['STREET'] = Config::getDataValue('direct_street');
                $arOrder['SENDER']['HOUSE'] = Config::getDataValue('direct_house');
                $arOrder['SENDER']['FLAT'] = Config::getDataValue('direct_flat');
            } else {
                $arOrder['SENDER']['ZIP'] = $this->config['DIRECT']['ZIP'];
                $arOrder['SENDER']['STREET'] = $this->config['DIRECT']['STREET'];
                $arOrder['SENDER']['HOUSE'] = $this->config['DIRECT']['HOUSE'];
                $arOrder['SENDER']['FLAT'] = $this->config['DIRECT']['FLAT'];
            }
            if(empty($arOrder['SENDER']['ZIP'])) {
                throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_ZIP'));
            }
            if(empty($arOrder['SENDER']['STREET'])) {
                throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_STREET'));
            }
            if(empty($arOrder['SENDER']['HOUSE'])) {
                throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_HOUSE'));
            }
        } elseif(in_array($this->category, array('delivery-point-to-door', 'delivery-point-to-delivery-point'))) {
            $arOrder['SENDER']['PVZ_CODE'] = $this->config['DIRECT']['PVZ_CODE'];
            $arOrder['SENDER']['PVZ_ADDRESS'] = $this->config['DIRECT']['PVZ_ADDRESS'];
            if(empty($arOrder['SENDER']['PVZ_CODE'])) {
                throw new \Exception($this->name . ' - ' . Loc::getMessage('OSH_NO_SENDER_PVZ_CODE'));
            }
        }
        $arOrder['SENDER']['COMMENT'] = $this->config['DIRECT']['COMMENT'];
        return $arOrder['SENDER'];
    }

    protected function checkAvailablePvz($arOrder)
    {
        $arParams = array(
            'kladr_id' => $arOrder['ADDRESS']['KLADR'],
            'shipping_method' => intval($arOrder['METHOD_ID']),
            'cod' => $arOrder['ORDER']['COD'],
            'card' => (bool)($arOrder['ORDER']['PAYMENT'] == 'card'),
            'limits' => array(
                'weight' => floatval($arOrder['ORDER']['WEIGHT']),
                'length' => intval($arOrder['ORDER']['DIMENSIONS']['LENGTH']),
                'width' => intval($arOrder['ORDER']['DIMENSIONS']['WIDTH']),
                'height' => intval($arOrder['ORDER']['DIMENSIONS']['HEIGHT']),
            )
        );
        if(!$arOrder['ORDER']['COD']) {
            unset($arParams['cod'], $arParams['card']);
        }
        $arPvz = \COshDeliveryHelper::getPvz($arParams);
        return $arPvz;
    }

    public function getBasket(Basket $basket)
    {
        \Bitrix\Main\Loader::includeModule('catalog');
        $arBasketItems = $basket->getBasketItems();
        $arItems = array();

        $mainUrl = \COption::GetOptionString("main", "server_name");
        foreach($arBasketItems as $oBasketItem) {
            $name = $oBasketItem->getField('NAME');
            $dimensions = $oBasketItem->getField('DIMENSIONS') ? unserialize($oBasketItem->getField('DIMENSIONS')) : false;
            $quantity = $oBasketItem->getQuantity();
//            $weight = Calc::weightToKg($oBasketItem->getWeight());
            if(\CCatalogSku::GetProductInfo($oBasketItem->getProductId())) {
                $article = $this->getBasketItemArticle($oBasketItem, true);
            } else {
                $article = $this->getBasketItemArticle($oBasketItem, false);
            }
            $arItems[] = array(
                'NAME' => $name,
                'QUANTITY' => $quantity,
                'WEIGHT' => 100,
                'PRICE' => $oBasketItem->getPrice(),
                'WIDTH' => $dimensions['WIDTH'],
                'HEIGHT' => $dimensions['HEIGHT'],
                'LENGTH' => $dimensions['LENGTH'],
                'URL' => $mainUrl . $oBasketItem->getField('DETAIL_PAGE_URL'),
                'ID' => $article,
                'VAT' => intval($oBasketItem->getVatRate() * 100)
            );
        }
        return $arItems;
    }

    private function getBasketItemArticle($oBasketItem, $isOffer = false)
    {
        if($isOffer) {
            $articleOption = Config::getDataValue('productOfferArticle');
            $compleXMLIDoption = (bool)Config::getDataValue('offerXmlIdComplex');
            $articleProp = Config::getDataValue('offerArticleProperty');
        } else {
            $articleOption = Config::getDataValue('productArticle');
            $compleXMLIDoption = (bool)Config::getDataValue('xmlIdComplex');
            $articleProp = Config::getDataValue('articleProperty');
        }
        switch($articleOption) {
            case 'PROP':
                $arProperty = \COshDeliveryHelper::getIblockPropertyData($articleProp);
                if(!empty($arProperty)) {
                    $sPropCode = 'PROPERTY_' . $arProperty['CODE'];
                    $arFilter = array("!$sPropCode" => false, 'ACTIVE' => 'Y',
                        'ID' => $oBasketItem->getProductId());
                    $arSelect = array('ID', 'NAME', 'IBLOCK_ID', $sPropCode);
                    $arWares = \CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, $arSelect)->Fetch();
                    if(!empty($arWares[$sPropCode . '_VALUE'])) {
                        $article = $arWares[$sPropCode . '_VALUE'];
                        break;
                    }
                }
            case 'ID':
            default:
                $article = $oBasketItem->getProductId();
                break;
            case 'XML_ID':
                $article = $oBasketItem->getField('PRODUCT_XML_ID');
                if($compleXMLIDoption && strpos($article, '#') !== false) {
                    $arArticle = explode('#', $article);
                    $article = $arArticle[1];
                }
                break;
        }
        return $article;
    }

    public function isPvz()
    {
        return in_array(
            $this->category,
            array('delivery-point', 'door-to-delivery-point', 'delivery-point-to-delivery-point'));
    }

    public function isDirect()
    {
        return true;
    }

    public function isOshCourier()
    {
        return boolval($this->group == 'osh_courier');
    }

    /**
     * Проверяем является ли группа "Самовывоз со склада"
     * Если является, возвращаем true и сложим результат в @var $isOshPickup
     * Так как @method getAdminFieldsList
     * является статичным
     * @return bool
     */
    private function isOshPickup(): bool
    {
        return self::$isOshPickup = $this->group == 'osh_pvz_pickup';
    }


    public function isOshCourierMkad(): bool
    {
        return boolval($this->group == 'osh_area_courier');
    }

    public function isSberCourier()
    {
        return boolval($this->group == 'sber_courier');
    }

    public function isOshToday()
    {
        return boolval($this->group == 'osh_one_day');
    }

    public function isRussianPost()
    {
        return boolval($this->group == 'russian_post');
    }

    public function isExport()
    {
        return boolval($this->courier == 'osh-international');
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getMethodId()
    {
        $arMethods = $this->getParentService()->getAvailableProfiles();
        return $arMethods[$this->getGroup()]['id'];
    }

    public function getLogo()
    {
        return $this->logotip;
    }

    public function isCalculatePriceImmediately()
    {
        return $this->getParentService()->isCalculatePriceImmediately();
    }

    public static function isProfile()
    {
        return self::$isProfile;
    }

    public function getParentService()
    {
        return $this->parent;
    }

    public function getLocation($locationCode)
    {
        return \COshDeliveryHelper::getLocationByCode($locationCode);
    }

    private function calculateMargin($price)
    {
        return $this->getParentService()->calculateMargin($price);
    }

    protected function getConfigStructure()
    {
        if(empty($this->profileName)) {
            $arAvailableProfiles = $this->getParentService()->getAvailableProfiles();
            $this->profileName = $arAvailableProfiles[$this->group]['name'];
        }
        return array(
            'MAIN' => array(
                'TITLE' => Loc::getMessage('OSH_CONFIG_MAIN'),
                'DESCRIPTION' => Loc::getMessage('OSH_CONFIG_MAIN'),
                'ITEMS' => array(
                    'NAME' => array(
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage('OSH_PROFILE_CONFIG_NAME'),
                        'READONLY' => true,
                        'DEFAULT' => $this->profileName,
                        'VALUE' => $this->profileName
                    ),
                    'CATEGORY' => array(
                        'TYPE' => 'STRING',
                        'NAME' => 'category',
                        'HIDDEN' => true,
                        'DEFAULT' => $this->category
                    ),
                    'COURIER' => array(
                        'TYPE' => 'STRING',
                        'NAME' => 'courier',
                        'HIDDEN' => true,
                        'DEFAULT' => $this->courier
                    ),
                    'GROUP' => array(
                        'TYPE' => 'STRING',
                        'NAME' => 'group',
                        'READONLY' => true,
                        'DEFAULT' => $this->group
                    )
                )
            )
        );
    }

    public function addAdminJS()
    {
        $cAsset = Asset::getInstance();
        $oRequest = Context::getCurrent()->getRequest();
        if(empty($oRequest['ID'])) {
            return true;
        }
        $order = Order::load($oRequest['ID']);
        $propertyCollection = $order->getPropertyCollection();
        $locationCode = $propertyCollection->getDeliveryLocation()->getValue();
        $locationPropId = $propertyCollection->getDeliveryLocation()->getPropertyId();
        $arLocation = $this->getLocation($locationCode);
        if(!$arLocation) {
            return true;
        }
        $deliveryId = $this->getId();

        if($this->id != $deliveryId || !empty($_POST['init' . $oRequest['ID']])) {
            return true;
        }

        $_POST['init' . $oRequest['ID']] = $deliveryId;
        $personTypeId = $order->getPersonTypeId();
        $arConfig = $this->getParentService()->getConfigOuter();

        $propPvzId = Config::getPvzPropId($personTypeId);
        $oPropPvz = \Osh\Delivery\Helpers\Order::getProperty($order, $propPvzId);

        if(!empty($oPropPvz)) {
            $currentPvz = $oPropPvz->getValue();
        }
        $orderBasket = $order->getBasket();
        $arOrder = array(
            'ORDER' => array(
                'ITEMS' => $this->getBasket($orderBasket),
            ),
            'CONFIG' => $arConfig
        );
        $orderDimensions = Calc::orderDimensions($arOrder);
        $weight = Calc::orderWeight($arOrder);
        $paymentIds = $order->getPaymentSystemId();
        $cashPaymentId = Config::getCashPayments();
        $paymentType = array_intersect($cashPaymentId, $paymentIds) ? 'cash' : 'card';
        $bPayed = $order->isPaid();
        switch($arConfig['COD']['CALCULATION_TYPE']) {
            case OshHandler::COD_ALWAYS:
            default:
                $bCod = true;
                break;
            case OshHandler::COD_NEVER:
                $bCod = false;
                break;
            case OshHandler::COD_CERTAIN:
                if($bPayed) {
                    $bCod = false;
                } else {
                    if($arConfig['COD']['SERVICE_COD'] == 'N') {
                        $bCod = false;
                    } else {
                        $codPaymentIds = $arConfig['COD']['SERVICES_LIST'];
                        $bCod = array_intersect($paymentIds, $codPaymentIds) ? true : false;
                    }
                }
                break;
        }
        $ymapsApikey = Config::getDataValueNew('API_KEY_YANDEX');
//        $cAsset->addJs(OSH_DELIVERY_YMAPS_URL . ($ymapsApikey ? '&apikey=' . $ymapsApikey : ''), true);


//        $cAsset->addJs('https://api-maps.yandex.ru/2.0-stable/?load=package.standard,package.geoObjects,package.geoQuery,package.route&lang=ru-RU&apikey=217ce27c-178e-4150-bcca-e6cddfddec77', true);
        $cAsset->addJs('https://yandex.st/jquery/1.9.0/jquery.js', true);

        \CJSCore::Init(array('osh_pickup'));
//        $arJSParams = array(
//            'ID' => $this->id,
//            'CONFIG' => $this->config,
//            'METHOD' => $this->getMethodId(),
//            'CITY' => $arLocation['CITY'],
//            'CURRENT_PVZ' => $currentPvz,
//            'PVZ_PROP_ID' => $propPvzId,
//            'ADDR_PROP_ID' => (Config::isAddressSimple() ? Config::getAddressPropId($personTypeId) : ''),
//            'LOCATION_PROP_ID' => $locationPropId,
//            'LOCATION_CODE' => $locationCode,
//            'KLADR' => $arLocation['KLADR'],
//            'PAYMENT_TYPE' => $paymentType,
//            'BPAYED' => $bPayed,
//            'BCOD' => $bCod,
//            'SEARCH' => Config::getDataValueNew('API_KEY_YANDEX') ? true : false,
//            'LIMITS' => array('weight' => $weight, 'length' => $orderDimensions['LENGTH'],
//                'width' => $orderDimensions['WIDTH'], 'heigth' => $orderDimensions['HEIGHT']
//            )
//        );
//        $jsonJSParams = \CUtil::PhpToJSObject($arJSParams);
//        $jsParams = <<<OSH_JS
//        <script type="text/javascript">
//
//
//        </script>
//OSH_JS;
//        $cAsset->addString($jsParams);
    }

    public function getTerms($text)
    {
        return $this->getParentService()->getTerms($text);
    }

    public function addDescription($addDescription)
    {
        $sDesc = $this->getDescription();
        if(strpos($sDesc, $addDescription) === false) {
            $this->setDescription($sDesc . $addDescription);
        }
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        if(!$description) {
            return false;
        }
        $this->description = $description;
        return true;
    }

    public function addName($text)
    {
        $this->name .= $text;
    }

    private function getRecieverConfig($hidden = false)
    {
        $arConfig = array(
            'RECIEVER' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_RECIEVER'),
                'SIZE' => 50,
            ),
            'PHONE' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_PHONE'),
                'SIZE' => 15,
            ),
            'EMAIL' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_EMAIL'),
                'SIZE' => 20,
            )
        );
        if($hidden) {
            $arConfig['RECIEVER']['READONLY'] = true;
            $arConfig['RECIEVER']['VALUE'] = Config::getDataValue('direct_reciever');
            $arConfig['PHONE']['READONLY'] = true;
            $arConfig['PHONE']['VALUE'] = Config::getDataValue('direct_phone');
            $arConfig['EMAIL']['READONLY'] = true;
            $arConfig['EMAIL']['VALUE'] = Config::getDataValue('direct_email');
        }
        return $arConfig;
    }

    private function getAddressConfig($hidden = false)
    {
        $arConfig = array(
            'ZIP' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_ZIP'),
                'SIZE' => 10
            ),
            'STREET' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_STREET'),
                'SIZE' => 50
            ),
            'HOUSE' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_HOUSE'),
                'SIZE' => 8
            ),
            'FLAT' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_FLAT'),
                'SIZE' => 8
            )
        );
        if($hidden) {
            $arConfig['ZIP']['READONLY'] = true;
            $arConfig['ZIP']['VALUE'] = Config::getDataValue('direct_zip');
            $arConfig['STREET']['READONLY'] = true;
            $arConfig['STREET']['VALUE'] = Config::getDataValue('direct_street');
            $arConfig['HOUSE']['READONLY'] = true;
            $arConfig['HOUSE']['VALUE'] = Config::getDataValue('direct_house');
            $arConfig['FLAT']['READONLY'] = true;
            $arConfig['FLAT']['VALUE'] = Config::getDataValue('direct_flat');
        }
        return $arConfig;
    }

    private function getDateConfig($hidden = false)
    {
        $arConfig = array(
            'DATE_TYPE' => array(
                'TYPE' => 'ENUM',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_DATE_TYPE'),
                'OPTIONS' => array(
                    Config::DATE_NEAR => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_DATE_NEAR'),
                    Config::DATE_DELAY => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_DATE_DELAY'),
                ),
                'ONCHANGE' => 'this.form.submit();'
            )
        );
        if($this->config['DIRECT']['DATE_TYPE'] == 'delay') {
            $arConfig['DELAY'] = array(
                'TYPE' => 'ENUM',
                'NAME' => Loc::getMessage('OSH_DIRECT_HANDLER_CONFIG_DIRECT_DATE_DELAY_NAME'),
                'OPTIONS' => array(
                    "1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5
                )
            );
        }
        if($hidden) {
            $arConfig['DATE_TYPE']['DISABLED'] = true;
            $arConfig['DATE_TYPE']['VALUE'] = Config::getDataValue('direct_date_type');
            if(!empty($arConfig['DELAY']) && $this->config['DIRECT']['DATE_TYPE'] == 'delay') {
                $arConfig['DELAY']['READONLY'] = true;
                $arConfig['DELAY']['VALUE'] = Config::getDataValue('direct_date_delay');
            }
        }
        return $arConfig;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    public function getEmbeddedExtraServicesList()
    {
        $extraServices = [
            'DATE_DELIVERY' => array(
                'NAME' => Loc::getMessage('OSH_DATE_DELIVERY_NAME'),
                'SORT' => 100,
                'RIGHTS' => 'NYY',
                'ACTIVE' => 'Y',
                'CLASS_NAME' => '\Osh\Delivery\Services\DateDelivery',
                'DESCRIPTION' => Loc::getMessage('OSH_DATE_DELIVERY_DESCRIPTION'),
                'INIT_VALUE' => date('d.m.Y'),
                'PARAMS' => array('PRICE' => 0)
            )
        ];
        if($this->isOshCourier()) {
            $extraServices['TIME_DELIVERY'] = array(
                'NAME' => Loc::getMessage('OSH_TIME_DELIVERY_NAME'),
                'SORT' => 100,
                'RIGHTS' => 'NYY',
                'ACTIVE' => 'Y',
                'CLASS_NAME' => '\Osh\Delivery\Services\TimeDelivery',
                'DESCRIPTION' => Loc::getMessage('OSH_TIME_DELIVERY_DESCRIPTION'),
                'INIT_VALUE' => array(0),
                'PARAMS' => array('PRICE' => 0)
            );
        }
        return $extraServices;
    }

    public static function onAfterAdd($serviceId, $fields)
    {
        if(substr($fields["CLASS_NAME"], 0, 1) != "\\") {
            $fields["CLASS_NAME"] = "\\" . $fields["CLASS_NAME"];
            DST::update($serviceId, array('CLASS_NAME' => $fields['CLASS_NAME']));
        }
        return true;
    }

    public static function onAfterUpdate($serviceId, array $fields = array())
    {
        if(substr($fields["CLASS_NAME"], 0, 1) != "\\") {
            $fields["CLASS_NAME"] = "\\" . $fields["CLASS_NAME"];
            DST::update($serviceId, array('CLASS_NAME' => $fields['CLASS_NAME']));
        }
        return true;
    }
}