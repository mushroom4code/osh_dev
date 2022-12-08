<?php
namespace PickPoint;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Order;
use Bitrix\Main\UserTable;

class OrderOptions
{
    const MODULE_ID = 'pickpoint.deliveryservice';

    /**
     * @param $orderId
     * @param $personalType
     * @param $arOptionDefaults
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getOrderOptions($orderId, $personalType, $arOptionDefaults)
    {
        $arTableOptions = (unserialize(Option::get(self::MODULE_ID, 'OPTIONS')));

        if (!isset($arTableOptions[$personalType])) {
            $arData = $arOptionDefaults[$personalType];
        } else {
            $arData = $arTableOptions[$personalType];
        }

        $result = [];

        foreach ($arData as $key => $option) {
            switch ($option[0]['TYPE']) {
                case 'ANOTHER':
                    $result[$key] = $option[0]['VALUE'];
                    break;
                case 'USER':
                    $result[$key] = self::getUserPropValue($orderId, $option[0]['VALUE']);
                    break;
                case 'ORDER':
                    $result[$key] = self::getOrderParam($orderId, $option[0]['VALUE']);
                    break;
                case 'PROPERTY':
                    $result[$key] = self::getOrderPropValue($orderId, $option[0]['VALUE']);
                    break;
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getStoreOptionValues()
    {
        $storeData['IKN'] = Option::get(self::MODULE_ID, 'pp_ikn_number', '');
        $storeData['FROM_CITY'] = Option::get(self::MODULE_ID, 'pp_from_city', 'Moscow');
        $storeData['ADDRESS'] = Option::get(self::MODULE_ID, 'pp_store_address', '');
        $storeData['PHONE'] = Option::get(self::MODULE_ID, 'pp_store_phone', '+79160000000');
        $storeData['ENCLOSURE'] = Option::get(self::MODULE_ID, 'pp_enclosure', '');

        $storeData['WIDTH'] = Option::get(self::MODULE_ID, 'pp_dimension_width', '50');
        $storeData['HEIGHT'] = Option::get(self::MODULE_ID, 'pp_dimension_height', '50');
        $storeData['DEPTH'] = Option::get(self::MODULE_ID, 'pp_dimension_depth', '50');

        $storeData['DELIVERY_VAT'] = Option::get(self::MODULE_ID, 'delivery_vat', 'VATNONE');        

        $storeData['returnAddress'] = [
            'CityName' => Option::get(self::MODULE_ID, 'pp_store_city', ''),
            'RegionName' => Option::get(self::MODULE_ID, 'pp_store_region', ''),
            'Address' => $storeData['ADDRESS'],
            'FIO' => Option::get(self::MODULE_ID, 'pp_store_fio', ''),
            'PostCode' => Option::get(self::MODULE_ID, 'pp_store_post', ''),
            'Organisation' => Option::get(self::MODULE_ID, 'pp_store_organisation', ''),
            'PhoneNumber' => $storeData['PHONE'],
            'Comment' => Option::get(self::MODULE_ID, 'pp_store_comment', 'comment')
        ];

        return $storeData;
    }

    /**
     * @param int $orderId
     * @param int $propId
     * @return null|string|array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getOrderPropValue($orderId, $propId)
    {
        $result = null;
        $order = Order::load($orderId);

        if (!empty($orderId)) {
            $propertyCollection = $order->getPropertyCollection();
        }

        if (!empty($propertyCollection)) {
            $getItemByOrderPropertyId_value = $propertyCollection->getItemByOrderPropertyId($propId);
        }

        if (!empty($getItemByOrderPropertyId_value)) {
            $result = $getItemByOrderPropertyId_value->getValue();
        }

        return $result;
    }

    /**
     * @param int $orderId
     * @param string $propName
     * @return null|string
     * @throws \Bitrix\Main\SystemException
     */
    public static function getOrderParam($orderId, $propName)
    {
        if (!empty($orderId)) {
            $order = Order::load($orderId);

            return $order->getField($propName);
        }

        return false;
    }

    /**
     * @param int $orderId
     * @param string $propName
     * @return null|string
     * @throws \Bitrix\Main\SystemException
     */
    public static function getUserPropValue($orderId, $propName)
    {
        $order = Order::load($orderId);
        
        if (!empty($orderId)) {
            $userId = $order->getField('USER_ID');

            $propData = UserTable::getList(
                array(
                    'filter' => array(
                        'ID' => $userId
                    ),
                    'select' => array(
                        $propName
                    )
                )
            )->fetch();

            return $propData[$propName];
        }

        return false;
    }

    /**
     * @param int $orderId
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getBasketItemsData($orderId)
    {
        $products = [];

        if (!empty($orderId)) {

            $order = Order::load($orderId);

            $basket = $order->getBasket();
            $basketItems = $basket->getBasketItems();

            foreach ($basketItems as $basketItem) {

                $products[] = [
                    'GoodsCode' => $basketItem->getField('PRODUCT_ID'),
                    'Name' => $basketItem->getField('NAME'),
                    'Price' => $basketItem->getField('PRICE'),
                    'Quantity' => $basketItem->getField('QUANTITY'),
                    'Vat' => intval($basketItem->getField('VAT_RATE') * 100),
                    'Description' => $basketItem->getField('QUANTITY'),
                ];
            }
        }

        return $products;
    }
}