<?php

namespace Xzag\Telegram\Event;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Table;
use Bitrix\Sale\Helpers\Admin\Product as ProductHelper;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\Shipment;
use Throwable;
use Xzag\Telegram\Service\PropertyService;

/**
 * Class SaleOrderSavedEvent
 * @package Xzag\Telegram\Event
 */
abstract class SaleOrderSavedEvent extends ConvertibleEvent
{
    const TYPE = 'OnSaleOrderSaved';
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var PropertyService
     */
    private $propertyService;

    /**
     * SaleOrderSavedEvent constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct($event);
        $this->order = $this->getEvent()->getParameter('ENTITY');
        $this->propertyService = new PropertyService();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ORDER' => $this->order instanceof Order
                ? $this->order->getFieldValues()
                : $this->getEvent()->getParameters()
        ];
    }

    /**
     * @return string
     */
    public function getOrderAdminUrl(): string
    {
        $server = Context::getCurrent()->getServer();
        $uri = new Uri('/bitrix/admin/sale_order_view.php');
        $uri->setHost($server->getHttpHost());
        $uri->addParams([
            'ID' => $this->order->getId()
        ]);

        return $uri->getUri();
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return (string)$this->order->getId();
    }

    /**
     * @return string
     */
    public static function getModule(): string
    {
        return 'sale';
    }

    /**
     * @param $eventData
     * @return BitrixBasedEventInterface
     */
    public static function make($eventData): BitrixBasedEventInterface
    {
        /**
         * @var Event $eventData
         */
        return new static($eventData);
    }

    /**
     * @return array
     */
    protected function getDeliveryMethods(): array
    {
        try {
            $deliveryList = method_exists($this->order, 'getDeliveryIdList')
                ? $this->order->getDeliveryIdList()
                : $this->order->getDeliverySystemId();

            $deliveryMethods = array_map(
                function ($dId) {
                    $s = Table::getById($dId)->fetch();
                    return !empty($s) ? $s : null;
                },
                array_unique($deliveryList)
            );

            return array_filter($deliveryMethods);
        } catch (SystemException $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    protected function getPaymentMethods(): array
    {
        try {
            $paymentList = method_exists($this->order, 'getPaySystemIdList')
                ? $this->order->getPaySystemIdList()
                : $this->order->getPaymentSystemId();

            $paymentMethods = array_map(
                function ($pId) {
                    $s = Manager::getById($pId);
                    return !empty($s) ? $s : null;
                },
                $paymentList
            );

            return array_filter($paymentMethods);
        } catch (SystemException $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    protected function getPayments(): array
    {
        $collection = $this->order->getPaymentCollection();
        $payments = [];
        /** @var Payment $payment */
        foreach ($collection as $payment) {
            if ($payment->isPaid()) {
                $payments[] = $payment->getFieldValues();
            }
        }

        return $payments;
    }

    /**
     * @return array
     */
    public function getShipments(): array
    {
        try {
            $collection = $this->order->getShipmentCollection();
        } catch (SystemException $e) {
            $collection = [];
        }

        $shipments = [];
        /** @var Shipment $shipment */
        foreach ($collection as $shipment) {
            if (!$shipment->isSystem()) {
                try {
                    $store = null;
                    if ($storeId = $shipment->getStoreId()) {
                        $store = StoreTable::getById($storeId)->fetch();
                    }
                } catch (Throwable $e) {
                    // do nothing
                }

                $shipments[] = array_merge(
                    $shipment->getFieldValues(),
                    [
                        'store' => $store
                    ]
                );
            }
        }

        return $shipments;
    }

    /**
     * @return array
     */
    public function getTemplateParams(): array
    {
        $userId = $this->order->getUserId();

        $order = $this->order->getFieldValues();
        $order['PROPERTY_VALUES'] = $this->getOrderProperties($this->order);

        $payments = $this->getPayments();
        $shipments = $this->getShipments();
        $basket = $this->order->getBasket();
        $items = array_map(function ($item) {
            /**
             * @var BasketItem $item
             */
            $itemData = $item->getFieldValues();
            $itemData['PROPERTY_VALUES'] = $this->getBasketItemProperties($item);

            return $itemData;
        }, $basket ? $basket->getBasketItems() : []);

        return array_merge(
            parent::getTemplateParams(),
            [
                'USER' => $this->getUserById($userId),
                'ORDER' => $order,
                'LINK' => $this->getOrderAdminUrl(),
                'PAYMENT_METHODS' => $this->getPaymentMethods(),
                'DELIVERY_METHODS' => $this->getDeliveryMethods(),
                'PAYMENTS' => $payments,
                'SHIPMENTS' => $shipments,
                'ITEMS' => $items
            ]
        );
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getOrderProperties(Order $order): array
    {
        $propertyValues = [];

        try {
            $orderPropertyCollection = $order->getPropertyCollection()->getArray();
            $properties = $this->propertyService->formatProperties($orderPropertyCollection['properties'] ?? []);

            foreach ($properties as $property) {
                $propertyValues[$property['CODE']] = $property;
            }
        } catch (Throwable $e) {
            //
        }

        return $propertyValues;
    }

    /**
     * @param BasketItem $item
     *
     * @return array
     */
    private function getBasketItemProperties(BasketItem $item): array
    {
        $site = $this->getSite();
        $propertyValues = [];
        try {
            $properties = $this->propertyService->getProperties($item->getProductId());

            $productData = ProductHelper::getData([$item->getProductId()], $site['ID'] ?? null);
            if (!empty($productData)) {
                $productData = $productData[$item->getProductId()];
            }

            $mainProductId = $productData['PRODUCT_ID'] ?? null;

            $productProperties = [];
            if ($mainProductId !== $item->getProductId()) {
                $productProperties = $this->propertyService->getProperties($mainProductId);
            }

            $propsList = array_merge(
                $this->propertyService->formatProperties($productProperties),
                $this->propertyService->formatProperties($properties),
                $productData['PROPS'] ?? []
            );
            foreach ($propsList as $prop) {
                if (mb_substr($prop['CODE'], -6) === 'XML_ID') {
                    continue;
                }
                $propertyValues[$prop['CODE']] = $prop;
            }
        } catch (Throwable $e) {
            $propertyValues = [];
        }

        return $propertyValues;
    }
}
