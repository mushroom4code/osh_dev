<?
namespace Ipol\Fivepost;

IncludeModuleLangFile(__FILE__);

use \Ipol\Fivepost\Bitrix\Adapter;
use \Ipol\Fivepost\Bitrix\Adapter\Cargo;
use \Ipol\Fivepost\Bitrix\Entity\DefaultGabarites;
use \Ipol\Fivepost\Bitrix\Entity\Options;
use \Ipol\Fivepost\Bitrix\Handler\Deliveries;
use \Ipol\Fivepost\Bitrix\Handler\GoodsPicker;
use \Ipol\Fivepost\Bitrix\Handler\LocationsDelivery;
use \Ipol\Fivepost\Bitrix\Tools;

use \Bitrix\Main\EventResult;
use \Bitrix\Sale\ResultError;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\Payment;
use \Bitrix\Sale\Shipment;

class PvzWidgetHandler extends abstractGeneral
{
    protected static $postField = 'PVZ';

    public static $city;
    public static $PAY_SYSTEM_ID  = false;
    public static $PERSON_TYPE_ID = false;
    public static $DELIVERY_ID     = false;

    public static $selDeliv;
    public static $widjetLoaded = false;
    public static $options      = false;

    public static function loadWidjet(){
        if(Deliveries::isActive() && $_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"]) {
            \CJSCore::Init(array('jquery3'));
            $pathToController = Tools::getJSPath().'pvzWidjet.js';
            $pathToWidjet     = Tools::getJSPath().'widjet/widjet.js';

            $GOODS = GoodsPicker::fromBasket();
            $obDefGabs = new DefaultGabarites();
            $obCargo = new Cargo($obDefGabs);

            // Something wrong with basket items CAN_BUY flag or basket was empty at all
            if (empty($GOODS))
                $GOODS = array(Tools::makeSimpleGood());

            $cargos = $obCargo->set($GOODS)->getCargo();
            $dims   = $cargos->getDimensions();

            if(
                file_exists($_SERVER['DOCUMENT_ROOT'].$pathToWidjet) &&
                file_exists($_SERVER['DOCUMENT_ROOT'].$pathToController)
            ){
                $GLOBALS['APPLICATION']->AddHeadScript($pathToWidjet);
                $GLOBALS['APPLICATION']->AddHeadScript($pathToController);
                ?>
                <script type="text/javascript">
                    var <?=self::getMODULELBL()?>PVZWidjet = new ipol_fivepost_pvzWidjet(
                        '<?=self::$city?>',
                        <?=\CUtil::PhpToJSObject(self::getProfileLink())?>,
                        <?=\CUtil::PhpToJSObject(Adapter::getPaymentCorresponds())?>,
                        '<?=self::getSavingLink()?>', // where pvz saves via request
                        '<?=self::getPostField()?>', // contents result of calculations?,
                        <?=\CUtil::PhpToJSObject(self::getAddressInput())?>, // id props where ID is saved
                        '<?=(self::$options->fetchPvzLabel()) ? self::$options->fetchPvzLabel() : Tools::getMessage('WIDJET_CHOOSE')?>',
                        '<?=self::$MODULE_LBL?>',
                        {
                            WRONG_PAY: '<?=Tools::getMessage('WIDJET_ERROR_WRONGPAY')?>',
                            PVZTYPE_POSTAMAT : '<?=Tools::getMessage('WIDJET_PVZTYPE_POSTAMAT')?>',
                            PVZTYPE_ISSUE_POINT : '<?=Tools::getMessage('WIDJET_PVZTYPE_ISSUE_POINT')?>',
                            PVZTYPE_TOBACCO : '<?=Tools::getMessage('WIDJET_PVZTYPE_TOBACCO')?>',
                        },
                        <?=\CUtil::PhpToJSObject(array(
                            'length'     => $dims['L'],
                            'width'      => $dims['W'],
                            'height'     => $dims['H'],
                            'price'      => $cargos->getTotalPrice()->getAmount(),
                            'weight'     => $cargos->getWeight()
                        ))?>,
                        <?=(Option::get('noYmaps') === 'Y') ? 'true' : 'false'?>,
                        '<?=(Option::get('ymapsAPIKey')) ?: \Bitrix\Main\Config\Option::get('fileman', 'yandex_map_api_key', '')?>',
                        <?=(Option::get('widgetSearch') === 'Y') ? 'true' : 'false'?>,
                        <?=(Option::get('widgetSearchMark') === 'Y') ? 'true' : 'false'?>,
                        '<?=Option::get('paySystemDefaultType')?>'
                    );
                    <?=self::getMODULELBL()?>PVZWidjet.setObRequestConcat({
                        getBasket : true,
                    });
                </script>
                <?
                Tools::getCommonCss();
            }
        }
    }

    // WORKOUT

    /**
     * @return array of places for linking with widjet
     */
    public static function getProfileLink()
    {
        $arProfiles = Deliveries::getActualProfiles(true);
        $profileClasses = ProfileHandler::getProfileClasses();
        $objProfiles = array();

        $specialPVZ      = Option::get('pvzID');
        foreach($arProfiles as $id => $arProfile) {
            switch(true) {
                case $specialPVZ && $arProfile['CLASS_NAME'] === $profileClasses['pickup'] :
                    $objProfiles[$specialPVZ] = array(
                        'tag'   => false,
                        'price' => false,
                        'self'  => true,
                        'link'  => $id,
                        //'type'  => 'pickup'
                    );
                    break;
                default :
                    $objProfiles[$id] = array(
                        'tag'   => false,
                        'price' => false,
                        'self'  => false,
                    );
                    break;
            }
        }

        return $objProfiles;
    }

    /**
     * In which key of ajax answer put the widjet data
     * @return string
     */
    public static function getPostField()
    {
        return self::$MODULE_LBL.self::$postField;
    }

    /**
     * In what key of request will be the id of chosen PVZ
     * @return string
     */
    public static function getSavingLink(){
//        return self::$MODULE_LBL.'pickup_pvz';
        return 'POINT_GUID';
    }

    public static function getAddressInput(){
        $arInputs = array();
        if(\cmodule::includeModule('sale')){
            $orderProp = self::getOptions()->fetchPvzPicker();
            $dbProps = \CSaleOrderProps::GetList(array(),array('CODE' => $orderProp));

            while($arProp=$dbProps->Fetch())
                $arInputs []= $arProp['ID'];
        }

        return $arInputs;
    }

    /**
     * @return Options
     */
    protected static function getOptions(){
        if(!self::$options){
            self::$options = new Options();
        }

        return self::$options;
    }

    /**
     * Add specific widget data to page (usually bitrix:sale.order.ajax component AJAX answer)
     * @param $content
     */
    public static function addWidjetData(&$content){
        if(Deliveries::isActive()) {
            $noJson = self::no_json($content);
            if (($_REQUEST['is_ajax_post'] == 'Y' || $_REQUEST["AJAX_CALL"] == 'Y' || $_REQUEST["ORDER_AJAX"]) && $noJson) {
                $content
                    .= '<input type="hidden"
                                id="' . self::getPostField() . '"
                                name="' . self::getPostField() . '"
                                value=\'' . Tools::jsonEncode(array(
                        'city'   => self::$city,
                        'paysys' => DeliveryHandler::definePaysystem(),
                        'PAY_SYSTEM_ID' => self::$PAY_SYSTEM_ID,
                        'PERSON_TYPE_ID' => self::$PERSON_TYPE_ID,
                        'DELIVERY_ID'    => self::$DELIVERY_ID
                    )) . '\' />
                        ';
            } elseif (
                (
                    $_REQUEST['action'] == 'refreshOrderAjax' ||
                    $_REQUEST['soa-action'] == 'refreshOrderAjax'
                ) &&
                !$noJson
            ) {
                $content = json_decode($content,true);
                $content [self::getPostField()] =  array(
                    'city'   => self::$city,
                    'paysys' => DeliveryHandler::definePaysystem(),
                    'PAY_SYSTEM_ID' => self::$PAY_SYSTEM_ID,
                    'PERSON_TYPE_ID' => self::$PERSON_TYPE_ID,
                    'DELIVERY_ID'     => self::$DELIVERY_ID
                );
                $content = json_encode($content);

            }
        }
    }

    public static function no_json($wat){
        return is_null(json_decode($wat,true));
    }

    public static function prepareData($arResult,$arUserResult){
        if(!Deliveries::isActive())
        {
            return false;
        }
        if($arUserResult['DELIVERY_LOCATION'])
        {
            $location = $arUserResult['DELIVERY_LOCATION'];
        }else
        {
            $locationProp = \CSaleOrderProps::GetList(array(),array(
                'PERSON_TYPE_ID' => $arUserResult['PERSON_TYPE_ID'],
                'ACTIVE'         => 'Y',
                'IS_LOCATION'    => 'Y'
            ))->Fetch();
            if($arUserResult['ORDER_PROP'][$locationProp['ID']])
            {
                $location = $arUserResult['ORDER_PROP'][$locationProp['ID']];
            }
            else
            {
                $location = $_REQUEST['order']['ORDER_PROP_'.$locationProp['ID']];
            }
        }

        if($location) {
            $location = Adapter::locationById($location);
            if($location->getLocationLink()) {
                self::$city   = $location->getLocationLink()->getApi()->getCode();
            }
        }

        if($arUserResult['PAY_SYSTEM_ID']){
            self::$PAY_SYSTEM_ID = $arUserResult['PAY_SYSTEM_ID'];
        }
        if($arUserResult['PERSON_TYPE_ID']){
            self::$PERSON_TYPE_ID = $arUserResult['PERSON_TYPE_ID'];
        }
        if($arUserResult['DELIVERY_ID']){
            self::$DELIVERY_ID = $arUserResult['DELIVERY_ID'];
        }

        return true;
    }


    /**
     * Gets PVZ code from request after making order
     * @return bool|string of id
     */
    protected static function getRequestPVZ()
    {
        $check = (
            !array_key_exists(PvzWidgetHandler::getSavingLink(), $_REQUEST) ||
            !$_REQUEST[PvzWidgetHandler::getSavingLink()] ||
            $_REQUEST[PvzWidgetHandler::getSavingLink()] == 'false'
        ) ? false : $_REQUEST[PvzWidgetHandler::getSavingLink()];

        return $check;
    }

    /**
     * @param Order $entity
     * @param $values
     * @return EventResult|bool
     */
    public static function checkPVZProp($entity,$values)
    {
        $options = new Options();
        if(
            !Tools::isAdminSection() &&
            Deliveries::isActive() &&
            $options->fetchNoPVZnoOrder() == 'Y'
        ) {
            $shipmentCollection = $entity->getShipmentCollection();
            $shipmentCollection->rewind();
            /** @var Shipment $obShipment */
            while($obShipment = $shipmentCollection->next()){
                if ($obShipment->isSystem()) {
                    continue;
                }

                $delivery = Deliveries::defineDelivery($obShipment->getField('DELIVERY_ID'));
                if ($delivery === 'pickup' && !self::getRequestPVZ())
                {
                    return new EventResult(EventResult::ERROR, new ResultError(Tools::getMessage('ERROR_NOPVZ'), 'code'), 'sale');
                }
            }
            $shipmentCollection->rewind();
        }
        return true;
    }

    /**
     * @param Order $entity
     * @param $values
     * @return EventResult|bool
     */
    public static function checkPVZPaysys($entity, $values)
    {
        $options = new Options();
        if(
            !Tools::isAdminSection() &&
            Deliveries::isActive()
        ){
            $shipmentCollection = $entity->getShipmentCollection();

            $shipmentCollection->rewind();
            /** @var Shipment $obShipment */
            while($obShipment = $shipmentCollection->next()){
                if($obShipment->isSystem()){
                    continue;
                }

                $delivery = Deliveries::defineDelivery($obShipment->getField('DELIVERY_ID'));

                if($delivery === 'pickup')
                {
                    $checkMode   = false;
                    $nalPayment  = $options->fetchPayNal();
                    $cardPayment = $options->fetchPayCard();
                    $paymentCollection = $entity->getPaymentCollection();
                    $paymentCollection->rewind();
                    /** @var Payment $obPayment */
                    foreach($paymentCollection as $index => $obPayment){
                        if(in_array($obPayment->getField('PAY_SYSTEM_ID'),$nalPayment)){
                            $checkMode = 'CASH';
                        } elseif(in_array($obPayment->getField('PAY_SYSTEM_ID'),$cardPayment)){
                            $checkMode = 'CARD';
                        }
                        if($checkMode){
                            break;
                        }
                    }
                    $paymentCollection->rewind();

                    $allow      = true;
                    $arPoints   = false;
                    if($checkMode) {
                        if ($chosenPVZ = self::getRequestPVZ()) {
                            $arPoints = PointsTable::getByPointGuid($chosenPVZ);
                            if($arPoints){
                                $allow = ($arPoints[$checkMode.'_ALLOWED'] === 'Y');
                            }
                        }
                    }

                    if(!$allow && $arPoints){
                        $errorMess = Tools::getMessage('ERROR_NOPAY_'.$checkMode);
                        return new EventResult(EventResult::ERROR, new ResultError($errorMess, 'code'), 'sale');
                    }
                }
            }
            $shipmentCollection->rewind();
        }

        return true;
    }

    protected static $answer = false;

    // widjet stuff

    public static function getPVZ()
    {
        $obPoints = PointsHandler::getPoints();
        $arCities = LocationsHandler::getCities();
        $arPoints = array(
            'POINTS' => array(),
            'CITY' => array(),
            'CITYREG' => array(),
            'REGIONSMAP' => array(),
            'CITYFULL' => array(),
            'REGIONS' => array(),
        );

        if($obPoints->isSuccess()){
            $curPoints = $obPoints->getData();
            $arPoints['POINTS'] = $curPoints['POINTS'];
        }

        foreach ($arCities as $arCity){
            $arPoints['CITY'][$arCity['CODE']]    = $arCity['NAME'];
            $arPoints['CITYREG'][$arCity['CODE']] = $arCity['REGION'];
            if(!array_key_exists($arCity['REGION'],$arPoints['REGIONSMAP'])){
                $arPoints['REGIONSMAP'][$arCity['REGION']] = array();
            }
            $arPoints['REGIONSMAP'][$arCity['REGION']] = $arCity['CODE'];
            $arPoints['CITYFULL'][$arCity['CODE']] = $arCity['COUNTRY'].' '.$arCity['REGION'].' '.$arCity['NAME'];
            $arPoints['REGIONS'][$arCity['CODE']] = implode(', ', array_filter(array($arCity['REGION'],$arCity['COUNTRY'])));
        }

        self::toAnswer(array('pvz' => $arPoints));
        self::printAnswer();
    }

    public static function getLang(){
        self::toAnswer(array('LANG' => self::getLangArray()));
        self::printAnswer();
    }

    public static function getLangArray()
    {
        $tanslate = array(
            'rus' => array(
                'YOURCITY'   => Tools::getMessage('WIDGET_YOURCITY'),
                'COURIER'    => Tools::getMessage('WIDGET_COURIER'),
                'PICKUP'     => Tools::getMessage('WIDGET_PICKUP'),
                'TERM'       => Tools::getMessage('WIDGET_TERM'),
                'PRICE'      => Tools::getMessage('WIDGET_PRICE'),
                'DAY'        => Tools::getMessage('WIDGET_DAY'),
                'RUB'        => Tools::getMessage('WIDGET_RUB'),
                'NODELIV'    => Tools::getMessage('WIDGET_NODELIV'),
                'CITYSEARCH' => Tools::getMessage('WIDGET_CITYSEARCH'),
                'ALL'        => Tools::getMessage('WIDGET_ALL'),
                'PVZ'        => Tools::getMessage('WIDGET_PVZ'),
                'MOSCOW'     => Tools::getMessage('WIDGET_MOSCOW'),
                'RUSSIA'     => Tools::getMessage('WIDGET_RUSSIA'),
                'COUNTING'   => Tools::getMessage('WIDGET_COUNTING'),
                'NO_AVAIL'          => Tools::getMessage('WIDGET_NO_AVAIL'),
                'NO_PAY'            => Tools::getMessage('WIDGET_NO_PAY'),
                'CHOOSE_TYPE_AVAIL' => Tools::getMessage('WIDGET_CHOOSE_TYPE_AVAIL'),
                'CHOOSE_OTHER_CITY' => Tools::getMessage('WIDGET_CHOOSE_OTHER_CITY'),
                'TYPE_ADDRESS'      => Tools::getMessage('WIDGET_TYPE_ADDRESS'),
                'TYPE_ADDRESS_HERE' => Tools::getMessage('WIDGET_TYPE_ADDRESS_HERE'),
                'L_ADDRESS' => Tools::getMessage('WIDGET_L_ADDRESS'),
                'L_TIME'    => Tools::getMessage('WIDGET_L_TIME'),
                'L_WAY'     => Tools::getMessage('WIDGET_L_WAY'),
                'L_DESCR'   => Tools::getMessage('WIDGET_L_DESCR'),
                'L_PRICE'     => Tools::getMessage('WIDGET_L_PRICE'),
                'L_CHOOSE'  => Tools::getMessage('WIDGET_L_CHOOSE'),
                'L_NOPAY'  => Tools::getMessage('WIDGET_L_NOPAY'),
                'H_LIST'    => Tools::getMessage('WIDGET_H_LIST'),
                'H_PROFILE' => Tools::getMessage('WIDGET_H_PROFILE'),
                'H_CASH'    => Tools::getMessage('WIDGET_H_CASH'),
                'H_CARD'   => Tools::getMessage('WIDGET_H_CARD'),
                'H_SUPPORT' => Tools::getMessage('WIDGET_H_SUPPORT'),
                'H_QUESTIONS' => Tools::getMessage('WIDGET_H_QUESTIONS'),
                'DAY0' => Tools::getMessage('WIDGET_DAY0'),
                'DAY1' => Tools::getMessage('WIDGET_DAY1'),
                'DAY2' => Tools::getMessage('WIDGET_DAY2'),
                'DAY3' => Tools::getMessage('WIDGET_DAY3'),
                'DAY4' => Tools::getMessage('WIDGET_DAY4'),
                'DAY5' => Tools::getMessage('WIDGET_DAY5'),
                'DAY6' => Tools::getMessage('WIDGET_DAY6'),
                'L_PAYMENT' => Tools::getMessage('WIDGET_L_PAYMENT'),
                'ERROR_WRONGPAY' => Tools::getMessage('WIDJET_ERROR_WRONGPAY'),
            )
        );

        if (isset($_REQUEST['lang']) && isset($tanslate[$_REQUEST['lang']]) ) return $tanslate[$_REQUEST['lang']];
        else return $tanslate['ru'];
    }

    public static function calcPVZ(){
        $blockedByFilter = false;
        if($_REQUEST['filters']){
            $obPoint = PointsTable::getByPointGuid($_REQUEST['shipment']['pointId']);
            if($obPoint){
                foreach($_REQUEST['filters'] as $filter => $val){
                    $blockedByFilter = (!array_key_exists($filter,$obPoint) || $obPoint[$filter] !== $val);
                }
            }
        }

        $arCalc = array();

        $arResponse = array(
            'result' => array(
                'pointId' => $_REQUEST['shipment']['pointId']
            ),
        );

        switch(true){
            case (array_key_exists('getBasket',$_REQUEST) && $_REQUEST['getBasket']) : $setter = array('basket' => true);break;
            case (array_key_exists('getOrder', $_REQUEST) && $_REQUEST['getOrder']) :  $setter = array('order'  => $_REQUEST['getOrder']);break;
            default : $setter = false; break;
        }

        if(!$blockedByFilter) {
            $obCity = LocationsDelivery::getByFiasGuid($_REQUEST['shipment']['city']);
            if ($obCity) {
                $arOrder = array(
                    'WEIGHT' => $_REQUEST['shipment']['goods'][0]['weight'],
                    'DIMENSIONS' => array(
                        'L' => $_REQUEST['shipment']['goods'][0]['length'],
                        'W' => $_REQUEST['shipment']['goods'][0]['width'],
                        'H' => $_REQUEST['shipment']['goods'][0]['height'],
                    ),
                    'LOCATION' => $obCity['BITRIX_CODE'],
                    'POINT_GUID' => $_REQUEST['shipment']['pointId']
                );
                if(array_key_exists('PAY_SYSTEM_ID',$_REQUEST) && $_REQUEST['PAY_SYSTEM_ID']){
                    $arOrder['PAY_SYSTEM_ID'] = $_REQUEST['PAY_SYSTEM_ID'];
                }
                if(array_key_exists('PERSON_TYPE_ID',$_REQUEST) && $_REQUEST['PERSON_TYPE_ID']){
                    $arOrder['PERSON_TYPE_ID'] = $_REQUEST['PERSON_TYPE_ID'];
                }

                $arCalc  = DeliveryHandler::calculateDelivery($arOrder,$setter);
            }
        }

        if(empty($arCalc)){
            $arResponse['error'] = 'No calculation';
        } else {
            foreach ($arCalc as $arProfiles){
                foreach ($arProfiles as $arResult){
                    $arResponse['result']['price'] = $arResult['PRICE'];
                    $arResponse['result']['term']  = $arResult['PERIOD'];

                    if($arResult['ERROR']){
                        $arResponse['error'] = $arResult['ERROR'];
                    }

                    break;
                }
                break;
            }
        }


        self::toAnswer($arResponse);
        self::printAnswer();
    }

    protected static function toAnswer($wat)
    {
        $stucked = array('error');
        if (!is_array($wat)) {
            $wat = array('info' => $wat);
        }
        if (!is_array(self::$answer)) {
            self::$answer = array();
        }
        foreach ($wat as $key => $sign) {
            if (in_array($key, $stucked)) {
                if (!array_key_exists($key, self::$answer)) {
                    self::$answer[$key] = array();
                }
                self::$answer[$key] [] = $sign;
            } else {
                self::$answer[$key] = $sign;
            }
        }
    }

    protected static function printAnswer()
    {
        echo Tools::jsonEncode(self::$answer);
    }
}