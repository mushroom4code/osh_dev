<?php

namespace  Enterego;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale;
use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;


class EnteregoGiftHandlers
{

    /**
     * Add gift in basket if discount sale can be applied
     *
     * @throws ObjectNotFoundException
     * @throws ObjectException
     * @throws NotSupportedException
     * @throws NotImplementedException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentException
     */
    public static function OnLoadSaleBasketAddGiftInBasket()
    {
        $userId = Fuser::getId() ;

        /** @var Basket $basket */
        $basket = Basket::loadItemsForFUser(
            $userId,
            Context::getCurrent()->getSite()
        );

        /** @var array<int, Sale\BasketItem> $basketItems */
        $basketItems = $basket->getBasketItems();
        $productIds = [];
        foreach ($basketItems as $basketItem) {
            $productIds[] = $basketItem->getField('product_id');
        }

        $discountData = self::getDiscountData($basket);
        if(!empty($discountData[0])){
            foreach ($discountData[0] as $discount) {

                $productGiftIds = self::getGiftsData($discount);
                foreach ($productGiftIds as $productId) {
                    if (EnteregoHelper::productIsGift($productId) && !in_array($productId, $productIds)) {
                        \Bitrix\Catalog\Product\Basket::addProduct(['PRODUCT_ID' => $productId, 'QUANTITY' => 1]);
                    }
                }

            }
        }
    }

    /**
     * @override \Bitrix\Sale\Discount\Gift\RelatedDataTable
     * Returns gift data which contains list of element id. It's gifts for the discount.
     *
     * @param array $discount The discount.
     * @return array
     */
    public static function getGiftsData(array $discount): array
    {
        $sectionIds = $elementIds = array();

        foreach($discount['ACTIONS']['CHILDREN'] as $child)
        {
            if(!isset($child['CLASS_ID']) || !isset($child['DATA']) || $child['CLASS_ID'] !== \CSaleActionGiftCtrlGroup::getControlID())
            {
                continue;
            }
            foreach($child['CHILDREN'] as $gifterChild)
            {
                switch($gifterChild['CLASS_ID'])
                {
                    case 'GifterCondIBElement':
                        if ($gifterChild['DATA']['Type'] === 'all') {
                            $elementIds = array_merge($elementIds, (array)$gifterChild['DATA']['Value']);
                        }
                        break;
                    case 'GifterCondIBSection':
                        $sectionIds = array_merge($sectionIds, (array)$gifterChild['DATA']['Value']);
                        break;
                }
            }
            unset($gifterChild);
        }
        unset($child);

        return $elementIds;
    }

    /** Initialize order for calculate discount
     * @param Sale\BasketBase $basket
     * @return Sale\Order|null
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     */
    private static function InitializeOrder(Sale\BasketBase $basket): ?Sale\Order
    {
        //$order = $basket->getOrder();
        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
        /** @var Sale\Order $orderClass */
        $orderClass = $registry->getOrderClassName();

        $userId = Fuser::getId() ;
        $order = $orderClass::create(Context::getCurrent()->getSite(), $userId);

        $result = $order->appendBasket($basket);
        if (!$result->isSuccess())
        {
            //            $this->errorCollection->add($result->getErrors());
            return null;
        }
        return $order;
    }

    /**
     * @override CBitrixBasketComponent
     * @param Sale\BasketBase $basket
     * @return array
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     */
    private static function getDiscountData(Sale\BasketBase $basket): array
    {
        /** @var Sale\Order $order */
        $order = self::InitializeOrder($basket);

        $calcResults = $order->getDiscount()->getApplyResult(true);

        $appliedDiscounts = [];

        foreach ($calcResults['DISCOUNT_LIST'] as $discountData)
        {
            $discountId = $discountData['REAL_DISCOUNT_ID'];
            if (isset($calcResults['FULL_DISCOUNT_LIST'][$discountId]))
            {
                $appliedDiscounts[$discountId] = $calcResults['FULL_DISCOUNT_LIST'][$discountId];

                if (empty($appliedDiscounts[$discountId]['RESULT']['BASKET']))
                {
                    $appliedDiscounts[$discountId]['RESULT']['BASKET'] = [];
                }

                $appliedDiscounts[$discountId]['RESULT']['BASKET'] = array_merge(
                    $appliedDiscounts[$discountId]['RESULT']['BASKET'],
                    self::getAffectedReformattedBasketItemsInDiscount($basket, $discountData, $calcResults)
                );
            }
        }
        unset($discountId, $discountData);

        return [$calcResults['FULL_DISCOUNT_LIST'], $appliedDiscounts];
    }

    /**
     * @override CBitrixBasketComponent
     * @param Sale\BasketBase $basket
     * @param array $discountData
     * @param array $calcResults
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected static function getAffectedReformattedBasketItemsInDiscount(Sale\BasketBase $basket, array $discountData, array $calcResults): array
    {
        $items = [];

        foreach ($calcResults['PRICES']['BASKET'] as $basketCode => $priceData)
        {
            if (empty($priceData['DISCOUNT']) || !empty($priceData['PRICE']) || empty($calcResults['RESULT']['BASKET'][$basketCode]))
            {
                continue;
            }

            //we have gift and PRICE equals 0.
            $found = false;

            foreach ($calcResults['RESULT']['BASKET'][$basketCode] as $data)
            {
                if ($data['DISCOUNT_ID'] == $discountData['ID'])
                {
                    $found = true;
                }
            }
            unset($data);

            if (!$found)
            {
                continue;
            }

            $basketItem = $basket->getItemByBasketCode($basketCode);
            if (!$basketItem || $basketItem->getField('MODULE') != 'catalog')
            {
                continue;
            }

            $items[] = [
                'PRODUCT_ID' => $basketItem->getProductId(),
                'VALUE_PERCENT' => '100',
                'MODULE' => 'catalog',
            ];
        }
        unset($priceData);

        return $items;
    }
}
