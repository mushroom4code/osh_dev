<?
namespace Ipol\Fivepost;

IncludeModuleLangFile(__FILE__);

/**
 * Class subscribeHandler
 * @package Ipol\Fivepost\
 * Класс, работающий с подписками на события Битрикса и обработчик аяксовых обращений для вызова конкретного
 * метода. Все подписки в первую очередь летят в этот класс, который уже вызывает нужные функции.
 * Это сделано для упрощения дебага: все отключается в одном месте.
 */
class SubscribeHandler extends AbstractGeneral
{
    public static $link = true;

    /**
     * @param $action
     * Вызыается из /js/ajax.php, чтобы все было в одном месте. Делегирует реквест в нужный Главный класс.
     */
    public static function getAjaxAction($action){
        // примеры вызовов
        if(method_exists('\Ipol\Fivepost\AgentHandler',$action))
            \Ipol\Fivepost\AgentHandler::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\OptionsHandler',$action))
            \Ipol\Fivepost\OptionsHandler::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\AuthHandler',$action))
            \Ipol\Fivepost\AuthHandler::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\Warhouses',$action))
            \Ipol\Fivepost\Warhouses::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\PvzWidgetHandler',$action))
            \Ipol\Fivepost\PvzWidgetHandler::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\OrderHandler',$action))
            \Ipol\Fivepost\OrderHandler::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\BarcodeHandler',$action))
            \Ipol\Fivepost\BarcodeHandler::$action($_POST);
        elseif(method_exists('\Ipol\Fivepost\StatusHandler',$action))
            \Ipol\Fivepost\StatusHandler::$action($_POST);
    }

    // RegisterModuleDependences

    /**
     * @return array
     * Список всех подписок методов на события Битрикса.
     * Формат: модуль Битрикса, событие модуля Битрикса, код текущего модуля, путь к Главному классу, сортировка (если надо)
     */
    protected static function getDependences(){
        return array(
            array('main', 'OnEpilog', self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler', 'onEpilog'),
            array('sale', 'OnSaleOrderBeforeSaved',self::$MODULE_ID,'\Ipol\Fivepost\SubscribeHandler','onBeforeOrderCreate'),
            array("sale", "OnSaleComponentOrderOneStepComplete", self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler','onOrderCreate'),
            array("sale", "OnSaleComponentOrderUserResult", self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler','getOrderCreatePaysystem'),

            // Add module delivery handler classes
            array('sale', 'onSaleDeliveryHandlersClassNamesBuildList', self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler', 'onSaleDeliveryHandlersClassNamesBuildList'),

            // PVZ
            array("sale", "OnSaleComponentOrderOneStepProcess",  self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler', "loadWidjet",900),
            array("main", "OnEndBufferContent", self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler', "addWidjetData"),
            array("sale", "OnSaleComponentOrderOneStepDelivery", self::$MODULE_ID, 'Ipol\Fivepost\SubscribeHandler', "prepareData",900),

        );
    }

    /**
     * Регистрация всех подписок, объявленных в getDepencences
     */
    public static function register(){
        foreach (self::getDependences() as $regArray){
            RegisterModuleDependences($regArray[0],$regArray[1],$regArray[2],$regArray[3],$regArray[4],(isset($regArray[5]) ? $regArray[5] : false));
        }
    }

    /**
     * Сброс всех подписок (при удалении модуля или разлогинивании)
     */
    public static function unRegister(){
        foreach(self::getDependences() as $regArray){
            UnRegisterModuleDependences($regArray[0],$regArray[1],$regArray[2],$regArray[3],$regArray[4]);
        }
    }

    // Events
    public static function onEpilog(){
         Admin\OrderSender::init();
    }
        // Widget
    public static function loadWidjet(){
        PvzWidgetHandler::loadWidjet();
    }

    public static function addWidjetData(&$content){
        PvzWidgetHandler::addWidjetData($content);
    }

    public static function prepareData($arResult,$arUserResult){
        PvzWidgetHandler::prepareData($arResult,$arUserResult);
    }

        // Register module delivery handler classes
    public static function onSaleDeliveryHandlersClassNamesBuildList()
    {
        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                // Delivery service
                '\Ipol\Fivepost\Bitrix\Handler\DeliveryHandler'       => '/bitrix/modules/'.self::$MODULE_ID.'/classes/lib/Bitrix/Handler/DeliveryHandler.php',
                // Delivery service profiles (only pickup for now)
                '\Ipol\Fivepost\Bitrix\Handler\DeliveryHandlerPickup' => '/bitrix/modules/'.self::$MODULE_ID.'/classes/lib/Bitrix/Handler/DeliveryHandlerPickup.php',
            )
        );

        return $result;
    }

    public static function getOrderCreatePaysystem($arUserResult, $obOrder, $arParams){
        DeliveryHandler::getOrderCreatePaysystem($arUserResult, $obOrder, $arParams);
    }

        // orderPVZPropHandling
    public static function onBeforeOrderCreate($entity,$values)
    {
        $pvz = PvzWidgetHandler::checkPVZProp($entity,$values);
        return ($pvz === true) ? PvzWidgetHandler::checkPVZPaysys($entity,$values) : $pvz;
    }

    public static function onOrderCreate($oId,$arFields)
    {
        OrderPropsHandler::onOrderCreate($oId,$arFields);
    }
}