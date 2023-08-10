<?php
namespace Ipol\Fivepost\Bitrix\Handler;

use \Bitrix\Iblock\ElementTable;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\Loader;
use \Bitrix\Sale\Shipment;

class GoodsPicker
{
    /**
     * @var string if constant defined, complects will be handled as separeted goods (not recommended at all)
     */
    protected static $complectsBlocker = 'IPOL_FIVEPOST_DOWNCOMPLECTS';

    /**
     * @var string if constant defined, CAN_BUY check skipped when receiving basket goods
     */
    protected static $ignoreCanBuy = 'IPOL_FIVEPOST_IGNORECANBUY';

    /**
     * Call onItemsListReady event
     * @param array $goods
     */
    protected static function callOnItemsListReady(&$goods)
    {
        // onItemsListReady module event
        $event = new Event(IPOL_FIVEPOST, "onItemsListReady", ['ITEMS' => $goods]);
        $event->send();

        $results = $event->getResults();
        if (is_array($results) && !empty($results)) {
            foreach ($results as $eventResult) {
                if ($eventResult->getType() !== EventResult::SUCCESS)
                    continue;

                $params = $eventResult->getParameters();
                if (isset($params["ITEMS"]))
                    $goods = $params["ITEMS"];
            }
        }
    }

    /**
     * Get goods from current user's basket
     * @return array
     */
    public static function fromBasket()
    {
        return self::getBasketGoods(["ORDER_ID" => "NULL", "FUSER_ID" => \CSaleBasket::GetBasketUserID(), "LID" => SITE_ID]);
    }

    /**
     * Get goods from specified Order
     * @param $orderId
     * @return array|false
     */
    public static function fromOrder($orderId)
    {
        if ($orderId) {
            return self::getBasketGoods(['ORDER_ID' => $orderId]);
        }

        return false;
    }

    /**
     * Get goods from specified Shipment
     * @return false
     */
    public static function fromShipment()
    {
        return false;
    }

    /**
     * Get goods from Bitrix Shipment object
     * @param Shipment $shipment
     * @return array
     */
    public static function fromShipmentObject($shipment)
    {
        $goods = [];

        // Get shipment items data
        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        /** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
        foreach ($shipmentItemCollection as $shipmentItem) {
            $basketItem = $shipmentItem->getBasketItem();
            if (!$basketItem)
                continue;

            // if ($basketItem->isBundleChild())
            //    continue;

            $item = $basketItem->getFieldValues();

            if (!is_array($item["DIMENSIONS"]) && !empty($item["DIMENSIONS"]) && is_string($item["DIMENSIONS"])) {
                $item["DIMENSIONS"] = unserialize($item["DIMENSIONS"], ['allowed_classes' => false]);
            }

            $chosenFields = [
                'ORDER_ID',
                'ID',
                'LID',
                'PRODUCT_ID',
                'TYPE',
                'SET_PARENT_ID',
                'NAME',
                'CAN_BUY',
                'DELAY',
                'PRICE',
                'BASE_PRICE',
                'CURRENCY',
                'VAT_RATE',
                'VAT_INCLUDED',
                'MEASURE_CODE',
                'MEASURE_NAME',
                'QUANTITY',
                'WEIGHT',
                'DIMENSIONS'
            ];

            $goods[] = array_intersect_key($item, array_flip($chosenFields));
        }

        self::handleComplects($goods);
        self::callOnItemsListReady($goods);

        return $goods;
    }

    /**
     * Returns goods data array for given goods ids
     * @param array $goods of type id => quantity
     * @param bool|string|int $source Bitrix order id or false
     * @return array
     */
    public static function fromArray($goods, $source = false)
    {
        $allItems = ($source) ? self::fromOrder($source) : self::fromBasket();
        $arReturn = [];

        foreach ($allItems as $key => $val) {
            if (array_key_exists($val['PRODUCT_ID'], $goods)) {
                $_val = $val;
                $_val['QUANTITY'] = $goods[$val['PRODUCT_ID']];
                $arReturn[] = $_val;
            }
        }

        return $arReturn;
    }

    /**
     * Get basket goods that match da filter
     * @param array $filter conditions
     * @return array
     */
    protected static function getBasketGoods($filter = [])
    {
        $goods  = [];
        $select = [
            'ORDER_ID',
            'ID',
            'LID',
            'PRODUCT_ID',
            'TYPE',
            'SET_PARENT_ID',
            'NAME',
            'CAN_BUY',
            'DELAY',
            'PRICE',
            'BASE_PRICE',
            'CURRENCY',
            'VAT_RATE',
            'VAT_INCLUDED',
            'MEASURE_CODE',
            'MEASURE_NAME',
            'QUANTITY',
            'WEIGHT',
            'DIMENSIONS'
        ];
        $noCanBuy = (defined(self::$ignoreCanBuy) && constant(self::$ignoreCanBuy) === true);

        $dbBasketItems = \CSaleBasket::GetList([], $filter, false, false, $select);
        while ($item = $dbBasketItems->Fetch()) {
            if (($item['CAN_BUY'] == 'Y' || $noCanBuy) && $item['DELAY'] == 'N') {
                $item['DIMENSIONS'] = unserialize($item['DIMENSIONS'], ['allowed_classes' => false]);
                $goods[] = $item;
            }
        }

        self::handleComplects($goods);
        self::callOnItemsListReady($goods);

        return $goods;
    }

    /**
     * Do magic with type SET basket goods
     * @param $goods
     */
    protected static function handleComplects(&$goods)
    {
        $complects = array();
        foreach ($goods as $good) {
            if (
                array_key_exists('SET_PARENT_ID', $good) &&
                $good['SET_PARENT_ID'] &&
                $good['SET_PARENT_ID'] != $good['ID']
            ) {
                $complects[$good['SET_PARENT_ID']] = true;
            }
        }

        if (defined(self::$complectsBlocker) && constant(self::$complectsBlocker) === true) {
            foreach ($goods as $key => $good) {
                if (array_key_exists($good['ID'], $complects)) {
                    unset($goods[$key]);
                }
            }
        } else {
            foreach ($goods as $key => $good) {
                if (
                    array_key_exists('SET_PARENT_ID', $good) &&
                    array_key_exists($good['SET_PARENT_ID'], $complects) &&
                    $good['SET_PARENT_ID'] != $good['ID']
                ) {
                    unset($goods[$key]);
                }
            }
        }
    }

    /**
     * Add marking codes to basket goods data
     * @param array $goods array of basket goods
     * @param int $orderId Bitrix order id
     */
    public static function addGoodsQRs(&$goods, $orderId)
    {
        $isMarkingAvailable = method_exists('\\Bitrix\\Sale\\ShipmentItemStore', 'getMarkingCode');
        $order = \Bitrix\Sale\Order::load($orderId);

        $shipments = $order->getShipmentCollection();
        foreach ($shipments as $shipment) {
            $items = $shipment->getShipmentItemCollection();
            foreach ($items as $item) {
                /** @var \Bitrix\Sale\BasketItem $basketItem */
                $basketItem = $item->getBasketItem();

                $stores = $item->getShipmentItemStoreCollection();
                foreach ($stores as $store) {
                    /** @var \Bitrix\Sale\ShipmentItemStore $store */
                    $mark = ($isMarkingAvailable) ? $store->getMarkingCode() : '';

                    foreach ($goods as $key => $stuff) {
                        if ($goods[$key]['PRODUCT_ID'] === $basketItem->getProductId()) {
                            if (!array_key_exists('QR', $goods[$key])) {
                                $goods[$key]['QR'] = array();
                            }
                            $goods[$key]['QR'][] = $mark;
                        }
                    }
                }
            }
        }
    }

    /**
     * Add property values to basket goods data
     * @param array $goods array of basket goods
     * @param string[] $propertyCodes array of IBlock element property codes
     */
    public static function addBasketGoodProperties(&$goods, $propertyCodes)
    {
        if (Loader::includeModule('iblock')) {
            $itemsProperties = [];
            $itemsToIblocks  = [];
            $itemsIds        = [];
            $offersToParents = [];
            $propertyCodes   = array_values(array_filter($propertyCodes));

            foreach ($goods as $good) {
                // Search for iblock required
                $itemsIds[] = $good['PRODUCT_ID'];

                // Already know where they are
                if ($parent = \CCatalogSku::GetProductInfo($good['PRODUCT_ID'])) {
                    $itemsToIblocks[$parent['IBLOCK_ID']][$parent['ID']]['SKU_CHILDS'][] = $good['PRODUCT_ID'];
                    $offersToParents[$good['PRODUCT_ID']] = $parent['ID'];
                }
            }

            $elementsDB = ElementTable::getList(['filter' => ['=ID' => $itemsIds], 'select' => ['ID', 'IBLOCK_ID']]);
            while ($tmp = $elementsDB->fetch()) {
                $itemsToIblocks[$tmp['IBLOCK_ID']][$tmp['ID']] = [];

                if (array_key_exists($tmp['ID'], $offersToParents?? [])) {
                    $itemsToIblocks[$tmp['IBLOCK_ID']][$tmp['ID']]['SKU_PARENT'] = $offersToParents[$tmp['ID']];
                }
            }
            unset($elementsDB);

            // Collect property values for all elements
            foreach ($itemsToIblocks as $iblockId => $elements) {
                $propsData = self::getElementPropertyValues($iblockId, array_keys($elements), $propertyCodes);

                foreach ($elements as $elementId => $data) {
                    $itemsProperties[$elementId] = $itemsToIblocks[$iblockId][$elementId];

                    if (!empty($propsData) && is_array($propsData[$elementId])) {
                        $itemsProperties[$elementId]['PROPERTIES'] = $propsData[$elementId];
                    }
                }
            }
            unset($itemsToIblocks);

            // Assign property values
            foreach ($goods as $key => $arGood) {
                $goods[$key]['PROPERTIES'] = [];

                $hasOwnProps = is_array($itemsProperties[$arGood['PRODUCT_ID']]['PROPERTIES']);
                foreach ($propertyCodes as $propertyCode) {
                    // Take own property value first
                    $goods[$key]['PROPERTIES'][$propertyCode] = ($hasOwnProps && array_key_exists($propertyCode, $itemsProperties[$arGood['PRODUCT_ID']]['PROPERTIES']?? [])) ?
                        $itemsProperties[$arGood['PRODUCT_ID']]['PROPERTIES'][$propertyCode] : '';

                    // Try SKU parent property if own property are empty and parent exists
                    if (empty($goods[$key]['PROPERTIES'][$propertyCode]) && array_key_exists('SKU_PARENT', $itemsProperties[$arGood['PRODUCT_ID']]?? [])) {
                        $parentId = $itemsProperties[$arGood['PRODUCT_ID']]['SKU_PARENT'];
                        if (is_array($itemsProperties[$parentId]['PROPERTIES']) && array_key_exists($propertyCode, $itemsProperties[$parentId]['PROPERTIES']?? [])) {
                            $goods[$key]['PROPERTIES'][$propertyCode] = $itemsProperties[$parentId]['PROPERTIES'][$propertyCode];
                        }
                    }
                }
            }
        }
    }

    /**
     * Get iblock element property values
     * @param int $iblockId IBlock Id
     * @param int[] $elementIds array of element Ids
     * @param string[] $propertyCodes array of IBlock element property codes
     * @return array
     */
    public static function getElementPropertyValues($iblockId, $elementIds, $propertyCodes)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $propertyResult = array_fill_keys($elementIds, ['PROPERTIES' => []]);
            $filter         = ['=ID' => $elementIds];
            $propertyFilter = ['CODE' => $propertyCodes];
            $options        = ['USE_PROPERTY_ID' => 'N', 'GET_RAW_DATA' => 'Y', 'PROPERTY_FIELDS' => ['DEFAULT_VALUE', 'MULTIPLE']];

            \CIBlockElement::GetPropertyValuesArray($propertyResult, (int)$iblockId, $filter, $propertyFilter, $options);

            foreach ($propertyResult as $elementId => $elementData) {
                if (!empty($elementData['PROPERTIES'])) {
                    foreach ($propertyCodes as $propertyCode) {
                        $result[$elementId][$propertyCode] = (array_key_exists($propertyCode, $propertyResult[$elementId]['PROPERTIES'])) ?
                            $propertyResult[$elementId]['PROPERTIES'][$propertyCode]['VALUE'] : '';

                        // Take first value if prop are multiple (normally no multiple props supported cause API handle only scalar values)
                        if ($propertyResult[$elementId]['PROPERTIES'][$propertyCode]['MULTIPLE'] === 'Y' && !empty($result[$elementId][$propertyCode])) {
                            $result[$elementId][$propertyCode] = array_shift($result[$elementId][$propertyCode]);
                        }
                    }
                }
            }
        }

        return $result;
    }
}