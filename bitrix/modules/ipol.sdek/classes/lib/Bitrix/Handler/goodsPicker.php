<?php
namespace Ipolh\SDEK\Bitrix\Handler;


use Ipolh\SDEK\Bitrix\Tools;

class goodsPicker
{
    public static function addGoodsQRs(&$arGoods,$bitrixId)
    {
        if(Tools::isConverted()) {
            $order = \Bitrix\Sale\Order::load($bitrixId);

            $shipments = $order->getShipmentCollection();
            foreach ($shipments as $shipment) {
                $items = $shipment->getShipmentItemCollection();
                foreach ($items as $item) {
                    /** @var \Bitrix\Sale\BasketItem $basketItem */
                    $basketItem = $item->getBasketItem();
                    $stores     = $item->getShipmentItemStoreCollection();
                    foreach ($stores as $store) {
                        $storeId = $store->getStoreId();
                        $mark    = $store->getMarkingCode();

                        foreach ($arGoods as $key => $stuff) {
                            if ($arGoods[$key]['PRODUCT_ID'] === $basketItem->getProductId()) {
                                if (!array_key_exists('QR', $arGoods[$key])) {
                                    $arGoods[$key]['QR'] = array();
                                }
                                $arGoods[$key]['QR'] [] = $mark;
                            }
                        }
                    }
                }
            }
        }
    }
}