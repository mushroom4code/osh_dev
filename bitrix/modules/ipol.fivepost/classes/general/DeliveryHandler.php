<?
namespace Ipol\Fivepost;

use \Ipol\Fivepost\Bitrix\Entity\Options;
use \Ipol\Fivepost\Bitrix\Handler\Deliveries;

use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\Basket;
use \Bitrix\Sale\Delivery;
use \Bitrix\Sale\PaySystem;
use \Bitrix\Sale\PriceMaths;
use Ipol\Fivepost\Bitrix\Handler\GoodsPicker;

/**
 * Class DeliveryHandler
 * @package Ipol\Fivepost
 */
class DeliveryHandler extends AbstractGeneral
{
    /**
     * Available paysystem types
     */
    const PAYSYSTEM_CASH    = 'CASH';
    const PAYSYSTEM_CARD    = 'CARD';
    const PAYSYSTEM_BILL    = 'BILL';
    const PAYSYSTEM_LOYALTY = 'LOYALTY';

    public static $chosenPaysystem = false;

    /**
     * Calculate delivery for external order. Used in points widget and other tools
     *
     * @param array $arOrder
     * @param array|bool $setter - false or array of type 'basket' => true | 'order'=>orderId
     * @return array
     */
    public static function calculateDelivery($arOrder,$setter=false)
    {
        $result = array();

        $order = Order::create(SITE_ID);
        $order->setField('CURRENCY', CurrencyManager::getBaseCurrency());

        // Basket
        $basket = Basket::create(SITE_ID);

        switch(true){
            case ($setter && is_array($setter) && array_key_exists('order',$setter) && $setter['order'])   :
                $exisOrder = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderById($setter['order']);
                if($exisOrder){
                    $arGoods = GoodsPicker::fromOrder($setter['order']);
                    $arOrder['PAY_SYSTEM_ID']  = $order->getPaymentSystemId();
                    $arOrder['PERSON_TYPE_ID'] = $order->getPersonTypeId();
                }
            break;
            case ($setter && is_array($setter) && array_key_exists('basket',$setter) && $setter['basket']) : $arGoods = GoodsPicker::fromBasket(); break;
            default : $arGoods = array(); break;
        }

        if (empty($arGoods)) {
            $setter = false;
        } else {
            foreach ($arGoods as $arGood) {
                $item = $basket->createItem('catalog', $arGood['PRODUCT_ID']);
                unset($arGood['ID']);
                unset($arGood['PRODUCT_ID']);
                unset($arGood['ORDER_ID']);
                $arGood['PRODUCT_PROVIDER_CLASS'] = 'CCatalogProductProvider';
                $item->setFields($arGood);
            }
        }

        if (!$setter) {
            $item = $basket->createItem('catalog', 1);
            $item->setFields(array(
                'NAME' => 'IpolFivepostBasket',
                'PRICE' => $arOrder['PRICE'],
                'WEIGHT' => $arOrder['WEIGHT'],
                'DIMENSIONS' => serialize(array(
                    'LENGTH' => $arOrder['DIMENSIONS']['L'],
                    'WIDTH' => $arOrder['DIMENSIONS']['W'],
                    'HEIGHT' => $arOrder['DIMENSIONS']['H'],
                )),
                'QUANTITY' => 1,
                'CURRENCY' => CurrencyManager::getBaseCurrency(),
                'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
                'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
                'DELAY' => 'N',
                'CAN_BUY' => 'Y',
                'SUBSCRIBE' => 'N'
            ));
        }

        $order->setBasket($basket);

        // Pay system
        if ($arOrder['PAY_SYSTEM_ID']) {
            $paymentCollection = $order->getPaymentCollection();
            $payment
                               = $paymentCollection->createItem(PaySystem\Manager::getObjectById($arOrder['PAY_SYSTEM_ID']));
            $payment->setField("SUM", $order->getPrice());
            $payment->setField("CURRENCY", $order->getCurrency());
        }
        // Person type
        if ($arOrder['PERSON_TYPE_ID']) {
            $order->setPersonTypeId($arOrder['PERSON_TYPE_ID']);
        }

        // Location
        $collection = $order->getPropertyCollection();

        $deliveryLocation = $collection->getDeliveryLocation();
        $propertyLocationId = $deliveryLocation->getField("ORDER_PROPS_ID");

        foreach ($collection as $item) {
            if ($item->getPropertyId() == $propertyLocationId)
                $item->setField('VALUE', $arOrder['LOCATION']);
        }

        //$arDeliveryServiceAll = Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);

        // Delivery
        $actualProfiles = Deliveries::getActualProfiles(); // Do we need inactive profiles there?
        foreach (array_keys($actualProfiles) as $deliveryId)
        {
            $deliveryObj = Delivery\Services\Manager::getObjectById($deliveryId);

            // Set selected point guid there
            $deliveryObj->setSelectedPointGuid($arOrder['POINT_GUID']);

            $shipmentCollection = $order->getShipmentCollection();
            $shipment = $shipmentCollection->createItem();

            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            foreach($basket as $basketItem)
            {
                $item = $shipmentItemCollection->createItem($basketItem);
                $item->setQuantity($basketItem->getQuantity());
            }

            //$deliveryObj->isCompatible($shipment);
            $order->refreshData();
            $shipment->setFields([
                'DELIVERY_ID' => $deliveryId,
                'CURRENCY' => $order->getCurrency(),
            ]);

            /** @var \Bitrix\Sale\Delivery\CalculationResult $calculationResult */
            $calculationResult = $shipment->calculateDelivery();

            $priceDelivery = $calculationResult->getPrice();
            $priceDelivery = PriceMaths::roundPrecision($priceDelivery);
            $shipment->setField('BASE_PRICE_DELIVERY', $priceDelivery);
            $order->doFinalAction(true);

            if ($calculationResult->isSuccess())
            {
                $profile = Deliveries::defineProfileByClass($actualProfiles[$deliveryId]['CLASS_NAME']);

                $result[$profile][$deliveryId] = array(
                    'PERIOD'      => $calculationResult->getPeriodDescription(),
                    'PERIOD_FROM' => $calculationResult->getPeriodFrom(),
                    'PERIOD_TO'   => $calculationResult->getPeriodTo(),
                    'PERIOD_TYPE' => $calculationResult->getPeriodType(),
                    'PRICE'       => $order->getDeliveryPrice(), // need for support cart-rules
                    'CURRENCY'    => CurrencyManager::getBaseCurrency(),
                    'ERROR'       => implode(',',$calculationResult->getErrorMessages())
                 );
            }
            $shipment->setField('DELIVERY_ID', 1); // reset delivery to default before delete
            $shipment->delete();
        }

        return $result;
    }

    public static function getOrderCreatePaysystem($arUserResult, $obOrder, $arParams)
    {
        if ($arUserResult['PAY_SYSTEM_ID']) {
            self::$chosenPaysystem = $arUserResult['PAY_SYSTEM_ID'];
        }
    }

    /**
     * Define paysystem type based on module options
     * @return string @see DeliveryHandler::PAYSYSTEM_* constants
     */
    public static function definePaysystem()
    {
        $options = new Options();
        $ps = false;

        if (self::$chosenPaysystem) {
            if (in_array(self::$chosenPaysystem, $options->fetchPayNal())) {
                $ps = self::PAYSYSTEM_CASH;
            } else if (in_array(self::$chosenPaysystem, $options->fetchPayCard())) {
                $ps = self::PAYSYSTEM_CARD;
            } else {
                $ps = self::PAYSYSTEM_BILL;
            }
        }

        if (self::$chosenPaysystem === false || !$ps) {
            // Get default variant from Module options
            switch ($options->fetchPaySystemDefaultType()) {
                case 'CASH':
                    $ps = self::PAYSYSTEM_CASH;
                    break;
                case 'CARD':
                    $ps = self::PAYSYSTEM_CARD;
                    break;
                case 'BILL':
                    $ps = self::PAYSYSTEM_BILL;
                    break;
            }
        }

        return $ps;
    }
}